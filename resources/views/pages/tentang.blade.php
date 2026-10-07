@extends('layouts.app')

@section('title', 'Tentang Kami')

@section('content')

    <section class="py-5" style="background: var(--navy);">
        <div class="container py-4 text-center text-white">
            <h1 class="fw-bold mb-2">Tentang Kami</h1>
            <p style="color:#AEC0CC;" class="mb-0">Mengenal lebih dekat {{ $companyProfile->nama_perusahaan ?? 'PT Janur Tangguh Abadi' }}</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container py-3">
            <div class="row g-5">
                <div class="col-lg-8">
                    <h4 class="text-navy mb-3">Sejarah Perusahaan</h4>
                    <p class="text-steel">
                        {{ $companyProfile->sejarah ?? 'Sejarah perusahaan akan segera dilengkapi.' }}
                    </p>

                    <h4 class="text-navy mb-3 mt-4">Visi</h4>
                    <p class="text-steel">
                        {{ $companyProfile->visi ?? 'Visi perusahaan akan segera dilengkapi.' }}
                    </p>

                    <h4 class="text-navy mb-3 mt-4">Misi</h4>
                    <p class="text-steel">
                        {{ $companyProfile->misi ?? 'Misi perusahaan akan segera dilengkapi.' }}
                    </p>
                </div>
                <h4 class="text-navy mb-3 mt-4">Layanan Kami</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="text-navy mb-2"><i class="bi bi-ship"></i> Freight Forwarding Reguler</h6>
                            <p class="text-steel small mb-0">
                                Pengurusan dan pengiriman barang melalui jalur pelayaran, domestik maupun
                                internasional, termasuk kepabeanan dan transportasi darat antar pulau.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="text-navy mb-2"><i class="bi bi-crane"></i> Project Shipment</h6>
                            <p class="text-steel small mb-0">
                                Charter LCT, tug, dan barge untuk pengiriman proyek berskala besar,
                                termasuk heavy lift shipment dan studi kelayakan jalur pengiriman.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="p-4 rounded-3 section-soft">
                        <h5 class="text-navy mb-3">Informasi Kontak</h5>
                        <p class="small mb-2">
                            <i class="bi bi-geo-alt text-accent"></i>
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

@endsection
