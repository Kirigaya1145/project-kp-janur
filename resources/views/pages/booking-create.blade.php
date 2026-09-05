@extends('layouts.app')

@section('title', 'Booking Pengiriman')

@section('content')
<section class="section-soft py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 text-navy mb-1">Booking Pengiriman Laut</h1>
                <p class="text-steel mb-0">Pilih rute, isi jadwal, dan masukkan daftar barang untuk mendapatkan estimasi awal.</p>
            </div>
            <a href="{{ route('booking.riwayat') }}" class="btn btn-outline-accent"><i class="bi bi-clock-history me-1"></i> Riwayat</a>
        </div>

        @if ($rutes->isEmpty())
            <div class="alert alert-warning">Belum ada data rute. Admin perlu menambahkan rute dan harga dasar terlebih dahulu.</div>
        @endif

        <form method="POST" action="{{ route('booking.store') }}" class="bg-white border rounded-3 p-4">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nama Customer</label>
                    <input type="text" name="nama_customer" class="form-control @error('nama_customer') is-invalid @enderror" value="{{ old('nama_customer', Auth::user()->nama ?? '') }}" required>
                    @error('nama_customer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email_customer" class="form-control @error('email_customer') is-invalid @enderror" value="{{ old('email_customer', Auth::user()->email ?? '') }}">
                    @error('email_customer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp_customer" class="form-control @error('no_hp_customer') is-invalid @enderror" value="{{ old('no_hp_customer', Auth::user()->no_hp ?? '') }}" required>
                    @error('no_hp_customer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rute Pengiriman</label>
                    <select name="rute_id" id="rute_id" class="form-select @error('rute_id') is-invalid @enderror" required>
                        <option value="">Pilih rute</option>
                        @foreach ($rutes as $rute)
                            <option value="{{ $rute->rute_id }}" data-harga="{{ $rute->harga_dasar }}" @selected(old('rute_id') == $rute->rute_id)>
                                {{ $rute->pelabuhan_asal }} - {{ $rute->pelabuhan_tujuan }} (Rp {{ number_format($rute->harga_dasar, 0, ',', '.') }}/kg)
                            </option>
                        @endforeach
                    </select>
                    @error('rute_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Pengiriman</label>
                    <input type="date" name="tanggal_pengiriman" class="form-control @error('tanggal_pengiriman') is-invalid @enderror" value="{{ old('tanggal_pengiriman') }}" required>
                    @error('tanggal_pengiriman')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jumlah Container</label>
                    <input type="number" name="jumlah_container" class="form-control @error('jumlah_container') is-invalid @enderror" value="{{ old('jumlah_container', 1) }}" min="1" required>
                    @error('jumlah_container')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 text-navy mb-0">Daftar Barang</h2>
                <button type="button" class="btn btn-sm btn-outline-accent" id="addItem"><i class="bi bi-plus-lg me-1"></i> Tambah Barang</button>
            </div>

            <div id="items" class="vstack gap-3">
                <div class="border rounded-3 p-3 item-row">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" name="barang[0][nama_barang]" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="barang[0][kategori_barang]" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Qty</label>
                            <input type="number" name="barang[0][qty]" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Berat Kg</label>
                            <input type="number" step="0.01" min="0" name="barang[0][berat_kg]" class="form-control berat-input" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="barang[0][keterangan]" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-end mt-4">
                <div class="col-lg-4">
                    <div class="section-soft rounded-3 p-3">
                        <div class="d-flex justify-content-between small mb-2"><span>Total Berat</span><strong id="totalBerat">0 kg</strong></div>
                        <div class="d-flex justify-content-between small mb-2"><span>Harga Dasar</span><strong id="hargaDasar">Rp 0/kg</strong></div>
                        <div class="d-flex justify-content-between"><span>Estimasi Awal</span><strong id="estimasi">Rp 0</strong></div>
                        <p class="small text-steel mt-2 mb-0">Harga final tetap menunggu review staff/admin.</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-accent px-4" @disabled($rutes->isEmpty())><i class="bi bi-send me-1"></i> Ajukan Booking</button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    let itemIndex = 1;
    const items = document.getElementById('items');
    const rupiah = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);

    function recalc() {
        const route = document.getElementById('rute_id');
        const harga = Number(route.options[route.selectedIndex]?.dataset.harga || 0);
        let total = 0;
        document.querySelectorAll('.berat-input').forEach((input) => total += Number(input.value || 0));
        document.getElementById('totalBerat').textContent = `${total.toLocaleString('id-ID')} kg`;
        document.getElementById('hargaDasar').textContent = `${rupiah(harga)}/kg`;
        document.getElementById('estimasi').textContent = rupiah(total * harga);
    }

    document.getElementById('addItem').addEventListener('click', () => {
        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded-3 p-3 item-row';
        wrapper.innerHTML = `
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Nama Barang</label><input type="text" name="barang[${itemIndex}][nama_barang]" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label">Kategori</label><input type="text" name="barang[${itemIndex}][kategori_barang]" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Qty</label><input type="number" name="barang[${itemIndex}][qty]" class="form-control" value="1" min="1" required></div>
                <div class="col-md-2"><label class="form-label">Berat Kg</label><input type="number" step="0.01" min="0" name="barang[${itemIndex}][berat_kg]" class="form-control berat-input" value="0"></div>
                <div class="col-md-2"><label class="form-label">Keterangan</label><input type="text" name="barang[${itemIndex}][keterangan]" class="form-control"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-outline-danger w-100 remove-item"><i class="bi bi-trash"></i></button></div>
            </div>`;
        items.appendChild(wrapper);
        itemIndex++;
    });

    document.addEventListener('input', (event) => {
        if (event.target.matches('.berat-input, #rute_id')) recalc();
    });
    document.addEventListener('change', (event) => {
        if (event.target.matches('#rute_id')) recalc();
    });
    document.addEventListener('click', (event) => {
        if (event.target.closest('.remove-item')) {
            event.target.closest('.item-row').remove();
            recalc();
        }
    });
    recalc();
</script>
@endpush
