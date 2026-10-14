<nav class="navbar navbar-expand-xl navbar-janur sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <img src="{{ asset($companyProfile->logo ?? 'images/logo-janur-nobg.png') }}" alt="Logo PT Janur Tangguh Abadi" style="height: 42px; width: auto;">
            <span class="brand-name">PT Janur Tangguh Abadi</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Buka menu navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('tentang') ? 'active' : '' }}" href="{{ url('/tentang') }}">Tentang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('booking') ? 'active' : '' }}" href="{{ url('/booking') }}">Booking</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('booking/cek*') ? 'active' : '' }}" href="{{ url('/booking/cek') }}">Cek Booking</a>
                </li>

                @guest
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <a class="btn btn-outline-light btn-sm me-2" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-accent btn-sm" href="{{ route('register') }}">Daftar</a>
                    </li>
                @else
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0 dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->nama }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if (in_array(Auth::user()->role, ['admin', 'staff']))
                                <li><a class="dropdown-item" href="{{ url('/admin/dashboard') }}">Dashboard Admin</a></li>
                            @else
                                <li><a class="dropdown-item" href="{{ url('/booking/riwayat') }}">Riwayat Booking</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Edit Profil</a></li>
                            @if(Auth::user()->role==='admin')
                            <li><a class="dropdown-item" href="{{ route('admin.accounts.index') }}">Kelola Akun</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
