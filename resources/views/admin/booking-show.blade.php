@extends('layouts.app')

@section('title', 'Kelola Booking')

@php
    $container = $booking->container->first();
    $suratJalan = $booking->suratJalan->first();

    $hasOffer = ! is_null($booking->harga_final);
    $hasContainer = $container && collect($container->only([
        'joa_number', 'no_container', 'shipping_line', 'feeder_vessel', 'connecting_vessel', 'destination',
    ]))->filter()->isNotEmpty();
    $hasSuratJalan = $suratJalan && ! empty($suratJalan->no_surat_jalan);

    $operationalStages = ['siap_operasional', 'dalam_pengiriman', 'diterima', 'selesai'];
    $canOperational = in_array($booking->status_booking, $operationalStages, true);
    $finalStages = ['selesai', 'dibatalkan'];
    $canUpdateProgress = ! in_array($booking->status_booking, $finalStages, true);

    $nextProgressOptions = [];
    if ($booking->status_booking === 'siap_operasional') {
        $nextProgressOptions['dalam_pengiriman'] = 'Dalam Pengiriman';
    }
    if ($booking->status_booking === 'dalam_pengiriman') {
        $nextProgressOptions['diterima'] = 'Diterima Penerima';
    }
    if ($booking->status_booking === 'diterima') {
        $nextProgressOptions['selesai'] = 'Selesai';
    }
    if ($canUpdateProgress) {
        $nextProgressOptions['dibatalkan'] = 'Dibatalkan';
    }

    $actionNeeded = match (true) {
        $booking->status_booking === 'menunggu_penawaran' => ['warning', 'Booking ini menunggu penawaran harga final dari Anda.'],
        $booking->status_booking === 'menunggu_verifikasi_pembayaran' => ['warning', 'Ada bukti pembayaran yang menunggu verifikasi Anda.'],
        $booking->status_booking === 'pembayaran_ditolak' => ['warning', 'Pembayaran sebelumnya ditolak. Customer perlu mengunggah ulang bukti bayar.'],
        $canOperational && ! $hasContainer => ['info', 'Pembayaran sudah beres. Lengkapi detail operasional pengiriman.'],
        default => null,
    };
@endphp

@section('content')
<section class="section-soft py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h1 class="h3 text-navy mb-1">{{ $booking->kode_booking }}</h1>
                <p class="text-steel mb-0">{{ $booking->nama_customer }} &middot; {{ $booking->asal }} ke {{ $booking->tujuan }}</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        @if ($actionNeeded)
            <div class="alert alert-{{ $actionNeeded[0] }} d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $actionNeeded[1] }}</span>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="bg-white border rounded-3 p-4 mb-4">
                    <h2 class="h5 text-navy">Ringkasan Booking</h2>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @foreach (['Menunggu Penawaran', 'Menunggu Konfirmasi', 'Menunggu Pembayaran', 'Verifikasi Pembayaran', 'Proses Pengiriman', 'Selesai'] as $step)
                            <span class="badge rounded-pill {{ $booking->statusSederhana() === $step ? 'text-bg-primary' : 'text-bg-light border text-secondary' }}">{{ $step }}</span>
                        @endforeach
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3"><div class="small text-steel">Status</div><strong>{{ $booking->statusSederhana() }}</strong></div>
                        <div class="col-md-3"><div class="small text-steel">Total Berat</div><strong>{{ number_format($booking->totalBerat(), 2, ',', '.') }} kg</strong></div>
                        <div class="col-md-3"><div class="small text-steel">Tanggal Kirim</div><strong>{{ optional($booking->tanggal_pengiriman)->format('d/m/Y') ?? '-' }}</strong></div>
                        <div class="col-md-3"><div class="small text-steel">Jumlah Container</div><strong>{{ $booking->jumlah_container }}</strong></div>
                        <div class="col-md-6"><div class="small text-steel">Estimasi Awal</div><strong>Rp {{ number_format($booking->harga_estimasi ?? 0, 0, ',', '.') }}</strong></div>
                        <div class="col-md-6"><div class="small text-steel">Harga Final</div><strong>{{ $hasOffer ? 'Rp ' . number_format($booking->harga_final, 0, ',', '.') : '- Belum ditawarkan -' }}</strong></div>
                    </div>
                </div>

                <div class="bg-white border rounded-3 p-4 mb-4">
                    <h2 class="h5 text-navy">Barang</h2>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>Nama</th><th>Kategori</th><th>Qty</th><th>Berat</th><th>Keterangan</th></tr></thead>
                            <tbody>
                                @foreach ($booking->barang as $item)
                                    <tr>
                                        <td>{{ $item->nama_barang }}</td>
                                        <td>{{ $item->kategori_barang ?? '-' }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>{{ number_format($item->berat_kg, 2, ',', '.') }} kg</td>
                                        <td>{{ $item->keterangan ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white border rounded-3 p-4">
                    <h2 class="h5 text-navy">Riwayat Progress</h2>
                    @forelse ($booking->statusHistory as $status)
                        <div class="border-bottom py-2">
                            <div class="fw-semibold text-capitalize">{{ $status->status }}</div>
                            <div class="small text-steel">{{ $status->keterangan }}</div>
                        </div>
                    @empty
                        <p class="text-steel mb-0">Belum ada progress.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4">
                {{-- ===== PENAWARAN HARGA FINAL ===== --}}
                <div class="bg-white border rounded-3 p-4 mb-4 @if($actionNeeded && str_contains($actionNeeded[1], 'penawaran harga')) border-warning @endif">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 text-navy mb-0">Penawaran Harga Final</h2>
                        @if ($hasOffer)
                            <button type="button" class="btn btn-sm btn-outline-accent" data-bs-toggle="collapse" data-bs-target="#formPenawaran">
                                <i class="bi bi-pencil me-1"></i> Ubah
                            </button>
                        @endif
                    </div>

                    @if ($hasOffer)
                        <div class="row g-3 mb-2">
                            <div class="col-6"><div class="small text-steel">Harga Final</div><strong>Rp {{ number_format($booking->harga_final, 0, ',', '.') }}</strong></div>
                            <div class="col-6"><div class="small text-steel">Estimasi Waktu</div><strong>{{ $booking->estimasi_waktu ?? '-' }}</strong></div>
                        </div>
                    @endif

                    <div id="formPenawaran" class="{{ $hasOffer ? 'collapse' : '' }}">
                        <form method="POST" action="{{ route('admin.booking.offer', $booking) }}" class="{{ $hasOffer ? 'pt-2 border-top mt-2' : '' }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Harga Final</label>
                                <input type="number" name="harga_final" class="form-control" value="{{ old('harga_final', $booking->harga_final) }}" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Estimasi Waktu</label>
                                <input type="text" name="estimasi_waktu" class="form-control" value="{{ old('estimasi_waktu', $booking->estimasi_waktu) }}" placeholder="Contoh: 7-10 hari">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
                            </div>
                            <button class="btn btn-accent w-100">{{ $hasOffer ? 'Simpan Perubahan' : 'Kirim Penawaran' }}</button>
                        </form>
                    </div>
                </div>

                {{-- ===== INVOICE ===== --}}
                <div class="bg-white border rounded-3 p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
                        <h2 class="h5 text-navy mb-0">Invoice</h2>
                        @if ($booking->latestInvoice)
                            <a href="{{ route('invoice.pdf', $booking->latestInvoice) }}" target="_blank" class="btn btn-sm btn-outline-accent">
                                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                            </a>
                        @endif
                    </div>
                    @if ($booking->latestInvoice)
                        <div class="small text-steel">No Invoice</div>
                        <div class="fw-semibold mb-2">{{ $booking->latestInvoice->no_invoice }}</div>
                        <div class="d-flex justify-content-between small"><span>Subtotal</span><strong>Rp {{ number_format($booking->latestInvoice->subtotal, 0, ',', '.') }}</strong></div>
                        <div class="d-flex justify-content-between small"><span>PPN {{ number_format($booking->latestInvoice->ppn_persen, 2, ',', '.') }}%</span><strong>Rp {{ number_format($booking->latestInvoice->ppn_nominal, 0, ',', '.') }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between"><span>Total</span><strong>Rp {{ number_format($booking->latestInvoice->total_bayar, 0, ',', '.') }}</strong></div>
                    @else
                        <p class="text-steel mb-0">Invoice akan otomatis dibuat setelah customer menyetujui penawaran harga final.</p>
                    @endif
                </div>

                {{-- ===== PEMBAYARAN ===== --}}
                @if ($booking->latestInvoice)
                    <div class="bg-white border rounded-3 p-4 mb-4 @if($actionNeeded && str_contains($actionNeeded[1], 'verifikasi')) border-warning @endif">
                        <h2 class="h5 text-navy">Pembayaran</h2>
                        <p class="mb-2">Total invoice: <strong>Rp {{ number_format($booking->latestInvoice->total_bayar, 0, ',', '.') }}</strong></p>
                        @forelse ($booking->latestInvoice->buktiPembayaran as $bukti)
                            <div class="border rounded-3 p-3 mb-3 {{ $bukti->status_konfirmasi === 'menunggu' ? 'border-warning' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="small text-steel">Bukti #{{ $bukti->bukti_id }}</div>
                                    <span class="badge {{ match($bukti->status_konfirmasi) { 'dikonfirmasi' => 'text-bg-success', 'ditolak' => 'text-bg-danger', default => 'text-bg-warning' } }}">{{ str_replace('_', ' ', $bukti->status_konfirmasi) }}</span>
                                </div>
                                <div class="fw-semibold">Rp {{ number_format($bukti->jumlah_bayar, 0, ',', '.') }}</div>
                                <a href="{{ asset('storage/' . $bukti->file_bukti) }}" target="_blank" class="small">Lihat file</a>
                                @if ($bukti->status_konfirmasi === 'menunggu')
                                    <form method="POST" action="{{ route('admin.payment.verify', $bukti) }}" class="mt-2">
                                        @csrf
                                        <textarea name="catatan_admin" class="form-control form-control-sm mb-2" rows="2" placeholder="Catatan admin (opsional)"></textarea>
                                        <div class="d-flex gap-2">
                                            <button name="status_konfirmasi" value="dikonfirmasi" class="btn btn-sm btn-success flex-fill">Sah</button>
                                            <button name="status_konfirmasi" value="ditolak" class="btn btn-sm btn-outline-danger flex-fill">Tolak</button>
                                        </div>
                                    </form>
                                @else
                                    <div class="small text-steel mt-1">{{ $bukti->catatan_admin ?? '' }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-steel mb-0">Belum ada bukti pembayaran.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                {{-- ===== DETAIL OPERASIONAL ===== --}}
                <div class="bg-white border rounded-3 p-4">
                    <h2 class="h5 text-navy mb-3">Detail Operasional</h2>

                    @if (! $canOperational)
                        <p class="text-steel mb-0"><i class="bi bi-lock me-1"></i> Bisa diisi setelah pembayaran diverifikasi dan booking siap operasional.</p>
                    @else
                        <form method="POST" action="{{ route('admin.booking.operational', $booking) }}">
                            @csrf

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h3 class="h6 text-navy mb-0">Container &amp; Vessel</h3>
                                @if ($hasContainer)
                                    <button type="button" class="btn btn-sm btn-outline-accent" data-bs-toggle="collapse" data-bs-target="#formContainer">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                @endif
                            </div>

                            @if ($hasContainer)
                                <div class="row g-2 small mb-2">
                                    <div class="col-md-4"><span class="text-steel">JOA Number:</span> <strong>{{ $container->joa_number ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">No Container:</span> <strong>{{ $container->no_container ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">Shipping Line:</span> <strong>{{ $container->shipping_line ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">Feeder Vessel:</span> <strong>{{ $container->feeder_vessel ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">Connecting Vessel:</span> <strong>{{ $container->connecting_vessel ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">Destination:</span> <strong>{{ $container->destination ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">Stuff Date:</span> <strong>{{ optional($container->stuff_date)->format('d/m/Y') ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">ETD:</span> <strong>{{ optional($container->etd)->format('d/m/Y') ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">ETA:</span> <strong>{{ optional($container->eta)->format('d/m/Y') ?? '-' }}</strong></div>
                                </div>
                            @endif

                            <div id="formContainer" class="row g-3 {{ $hasContainer ? 'collapse mt-2 pt-2 border-top' : '' }}">
                                <div class="col-md-4"><label class="form-label">JOA Number</label><input name="joa_number" class="form-control" value="{{ old('joa_number', optional($container)->joa_number) }}"></div>
                                <div class="col-md-4"><label class="form-label">No Container</label><input name="no_container" class="form-control" value="{{ old('no_container', optional($container)->no_container) }}"></div>
                                <div class="col-md-4"><label class="form-label">Shipping Line</label><input name="shipping_line" class="form-control" value="{{ old('shipping_line', optional($container)->shipping_line) }}"></div>
                                <div class="col-md-4"><label class="form-label">Feeder Vessel</label><input name="feeder_vessel" class="form-control" value="{{ old('feeder_vessel', optional($container)->feeder_vessel) }}"></div>
                                <div class="col-md-4"><label class="form-label">Connecting Vessel</label><input name="connecting_vessel" class="form-control" value="{{ old('connecting_vessel', optional($container)->connecting_vessel) }}"></div>
                                <div class="col-md-4"><label class="form-label">Destination</label><input name="destination" class="form-control" value="{{ old('destination', optional($container)->destination) }}"></div>
                                <div class="col-md-4"><label class="form-label">Stuff Date</label><input type="date" name="stuff_date" class="form-control" value="{{ old('stuff_date', optional(optional($container)->stuff_date)->format('Y-m-d')) }}"></div>
                                <div class="col-md-4"><label class="form-label">ETD</label><input type="date" name="etd" class="form-control" value="{{ old('etd', optional(optional($container)->etd)->format('Y-m-d')) }}"></div>
                                <div class="col-md-4"><label class="form-label">ETA</label><input type="date" name="eta" class="form-control" value="{{ old('eta', optional(optional($container)->eta)->format('Y-m-d')) }}"></div>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h3 class="h6 text-navy mb-0">Surat Jalan</h3>
                                @if ($hasSuratJalan)
                                    <button type="button" class="btn btn-sm btn-outline-accent" data-bs-toggle="collapse" data-bs-target="#formSuratJalan">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                @endif
                            </div>

                            @if ($hasSuratJalan)
                                <div class="row g-2 small mb-2">
                                    <div class="col-md-4"><span class="text-steel">No Surat Jalan:</span> <strong>{{ $suratJalan->no_surat_jalan }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">Tanggal:</span> <strong>{{ optional($suratJalan->tanggal)->format('d/m/Y') ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">Kendaraan:</span> <strong>{{ $suratJalan->kendaraan ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">No Polisi:</span> <strong>{{ $suratJalan->nopol_kendaraan ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">Nama Sopir:</span> <strong>{{ $suratJalan->nama_sopir ?? '-' }}</strong></div>
                                    <div class="col-md-4"><span class="text-steel">Penerima:</span> <strong>{{ $suratJalan->penerima_kepada ?? '-' }}</strong></div>
                                    <div class="col-md-6"><span class="text-steel">Lokasi Penerima:</span> <strong>{{ $suratJalan->lokasi_penerima ?? '-' }}</strong></div>
                                    <div class="col-md-6"><span class="text-steel">Nama Pengirim:</span> <strong>{{ $suratJalan->nama_pengirim ?? '-' }}</strong></div>
                                </div>
                            @endif

                            <div id="formSuratJalan" class="row g-3 {{ $hasSuratJalan ? 'collapse mt-2 pt-2 border-top' : '' }}">
                                <div class="col-md-4"><label class="form-label">No Surat Jalan</label><input name="no_surat_jalan" class="form-control" value="{{ old('no_surat_jalan', optional($suratJalan)->no_surat_jalan) }}"></div>
                                <div class="col-md-4"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', optional(optional($suratJalan)->tanggal)->format('Y-m-d')) }}"></div>
                                <div class="col-md-4"><label class="form-label">Kendaraan</label><input name="kendaraan" class="form-control" value="{{ old('kendaraan', optional($suratJalan)->kendaraan) }}"></div>
                                <div class="col-md-4"><label class="form-label">No Polisi</label><input name="nopol_kendaraan" class="form-control" value="{{ old('nopol_kendaraan', optional($suratJalan)->nopol_kendaraan) }}"></div>
                                <div class="col-md-4"><label class="form-label">Nama Sopir</label><input name="nama_sopir" class="form-control" value="{{ old('nama_sopir', optional($suratJalan)->nama_sopir) }}"></div>
                                <div class="col-md-4"><label class="form-label">Penerima</label><input name="penerima_kepada" class="form-control" value="{{ old('penerima_kepada', optional($suratJalan)->penerima_kepada) }}"></div>
                                <div class="col-md-6"><label class="form-label">Lokasi Penerima</label><input name="lokasi_penerima" class="form-control" value="{{ old('lokasi_penerima', optional($suratJalan)->lokasi_penerima) }}"></div>
                                <div class="col-md-6"><label class="form-label">Nama Pengirim</label><input name="nama_pengirim" class="form-control" value="{{ old('nama_pengirim', optional($suratJalan)->nama_pengirim) }}"></div>
                            </div>

                            <button class="btn btn-accent mt-3">Simpan Operasional</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                {{-- ===== UPDATE PROGRESS ===== --}}
                <div class="bg-white border rounded-3 p-4">
                    <h2 class="h5 text-navy">Update Progress</h2>

                    @if (! $canUpdateProgress)
                        <p class="text-steel mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            Booking ini sudah {{ $booking->status_booking === 'selesai' ? 'selesai' : 'dibatalkan' }}. Tidak ada progress lanjutan.
                        </p>
                    @else
                        <form method="POST" action="{{ route('admin.booking.progress', $booking) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Status Berikutnya</label>
                                <select name="status_booking" class="form-select" required>
                                    @foreach ($nextProgressOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="4" required placeholder="Jelaskan progres terbaru..."></textarea>
                            </div>
                            <button class="btn btn-outline-accent w-100">Simpan Progress</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection