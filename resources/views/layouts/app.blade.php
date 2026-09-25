<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="theme-color" content="#667eea">
    <title>@yield('title', 'Absensi ICT')</title>
    
    <!-- PWA Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Absensi ICT">
    <meta name="description" content="Sistem Absensi Karyawan ICT">
    <meta name="application-name" content="Absensi ICT">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" href="{{ asset('logo-512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo-512.png') }}">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Basic layout (native CSS) */
        body {
            min-height: 100vh;
            background: #f9fafb;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #111827;
            margin: 0;
        }
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 64px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #111827;
            font-weight: 600;
            font-size: 17px;
        }
        .brand-icon {
            width: 32px;
            height: 32px;
            background: #4f46e5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
        }
        .brand span {
            white-space: nowrap;
        }
        @media (max-width: 480px) {
            .brand span {
                font-size: 15px;
            }
        }
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #374151;
            font-weight: 500;
            padding: 8px 10px;
            border-radius: 8px;
            transition: background 0.15s;
        }
        .nav-user:hover {
            background: #f3f4f6;
        }
        .nav-user span {
            white-space: nowrap;
        }
        @media (max-width: 640px) {
            .nav-user span {
                display: none;
            }
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 8px;
            text-decoration: none;
            color: #4b5563;
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .nav-link:hover {
            background: #f3f4f6;
            color: #111827;
        }
        @media (max-width: 640px) {
            .nav-link span {
                display: none;
            }
            .nav-link {
                padding: 8px;
                min-width: 36px;
                justify-content: center;
            }
        }
        .nav-button {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border: none;
            border-radius: 8px;
            background: #f3f4f6;
            color: #374151;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .nav-button:hover {
            background: #e5e7eb;
            color: #111827;
        }
        @media (max-width: 640px) {
            .nav-button span {
                display: none;
            }
            .nav-button {
                padding: 8px;
                min-width: 36px;
                justify-content: center;
            }
        }
        /* Mobile Menu Dropdown */
        .nav-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #6b7280;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.15s;
            align-items: center;
            justify-content: center;
        }
        .nav-menu-toggle:hover {
            background: #f3f4f6;
        }
        @media (max-width: 640px) {
            .nav-menu-toggle {
                display: flex;
            }
            .nav-actions .nav-user,
            .nav-actions .nav-link,
            .nav-actions .nav-button {
                display: none;
            }
        }
        .nav-dropdown {
            position: absolute;
            top: 100%;
            right: 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            min-width: 200px;
            margin-top: 8px;
            display: none;
            z-index: 1001;
            overflow: hidden;
        }
        .nav-dropdown.active {
            display: block;
        }
        .nav-dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #374151;
            text-decoration: none;
            transition: background 0.15s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }
        .nav-dropdown-item:hover {
            background: #f9fafb;
        }
        .nav-dropdown-item.logout {
            color: #dc2626;
            border-top: 1px solid #e5e7eb;
        }
        .nav-dropdown-item.logout:hover {
            background: #fef2f2;
        }
        .nav-dropdown-item i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }
        .nav-actions-wrapper {
            position: relative;
        }
        @media (min-width: 641px) {
            .nav-dropdown {
                display: none !important;
            }
        }
        .page-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 16px;
        }
    </style>
    @yield('styles')
</head>
<body>
    @if(auth()->check() && auth()->user()->role !== 'admin')
    <nav class="navbar" style="z-index: 9999;">
        <div class="nav-container">
            <a href="{{ route('attendance.index') }}" class="brand">
                <div class="brand-icon" style="background: transparent; width: 32px; height: 32px; padding: 0;">
                    <img src="{{ asset('logo-512.png') }}" alt="Logo" style="width: 32px; height: 32px; border-radius: 8px;">
                </div>
                <span>Absensi ICT</span>
            </a>
            <div class="nav-actions-wrapper">
                <div class="nav-actions">
                    @if(auth()->check())
                    <div class="nav-user">
                        <i class="fas fa-user" style="color:#9ca3af; font-size:13px;"></i>
                        <span>{{ auth()->user()->name }}</span>
                    </div>
                    @endif
                    @if(auth()->user()->isPenilai())
                    <a href="{{ route('kpi.index') }}" class="nav-link">
                        <i class="fas fa-chart-line" style="font-size:13px;"></i>
                        <span style="font-size:13px;">Kelola KPI</span>
                    </a>
                    @endif
                    <a href="{{ route('leave.index') }}" class="nav-link">
                        <i class="fas fa-calendar-times" style="font-size:13px;"></i>
                        <span style="font-size:13px;">Perizinan</span>
                    </a>
                    <a href="{{ route('profile.change-username') }}" class="nav-link">
                        <i class="fas fa-user-edit" style="font-size:13px;"></i>
                        <span style="font-size:13px;">Ganti Username</span>
                    </a>
                    <a href="{{ route('profile.change-password') }}" class="nav-link">
                        <i class="fas fa-key" style="font-size:13px;"></i>
                        <span style="font-size:13px;">Ganti Password</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="button" id="logout-btn" class="nav-button">
                            <i class="fas fa-sign-out-alt" style="font-size:13px;"></i>
                            <span style="font-size:13px;">Logout</span>
                        </button>
                    </form>
                </div>
                <button type="button" class="nav-menu-toggle" id="nav-menu-toggle" aria-label="Menu">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="nav-dropdown" id="nav-dropdown">
                    @if(auth()->check())
                        <div class="nav-dropdown-item" style="pointer-events: none; background: #f9fafb; color: #6b7280;">
                            <i class="fas fa-user"></i>
                            <span>{{ auth()->user()->name }}</span>
                        </div>
                    @endif
                    @if(auth()->user()->isPenilai())
                    <a href="{{ route('kpi.index') }}" class="nav-dropdown-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Kelola KPI</span>
                    </a>
                    @endif
                    <a href="{{ route('leave.index') }}" class="nav-dropdown-item">
                        <i class="fas fa-calendar-times"></i>
                        <span>Perizinan</span>
                    </a>
                    <a href="{{ route('profile.change-username') }}" class="nav-dropdown-item">
                        <i class="fas fa-user-edit"></i>
                        <span>Ganti Username</span>
                    </a>
                    <a href="{{ route('profile.change-password') }}" class="nav-dropdown-item">
                        <i class="fas fa-key"></i>
                        <span>Ganti Password</span>
                    </a>
                    <button type="button" id="logout-btn-mobile" class="nav-dropdown-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    @endif

    <div class="page-container">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    @include('partials.csrf-handler')

    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('success')),
                timer: 3000,
                showConfirmButton: false,
                allowOutsideClick: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: @json(session('error')),
                timer: 4000,
                showConfirmButton: false,
                allowOutsideClick: false
            });
        @endif

        // Handle mobile menu toggle
        const menuToggle = document.getElementById('nav-menu-toggle');
        const navDropdown = document.getElementById('nav-dropdown');
        
        if (menuToggle && navDropdown) {
            menuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                navDropdown.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!menuToggle.contains(e.target) && !navDropdown.contains(e.target)) {
                    navDropdown.classList.remove('active');
                }
            });
            
            // Close dropdown when clicking on dropdown item (except logout)
            navDropdown.addEventListener('click', function(e) {
                if (e.target.closest('.nav-dropdown-item') && !e.target.closest('.logout')) {
                    setTimeout(() => {
                        navDropdown.classList.remove('active');
                    }, 100);
                }
            });
        }
        
        // Function untuk logout
        function performLogout(retryCount = 0) {
            const form = document.getElementById('logout-form');
            const formData = new FormData(form);
            const tokenInput = form.querySelector('input[name="_token"]');
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            
            // Pastikan token terbaru
            if (tokenInput && metaToken) {
                tokenInput.value = metaToken.getAttribute('content');
            }
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.status === 419 && retryCount < 2) {
                    // Jika 419, refresh token dan retry
                    return refreshCsrfToken().then(() => {
                        // Update token di form
                        const newToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        formData.set('_token', newToken);
                        return performLogout(retryCount + 1);
                    });
                }
                
                if (response.redirected) {
                    // Clear storage sebelum redirect
                    sessionStorage.clear();
                    localStorage.clear();
                    window.location.href = response.url;
                } else if (response.ok) {
                    return response.json().then(data => {
                        // Clear storage sebelum redirect
                        sessionStorage.clear();
                        localStorage.clear();
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '{{ route("login") }}';
                        }
                    });
                } else {
                    // Jika masih error, gunakan GET fallback
                    sessionStorage.clear();
                    localStorage.clear();
                    window.location.href = '{{ route("logout") }}?fallback=1';
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                // Fallback ke GET logout
                sessionStorage.clear();
                localStorage.clear();
                window.location.href = '{{ route("logout") }}?fallback=1';
            });
        }
        
        // Handle logout dengan AJAX dan retry mechanism (desktop)
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                performLogout();
            });
        }
        
        // Handle logout mobile
        const logoutBtnMobile = document.getElementById('logout-btn-mobile');
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener('click', function(e) {
                e.preventDefault();
                if (navDropdown) {
                    navDropdown.classList.remove('active');
                }
                performLogout();
            });
        }
    </script>
    
    @yield('scripts')
</body>
</html>