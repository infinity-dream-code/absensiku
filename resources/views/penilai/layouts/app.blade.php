<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Penilai KPI') - Absensi ICT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; color: #111827; }
        .topbar {
            background: #0f766e; color: #fff; padding: 14px 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .topbar a { color: #fff; text-decoration: none; font-weight: 600; }
        .btn-logout {
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3);
            color: #fff; border-radius: 8px; padding: 8px 12px; cursor: pointer; font-weight: 600;
        }
        .container { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
    </style>
    @yield('styles')
</head>
<body>
<div class="topbar">
    <div>
        <strong>Panel Penilai KPI</strong>
        <span style="opacity:.85; margin-left:10px;">{{ auth()->user()->name ?? '' }}</span>
    </div>
    <div style="display:flex; gap:12px; align-items:center;">
        <a href="{{ route('penilai.kpi.index') }}"><i class="fas fa-chart-line"></i> Kelola KPI</a>
        <form action="{{ route('penilai.logout') }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>
</div>
<div class="container">
    @yield('content')
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
@if(session('success'))
Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('success')), timer: 2800, showConfirmButton: false });
@endif
@if(session('error'))
Swal.fire({ icon: 'error', title: 'Gagal!', text: @json(session('error')) });
@endif
</script>
@yield('scripts')
</body>
</html>
