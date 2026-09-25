<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Penilai - Absensi ICT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .login-card {
            width: 100%; max-width: 480px; background: #fff; border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3); padding: 48px 40px;
        }
        .login-title { font-size: 28px; font-weight: 700; color: #111827; text-align: center; margin-bottom: 6px; }
        .login-subtitle { text-align: center; color: #6b7280; margin-bottom: 28px; }
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        .form-input {
            width: 100%; padding: 12px 14px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 15px; outline: none;
        }
        .form-input:focus { border-color: #0f766e; box-shadow: 0 0 0 4px rgba(15,118,110,.12); }
        .btn-login {
            width: 100%; margin-top: 8px; border: 0; border-radius: 10px; padding: 14px;
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); color: #fff;
            font-weight: 700; font-size: 16px; cursor: pointer;
        }
        .btn-link {
            display: block; text-align: center; margin-top: 18px; color: #0f766e; text-decoration: none; font-weight: 600;
        }
        .alert-error {
            background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 10px;
            padding: 12px 14px; margin-bottom: 16px; font-size: 14px;
        }
    </style>
</head>
<body>
<div class="login-card">
    <h2 class="login-title">Login Penilai</h2>
    <p class="login-subtitle">Khusus ketua divisi untuk penilaian KPI</p>

    @if($errors->any())
        <div class="alert-error">
            <ul style="margin-left: 16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penilai.login.post') }}">
        @csrf
        <div class="form-group">
            <label class="form-label" for="username"><i class="fas fa-user"></i> Username / NIK</label>
            <input type="text" id="username" name="username" class="form-input" value="{{ old('username') }}" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label" for="password"><i class="fas fa-lock"></i> Password</label>
            <input type="password" id="password" name="password" class="form-input" required>
        </div>
        <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login Penilai</button>
    </form>

    <a href="{{ route('admin.login') }}" class="btn-link"><i class="fas fa-arrow-left"></i> Kembali ke Login Admin</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
@if(session('success'))
Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('success')), timer: 2500, showConfirmButton: false });
@endif
</script>
</body>
</html>
