@extends('layouts.app')

@section('title', 'Home')

@section('content')

    {{-- HERO --}}
    <section class="text-white" style="background: linear-gradient(160deg, var(--navy) 0%, #123763 100%);">
        <div class="container py-5 py-lg-6">
            <div class="row align-items-center py-4">
                <div class="col-lg-7">
                    <p class="text-uppercase small mb-2" style="color:#8FA6BC; letter-spacing:1px;">
                        Freight Forwarding &middot; Domestik & Internasional
                    </p>
                    <h1 class="display-5 fw-bold mb-3" style="line-height:1.2;">
                        Mengantar Barang Anda,<br>Menjaga Kepercayaan Anda.
                    </h1>
                    <p class="mb-4" style="color:#C9D4DD; max-width: 540px;">
                        {{ $companyProfile->nama_perusahaan ?? 'PT Janur Tangguh Abadi' }} melayani
                        pengurusan dan pengiriman barang dengan proses yang transparan,
                        dari pemesanan hingga barang sampai tujuan.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ url('/booking') }}" class="btn btn-accent btn-lg px-4">Ajukan Booking</a>
                        <a href="{{ url('/booking/cek') }}" class="btn btn-outline-light btn-lg px-4">Cek Status Booking</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PROFIL PERUSAHAAN --}}
    <section class="py-5">
        <div class="container py-4">
            <div class="row g-5 align-items-start">
                <div class="col-lg-7">
                    <p class="text-uppercase small text-accent fw-semibold mb-2" style="letter-spacing:1px;">Profil Perusahaan</p>
                    <h2 class="text-navy mb-4">Mengenal {{ $companyProfile->nama_perusahaan ?? 'PT Janur Tangguh Abadi' }}</h2>
                    <p class="text-steel">
                        {{ $companyProfile->sejarah ?? 'Sejarah perusahaan akan segera dilengkapi.' }}
                    </p>

                    <div class="row g-4 mt-2">
                        <div class="col-sm-6">
                            <h6 class="text-navy mb-2"><i class="bi bi-eye me-1"></i> Visi</h6>
                            <p class="text-steel small mb-0">
                                {{ $companyProfile->visi ?? 'Visi perusahaan akan segera dilengkapi.' }}
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <h6 class="text-navy mb-2"><i class="bi bi-flag me-1"></i> Misi</h6>
                            <p class="text-steel small mb-0">
                                {{ $companyProfile->misi ?? 'Misi perusahaan akan segera dilengkapi.' }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ url('/tentang') }}" class="btn btn-outline-accent mt-4">
                        Selengkapnya Tentang Kami
                    </a>
                </div>

                <div class="col-lg-5">
                    <div class="p-4 rounded-3 section-soft h-100">
                        <h6 class="text-navy mb-3">Informasi Kontak</h6>
                        <p class="small mb-2">
                            <i class="bi bi-geo-alt"></i>
                            {{ $companyProfile->alamat ?? '-' }}
                        </p>
                        <p class="small mb-2">
                            <i class="bi bi-envelope"></i>
                            {{ $companyProfile->email ?? '-' }}
                        </p>
                        <p class="small mb-0">
                            <i class="bi bi-telephone"></i>
                            {{ $companyProfile->telepon ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- KEUNGGULAN --}}
    <section class="py-5 section-soft">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="p-4 h-100 border rounded-3 bg-white">
                        <div class="mb-3 fs-2 text-navy"><i class="bi bi-box-seam"></i></div>
                        <h5 class="mb-2">Pengurusan Terpadu</h5>
                        <p class="text-steel small mb-0">
                            Dari dokumen, pengemasan, hingga pengiriman ditangani dalam satu proses.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 h-100 border rounded-3">
                        <div class="mb-3 fs-2 text-navy"><i class="bi bi-geo-alt"></i></div>
                        <h5 class="mb-2">Jangkauan Domestik & Internasional</h5>
                        <p class="text-steel small mb-0">
                            Melayani pengiriman antar pulau maupun lintas negara.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 h-100 border rounded-3">
                        <div class="mb-3 fs-2 text-navy"><i class="bi bi-clipboard-check"></i></div>
                        <h5 class="mb-2">Status Transparan</h5>
                        <p class="text-steel small mb-0">
                            Pantau status booking Anda kapan saja lewat kode booking.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA STRIP --}}
    <section class="section-soft py-5">
        <div class="container py-3 text-center">
            <h3 class="text-navy mb-2">Siap mengirim barang Anda?</h3>
            <p class="text-steel mb-4">Isi formulir booking, tim kami akan menghubungi Anda untuk penawaran harga.</p>
            <a href="{{ url('/booking') }}" class="btn btn-accent btn-lg px-5">Mulai Booking</a>
        </div>
    </section>

@endsection
