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
                    <label class="form-label">Nama Customer / Nama untuk Booking</label>
                    <div class="form-check mb-2"><input id="atas_nama_lain" class="form-check-input" type="checkbox" name="atas_nama_lain" value="1" @checked(old('atas_nama_lain'))><label class="form-check-label" for="atas_nama_lain">Booking atas nama orang lain</label></div>
                    <small class="text-steel">Isi identitas pihak yang diwakili jika booking atas nama orang lain.</small>
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
                            <option value="{{ $rute->rute_id }}" data-harga20="{{ $rute->tarifs->firstWhere('ukuran_feet',20)?->harga }}" data-harga40="{{ $rute->tarifs->firstWhere('ukuran_feet',40)?->harga }}" @selected(old('rute_id') == $rute->rute_id)>
                                {{ $rute->pelabuhan_asal }} - {{ $rute->pelabuhan_tujuan }}
                            </option>
                        @endforeach
                    </select>
                    @error('rute_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Pengiriman</label>
                    <input type="date" min="{{ today()->toDateString() }}" name="tanggal_pengiriman" class="form-control @error('tanggal_pengiriman') is-invalid @enderror" value="{{ old('tanggal_pengiriman') }}" required>
                    @error('tanggal_pengiriman')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ukuran Kontainer</label>
                    <select id="ukuran_container" name="ukuran_container" class="form-select mb-3" required>
                        <option value="20" @selected(old('ukuran_container',20)==20)>20 feet</option>
                        <option value="40" @selected(old('ukuran_container')==40)>40 feet</option>
                    </select>
                    <label class="form-label">Jumlah Kontainer Penuh</label>
                    <input type="number" id="jumlah_container" name="jumlah_container" class="form-control @error('jumlah_container') is-invalid @enderror" value="{{ old('jumlah_container', 1) }}" min="1" required>
                    @error('jumlah_container')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 text-navy mb-0">Daftar Barang</h2>
                <button type="button" class="btn btn-sm btn-outline-accent" id="addItem"><i class="bi bi-plus-lg me-1"></i> Tambah Barang</button>
            </div>

            <div id="items" class="vstack gap-3">
                @foreach(old('barang',[['nama_barang'=>'','kategori_barang'=>'','qty'=>1,'berat_kg'=>0,'keterangan'=>'']]) as $index=>$item)
                <div class="border rounded-3 p-3 item-row"><div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Commodity / Nama Barang</label><input name="barang[{{ $index }}][nama_barang]" class="form-control" value="{{ $item['nama_barang'] ?? '' }}" maxlength="150" required></div>
                    <div class="col-md-2"><label class="form-label">Kategori</label><select name="barang[{{ $index }}][kategori_barang]" class="form-select" required><option value="">Pilih kategori</option>
                    @foreach(['Dry'=>'Dry — barang kering','Reefer'=>'Reefer — makanan','Liquid'=>'Liquid — cairan'] as $value=>$label)<option value="{{ $value }}" @selected(($item['kategori_barang'] ?? '')===$value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-2"><label class="form-label">Qty Barang</label><input type="number" min="1" name="barang[{{ $index }}][qty]" class="form-control" value="{{ $item['qty'] ?? 1 }}" required></div>
                    <div class="col-md-2"><label class="form-label">Berat Kg</label><input type="number" min="0" step="0.01" name="barang[{{ $index }}][berat_kg]" class="form-control" value="{{ $item['berat_kg'] ?? 0 }}"></div>
                    <div class="col-md-2"><label class="form-label">Keterangan</label><input name="barang[{{ $index }}][keterangan]" class="form-control" value="{{ $item['keterangan'] ?? '' }}"></div>
                    <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-outline-danger remove-item" aria-label="Hapus barang"><span>Hapus</span></button></div>
                </div></div>
                @endforeach
            </div>

            <div class="row justify-content-end mt-4">
                <div class="col-lg-4">
                    <div class="section-soft rounded-3 p-3">
                        <div class="d-flex justify-content-between small mb-2"><span>Jumlah Container</span><strong id="jumlahContainerText">1</strong></div>
                        <div class="d-flex justify-content-between small mb-2"><span>Harga per Container</span><strong id="hargaDasar">Rp 0</strong></div>
                        <div class="d-flex justify-content-between"><span>Estimasi Awal</span><strong id="estimasi">Rp 0</strong></div>
                        <p class="small text-steel mt-2 mb-0">Harga final tetap menunggu review staff/admin.</p>
                    </div>
                </div>
            </div>

            <div id="tarifNotice" class="alert alert-warning mt-3">Pilih rute dan ukuran yang memiliki tarif. Hubungi admin jika tarif belum tersedia.</div>
            <div class="mt-4 text-end">
                <button id="submitBooking" type="submit" class="btn btn-accent px-4" @disabled($rutes->isEmpty())><i class="bi bi-send me-1"></i> Ajukan Booking</button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const bookingName=document.querySelector('[name="nama_customer"]');
    const bookingEmail=document.querySelector('[name="email_customer"]');
    const bookingPhone=document.querySelector('[name="no_hp_customer"]');
    const selfIdentity={name:@json(Auth::user()->nama),email:@json(Auth::user()->email),phone:@json(Auth::user()->no_hp ?? '')};
    document.getElementById('atas_nama_lain').addEventListener('change',event=>{
        bookingName.value=event.target.checked?'':selfIdentity.name;
        bookingEmail.value=event.target.checked?'':selfIdentity.email;
        bookingPhone.value=event.target.checked?'':selfIdentity.phone;
        bookingName.focus();
    });
    let itemIndex = @json(max(array_keys(old('barang',[0=>[]])))+1);
    const items = document.getElementById('items');
    const rupiah = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);

    function recalc() {
        const route = document.getElementById('rute_id');
        const size=document.getElementById('ukuran_container').value;
        const harga = Number(route.options[route.selectedIndex]?.dataset['harga'+size] || 0);
        document.getElementById('submitBooking').disabled = harga<=0;
        document.getElementById('tarifNotice').hidden = harga>0;
        const jumlah = Number(document.getElementById('jumlah_container').value || 0);
        document.getElementById('jumlahContainerText').textContent = jumlah.toLocaleString('id-ID');
        document.getElementById('hargaDasar').textContent = rupiah(harga);
        document.getElementById('estimasi').textContent = rupiah(jumlah * harga);
    }

    document.getElementById('addItem').addEventListener('click', () => {
        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded-3 p-3 item-row';
        wrapper.innerHTML = `
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Commodity / Nama Barang</label><input type="text" name="barang[${itemIndex}][nama_barang]" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label">Kategori</label><select name="barang[${itemIndex}][kategori_barang]" class="form-select" required><option value="">Pilih kategori</option><option value="Dry">Dry — barang kering</option><option value="Reefer">Reefer — makanan</option><option value="Liquid">Liquid — cairan</option></select></div>
                <div class="col-md-2"><label class="form-label">Qty</label><input type="number" name="barang[${itemIndex}][qty]" class="form-control" value="1" min="1" required></div>
                <div class="col-md-2"><label class="form-label">Berat Kg</label><input type="number" step="0.01" min="0" name="barang[${itemIndex}][berat_kg]" class="form-control berat-input" value="0"></div>
                <div class="col-md-2"><label class="form-label">Keterangan</label><input type="text" name="barang[${itemIndex}][keterangan]" class="form-control"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-outline-danger w-100 remove-item"><span>Hapus</span></button></div>
            </div>`;
        items.appendChild(wrapper);
        itemIndex++;
    });

    document.addEventListener('input', (event) => {
        if (event.target.matches('#jumlah_container, #rute_id, #ukuran_container')) recalc();
    });
    document.addEventListener('change', (event) => {
        if (event.target.matches('#rute_id, #ukuran_container')) recalc();
    });
    document.addEventListener('click', (event) => {
        if (event.target.closest('.remove-item') && items.querySelectorAll('.item-row').length>1) {
            event.target.closest('.item-row').remove();
            recalc();
        }
    });
    recalc();
</script>
@endpush
