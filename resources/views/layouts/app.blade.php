<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS Dashboard - Sidebar & Topbar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" crossorigin="anonymous"></script>
    @stack('styles')
</head>

<body>

    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
            <span class="logo-text">TWINS</span>
        </div>

        <nav class="menu-nav">
            @if (Auth::user()->hasFeature(1))
                <a href="{{ route('dashboard') }}"
                    class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:widget-4-bold-duotone"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            @endif

            @if (Auth::user()->hasFeature(2))
                <a href="{{ url('/products') }}"
                    class="menu-item {{ request()->routeIs('products.*') || request()->is('products*') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                    <span>Manajemen Produk</span>
                </a>
            @endif

            @if (Auth::user()->hasFeature(3))
                <a href="{{ url('/transaksi') }}"
                    class="menu-item {{ request()->is('transaksi') || request()->is('transaksi/*') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                    <span>Transaksi & Diskon</span>
                </a>
            @endif

            @if (Auth::user()->hasFeature(4))
                <a href="{{ route('keuangan.index') }}"
                    class="menu-item {{ request()->is('keuangan*') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:graph-up-bold-duotone"></iconify-icon>
                    <span>Keuangan</span>
                </a>
            @endif

            @if (Auth::user()->hasFeature(5))
                <a href="{{ url('/users') }}" class="menu-item {{ request()->is('users*') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                    <span>Manajemen User</span>
                </a>
            @endif

            @if (Auth::user()->hasFeature(6))
                <a href="{{ url('/outlet') }}" class="menu-item {{ request()->is('outlet*') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:shop-2-bold-duotone"></iconify-icon>
                    <span>Operasional Outlet</span>
                </a>
            @endif

            @if (Auth::user()->hasFeature(7))
                <a href="{{ route('kontak.index') }}"
                    class="menu-item {{ request()->routeIs('kontak.*') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:phone-calling-bold-duotone"></iconify-icon>
                    <span>Kelola Kontak</span>
                </a>
            @endif

            @if (Auth::user()->hasFeature(8))
                <a href="{{ route('keuangan.transaksi') }}"
                    class="menu-item {{ request()->is('buku-kas*') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                    <span>Buku Kas</span>
                </a>
            @endif

            @if (Auth::user()->hasFeature(9))
                <a href="{{ route('laporan.index') }}"
                    class="menu-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                    <span>Laporan</span>
                </a>
            @endif

            @if (Auth::user()->hasFeature(10))
                <a href="{{ route('absensi.index') }}"
                    class="menu-item {{ request()->routeIs('absensi.*') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:calendar-date-bold-duotone"></iconify-icon>
                    <span>Absensi</span>
                </a>
            @endif

            @if (Auth::user()->hasFeature(11))
                <a href="{{ route('perilaku.index') }}"
                    class="menu-item {{ request()->is('perilaku*') ? 'active' : '' }}">
                    <div class="curve-helper"></div>
                    <iconify-icon icon="solar:graph-new-bold-duotone"></iconify-icon>
                    <span>Perilaku</span>
                </a>
            @endif
        </nav>

    </aside>

    <div class="page-container">
        <header class="topbar">
            <div class="topbar-left">
                @if (request()->routeIs('dashboard'))
                    <i id="topbar-icon" data-lucide="layout-grid"></i>
                    <h2 id="topbar-title">Dashboard</h2>
                @elseif(request()->is('products*'))
                    <i id="topbar-icon" data-lucide="package"></i>
                    <h2 id="topbar-title">Manajemen Produk</h2>
                @elseif(request()->is('transaksi*'))
                    <i id="topbar-icon" data-lucide="receipt"></i>
                    <h2 id="topbar-title">Transaksi & Diskon</h2>
                @elseif(request()->is('keuangan*'))
                    @php
                        $tab = request('tab', 'cashbox');
                        $icon = 'trending-up';
                        $title = 'Keuangan';
                        if ($tab == 'cashbox') {
                            $icon = 'wallet';
                            $title = 'Cashbox';
                        } elseif ($tab == 'arus-uang') {
                            $icon = 'arrow-left-right';
                            $title = 'Arus Uang';
                        } elseif ($tab == 'pemindahan-saldo') {
                            $icon = 'move';
                            $title = 'Pemindahan Saldo';
                        }
                    @endphp
                    <i id="topbar-icon" data-lucide="{{ $icon }}"></i>
                    <h2 id="topbar-title">{{ $title }}</h2>
                @elseif(request()->is('buku-kas*'))
                    @php
                        $tab = request('tab', 'pengeluaran');
                        $icon = 'trending-up';
                        $title = 'Buku Kas';
                        if ($tab == 'pengeluaran') {
                            $icon = 'round-arrow-left-down';
                            $title = 'Pengeluaran';
                        } elseif ($tab == 'pemasukan') {
                            $icon = 'round-arrow-right-up';
                            $title = 'Pemasukan';
                        } elseif ($tab == 'hutang') {
                            $icon = 'wallet-money';
                            $title = 'Hutang';
                        } elseif ($tab == 'piutang') {
                            $icon = 'hand-money';
                            $title = 'Piutang';
                        }
                    @endphp
                    <i id="topbar-icon" data-lucide="{{ $icon }}"></i>
                    <h2 id="topbar-title">{{ $title }}</h2>
                @elseif(request()->is('outlet*'))
                    <i id="topbar-icon" data-lucide="store"></i>
                    <h2 id="topbar-title">Operasional & Outlet</h2>
                @elseif(request()->is('users*'))
                    <i id="topbar-icon" data-lucide="users"></i>
                    <h2 id="topbar-title">Manajemen User</h2>
                @elseif(request()->routeIs('kontak.*'))
                    <i id="topbar-icon" data-lucide="contact"></i>
                    <h2 id="topbar-title">Kelola Kontak</h2>
                @elseif(request()->is('buku-kas*'))
                    <i id="topbar-icon" data-lucide="wallet"></i>
                    <h2 id="topbar-title">Buku Kas</h2>
                @elseif(request()->routeIs('laporan.*'))
                    <i id="topbar-icon" data-lucide="file-text"></i>
                    <h2 id="topbar-title">Laporan Keseluruhan</h2>
                @elseif(request()->routeIs('absensi.*'))
                    <i id="topbar-icon" data-lucide="calendar-check"></i>
                    <h2 id="topbar-title">Kelola Jadwal & Absensi</h2>
                @elseif(request()->is('perilaku*'))
                    <i id="topbar-icon" data-lucide="bar-chart-3"></i>
                    <h2 id="topbar-title">Perilaku Customer & Produk</h2>
                @else
                    <i id="topbar-icon" data-lucide="layers"></i>
                    <h2 id="topbar-title">Web Twins</h2>
                @endif
            </div>

            <div class="topbar-right">
                <div class="topbar-center">
                    <div class="greeting" id="greeting-text">Selamat Pagi</div>
                    <div class="datetime">
                        <span id="date-text"></span><br>
                        <span class="time-bold" id="time-text"></span>
                    </div>
                </div>
                <div class="user-profile">
                    <iconify-icon icon="solar:user-circle-bold-duotone"></iconify-icon>
                    <span>{{ Auth::user()->name }}</span>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>

                <button class="btn-logout"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <iconify-icon icon="solar:logout-3-bold-duotone"></iconify-icon>
                    <span>Logout</span>
                </button>
            </div>
        </header>


        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();

        function updateDateTime() {
            const now = new Date();
            const optionsDate = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const dateStr = now.toLocaleDateString('id-ID', optionsDate);
            const timeStr = now.toLocaleTimeString('id-ID', {
                hour12: false
            });

            const dateEl = document.getElementById('date-text');
            const timeEl = document.getElementById('time-text');
            const greetEl = document.getElementById('greeting-text');

            if (dateEl) dateEl.innerText = dateStr;
            if (timeEl) timeEl.innerText = timeStr;

            const hour = now.getHours();
            let greeting = "Selamat Malam";
            if (hour < 11) greeting = "Selamat Pagi";
            else if (hour < 15) greeting = "Selamat Siang";
            else if (hour < 19) greeting = "Selamat Sore";
            if (greetEl) greetEl.innerText = greeting;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();

        function setActive(element, title, iconName) {
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            element.classList.add('active');

            const titleEl = document.getElementById('topbar-title');
            const topIcon = document.getElementById('topbar-icon');

            if (titleEl) titleEl.innerText = title;
            if (topIcon) {
                topIcon.setAttribute('data-lucide', iconName);
                lucide.createIcons();
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonColor: '#0477bf',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    title: 'Oops!',
                    text: "{{ session('error') }}",
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            @endif
        });
    </script>
    @stack('scripts')
</body>

</html>
