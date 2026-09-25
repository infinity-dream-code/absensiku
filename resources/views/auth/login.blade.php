@extends('layouts.app')

@section('title', 'Login - Sistem Absensi')

@section('styles')
<style>
    .login-page {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
    }
    .login-card {
        width: 100%;
        max-width: 420px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        padding: 32px;
        box-sizing: border-box;
    }
    .login-header {
        text-align: center;
        margin-bottom: 28px;
    }
    .login-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 16px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 32px;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35);
    }
    .login-title {
        font-size: 26px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 6px 0;
    }
    .login-subtitle {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }
    .alert-errors {
        margin-bottom: 16px;
        padding: 12px 14px;
        border-left: 4px solid #ef4444;
        background: #fef2f2;
        border-radius: 8px;
        color: #b91c1c;
        font-size: 14px;
    }
    .alert-errors ul {
        margin: 0;
        padding-left: 18px;
    }
    .form-group {
        margin-bottom: 16px;
        width: 100%;
        box-sizing: border-box;
    }
    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    .form-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.15s ease;
        outline: none;
        box-sizing: border-box;
        display: block;
    }
    .form-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
    }
    .form-input.error {
        border-color: #ef4444;
    }
    .error-text {
        margin-top: 6px;
        font-size: 13px;
        color: #dc2626;
    }
    .remember-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
    }
    .remember-row input {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    .remember-row label {
        font-size: 13px;
        color: #4b5563;
        cursor: pointer;
        margin: 0;
    }
    .form-actions {
        margin-top: 0;
        width: 100%;
        box-sizing: border-box;
    }
    .btn-submit {
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: #fff;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 10px 25px rgba(99,102,241,0.25);
        transition: all 0.15s ease;
        margin: 0;
        box-sizing: border-box;
    }
    .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px rgba(99,102,241,0.3);
    }
    .btn-submit:active {
        transform: translateY(0);
    }
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 24px 0;
        position: relative;
        width: 100%;
        box-sizing: border-box;
    }
    .divider::before {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
    }
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
    }
    .divider span {
        padding: 0 16px;
        color: #9ca3af;
        font-size: 13px;
        font-weight: 500;
        background: #ffffff;
    }
    .admin-login-wrapper {
        margin-top: 0;
        width: 100%;
        box-sizing: border-box;
    }
    .btn-admin-link {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #6366f1;
        border-radius: 10px;
        background: transparent;
        color: #6366f1;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.15s ease;
        margin: 0;
        box-sizing: border-box;
    }
    .btn-admin-link:hover {
        background: #6366f1;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(99,102,241,0.25);
    }
    .btn-admin-link:active {
        transform: translateY(0);
    }
</style>
@endsection

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="login-header">
            <div class="login-icon" style="background: transparent; box-shadow: none;">
                <img src="{{ asset('logo-512.png') }}" alt="Logo" style="width: 72px; height: 72px; border-radius: 50%;">
            </div>
            <h2 class="login-title">Login</h2>
            <p class="login-subtitle">Masuk dengan Username/NIK dan Password</p>
        </div>

        @if($errors->any())
            <div class="alert-errors">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-form" style="width: 100%; box-sizing: border-box;">
            @csrf
            
            <div class="form-group">
                <label for="nik" class="form-label">
                    <i class="fas fa-id-card" style="margin-right:8px; color:#6366f1;"></i>Username atau NIK
                </label>
                <input type="text" 
                       class="form-input @error('nik') error @enderror" 
                       id="nik" 
                       name="nik" 
                       value="{{ old('nik') }}" 
                       required 
                       autofocus
                       placeholder="Masukkan Username atau NIK">
             
                @error('nik')
                    <p class="error-text">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock" style="margin-right:8px; color:#6366f1;"></i>Password
                </label>
                <input type="password" 
                       class="form-input @error('password') error @enderror" 
                       id="password" 
                       name="password" 
                       required
                       placeholder="Masukkan Password">
                @error('password')
                    <p class="error-text">{{ $message }}</p>
                @enderror
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </button>
            </div>
        </form>

        <div class="divider">
            <span>atau</span>
        </div>

        <div class="admin-login-wrapper">
            <a href="{{ route('admin.login') }}" class="btn-admin-link">
                <i class="fas fa-user-shield"></i>
                <span>Login Admin</span>
            </a>
        </div>
    </div>
</div>

@include('partials.login-csrf')
@endsection
