    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>TWINS - Login</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
    </head>
    <body>

    <div class="kontainer-utama">
        <div id="overlaySukses" class="overlay-status overlay-sukses" style="display: none; opacity: 0;">
            <div class="kartu-status">
                <div class="container-ikon">
                    <div class="ring ring-1"></div>
                    <div class="ring ring-2"></div>
                    <div class="pusat-ikon">
                        <svg class="ikon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
                <h2 class="judul-status">Memproses...</h2>
                <p class="teks-status">Mohon tunggu sebentar, kami sedang memverifikasi akun kamu.</p>
                <div class="loader-bar">
                    <div id="progressBar" class="loader-fill"></div>
                </div>
            </div>
        </div>

        {{-- Error overlay removed to use inline validation instead --}}

        <div class="panel-visual">
            <div class="nama-brand">TWINS</div>
            <div class="teks-hero">
                <h1>SELAMAT DATANG</h1>
                <p>Belanja bahan kue jadi lebih gampang di Twins. Lengkap, cepat, dan siap bantu kamu bikin kue impian.</p>
            </div>

            <div class="container-visual-bawah">
                <img src="{{ asset('images/toko-luar.png') }}" alt="Visual 1" class="gambar-satu">
                <div id="wrapperGambarDua" class="wrapper-gambar-dua">
                    <img src="{{ asset('images//orang.png') }}" alt="Visual 2" class="gambar-dua">
                </div>
            </div>
        </div>

        <div class="panel-form">
            <div class="bungkus-form">
                <h2 class="judul-form">Login</h2>
                <p class="subjudul-form">Masuk ke akun Twins kamu dan mulai belanja bahan kue dengan mudah.</p>
                
                <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                    @csrf
                    
                    <div class="grup-input">
                        <label class="label-input">Email</label>
                        <input type="text" name="email" id="inputEmail" class="field-input @error('email') is-invalid @enderror" 
                            placeholder="nama@gmail.com" value="{{ old('email') }}" autofocus>
                        @error('email')
                            <span class="pesan-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grup-input">
                        <div style="display: flex; justify-content: space-between;">
                            <label class="label-input">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" style="font-size: 12px; color: var(--warna-utama); text-decoration: none; font-weight: 600;">Lupa kata sandi?</a>
                            @endif
                        </div>

                        <div style="position: relative;">
                            <input type="password" name="password" id="inputConfirm" class="field-input @error('password') is-invalid @enderror" placeholder="••••••••" style="padding-right: 45px;">
                            
                            <div class="toggle-password" onclick="togglePassword('inputConfirm', 'eyeIconConfirm')" 
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; display: flex; align-items: center;">
                                <i id="eyeIconConfirm" data-lucide="eye" style="width: 20px; color: #666;"></i>
                            </div>
                        </div>
                        @error('password')
                            <span class="pesan-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="opsi-tambahan">
                        <input type="checkbox" name="remember" id="remember_me" style="width: 16px; height: 16px; accent-color: var(--warna-utama);">
                        <label for="remember_me">Ingat saya pada perangkat ini</label>
                    </div>

                    <button type="submit" class="tombol-masuk" onclick="mulaiAnimasi(event)">Log In</button>
                </form>

                <p style="margin-top: 40px; text-align: center; font-size: 14px; color: var(--warna-abu);">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" style="color: var(--warna-utama); font-weight: 700; text-decoration: none;">Daftar</a>
                </p>
            </div>
        </div>
    </div>

    <div id="session-status" data-value="{{ session('status') }}"></div>
    <div id="session-success" data-value="{{ session('success') }}"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusMsg = document.getElementById('session-status').dataset.value;
            const successMsg = document.getElementById('session-success').dataset.value;
            
            const messageToShow = statusMsg || successMsg;

            if (messageToShow) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: messageToShow,
                    icon: 'success',
                    confirmButtonColor: '#0477bf',
                    timer: 2000,
                    timerProgressBar: true,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    }
                });
            }
        });

        function togglePassword(inputId, iconId) {
            const passInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            
            lucide.createIcons(); 
        }

        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });

        function mulaiAnimasi(e) {
            const form = document.getElementById('loginForm');
            const email = document.getElementById('inputEmail').value;
            const password = document.getElementById('inputConfirm').value;
            
            // Regex sederhana untuk validasi format email di client-side
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Hanya jalankan animasi jika:
            // 1. Email & Password tidak kosong
            // 2. Format email benar
            // 3. Password minimal 6 karakter
            if (email.trim() !== '' && 
                password.trim() !== '' && 
                emailPattern.test(email) && 
                password.length >= 6) {
                
                e.preventDefault();

                const wrapperDua = document.getElementById('wrapperGambarDua');
                const overlaySukses = document.getElementById('overlaySukses');
                const bar = document.getElementById('progressBar');

                wrapperDua.classList.add('image-slide-out-right');

                setTimeout(() => {
                    overlaySukses.style.display = 'flex';
                    overlaySukses.style.opacity = '1';

                    setTimeout(() => {
                        bar.style.width = '100%';
                    }, 100);

                    setTimeout(() => {
                        form.submit();
                    }, 2000);

                }, 600);
            }
            // Jika tidak memenuhi syarat, biarkan submit langsung untuk memicu error merah dari Laravel
        }

        function tutupOverlay(id) {
            const overlay = document.getElementById(id);
            overlay.style.opacity = '0';
            
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }
    </script>

    </body>
    </html>