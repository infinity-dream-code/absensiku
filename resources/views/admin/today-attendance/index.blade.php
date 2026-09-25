@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('styles')
<style>
    .page-header {
        margin-bottom: 28px;
    }
    .page-title {
        font-size: 28px;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 6px;
    }
    .page-subtitle {
        font-size: 15px;
        color: #6b7280;
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 16px;
    }
    .filter-form label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }
    .filter-form input[type="date"] {
        padding: 10px 14px;
        font-size: 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
    }
    .filter-form input[type="date"]:focus {
        border-color: #667eea;
    }
    .btn-apply {
        padding: 10px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-apply:hover {
        opacity: 0.95;
        transform: translateY(-1px);
    }
    
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    .stat-box {
        border-radius: 12px;
        padding: 24px;
        color: white;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .stat-box.present {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    .stat-box.leave {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    .stat-box.absent {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    .stat-box .stat-icon {
        font-size: 28px;
        margin-bottom: 12px;
        opacity: 0.95;
    }
    .stat-box .stat-number {
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 4px;
    }
    .stat-box .stat-text {
        font-size: 14px;
        font-weight: 600;
        opacity: 0.95;
    }
    
    .total-info {
        background: #f8fafc;
        border-radius: 10px;
        padding: 14px 20px;
        margin-bottom: 24px;
        font-size: 15px;
        font-weight: 600;
        color: #475569;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .employee-cards {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .employee-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 20px;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        background: white;
        border-left: 4px solid;
    }
    .employee-card.present {
        border-left-color: #10b981;
        background: linear-gradient(to right, #ecfdf5 0%, white 8%);
    }
    .employee-card.leave {
        border-left-color: #f59e0b;
        background: linear-gradient(to right, #fffbeb 0%, white 8%);
    }
    .employee-card.absent {
        border-left-color: #ef4444;
        background: linear-gradient(to right, #fef2f2 0%, white 8%);
    }
    .employee-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: bold;
        color: white;
        flex-shrink: 0;
    }
    .employee-card.present .employee-avatar { background: #10b981; }
    .employee-card.leave .employee-avatar { background: #f59e0b; }
    .employee-card.absent .employee-avatar { background: #ef4444; }
    .employee-info {
        flex: 1;
        min-width: 0;
    }
    .employee-name {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }
    .employee-username {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 6px;
    }
    .employee-detail {
        font-size: 13px;
        color: #4b5563;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .employee-detail i {
        font-size: 12px;
        color: #9ca3af;
    }
    .employee-card.absent .employee-detail {
        color: #dc2626;
    }
    .employee-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        flex-shrink: 0;
    }
    .employee-card.present .employee-badge {
        background: #d1fae5;
        color: #065f46;
    }
    .employee-card.leave .employee-badge {
        background: #fed7aa;
        color: #9a3412;
    }
    .employee-card.absent .employee-badge {
        background: #fee2e2;
        color: #991b1b;
    }
    .work-type-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        margin-top: 6px;
    }
    .work-type-tag.wfo { background: #dbeafe; color: #1e40af; }
    .work-type-tag.wfa { background: #e0e7ff; color: #4338ca; }
    .work-type-tag.wfh { background: #d1fae5; color: #065f46; }
    
    @media (max-width: 640px) {
        .stats-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Ringkasan absensi per tanggal</p>
</div>

<div style="margin-bottom: 16px;">
    <form method="POST" action="{{ route('admin.ref-bulan.refresh') }}" onsubmit="return confirm('Generate / update Ref Bulan untuk tahun berjalan?');" style="display: inline-block;">
        @csrf
        <button type="submit" class="btn-apply">
            <i class="fas fa-sync-alt"></i>
            Ref Bulan
        </button>
    </form>
</div>

<form method="GET" action="{{ route('admin.dashboard') }}" class="filter-card">
    <div class="filter-form">
        <label for="date">Pilih Tanggal</label>
        <input type="date" 
               id="date" 
               name="date" 
               value="{{ $selectedDate->format('Y-m-d') }}"
               max="{{ \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d') }}">
        <button type="submit" class="btn-apply">
            <i class="fas fa-search"></i>
            Lihat
        </button>
    </div>
</form>

<div class="stats-row">
    <div class="stat-box present">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-number">{{ $countPresent }}</div>
        <div class="stat-text">Sudah Absen</div>
    </div>
    <div class="stat-box leave">
        <div class="stat-icon"><i class="fas fa-umbrella-beach"></i></div>
        <div class="stat-number">{{ $countOnLeave }}</div>
        <div class="stat-text">Izin / Leave</div>
    </div>
    <div class="stat-box absent">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-number">{{ $countAbsent }}</div>
        <div class="stat-text">Tidak Absen</div>
    </div>
</div>

<div class="total-info">
    Total: {{ $totalEmployees }} karyawan
</div>

<h2 class="section-title">
    <i class="fas fa-users"></i>
    Daftar Karyawan
</h2>

<div class="employee-cards">
    @foreach($employeeCards as $card)
    <div class="employee-card {{ $card['status'] }}">
        <div class="employee-avatar">
            {{ strtoupper(mb_substr($card['user']->name, 0, 1)) }}
        </div>
        <div class="employee-info">
            <div class="employee-name">{{ $card['user']->name }}</div>
            @if(!empty($card['user']->username))
            <div class="employee-username">{{ '@' . $card['user']->username }}</div>
            @endif
            <div class="employee-detail">
                @if($card['status'] === 'absent')
                <i class="fas fa-exclamation-triangle"></i>
                @else
                <i class="fas fa-info-circle"></i>
                @endif
                {{ $card['detail'] }}
            </div>
            @if($card['status'] === 'present' && isset($card['work_type']))
            <span class="work-type-tag {{ strtolower($card['work_type']) }}">
                <i class="fas fa-briefcase"></i>
                {{ $card['work_type'] }}
            </span>
            @endif
        </div>
        <div class="employee-badge">{{ $card['label'] }}</div>
    </div>
    @endforeach
</div>

@if(count($employeeCards) === 0)
<div style="text-align: center; padding: 48px; color: #6b7280;">
    <i class="fas fa-users-slash" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
    <p>Tidak ada data karyawan.</p>
</div>
@endif
@endsection
