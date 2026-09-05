<?php

namespace App\Http\Controllers;
use App\Models\Booking;
use App\Models\BuktiPembayaran;
use App\Models\Invoice;
use App\Models\RuteHarga;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $bookings = Booking::where('user_id', Auth::id())
            ->with(['rute', 'latestInvoice.buktiPembayaran'])
            ->latest('booking_id')
            ->get();

        return view('pages.booking-riwayat', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $rutes = RuteHarga::orderBy('pelabuhan_asal')->orderBy('pelabuhan_tujuan')->get();

        return view('pages.booking-create', compact('rutes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'nama_customer' => ['required', 'string', 'max:100'],
            'email_customer' => ['nullable', 'email', 'max:100'],
            'no_hp_customer' => ['required', 'string', 'max:20'],
            'rute_id' => ['required', 'exists:rute_harga,rute_id'],
            'tanggal_pengiriman' => ['required', 'date', 'after_or_equal:today'],
            'jumlah_container' => ['required', 'integer', 'min:1'],
            'barang' => ['required', 'array', 'min:1'],
            'barang.*.nama_barang' => ['required', 'string', 'max:150'],
            'barang.*.kategori_barang' => ['nullable', 'string', 'max:100'],
            'barang.*.qty' => ['required', 'integer', 'min:1'],
            'barang.*.berat_kg' => ['nullable', 'numeric', 'min:0'],
            'barang.*.keterangan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $rute = RuteHarga::findOrFail($validated['rute_id']);
            $totalBerat = collect($validated['barang'])->sum(fn ($item) => (float) ($item['berat_kg'] ?? 0));
            $hargaEstimasi = $totalBerat * (float) $rute->harga_dasar;

            $booking = Booking::create([
                'kode_booking' => $this->generateKodeBooking(),
                'user_id' => Auth::id(),
                'nama_customer' => $validated['nama_customer'],
                'email_customer' => $validated['email_customer'] ?? null,
                'no_hp_customer' => $validated['no_hp_customer'],
                'rute_id' => $rute->rute_id,
                'harga_estimasi' => $hargaEstimasi,
                'status_harga' => 'menunggu_penawaran',
                'status_booking' => 'menunggu_penawaran',
                'tanggal_booking' => now()->toDateString(),
                'tanggal_pengiriman' => $validated['tanggal_pengiriman'],
                'jumlah_container' => $validated['jumlah_container'],
                'asal' => $rute->pelabuhan_asal,
                'tujuan' => $rute->pelabuhan_tujuan,
                'total_harga' => $hargaEstimasi,
            ]);

            foreach ($validated['barang'] as $item) {
                $booking->barang()->create([
                    'nama_barang' => $item['nama_barang'],
                    'kategori_barang' => $item['kategori_barang'] ?? null,
                    'qty' => $item['qty'],
                    'berat_kg' => $item['berat_kg'] ?? 0,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }

            session(['last_kode_booking' => $booking->kode_booking]);
        });

        return redirect('/booking/sukses')
            ->with('success', 'Booking berhasil diajukan. Tim kami akan segera menghubungi Anda untuk penawaran harga.');
    }

    public function sukses()
    {
        $kodeBooking = session('last_kode_booking');

        if (! $kodeBooking) {
            return redirect('/booking');
        }

        return view('pages.booking-sukses', compact('kodeBooking'));
    }

    public function formCek()
    {
        return view('pages.booking-cek');
    }

    public function show(string $id)
    {
        //
        $booking = Booking::with(['barang', 'statusHistory' => function ($q) {
                $q->latest('status_id');
            }])
            ->where('kode_booking', $id)
            ->first();

        if (! $booking) {
            return back()->with('error', 'Kode booking tidak ditemukan. Periksa kembali kode yang Anda masukkan.');
        }

        $booking->load(['rute', 'barang', 'statusHistory.updater', 'latestInvoice.buktiPembayaran', 'container', 'suratJalan']);

        return view('pages.booking-cek', compact('booking'));
    }

    public function confirmOffer(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'keputusan' => ['required', 'in:setuju,tolak'],
        ]);

        if ($validated['keputusan'] === 'setuju') {
            DB::transaction(function () use ($booking) {
                $booking->update([
                    'status_harga' => 'dikonfirmasi_customer',
                    'status_booking' => 'menunggu_pembayaran',
                    'tanggal_konfirmasi' => now(),
                ]);

                $this->createInvoiceForBooking($booking);

                $booking->statusHistory()->create([
                    'updated_by' => Auth::id(),
                    'status' => 'diproses',
                    'keterangan' => 'Customer menyetujui penawaran. Invoice otomatis diterbitkan.',
                ]);
            });

            return back()->with('success', 'Penawaran disetujui. Invoice sudah tersedia untuk pembayaran.');
        }

        $booking->update([
            'status_harga' => 'ditolak_customer',
            'status_booking' => 'penawaran_ditolak',
            'tanggal_konfirmasi' => now(),
        ]);

        return back()->with('success', 'Penawaran ditolak. Tim kami akan meninjau ulang booking Anda.');
    }

    public function uploadPayment(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->booking && $invoice->booking->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'jumlah_bayar' => ['required', 'numeric', 'min:1'],
            'file_bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $path = $request->file('file_bukti')->store('bukti-pembayaran', 'public');

        BuktiPembayaran::create([
            'invoice_id' => $invoice->invoice_id,
            'jumlah_bayar' => $validated['jumlah_bayar'],
            'file_bukti' => $path,
            'tanggal_upload' => now(),
            'status_konfirmasi' => 'menunggu',
        ]);

        $invoice->booking->update(['status_booking' => 'menunggu_verifikasi_pembayaran']);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi admin.');
    }

    public function downloadInvoice(Invoice $invoice)
    {
        $invoice->load(['booking.rute', 'booking.barang', 'booking.container', 'booking.suratJalan']);
        $booking = $invoice->booking;

        abort_unless($booking && (Auth::id() === $booking->user_id || in_array(Auth::user()->role, ['admin', 'staff'], true)), 403);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', public_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.invoice', compact('invoice', 'booking'))->render());
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();

        $filename = 'invoice-' . str_replace(['/', '\\'], '-', $invoice->no_invoice) . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function generateKodeBooking()
    {
        $lastId = (Booking::max('booking_id') ?? 0) + 1;

        return 'BKJ-' . str_pad($lastId, 3, '0', STR_PAD_LEFT);
    }

    private function createInvoiceForBooking(Booking $booking): void
    {
        $subtotal = (float) $booking->harga_final;
        $ppnPersen = 1.1;
        $ppnNominal = $subtotal * ($ppnPersen / 100);

        Invoice::updateOrCreate(
            ['booking_id' => $booking->booking_id],
            [
                'no_invoice' => str_replace('BKJ-', 'INV-', $booking->kode_booking) . '/' . now()->format('Y'),
                'tanggal_invoice' => now()->toDateString(),
                'subtotal' => $subtotal,
                'ppn_persen' => $ppnPersen,
                'ppn_nominal' => $ppnNominal,
                'total_bayar' => $subtotal + $ppnNominal,
                'terms' => 'CASH',
                'status_bayar' => 'belum_lunas',
                'catatan' => 'Invoice otomatis setelah customer menyetujui penawaran.',
            ]
        );
    }

}
