<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="session-success" content="{{ session('success') ?? '' }}">
    <meta name="session-error" content="{{ session('error') ?? '' }}">
    <meta name="auth-check" content="{{ auth()->check() ? 'true' : 'false' }}">
    <meta name="login-url" content="{{ route('login') }}">
    <meta name="outlet-address" content="{{ $outlet->alamat ?? 'Alamat outlet belum tersedia' }}">
    <meta name="store-hours" content="{{ $outlet->jam_buka ?? '' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="outlet-uuid" content="{{ $outlet->uuid }}">
    <meta name="delivery-address-store-url"
        content="{{ route('user.delivery-address.store', ['id' => $outlet->uuid]) }}">
    <meta name="checkout-token-url" content="{{ route('user.checkout.token', ['id' => $outlet->uuid]) }}">
    <meta name="user-history-url" content="{{ route('user.history.api', ['id' => $outlet->uuid]) }}">
    <meta name="sync-payment-url" content="{{ route('user.payment.sync', ['id' => $outlet->uuid]) }}">
    <meta name="midtrans-enabled"
        content="{{ config('services.midtrans.client_key') && config('services.midtrans.server_key') ? 'true' : 'false' }}">
    <meta name="persisted-delivery-preference" content="{{ json_encode($deliveryPreference ?? null) }}">
    <meta name="user-name" content="{{ optional(auth()->user())->name ?? '' }}">
    <meta name="user-phone" content="{{ optional(auth()->user())->no_hp ?? '' }}">
    <title>TWINS - Food Delivery Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
    @if (config('services.midtrans.client_key'))
        <script
            src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @endif

    <style>
        /* Address Popup Premium Styling */
        .address-popup-wrap {
            text-align: left;
            padding: 5px;
        }

        .address-popup-layout {
            display: flex;
            gap: 24px;
            flex-direction: row;
        }

        .address-popup-left {
            flex: 1;
            min-width: 360px;
        }

        .address-popup-right {
            flex: 1.2;
            min-width: 0;
        }

        @media (max-width: 768px) {
            .address-popup-layout {
                flex-direction: column;
                gap: 20px;
            }

            .address-popup-left,
            .address-popup-right {
                width: 100%;
                min-width: 0;
            }
        }

        .address-popup-left label {
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
            color: var(--sub-text);
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .address-popup-left input,
        .address-popup-left textarea {
            width: 100%;
            border: 1px solid var(--card-border) !important;
            border-radius: 12px !important;
            padding: 12px 15px !important;
            background: rgba(255, 255, 255, 0.03) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: var(--text-color) !important;
            font-size: 14px !important;
            transition: all 0.3s ease !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .address-popup-left input:focus,
        .address-popup-left textarea:focus {
            border-color: var(--accent-purple) !important;
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.2), inset 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            background: rgba(255, 255, 255, 0.07) !important;
            transform: translateY(-1px);
        }

        .route-tracking-card {
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 15px;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(14, 165, 233, 0.03));
            backdrop-filter: blur(10px);
            margin-bottom: 20px;
            border-left: 5px solid var(--accent-purple);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        #addressMapCanvas {
            width: 100%;
            height: 380px;
            border-radius: 18px;
            border: 1px solid var(--card-border);
            box-shadow: var(--glow);
            overflow: hidden;
        }

        .swal2-popup.address-modal-custom {
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            border-radius: 32px !important;
            background: rgba(15, 23, 42, 0.6) !important;
        }

        /* Global SweetAlert2 Z-Index & Glassmorphism */
        .swal2-container {
            z-index: 10000 !important;
        }

        .swal2-popup {
            backdrop-filter: blur(15px) !important;
            -webkit-backdrop-filter: blur(15px) !important;
            border-radius: 32px !important;
            background: rgba(15, 23, 42, 0.75) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4) !important;
            will-change: transform, opacity;
        }

        [data-theme="light"] .swal2-popup {
            background: rgba(255, 255, 255, 0.7) !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            color: #1e293b !important;
        }

        .premium-swal-success {
            border: 1px solid rgba(16, 185, 129, 0.2) !important;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(6, 78, 59, 0.4)) !important;
        }

        .swal2-success-circular-line-left,
        .swal2-success-circular-line-right,
        .swal2-success-fix {
            background-color: transparent !important;
        }

        .swal2-title,
        .swal2-html-container {
            color: var(--text-color) !important;
        }

        @keyframes swalPremiumIn {
            from { transform: scale(0.85); opacity: 0; filter: blur(4px); }
            to { transform: scale(1); opacity: 1; filter: blur(0); }
        }

        @keyframes swalPremiumOut {
            from { transform: scale(1); opacity: 1; filter: blur(0); }
            to { transform: scale(0.95); opacity: 0; filter: blur(4px); }
        }

        .premium-swal-show {
            animation: swalPremiumIn 0.3s cubic-bezier(0.19, 1, 0.22, 1) forwards !important;
        }

        .premium-swal-hide {
            animation: swalPremiumOut 0.2s cubic-bezier(0.19, 1, 0.22, 1) forwards !important;
        }

            .stars-gold {
                color: #f59e0b;
                display: flex;
                gap: 2px;
            }

                .back-btn-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 12px;
                background: rgba(255, 255, 255, 0.05);
                color: var(--text-color);
                margin-right: 15px;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1px solid var(--card-border);
                text-decoration: none;
            }

            .back-btn-icon:hover {
                background: var(--accent-purple);
                color: white !important;
                transform: translateX(-3px);
                box-shadow: var(--glow);
                border-color: transparent;
            }

            /* Full Width Force */
        html, body {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden !important;
            position: relative;
        }

        #mainHeader {
            width: 100% !important;
            max-width: 100% !important;
            left: 0 !important;
            right: 0 !important;
            margin: 0 !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
            padding: 0 15px !important;
        }

        .container {
            width: 100% !important;
            max-width: 100% !important;
            padding: 110px 4% 30px !important;
            margin: 0 !important;
        }

        @media (max-width: 768px) {
            .container {
                padding: 90px 20px 30px !important;
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .main-content {
                padding: 0 !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .promo-banner {
                padding: 25px 20px !important;
                border-radius: 20px !important;
                margin: 0 0 25px 0 !important;
                min-height: auto !important;
                width: 100% !important;
                box-sizing: border-box !important;
                display: block !important;
            }
            .promo-banner h1 {
                font-size: 1.4rem !important;
                line-height: 1.2 !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
                margin: 10px 0 !important;
            }
            .promo-banner p {
                font-size: 0.8rem !important;
                white-space: normal !important;
                margin-bottom: 15px !important;
            }
            .cart-panel {
                width: 100% !important;
                border-radius: 0 !important;
                border-top-left-radius: 20px !important;
                border-top-right-radius: 20px !important;
            }
        }
    </style>
</head>
<script type="application/json" id="products-data">
    {!! json_encode($products) !!}
</script>

<body id="body">
    <div class="animated-bg"></div>
    <div class="light-rays-container">
        <div class="god-ray ray1"></div>
        <div class="god-ray ray2"></div>
        <div class="god-ray ray3"></div>
        <div class="god-ray ray4"></div>
    </div>
    <header id="mainHeader">
        <div class="logo">
            <a href="{{ route('home') }}" class="back-btn-icon" title="Kembali ke Daftar Outlet">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
            <span class="logo-text">TWINS</span>
        </div>

        <nav class="main-nav" id="mainNav">
            <a class="nav-link active" id="nav-home" onclick="switchPage('home')">Beranda</a>
            <a class="nav-link" id="nav-cat" onclick="scrollToCategory()">Kategori</a>
            <a class="nav-link" id="nav-history" onclick="switchPage('history')">Riwayat</a>
            <a class="nav-link" id="nav-chat" onclick="goToWhatsApp()">Chat</a>
        </nav>
        <div class="nav-btns">
            @auth
                <div class="user-premium-card desktop-only">
                    <span class="user-name-text">{{ Auth::user()->name }}</span>
                    
                    @if(auth()->user()->role === 'owner' || auth()->user()->role === 'kepala_toko')
                        <a href="/dashboard" class="nav-action-btn" title="Dashboard">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" id="logout-form-card" style="display: none;">
                        @csrf
                    </form>
                    <button type="button" class="nav-action-btn logout-btn" title="Logout" onclick="document.getElementById('logout-form-card').submit();">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>
                </div>

                <div class="mobile-user-drop mobile-only">
                    <button class="user-icon-btn" onclick="toggleUserMenu()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>
                    <div class="user-dropdown-menu" id="userMenu">
                        <div class="user-menu-header" style="padding: 12px 16px; border-bottom: 1px solid var(--card-border); margin-bottom: 5px;">
                            <span style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-color);">{{ Auth::user()->name }}</span>
                            <span style="display: block; font-size: 0.75rem; color: var(--sub-text);">{{ Auth::user()->email }}</span>
                        </div>
                        @if(auth()->user()->role === 'owner' || auth()->user()->role === 'kepala_toko')
                            <button onclick="location.href='/dashboard'">Dashboard</button>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" style="display: none;" id="logout-form-user-page-mob">
                            @csrf
                        </form>
                        <button onclick="document.getElementById('logout-form-user-page-mob').submit();" style="display: flex; align-items: center; color: #ef4444;">
                            Logout
                        </button>
                    </div>
                </div>
            @else
                <div class="mobile-user-drop">
                    <button class="user-icon-btn" onclick="toggleUserMenu()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>
                    <div class="user-dropdown-menu" id="userMenu">
                        <button onclick="location.href='/login'">Login</button>
                        <button onclick="location.href='/register'">Register</button>
                    </div>
                </div>
            @endauth

            <div class="theme-dropdown">
                <button class="theme-btn" onclick="toggleThemeMenu()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                    Tema
                </button>
                <div class="theme-dropdown-content" id="themeMenu">
                    <button onclick="setTheme('dark')" data-theme-val="dark">🌙 Dark</button>
                    <button onclick="setTheme('light')" data-theme-val="light">☀️ Light</button>
                    <button onclick="setTheme('twins')" data-theme-val="twins">🏮 Twins (Red)</button>
                    <button onclick="setTheme('neon')" data-theme-val="neon">🟣 Neon</button>
                    <button onclick="setTheme('ocean')" data-theme-val="ocean">🌊 Ocean</button>
                    <button onclick="setTheme('forest')" data-theme-val="forest">🍂 Autumn (Orange)</button>
                </div>
            </div>

            @guest
                <a href="{{ route('login') }}" class="btn-outline desktop-only" style="text-decoration: none;">Login</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-fill desktop-only" style="text-decoration: none;">Register</a>
                @endif
            @endguest
        </div>
    </header>


    <div class="mobile-cart-fab" id="mobileCartBtn" onclick="toggleBottomSheet()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <div class="cart-badge" id="cartBadge">0</div>
    </div>

    <div class="sheet-overlay" id="sheetOverlay" onclick="toggleBottomSheet()"></div>
    <div class="bottom-sheet" id="bottomSheet">
        <div class="handle"></div>
        <div id="mobileSheetContent" style="padding: 0 15px 30px 15px;">
            <!-- Pre-populated for mobile to avoid innerHTML copy issues -->
            <div class="white-card hidden address-section"
                style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 style="font-size: 0.95rem;">Delivery Address</h4>
                    <a href="#" onclick="openAddressPopup(event)"
                        style="color: var(--orange-brand); font-size: 0.75rem; text-decoration: none;">Change</a>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 10px;">
                    <span style="font-size: 1.2rem;">📍</span>
                    <div style="flex: 1;">
                        <p class="delivery-address-value" style="font-size: 0.85rem; font-weight: 600;">-</p>
                        <p class="delivery-address-note"
                            style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4;">Alamat pengiriman
                            default Anda.</p>
                        <p class="delivery-contact-note"
                            style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4; margin-top: 4px;">
                            Penerima: - | No HP: -</p>
                    </div>
                </div>
            </div>

            <div class="white-card hidden discount-section"
                style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                <h4 style="margin-bottom: 12px; font-size: 0.9rem;">Promo Code</h4>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="promoInputMobile" placeholder="TWINS20"
                        style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--card-border); background: rgba(255,255,255,0.05); color: var(--text-color); font-size: 0.8rem;">
                    <button onclick="applyPromo('mobile')"
                        style="background: var(--orange-brand); color: white; border: none; padding: 0 15px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.8rem;">Apply</button>
                </div>
                <p class="promoMessage" style="font-size: 0.7rem; margin-top: 8px; display: none;"></p>
            </div>

            <div class="white-card hidden order-section"
                style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 12px; border-radius: 15px; margin-bottom: 15px;">
                <h4 style="margin-bottom: 12px; font-size: 0.85rem;">Order Menu</h4>
                <div class="cart-items-container"></div>
                <hr style="border: 0; border-top: 1px solid var(--card-border); margin: 12px 0;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600; font-size: 0.8rem;">Total</span>
                        <span class="totalPriceDisplay"
                            style="font-size: 0.8rem; font-weight: 700; color: var(--orange-brand);"><span
                                style="font-size: 0.8em;">Rp</span> 0</span>
                    </div>
                </div>
                <button class="btn-fill checkout-btn" onclick="checkout()"
                    style="width: 100%; margin-top: 12px; padding: 10px; font-size: 0.9rem;">Checkout</button>
            </div>
        </div>
    </div>

    <div class="container" id=
    "mainContainer">
        <main class="main-content anim-fade-up" id="homePage">
            <div class="promo-banner float-hover">
                <span class="badge" style="margin-bottom: 10px;">Outlet TWINS</span>
                <h1>{{ $outlet->nama }}</h1>
                <p>📍 {{ $outlet->alamat }}</p>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <span class="badge"
                        style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">🕒
                        {{ $outlet->jam_buka }}</span>
                    <span class="badge"
                        style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">⭐
                        {{ number_format($outlet->rating, 1) }}</span>
                </div>
            </div>

            @if (count($discounts) > 0)
                <div class="discounts-container anim-fade-up" style="margin-top: 30px; max-width: 100%; min-width: 0;">
                    <h3 style="margin-bottom: 20px; font-size: 1.2rem; display: flex; align-items: center; gap: 10px;">
                        <iconify-icon icon="solar:ticket-sale-bold-duotone"
                            style="color: #f59e0b; font-size: 28px;"></iconify-icon>
                        Penawaran Diskon Hari Ini
                    </h3>
                    <div
                        style="display: flex; gap: 15px; overflow-x: auto; overflow-y: visible; padding-bottom: 20px; scrollbar-width: none; -ms-overflow-style: none; align-items: stretch; max-width: 100%; min-width: 0;">
                        @php $shownProducts = []; @endphp
                        @foreach ($discounts as $discount)
                            @foreach ($discount->products as $p)
                                @if (!in_array($p->uuid, $shownProducts))
                                    @php
                                        $shownProducts[] = $p->uuid;
                                        $originalPrice = (int) $p->harga_jual;
                                        $tipeDiskon = $p->pivot->tipe_diskon ?? $discount->tipe;
                                        $nilaiDiskon = (int) ($p->pivot->nilai_diskon ?? $discount->nilai);
                                        $newPrice =
                                            $tipeDiskon == 'persen' || $tipeDiskon == 'Promo'
                                                ? $originalPrice * (1 - $nilaiDiskon / 100)
                                                : $originalPrice - $nilaiDiskon;
                                        if ($newPrice < 0) {
                                            $newPrice = 0;
                                        }
                                    @endphp
                                    @php
                                        $currentStok = $stockMap[$p->uuid] ?? 0;
                                        $isOutOfStock = $currentStok <= 0;
                                    @endphp
                                    <div class="discounted-item-vertical {{ $isOutOfStock ? 'out-of-stock' : '' }}"
                                        style="opacity: {{ $isOutOfStock ? '0.6' : '1' }};">
                                        <div
                                            style="width: 100%; aspect-ratio: 1 / 1; overflow: hidden; background: white; position: relative;">
                                            <img src="{{ \App\Http\Controllers\LandingController::resolveImageUrl($p->image_url) }}"
                                                class="{{ $isOutOfStock ? 'img-out-of-stock' : '' }}"
                                                style="width: 100%; height: 100%; object-fit: cover;">
                                            <div
                                                style="position: absolute; top: 8px; left: 8px; background: #ff4d4d; color: white; padding: 3px 6px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; z-index: 3;">
                                                -{{ $tipeDiskon == 'persen' ? $nilaiDiskon . '%' : 'Rp' . number_format($nilaiDiskon / 1000, 0) . 'k' }}
                                            </div>
                                            @if ($isOutOfStock)
                                                <div
                                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #ef4444; color: white; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; z-index: 4;">
                                                    HABIS</div>
                                            @endif
                                        </div>

                                        <div
                                            style="padding: 10px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                            <h5
                                                class="product-name-discount {{ $isOutOfStock ? 'text-muted-stock' : '' }}">
                                                {{ $p->nama_produk }}
                                            </h5>
                                            <div
                                                style="margin-top: 8px; display: flex; justify-content: space-between; align-items: flex-end;">
                                                <div>
                                                    <div
                                                        style="font-size: 0.7rem; text-decoration: line-through; color: #777; margin-bottom: 2px;">
                                                        Rp{{ number_format($originalPrice, 0, ',', '.') }}
                                                    </div>
                                                    <div
                                                        class="product-new-price-discount {{ $isOutOfStock ? 'text-muted-stock' : '' }}">
                                                        Rp{{ number_format($newPrice, 0, ',', '.') }}
                                                    </div>
                                                </div>

                                                <button
                                                    class="discount-add-btn {{ $isOutOfStock ? 'out-of-stock btn-oos' : 'btn-available' }}"
                                                    data-name="{{ $p->nama_produk }}"
                                                    data-price="{{ $newPrice }}"
                                                    data-stock="{{ $currentStok }}" onclick="addToCartFromEl(this)">
                                                    <svg width="20" height="20" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M12 5V19M5 12H19" stroke="white" stroke-width="3"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endif

            <section id="categorySection" class="search-filter-section">
                <div class="search-row">
                    <div class="search-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="searchInput" placeholder="Cari menu favoritmu..."
                            oninput="handleSearch()">
                    </div>
                    <button class="filter-btn" onclick="toggleFilterPanel()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        Filter & Sort
                    </button>
                </div>

                <!-- Wadah Badge Filter Aktif -->
                <div id="activeFilters" class="active-filters-container"></div>

                <!-- Advanced Filter Panel (Hidden by default) -->
                <div id="filterPanel" class="filter-panel hidden">
                    <div class="filter-content">
                        <div class="filter-section">
                            <h5>Kategori Produk</h5>
                            <div class="category-grid">
                                <label class="check-container">Semua Kategori
                                    <input type="checkbox" id="check-all" checked
                                        onchange="toggleAllCategories(this)">
                                    <span class="checkmark"></span>
                                </label>
                                @foreach ($categories as $category)
                                    <label class="check-container">{{ $category['name'] }}
                                        <input type="checkbox" class="cat-check" value="{{ $category['id'] }}"
                                            data-name="{{ $category['name'] }}">
                                        <span class="checkmark"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                            <div class="filter-section" style="flex: 1; min-width: 250px;">
                                <h5>Urutkan Harga</h5>
                                <select id="priceSort" class="filter-select">
                                    <option value="default">Default</option>
                                    <option value="low-high">Harga: Terendah ke Tertinggi</option>
                                    <option value="high-low">Harga: Tertinggi ke Terendah</option>
                                </select>
                            </div>
                            <div style="padding-bottom: 5px;">
                                <button onclick="applyFilters()" class="btn-fill"
                                    style="padding: 12px 30px; border-radius: 12px;">Terapkan Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="food-grid" id="productGrid"></div>
            </section>

            <!-- STORE REVIEWS SECTION -->
            <section class="reviews-section anim-fade-up">
                <div class="reviews-header">
                    <h3>Ulasan & Rating Toko</h3>
                    <div class="avg-stats">
                        <span class="avg-val">{{ number_format($outlet->rating, 1) }}</span>
                        <div class="stars-gold">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= floor($outlet->rating))
                                    ★
                                @elseif ($i == ceil($outlet->rating) && $outlet->rating - floor($outlet->rating) > 0)
                                    {{-- Bisa pakai ikon half star jika ada, tapi sementara kita pakai bintang penuh/kosong --}}
                                    ★
                                @else
                                    ☆
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Review Form -->
                @auth
                    <div class="review-form-card">
                        <h4>Bagaimana menurutmu tentang toko ini?</h4>
                        <form action="{{ route('store.review.store', $outlet->uuid) }}" method="POST">
                            @csrf
                            <div class="rating-selector">
                                <input type="radio" name="rating" value="5" id="star5"><label
                                    for="star5">★</label>
                                <input type="radio" name="rating" value="4" id="star4"><label
                                    for="star4">★</label>
                                <input type="radio" name="rating" value="3" id="star3"><label
                                    for="star3">★</label>
                                <input type="radio" name="rating" value="2" id="star2"><label
                                    for="star2">★</label>
                                <input type="radio" name="rating" value="1" id="star1" required><label
                                    for="star1">★</label>
                            </div>
                            <textarea name="comment" placeholder="Berikan komentar Anda..." rows="3"></textarea>
                            <button type="submit" class="btn-fill" style="margin-top: 15px; width: 100%;">Kirim
                                Ulasan</button>
                        </form>
                    </div>
                @else
                    <div class="login-prompt-card">
                        <p>Silakan <a href="{{ route('login') }}">Login</a> untuk memberikan ulasan.</p>
                    </div>
                @endauth

                <!-- Reviews List -->
                <div class="reviews-list">
                    @forelse($reviews as $review)
                        <div class="review-item-card">
                            <div class="review-top">
                                <div class="user-meta">
                                    <div class="user-avatar-sm">
                                        {{ strtoupper(substr($review->user->username, 0, 1)) }}</div>
                                    <strong>{{ $review->user->username }}</strong>
                                </div>
                                <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="review-rating">
                                @for ($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $review->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                            <p class="review-comment">{{ $review->comment ?? 'Hanya memberikan rating.' }}</p>
                        </div>
                    @empty
                        <p class="empty-msg">Belum ada ulasan untuk toko ini.</p>
                    @endforelse
                </div>
            </section>
        </main>

        <main class="main-content hidden" id="historyPage">
            <h2 style="margin-bottom: 25px;">Riwayat Transaksi</h2>
            <div id="historyList">
                <p style="color: var(--sub-text); text-align: center; padding: 50px;">Belum ada riwayat pesanan.</p>
            </div>
        </main>

        <aside class="sidebar anim-fade-up" id="sidebarArea">
            <div id="sidebarContentWrapper">
                <div class="white-card hidden address-section"
                    style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h4 style="font-size: 0.95rem;">Delivery Address</h4>
                        <a href="#" onclick="openAddressPopup(event)"
                            style="color: var(--orange-brand); font-size: 0.75rem; text-decoration: none;">Change</a>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 1.2rem;">📍</span>
                        <div>
                            <p class="delivery-address-value" style="font-size: 0.85rem; font-weight: 600;">-</p>
                            <p class="delivery-address-note"
                                style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4;">Alamat pengiriman
                                default Anda.</p>
                            <p class="delivery-contact-note"
                                style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4; margin-top: 4px;">
                                Penerima: - | No HP: -</p>
                        </div>
                    </div>
                </div>

                <div class="white-card hidden discount-section"
                    style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                    <h4 style="margin-bottom: 12px; font-size: 0.9rem;">Promo Code</h4>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="promoInput" placeholder="TWINS20"
                            style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--card-border); background: rgba(255,255,255,0.05); color: var(--text-color); font-size: 0.8rem;">
                        <button onclick="applyPromo()"
                            style="background: var(--orange-brand); color: white; border: none; padding: 0 15px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.8rem;">Apply</button>
                    </div>
                    <p id="promoMessage" style="font-size: 0.7rem; margin-top: 8px; display: none;"></p>
                </div>

                <div class="white-card hidden order-section"
                    style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 12px; border-radius: 15px; margin-bottom: 15px;">
                    <h4 style="margin-bottom: 12px; font-size: 0.85rem;">Order Menu</h4>
                    <div class="cart-items-container"></div>
                    <hr style="border: 0; border-top: 1px solid var(--card-border); margin: 12px 0;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; color: var(--sub-text);">Ongkir (sementara)</span>
                            <span class="shippingFeeDisplay" style="font-size: 0.8rem; font-weight: 700;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600; font-size: 0.8rem;">Total</span>
                            <span class="totalPriceDisplay"
                                style="font-size: 0.8rem; font-weight: 700; color: var(--orange-brand);"><span
                                    style="font-size: 0.8em;">Rp</span> 0</span>
                        </div>
                    </div>
                    <button class="btn-fill checkout-btn" onclick="checkout()"
                        style="width: 100%; margin-top: 12px; padding: 10px; font-size: 0.9rem;">Checkout</button>
                </div>
            </div>
        </aside>
    </div>

    <nav class="mobile-nav">
        <div class="mob-nav-item active" id="mob-home" onclick="switchPage('home')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>Beranda</span>
        </div>
        <div class="mob-nav-item" id="mob-cat" onclick="scrollToCategory()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Kategori</span>
        </div>
        <div class="mob-nav-item" id="mob-history" onclick="switchPage('history')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Riwayat</span>
        </div>
        <div class="mob-nav-item" onclick="goToWhatsApp()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path
                    d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                </path>
            </svg>
            <span>Chat</span>
        </div>
    </nav>

    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('show');
        }

        window.addEventListener('click', function(e) {
            const menu = document.getElementById('userMenu');
            const btn = document.querySelector('.user-icon-btn');
            if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        const body = document.getElementById('body');

        window.addEventListener('scroll', () => {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        const cartItemsContainer = document.getElementById('cartItems');
        const productGrid = document.getElementById('productGrid');
        const searchInput = document.getElementById('searchInput');
        const mainContainer = document.getElementById('mainContainer');

        const addressSections = document.querySelectorAll('.address-section');
        const orderSections = document.querySelectorAll('.order-section');
        const discountSections = document.querySelectorAll('.discount-section');

        const homePage = document.getElementById('homePage');
        const historyPage = document.getElementById('historyPage');
        const historyList = document.getElementById('historyList');

        // Helper untuk format Rupiah
        function formatRupiah(amount) {
            return "Rp " + Math.floor(amount).toLocaleString('id-ID');
        }

        function escapeHtml(text) {
            const safeText = String(text ?? '');
            return safeText
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // Sementara: ongkir dihitung dari jarak pengantaran (500 rupiah per km).
        function calculateTemporaryShippingFee(distanceKm) {
            const safeDistanceKm = Number.isFinite(distanceKm) ? Math.max(0, distanceKm) : 0;
            return Math.ceil(safeDistanceKm * 500);
        }

        const products = JSON.parse(document.getElementById('products-data').textContent);

        let cart = [];
        let historyData = [];
        let discountPercent = 0;
        const isAuthenticated = document.querySelector('meta[name="auth-check"]').content === 'true';
        const loginUrl = document.querySelector('meta[name="login-url"]').content;
        const storeHours = document.querySelector('meta[name="store-hours"]').content || '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const deliveryAddressStoreUrl = document.querySelector('meta[name="delivery-address-store-url"]').content;
        const checkoutTokenUrl = document.querySelector('meta[name="checkout-token-url"]').content;
        const midtransEnabled = document.querySelector('meta[name="midtrans-enabled"]').content === 'true';
        const persistedDeliveryPreference = JSON.parse(document.querySelector('meta[name="persisted-delivery-preference"]')
            .content);

        function parseStoreHours(hoursText) {
            const match = (hoursText || '').match(/(\d{1,2})[.:](\d{2})\s*-\s*(\d{1,2})[.:](\d{2})/);
            if (!match) return null;

            const openHour = Number(match[1]);
            const openMinute = Number(match[2]);
            const closeHour = Number(match[3]);
            const closeMinute = Number(match[4]);

            const validOpen = Number.isInteger(openHour) && Number.isInteger(openMinute) && openHour >= 0 && openHour <=
                23 && openMinute >= 0 && openMinute <= 59;
            const validClose = Number.isInteger(closeHour) && Number.isInteger(closeMinute) && closeHour >= 0 &&
                closeHour <= 23 && closeMinute >= 0 && closeMinute <= 59;

            if (!validOpen || !validClose) return null;

            return {
                openMinutes: (openHour * 60) + openMinute,
                closeMinutes: (closeHour * 60) + closeMinute,
            };
        }

        function isStoreClosedNow() {
            const parsed = parseStoreHours(storeHours);
            if (!parsed) return false;

            const now = new Date();
            const nowMinutes = (now.getHours() * 60) + now.getMinutes();
            const {
                openMinutes,
                closeMinutes
            } = parsed;

            let isOpen = false;
            if (openMinutes === closeMinutes) {
                isOpen = true;
            } else if (openMinutes < closeMinutes) {
                isOpen = nowMinutes >= openMinutes && nowMinutes < closeMinutes;
            } else {
                // Jadwal lintas tengah malam, contoh: 20.00 - 02.00
                isOpen = nowMinutes >= openMinutes || nowMinutes < closeMinutes;
            }

            return !isOpen;
        }

        function showStoreClosedNotification() {
            const scheduleText = storeHours ? `Jam operasional: ${storeHours}` :
                'Silakan cek kembali jam operasional outlet.';
            Swal.fire({
                title: 'Toko Sedang Tutup',
                text: `Checkout belum tersedia saat toko tutup. ${scheduleText}`,
                icon: 'info',
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                confirmButtonColor: 'var(--orange-brand)',
                confirmButtonText: 'Mengerti'
            });
        }

        function applyCheckoutAvailability() {
            const closed = isStoreClosedNow();
            const checkoutButtons = document.querySelectorAll('.checkout-btn');

            checkoutButtons.forEach((btn) => {
                if (!btn.dataset.originalBackground) btn.dataset.originalBackground = btn.style.background || '';
                if (!btn.dataset.originalCursor) btn.dataset.originalCursor = btn.style.cursor || '';
                if (!btn.dataset.originalOpacity) btn.dataset.originalOpacity = btn.style.opacity || '';

                if (closed) {
                    btn.style.background = '#9ca3af';
                    btn.style.cursor = 'not-allowed';
                    btn.style.opacity = '0.85';
                    btn.setAttribute('aria-disabled', 'true');
                    btn.setAttribute('title', 'Toko sedang tutup');
                } else {
                    btn.style.background = btn.dataset.originalBackground;
                    btn.style.cursor = btn.dataset.originalCursor;
                    btn.style.opacity = btn.dataset.originalOpacity;
                    btn.setAttribute('aria-disabled', 'false');
                    btn.removeAttribute('title');
                }
            });
        }

        function savePersistence() {
            if (!isAuthenticated) return;
            localStorage.setItem('twins_cart', JSON.stringify(cart));
            localStorage.setItem('twins_history', JSON.stringify(historyData));
            if (window.deliveryDetailAddress) {
                localStorage.setItem('twins_delivery_detail', window.deliveryDetailAddress);
            }
        }

        function loadPersistence() {
            if (!isAuthenticated) {
                // Bersihkan jika tidak login (untuk keamanan)
                localStorage.removeItem('twins_cart');
                localStorage.removeItem('twins_history');
                localStorage.removeItem('twins_delivery_detail');
                return;
            }
            const savedCart = localStorage.getItem('twins_cart');
            if (savedCart) {
                try {
                    cart = JSON.parse(savedCart);
                } catch (e) {}
            }
            const savedHistory = localStorage.getItem('twins_history');
            if (savedHistory) {
                try {
                    historyData = JSON.parse(savedHistory);
                } catch (e) {}
            }
            const savedDetail = localStorage.getItem('twins_delivery_detail');
            if (savedDetail) window.deliveryDetailAddress = savedDetail;
        }

        loadPersistence();

        function savePersistedDeliveryAddress() {
            if (!isAuthenticated) return Promise.resolve(true);

            const safeAddress = (deliveryAddress || '').trim();
            const hasCoordinates = !!(deliveryCoordinates && Number.isFinite(deliveryCoordinates.lat) && Number.isFinite(
                deliveryCoordinates.lng));

            if (!safeAddress) return Promise.resolve(false);

            return fetch(deliveryAddressStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        address: safeAddress,
                        recipient_name: deliveryContactName,
                        recipient_phone: deliveryPhone,
                        coordinates: hasCoordinates ? {
                            lat: deliveryCoordinates.lat,
                            lng: deliveryCoordinates.lng
                        } : null
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`save_delivery_failed_${response.status}`);
                    }
                    return true;
                })
                .catch(() => {
                    return false;
                });
        }

        // Toggle Panel Filter
        function toggleFilterPanel() {
            const panel = document.getElementById('filterPanel');
            panel.classList.toggle('hidden');
        }

        // Toggle Semua Kategori
        function toggleAllCategories(checkbox) {
            if (checkbox.checked) {
                // Jika 'Semua' dicentang, hapus semua centang kategori lain
                const catChecks = document.querySelectorAll('.cat-check');
                catChecks.forEach(c => c.checked = false);
            }
            // Jangan panggil applyFilters di sini agar user bisa pilih dulu
        }

        // Event listener untuk kategori satuan
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('cat-check')) {
                if (e.target.checked) {
                    // Jika kategori satuan dicentang, hapus centang 'Semua'
                    document.getElementById('check-all').checked = false;
                } else {
                    // Jika semua kategori satuan tidak dicentang, centang kembali 'Semua'
                    const anyChecked = document.querySelectorAll('.cat-check:checked').length > 0;
                    if (!anyChecked) document.getElementById('check-all').checked = true;
                }
            }
        });

        // Jalankan Filter & Sort
        function applyFilters() {
            renderProducts();
            renderActiveFilters();

            // Tutup panel secara paksa
            const panel = document.getElementById('filterPanel');
            panel.classList.add('hidden');
        }

        // Tampilkan Badge Filter Aktif
        function renderActiveFilters() {
            const container = document.getElementById('activeFilters');
            container.innerHTML = '';

            const isAllChecked = document.getElementById('check-all').checked;
            const priceSort = document.getElementById('priceSort');

            // 1. Tambah Badge Harga (Jika tidak default)
            if (priceSort.value !== 'default') {
                const priceText = priceSort.options[priceSort.selectedIndex].text;
                const priceBadge = document.createElement('div');
                priceBadge.className = 'filter-badge';
                priceBadge.style.borderColor = '#10b981'; // Beri warna hijau agar beda dengan kategori
                priceBadge.style.color = '#10b981';
                priceBadge.innerHTML = `
                    <span>${priceText}</span>
                    <div class="remove-btn" onclick="removePriceFilter()">✕</div>
                `;
                container.appendChild(priceBadge);
            }

            // 2. Tambah Badge Kategori
            if (!isAllChecked) {
                const checkedCats = document.querySelectorAll('.cat-check:checked');
                checkedCats.forEach(cb => {
                    const badge = document.createElement('div');
                    badge.className = 'filter-badge';
                    badge.innerHTML = `
                        <span>${cb.dataset.name}</span>
                        <div class="remove-btn" onclick="removeFilterBadge('${cb.value}')">✕</div>
                    `;
                    container.appendChild(badge);
                });
            }
        }

        // Hapus Filter Harga lewat Badge
        function removePriceFilter() {
            document.getElementById('priceSort').value = 'default';
            renderProducts();
            renderActiveFilters();
        }

        // Hapus Filter Kategori lewat Badge
        function removeFilterBadge(catId) {
            const cb = document.querySelector(`.cat-check[value="${catId}"]`);
            if (cb) {
                cb.checked = false;

                // Jika setelah dihapus tidak ada lagi yang dicentang, balikkan ke 'Semua'
                const anyChecked = document.querySelectorAll('.cat-check:checked').length > 0;
                if (!anyChecked) {
                    document.getElementById('check-all').checked = true;
                }

                renderProducts();
                renderActiveFilters();
            }
        }
        const outletAddress = document.querySelector('meta[name="outlet-address"]').content;
        let deliveryAddress = (persistedDeliveryPreference && typeof persistedDeliveryPreference.address === 'string' &&
                persistedDeliveryPreference.address.trim()) ? persistedDeliveryPreference.address.trim() :
            document.querySelector('meta[name="outlet-address"]').content;
        let deliveryCoordinates = (persistedDeliveryPreference && persistedDeliveryPreference.coordinates && Number
            .isFinite(persistedDeliveryPreference.coordinates.lat) && Number.isFinite(persistedDeliveryPreference
                .coordinates.lng)) ? {
            lat: Number(persistedDeliveryPreference.coordinates.lat),
            lng: Number(persistedDeliveryPreference.coordinates.lng)
        } : null;
        let deliveryContactName = document.querySelector('meta[name="user-name"]').content;
        let deliveryPhone = document.querySelector('meta[name="user-phone"]').content;
        let deliveryDistanceKm = 0;
        let outletCoordinates = null;
        let outletGeocodeTried = false;

        function updateDeliveryAddressUI() {
            const safeAddress = (deliveryAddress || '').trim() || 'Alamat belum diisi';
            const hasCoordinates = !!(deliveryCoordinates && Number.isFinite(deliveryCoordinates.lat) && Number.isFinite(
                deliveryCoordinates.lng));

            document.querySelectorAll('.delivery-address-value').forEach(el => {
                el.textContent = safeAddress;
            });

            document.querySelectorAll('.delivery-address-note').forEach(el => {
                el.textContent = hasCoordinates ?
                    `Dipilih dari peta (${deliveryCoordinates.lat.toFixed(6)}, ${deliveryCoordinates.lng.toFixed(6)}).` :
                    'Alamat pengiriman default Anda.';
            });

            document.querySelectorAll('.delivery-contact-note').forEach(el => {
                const nameText = (deliveryContactName || '').trim() || '-';
                const phoneText = (deliveryPhone || '').trim() || '-';
                const detailText = (window.deliveryDetailAddress || '').trim();
                el.innerHTML =
                    `Penerima: ${nameText} | No HP: ${phoneText}${detailText ? '<br><span style="color:var(--orange-brand); font-style:italic;">Detail: ' + detailText + '</span>' : ''}`;
            });
        }

        function calculateDistanceKmBetweenPoints(from, to) {
            const earthRadiusKm = 6371;
            const dLat = (to.lat - from.lat) * (Math.PI / 180);
            const dLng = (to.lng - from.lng) * (Math.PI / 180);
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(from.lat * (Math.PI / 180)) * Math.cos(to.lat * (Math.PI / 180)) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return earthRadiusKm * c;
        }

        function resolveOutletCoordinatesFromAddress() {
            if (outletCoordinates) return Promise.resolve(outletCoordinates);
            if (outletGeocodeTried) return Promise.resolve(null);

            outletGeocodeTried = true;

            return fetch(
                    `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(outletAddress)}`
                )
                .then(response => response.ok ? response.json() : [])
                .then(results => {
                    if (!Array.isArray(results) || results.length === 0) return null;
                    const first = results[0];
                    const lat = Number(first.lat);
                    const lng = Number(first.lon);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
                    outletCoordinates = {
                        lat,
                        lng
                    };
                    return outletCoordinates;
                })
                .catch(() => null);
        }

        function syncPersistedDeliveryDistance() {
            const hasDeliveryCoordinates = !!(deliveryCoordinates && Number.isFinite(deliveryCoordinates.lat) && Number
                .isFinite(deliveryCoordinates.lng));

            if (!hasDeliveryCoordinates) {
                deliveryDistanceKm = 0;
                renderCart();
                return;
            }

            resolveOutletCoordinatesFromAddress().then(outletLatLng => {
                if (!outletLatLng) {
                    deliveryDistanceKm = 0;
                    renderCart();
                    return;
                }

                fetch(
                        `https://router.project-osrm.org/route/v1/driving/${outletLatLng.lng},${outletLatLng.lat};${deliveryCoordinates.lng},${deliveryCoordinates.lat}?overview=false`
                    )
                    .then(response => response.ok ? response.json() : null)
                    .then(data => {
                        const route = data && Array.isArray(data.routes) && data.routes.length > 0 ? data
                            .routes[0] : null;
                        const routeDistanceKm = route ? Number(route.distance || 0) / 1000 : NaN;

                        if (Number.isFinite(routeDistanceKm) && routeDistanceKm > 0) {
                            deliveryDistanceKm = routeDistanceKm;
                        } else {
                            const straightDistance = calculateDistanceKmBetweenPoints(outletLatLng,
                                deliveryCoordinates);
                            deliveryDistanceKm = Number.isFinite(straightDistance) ? Math.max(0,
                                straightDistance) : 0;
                        }

                        renderCart();
                    })
                    .catch(() => {
                        const straightDistance = calculateDistanceKmBetweenPoints(outletLatLng,
                            deliveryCoordinates);
                        deliveryDistanceKm = Number.isFinite(straightDistance) ? Math.max(0,
                            straightDistance) : 0;
                        renderCart();
                    });
            });
        }

        function openAddressPopup(event) {
            if (event) event.preventDefault();

            let popupMap = null;
            let popupMarker = null;
            let outletMarker = null;
            let routeLine = null;
            let selectedLatLng = deliveryCoordinates ? {
                lat: deliveryCoordinates.lat,
                lng: deliveryCoordinates.lng
            } : null;
            let selectedDistanceKm = Number.isFinite(deliveryDistanceKm) ? Math.max(0, deliveryDistanceKm) : 0;
            let geocodeDebounceTimer = null;
            let geocodeRequestToken = 0;

            const popupHtml = `
                <div class="address-popup-wrap">
                    <div class="address-popup-layout">
                        <div class="address-popup-left">
                            <div class="route-tracking-card">
                                <p style="font-size:11px; letter-spacing:0.05em; color:var(--accent-purple); margin:0 0 6px 0; font-weight:800;">🛰️ LIVE ROUTE TRACKING</p>
                                <p style="font-size:13px; color:var(--text-color); margin:0; line-height:1.5; font-weight:500;" id="routeTrackingSummary">Menyiapkan rute dari outlet ke alamat tujuan...</p>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 18px;">
                                <div>
                                    <label for="recipientNameInput">Nama Penerima</label>
                                    <input type="text" id="recipientNameInput" placeholder="Contoh: Budi">
                                </div>
                                <div>
                                    <label for="recipientPhoneInput">No HP / WhatsApp</label>
                                    <input type="text" id="recipientPhoneInput" placeholder="0812...">
                                </div>
                            </div>

                            <div style="margin-bottom: 18px;">
                                <label for="manualAddressInput">Alamat Utama (Pencarian/Geser Peta)</label>
                                <textarea id="manualAddressInput" rows="3" placeholder="Nama jalan, kecamatan, kota..."></textarea>
                            </div>

                            <div>
                                <label for="detailAddressInput">Detail / Catatan (Opsional)</label>
                                <input type="text" id="detailAddressInput" placeholder="Nomor rumah, warna pagar, atau instruksi khusus">
                            </div>
                        </div>

                        <div class="address-popup-right">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <p style="font-size:12px; margin:0; color:var(--sub-text); font-weight:500;">📍 Pilih titik lokasi tepat pada peta.</p>
                                <button type="button" id="useCurrentLocationBtn" style="background: var(--accent-purple); color: white; border: none; border-radius: 8px; padding: 6px 12px; font-size: 11px; cursor: pointer; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3);">📍 Lokasi Saya</button>
                            </div>
                            <div id="addressMapCanvas"></div>
                            <div id="mapAddressResult" style="margin-top:10px; font-size:11px; color:var(--sub-text); line-height:1.5; font-style: italic;"></div>
                        </div>
                    </div>
                </div>
            `;

            Swal.fire({
                title: 'Ubah Alamat Pengiriman',
                html: popupHtml,
                showCancelButton: true,
                confirmButtonText: 'Simpan Lokasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: 'var(--orange-brand)',
                width: window.innerWidth > 768 ? '950px' : '96vw',
                customClass: {
                    popup: 'address-modal-custom'
                },
                didOpen: () => {
                    const popup = Swal.getPopup();
                    const htmlContainer = Swal.getHtmlContainer();
                    const recipientNameInput = popup.querySelector('#recipientNameInput');
                    const recipientPhoneInput = popup.querySelector('#recipientPhoneInput');
                    const manualAddressInput = popup.querySelector('#manualAddressInput');
                    const detailAddressInput = popup.querySelector('#detailAddressInput');
                    const mapAddressResult = popup.querySelector('#mapAddressResult');
                    const routeTrackingSummary = popup.querySelector('#routeTrackingSummary');
                    const useCurrentLocationBtn = popup.querySelector('#useCurrentLocationBtn');

                    if (htmlContainer) {
                        htmlContainer.style.maxHeight = '72vh';
                        htmlContainer.style.overflowY = 'auto';
                        htmlContainer.style.paddingRight = '4px';
                    }
                    if (popup) {
                        popup.style.maxHeight = '95vh';
                    }

                    recipientNameInput.value = (deliveryContactName || '').trim();
                    recipientPhoneInput.value = (deliveryPhone || '').trim();
                    manualAddressInput.value = (deliveryAddress || '').trim();
                    detailAddressInput.value = (window.deliveryDetailAddress || '').trim();

                    function renderMapResultText(text) {
                        mapAddressResult.textContent = text || '';
                    }

                    function renderRouteTrackingText(text) {
                        routeTrackingSummary.textContent = text || '';
                    }

                    function calculateDistanceKm(from, to) {
                        const earthRadiusKm = 6371;
                        const dLat = (to.lat - from.lat) * (Math.PI / 180);
                        const dLng = (to.lng - from.lng) * (Math.PI / 180);
                        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                            Math.cos(from.lat * (Math.PI / 180)) * Math.cos(to.lat * (Math.PI / 180)) *
                            Math.sin(dLng / 2) * Math.sin(dLng / 2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                        return earthRadiusKm * c;
                    }

                    function resolveOutletCoordinates() {
                        if (outletCoordinates) return Promise.resolve(outletCoordinates);
                        if (outletGeocodeTried) return Promise.resolve(null);

                        outletGeocodeTried = true;

                        return fetch(
                                `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(outletAddress)}`
                            )
                            .then(response => response.ok ? response.json() : [])
                            .then(results => {
                                if (!Array.isArray(results) || results.length === 0) return null;
                                const first = results[0];
                                const lat = Number(first.lat);
                                const lng = Number(first.lon);
                                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
                                outletCoordinates = {
                                    lat,
                                    lng
                                };
                                return outletCoordinates;
                            })
                            .catch(() => null);
                    }

                    function updateRouteTracking() {
                        if (!popupMap) return;

                        if (routeLine) {
                            popupMap.removeLayer(routeLine);
                            routeLine = null;
                        }

                        resolveOutletCoordinates().then(outletLatLng => {
                            if (!outletLatLng) {
                                selectedDistanceKm = 0;
                                renderRouteTrackingText(
                                    'Lokasi outlet belum ditemukan. Rute tidak dapat ditampilkan.');
                                return;
                            }

                            if (!outletMarker) {
                                outletMarker = L.circleMarker([outletLatLng.lat, outletLatLng.lng], {
                                    radius: 6,
                                    color: '#2563eb',
                                    fillColor: '#60a5fa',
                                    fillOpacity: 0.9,
                                    weight: 2
                                }).addTo(popupMap);
                                outletMarker.bindTooltip('Lokasi Outlet', {
                                    permanent: false
                                });
                            } else {
                                outletMarker.setLatLng([outletLatLng.lat, outletLatLng.lng]);
                            }

                            if (!selectedLatLng) {
                                selectedDistanceKm = 0;
                                renderRouteTrackingText(
                                    'Pilih alamat tujuan untuk menampilkan rute dari outlet.');
                                return;
                            }

                            renderRouteTrackingText('Menghitung rute dari outlet ke tujuan...');

                            fetch(
                                    `https://router.project-osrm.org/route/v1/driving/${outletLatLng.lng},${outletLatLng.lat};${selectedLatLng.lng},${selectedLatLng.lat}?overview=full&geometries=geojson`
                                )
                                .then(response => response.ok ? response.json() : null)
                                .then(data => {
                                    if (!data || !Array.isArray(data.routes) || data.routes
                                        .length === 0) {
                                        throw new Error('route_not_found');
                                    }

                                    const route = data.routes[0];
                                    const coords = route.geometry && Array.isArray(route.geometry
                                            .coordinates) ?
                                        route.geometry.coordinates : [];
                                    const latLngs = coords.map(point => [point[1], point[0]]);

                                    if (latLngs.length > 0) {
                                        routeLine = L.polyline(latLngs, {
                                            color: '#f97316',
                                            weight: 4,
                                            opacity: 0.9
                                        }).addTo(popupMap);
                                        popupMap.fitBounds(routeLine.getBounds(), {
                                            padding: [30, 30]
                                        });
                                    }

                                    const distanceKm = Number(route.distance || 0) / 1000;
                                    const durationMin = Number(route.duration || 0) / 60;
                                    selectedDistanceKm = Number.isFinite(distanceKm) ? Math.max(0,
                                        distanceKm) : 0;
                                    renderRouteTrackingText(
                                        `Rute outlet -> tujuan sekitar ${distanceKm.toFixed(2)} km (${durationMin.toFixed(0)} menit).`
                                    );
                                })
                                .catch(() => {
                                    routeLine = L.polyline([
                                        [outletLatLng.lat, outletLatLng.lng],
                                        [selectedLatLng.lat, selectedLatLng.lng]
                                    ], {
                                        color: '#f97316',
                                        weight: 3,
                                        dashArray: '8, 8',
                                        opacity: 0.75
                                    }).addTo(popupMap);
                                    popupMap.fitBounds(routeLine.getBounds(), {
                                        padding: [30, 30]
                                    });

                                    const straightDistance = calculateDistanceKm(outletLatLng,
                                        selectedLatLng);
                                    selectedDistanceKm = Number.isFinite(straightDistance) ? Math
                                        .max(0,
                                            straightDistance) : 0;
                                    renderRouteTrackingText(
                                        `Rute detail belum tersedia. Jarak garis lurus outlet -> tujuan sekitar ${straightDistance.toFixed(2)} km.`
                                    );
                                });
                        });
                    }

                    function setMarker(latlng, shouldCenter = false) {
                        selectedLatLng = {
                            lat: latlng.lat,
                            lng: latlng.lng
                        };
                        if (!popupMarker) {
                            popupMarker = L.marker(latlng).addTo(popupMap);
                        } else {
                            popupMarker.setLatLng(latlng);
                        }

                        if (shouldCenter && popupMap) {
                            popupMap.setView([latlng.lat, latlng.lng], 16);
                        }

                        renderMapResultText(
                            `Koordinat dipilih: ${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`);

                        fetch(
                                `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latlng.lat}&lon=${latlng.lng}`
                            )
                            .then(response => response.ok ? response.json() : null)
                            .then(data => {
                                if (data && data.display_name) {
                                    manualAddressInput.value = data.display_name;
                                    renderMapResultText(data.display_name);
                                }
                                updateRouteTracking();
                            })
                            .catch(() => {
                                // Keep coordinate fallback when reverse geocoding fails.
                                updateRouteTracking();
                            });

                        updateRouteTracking();
                    }

                    function geocodeAddressToMap(addressText) {
                        const query = (addressText || '').trim();
                        if (!query || query.length < 5) {
                            return;
                        }

                        geocodeRequestToken += 1;
                        const currentToken = geocodeRequestToken;

                        fetch(
                                `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(query)}`
                            )
                            .then(response => response.ok ? response.json() : [])
                            .then(results => {
                                if (currentToken !== geocodeRequestToken) return;
                                if (!Array.isArray(results) || results.length === 0) {
                                    renderMapResultText(
                                        'Alamat belum ditemukan di peta. Coba detailkan alamat.');
                                    return;
                                }

                                const first = results[0];
                                const lat = Number(first.lat);
                                const lng = Number(first.lon);
                                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                                    renderMapResultText(
                                        'Koordinat alamat tidak valid dari hasil pencarian.');
                                    return;
                                }

                                setMarker({
                                    lat,
                                    lng
                                }, true);
                                if (first.display_name) {
                                    renderMapResultText(first.display_name);
                                }
                            })
                            .catch(() => {
                                renderMapResultText('Gagal mencari alamat. Periksa koneksi internet Anda.');
                            });
                    }

                    function initMap() {
                        if (popupMap || typeof L === 'undefined') return;

                        // Fix for Leaflet default marker icons being blocked by Tracking Prevention
                        delete L.Icon.Default.prototype._getIconUrl;
                        L.Icon.Default.mergeOptions({
                            iconRetinaUrl: "{{ asset('vendor/leaflet/images/marker-icon-2x.png') }}",
                            iconUrl: "{{ asset('vendor/leaflet/images/marker-icon.png') }}",
                            shadowUrl: "{{ asset('vendor/leaflet/images/marker-shadow.png') }}",
                        });

                        const initialLatLng = selectedLatLng ? [selectedLatLng.lat, selectedLatLng.lng] : [-6.200000, 106.816666];
                        const initialZoom = selectedLatLng ? 16 : 12;

                        popupMap = L.map('addressMapCanvas', {
                            zoomControl: true,
                            attributionControl: true
                        }).setView(initialLatLng, initialZoom);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(popupMap);

                        if (selectedLatLng) {
                            setMarker(selectedLatLng, true);
                        } else if ((manualAddressInput.value || '').trim()) {
                            geocodeAddressToMap(manualAddressInput.value);
                        }

                        popupMap.on('click', e => setMarker(e.latlng));

                        updateRouteTracking();
                    }

                    manualAddressInput.addEventListener('input', () => {
                        if (geocodeDebounceTimer) {
                            clearTimeout(geocodeDebounceTimer);
                        }
                        geocodeDebounceTimer = setTimeout(() => {
                            geocodeAddressToMap(manualAddressInput.value);
                        }, 700);
                    });

                    useCurrentLocationBtn.addEventListener('click', () => {
                        if (!navigator.geolocation) {
                            Swal.showValidationMessage('Geolocation tidak didukung oleh browser Anda.');
                            return;
                        }

                        useCurrentLocationBtn.innerText = '⌛ Mencari...';
                        useCurrentLocationBtn.disabled = true;

                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const latlng = {
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude
                                };
                                setMarker(latlng, true);
                                useCurrentLocationBtn.innerText = '📍 Gunakan Lokasi Saat Ini';
                                useCurrentLocationBtn.disabled = false;
                            },
                            (error) => {
                                let msg = 'Gagal mendapatkan lokasi.';
                                if (error.code === 1) msg =
                                    'Izin lokasi ditolak. Harap aktifkan izin lokasi di browser.';
                                else if (error.code === 2) msg =
                                    'Lokasi tidak tersedia (Pastikan GPS aktif).';
                                else if (error.code === 3) msg =
                                    'Waktu pencarian habis. Coba klik lagi.';

                                if (window.location.protocol !== 'https:' && window.location
                                    .hostname !== 'localhost' && window.location.hostname !==
                                    '127.0.0.1') {
                                    msg += ' (GPS memerlukan HTTPS)';
                                }

                                Swal.showValidationMessage(msg);
                                useCurrentLocationBtn.innerText = '📍 Gunakan Lokasi Saat Ini';
                                useCurrentLocationBtn.disabled = false;
                            }, {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    });

                    initMap();
                    if (popupMap) {
                        setTimeout(() => popupMap.invalidateSize(), 100);
                    }
                },
                preConfirm: () => {
                    const popup = Swal.getPopup();
                    const nameIn = popup.querySelector('#recipientNameInput');
                    const phoneIn = popup.querySelector('#recipientPhoneInput');
                    const addressIn = popup.querySelector('#manualAddressInput');
                    const detailIn = popup.querySelector('#detailAddressInput');

                    const recipientName = (nameIn.value || '').trim();
                    const recipientPhone = (phoneIn.value || '').trim();
                    const manualAddress = (addressIn.value || '').trim();
                    const detailAddress = (detailIn.value || '').trim();

                    if (!recipientName) {
                        Swal.showValidationMessage('Nama penerima wajib diisi.');
                        return false;
                    }

                    if (!recipientPhone) {
                        Swal.showValidationMessage('No HP wajib diisi.');
                        return false;
                    }

                    if (!manualAddress) {
                        Swal.showValidationMessage('Pilih alamat pada peta atau isi alamat utama.');
                        return false;
                    }

                    return {
                        recipientName,
                        recipientPhone,
                        address: manualAddress,
                        detail: detailAddress,
                        distanceKm: selectedDistanceKm,
                        coordinates: selectedLatLng ? {
                            lat: selectedLatLng.lat,
                            lng: selectedLatLng.lng
                        } : null
                    };
                }
            }).then(async (result) => {
                if (!result.isConfirmed || !result.value) return;

                deliveryContactName = result.value.recipientName;
                deliveryPhone = result.value.recipientPhone;
                deliveryAddress = result.value.address;
                window.deliveryDetailAddress = result.value
                    .detail; // Simpan di window agar persisten selama sesi
                deliveryDistanceKm = Number.isFinite(result.value.distanceKm) ? Math.max(0, result.value
                    .distanceKm) : 0;
                deliveryCoordinates = result.value.coordinates;

                // Animasi Menyimpan Data
                Swal.fire({
                    title: 'Menyimpan Lokasi',
                    html: 'Sedang mensinkronkan data alamat Anda...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const persisted = await savePersistedDeliveryAddress();
                updateDeliveryAddressUI();
                renderCart();

                if (!persisted && isAuthenticated) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Alamat tersimpan sementara',
                        text: 'Penyimpanan alamat ke server gagal. Coba simpan ulang alamat Anda.',
                        background: 'var(--bg-color)',
                        color: 'var(--text-color)',
                        confirmButtonColor: 'var(--orange-brand)'
                    });
                    return;
                }

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Alamat Diperbarui',
                        text: 'Lokasi telah disinkronkan.',
                        timer: 1500,
                        showConfirmButton: false,
                        background: 'var(--bg-color)',
                        color: 'var(--text-color)',
                        customClass: {
                            popup: 'premium-swal-success',
                        },
                        showClass: {
                            popup: 'premium-swal-show'
                        },
                        hideClass: {
                            popup: 'premium-swal-hide'
                        }
                    });
                }, 100);
            });
        }

        function renderProducts() {
            if (!productGrid) return;
            productGrid.innerHTML = '';

            const searchEl = document.getElementById('searchInput');
            const sortEl = document.getElementById('priceSort');
            const searchTerm = searchEl ? searchEl.value.toLowerCase().trim() : '';
            const priceSort = sortEl ? sortEl.value : 'default';
            const checkedCats = Array.from(document.querySelectorAll('.cat-check:checked')).map(c => c.value);
            const isAllChecked = document.getElementById('check-all') ? document.getElementById('check-all').checked : true;

            let filtered = products.filter(p => {
                const matchesCategory = isAllChecked || checkedCats.length === 0 || checkedCats.includes(p
                    .category_id);
                const matchesSearch = p.name.toLowerCase().includes(searchTerm);
                return matchesCategory && matchesSearch;
            });

            if (priceSort === 'low-high') {
                filtered.sort((a, b) => a.price - b.price);
            } else if (priceSort === 'high-low') {
                filtered.sort((a, b) => b.price - a.price);
            }

            if (filtered.length === 0) {
                const emptyMsg = document.createElement('div');
                emptyMsg.style.cssText =
                    'grid-column: 1/-1; text-align: center; padding: 60px; color: var(--sub-text); font-size: 1.1rem;';
                emptyMsg.innerHTML = '<div style="margin-bottom: 15px; font-size: 3rem;">🔍</div>Item tidak ditemukan.';
                productGrid.appendChild(emptyMsg);
                return;
            }

            filtered.forEach(product => {
                const isOutOfStock = product.stok <= 0;
                const card = document.createElement('div');
                card.className = `food-card anim-zoom-in ${isOutOfStock ? 'out-of-stock' : ''}`;

                card.innerHTML = `
                    <div style="width: 100%; aspect-ratio: 1/1; overflow: hidden; border-radius: 14px; margin-bottom: 8px; position: relative; background: #fff; display: flex; align-items: center; justify-content: center; padding: 6px;">
                        <img src="${product.img}" class="food-img" style="max-width: 100%; max-height: 100%; object-fit: contain; filter: ${isOutOfStock ? 'grayscale(1) opacity(0.6)' : 'none'}">

                        ${product.is_discount && !isOutOfStock ? `
                            <div style="position: absolute; top: 4px; right: 4px; background: #ef4444; color: white; padding: 2px 5px; border-radius: 4px; font-size: 0.6rem; font-weight: 800; z-index: 2; box-shadow: 0 2px 4px rgba(239,68,68,0.3);">
                                -${product.discount_label}
                            </div>
                        ` : ''}

                        ${isOutOfStock ? `
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(239, 68, 68, 0.9); color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; z-index: 2; backdrop-filter: blur(4px);">HABIS</div>
                        ` : ''}
                    </div>
                    
                    <div style="display: flex; flex-direction: column; flex-grow: 1; min-height: 0;">
                        <h4 style="font-size: 0.72rem; color: var(--text-color); font-weight: 700; margin-bottom: 2px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.25; height: 2.5em; min-height: 2.5em;">${product.name}</h4>

                        <div style="height: 1em; margin-bottom: 6px; display: flex; align-items: center;">
                            ${!isOutOfStock ? `
                                <p style="color: #10b981; font-size: 0.58rem; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">Stok: ${product.stok}</p>
                            ` : ''}
                        </div>

                        <div style="margin-top: auto;">
                            <div style="height: 0.9rem; margin-bottom: 0px; display: flex; align-items: flex-end;">
                                ${product.is_discount && !isOutOfStock ? `
                                    <span style="display: block; color: var(--sub-text); text-decoration: line-through; font-size: 0.65rem; line-height: 1;">
                                        ${formatRupiah(product.original_price)}
                                    </span>
                                ` : ''}
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 4px;">
                                <span style="font-weight: 800; color: ${isOutOfStock ? 'var(--sub-text)' : 'var(--orange-brand)'}; font-size: 0.8rem; white-space: nowrap;">
                                    ${formatRupiah(product.price).replace('Rp', '<span style="font-size: 0.75em;">Rp</span>')}
                                </span>
                                <button class="add-btn"
                                        data-name="${product.name}"
                                        data-price="${product.price}"
                                        data-stock="${product.stok}"
                                        onclick="addToCartFromEl(this)"
                                        style="width: 28px; height: 28px; border-radius: 8px; background: ${isOutOfStock ? 'rgba(255,255,255,0.1)' : 'var(--btn-grad)'}; color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: transform 0.2s; box-shadow: ${isOutOfStock ? 'none' : 'var(--glow)'};">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                productGrid.appendChild(card);
            });
        }

        function handleSearch() {
            renderProducts();
        }

        function getCartPricedItems() {
            return cart.map((item) => {
                const pInfo = products.find(p => p.name === item.name);
                let displayPrice = item.price;
                if (pInfo && pInfo.price_levels && pInfo.price_levels.length > 0) {
                    const levels = [...pInfo.price_levels].sort((a, b) => b.jmlh - a.jmlh);
                    const appliedLevel = levels.find(l => item.qty >= l.jmlh);
                    if (appliedLevel) displayPrice = appliedLevel.harga;
                }

                return {
                    ...item,
                    product_id: pInfo ? pInfo.id : (item.product_id || null),
                    image: pInfo ? pInfo.img : '',
                    displayPrice,
                    subtotal: displayPrice * item.qty,
                    original_price: pInfo ? pInfo.original_price : item.price,
                    is_discount: pInfo ? pInfo.is_discount : false,
                    discount_label: pInfo ? pInfo.discount_label : ''
                };
            });
        }

        function calculateCartSummary() {
            const pricedItems = getCartPricedItems();
            const subtotal = pricedItems.reduce((acc, item) => acc + item.subtotal, 0);
            
            // Calculate total from original prices to get total product discount
            const originalSubtotal = pricedItems.reduce((acc, item) => acc + (item.original_price * item.qty), 0);
            const productDiscountAmount = originalSubtotal - subtotal;
            
            const shippingFee = calculateTemporaryShippingFee(deliveryDistanceKm);
            const promoDiscountAmount = subtotal > 0 ? subtotal * discountPercent : 0;
            const discountedSubtotal = subtotal - promoDiscountAmount;
            
            const totalDiscountAmount = productDiscountAmount + promoDiscountAmount;
            const total = discountedSubtotal + shippingFee;

            return {
                originalSubtotal,
                subtotal, // Subtotal after product discount, before promo
                productDiscountAmount,
                promoDiscountAmount,
                totalDiscountAmount,
                shippingFee,
                total,
                pricedItems,
            };
        }

        function addToCartFromEl(el) {
            const name = el.getAttribute('data-name');
            const price = parseFloat(el.getAttribute('data-price'));
            const stock = parseInt(el.getAttribute('data-stock'));

            if (stock <= 0) {
                Swal.fire('Opps!', 'Stok produk ini sedang habis.', 'error');
                return;
            }

            const productInfo = products.find(p => p.name === name);
            addToCart(name, price, productInfo ? productInfo.id : null);
        }

        function addToCart(name, price, productId = null) {
            // Temukan info stok asli dari array products
            const productInfo = products.find(p => p.name === name);
            if (productInfo && productInfo.stok <= 0) {
                Swal.fire('Maaf!', 'Stok barang ini sudah habis.', 'error');
                return;
            }

            const existingItem = cart.find(item => item.product_id === productId || item.name === name);
            if (existingItem) {
                // Cek jika jumlah di keranjang sudah melebihi stok
                if (existingItem.qty >= productInfo.stok) {
                    Swal.fire('Limit Stok!', `Anda hanya bisa memesan maksimal ${productInfo.stok} item.`, 'warning');
                    return;
                }
                existingItem.qty += 1;
            } else {
                cart.push({
                    name,
                    product_id: productId,
                    price,
                    qty: 1
                });
            }
            renderCart();
            const fab = document.getElementById('mobileCartBtn');
            if (fab) {
                fab.style.transform = 'scale(1.2)';
                setTimeout(() => fab.style.transform = '', 200);
            }
        }

        function updateQty(index, delta) {
            cart[index].qty += delta;
            if (cart[index].qty <= 0) {
                cart.splice(index, 1);
            }
            renderCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function renderCart() {
            applyCheckoutAvailability();

            const isMobile = window.innerWidth <= 992;
            const badge = document.getElementById('cartBadge');
            const totalCount = cart.reduce((acc, item) => acc + item.qty, 0);
            if (badge) badge.innerText = totalCount;

            const mobileCartBtn = document.getElementById('mobileCartBtn');

            if (cart.length > 0) {
                [...addressSections, ...orderSections, ...discountSections].forEach(el => el.classList.remove('hidden'));
                if (!isMobile) {
                    mainContainer.classList.add('has-sidebar');
                    if (mobileCartBtn) mobileCartBtn.style.display = 'none';
                } else {
                    mainContainer.classList.remove('has-sidebar');
                    if (mobileCartBtn) mobileCartBtn.style.display = 'flex';
                }
            } else {
                [...addressSections, ...orderSections, ...discountSections].forEach(el => el.classList.add('hidden'));
                mainContainer.classList.remove('has-sidebar');
                if (mobileCartBtn) mobileCartBtn.style.display = 'none';
                toggleBottomSheet(false);
            }

            // Calculate totals
            const summary = calculateCartSummary();
            const cartHtmlItems = summary.pricedItems.map((item, index) => {
                const pInfo = {
                    img: item.image,
                };

                return `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; overflow: hidden; background: white; flex-shrink: 0;">
                                <img src="${pInfo ? pInfo.img : ''}" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div style="flex: 1;">
                                <h5 style="font-size: 0.85rem;">${item.name}</h5>
                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                    <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                                    <span style="font-size: 0.8rem;">${item.qty}</span>
                                    <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px; text-align: right;">
                            <span style="color: var(--orange-brand); font-weight: 700; font-size: 0.75rem;">${formatRupiah(item.subtotal).replace('Rp', '<span style="font-size: 0.8em;">Rp</span>')}</span>
                            ${item.displayPrice < item.price ? `<span style="font-size: 0.65rem; color: #10b981; font-weight: 700;">Hemat Grosir!</span>` : ''}
                            <button class="delete-item-btn" onclick="removeFromCart(${index})">🗑️</button>
                        </div>
                    </div>
                `;
            }).join('');

            const formattedTotal = formatRupiah(summary.total);

            document.querySelectorAll('.shippingFeeDisplay').forEach(el => {
                el.innerText = formatRupiah(summary.shippingFee);
            });

            document.querySelectorAll('.totalPriceDisplay').forEach(el => {
                el.innerHTML = formattedTotal.replace('Rp', '<span style="font-size: 0.8em;">Rp</span>');
            });

            document.querySelectorAll('.cart-items-container').forEach(c => c.innerHTML = cartHtmlItems);

            if (isMobile) {
                const sheetContent = document.getElementById('mobileSheetContent');
                if (sheetContent) {
                    sheetContent.querySelectorAll('.totalPriceDisplay').forEach(el => el.innerHTML = formattedTotal.replace(
                        'Rp', '<span style="font-size: 0.8em;">Rp</span>'));
                }
            }

            updateDeliveryAddressUI();
            savePersistence();
        }

        function toggleBottomSheet(force) {
            const sheet = document.getElementById('bottomSheet');
            const overlay = document.getElementById('sheetOverlay');
            if (!sheet || !overlay) return;

            let isActive = false;
            if (force === true) {
                sheet.classList.add('active');
                overlay.classList.add('active');
                isActive = true;
            } else if (force === false) {
                sheet.classList.remove('active');
                overlay.classList.remove('active');
                isActive = false;
            } else {
                sheet.classList.toggle('active');
                overlay.classList.toggle('active');
                isActive = sheet.classList.contains('active');
            }

            if (isActive) {
                renderCart();
            }
        }

        function showWholesaleInfo(productId) {
            const product = products.find(p => p.id === productId);
            if (!product || !product.price_levels) return;

            let tableHtml = `
                <div style="text-align: left; margin-top: 10px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid #eee;">
                                <th style="padding: 10px 5px; text-align: left;">Min. Pembelian</th>
                                <th style="padding: 10px 5px; text-align: left;">Harga per Unit</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            product.price_levels.forEach(level => {
                tableHtml += `
                    <tr style="border-bottom: 1px solid #f5f5f5;">
                        <td style="padding: 12px 5px; font-weight: 600;">${level.jmlh} Unit atau lebih</td>
                        <td style="padding: 12px 5px; color: #C62828; font-weight: 700;">${formatRupiah(level.harga)}</td>
                    </tr>
                `;
            });

            tableHtml += `</tbody></table></div>`;

            Swal.fire({
                title: 'Harga Grosir',
                html: `Dapatkan harga lebih hemat untuk pembelian dalam jumlah banyak pada produk <strong>${product.name}</strong>.<br>${tableHtml}`,
                icon: 'info',
                confirmButtonText: 'Tutup',
                confirmButtonColor: 'var(--orange-brand)'
            });
        }

        function applyPromo() {
            const input = document.getElementById('promoInput').value.trim().toUpperCase();
            const message = document.getElementById('promoMessage');
            if (input === 'TWINS20') {
                discountPercent = 0.20;
                message.innerText = "Promo TWINS20 applied! (20% Off)";
                message.style.color = "#10b981";
            } else {
                discountPercent = 0;
                message.innerText = input === "" ? "" : "Invalid promo code.";
                message.style.color = "#ef4444";
            }
            message.style.display = 'block';
            renderCart();
        }

        function switchPage(page) {
            document.querySelectorAll('.nav-link, .mob-nav-item').forEach(l => l.classList.remove('active'));
            homePage.classList.add('hidden');
            historyPage.classList.add('hidden');

            if (page === 'home') {
                homePage.classList.remove('hidden');
                document.getElementById('nav-home').classList.add('active');
                const mobHome = document.getElementById('mob-home');
                if (mobHome) mobHome.classList.add('active');
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                renderCart();
            } else if (page === 'history') {
                historyPage.classList.remove('hidden');
                document.getElementById('nav-history').classList.add('active');
                const mobHistory = document.getElementById('mob-history');
                if (mobHistory) mobHistory.classList.add('active');
                mainContainer.classList.remove('has-sidebar');
                if (isAuthenticated) {
                    fetchHistoryFromServer().then(() => renderHistory());
                } else {
                    renderHistory();
                }
            }
        }

        function scrollToCategory() {
            switchPage('home');
            document.querySelectorAll('.nav-link, .mob-nav-item').forEach(l => l.classList.remove('active'));
            document.getElementById('nav-cat').classList.add('active');
            const mobCat = document.getElementById('mob-cat');
            if (mobCat) mobCat.classList.add('active');

            setTimeout(() => {
                const categorySection = document.getElementById('categorySection');
                if (categorySection) {
                    categorySection.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }, 100);
        }

        function goToWhatsApp() {
            window.open(`https://wa.me/6282330755390?text=Halo TWINS!`, '_blank');
        }

        function checkout() {
            if (isStoreClosedNow()) {
                showStoreClosedNotification();
                return;
            }

            if (cart.length === 0) return;

            if (!isAuthenticated) {
                Swal.fire({
                    title: 'Login Diperlukan',
                    text: 'Silakan login terlebih dahulu untuk melanjutkan checkout.',
                    icon: 'warning',
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    confirmButtonColor: 'var(--orange-brand)',
                    confirmButtonText: 'Login Sekarang',
                    showCancelButton: true,
                    cancelButtonText: 'Nanti',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = loginUrl;
                    }
                });
                return;
            }

            if (!midtransEnabled || typeof window.snap === 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Belum Tersedia',
                    text: 'Konfigurasi Midtrans belum aktif. Hubungi admin untuk mengaktifkan pembayaran.',
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    confirmButtonColor: 'var(--orange-brand)'
                });
                return;
            }

            const recipientName = (deliveryContactName || '').trim();
            const recipientPhone = (deliveryPhone || '').trim();
            const address = (deliveryAddress || '').trim();

            if (!recipientName || !recipientPhone || !address) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Lengkapi Data Pengiriman',
                    text: 'Isi dulu nama penerima, no HP, dan alamat pengiriman sebelum checkout.',
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    confirmButtonColor: 'var(--orange-brand)'
                });
                return;
            }

            const summary = calculateCartSummary();
            if (summary.total <= 0) {
                Swal.fire('Oops', 'Total pembayaran tidak valid.', 'error');
                return;
            }

            const visibleItems = summary.pricedItems.slice(0, 5);
            const hiddenItemCount = Math.max(0, summary.pricedItems.length - visibleItems.length);
            const itemListHtml = visibleItems.map(i => `
                <div style="display:flex; justify-content:space-between; gap:10px; font-size:0.82rem; padding:8px 0; border-bottom:1px dashed rgba(148,163,184,0.25);">
                    <div style="flex:1; min-width:0;">
                        <span style="color:var(--text-color); display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(i.qty)}x ${escapeHtml(i.name)}</span>
                        ${i.is_discount ? `
                            <div style="display:flex; align-items:center; gap:6px; margin-top:2px;">
                                <span style="font-size:0.7rem; text-decoration:line-through; color:var(--sub-text);">${escapeHtml(formatRupiah(i.original_price * i.qty))}</span>
                                <span style="font-size:0.7rem; color:#10b981; font-weight:700;">Diskon Produk</span>
                            </div>
                        ` : ''}
                    </div>
                    <span style="color:var(--orange-brand); font-weight:700; white-space:nowrap; align-self:center;">${escapeHtml(formatRupiah(i.subtotal))}</span>
                </div>
            `).join('');

            const totalDiscountFormatted = summary.totalDiscountAmount > 0 ? `- ${formatRupiah(summary.totalDiscountAmount)}` : 'Rp 0';
            const estimatedArrivalMinutes = Math.max(10, Math.round(Number(deliveryDistanceKm || 0) * 4));

            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                html: `
                    <div style="text-align:left; font-size:0.9rem; line-height:1.45; display:grid; gap:12px;">
                        <div style="border:1px solid var(--card-border); border-radius:12px; padding:12px; background:rgba(15,23,42,0.18);">
                            <p style="margin:0 0 8px 0; font-size:0.75rem; letter-spacing:0.04em; color:var(--sub-text);">DETAIL PENGIRIMAN</p>
                            <div style="display:grid; gap:6px; font-size:0.82rem;">
                                <div><span style="color:var(--sub-text);">Penerima:</span> <strong>${escapeHtml(recipientName)}</strong></div>
                                <div><span style="color:var(--sub-text);">No HP:</span> <strong>${escapeHtml(recipientPhone)}</strong></div>
                                <div><span style="color:var(--sub-text);">Jarak:</span> <strong>${escapeHtml(Number(deliveryDistanceKm || 0).toFixed(2))} km</strong></div>
                                <div><span style="color:var(--sub-text);">Estimasi:</span> <strong>${escapeHtml(estimatedArrivalMinutes)} menit</strong></div>
                                <div style="display:flex; gap:6px;"><span style="color:var(--sub-text); white-space:nowrap;">Alamat:</span><span style="word-break:break-word;">${escapeHtml(address)}</span></div>
                            </div>
                        </div>

                        <div style="border:1px solid var(--card-border); border-radius:12px; padding:12px; background:rgba(2,132,199,0.06);">
                            <p style="margin:0 0 8px 0; font-size:0.75rem; letter-spacing:0.04em; color:var(--sub-text);">RINGKASAN ITEM (${escapeHtml(summary.pricedItems.length)})</p>
                            <div style="max-height:168px; overflow:auto; padding-right:4px;">${itemListHtml}</div>
                            ${hiddenItemCount > 0 ? `<p style="margin:8px 0 0 0; font-size:0.75rem; color:var(--sub-text);">+${escapeHtml(hiddenItemCount)} item lainnya</p>` : ''}
                        </div>

                        <div style="border:1px solid rgba(249,115,22,0.35); border-radius:14px; padding:12px; background:linear-gradient(135deg, rgba(249,115,22,0.14), rgba(249,115,22,0.04));">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px; margin-bottom:8px;">
                                <div>
                                    <p style="margin:0; font-size:0.75rem; letter-spacing:0.04em; color:var(--sub-text);">TOTAL PEMBAYARAN</p>
                                    <p style="margin:4px 0 0 0; font-size:1.22rem; font-weight:800; color:var(--orange-brand);">${escapeHtml(formatRupiah(summary.total))}</p>
                                </div>
                                <span style="font-size:0.68rem; padding:4px 8px; border-radius:999px; border:1px solid rgba(16,185,129,0.4); color:#10b981; font-weight:700;">MIDTRANS</span>
                            </div>
                            <div style="display:grid; gap:5px; font-size:0.8rem;">
                                <div style="display:flex; justify-content:space-between;"><span style="color:var(--sub-text);">Subtotal</span><span style="font-weight:700;">${escapeHtml(formatRupiah(summary.originalSubtotal))}</span></div>
                                <div style="display:flex; justify-content:space-between;"><span style="color:var(--sub-text);">Diskon</span><span style="font-weight:700; color:#10b981;">${escapeHtml(totalDiscountFormatted)}</span></div>
                                <div style="display:flex; justify-content:space-between;"><span style="color:var(--sub-text);">Ongkir</span><span style="font-weight:700;">${escapeHtml(formatRupiah(summary.shippingFee))}</span></div>
                            </div>
                        </div>

                        <p style="margin:0; font-size:0.76rem; color:var(--sub-text);">Setelah klik Lanjut ke Pembayaran, Anda akan diarahkan ke popup Midtrans untuk memilih metode pembayaran.</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Lanjut ke Pembayaran',
                cancelButtonText: 'Kembali',
                confirmButtonColor: 'var(--orange-brand)',
                width: 'min(760px, 96vw)',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                didOpen: () => {
                    const popup = Swal.getPopup();
                    if (popup) {
                        popup.style.borderRadius = '20px';
                    }
                },
                preConfirm: () => {
                    return fetch(checkoutTokenUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                recipient_name: recipientName,
                                recipient_phone: recipientPhone,
                                address,
                                coordinates: deliveryCoordinates ? {
                                    lat: deliveryCoordinates.lat,
                                    lng: deliveryCoordinates.lng
                                } : null,
                                distance_km: deliveryDistanceKm,
                                discount_percent: discountPercent,
                                shipping_fee: summary.shippingFee,
                                items: summary.pricedItems.map(i => ({
                                    product_id: i.product_id,
                                    name: i.name,
                                    qty: i.qty,
                                    unit_price: i.displayPrice
                                }))
                            })
                        })
                        .then(async (response) => {
                            const data = await response.json().catch(() => ({}));
                            if (!response.ok) {
                                const errorMessage = data.message || 'Gagal membuat token pembayaran.';
                                throw new Error(errorMessage);
                            }
                            return data;
                        })
                        .catch((error) => {
                            Swal.showValidationMessage(error.message || 'Gagal memproses pembayaran.');
                        });
                }
            }).then((result) => {
                if (!result.isConfirmed || !result.value || !result.value.snap_token) return;

                const paymentData = result.value;

                const finalizeOrder = (paymentStatus, paymentResult) => {
                    historyData.unshift({
                        id: paymentData.order_id || Date.now(),
                        payment_order_db_id: paymentData.payment_order_id || null,
                        date: new Date().toLocaleString('id-ID'),
                        items: summary.pricedItems.map(i => ({
                            name: i.name,
                            qty: i.qty,
                            price: i.displayPrice,
                            product_id: i.product_id
                        })),
                        total: summary.total,
                        shipping_fee: summary.shippingFee,
                        recipient_name: recipientName,
                        recipient_phone: recipientPhone,
                        address,
                        coordinates: deliveryCoordinates ? {
                            lat: deliveryCoordinates.lat,
                            lng: deliveryCoordinates.lng
                        } : null,
                        payment_status: paymentStatus,
                        midtrans_result: paymentResult || null,
                    });

                    cart = [];
                    discountPercent = 0;
                    savePersistence();
                    renderCart();
                    switchPage('history');
                };

                const syncPaymentStatus = (orderId) => {
                    const syncUrl = document.querySelector('meta[name="sync-payment-url"]').content;
                    return fetch(syncUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            order_id: orderId
                        })
                    }).catch(err => console.error('Sync failed:', err));
                };

                window.snap.pay(paymentData.snap_token, {
                    onSuccess: function(resultSnap) {
                        Swal.fire({
                            title: 'Memproses Transaksi...',
                            text: 'Pembayaran berhasil! Sedang mensinkronkan pesanan Anda.',
                            allowOutsideClick: false,
                            background: 'var(--bg-color)',
                            color: 'var(--text-color)',
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        syncPaymentStatus(paymentData.order_id).finally(() => {
                            finalizeOrder('paid', resultSnap);
                            Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Berhasil',
                                text: 'Pesanan Anda telah diterima dan sedang diproses.',
                                background: 'var(--bg-color)',
                                color: 'var(--text-color)',
                                confirmButtonColor: 'var(--orange-brand)'
                            }).then(() => {
                                switchPage('history');
                            });
                        });
                    },
                    onPending: function(resultSnap) {
                        Swal.fire({
                            title: 'Menunggu Konfirmasi...',
                            text: 'Sedang menyiapkan instruksi pembayaran.',
                            allowOutsideClick: false,
                            background: 'var(--bg-color)',
                            color: 'var(--text-color)',
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        syncPaymentStatus(paymentData.order_id).finally(() => {
                            finalizeOrder('pending', resultSnap);
                            Swal.fire({
                                icon: 'info',
                                title: 'Menunggu Pembayaran',
                                text: 'Silakan selesaikan pembayaran Anda sesuai instruksi.',
                                background: 'var(--bg-color)',
                                color: 'var(--text-color)',
                                confirmButtonColor: 'var(--orange-brand)'
                            }).then(() => {
                                switchPage('history');
                            });
                        });
                    },
                    onError: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pembayaran Gagal',
                            text: 'Transaksi gagal diproses. Silakan coba lagi.',
                            background: 'var(--bg-color)',
                            color: 'var(--text-color)',
                            confirmButtonColor: 'var(--orange-brand)'
                        });
                    },
                    onClose: function() {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pembayaran Dibatalkan',
                            text: 'Anda menutup popup pembayaran sebelum menyelesaikan transaksi.',
                            background: 'var(--bg-color)',
                            color: 'var(--text-color)',
                            confirmButtonColor: 'var(--orange-brand)'
                        });
                    }
                });
            });
        }

        function getPaymentStatusLabel(status) {
            const normalized = (status || '').toLowerCase();
            if (normalized === 'pending') return 'MENUNGGU PEMBAYARAN';
            if (normalized === 'paid' || normalized === 'success' || normalized === 'settlement' || normalized === 'capture') return 'PESANAN DIPROSES';
            if (normalized === 'expired') return 'KADALUWARSA';
            if (normalized === 'canceled' || normalized === 'cancel') return 'DIBATALKAN';
            if (normalized === 'denied') return 'DITOLAK';
            return 'GAGAL';
        }

        function getPaymentStatusColor(status) {
            const normalized = (status || '').toLowerCase();
            if (normalized === 'pending') return '#f59e0b'; // Amber/Orange
            if (normalized === 'paid' || normalized === 'success' || normalized === 'settlement' || normalized === 'capture') return '#10b981'; // Green
            if (normalized === 'expired' || normalized === 'canceled' || normalized === 'denied' || normalized === 'failed') return '#ef4444'; // Red
            return '#10b981';
        }

        function renderHistory() {
            if (historyData.length === 0) {
                historyList.innerHTML =
                    '<p style="color: var(--sub-text); text-align: center; padding: 50px;">Belum ada riwayat pesanan.</p>';
                return;
            }
            historyList.innerHTML = historyData.map(trx => `
                <div class="history-item" data-db-id="${trx.payment_order_db_id || ''}" onclick="showTransactionDetail('${trx.payment_order_db_id || ''}','${trx.id}')" style="display: flex; justify-content: space-between; align-items: center; background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 10px; cursor: pointer;">
                    <div>
                        <p style="font-weight: 700;">ID: #${trx.id.toString().slice(-6)}</p>
                        <p style="font-size: 0.75rem; color: var(--sub-text);">${trx.date}</p>
                        <p style="font-size: 0.85rem; margin-top: 8px;">${trx.items.map(i => `${i.qty}x ${i.name}`).join(', ')}</p>
                        <p style="font-size: 0.75rem; color: var(--sub-text); margin-top: 6px;">👤 ${trx.recipient_name || '-'} | 📞 ${trx.recipient_phone || '-'}</p>
                        <p style="font-size: 0.75rem; color: var(--sub-text); margin-top: 6px;">📍 ${trx.address || '-'}</p>
                        <p style="font-size: 0.75rem; color: var(--sub-text); margin-top: 6px;">🚚 Ongkir: ${formatRupiah(trx.shipping_fee || 0)}</p>
                    </div>
                    <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                        <span style="font-size: 1.1rem; font-weight: 800; color: var(--orange-brand);">${formatRupiah(trx.total)}</span>
                        <p style="color: ${getPaymentStatusColor(trx.payment_status)}; font-size: 0.7rem; font-weight: bold;">${getPaymentStatusLabel(trx.payment_status)}</p>
                        ${(trx.payment_status || '').toLowerCase() === 'pending' && trx.snap_token ? `
                            <button onclick="event.stopPropagation(); payPendingOrder('${trx.snap_token}', '${trx.id}')" style="background: var(--orange-brand); color: white; border: none; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(249, 115, 22, 0.3);">Bayar Sekarang</button>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }

        function payPendingOrder(token, orderId) {
            if (typeof window.snap === 'undefined') {
                Swal.fire('Error', 'Midtrans Snap belum dimuat.', 'error');
                return;
            }

            const syncUrl = document.querySelector('meta[name="sync-payment-url"]').content;
            const syncPaymentStatus = (oId) => {
                return fetch(syncUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        order_id: oId
                    })
                }).catch(err => console.error('Sync failed:', err));
            };

            window.snap.pay(token, {
                onSuccess: function(resultSnap) {
                    Swal.fire({
                        title: 'Memproses Transaksi...',
                        text: 'Sedang mensinkronkan status pembayaran Anda.',
                        allowOutsideClick: false,
                        background: 'var(--bg-color)',
                        color: 'var(--text-color)',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    syncPaymentStatus(orderId).finally(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil',
                            text: 'Status pesanan Anda telah diperbarui.',
                            confirmButtonColor: 'var(--orange-brand)',
                            background: 'var(--bg-color)',
                            color: 'var(--text-color)',
                        }).then(() => {
                            fetchHistoryFromServer().then(() => renderHistory());
                        });
                    });
                },
                onPending: function(resultSnap) {
                    Swal.fire({
                        title: 'Menunggu Konfirmasi...',
                        text: 'Sedang mengecek status pembayaran.',
                        allowOutsideClick: false,
                        background: 'var(--bg-color)',
                        color: 'var(--text-color)',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    syncPaymentStatus(orderId).finally(() => {
                        Swal.fire({
                            icon: 'info',
                            title: 'Menunggu Pembayaran',
                            text: 'Silakan selesaikan pembayaran Anda.',
                            confirmButtonColor: 'var(--orange-brand)',
                            background: 'var(--bg-color)',
                            color: 'var(--text-color)',
                        }).then(() => {
                            fetchHistoryFromServer().then(() => renderHistory());
                        });
                    });
                }
            });
        }

        // Show transaction detail modal. Try DB lookup by numeric id first, fall back to minimal data in history.
        function showTransactionDetail(dbId, legacyId) {
            const outletUuid = document.querySelector('meta[name="outlet-uuid"]').content || '';
            
            // Show fast loading animation
            Swal.fire({
                title: 'Memuat Pesanan...',
                allowOutsideClick: false,
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            if (dbId) {
                const url = `/outlet/${outletUuid}/payment-order/${encodeURIComponent(dbId)}`;
                fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.ok ? r.json() : Promise.reject(r))
                    .then(data => {
                        const order = data.order;
                        const items = order.items || [];
                        const itemsHtml = items.map(it => `
                            <div style="display:flex; justify-content:space-between; gap:8px; padding:10px 0; border-bottom:1px solid rgba(148,163,184,0.08);">
                                <div style="flex:1; min-width:0;">
                                    <div style="font-weight:700;">${escapeHtml(it.product_name)}</div>
                                    <div style="font-size:0.8rem; color:var(--sub-text);">${it.quantity} × ${formatRupiah(it.unit_price)}</div>
                                    ${it.discount_amount > 0 ? `<div style="font-size:0.8rem; color:#10b981;">Diskon: ${formatRupiah(it.discount_amount)}</div>` : ''}
                                </div>
                                <div style="text-align:right; font-weight:700;">${formatRupiah(it.final_price)}</div>
                            </div>
                        `).join('');

                        const meta = order.meta || {};
                        const orderDate = new Date(order.created_at).toLocaleString('id-ID', {
                            dateStyle: 'medium',
                            timeStyle: 'short'
                        });

                        Swal.fire({
                            title: `<div style="font-size: 1.1rem; font-weight: 800;">Detail Pesanan #${String(order.id).slice(-6)}</div>`,
                            html: `
                                <div style="text-align:left; font-size:0.9rem; line-height:1.45;">
                                    <div style="background: rgba(148,163,184,0.05); padding: 12px; border-radius: 12px; margin-bottom: 20px;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                            <span style="color: var(--sub-text);">Status:</span>
                                            <strong style="color: ${getPaymentStatusColor(order.payment_status)};">${getPaymentStatusLabel(order.payment_status)}</strong>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                            <span style="color: var(--sub-text);">Waktu:</span>
                                            <strong>${orderDate}</strong>
                                        </div>
                                        <div style="display: flex; justify-content: space-between;">
                                            <span style="color: var(--sub-text);">Metode:</span>
                                            <strong>${(order.midtrans_payment_type || order.payment_gateway || 'Midtrans').toUpperCase()}</strong>
                                        </div>
                                    </div>

                                    <div style="margin-bottom: 20px;">
                                        <p style="margin:0 0 6px 0; font-size:0.75rem; color:var(--sub-text); font-weight: 700;">PENERIMA</p>
                                        <p style="margin:0; font-weight: 700;">${escapeHtml(order.recipient_name)} | ${escapeHtml(order.recipient_phone)}</p>
                                        <p style="margin:4px 0 0 0; font-size:0.8rem; color:var(--sub-text);">${escapeHtml(order.delivery_address || '')}</p>
                                    </div>

                                    <div style="margin-bottom: 10px;">
                                        <p style="margin:0 0 6px 0; font-size:0.75rem; color:var(--sub-text); font-weight: 700;">DAFTAR ITEM</p>
                                        <div style="border-top:1px solid rgba(148,163,184,0.06);">${itemsHtml}</div>
                                    </div>

                                    <div style="margin-top:20px; background: rgba(249, 115, 22, 0.05); padding: 12px; border-radius: 12px; font-size:0.9rem;">
                                        <div style="display:flex; justify-content:space-between; margin-bottom: 4px;"><span>Subtotal</span><strong>${formatRupiah(order.subtotal_amount)}</strong></div>
                                        <div style="display:flex; justify-content:space-between; margin-bottom: 4px;"><span>Diskon Total</span><strong>${formatRupiah((meta.item_discount_total || 0) + (meta.global_discount_amount || 0))}</strong></div>
                                        <div style="display:flex; justify-content:space-between; margin-bottom: 4px;"><span>Ongkir</span><strong>${formatRupiah(order.shipping_fee || 0)}</strong></div>
                                        <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:1.1rem; color: var(--orange-brand);"><span>Total</span><strong>${formatRupiah(order.total_amount)}</strong></div>
                                    </div>

                                    <div style="margin-top: 25px; display: grid; gap: 10px;">
                                        <a href="https://wa.me/6281249414369?text=Halo Admin, saya ingin menanyakan pesanan #${String(order.id).slice(-6)}" target="_blank" style="text-decoration: none; background: #25D366; color: white; padding: 12px; border-radius: 12px; text-align: center; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                            <span>💬 Hubungi Admin</span>
                                        </a>
                                    </div>
                                </div>
                            `,
                            width: 'min(500px, 96vw)',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: 'var(--orange-brand)',
                            background: 'var(--bg-color)',
                            color: 'var(--text-color)',
                        });
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal memuat detail pesanan',
                            text: 'Silakan coba lagi nanti.',
                            confirmButtonColor: 'var(--orange-brand)'
                        });
                    });
                return;
            }

            // Fallback: cari di historyData berdasarkan legacy id
            const found = historyData.find(h => String(h.id) === String(legacyId));
            if (!found) {
                Swal.fire({
                    icon: 'info',
                    title: 'Detail tidak tersedia',
                    text: 'Detail pesanan tidak ditemukan di riwayat lokal.',
                    confirmButtonColor: 'var(--orange-brand)'
                });
                return;
            }

            const fallbackHtml = found.items.map(i => `
                <div style="display:flex; justify-content:space-between; gap:8px; padding:10px 0; border-bottom:1px solid rgba(148,163,184,0.08);">
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:700;">${escapeHtml(i.name)}</div>
                        <div style="font-size:0.8rem; color:var(--sub-text);">${i.qty} × ${formatRupiah(i.price)}</div>
                    </div>
                    <div style="text-align:right; font-weight:700;">${formatRupiah(i.qty * i.price)}</div>
                </div>
            `).join('');

            Swal.fire({
                title: `<div style="font-size: 1.1rem; font-weight: 800;">Detail Pesanan #${String(found.id).slice(-6)}</div>`,
                html: `
                    <div style="text-align:left; font-size:0.9rem; line-height:1.45;">
                        <div style="background: rgba(148,163,184,0.05); padding: 12px; border-radius: 12px; margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <span style="color: var(--sub-text);">Status:</span>
                                <strong style="color: ${getPaymentStatusColor(found.payment_status)};">${getPaymentStatusLabel(found.payment_status)}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--sub-text);">Waktu:</span>
                                <strong>${found.date || '-'}</strong>
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <p style="margin:0 0 6px 0; font-size:0.75rem; color:var(--sub-text); font-weight: 700;">PENERIMA</p>
                            <p style="margin:0; font-weight: 700;">${escapeHtml(found.recipient_name || '-')} | ${escapeHtml(found.recipient_phone || '-')}</p>
                            <p style="margin:4px 0 0 0; font-size:0.8rem; color:var(--sub-text);">${escapeHtml(found.address || '')}</p>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <p style="margin:0 0 6px 0; font-size:0.75rem; color:var(--sub-text); font-weight: 700;">DAFTAR ITEM</p>
                            <div style="border-top:1px solid rgba(148,163,184,0.06);">${fallbackHtml}</div>
                        </div>

                        <div style="margin-top:20px; background: rgba(249, 115, 22, 0.05); padding: 12px; border-radius: 12px; font-size:0.9rem;">
                            <div style="display:flex; justify-content:space-between; margin-top:4px; font-size:1.1rem; color: var(--orange-brand);"><span>Total</span><strong>${formatRupiah(found.total)}</strong></div>
                        </div>

                        <div style="margin-top: 25px;">
                            <a href="https://wa.me/6281249414369?text=Halo Admin, saya ingin menanyakan pesanan #${String(found.id).slice(-6)}" target="_blank" style="text-decoration: none; background: #25D366; color: white; padding: 12px; border-radius: 12px; text-align: center; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <span>💬 Hubungi Admin</span>
                            </a>
                        </div>
                    </div>
                `,
                width: 'min(500px, 96vw)',
                confirmButtonText: 'Tutup',
                confirmButtonColor: 'var(--orange-brand)',
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
            });
        }

        // Intersection Observer for Animations
        window.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                }
            });
        }, {
            threshold: 0.1
        });

        // Theme Menu Logic
        function toggleThemeMenu() {
            document.getElementById('themeMenu').classList.toggle('show');
        }

        function setTheme(themeName) {
            body.setAttribute('data-theme', themeName);
            localStorage.setItem('twins_theme', themeName);
            document.getElementById('themeMenu').classList.remove('show');
            updateActiveThemeBtn(themeName);
        }

        function updateActiveThemeBtn(themeName) {
            document.querySelectorAll('#themeMenu button').forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-theme-val') === themeName) {
                    btn.classList.add('active');
                }
            });
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const menu = document.getElementById('themeMenu');
            const btn = document.querySelector('.theme-btn');
            if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        }, true);

        // Initialize Theme from Storage
        const savedTheme = localStorage.getItem('twins_theme') || 'dark';
        setTheme(savedTheme);

        document.querySelectorAll('.anim-fade-up, .anim-zoom-in, .white-card').forEach(el => {
            if (!el.classList.contains('anim-fade-up') && !el.classList.contains('anim-zoom-in')) {
                el.classList.add('anim-fade-up');
            }
            window.observer.observe(el);
        });

        window.addEventListener('resize', renderCart);
        renderProducts();
        renderCart();
        syncPersistedDeliveryDistance();
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const items = ['🧁', '🥐', '🍰', '🥨', '🎂', '🍪', '🥖', '🥞', '🍩'];
            const bgContainer = document.getElementById('bakery-bg');
            let parallaxLayers = [];

            if (bgContainer) {
                // Initialize 3D Engine for Background
                bgContainer.style.perspective = '1200px';
                bgContainer.style.transformStyle = 'preserve-3d';

                for (let i = 0; i < 20; i++) {
                    const el = document.createElement('div');
                    el.className = 'walking-cake ' + (Math.random() > 0.5 ? 'dir-right' : 'dir-left');
                    el.innerText = items[Math.floor(Math.random() * items.length)];
                    el.style.top = (Math.random() * 90) + 'vh';
                    el.style.animationDuration = (Math.random() * 25 + 20) + 's';
                    el.style.animationDelay = '-' + (Math.random() * 20) + 's';
                    el.style.fontSize = (Math.random() * 2.5 + 1.5) + 'rem';

                    const wrapper = document.createElement('div');
                    wrapper.style.position = 'absolute';
                    wrapper.style.width = '100vw';
                    wrapper.style.height = '100vh';
                    wrapper.style.top = '0';
                    wrapper.style.left = '0';
                    wrapper.style.pointerEvents = 'none';
                    wrapper.style.transformStyle = 'preserve-3d';

                    const depth = Math.random() * 200 - 100; // Between -100px and +100px Z depth
                    wrapper.dataset.depthZ = depth;

                    wrapper.appendChild(el);
                    bgContainer.appendChild(wrapper);
                    parallaxLayers.push(wrapper);
                }

                // Smooth Animation Variables
                let targetX = 0,
                    targetY = 0;
                let currentX = 0,
                    currentY = 0;

                document.addEventListener("mousemove", (e) => {
                    targetX = (e.clientX - window.innerWidth / 2) * 0.08;
                    targetY = (e.clientY - window.innerHeight / 2) * 0.08;
                });

                function animate3D() {
                    currentX += (targetX - currentX) * 0.05;
                    currentY += (targetY - currentY) * 0.05;

                    // Tilt the entire bakery container & scale slightly to prevent edge cutoff
                    bgContainer.style.transform =
                        `scale(1.1) rotateX(${-currentY * 0.4}deg) rotateY(${currentX * 0.4}deg)`;

                    // Shift individual cakes based on their 3D depth to create parallax distance
                    parallaxLayers.forEach((layer) => {
                        const z = parseFloat(layer.dataset.depthZ);
                        const moveX = currentX * (z / 50);
                        const moveY = currentY * (z / 50);
                        layer.style.transform = `translate3d(${moveX}px, ${moveY}px, ${z}px)`;
                    });

                    requestAnimationFrame(animate3D);
                }
                animate3D();
            }

            const savedTheme = localStorage.getItem('twins_theme') || 'dark';
            setTheme(savedTheme);
        });
        // SweetAlert2 Session Messages
        const _sessionSuccess = document.querySelector('meta[name="session-success"]')?.content || null;
        const _sessionError = document.querySelector('meta[name="session-error"]')?.content || null;

        document.addEventListener('DOMContentLoaded', () => {
            if (_sessionSuccess) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: _sessionSuccess,
                    icon: 'success',
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    confirmButtonColor: 'var(--accent-purple)',
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            if (_sessionError) {
                Swal.fire({
                    title: 'Oops!',
                    text: _sessionError,
                    icon: 'error',
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    confirmButtonColor: 'var(--accent-pink)',
                });
            }
        });

        // --- DASHBOARD PREMIUM HEADER ANIMATION ---
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap !== 'undefined') {
                gsap.set("#mainHeader", {
                    y: -100,
                    opacity: 0
                });
                gsap.to("#mainHeader", {
                    y: 0,
                    opacity: 1,
                    duration: 1.2,
                    ease: "expo.out",
                    delay: 0.2
                });
            }
        });
        // Initial load for payment and history
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const redirectOrderId = urlParams.get('order_id');
            const redirectStatus = urlParams.get('status_code');

            if (redirectOrderId && redirectStatus) {
                Swal.fire({
                    title: 'Memperbarui Status...',
                    text: 'Sedang mensinkronkan pembayaran Anda.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                const syncUrl = document.querySelector('meta[name="sync-payment-url"]').content;
                fetch(syncUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ order_id: redirectOrderId })
                })
                .then(res => res.json())
                .then(data => {
                    Swal.close();
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                    if (isAuthenticated) fetchHistoryFromServer();
                })
                .catch(err => {
                    console.error('Auto-sync failed:', err);
                    Swal.close();
                    if (isAuthenticated) fetchHistoryFromServer();
                });
            } else {
                if (isAuthenticated) fetchHistoryFromServer();
            }

            renderProducts();
        });
        async function fetchHistoryFromServer() {
            if (!isAuthenticated) return;
            const historyUrl = document.querySelector('meta[name="user-history-url"]').content;
            try {
                const response = await fetch(historyUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    historyData = data.history || [];
                    savePersistence();
                    renderHistory();
                }
            } catch (error) {
                console.error('Failed to fetch history:', error);
                const savedHistory = localStorage.getItem('twins_history');
                if (savedHistory) {
                    try {
                        historyData = JSON.parse(savedHistory);
                    } catch (e) {}
                }
            }
        }
    </script>

    <nav class="mobile-nav">
        <div id="mob-home" class="mob-nav-item active" onclick="switchPage('home')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Beranda</span>
        </div>

        <div id="mob-cat" class="mob-nav-item" onclick="scrollToCategory()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
            <span>Kategori</span>
        </div>

        <div id="mob-history" class="mob-nav-item" onclick="switchPage('history')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Riwayat</span>
        </div>

        <div id="mob-chat" class="mob-nav-item" onclick="goToWhatsApp()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
            <span>Chat</span>
        </div>
    </nav>
</body>

</html>
