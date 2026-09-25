@extends('admin.layouts.app')

@section('title', 'Kelola Karyawan')

@section('styles')
<style>
    .page-header {
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
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
    
    .btn-add {
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 600;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }
    
    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .table-wrapper {
        overflow-x: auto;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    thead {
        background: #f9fafb;
    }
    
    th {
        padding: 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    td {
        padding: 16px;
        font-size: 14px;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }
    
    tbody tr:hover {
        background: #f9fafb;
    }
    
    .action-buttons {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .btn-action {
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .btn-edit {
        background: #eef2ff;
        color: #667eea;
    }
    
    .btn-edit:hover {
        background: #e0e7ff;
    }
    
    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
    }
    
    .btn-delete:hover {
        background: #fee2e2;
    }
    
    .btn-reset {
        background: #fef3c7;
        color: #d97706;
    }
    
    .btn-reset:hover {
        background: #fde68a;
    }
    
    .empty-state {
        padding: 48px;
        text-align: center;
        color: #6b7280;
    }
    
    .pagination-wrapper {
        padding: 16px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: center;
    }
    
    /* Style pagination normally - hide large arrows */
    .pagination-wrapper .pagination {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .pagination-wrapper .pagination li {
        display: inline-block;
    }
    
    .pagination-wrapper .pagination li a,
    .pagination-wrapper .pagination li span {
        display: inline-block;
        padding: 8px 12px;
        text-decoration: none;
        color: #374151;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s;
        background: white;
        min-width: 40px;
        text-align: center;
    }
    
    .pagination-wrapper .pagination li.active span,
    .pagination-wrapper .pagination li.active a {
        background: #667eea;
        color: white;
        border-color: #667eea;
        font-weight: 600;
    }
    
    .pagination-wrapper .pagination li a:hover:not(.disabled) {
        background: #f3f4f6;
        border-color: #d1d5db;
    }
    
    .pagination-wrapper .pagination li.disabled span,
    .pagination-wrapper .pagination li.disabled a {
        color: #9ca3af;
        cursor: not-allowed;
        background: #f9fafb;
        opacity: 0.6;
    }
    
    /* Hide large arrow icons - only show text */
    .pagination-wrapper .pagination li i,
    .pagination-wrapper .pagination li .fa,
    .pagination-wrapper .pagination li [class*="fa-"],
    .pagination-wrapper .pagination li svg,
    #paginationContainer i,
    #paginationContainer .fa,
    #paginationContainer [class*="fa-"],
    #paginationContainer svg {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        width: 0 !important;
        height: 0 !important;
        font-size: 0 !important;
    }
    
    /* Ensure text is visible */
    .pagination-wrapper .pagination li a,
    .pagination-wrapper .pagination li span,
    #paginationContainer .pagination li a,
    #paginationContainer .pagination li span {
        font-size: 14px !important;
        padding: 8px 12px !important;
    }
    
    /* Hide links that only contain arrows */
    #paginationContainer .pagination li a:only-child:empty,
    #paginationContainer .pagination li span:only-child:empty {
        display: none !important;
    }
    
    
    /* Content styles */
    .content-header {
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .content-title {
        font-size: 32px;
        font-weight: bold;
        color: #1f2937;
        margin: 0 0 8px 0;
    }
    
    .content-subtitle {
        font-size: 16px;
        color: #6b7280;
        margin: 0;
    }
    
    .table-row {
        transition: background 0.2s;
    }
    
    .table-row:hover {
        background: #f9fafb;
    }
    
    .empty-state-cell {
        padding: 48px;
        text-align: center;
        color: #6b7280;
    }
    
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .page-title {
            font-size: 24px;
        }
        
        table {
            font-size: 12px;
        }
        
        th, td {
            padding: 12px 8px;
        }
        
        .content-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .pagination-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div>
        <h1 class="content-title">Kelola Karyawan</h1>
        <p class="content-subtitle">Daftar semua karyawan</p>
    </div>
    <a href="{{ route('admin.employees.create') }}" class="btn-add">
        <i class="fas fa-plus"></i>
        <span>Tambah Karyawan</span>
    </a>
</div>

<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Role KPI</th>
                    <th>Penilai</th>
                    <th>Jenis</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr class="table-row">
                    <td style="font-weight: 600;">{{ $employee->nik }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->kpiRole->role ?? '-' }}</td>
                    <td>
                        @if($employee->is_penilai)
                            <span style="display:inline-block; padding:4px 8px; border-radius:999px; background:#ecfdf5; color:#047857; font-size:12px; font-weight:600;">Ya</span>
                        @else
                            <span style="color:#9ca3af;">Tidak</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.employees.toggle-jenis', $employee) }}" method="POST">
                            @csrf
                            <input type="hidden" name="jenis" value="0">
                            <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                                <input type="checkbox" name="jenis" value="1" onchange="this.form.submit()" {{ (int) $employee->jenis === 1 ? 'checked' : '' }}>
                                <span>Wajib Absen</span>
                            </label>
                        </form>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn-action btn-edit">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </a>
                            <form action="{{ route('admin.employees.reset-password', $employee) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin mereset password karyawan ini menjadi 123456?')">
                                @csrf
                                <button type="submit" class="btn-action btn-reset">
                                    <i class="fas fa-key"></i>
                                    <span>Reset Password</span>
                                </button>
                            </form>
                            <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus karyawan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete">
                                    <i class="fas fa-trash"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state-cell">
                        Tidak ada data karyawan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
