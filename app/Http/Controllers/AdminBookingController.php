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
    private function notifyCustomer(Booking $booking, string $title, array $details = []): void
    {
        app(\App\Services\CustomerUpdateMailer::class)->booking($booking,$title,$details);
    }

    public function dashboard()
    {
        $bookings = Booking::with(['rute', 'barang', 'latestInvoice.buktiPembayaran'])
            ->orderByDesc('tanggal_booking')
            ->orderByDesc('booking_id')
            ->get();

        return view('admin.dashboard', compact('bookings'));
    }

    public function ruteIndex()
    {
        $rutes = RuteHarga::with('tarifs')->orderBy('pelabuhan_asal')->orderBy('pelabuhan_tujuan')->get();

        return view('admin.rute-index', compact('rutes'));
    }

    public function ruteStore(Request $request)
    {
        $validated = $request->validate([
            'pelabuhan_asal' => ['required', 'string', 'max:150'],
            'pelabuhan_tujuan' => ['required', 'string', 'max:150'],
            'harga_20' => ['required', 'numeric', 'min:1', 'max:9999999999'],
            'harga_40' => ['required', 'numeric', 'min:1', 'max:9999999999'],
            'keterangan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
        $rute = RuteHarga::updateOrCreate(
            [
                'pelabuhan_asal' => $validated['pelabuhan_asal'],
                'pelabuhan_tujuan' => $validated['pelabuhan_tujuan'],
            ],
            ['harga_dasar'=>$validated['harga_20'], 'keterangan'=>$validated['keterangan'] ?? null]
        );
        foreach ([20,40] as $size) {
            $rute->tarifs()->updateOrCreate(['ukuran_feet'=>$size], ['harga'=>$validated['harga_'.$size]]);
        }
        });

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
            'harga_final' => ['required', 'numeric', 'min:1', 'max:9999999999.99', 'decimal:0,2'],
            'estimasi_waktu' => ['required', 'integer', 'min:1', 'max:3650'],
            'tanggal_pengiriman_usulan'=>['required','date','after_or_equal:today'],
            'keterangan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($booking, $validated) {
            $booking=Booking::whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();
            abort_if($booking->status_booking==='dibatalkan' || $booking->status_harga==='dikonfirmasi_customer' || $booking->latestInvoice()->exists(),409,'Penawaran yang sudah disetujui tidak dapat diubah.');
            $booking->update([
                'harga_final'  => $validated['harga_final'],
                'status_harga' => 'sudah_ditawarkan',
                'status_booking' => 'menunggu_konfirmasi_customer',
                'diberikan_oleh' => Auth::id(),
                'estimasi_waktu' => (string)$validated['estimasi_waktu'],
                'estimasi_hari' => $validated['estimasi_waktu'],
                'tanggal_pengiriman_usulan' => $validated['tanggal_pengiriman_usulan'],
                'versi_penawaran' => $booking->versi_penawaran + 1,
                'catatan_penolakan' => null,
                'total_harga' => $validated['harga_final'],
            ]);
            $booking->statusHistory()->create([
                'updated_by' => Auth::id(),
                'status' => 'diproses',
                'keterangan' => 'Penawaran #'.$booking->versi_penawaran.': total Rp '.number_format($booking->harga_final,0,',','.').'; tanggal '.$validated['tanggal_pengiriman_usulan'].'; estimasi '.$validated['estimasi_waktu'].' hari. '.($validated['keterangan'] ?? ''),
            ]);
        });

        $this->notifyCustomer($booking,'Penawaran harga dan jadwal diperbarui',[
            'Total penawaran: Rp '.number_format($validated['harga_final'],0,',','.'),
            'Usulan tanggal pengiriman: '.$validated['tanggal_pengiriman_usulan'],
            'Estimasi waktu: '.$validated['estimasi_waktu'].' hari',
            $validated['keterangan'] ?? '',
        ]);
        return back()->with('success', 'Penawaran harga final berhasil dikirim.');
    }

    public function invoice(Request $request, Booking $booking)
    {
        abort_unless($booking->status_harga === 'dikonfirmasi_customer',409,'Penawaran belum disetujui.');
        abort_if($booking->latestInvoice()->exists(),409,'Invoice sudah diterbitkan. Gunakan invoice yang ada.');
        $validated = $request->validate([
            'tanggal_invoice' => ['required', 'date'],
            'ppn_persen' => ['required', 'numeric', 'min:0'],
            'terms' => ['required', 'string', 'max:20'],
            'catatan' => ['nullable', 'string'],
        ]);

        $subtotal = (float) $booking->harga_final;
        $ppn = $subtotal * ((float) $validated['ppn_persen'] / 100);

        DB::transaction(function() use ($booking,$validated,$subtotal,$ppn) {
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

        });
        $this->notifyCustomer($booking,'Invoice pengiriman diterbitkan',['Nomor invoice: '.$booking->latestInvoice->no_invoice]);
        return back()->with('success', 'Invoice berhasil diterbitkan.');
    }

    public function verifyPayment(Request $request, BuktiPembayaran $bukti)
    {
        $validated = $request->validate([
            'status_konfirmasi' => ['required', 'in:dikonfirmasi,ditolak'],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($bukti, $validated) {
            $invoice=Invoice::whereKey($bukti->invoice_id)->lockForUpdate()->firstOrFail();
            $bukti=BuktiPembayaran::whereKey($bukti->bukti_id)->lockForUpdate()->firstOrFail();
            abort_unless($bukti->status_konfirmasi==='menunggu',409,'Bukti pembayaran sudah diverifikasi.');
            $bukti->update([
                'status_konfirmasi'=>$validated['status_konfirmasi'],
                'dikonfirmasi_oleh'=>Auth::id(),'tanggal_konfirmasi'=>now(),
                'catatan_admin'=>$validated['catatan_admin'] ?? null,
            ]);
            $paid=$invoice->buktiPembayaran()->where('status_konfirmasi','dikonfirmasi')->sum('jumlah_bayar');
            $lunas=(float)$paid >= (float)$invoice->total_bayar;
            $invoice->update(['status_bayar'=>$lunas?'lunas':'belum_lunas',
                'tanggal_lunas'=>$lunas?($invoice->tanggal_lunas ?? today()):null]);
            // Preserve an operational shipment's current state if another queued proof is reviewed.
            if (!in_array($invoice->booking->status_booking,['dalam_pengiriman','diterima','selesai','dibatalkan'],true)) {
                $invoice->booking->update(['status_booking'=>$lunas?'siap_operasional':
                    ($validated['status_konfirmasi']==='ditolak'?'pembayaran_ditolak':'menunggu_pembayaran')]);
            }
        });

        $invoice=$bukti->invoice()->firstOrFail();
        $this->notifyCustomer($invoice->booking,'Hasil verifikasi pembayaran',[
            'Bukti pembayaran: '.($validated['status_konfirmasi']==='dikonfirmasi'?'Dikonfirmasi':'Ditolak'),
            'Status invoice: '.($invoice->status_bayar==='lunas'?'Lunas':'Belum Lunas'),
            $invoice->tanggal_lunas?'Tanggal pelunasan: '.$invoice->tanggal_lunas->format('d/m/Y'):'',
            $validated['catatan_admin'] ?? '',
        ]);
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
            'joa_number',
            'no_container',
            'shipping_line',
            'feeder_vessel',
            'connecting_vessel',
            'destination',
            'stuff_date',
            'etd',
            'eta',
        ])->all();

        $suratJalanData = collect($validated)->only([
            'no_surat_jalan',
            'tanggal',
            'kendaraan',
            'nopol_kendaraan',
            'nama_sopir',
            'penerima_kepada',
            'lokasi_penerima',
            'nama_pengirim',
        ])->all();

        DB::transaction(function() use ($booking,$containerData,$suratJalanData) {
        if (collect($containerData)->filter()->isNotEmpty()) {
            BookingContainer::updateOrCreate(['booking_id' => $booking->booking_id], $containerData);
        }

        if (!empty($suratJalanData['no_surat_jalan'])) {
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

        });
        $labels=['joa_number'=>'Nomor JOA','no_container'=>'Nomor kontainer','shipping_line'=>'Shipping line',
            'feeder_vessel'=>'Feeder vessel','connecting_vessel'=>'Connecting vessel','destination'=>'Tujuan',
            'stuff_date'=>'Tanggal muat','etd'=>'Estimasi keberangkatan','eta'=>'Estimasi kedatangan',
            'no_surat_jalan'=>'Nomor surat jalan','tanggal'=>'Tanggal surat jalan','kendaraan'=>'Kendaraan',
            'nopol_kendaraan'=>'Nomor kendaraan','nama_sopir'=>'Nama sopir','penerima_kepada'=>'Penerima',
            'lokasi_penerima'=>'Lokasi penerima','nama_pengirim'=>'Nama pengirim'];
        $details=[];
        foreach($validated as $key=>$value) if($value!==null && $value!=='') $details[]=($labels[$key] ?? $key).': '.$value;
        $this->notifyCustomer($booking,'Detail operasional pengiriman diperbarui',$details);
        return back()->with('success', 'Detail operasional berhasil diperbarui.');
    }

    public function updateProgress(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status_booking' => ['required', 'in:dalam_pengiriman,diterima,selesai,dibatalkan'],
            'keterangan' => ['required', 'string'],
        ]);

        DB::transaction(function() use ($booking,$validated) {
        $booking->update(['status_booking' => $validated['status_booking']]);
        $booking->statusHistory()->create([
            'updated_by' => Auth::id(),
            'status' => in_array($validated['status_booking'], ['selesai', 'diterima'], true) ? 'selesai' : ($validated['status_booking'] === 'dibatalkan' ? 'dibatalkan' : 'diproses'),
            'keterangan' => $validated['keterangan'],
        ]);

        });
        $this->notifyCustomer($booking,'Status pengiriman diperbarui',[$validated['keterangan']]);
        return back()->with('success', 'Progress pengiriman berhasil diperbarui.');
    }

    private function generateInvoiceNumber(Booking $booking): string
    {
        return str_replace('BKJ-', 'INV-', $booking->kode_booking) . '/' . now()->format('Y');
    }
}
