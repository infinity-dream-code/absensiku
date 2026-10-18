@extends('admin.layouts.app')

@section('title', 'Riwayat Lembur')

@section('styles')
<style>
    .page-header { margin-bottom: 32px; }
    .page-title { font-size: 32px; font-weight: bold; color: #1f2937; margin-bottom: 8px; }
    .page-subtitle { font-size: 16px; color: #6b7280; }
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 24px;
    }
    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        align-items: end;
    }
    .form-group { display: flex; flex-direction: column; }
    .form-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
    .form-input, .form-select {
        padding: 10px 14px;
        font-size: 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
    }
    .form-input:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    .btn-filter {
        padding: 10px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }
    .btn-export {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: #059669;
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
    }
    .btn-export:hover { background: #047857; color: white; }
    .export-bar {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .table-wrapper { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead { background: #f9fafb; }
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
    tbody tr:hover { background: #f9fafb; }
    .empty-cell { text-align: center; color: #9ca3af; padding: 40px 16px; }
    .badge-time {
        display: inline-block;
        background: #eef2ff;
        color: #4338ca;
        font-weight: 600;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 13px;
    }
    .pagination-wrap { padding: 16px 20px; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Riwayat Lembur</h1>
    <p class="page-subtitle">Rekap lembur per karyawan (hanya lihat)</p>
</div>

<div class="filter-card">
    <form method="GET" action="{{ route('admin.lembur-history.index') }}" class="filter-form">
        <div class="form-group">
            <label class="form-label" for="user_id">Karyawan</label>
            <select id="user_id" name="user_id" class="form-select">
                <option value="">Semua karyawan</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ (string) request('user_id') === (string) $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}{{ $employee->nip ? ' (' . $employee->nip . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="year">Tahun</label>
            <select id="year" name="year" class="form-select">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="month">Bulan</label>
            <select id="month" name="month" class="form-select">
                <option value="">Semua</option>
                @foreach($monthNames as $m => $label)
                    <option value="{{ $m }}" {{ (int)$month === (int)$m ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="search">Cari nama / NIP</label>
            <input type="text" id="search" name="search" class="form-input" value="{{ request('search') }}" placeholder="Cari...">
        </div>
        <div class="form-group">
            <button type="submit" class="btn-filter">
                <i class="fas fa-search"></i>
                <span>Filter</span>
            </button>
        </div>
    </form>
    <div class="export-bar">
        <a href="{{ route('admin.lembur-history.export', request()->query()) }}" class="btn-export">
            <i class="fas fa-file-excel"></i>
            <span>Export Excel</span>
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Jam</th>
                    <th>Durasi</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($overtimes as $item)
                    <tr>
                        <td>{{ $item->overtime_date->locale('id')->isoFormat('D MMM YYYY') }}</td>
                        <td>{{ $item->overtime_date->locale('id')->isoFormat('dddd') }}</td>
                        <td style="font-weight:600;">{{ $item->user->nip ?: '-' }}</td>
                        <td>{{ $item->user->name }}</td>
                        <td>
                            <span class="badge-time">
                                {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}
                                –
                                {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}
                            </span>
                        </td>
                        <td>{{ $item->durationLabel() }}</td>
                        <td>{{ $item->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-cell">Belum ada data lembur untuk filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($overtimes->hasPages())
        <div class="pagination-wrap">
            {{ $overtimes->links() }}
        </div>
    @endif
</div>
@endsection
