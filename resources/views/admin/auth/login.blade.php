<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#667eea">
    <title>Admin Login - Absensi ICT</title>
    
    <!-- PWA Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Absensi ICT">
    <meta name="description" content="Admin Login Sistem Absensi Karyawan ICT">
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
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 500px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .icon-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .icon-wrapper i {
            font-size: 36px;
            color: white;
        }
        
        .login-title {
            font-size: 32px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .login-subtitle {
            font-size: 16px;
            color: #6b7280;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
        }
        
        .form-label i {
            color: #667eea;
            margin-right: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 18px;
            font-size: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.3s;
            outline: none;
        }
        
        .form-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .form-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .form-checkbox input {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            cursor: pointer;
        }
        
        .form-checkbox label {
            font-size: 15px;
            color: #6b7280;
            cursor: pointer;
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            font-size: 18px;
            font-weight: bold;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert-error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        
        .alert-error ul {
            list-style: disc;
            list-style-position: inside;
            color: #dc2626;
        }
        
        .alert-error li {
            margin-bottom: 4px;
        }
        
        .btn-user-link {
            width: 100%;
            padding: 14px 18px;
            font-size: 16px;
            font-weight: 600;
            color: #667eea;
            background: transparent;
            border: 2px solid #667eea;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            box-sizing: border-box;
        }
        
        .btn-user-link:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-user-link:active {
            transform: translateY(0);
        }
        
        @media (max-width: 640px) {
            .login-card {
                padding: 40px 30px;
            }
            
            .login-title {
                font-size: 28px;
            }
            
            .icon-wrapper {
                width: 70px;
                height: 70px;
            }
            
            .icon-wrapper i {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="icon-wrapper" style="background: transparent; box-shadow: none;">
                    <img src="{{ asset('logo-512.png') }}" alt="Logo" style="width: 80px; height: 80px; border-radius: 50%;">
                </div>
                <h2 class="login-title">Admin Login</h2>
                <p class="login-subtitle">Absensi ICT - Panel Administrasi</p>
            </div>

            @if($errors->any())
                <div class="alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/admin/ict-login" class="login-form">
                @csrf
                
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i>Username
                    </label>
                    <input type="text" 
                           class="form-input" 
                           id="username" 
                           name="username" 
                           value="{{ old('username') }}" 
                           required 
                           autofocus
                           placeholder="Masukkan username">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>Password
                    </label>
                    <input type="password" 
                           class="form-input" 
                           id="password" 
                           name="password" 
                           required
                           placeholder="Masukkan password">
                </div>

                <div class="form-checkbox">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember">
                    <label for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </button>
            </form>

            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <p style="text-align:center; color:#9ca3af; font-size:13px; margin-bottom:12px;">atau</p>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <a href="/login" class="btn-user-link">
                        <i class="fas fa-user"></i>
                        <span>Login User</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: '{{ session('error') }}',
                timer: 4000,
                showConfirmButton: false
            });
        @endif
    </script>

    @include('partials.login-csrf')
</body>
</html>
