@extends('admin.layouts.app')

@section('title', 'Edit Karyawan')

@section('styles')
<style>
    .page-header {
        margin-bottom: 32px;
    }
    
    .page-title {
        font-size: 32px;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 8px;
    }
    
    .page-subtitle {
        font-size: 16px;
        color: #6b7280;
    }
    
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .form-group {
        margin-bottom: 24px;
    }
    
    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    
    .required {
        color: #dc2626;
    }
    
    .form-input {
        width: 100%;
        padding: 12px 16px;
        font-size: 15px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.2s;
        outline: none;
    }
    
    .form-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .form-input.error {
        border-color: #dc2626;
    }
    
    .error-message {
        color: #dc2626;
        font-size: 14px;
        margin-top: 6px;
    }
    
    .form-help {
        font-size: 12px;
        color: #6b7280;
        margin-top: 6px;
    }
    
    .form-actions {
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    
    .btn-cancel {
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        color: #374151;
        background: white;
        border: 2px solid #d1d5db;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
    }
    
    .btn-cancel:hover {
        background: #f9fafb;
    }
    
    .btn-submit {
        padding: 12px 32px;
        font-size: 16px;
        font-weight: 600;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }
    
    @media (max-width: 768px) {
        .form-card {
            padding: 24px;
        }
        
        .page-title {
            font-size: 24px;
        }
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Karyawan</h1>
    <p class="page-subtitle">Edit data karyawan</p>
</div>

<div class="form-card">
    <form action="{{ route('admin.employees.update', $employee) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="nik" class="form-label">
                NIK <span class="required">*</span>
            </label>
            <input type="text" 
                   id="nik" 
                   name="nik" 
                   value="{{ old('nik', $employee->nik) }}"
                   class="form-input @error('nik') error @enderror"
                   required>
            @error('nik')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="name" class="form-label">
                Nama <span class="required">*</span>
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="{{ old('name', $employee->name) }}"
                   class="form-input @error('name') error @enderror"
                   required>
            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group" style="background:#f5f3ff; border:1px solid #ddd6fe; border-radius:12px; padding:16px;">
            <label for="id_role" class="form-label">Role KPI (4 pilihan)</label>
            <select id="id_role" name="id_role" class="form-input @error('id_role') error @enderror">
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ (string) old('id_role', $employee->id_role) === (string) $role->id ? 'selected' : '' }}>
                        {{ $role->role }}
                    </option>
                @endforeach
            </select>
            @error('id_role')
                <p class="error-message">{{ $message }}</p>
            @enderror
            <p class="form-help">Pilih salah satu: Developer, Finance, HR & Admin, Support & Implementasi.</p>

            <label class="form-label" style="display:flex; align-items:center; gap:8px; margin-top:14px;">
                <input type="checkbox" name="is_penilai" value="1" {{ old('is_penilai', $employee->is_penilai) ? 'checked' : '' }}>
                Jadikan Penilai KPI (ketua divisi)
            </label>
            <p class="form-help">Centang agar user bisa login di halaman Penilai KPI. Wajib pilih Role KPI dulu.</p>
        </div>
        
        <div class="form-group">
            <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 8px; margin-top: 8px;">
                <p style="color: #1e40af; font-size: 14px; margin: 0;">
                    <i class="fas fa-info-circle"></i> 
                    Untuk mengubah password, user dapat menggunakan menu "Ganti Password" setelah login.
                </p>
            </div>
        </div>
        
        <div class="form-actions">
            <a href="{{ route('admin.employees.index') }}" class="btn-cancel">
                Batal
            </a>
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i>
                <span>Update</span>
            </button>
        </div>
    </form>
</div>
@endsection
