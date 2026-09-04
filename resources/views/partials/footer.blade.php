<footer class="site-footer pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="text-white brand-font mb-3">PT Janur Tangguh Abadi</h5>
                <p class="small mb-0">
                    {{ $companyProfile->alamat ?? 'Jl. Ikan Sepat IV No. 26, Tanjung Perak, Surabaya 60177' }}
                </p>
            </div>
            <div class="col-lg-4">
                <h6 class="text-white mb-3">Kontak</h6>
                <p class="small mb-1">
                    <i class="bi bi-envelope"></i>
                    {{ $companyProfile->email ?? 'info@janurtangguhabadi.com' }}
                </p>
                <p class="small mb-0">
                    <i class="bi bi-telephone"></i>
                    {{ $companyProfile->telepon ?? '+62 31 9901 8632' }}
                </p>
            </div>
            <div class="col-lg-4">
                <h6 class="text-white mb-3">Tautan</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1"><a href="{{ url('/booking') }}">Ajukan Booking</a></li>
                    <li class="mb-1"><a href="{{ url('/booking/cek') }}">Cek Status Booking</a></li>
                    <li class="mb-1"><a href="{{ url('/tentang') }}">Tentang Kami</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4" style="border-color: rgba(255,255,255,.1)">
        <p class="small text-center mb-0" style="color:#7C8A93;">
            &copy; {{ date('Y') }} PT Janur Tangguh Abadi. Seluruh hak cipta dilindungi.
        </p>
    </div>
</footer>
