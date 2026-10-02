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
    <title>@yield('title', 'Admin Dashboard - Absensi ICT')</title>
    
    <!-- PWA Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Absensi ICT">
    <meta name="description" content="Admin Panel Sistem Absensi Karyawan ICT">
    <meta name="application-name" content="Absensi ICT">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" href="{{ asset('logo-512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo-512.png') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
            }
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }
        
        .logo-text {
            font-weight: bold;
            font-size: 16px;
            color: #1f2937;
        }
        
        .close-sidebar {
            display: block;
            background: none;
            border: none;
            color: #6b7280;
            font-size: 20px;
            cursor: pointer;
        }
        
        @media (min-width: 1024px) {
            .close-sidebar {
                display: none;
            }
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 20px 16px;
            overflow-y: auto;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 8px;
            text-decoration: none;
            color: #374151;
            margin-bottom: 8px;
            transition: all 0.2s;
        }
        
        .nav-item:hover {
            background: #f3f4f6;
        }
        
        .nav-item.active {
            background: #eef2ff;
            color: #667eea;
        }
        
        .nav-item i {
            width: 20px;
            text-align: center;
        }
        
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .nav-group {
            margin-bottom: 8px;
        }

        .nav-group-toggle {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
            user-select: none;
            transition: all 0.2s;
        }

        .nav-group-toggle:hover {
            background: #f3f4f6;
        }

        .nav-group-toggle i {
            width: 20px;
            text-align: center;
        }

        .nav-group-chevron {
            margin-left: auto;
            font-size: 12px;
        }

        .nav-submenu {
            display: none;
            flex-direction: column;
            padding-left: 32px;
        }

        .nav-submenu.open {
            display: flex;
        }

        .nav-submenu .nav-item {
            font-size: 14px;
            padding: 10px 12px;
            gap: 10px;
        }

        .nav-submenu .nav-item i {
            width: 18px;
            font-size: 14px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #eef2ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
        }
        
        .user-details {
            flex: 1;
            min-width: 0;
        }
        
        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .user-role {
            font-size: 12px;
            color: #6b7280;
        }
        
        .btn-logout {
            width: 100%;
            padding: 10px 16px;
            background: #fef2f2;
            color: #dc2626;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-logout:hover {
            background: #fee2e2;
        }
        
        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        
        .overlay.active {
            display: block;
        }
        
        @media (min-width: 1024px) {
            .overlay {
                display: none !important;
            }
        }
        
        /* Main Content */
        .main-content {
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }
        
        @media (min-width: 1024px) {
            .main-content {
                margin-left: 260px;
            }
        }
        
        .topbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 0 24px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .menu-toggle {
            display: block;
            background: none;
            border: none;
            color: #6b7280;
            font-size: 20px;
            cursor: pointer;
        }
        
        @media (min-width: 1024px) {
            .menu-toggle {
                display: none;
            }
        }
        
        .topbar-date {
            font-size: 14px;
            color: #6b7280;
        }
        
        .page-content {
            padding: 24px;
        }
        
        /* Alerts */
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .alert-error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        
        .alert i {
            font-size: 18px;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-wrapper">
                <div class="logo-icon" style="background: transparent; padding: 0;">
                    <img src="{{ asset('logo-512.png') }}" alt="Logo" style="width: 32px; height: 32px; border-radius: 8px;">
                </div>
                <span class="logo-text">Absensi ICT</span>
            </div>
            <button class="close-sidebar" id="closeSidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            @php
                $isPenilaiMode = auth()->check()
                    && auth()->user()->role !== 'admin'
                    && auth()->user()->isPenilai()
                    && request()->routeIs('kpi.*');
                $isReportActive = request()->routeIs('admin.attendance-history.*')
                    || request()->routeIs('admin.attendance-summary.*');
                $isValidationActive = request()->routeIs('admin.attendance-validation.*')
                    || request()->routeIs('admin.wfo-validation.*');
                $isKpiActive = request()->routeIs('admin.kpi.*');
            @endphp

            @if($isPenilaiMode)
                <a href="{{ route('kpi.index') }}" class="nav-item {{ request()->routeIs('kpi.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Kelola KPI</span>
                </a>
                <a href="{{ route('attendance.index') }}" class="nav-item">
                    <i class="fas fa-fingerprint"></i>
                    <span>Absensi</span>
                </a>
            @else
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                <span>Set Waktu</span>
            </a>
            <a href="{{ route('admin.employees.index') }}" class="nav-item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Kelola Karyawan</span>
            </a>
            <div class="nav-group">
                <div class="nav-group-toggle" data-nav-group="report-absensi">
                    <i class="fas fa-file-alt"></i>
                    <span>Report Absensi</span>
                    <span class="nav-group-chevron">
                        <i class="fas fa-chevron-{{ $isReportActive ? 'down' : 'right' }}"></i>
                    </span>
                </div>
                <div class="nav-submenu {{ $isReportActive ? 'open' : '' }}" id="nav-group-report-absensi">
                    <a href="{{ route('admin.attendance-history.index') }}" class="nav-item {{ request()->routeIs('admin.attendance-history.*') ? 'active' : '' }}" style="margin-bottom: 4px; padding: 8px 12px;">
                        <span>History Absensi</span>
                    </a>
                    <a href="{{ route('admin.attendance-summary.index') }}" class="nav-item {{ request()->routeIs('admin.attendance-summary.*') ? 'active' : '' }}" style="margin-bottom: 0; padding: 8px 12px;">
                        <span>Summary Absensi</span>
                    </a>
                </div>
            </div>
            <div class="nav-group">
                <div class="nav-group-toggle" data-nav-group="validasi">
                    <i class="fas fa-check-double"></i>
                    <span>Validasi</span>
                    <span class="nav-group-chevron">
                        <i class="fas fa-chevron-{{ $isValidationActive ? 'down' : 'right' }}"></i>
                    </span>
                </div>
                <div class="nav-submenu {{ $isValidationActive ? 'open' : '' }}" id="nav-group-validasi">
                    <a href="{{ route('admin.attendance-validation.index') }}" class="nav-item {{ request()->routeIs('admin.attendance-validation.*') ? 'active' : '' }}" style="margin-bottom: 0; padding: 8px 12px;">
                        <span>Validasi Alpha</span>
                    </a>
                    <a href="{{ route('admin.wfo-validation.index') }}" class="nav-item {{ request()->routeIs('admin.wfo-validation.*') ? 'active' : '' }}" style="margin-bottom: 0; padding: 8px 12px;">
                        <span>Validasi WFO</span>
                    </a>
                </div>
            </div>
            <div class="nav-group">
                <div class="nav-group-toggle" data-nav-group="kpi">
                    <i class="fas fa-chart-line"></i>
                    <span>Kelola KPI</span>
                    <span class="nav-group-chevron">
                        <i class="fas fa-chevron-{{ $isKpiActive ? 'down' : 'right' }}"></i>
                    </span>
                </div>
                <div class="nav-submenu {{ $isKpiActive ? 'open' : '' }}" id="nav-group-kpi">
                    <a href="{{ route('admin.kpi.index') }}" class="nav-item {{ request()->routeIs('admin.kpi.index') || request()->routeIs('admin.kpi.form') || request()->routeIs('admin.kpi.store') || request()->routeIs('admin.kpi.export') || request()->routeIs('admin.kpi.indikator.store') || request()->routeIs('admin.kpi.indikator.update') || request()->routeIs('admin.kpi.indikator.destroy') ? 'active' : '' }}" style="margin-bottom: 4px;">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Daftar Penilaian</span>
                    </a>
                    <a href="{{ route('admin.kpi.indikator-role') }}" class="nav-item {{ request()->routeIs('admin.kpi.indikator-role*') ? 'active' : '' }}" style="margin-bottom: 0;">
                        <i class="fas fa-list-ul"></i>
                        <span>Indikator Role</span>
                    </a>
                </div>
            </div>
            <a href="{{ route('admin.referensi-wfo.index') }}" class="nav-item {{ request()->routeIs('admin.referensi-wfo.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Referensi WFO</span>
            </a>

            <a href="{{ route('admin.leave-history.index') }}" class="nav-item {{ request()->routeIs('admin.leave-history.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-times"></i>
                <span>History Izin</span>
            </a>
            <a href="{{ route('admin.location.index') }}" class="nav-item {{ request()->routeIs('admin.location.*') ? 'active' : '' }}">
                <i class="fas fa-map-marker-alt"></i>
                <span>Set Lokasi</span>
            </a>
            <a href="{{ route('admin.holiday.index') }}" class="nav-item {{ request()->routeIs('admin.holiday.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span>Libur</span>
            </a>
            @endif
        </nav>
        
        <div class="sidebar-footer">
            @if(auth()->check())
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ $isPenilaiMode ? 'Penilai KPI' : 'Administrator' }}</div>
                    </div>
                </div>
            @endif
            <form id="admin-logout-form" action="{{ $isPenilaiMode ? '/logout' : '/admin/logout' }}" method="POST">
                @csrf
                <button type="button" id="admin-logout-btn" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Overlay -->
    <div class="overlay" id="sidebarOverlay"></div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <button class="menu-toggle" id="openSidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-date">
                {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            
            @yield('content')
        </main>
    </div>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');
        
        openBtn.addEventListener('click', () => {
            sidebar.classList.add('active');
            overlay.classList.add('active');
        });
        
        closeBtn.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
        
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
        
        // Setup CSRF token
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Function untuk refresh CSRF token
        function refreshCsrfToken() {
            return fetch('/csrf-token', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => response.json())
              .then(data => {
                  if (data.token) {
                      document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                      axios.defaults.headers.common['X-CSRF-TOKEN'] = data.token;
                      document.querySelectorAll('input[name="_token"]').forEach(input => {
                          input.value = data.token;
                      });
                  }
                  return data.token;
              }).catch(() => {
                  return null;
              });
        }

        (function () {
            var nativeFetch = window.fetch.bind(window);

            function requestUrl(input) {
                if (typeof input === 'string') {
                    return input;
                }
                return input && input.url ? input.url : '';
            }

            window.fetch = function (input, init) {
                var options = init || {};
                return nativeFetch(input, options).then(function (response) {
                    var url = requestUrl(input);
                    if (response.status !== 419 || options._csrfRetried || url.indexOf('/csrf-token') !== -1) {
                        return response;
                    }

                    return refreshCsrfToken().then(function () {
                        var retry = Object.assign({}, options, { _csrfRetried: true });
                        var meta = document.querySelector('meta[name="csrf-token"]');
                        var token = meta ? meta.getAttribute('content') : '';
                        if (token) {
                            var headers = new Headers(retry.headers || (input instanceof Request ? input.headers : undefined));
                            headers.set('X-CSRF-TOKEN', token);
                            headers.set('X-Requested-With', 'XMLHttpRequest');
                            retry.headers = headers;
                            if (retry.body instanceof FormData) {
                                retry.body.set('_token', token);
                            }
                        }
                        return nativeFetch(input, retry);
                    });
                });
            };
        })();

        axios.interceptors.response.use(function (response) {
            return response;
        }, function (error) {
            var config = error.config || {};
            var url = config.url || '';
            if (!(error.response && error.response.status === 419) || config._csrfRetried || url.indexOf('/csrf-token') !== -1) {
                return Promise.reject(error);
            }

            config._csrfRetried = true;
            return refreshCsrfToken().then(function (token) {
                var meta = document.querySelector('meta[name="csrf-token"]');
                config.headers = config.headers || {};
                if (meta) {
                    config.headers['X-CSRF-TOKEN'] = meta.getAttribute('content');
                }
                if (token && config.data instanceof FormData) {
                    config.data.set('_token', token);
                }
                return axios(config);
            });
        });

        var keepAliveRunning = false;
        function keepAlive() {
            if (keepAliveRunning) {
                return;
            }
            keepAliveRunning = true;
            refreshCsrfToken().finally(function () {
                keepAliveRunning = false;
            });
        }
        setInterval(keepAlive, 4 * 60 * 1000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                keepAlive();
            }
        });

        // Refresh CSRF token saat halaman load dan setelah login/logout
        function initializeCsrfToken() {
            return refreshCsrfToken().then(() => {
                // Update semua form token
                document.querySelectorAll('input[name="_token"]').forEach(input => {
                    const metaToken = document.querySelector('meta[name="csrf-token"]');
                    if (metaToken) {
                        input.value = metaToken.getAttribute('content');
                    }
                });
                return true;
            });
        }
        
        // Refresh token hanya saat diperlukan (tidak force refresh)
        // Karena sekarang tidak regenerate session setelah login,
        // token di meta tag sudah match dengan session
        window.addEventListener('DOMContentLoaded', function() {
            // Sync form token dengan meta tag (tidak perlu refresh dari server)
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                const token = metaToken.getAttribute('content');
                document.querySelectorAll('input[name="_token"]').forEach(input => {
                    input.value = token;
                });
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            }
        });

        // Intercept form submission untuk memastikan token valid
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.tagName === 'FORM' && form.method.toUpperCase() === 'POST') {
                // Skip untuk logout form karena sudah dihandle khusus
                if (form.id === 'admin-logout-form') {
                    e.preventDefault();
                    return;
                }
                
                const tokenInput = form.querySelector('input[name="_token"]');
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                
                // Pastikan token form sama dengan token meta
                if (tokenInput && metaToken && tokenInput.value !== metaToken.getAttribute('content')) {
                    tokenInput.value = metaToken.getAttribute('content');
                }
            }
        }, false);

        // Handle admin logout dengan AJAX dan retry mechanism
        const adminLogoutBtn = document.getElementById('admin-logout-btn');
        if (adminLogoutBtn) {
            adminLogoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const form = document.getElementById('admin-logout-form');
                const tokenInput = form.querySelector('input[name="_token"]');
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                
                // Pastikan token terbaru
                if (tokenInput && metaToken) {
                    tokenInput.value = metaToken.getAttribute('content');
                }
                
                // Fungsi untuk logout
                function performLogout(retryCount = 0) {
                    const formData = new FormData(form);
                    
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
                            sessionStorage.clear();
                            localStorage.clear();
                            window.location.href = '{{ $isPenilaiMode ? '/login' : '/admin/ict-login' }}';
                        } else if (response.ok) {
                            return response.json().then(data => {
                                sessionStorage.clear();
                                localStorage.clear();
                                window.location.href = '{{ $isPenilaiMode ? '/login' : '/admin/ict-login' }}';
                            });
                        } else {
                            // Jika masih error, gunakan GET fallback
                            sessionStorage.clear();
                            localStorage.clear();
                            window.location.href = '{{ $isPenilaiMode ? '/logout' : '/admin/logout' }}?fallback=1';
                        }
                    })
                    .catch(error => {
                        console.error('Logout error:', error);
                        window.location.href = '{{ $isPenilaiMode ? '/logout' : '/admin/logout' }}?fallback=1';
                    });
                }
                
                performLogout();
            });
        }

        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js?v=4')
                    .then(function (registration) {
                        registration.update();
                    })
                    .catch(function () {});
            });
        }

        // Sidebar nav-group dropdown (Report Absensi)
        document.addEventListener('DOMContentLoaded', function () {
            const toggles = document.querySelectorAll('.nav-group-toggle');
            toggles.forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    const key = this.getAttribute('data-nav-group');
                    if (!key) return;
                    const submenu = document.getElementById('nav-group-' + key);
                    if (!submenu) return;

                    const isOpen = submenu.classList.contains('open');
                    if (isOpen) {
                        submenu.classList.remove('open');
                        const icon = this.querySelector('.nav-group-chevron i');
                        if (icon) {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-right');
                        }
                    } else {
                        submenu.classList.add('open');
                        const icon = this.querySelector('.nav-group-chevron i');
                        if (icon) {
                            icon.classList.remove('fa-chevron-right');
                            icon.classList.add('fa-chevron-down');
                        }
                    }
                });
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>
