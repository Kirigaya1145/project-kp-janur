@extends('layouts.app')

@section('title', 'Cek Booking')

@section('content')
@php
    $statusLabel = [
        'menunggu_penawaran' => 'Menunggu Penawaran',
        'menunggu_konfirmasi_customer' => 'Menunggu Konfirmasi Customer',
        'menunggu_invoice' => 'Menunggu Invoice',
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_verifikasi_pembayaran' => 'Menunggu Verifikasi Pembayaran',
        'pembayaran_ditolak' => 'Pembayaran Ditolak',
        'siap_operasional' => 'Siap Operasional',
        'dalam_pengiriman' => 'Dalam Pengiriman',
        'diterima' => 'Diterima',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        'penawaran_ditolak' => 'Penawaran Ditolak',
    ];
@endphp
<section class="section-soft py-5">
    <div class="container">
        <div class="bg-white border rounded-3 p-4 mb-4">
            <h1 class="h4 text-navy mb-3">Cek Status Booking</h1>
            <form method="POST" action="{{ route('booking.cek.submit') }}" class="row g-2">
                @csrf
                <div class="col-md-9">
                    <input type="text" name="kode_booking" class="form-control" placeholder="Contoh: BKJ-001" value="{{ old('kode_booking', $booking->kode_booking ?? '') }}" required>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-accent" type="submit"><i class="bi bi-search me-1"></i> Cek Booking</button>
                </div>
            </form>
        </div>

        @isset($booking)
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="bg-white border rounded-3 p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <h2 class="h4 text-navy mb-1">{{ $booking->kode_booking }}</h2>
                                <p class="text-steel mb-0">{{ $booking->asal }} - {{ $booking->tujuan }}</p>
                            </div>
                            <span class="badge text-bg-primary">{{ $statusLabel[$booking->status_booking] ?? $statusLabel[$booking->status_harga] ?? $booking->status_harga }}</span>
                        </div>
                        <hr>
                        <div class="row g-3">
                            <div class="col-md-4"><div class="small text-steel">Estimasi Awal</div><strong>Rp {{ number_format($booking->harga_estimasi ?? 0, 0, ',', '.') }}</strong></div>
                            <div class="col-md-4"><div class="small text-steel">Harga Final</div><strong>Rp {{ number_format($booking->harga_final ?? 0, 0, ',', '.') }}</strong></div>
                            <div class="col-md-4"><div class="small text-steel">Tanggal Kirim</div><strong>{{ optional($booking->tanggal_pengiriman)->format('d/m/Y') }}</strong></div>
                        </div>
                    </div>

                    @if (Auth::check() && Auth::id() === $booking->user_id && $booking->status_harga === 'sudah_ditawarkan')
                        <div class="bg-white border rounded-3 p-4 mb-4">
                            <h3 class="h5 text-navy">Konfirmasi Penawaran</h3>
                            <p class="text-steel">Harga final yang ditawarkan: <strong>Rp {{ number_format($booking->harga_final, 0, ',', '.') }}</strong></p>
                            <form method="POST" action="{{ route('booking.confirm-offer', $booking) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="keputusan" value="setuju">
                                <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Setuju</button>
                            </form>
                            <form method="POST" action="{{ route('booking.confirm-offer', $booking) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="keputusan" value="tolak">
                                <button class="btn btn-outline-danger"><i class="bi bi-x-lg me-1"></i> Tolak</button>
                            </form>
                        </div>
                    @endif

                    @if ($booking->latestInvoice)
                        <div class="bg-white border rounded-3 p-4 mb-4">
                            <h3 class="h5 text-navy">Invoice</h3>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4"><div class="small text-steel">No Invoice</div><strong>{{ $booking->latestInvoice->no_invoice }}</strong></div>
                                <div class="col-md-4"><div class="small text-steel">Total Bayar</div><strong>Rp {{ number_format($booking->latestInvoice->total_bayar, 0, ',', '.') }}</strong></div>
                                <div class="col-md-4"><div class="small text-steel">Status</div><span class="badge text-bg-{{ $booking->latestInvoice->status_bayar === 'lunas' ? 'success' : 'warning' }}">{{ str_replace('_', ' ', $booking->latestInvoice->status_bayar) }}</span></div>
                            </div>
                            @if (Auth::check() && Auth::id() === $booking->user_id && $booking->latestInvoice->status_bayar !== 'lunas')
                                <form method="POST" action="{{ route('invoice.upload-payment', $booking->latestInvoice) }}" enctype="multipart/form-data" class="row g-3">
                                    @csrf
                                    <div class="col-md-4">
                                        <label class="form-label">Jumlah Bayar</label>
                                        <input type="number" name="jumlah_bayar" class="form-control" value="{{ old('jumlah_bayar', $booking->latestInvoice->total_bayar) }}" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Bukti Pembayaran</label>
                                        <input type="file" name="file_bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button class="btn btn-accent w-100">Unggah Bukti</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endif

                    <div class="bg-white border rounded-3 p-4">
                        <h3 class="h5 text-navy">Riwayat Progress</h3>
                        <div class="list-group list-group-flush">
                            @forelse ($booking->statusHistory as $status)
                                <div class="list-group-item px-0">
                                    <div class="fw-semibold text-capitalize">{{ $status->status }}</div>
                                    <div class="small text-steel">{{ $status->keterangan }}</div>
                                </div>
                            @empty
                                <div class="text-steel">Belum ada pembaruan progress.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-white border rounded-3 p-4">
                        <h3 class="h5 text-navy">Barang</h3>
                        @foreach ($booking->barang as $item)
                            <div class="border-bottom py-2">
                                <div class="fw-semibold">{{ $item->nama_barang }}</div>
                                <div class="small text-steel">{{ $item->qty }} item, {{ number_format($item->berat_kg, 2, ',', '.') }} kg</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endisset
    </div>
</section>
@endsection
