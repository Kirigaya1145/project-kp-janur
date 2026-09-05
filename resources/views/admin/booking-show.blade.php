@extends('layouts.app')

@section('title', 'Kelola Booking')

@section('content')
<section class="section-soft py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 text-navy mb-1">{{ $booking->kode_booking }}</h1>
                <p class="text-steel mb-0">{{ $booking->nama_customer }} - {{ $booking->asal }} ke {{ $booking->tujuan }}</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="bg-white border rounded-3 p-4 mb-4">
                    <h2 class="h5 text-navy">Ringkasan Booking</h2>
                    <div class="row g-3">
                        <div class="col-md-3"><div class="small text-steel">Status Harga</div><strong>{{ str_replace('_', ' ', $booking->status_harga) }}</strong></div>
                        <div class="col-md-3"><div class="small text-steel">Status Booking</div><strong>{{ str_replace('_', ' ', $booking->status_booking ?? '-') }}</strong></div>
                        <div class="col-md-3"><div class="small text-steel">Total Berat</div><strong>{{ number_format($booking->totalBerat(), 2, ',', '.') }} kg</strong></div>
                        <div class="col-md-3"><div class="small text-steel">Tanggal Kirim</div><strong>{{ optional($booking->tanggal_pengiriman)->format('d/m/Y') }}</strong></div>
                        <div class="col-md-6"><div class="small text-steel">Estimasi Awal</div><strong>Rp {{ number_format($booking->harga_estimasi ?? 0, 0, ',', '.') }}</strong></div>
                        <div class="col-md-6"><div class="small text-steel">Harga Final</div><strong>Rp {{ number_format($booking->harga_final ?? 0, 0, ',', '.') }}</strong></div>
                    </div>
                </div>

                <div class="bg-white border rounded-3 p-4 mb-4">
                    <h2 class="h5 text-navy">Barang</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Nama</th><th>Kategori</th><th>Qty</th><th>Berat</th><th>Keterangan</th></tr></thead>
                            <tbody>
                                @foreach ($booking->barang as $item)
                                    <tr>
                                        <td>{{ $item->nama_barang }}</td>
                                        <td>{{ $item->kategori_barang }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>{{ number_format($item->berat_kg, 2, ',', '.') }} kg</td>
                                        <td>{{ $item->keterangan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white border rounded-3 p-4 mb-4">
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
                <form method="POST" action="{{ route('admin.booking.offer', $booking) }}" class="bg-white border rounded-3 p-4 mb-4">
                    @csrf
                    <h2 class="h5 text-navy">Penawaran Harga Final</h2>
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
                    <button class="btn btn-accent w-100">Kirim Penawaran</button>
                </form>

                <form method="POST" action="{{ route('admin.booking.invoice', $booking) }}" class="bg-white border rounded-3 p-4 mb-4">
                    @csrf
                    <h2 class="h5 text-navy">Terbitkan Invoice</h2>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Invoice</label>
                        <input type="date" name="tanggal_invoice" class="form-control" value="{{ old('tanggal_invoice', now()->toDateString()) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">PPN Persen</label>
                        <input type="number" name="ppn_persen" class="form-control" value="{{ old('ppn_persen', 11) }}" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Terms</label>
                        <input type="text" name="terms" class="form-control" value="{{ old('terms', 'CASH') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2">{{ old('catatan') }}</textarea>
                    </div>
                    <button class="btn btn-outline-accent w-100" @disabled($booking->status_harga !== 'dikonfirmasi_customer')>Terbitkan Invoice</button>
                </form>

                @if ($booking->latestInvoice)
                    <div class="bg-white border rounded-3 p-4 mb-4">
                        <h2 class="h5 text-navy">Pembayaran</h2>
                        <p class="mb-2">Total invoice: <strong>Rp {{ number_format($booking->latestInvoice->total_bayar, 0, ',', '.') }}</strong></p>
                        @forelse ($booking->latestInvoice->buktiPembayaran as $bukti)
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="small text-steel">Bukti #{{ $bukti->bukti_id }} - {{ str_replace('_', ' ', $bukti->status_konfirmasi) }}</div>
                                <div class="fw-semibold">Rp {{ number_format($bukti->jumlah_bayar, 0, ',', '.') }}</div>
                                <a href="{{ asset('storage/' . $bukti->file_bukti) }}" target="_blank" class="small">Lihat file</a>
                                @if ($bukti->status_konfirmasi === 'menunggu')
                                    <form method="POST" action="{{ route('admin.payment.verify', $bukti) }}" class="mt-2">
                                        @csrf
                                        <textarea name="catatan_admin" class="form-control form-control-sm mb-2" rows="2" placeholder="Catatan admin"></textarea>
                                        <div class="d-flex gap-2">
                                            <button name="status_konfirmasi" value="dikonfirmasi" class="btn btn-sm btn-success flex-fill">Sah</button>
                                            <button name="status_konfirmasi" value="ditolak" class="btn btn-sm btn-outline-danger flex-fill">Tolak</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-steel mb-0">Belum ada bukti pembayaran.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('admin.booking.operational', $booking) }}" class="bg-white border rounded-3 p-4">
                    @csrf
                    <h2 class="h5 text-navy">Detail Operasional</h2>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">JOA Number</label><input name="joa_number" class="form-control" value="{{ old('joa_number', optional($booking->container->first())->joa_number) }}"></div>
                        <div class="col-md-4"><label class="form-label">No Container</label><input name="no_container" class="form-control" value="{{ old('no_container', optional($booking->container->first())->no_container) }}" required></div>
                        <div class="col-md-4"><label class="form-label">Shipping Line</label><input name="shipping_line" class="form-control" value="{{ old('shipping_line', optional($booking->container->first())->shipping_line) }}"></div>
                        <div class="col-md-4"><label class="form-label">Feeder Vessel</label><input name="feeder_vessel" class="form-control" value="{{ old('feeder_vessel', optional($booking->container->first())->feeder_vessel) }}"></div>
                        <div class="col-md-4"><label class="form-label">Connecting Vessel</label><input name="connecting_vessel" class="form-control" value="{{ old('connecting_vessel', optional($booking->container->first())->connecting_vessel) }}"></div>
                        <div class="col-md-4"><label class="form-label">Destination</label><input name="destination" class="form-control" value="{{ old('destination', optional($booking->container->first())->destination) }}"></div>
                        <div class="col-md-4"><label class="form-label">Stuff Date</label><input type="date" name="stuff_date" class="form-control" value="{{ old('stuff_date', optional(optional($booking->container->first())->stuff_date)->format('Y-m-d')) }}"></div>
                        <div class="col-md-4"><label class="form-label">ETD</label><input type="date" name="etd" class="form-control" value="{{ old('etd', optional(optional($booking->container->first())->etd)->format('Y-m-d')) }}"></div>
                        <div class="col-md-4"><label class="form-label">ETA</label><input type="date" name="eta" class="form-control" value="{{ old('eta', optional(optional($booking->container->first())->eta)->format('Y-m-d')) }}"></div>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">No Surat Jalan</label><input name="no_surat_jalan" class="form-control" value="{{ old('no_surat_jalan', optional($booking->suratJalan->first())->no_surat_jalan) }}" required></div>
                        <div class="col-md-4"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', optional(optional($booking->suratJalan->first())->tanggal)->format('Y-m-d') ?? now()->toDateString()) }}" required></div>
                        <div class="col-md-4"><label class="form-label">Kendaraan</label><input name="kendaraan" class="form-control" value="{{ old('kendaraan', optional($booking->suratJalan->first())->kendaraan) }}"></div>
                        <div class="col-md-4"><label class="form-label">No Polisi</label><input name="nopol_kendaraan" class="form-control" value="{{ old('nopol_kendaraan', optional($booking->suratJalan->first())->nopol_kendaraan) }}"></div>
                        <div class="col-md-4"><label class="form-label">Nama Sopir</label><input name="nama_sopir" class="form-control" value="{{ old('nama_sopir', optional($booking->suratJalan->first())->nama_sopir) }}"></div>
                        <div class="col-md-4"><label class="form-label">Penerima</label><input name="penerima_kepada" class="form-control" value="{{ old('penerima_kepada', optional($booking->suratJalan->first())->penerima_kepada) }}"></div>
                        <div class="col-md-6"><label class="form-label">Lokasi Penerima</label><input name="lokasi_penerima" class="form-control" value="{{ old('lokasi_penerima', optional($booking->suratJalan->first())->lokasi_penerima) }}"></div>
                        <div class="col-md-6"><label class="form-label">Nama Pengirim</label><input name="nama_pengirim" class="form-control" value="{{ old('nama_pengirim', optional($booking->suratJalan->first())->nama_pengirim) }}"></div>
                    </div>
                    <button class="btn btn-accent mt-3" @disabled($booking->status_booking !== 'siap_operasional' && $booking->status_booking !== 'dalam_pengiriman')>Simpan Operasional</button>
                </form>
            </div>
            <div class="col-lg-4">
                <form method="POST" action="{{ route('admin.booking.progress', $booking) }}" class="bg-white border rounded-3 p-4">
                    @csrf
                    <h2 class="h5 text-navy">Update Progress</h2>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status_booking" class="form-select" required>
                            <option value="dalam_pengiriman">Dalam Pengiriman</option>
                            <option value="diterima">Diterima Penerima</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="4" required></textarea>
                    </div>
                    <button class="btn btn-outline-accent w-100">Simpan Progress</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
