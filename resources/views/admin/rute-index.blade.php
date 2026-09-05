@extends('layouts.app')

@section('title', 'Kelola Rute')

@section('content')
<section class="section-soft py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 text-navy mb-1">Rute dan Harga Dasar</h1>
                <p class="text-steel mb-0">Harga dasar per kilogram dipakai untuk estimasi awal booking customer.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <form method="POST" action="{{ route('admin.rute.store') }}" class="bg-white border rounded-3 p-4">
                    @csrf
                    <h2 class="h5 text-navy">Tambah atau Update Rute</h2>
                    <div class="mb-3">
                        <label class="form-label">Pelabuhan Asal</label>
                        <input type="text" name="pelabuhan_asal" class="form-control" value="{{ old('pelabuhan_asal') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pelabuhan Tujuan</label>
                        <input type="text" name="pelabuhan_tujuan" class="form-control" value="{{ old('pelabuhan_tujuan') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Dasar per Kg</label>
                        <input type="number" name="harga_dasar" class="form-control" value="{{ old('harga_dasar') }}" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
                    </div>
                    <button class="btn btn-accent w-100">Simpan Rute</button>
                </form>
            </div>
            <div class="col-lg-8">
                <div class="bg-white border rounded-3 table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Asal</th><th>Tujuan</th><th>Harga Dasar</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            @forelse ($rutes as $rute)
                                <tr>
                                    <td>{{ $rute->pelabuhan_asal }}</td>
                                    <td>{{ $rute->pelabuhan_tujuan }}</td>
                                    <td>Rp {{ number_format($rute->harga_dasar, 0, ',', '.') }}/kg</td>
                                    <td>{{ $rute->keterangan }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-steel py-4">Belum ada rute.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
