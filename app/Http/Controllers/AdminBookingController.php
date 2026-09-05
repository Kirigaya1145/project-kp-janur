<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingContainer;
use App\Models\BuktiPembayaran;
use App\Models\Invoice;
use App\Models\RuteHarga;
use App\Models\SuratJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminBookingController extends Controller
{
    public function dashboard()
    {
        $bookings = Booking::with(['rute', 'barang', 'latestInvoice.buktiPembayaran'])
            ->latest('booking_id')
            ->get();

        return view('admin.dashboard', compact('bookings'));
    }

    public function ruteIndex()
    {
        $rutes = RuteHarga::orderBy('pelabuhan_asal')->orderBy('pelabuhan_tujuan')->get();

        return view('admin.rute-index', compact('rutes'));
    }

    public function ruteStore(Request $request)
    {
        $validated = $request->validate([
            'pelabuhan_asal' => ['required', 'string', 'max:150'],
            'pelabuhan_tujuan' => ['required', 'string', 'max:150'],
            'harga_dasar' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ]);

        RuteHarga::updateOrCreate(
            [
                'pelabuhan_asal' => $validated['pelabuhan_asal'],
                'pelabuhan_tujuan' => $validated['pelabuhan_tujuan'],
            ],
            $validated
        );

        return back()->with('success', 'Data rute berhasil disimpan.');
    }

    public function show(Booking $booking)
    {
        $booking->load(['rute', 'barang', 'statusHistory.updater', 'latestInvoice.buktiPembayaran', 'container', 'suratJalan']);

        return view('admin.booking-show', compact('booking'));
    }

    public function offer(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'harga_final' => ['required', 'numeric', 'min:1'],
            'estimasi_waktu' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($booking, $validated) {
            $booking->update([
                'harga_final' => $validated['harga_final'],
                'status_harga' => 'sudah_ditawarkan',
                'status_booking' => 'menunggu_konfirmasi_customer',
                'diberikan_oleh' => Auth::id(),
                'estimasi_waktu' => $validated['estimasi_waktu'] ?? null,
                'total_harga' => $validated['harga_final'],
            ]);
            $booking->statusHistory()->create([
                'updated_by' => Auth::id(),
                'status' => 'diproses',
                'keterangan' => $validated['keterangan'] ?: 'Harga final telah ditawarkan ke customer.',
            ]);
        });

        return back()->with('success', 'Penawaran harga final berhasil dikirim.');
    }

    public function invoice(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'tanggal_invoice' => ['required', 'date'],
            'ppn_persen' => ['required', 'numeric', 'min:0'],
            'terms' => ['required', 'string', 'max:20'],
            'catatan' => ['nullable', 'string'],
        ]);

        $subtotal = (float) $booking->harga_final;
        $ppn = $subtotal * ((float) $validated['ppn_persen'] / 100);

        Invoice::updateOrCreate(
            ['booking_id' => $booking->booking_id],
            [
                'no_invoice' => $this->generateInvoiceNumber($booking),
                'tanggal_invoice' => $validated['tanggal_invoice'],
                'subtotal' => $subtotal,
                'ppn_persen' => $validated['ppn_persen'],
                'ppn_nominal' => $ppn,
                'total_bayar' => $subtotal + $ppn,
                'terms' => $validated['terms'],
                'status_bayar' => 'belum_lunas',
                'catatan' => $validated['catatan'] ?? null,
            ]
        );

        $booking->update(['status_booking' => 'menunggu_pembayaran']);

        return back()->with('success', 'Invoice berhasil diterbitkan.');
    }

    public function verifyPayment(Request $request, BuktiPembayaran $bukti)
    {
        $validated = $request->validate([
            'status_konfirmasi' => ['required', 'in:dikonfirmasi,ditolak'],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($bukti, $validated) {
            $bukti->update([
                'status_konfirmasi' => $validated['status_konfirmasi'],
                'dikonfirmasi_oleh' => Auth::id(),
                'tanggal_konfirmasi' => now(),
                'catatan_admin' => $validated['catatan_admin'] ?? null,
            ]);

            $invoice = $bukti->invoice;
            $booking = $invoice->booking;

            if ($validated['status_konfirmasi'] === 'dikonfirmasi') {
                $invoice->update([
                    'status_bayar' => 'lunas',
                    'tanggal_lunas' => now()->toDateString(),
                ]);
                $booking->update(['status_booking' => 'siap_operasional']);
            } else {
                $invoice->update(['status_bayar' => 'belum_lunas']);
                $booking->update(['status_booking' => 'pembayaran_ditolak']);
            }
        });

        return back()->with('success', 'Verifikasi pembayaran berhasil disimpan.');
    }

    public function operational(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'joa_number' => ['nullable', 'string', 'max:50'],
            'no_container' => ['nullable', 'string', 'max:50'],
            'shipping_line' => ['nullable', 'string', 'max:100'],
            'feeder_vessel' => ['nullable', 'string', 'max:100'],
            'connecting_vessel' => ['nullable', 'string', 'max:100'],
            'destination' => ['nullable', 'string', 'max:150'],
            'stuff_date' => ['nullable', 'date'],
            'etd' => ['nullable', 'date'],
            'eta' => ['nullable', 'date'],
            'no_surat_jalan' => ['nullable', 'string', 'max:50'],
            'tanggal' => ['nullable', 'date'],
            'kendaraan' => ['nullable', 'string', 'max:50'],
            'nopol_kendaraan' => ['nullable', 'string', 'max:20'],
            'nama_sopir' => ['nullable', 'string', 'max:100'],
            'penerima_kepada' => ['nullable', 'string', 'max:150'],
            'lokasi_penerima' => ['nullable', 'string', 'max:150'],
            'nama_pengirim' => ['nullable', 'string', 'max:100'],
        ]);

        $containerData = collect($validated)->only([
            'joa_number', 'no_container', 'shipping_line', 'feeder_vessel',
            'connecting_vessel', 'destination', 'stuff_date', 'etd', 'eta',
        ])->all();

        $suratJalanData = collect($validated)->only([
            'no_surat_jalan', 'tanggal', 'kendaraan', 'nopol_kendaraan',
            'nama_sopir', 'penerima_kepada', 'lokasi_penerima', 'nama_pengirim',
        ])->all();

        if (collect($containerData)->filter()->isNotEmpty()) {
            BookingContainer::updateOrCreate(['booking_id' => $booking->booking_id], $containerData);
        }

        if (! empty($suratJalanData['no_surat_jalan'])) {
            SuratJalan::updateOrCreate(['booking_id' => $booking->booking_id], $suratJalanData);
        }

        if (in_array($booking->status_booking, ['siap_operasional', 'dalam_pengiriman'], true)) {
            $booking->update(['status_booking' => 'dalam_pengiriman']);
        }

        $booking->statusHistory()->create([
            'updated_by' => Auth::id(),
            'status' => 'diproses',
            'keterangan' => 'Detail operasional pengiriman diperbarui.',
        ]);

        return back()->with('success', 'Detail operasional berhasil diperbarui.');
    }

    public function updateProgress(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status_booking' => ['required', 'in:dalam_pengiriman,diterima,selesai,dibatalkan'],
            'keterangan' => ['required', 'string'],
        ]);

        $booking->update(['status_booking' => $validated['status_booking']]);
        $booking->statusHistory()->create([
            'updated_by' => Auth::id(),
            'status' => in_array($validated['status_booking'], ['selesai', 'diterima'], true) ? 'selesai' : ($validated['status_booking'] === 'dibatalkan' ? 'dibatalkan' : 'diproses'),
            'keterangan' => $validated['keterangan'],
        ]);

        return back()->with('success', 'Progress pengiriman berhasil diperbarui.');
    }

    private function generateInvoiceNumber(Booking $booking): string
    {
        return str_replace('BKJ-', 'INV-', $booking->kode_booking) . '/' . now()->format('Y');
    }
}
