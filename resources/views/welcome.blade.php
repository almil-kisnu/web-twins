<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <meta name="session-success" content="{{ session('success') ?? '' }}">
    <meta name="session-error" content="{{ session('error') ?? '' }}">
    <title>TWINS - ahlinya belanja sembako</title>

    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- GSAP + ScrollTrigger + Lenis untuk animasi premium -->
    <script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>

    <style>
        .hero-text-clip {
            display: inline-block;
            overflow: hidden;
            vertical-align: bottom;
            line-height: 1.25;
            margin-left: 0 !important; 
        }
        
        .hero-text-clip + .hero-text-clip {
            margin-left: 0.35em !important;
        }

        #hero-word-right,
        #hero-word-right > span {
            margin-left: 0 !important;
        }
        
        #hero-paragraph { opacity: 0; }

        #hero-word-left {
            background: none !important;
            -webkit-text-fill-color: var(--text-color) !important;
            color: var(--text-color) !important;
        }

        /* --- Walking Cake Background (Black Emojis) --- */
        .walking-cake {
            position: absolute;
            opacity: 0.35;
            filter: grayscale(100%) brightness(0%);
            user-select: none;
            pointer-events: none;
            z-index: -1;
            transition: opacity 0.5s ease;
            font-family: "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", sans-serif;
        }

        .dir-right { animation: walk-right linear infinite; }
        .dir-left { animation: walk-left linear infinite; }

        @keyframes walk-right {
            0% { left: -10%; transform: translateX(0) rotate(0deg); }
            50% { transform: translateX(5vw) rotate(10deg); }
            100% { left: 110%; transform: translateX(0) rotate(0deg); }
        }

        @keyframes walk-left {
            0% { left: 110%; transform: translateX(0) rotate(0deg); }
            50% { transform: translateX(-5vw) rotate(-10deg); }
            100% { left: -10%; transform: translateX(0) rotate(0deg); }
        }

        [data-theme="light"] .walking-cake {
            opacity: 0.22;
        }

        /* Full Width Force */
        html, body {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: clip !important;
            position: relative;
            touch-action: pan-y;
            -webkit-overflow-scrolling: touch;
            left: 0 !important;
            right: 0 !important;
            box-sizing: border-box !important;
        }
        * {
            box-sizing: border-box !important;
            -webkit-tap-highlight-color: transparent;
        }
        section {
            width: 100% !important;
            max-width: 100vw !important;
            overflow: hidden !important;
            position: relative !important;
        }
        #bakery-bg, .animated-bg, .light-rays-container {
            width: 100% !important;
            max-width: 100vw !important;
            overflow: hidden !important;
        }
    </style>
    
<body class="hide-overflow">
    <div id="welcome-splash">
        <div class="splash-panel top"></div>
        <div class="splash-panel bottom"></div>
        <div class="splash-center">
            <div class="logo-wrapper">
                <div class="logo-energy-ring" id="energyRing"></div>
                <img src="{{ asset('images/logo.png') }}" alt="Twins Logo" class="splash-logo" id="splashLogo">
            </div>
            <div class="splash-text" id="splashText">
                <span class="splash-char">T</span>
                <span class="splash-char">W</span>
                <span class="splash-char">I</span>
                <span class="splash-char">N</span>
                <span class="splash-char">S</span>
            </div>
        </div>
    </div>
    <div class="animated-bg"></div>
    <div class="light-rays-container">
        <div class="god-ray ray1"></div>
        <div class="god-ray ray2"></div>
        <div class="god-ray ray3"></div>
        <div class="god-ray ray4"></div>
    </div>
    <div id="bakery-bg" style="position:fixed; top:0; left:0; width:100%; height:100vh; z-index:-1; overflow:hidden;"></div>
    <div class="glow-sphere"></div>

    <header id="mainHeader">
        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
            <span class="logo-text">TWINS</span>
        </div>

        <nav class="main-nav" id="mainNav">
            <a href="#heroBadge" class="nav-link" id="nav-home">Beranda</a>
            <a href="#promoTitle" class="nav-link" id="nav-promo">Promo</a>
            <a href="#outletTitle" class="nav-link" id="nav-outlet">Outlet</a>
            <a href="#featuresTitle" class="nav-link" id="nav-features">Keunggulan</a>
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
                        <form method="POST" action="{{ route('logout') }}" style="display: none;" id="logout-form-header-mob">
                            @csrf
                        </form>
                        <button onclick="document.getElementById('logout-form-header-mob').submit();" style="display: flex; align-items: center; color: #ef4444;">
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
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
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

    <section id="beranda">
        <main class="hero anim-fade-up">
            <div class="badge" id="heroBadge">TWINS by Kelompok 4</div>
            <h1 id="hero-title">
                <span class="hero-text-clip"><span id="hero-word-left">Belanja Mudah</span></span><span class="hero-text-clip"><span id="hero-word-right"><span>Dimana Saja</span></span></span>
            </h1>
            <p id="hero-paragraph">Setiap outlet punya pilihan terbaiknya masing-masing. Pilih outlet terdekatmu sekarang dan mulai belanja bahan kue dengan lebih cepat, mudah, dan praktis.</p>

            <div class="nft-container anim-zoom-in" id="nftContainer">
                @php
                    $heroOutlets = $outlets->take(5);
                    // Ensure we have at least 5 cards for the 3D stack effect by repeating if necessary
                    if ($heroOutlets->count() > 0 && $heroOutlets->count() < 5) {
                        $count = $heroOutlets->count();
                        for ($i = $count; $i < 5; $i++) {
                            $heroOutlets->push($heroOutlets[$i % $count]);
                        }
                    }
                @endphp
                
                @foreach($heroOutlets as $index => $heroOutlet)
                <div class="nft-card">
                    <img src="{{ asset('images/toko'.(($index % 5) + 1).'.jpg') }}" alt="Store Image">
                </div>
                @endforeach
            </div>
        </main>
    </section>

    <section id="promo-outlet" class="promo-section" style="padding: 30px 0; overflow: hidden; background: transparent;">
        <div class="promo-header" style="margin-bottom: 20px; text-align: center;">
            <h2 id="promoTitle" style="font-size: 28px; letter-spacing: 2px; color: var(--text-color); margin: 0; text-transform: uppercase; font-weight: 800;">
                PROMO <span style="color: var(--accent-purple);">UNGGULAN</span>
            </h2>
            <p style="color: var(--sub-text); margin-top: 10px;">Penawaran spesial terbaik hanya untuk Anda</p>
        </div>

        <div class="promo-carousel-wrapper">
            <button class="promo-nav-btn prev" id="prevPromo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            
            <div class="promo-carousel-slider" id="promoSliderMain">
                @forelse($promoProducts as $promo)
                <div class="promo-carousel-item">
                    @if(isset($promo->diskon) && $promo->diskon > 0)
                        <div class="promo-badge-premium">Diskon {{ $promo->diskon }}%</div>
                    @endif
                    <img src="{{ $promo->image_banner }}" alt="{{ $promo->nama_promo }}">
                </div>
                @empty
                <div style="text-align: center; width: 100%; padding: 40px 20px; color: var(--sub-text); font-size: 16px; background: var(--card-bg); border-radius: 20px;">
                    Belum ada promo aktif saat ini.
                </div>
                @endforelse
            </div>

            <button class="promo-nav-btn next" id="nextPromo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
        </div>

        <script>
            function movePromo(direction) {
                const slider = document.getElementById('promoSliderMain');
                if (!slider) return;
                
                const itemWidth = slider.querySelector('.promo-carousel-item')?.offsetWidth || slider.offsetWidth;
                const scrollAmount = itemWidth + 20; // width + gap
                
                if (direction === 'next') {
                    const isAtEnd = slider.scrollLeft + slider.offsetWidth >= slider.scrollWidth - 20;
                    if (isAtEnd) {
                        slider.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                        slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                    }
                } else {
                    const isAtStart = slider.scrollLeft <= 10;
                    if (isAtStart) {
                        slider.scrollTo({ left: slider.scrollWidth, behavior: 'smooth' });
                    } else {
                        slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                    }
                }
            }

            // Bind buttons
            document.getElementById('prevPromo')?.addEventListener('click', () => movePromo('prev'));
            document.getElementById('nextPromo')?.addEventListener('click', () => movePromo('next'));

            // Auto-play
            let promoAutoPlay = setInterval(() => { movePromo('next'); }, 6000);
            
            const sliderWrap = document.querySelector('.promo-carousel-wrapper');
            if(sliderWrap) {
                sliderWrap.addEventListener('mouseenter', () => clearInterval(promoAutoPlay));
                sliderWrap.addEventListener('mouseleave', () => {
                    clearInterval(promoAutoPlay);
                    promoAutoPlay = setInterval(() => { movePromo('next'); }, 6000);
                });
            }

            // Intersection Observer to ensure autoplay only when in view
            const promoObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // restart autoplay if needed
                    } else {
                        clearInterval(promoAutoPlay);
                    }
                });
            }, { threshold: 0.2 });
            if(sliderWrap) promoObserver.observe(sliderWrap);
        </script>
    </section>

    <section id="outlet" class="explore-section">
        <h2 id="outletTitle" data-split-text>Pilih Cabang <span>Terdekatmu</span></h2>

        <div class="nft-grid" data-stagger-grid>
            @foreach($outlets as $index => $outlet)
            <div class="nft-item float-hover {{ $index === 1 ? 'featured' : '' }}" data-stagger-item>
                <div class="owner-info">
                    <div class="owner-details">
                        <p>Outlet TWINS</p>
                        <p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">Cabang {{ $outlet->nama }}</p>
                    </div>
                </div>
                <div class="nft-item-img" data-parallax-wrap>
                    <img src="{{ asset('images/toko'.(($index % 5) + 1).'.jpg') }}" data-parallax>
                </div>
                <h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $outlet->nama }}</h4>
                <div class="bid-box">
                    <div class="bid-info" style="flex: 1; min-width: 0;">
                        <p>TWINS</p>
                        <p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">📍 {{ $outlet->alamat }}</p>
                        <p>🕒 {{ $outlet->jam_buka }}</p>
                        <p>⭐ {{ number_format($outlet->rating, 1) }}</p>
                    </div>
                    <a href="{{ route('user.index', $outlet->uuid) }}" class="btn-action" style="text-decoration: none; text-align: center;">
                        Pilih
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    
    <section id="tentang-toko" class="highlight-section">
        <div class="highlight-header">
            <h2 data-split-text>Tentang Toko</h2>
        </div>

        <div class="highlight-container">
            <!-- BOX 1: MEDIA BOX (Sekarang di atas untuk mobile) -->
            <div class="highlight-media-box" data-reveal-right>
                <div class="media-item image-item" data-parallax-wrap>
                    <img src="{{ asset('images/toko5.jpg') }}" alt="Store Gallery" class="main-media" data-parallax>
                    <div class="media-badge">Galeri Store</div>
                </div>

                <div class="media-group-right">
                    <div class="media-item video-item">
                        <img src="{{ asset('images/toko-luar.png') }}" alt="Video Preview" class="main-media">
                        <div class="play-btn">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="30" height="30">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="video-meta">
                        <p>Suasana Hangat Toko Twins</p>
                        <div class="action-wrap">
                            <button class="btn-highlights-sm">Lihat Selengkapnya <span>→</span></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOX 2: TEXT BOX -->
            <div class="highlight-text-box" data-reveal-left>
                <div class="owner-profile">
                    <div class="owner-avatar">
                        <img src="{{ asset('images/logo.png') }}" alt="Twins Owner">
                    </div>
                    <div class="owner-meta">
                        <h4>Twins Bakery Team</h4>
                        <p>Kualitas & Kepercayaan</p>
                    </div>
                </div>

                <div class="star-rating">
                    <span class="stars">★★★★★</span>
                </div>

                <div class="story-content">
                    <h3 class="highlight-title">Perjalanan Menghadirkan Bahan Kue Terbaik</h3>
                    <p class="highlight-desc">Berawal dari semangat untuk mendukung setiap kreator kue di Indonesia, Twins menghadirkan bahan-bahan berkualitas pilihan. Kami percaya bahwa setiap adonan punya cerita, dan setiap cerita layak mendapatkan hasil terbaik. Tekstur sempurna dan rasa yang autentik dimulai dari sini, membawa kebahagian dari setiap panggangan kami ke meja Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="keunggulan" class="product-features-section">
        <h2 id="featuresTitle" class="heading" data-split-text>
            Kenapa Belanja di Twins<br>Lebih Mudah &amp; Menyenangkan?
        </h2>

        <div class="grid-container">
            <div class="feature-list left-side">
                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3h18v18H3z"></path>
                            <path d="M7 12h10"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Produk Lengkap</h3>
                    <p class="feature-description">
                        Semua kebutuhan baking kamu tersedia di satu tempat, dari bahan dasar sampai dekorasi kue.
                    </p>
                </article>

                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v20"></path>
                            <path d="M5 12h14"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Harga Terjangkau</h3>
                    <p class="feature-description">
                        Belanja bahan kue tanpa khawatir mahal, dengan harga bersahabat untuk semua kalangan.
                    </p>
                </article>
            </div>

            <div class="product-image-container">
                <div class="featured-product-image" data-parallax-wrap>
                    <img src="{{ asset('images/toko4.jpg') }}" alt="Produk Unggulan Twins" style="width:100%;height:100%;object-fit:cover;border-radius:20px;" data-parallax>
                </div>
            </div>

            <div class="feature-list right-side">
                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Pengiriman Cepat</h3>
                    <p class="feature-description">
                        Pesanan kamu diproses dengan cepat agar bisa langsung dipakai untuk baking tanpa nunggu lama.
                    </p>
                </article>

                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 1l3 5h6l-4.5 4 2 6-6-3.5L6.5 16l2-6L4 6h6z"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Kualitas Terjamin</h3>
                    <p class="feature-description">
                        Produk berkualitas tinggi yang aman dan terpercaya untuk hasil baking yang maksimal.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS MARQUEE SECTION -->
    <section id="testimonials" class="testimonials-marquee-section">
        <div class="marquee-header" data-reveal-up>
            <h2 data-split-text>Suara <span>Pelanggan</span></h2>
            <p data-reveal-up>Apa kata mereka yang sudah merasakan manisnya belanja di Twins Bakery?</p>
        </div>

        <div class="marquee-container">
            <!-- Row 1: To the Right -->
            <div class="marquee-row marquee-row-right">
                <div class="marquee-track" id="trackTop">
                    @php $row1 = $testimonials->shuffle(); @endphp
                    @foreach($row1 as $testi)
                    <div class="testimonial-item-card">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- Cloning 2nd set -->
                    @foreach($row1 as $testi)
                    <div class="testimonial-item-card marquee-clone">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- Cloning 3rd set (Security for small data) -->
                    @foreach($row1 as $testi)
                    <div class="testimonial-item-card marquee-clone">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Row 2: To the Left -->
            <div class="marquee-row marquee-row-left">
                <div class="marquee-track" id="trackBottom">
                    @php $row2 = $testimonials->shuffle(); @endphp
                    @foreach($row2 as $testi)
                    <div class="testimonial-item-card">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- Cloning 2nd set -->
                    @foreach($row2 as $testi)
                    <div class="testimonial-item-card marquee-clone">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- Cloning 3rd set -->
                    @foreach($row2 as $testi)
                    <div class="testimonial-item-card marquee-clone">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Gradient Overlays for smooth entry/exit -->
            <div class="marquee-overlay marquee-overlay-left"></div>
            <div class="marquee-overlay marquee-overlay-right"></div>
        </div>

        <div class="add-review-cta">
            <div class="cta-inner">
                <p>Ingin berbagi pengalaman belanja Anda?</p>
                @auth
                    <button onclick="openReviewModal()" class="btn-fill main-cta">Tambah Komentar Anda <span>→</span></button>
                @else
                    <a href="{{ route('login') }}" class="btn-fill main-cta">Login untuk Menambah Komentar <span>→</span></a>
                @endauth
            </div>
        </div>
    </section>

    <!-- REVIEW MODAL -->
    @auth
    <div class="modal-overlay" id="reviewModal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeReviewModal()">×</button>
            <div class="modal-header">
                <h3>Beri Ulasan <span>Twins Bakery</span></h3>
                <p>Pilih cabang dan bagikan pengalaman manismu!</p>
            </div>

            <form action="{{ route('landing.review.store') }}" method="POST" id="reviewForm">
                @csrf
                <input type="hidden" name="store_id" id="selectedStoreId" required>

                <div class="modal-body">
                    <!-- Step 1: Select Outlet Grid -->
                    <label class="form-label">1. Pilih Cabang</label>
                    <div class="outlet-selection-grid">
                        @foreach($outlets as $outlet)
                        <div class="outlet-option-card" data-id="{{ $outlet->uuid }}" onclick="selectOutlet('{{ $outlet->uuid }}', this)">
                            <div class="outlet-check">✓</div>
                            <div class="outlet-info-mini">
                                <strong>{{ $outlet->nama }}</strong>
                                <span>{{ Str::limit($outlet->alamat, 30) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Step 2: Rating -->
                    <label class="form-label">2. Berikan Bintang</label>
                    <div class="rating-selector-modal">
                        <input type="radio" name="rating" value="5" id="modal-star5"><label for="modal-star5">★</label>
                        <input type="radio" name="rating" value="4" id="modal-star4"><label for="modal-star4">★</label>
                        <input type="radio" name="rating" value="3" id="modal-star3"><label for="modal-star3">★</label>
                        <input type="radio" name="rating" value="2" id="modal-star2"><label for="modal-star2">★</label>
                        <input type="radio" name="rating" value="1" id="modal-star1" required><label for="modal-star1">★</label>
                    </div>

                    <!-- Step 3: Comment -->
                    <label class="form-label">3. Tulis Komentar</label>
                    <textarea name="comment" placeholder="Ceritakan pengalamanmu di toko ini..." rows="4"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-outline" onclick="closeReviewModal()">Batal</button>
                    <button type="submit" class="btn-fill">Kirim Ulasan</button>
                </div>
            </form>
        </div>
    </div>
    @endauth

    <!-- MODERN 3-COLUMN FOOTER -->
    <footer class="main-footer">
        <div class="footer-container">
            <!-- Col 1: Identity -->
            <div class="footer-col footer-identity">
                <div class="footer-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Twins Logo">
                    <span>TWINS</span>
                </div>
                <p class="footer-desc">Solusi terpercaya untuk kebutuhan bahan kue dan sembako berkualitas. Kami hadir di berbagai cabang untuk melayani kebutuhan dapur Anda dengan sepenuh hati.</p>
                <div class="social-links">
                    <a href="https://www.instagram.com/sweetbake.official?igsh=MTl3dW5pY3J6aHEyYg==" target="_blank" class="social-icon" title="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                    <a href="#" target="_blank" class="social-icon" title="Youtube">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.42a2.78 2.78 0 0 0-1.94 2C1 8.11 1 12 1 12s0 3.89.4 5.58a2.78 2.78 0 0 0 1.94 2c1.71.42 8.6.42 8.6.42s6.88 0 8.6-.42a2.78 2.78 0 0 0 1.94-2C23 15.89 23 12 23 12s0-3.89-.46-5.58z"></path><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"></polygon></svg>
                    </a>
                    <a href="https://wa.me/6282330755390" target="_blank" class="social-icon" title="WhatsApp">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .004 5.411.002 12.046c0 2.121.54 4.192 1.566 6.033L0 24l6.135-1.61a11.81 11.81 0 005.911 1.569h.005c6.632 0 12.042-5.411 12.045-12.047a11.812 11.812 0 00-3.576-8.514z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Col 2: Other Page -->
            <div class="footer-col">
                <h4>Halaman Lain</h4>
                <ul class="footer-links">
                    <li><a href="#beranda" onclick="switchPage('beranda')">Beranda</a></li>
                    <li><a href="#promo-outlet" onclick="switchPage('promo-outlet')">Promo Spesial</a></li>
                    <li><a href="#outlet" onclick="scrollToCategory('outlet')">Cabang Kami</a></li>
                    <li><a href="#tentang-toko" onclick="smoothScroll('#tentang-toko')">Tentang Toko</a></li>
                    <li><a href="#keunggulan" onclick="smoothScroll('#keunggulan')">Keunggulan Kami</a></li>
                    <li><a href="#testimonials" onclick="smoothScroll('#testimonials')">Komentar Pelanggan</a></li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} TWINS Bakery - Premium Quality Baking Supplies. All Rights Reserved.</p>
        </div>
    </footer>

    <nav class="mobile-nav">
        <div id="mob-home" class="mob-nav-item active" onclick="switchPage('beranda')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Beranda</span>
        </div>

        <div id="mob-promo" class="mob-nav-item" onclick="switchPage('promo-outlet')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>
            <span>Promo</span>
        </div>

        <div id="mob-outlet" class="mob-nav-item" onclick="scrollToCategory('outlet')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l18 0l-1 10l-16 0z"></path><path d="M3 11l18 0"></path><path d="M2 3l20 0l-1 6l-18 0z"></path></svg>
            <span>Outlet</span>
        </div>

        <div id="mob-features" class="mob-nav-item" onclick="switchPage('keunggulan')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
            <span>Keunggulan</span>
        </div>
    </nav>

    <script>
    (function() {
        
        function initFinalReliability() {
            if (window._twinsStarted) return;
            window._twinsStarted = true;
            
            if (typeof gsap === 'undefined') {
                console.error("[TWINS] GSAP missing.");
                return;
            }

            console.log("[TWINS] Cinematic Engine Initialized");

            // --- PER-PAGE VISIBILITY (Prevent Flicker) ---
            gsap.set("header", { y: -100, opacity: 0 });
            gsap.set("section#beranda", { opacity: 0 });

            const parseTransform = (str) => {
                str = str || '';
                const sm = str.match(/scale\(([\d.]+)\)/);
                const rm = str.match(/rotate\(([-\d.]+)deg\)/);
                return { scale: sm ? parseFloat(sm[1]) : 1, rotation: rm ? parseFloat(rm[1]) : 0 };
            };

            const runHeroReveal = () => {
                try {
                    console.log("[TWINS] Triggering High-Impact Reveal Sequence...");
                    const badge = document.getElementById('hero-badge');
                    const wordLeft = document.getElementById('hero-word-left');
                    const wordRight = document.getElementById('hero-word-right');
                    const paragraph = document.getElementById('hero-paragraph');
                    const cards = Array.from(document.querySelectorAll('#nftContainer .nft-card'));
                    const clips = document.querySelectorAll('.hero-text-clip');

                    if (!cards.length) return;

                    // I. Initial State (Clean & Fast)
                    gsap.set(["#hero-badge", "#hero-word-left", "#hero-word-right", "#hero-paragraph"], { autoAlpha: 0 });
                    gsap.set(clips, { overflow: 'visible' }); // No clipping during entry
                    
                    const finals = cards.map(card => {
                        const t = parseTransform(card.style.transform);
                        return {
                            left: card.style.left || '50%', top: card.style.top || '50%',
                            transform: card.style.transform || 'translate(-50%,-50%)',
                            scale: t.scale, rotation: t.rotation,
                            opacity: parseFloat(card.style.opacity) || 1,
                            zIndex: card.style.zIndex || '1'
                        };
                    });

                    // II. Prepare Elements
                    cards.forEach(card => {
                        card.style.transition = 'none';
                        card.style.left = '50%'; card.style.top = '50%';
                        card.style.zIndex = '5'; card.style.transform = '';
                        gsap.set(card, { xPercent: -50, yPercent: -50, scale: 0.1, rotation: 0, autoAlpha: 0 });
                    });

                    // AMPLIFIED OFFSETS
                    if (badge) gsap.set(badge, { y: -120 });
                    if (wordLeft) gsap.set(wordLeft, { x: -150 });
                    if (wordRight) gsap.set(wordRight, { x: 150 });
                    if (paragraph) gsap.set(paragraph, { y: 40 });

                    const htl = gsap.timeline({ defaults: { ease: 'power4.out' } });

                    // III. THE SHOW (Parallel Action)
                    // Start Text & Cards together for energy
                    htl.to(cards, { scale: 0.75, autoAlpha: 1, duration: 0.8, stagger: 0.05, ease: "back.out(1.7)" }, 0);
                    
                    if (badge) htl.to(badge, { y: 0, autoAlpha: 1, duration: 1.2, ease: 'elastic.out(1, 0.6)' }, 0.1);
                    if (wordLeft) htl.to(wordLeft, { x: 0, autoAlpha: 1, duration: 1.4 }, 0.2);
                    if (wordRight) htl.to(wordRight, { x: 0, autoAlpha: 1, duration: 1.4 }, 0.3);
                    if (paragraph) htl.to(paragraph, { autoAlpha: 1, y: 0, duration: 1.6 }, 0.5);

                    // Opening Arc (Dramatic Sweep)
                    cards.forEach((card, i) => {
                        const f = finals[i];
                        htl.to(card, {
                            left: f.left, top: f.top, scale: f.scale, rotation: f.rotation, autoAlpha: f.opacity,
                            duration: 1.8, ease: 'expo.out',
                            onStart: () => { card.style.zIndex = f.zIndex; },
                            onComplete: () => {
                                gsap.set(card, { clearProps: 'all' });
                                card.style.cssText = `left:${f.left}; top:${f.top}; transform:${f.transform}; opacity:${f.opacity}; z-index:${f.zIndex};`;
                            }
                        }, 0.8 + (i * 0.1));
                    });

                    // Cleanup states
                    htl.set(clips, { overflow: 'hidden' }, "+=0.2");

                } catch (e) {
                    console.error("[TWINS] Hero Animation Error:", e);
                    gsap.set(["section#beranda", "#hero-badge", "h1", "p", ".nft-card"], { autoAlpha: 1 });
                }
            };

            window.twinsHeroManual = runHeroReveal;

            // SPLASH TIMELINE
            const stl = gsap.timeline({
                onComplete: () => {
                    document.getElementById('welcome-splash').style.display = 'none';
                    document.body.classList.remove('hide-overflow');
                    document.body.classList.add('show-content');
                }
            });

            stl.to("#splashLogo", { scale: 1, opacity: 1, duration: 0.6, ease: "expo.out", filter: "brightness(2) contrast(1.5)" })
               .to("#splashLogo", { filter: "brightness(1) contrast(1)", duration: 0.4 }, "-=0.2")
               .to(".splash-char", { opacity: 1, y: 0, rotateX: 0, duration: 0.8, stagger: 0.08, ease: "power4.out" }, "-=0.4")
               .set("#energyRing", { opacity: 1 })
               .to("#energyRing", { rotate: 270, scale: 1.3, opacity: 0.6, duration: 1.2, ease: "power2.out" }, "-=0.5")
               .to("#splashText", { opacity: 0, scale: 0.8, duration: 0.4, ease: "power2.in" }, "+=0.3")
               .to("#splashLogo", { scale: 0, opacity: 0, duration: 0.5, ease: "back.in(1.5)" }, "-=0.2")
               .to("#energyRing", { scale: 2.5, opacity: 0, duration: 0.6, ease: "expo.out" }, "<")
               .to(".splash-panel.top", { yPercent: -100, duration: 1.4, ease: "expo.inOut" }, "+=0.1")
               .to(".splash-panel.bottom", { yPercent: 100, duration: 1.4, ease: "expo.inOut" }, "<")
               .to("section#beranda", { opacity: 1, duration: 0.8, ease: "power2.out" }, "-=1.0")
               
               // PRECISE SYNC: Trigger hero reveal earlier (1.2s before end)
               .add(() => {
                   console.log("[TWINS] Double-Trigger: Cinematic Reveal Started");
                   runHeroReveal();
               }, "-=1.2")

               .to("header", { y: 0, opacity: 1, duration: 1.0, ease: "expo.out" }, "-=0.6")
               .set("body", { onStart: () => {
                   document.body.classList.add('show-content');
                   if (typeof ScrollTrigger !== 'undefined') ScrollTrigger.refresh();
               }}, "-=0.2");
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            initFinalReliability();
        } else {
            document.addEventListener('DOMContentLoaded', initFinalReliability);
        }
    })();
    </script>

    <script>
        const themeBtn = document.getElementById('themeBtn');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        const body = document.body;
        const cards = Array.from(document.querySelectorAll('.nft-card'));
        const menuToggle = document.getElementById('menuToggle');
        const mainNav = document.getElementById('mainNav');

        let activeIndex = Math.floor(cards.length / 2);

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
        let startX = 0;
        let isDragging = false;
        let dragOffset = 0;

        function updateLayout(duration = 0.8) {
            const isMobile = window.innerWidth <= 768;
            const horizontalGap = isMobile ? 85 : 110;
            const radiusY = isMobile ? 25 : 40;
            const rotationAngle = isMobile ? 12 : 15;

            cards.forEach((card, i) => {
                const diff = i - activeIndex;
                const absDiff = Math.abs(diff);

                card.classList.remove('active');

                if (diff === 0) {
                    card.classList.add('active');
                    gsap.to(card, {
                        left: '50%',
                        top: '50%',
                        xPercent: -50,
                        yPercent: -50,
                        scale: 1.2,
                        rotation: 0,
                        zIndex: 500,
                        opacity: 1,
                        duration: duration,
                        ease: "power3.out"
                    });
                } else {
                    const x = 50 + (diff * (horizontalGap / 10));
                    const yOffset = absDiff * absDiff * (radiusY / 10);

                    const scale = 1 - (absDiff * 0.1);
                    const rotate = diff * rotationAngle;
                    const opacity = Math.max(1 - (absDiff * 0.2), 0.4);

                    gsap.to(card, {
                        left: `${x}%`,
                        top: `calc(50% + ${yOffset}px)`,
                        xPercent: -50,
                        yPercent: -50,
                        scale: scale,
                        rotation: rotate,
                        zIndex: 100 - absDiff,
                        opacity: opacity,
                        duration: duration,
                        ease: "power3.out"
                    });
                }
            });
        }

        const nftContainer = document.getElementById('nftContainer');
        if (nftContainer) {
            const handleDragStart = (e) => {
                isDragging = true;
                startX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                dragOffset = 0;
                nftContainer.style.cursor = 'grabbing';
            };

            const handleDragMove = (e) => {
                if (!isDragging) return;
                const currentX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                dragOffset = currentX - startX;
            };

            const handleDragEnd = () => {
                if (!isDragging) return;
                isDragging = false;
                nftContainer.style.cursor = 'grab';

                const threshold = 50;
                if (dragOffset > threshold && activeIndex > 0) {
                    activeIndex--;
                } else if (dragOffset < -threshold && activeIndex < cards.length - 1) {
                    activeIndex++;
                }
                updateLayout();
            };

            nftContainer.addEventListener('mousedown', handleDragStart);
            window.addEventListener('mousemove', handleDragMove);
            window.addEventListener('mouseup', handleDragEnd);

            nftContainer.addEventListener('touchstart', handleDragStart, { passive: true });
            window.addEventListener('touchmove', handleDragMove, { passive: true });
            window.addEventListener('touchend', handleDragEnd);
            
            nftContainer.style.cursor = 'grab';
        }

        cards.forEach((card, index) => {
            card.addEventListener('click', () => {
                if (Math.abs(dragOffset) < 10) {
                    activeIndex = index;
                    updateLayout();
                }
            });
        });

        function switchPage(pageId) {
            const element = document.getElementById(pageId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
            
            // Manual active for mobile bottom nav
            const mobItems = document.querySelectorAll('.mob-nav-item');
            mobItems.forEach(item => item.classList.remove('active'));
            
            // Find which one was clicked based on pageId
            if(pageId === 'beranda') document.getElementById('mob-home')?.classList.add('active');
            else if(pageId === 'promo-outlet') document.getElementById('mob-promo')?.classList.add('active');
            else if(pageId === 'keunggulan') document.getElementById('mob-features')?.classList.add('active');
        }

        function scrollToCategory(id) {
            const element = document.getElementById(id);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
            
            // Manual active for Outlet in bottom nav
            if(id === 'outlet') {
                document.querySelectorAll('.mob-nav-item').forEach(item => item.classList.remove('active'));
                document.getElementById('mob-outlet')?.classList.add('active');
            }
        }

        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        cards.forEach((card, index) => {
            card.addEventListener('click', () => {
                activeIndex = index;
                updateLayout();
            });
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
                if(btn.getAttribute('data-theme-val') === themeName) {
                    btn.classList.add('active');
                }
            });
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const menu = document.getElementById('themeMenu');
            const btn = document.querySelector('.theme-btn');
            // Menutup dropdown user atau theme menu jika diklik di luar
            const userMenu = document.getElementById('userMenu');
            const userBtn = document.querySelector('.user-icon-btn');
            if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
            if (userMenu && userBtn && !userBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.remove('show');
            }
        }, true); // Use capture phase to ensure it runs properly

        // Initialize Theme from Storage
        const savedTheme = localStorage.getItem('twins_theme') || 'dark';
        setTheme(savedTheme);

        // Intersection Observer for Animations - Cleaned up

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.anim-fade-up, .anim-zoom-in').forEach(el => observer.observe(el));

        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                mainNav.classList.remove('active');
            });
        });

        window.addEventListener('resize', updateLayout);
        updateLayout();

        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);

            if (params.get('verified') === '1') {
                Swal.fire({
                    title: 'Verifikasi Berhasil!',
                    text: 'Selamat bergabung di TWINS! Akun Anda sudah aktif.',
                    icon: 'success',
                    confirmButtonColor: '#0477bf',
                    showClass: {
                        popup: 'animate__animated animate__zoomIn'
                    }
                });

                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const items = ['🧁', '🥐', '🍰', '🥨', '🎂', '🍪', '🥖', '🥞', '🍩'];
            const bgContainer = document.getElementById('bakery-bg');
            let parallaxLayers = [];

            if(bgContainer) {
                const isMobile = window.innerWidth <= 768;
                const layerCount = isMobile ? 10 : 20;
                
                // Initialize 3D Engine for Background
                bgContainer.style.perspective = '1200px';
                bgContainer.style.transformStyle = 'preserve-3d';

                for(let i = 0; i < layerCount; i++) {
                    const el = document.createElement('div');
                    el.className = 'walking-cake ' + (Math.random() > 0.5 ? 'dir-right' : 'dir-left');
                    el.innerText = items[Math.floor(Math.random() * items.length)];
                    el.style.top = (Math.random() * 90) + 'vh';
                    el.style.animationDuration = (Math.random() * 25 + 20) + 's';
                    el.style.animationDelay = '-' + (Math.random() * 20) + 's';
                    el.style.fontSize = (Math.random() * 2.5 + 1.5) + 'rem';
                    
                    const wrapper = document.createElement('div');
                    wrapper.style.position = 'absolute';
                    wrapper.style.width = '100%';
                    wrapper.style.height = '100vh';
                    wrapper.style.top = '0';
                    wrapper.style.left = '0';
                    wrapper.style.pointerEvents = 'none';
                    wrapper.style.transformStyle = 'preserve-3d';
                    wrapper.style.willChange = 'transform';
                    
                    const depth = Math.random() * 200 - 100;
                    wrapper.dataset.depthZ = depth;
                    
                    wrapper.appendChild(el);
                    bgContainer.appendChild(wrapper);
                    parallaxLayers.push(wrapper);
                }

                let targetX = 0, targetY = 0;
                let currentX = 0, currentY = 0;
                let rafId = null;
                let isIdle = true;

                document.addEventListener("mousemove", (e) => {
                    targetX = (e.clientX - window.innerWidth / 2) * 0.08;
                    targetY = (e.clientY - window.innerHeight / 2) * 0.08;
                    if (isIdle) {
                        isIdle = false;
                        if (!rafId) rafId = requestAnimationFrame(animate3D);
                    }
                });

                function animate3D() {
                    const dx = targetX - currentX;
                    const dy = targetY - currentY;
                    
                    currentX += dx * 0.05;
                    currentY += dy * 0.05;

                    bgContainer.style.transform = `scale(1.1) rotateX(${-currentY * 0.4}deg) rotateY(${currentX * 0.4}deg)`;

                    parallaxLayers.forEach((layer) => {
                        const z = parseFloat(layer.dataset.depthZ);
                        const moveX = currentX * (z / 50); 
                        const moveY = currentY * (z / 50);
                        layer.style.transform = `translate3d(${moveX}px, ${moveY}px, ${z}px)`;
                    });

                    // Stop loop if motion is negligible
                    if (Math.abs(dx) < 0.01 && Math.abs(dy) < 0.01) {
                        isIdle = true;
                        rafId = null;
                        return;
                    }

                    rafId = requestAnimationFrame(animate3D);
                }
                
                // Initial run
                rafId = requestAnimationFrame(animate3D);
            }

            const savedTheme = localStorage.getItem('twins_theme') || 'dark';
            setTheme(savedTheme);
        });
        // Dual-Row Marquee Logic
        class TestimonialMarquee {
            constructor(rowSelector, speed = 1) {
                this.row = document.querySelector(rowSelector);
                this.speed = speed;
                this.isPaused = false;
                this.isDragging = false;
                this.startX = 0;
                this.scrollLeft = 0;
                this.init();
            }

            init() {
                // Diperbarui: Hapus listener pause agar ulasan jalan terus tanpa henti
                
                this.row.addEventListener('mousedown', (e) => this.startDragging(e));
                window.addEventListener('mouseup', () => this.stopDragging());
                window.addEventListener('mousemove', (e) => this.drag(e));

                this.row.addEventListener('touchstart', (e) => this.startDragging(e.touches[0]), { passive: true });
                window.addEventListener('touchend', () => this.stopDragging());
                window.addEventListener('touchmove', (e) => this.drag(e.touches[0]));

                this.animate();
            }

            startDragging(e) {
                this.isDragging = true;
                this.isPaused = true;
                this.startX = e.pageX - this.row.offsetLeft;
                this.scrollLeft = this.row.scrollLeft;
            }

            stopDragging() {
                this.isDragging = false;
                this.isPaused = false; // Lanjut jalan setelah dilepas
            }

            drag(e) {
                if (!this.isDragging) return;
                const x = e.pageX - this.row.offsetLeft;
                const walk = (x - this.startX) * 1.5;
                this.row.scrollLeft = this.scrollLeft - walk;
            }

            animate() {
                if (!this.isPaused && !this.isDragging && this.isVisible) {
                    this.row.scrollLeft += this.speed;

                    const loopPoint = this.row.scrollWidth / 3;
                    
                    if (this.speed > 0 && this.row.scrollLeft >= loopPoint) {
                        this.row.scrollLeft = 0;
                    } else if (this.speed < 0 && this.row.scrollLeft <= 0) {
                        this.row.scrollLeft = loopPoint;
                    }
                }
                requestAnimationFrame(() => this.animate());
            }

            initObserver() {
                this.isVisible = false;
                const obs = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        this.isVisible = entry.isIntersecting;
                    });
                }, { threshold: 0.01 });
                obs.observe(this.row);
            }
        }

        // Modified init to include observer
        const originalInit = TestimonialMarquee.prototype.init;
        TestimonialMarquee.prototype.init = function() {
            this.initObserver();
            originalInit.call(this);
        };

        // Initialize Rows: ATAS KE KANAN (speed negatif), BAWAH KE KIRI (speed positif)
        window.onload = () => {
            const rowTop = new TestimonialMarquee('.marquee-row-right', -0.8); 
            const rowBottom = new TestimonialMarquee('.marquee-row-left', 0.8);
            
            // Set posisi awal random agar tidak terlihat terlalu sinkron di awal
            const topTrack = document.querySelector('.marquee-row-right');
            const bottomTrack = document.querySelector('.marquee-row-left');
            if(topTrack) topTrack.scrollLeft = topTrack.scrollWidth / 3;
            if(bottomTrack) bottomTrack.scrollLeft = (bottomTrack.scrollWidth / 3) * 0.5;
        };

        // Modal Functions
        function openReviewModal() {
            const modal = document.getElementById('reviewModal');
            if(modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Prevent scroll
            }
        }

        function closeReviewModal() {
            const modal = document.getElementById('reviewModal');
            if(modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function selectOutlet(id, element) {
            // Remove active class from all options
            document.querySelectorAll('.outlet-option-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add to clicked one
            element.classList.add('selected');
            
            // Set hidden value
            document.getElementById('selectedStoreId').value = id;
        }

        // Close on overlay click
        window.onclick = function(event) {
            const modal = document.getElementById('reviewModal');
            if (event.target == modal) {
                closeReviewModal();
            }
        }

        function smoothScroll(target) {
            const element = document.querySelector(target);
            if(element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }
        // Testimonials are handled by TestimonialMarquee class automatically.

        // SweetAlert2 Session Messages
        const _sessionSuccess = document.querySelector('meta[name="session-success"]')?.content || null;
        const _sessionError   = document.querySelector('meta[name="session-error"]')?.content || null;

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

            // --- Scroll Spy & Nav Active Logic ---
            const navLinks = document.querySelectorAll('.nav-link');
            const mobLinks = document.querySelectorAll('.mob-nav-item');
            const spyTargets = [
                { section: 'beranda', linkId: 'nav-home', mobId: 'mob-home' },
                { section: 'promo-outlet', linkId: 'nav-promo', mobId: 'mob-promo' },
                { section: 'outlet', linkId: 'nav-outlet', mobId: 'mob-outlet' },
                { section: 'keunggulan', linkId: 'nav-features', mobId: 'mob-features' }
            ];

            let isScrolling = false;

            // --- High Performance Scroll Spy ---
            const observerOptions = {
                root: null,
                rootMargin: '-20% 0px -70% 0px', // Sweet spot for detection
                threshold: 0
            };

            const observer = new IntersectionObserver((entries) => {
                if (isScrolling) return;

                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const sectionId = entry.target.id;
                        const target = spyTargets.find(t => t.section === sectionId);
                        
                        if (target) {
                            navLinks.forEach(link => {
                                link.classList.toggle('active', link.id === target.linkId);
                            });
                            mobLinks.forEach(link => {
                                link.classList.toggle('active', link.id === target.mobId);
                            });
                        }
                    }
                });
            }, observerOptions);

            spyTargets.forEach(target => {
                const section = document.getElementById(target.section);
                if (section) observer.observe(section);
            });

            // Special case for very top
            window.addEventListener('scroll', () => {
                if (window.scrollY < 100 && !isScrolling) {
                    navLinks.forEach(l => l.classList.toggle('active', l.id === spyTargets[0].linkId));
                    mobLinks.forEach(l => l.classList.toggle('active', l.id === spyTargets[0].mobId));
                }
            }, { passive: true });

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        isScrolling = true;
                        navLinks.forEach(l => l.classList.remove('active'));
                        this.classList.add('active');
                        
                        const offset = 100;
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - offset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: "smooth"
                        });
                        
                        setTimeout(() => {
                            isScrolling = false;
                        }, 1000);
                    }

                    if (window.innerWidth <= 768) {
                        const mainNav = document.getElementById('mainNav');
                        const menuToggle = document.querySelector('.menu-toggle');
                        mainNav?.classList.remove('active');
                        menuToggle?.classList.remove('active');
                    }
                });
            });
        });
    </script>

    <!-- Animasi premium hero beranda -->
    <script src="{{ asset('js/premium-animations.js') }}"></script>
    </script>
</body>
</html>