@extends('admin.layouts.app')

@section('title', 'History Absensi')

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
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 24px;
    }
    
    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        align-items: end;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    
    .form-input, .form-select {
        padding: 10px 14px;
        font-size: 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.2s;
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
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .btn-export {
        padding: 10px 20px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .btn-export:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    .btn-export-monthly {
        padding: 10px 20px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-export-monthly:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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
        vertical-align: top;
    }
    
    tbody tr:hover {
        background: #f9fafb;
    }
    
    td.notes-cell {
        max-width: 200px;
        white-space: normal;
        word-wrap: break-word;
        font-size: 13px;
    }
    
    .badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    
    .badge-wfa {
        background: #e0e7ff;
        color: #4338ca;
    }
    
    .badge-wfo {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .badge-wfh {
        background: #d1fae5;
        color: #065f46;
    }
    
    .pagination-wrapper {
        padding: 16px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper nav {
        width: 100%;
    }

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
        line-height: 1.25;
    }

    .pagination-wrapper .pagination li.active span,
    .pagination-wrapper .pagination li.active a {
        background: #667eea;
        color: white;
        border-color: #667eea;
        font-weight: 600;
    }

    .pagination-wrapper .pagination li a:hover {
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

    /* Sembunyikan SVG/icon Tailwind yang jadi panah raksasa tanpa Tailwind CSS */
    .pagination-wrapper svg,
    .pagination-wrapper i,
    .pagination-wrapper [class*="fa-"] {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .pagination-info {
        text-align: center;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 12px;
    }
    
    .empty-state {
        padding: 48px;
        text-align: center;
        color: #6b7280;
    }
    
    .holiday-note {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 12px 16px;
        margin: 8px 0;
        border-radius: 6px;
    }
    
    .holiday-note-title {
        font-weight: 600;
        color: #92400e;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .holiday-note-content {
        font-size: 13px;
        color: #78350f;
        margin-top: 4px;
    }
    
    .date-group-header {
        background: #f9fafb;
        padding: 12px 16px;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .btn-view-location {
        padding: 6px 12px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    
    .btn-view-location:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }
    
    .btn-view-image {
        padding: 6px 12px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    
    .btn-view-image:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }
    
    .expand-cell {
        vertical-align: middle;
        padding: 12px 16px;
    }
    
    .btn-expand-logs {
        padding: 6px 10px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        transition: all 0.2s;
    }
    
    .btn-expand-logs:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
    }
    
    .btn-expand-logs[aria-expanded="true"] {
        background: #64748b;
    }
    
    .logs-expanded-row td {
        padding: 0;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    
    .logs-expanded-content {
        padding: 16px 16px 16px 64px;
    }
    
    .logs-expanded-title {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .log-items-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    
    .log-items-table th {
        padding: 10px 12px;
        text-align: left;
        background: #f1f5f9;
        font-weight: 600;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
    }
    
    .log-items-table td {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
        color: #374151;
    }
    
    .log-items-table tr:last-child td {
        border-bottom: none;
    }
    
    .log-items-table .btn-view-location {
        padding: 4px 10px;
        font-size: 11px;
    }
    
    .location-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .location-modal-content {
        background-color: white;
        border-radius: 12px;
        width: 100%;
        max-width: 800px;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
    }
    
    .location-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .location-modal-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
    }
    
    .location-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #6b7280;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    .location-modal-close:hover {
        background: #f3f4f6;
        color: #1f2937;
    }
    
    .location-modal-body {
        padding: 24px;
        overflow-y: auto;
    }
    
    .location-info {
        margin-top: 16px;
        padding: 16px;
        background: #f9fafb;
        border-radius: 8px;
    }
    
    .location-info p {
        margin: 8px 0;
        font-size: 14px;
        color: #374151;
    }
    
    .location-info strong {
        color: #1f2937;
    }
    
    @media (max-width: 768px) {
        .filter-form {
            grid-template-columns: 1fr;
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
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">History Absensi</h1>
    <p class="page-subtitle">Riwayat absensi semua karyawan</p>
</div>

<!-- Filter Card -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.attendance-history.index') }}" class="filter-form">
        <div class="form-group">
            <label for="date_from" class="form-label">Dari Tanggal</label>
            <input type="date" 
                   id="date_from" 
                   name="date_from" 
                   value="{{ request('date_from') }}"
                   class="form-input">
        </div>
        
        <div class="form-group">
            <label for="date_to" class="form-label">Sampai Tanggal</label>
            <input type="date" 
                   id="date_to" 
                   name="date_to" 
                   value="{{ request('date_to') }}"
                   class="form-input">
        </div>
        
        <div class="form-group">
            <label for="year" class="form-label">Tahun</label>
            <select id="year" name="year" class="form-select">
                <option value="">Semua</option>
                @for($y = date('Y'); $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        
        <div class="form-group">
            <label for="month" class="form-label">Bulan</label>
            <select id="month" name="month" class="form-select">
                <option value="">Semua</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m, 1)->locale('id')->isoFormat('MMMM') }}
                    </option>
                @endfor
            </select>
        </div>
        
        <div class="form-group">
            <label for="work_type" class="form-label">Jenis Kerja</label>
            <select id="work_type" name="work_type" class="form-select">
                <option value="all" {{ request('work_type') == 'all' || !request('work_type') ? 'selected' : '' }}>Semua</option>
                <option value="WFA" {{ request('work_type') == 'WFA' ? 'selected' : '' }}>WFA</option>
                <option value="WFO" {{ request('work_type') == 'WFO' ? 'selected' : '' }}>WFO</option>
                <option value="WFH" {{ request('work_type') == 'WFH' ? 'selected' : '' }}>WFH</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="user_id" class="form-label">Karyawan</label>
            <select id="user_id" name="user_id" class="form-select">
                <option value="">Semua karyawan</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ (string) request('user_id') === (string) $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}{{ $employee->nik ? ' (' . $employee->nik . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn-filter">
                <i class="fas fa-search"></i>
                <span>Filter</span>
            </button>
        </div>
    </form>
    
    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('admin.attendance-history.export', request()->all()) }}" class="btn-export">
            <i class="fas fa-file-excel"></i>
            <span>Export Absensi</span>
        </a>
        <button type="button" class="btn-export-monthly" onclick="exportMonthly()">
            <i class="fas fa-file-excel"></i>
            <span>Rekap Absensi Bulanan</span>
        </button>
    </div>
</div>

<!-- Table Card -->
<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width: 48px;"></th>
                    <th>Tanggal</th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Jenis</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Lokasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentDate = null;
                    $holidaysByDate = $holidaysByDate ?? [];
                @endphp
                @forelse($attendances as $attendance)
                @php
                    $attendanceDate = \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d');
                    $showDateHeader = $currentDate !== $attendanceDate;
                    $currentDate = $attendanceDate;
                    $holiday = isset($holidaysByDate[$attendanceDate]) ? $holidaysByDate[$attendanceDate] : null;
                @endphp
                
                @if($showDateHeader && $holiday && !empty($holiday->notes))
                <tr>
                    <td colspan="11" style="padding: 0;">
                        <div class="holiday-note">
                            <div class="holiday-note-title">
                                <i class="fas fa-calendar-day"></i>
                                <span>{{ $holiday->description }}</span>
                            </div>
                            <div class="holiday-note-content">
                                <i class="fas fa-info-circle"></i> {{ $holiday->notes }}
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
                
                <tr data-attendance-id="{{ $attendance->id }}" class="attendance-main-row">
                    <td class="expand-cell">
                        <button type="button" 
                                class="btn-expand-logs" 
                                onclick="toggleLogs({{ $attendance->id }}, this)"
                                title="Tampilkan / sembunyikan log absensi"
                                aria-expanded="false">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->locale('id')->isoFormat('D MMM YYYY') }}</td>
                    <td style="font-weight: 600;">{{ $attendance->user->nik }}</td>
                    <td>{{ $attendance->user->name }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower($attendance->work_type) }}">
                            {{ $attendance->work_type }}
                        </span>
                    </td>
                    <td>
                        @if($attendance->check_in)
                            {{ \Carbon\Carbon::parse($attendance->check_in)->setTimezone('Asia/Jakarta')->format('H:i:s') }}
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        @if($attendance->check_out)
                            {{ \Carbon\Carbon::parse($attendance->check_out)->setTimezone('Asia/Jakarta')->format('H:i:s') }}
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $status = $attendance->punctualityStatus($settings->check_in_end ?: '09:00:00');
                        @endphp
                        @if($status === 'Terlambat')
                            <span style="color: #dc2626; font-weight: 600;">Terlambat</span>
                        @elseif($status === 'Tepat Waktu')
                            <span style="color: #059669; font-weight: 600;">Tepat Waktu</span>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td class="notes-cell">
                        @if($attendance->notes)
                            {{ $attendance->notes }}
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        @if($attendance->latitude && $attendance->longitude)
                            @if($attendance->work_type === 'WFO')
                                @if($attendance->location_name)
                                    <span style="color: #374151; font-weight: 500;">{{ $attendance->location_name }}</span>
                                @else
                                    @if($attendance->location_valid)
                                        <span style="color: #059669; font-weight: 600;">✓ Valid</span>
                                    @else
                                        <span style="color: #dc2626; font-weight: 600;">✗ Di Luar Jangkauan</span>
                                    @endif
                                @endif
                            @else
                                {{-- WFA dan WFH: hanya tampilkan nama lokasi tanpa status valid --}}
                                @if($attendance->location_name)
                                    <span style="color: #374151; font-weight: 500;">{{ $attendance->location_name }}</span>
                                @else
                                    <span style="color: #6b7280;">Lokasi tersedia</span>
                                @endif
                            @endif
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                            @if($attendance->latitude && $attendance->longitude)
                                <button type="button" 
                                        class="btn-view-location" 
                                        onclick="viewLocation({{ $attendance->latitude }}, {{ $attendance->longitude }}, '{{ addslashes($attendance->user->name) }}', '{{ \Carbon\Carbon::parse($attendance->check_in)->setTimezone('Asia/Jakarta')->format('H:i:s') }}', '{{ $attendance->work_type }}', {{ $attendance->location_valid ? 'true' : 'false' }}, '{{ addslashes($attendance->location_name ?? '') }}')">
                                    <i class="fas fa-map-marker-alt"></i> Lokasi
                                </button>
                            @endif
                            @if($attendance->image)
                                <button type="button" 
                                        class="btn-view-image" 
                                        onclick="viewImage('{{ $attendance->image }}', '{{ addslashes($attendance->user->name) }}')">
                                    <i class="fas fa-image"></i> Gambar
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="empty-state">
                        Tidak ada data absensi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($paginator->hasPages())
    <div class="pagination-wrapper">
        <div>
            <div class="pagination-info">
                @if(!empty($paginateByDay))
                    Menampilkan hari {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
                    dari {{ $paginator->total() }} hari
                @else
                    Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
                    dari {{ $paginator->total() }} data
                @endif
            </div>
            {{ $paginator->onEachSide(1)->links('vendor.pagination.default') }}
        </div>
    </div>
    @elseif($paginator->total() > 0)
    <div class="pagination-wrapper">
        <div class="pagination-info">
            @if(!empty($paginateByDay))
                Menampilkan {{ $paginator->total() }} hari
            @else
                Menampilkan {{ $paginator->total() }} data
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Modal View Image -->
<div id="imageModal" class="location-modal">
    <div class="location-modal-content">
        <div class="location-modal-header">
            <h3 id="imageModalTitle">Gambar Absensi</h3>
            <button type="button" class="location-modal-close" onclick="closeImageModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="location-modal-body">
            <div style="text-align: center;">
                <img id="imageModalImage" src="" alt="Gambar Absensi" style="max-width: 100%; max-height: 70vh; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            </div>
            <div class="location-info">
                <p><strong>Nama:</strong> <span id="imageEmployeeName"></span></p>
            </div>
        </div>
    </div>
</div>

<!-- Modal View Location -->
<div id="locationModal" class="location-modal">
    <div class="location-modal-content">
        <div class="location-modal-header">
            <h3 id="locationModalTitle">Lokasi Check-In</h3>
            <button type="button" class="location-modal-close" onclick="closeLocationModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="location-modal-body">
            <div id="locationMap" style="width: 100%; height: 400px; border-radius: 8px;"></div>
            <div class="location-info">
                <p><strong>Nama:</strong> <span id="locationEmployeeName"></span></p>
                <p><strong>Waktu Check-In:</strong> <span id="locationCheckInTime"></span></p>
                <p id="locationStatusContainer" style="display: none;"><strong>Status Lokasi:</strong> <span id="locationStatus"></span></p>
                <p id="locationNameContainer" style="display: none;"><strong>Lokasi Check-In:</strong> <span id="locationName"></span></p>
            </div>
        </div>
    </div>
</div>

<!-- Modal View Logs -->
<div id="logsModal" class="location-modal">
    <div class="location-modal-content">
        <div class="location-modal-header">
            <h3 id="logsModalTitle">Log Absensi</h3>
            <button type="button" class="location-modal-close" onclick="closeLogsModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="location-modal-body">
            <div id="logsContent" style="min-height: 200px;">
                <div style="text-align: center; padding: 40px;">
                    <div class="swal2-loader"></div>
                    <p style="margin-top: 16px; color: #6b7280;">Memuat log absensi...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let locationMap;
    let locationMarker;
    
    function viewLocation(lat, lng, employeeName, checkInTime, workType, locationValid, locationName) {
        document.getElementById('locationEmployeeName').textContent = employeeName;
        document.getElementById('locationCheckInTime').textContent = checkInTime;
        
        // Show location status only for WFO
        const locationStatusContainer = document.getElementById('locationStatusContainer');
        const locationStatusSpan = document.getElementById('locationStatus');
        if (workType === 'WFO') {
            locationStatusSpan.innerHTML = locationValid 
                ? '<span style="color: #059669; font-weight: 600;">✓ Valid</span>' 
                : '<span style="color: #dc2626; font-weight: 600;">✗ Di Luar Jangkauan</span>';
            locationStatusContainer.style.display = 'block';
        } else {
            // WFA dan WFH tidak perlu status valid
            locationStatusContainer.style.display = 'none';
        }
        
        // Show location name if available
        const locationNameContainer = document.getElementById('locationNameContainer');
        const locationNameSpan = document.getElementById('locationName');
        if (locationName && locationName.trim() !== '') {
            locationNameSpan.textContent = locationName;
            locationNameContainer.style.display = 'block';
        } else {
            locationNameContainer.style.display = 'none';
        }
        
        document.getElementById('locationModal').style.display = 'flex';
        
        // Initialize or update map
        if (locationMap) {
            locationMap.remove();
        }
        
        locationMap = L.map('locationMap').setView([lat, lng], 17);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(locationMap);
        
        // Add marker
        if (locationMarker) {
            locationMarker.remove();
        }
        
        // Marker color: green untuk valid/invalid (WFO), atau blue untuk WFA/WFH
        let markerColor = 'blue'; // default untuk WFA/WFH
        if (workType === 'WFO') {
            markerColor = locationValid ? 'green' : 'red';
        }
        
        locationMarker = L.marker([lat, lng], {
            icon: L.icon({
                iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${markerColor}.png`,
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        }).addTo(locationMap);
        
        let popupContent = `<div style="padding: 4px;">
                <strong>${employeeName}</strong><br>
                Check-In: ${checkInTime}`;
        
        // Hanya tampilkan status untuk WFO
        if (workType === 'WFO') {
            popupContent += `<br>Status: ${locationValid ? '✓ Valid' : '✗ Di Luar Jangkauan'}`;
        }
        
        if (locationName && locationName.trim() !== '') {
            popupContent += `<br>Lokasi: ${locationName}`;
        }
        popupContent += `</div>`;
        
        locationMarker.bindPopup(popupContent).openPopup();
    }
    
    function closeLocationModal() {
        document.getElementById('locationModal').style.display = 'none';
    }
    
    function viewLocationForLog(attendanceId, logIndex) {
        const data = window._logsByAttendance && window._logsByAttendance[attendanceId];
        if (!data || !data.logs[logIndex]) return;
        const log = data.logs[logIndex];
        const attendance = data.attendance;
        const timeStr = new Date(log.check_in_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        viewLocation(
            log.latitude,
            log.longitude,
            attendance.user ? attendance.user.name : '',
            timeStr,
            log.status || 'WFA',
            true,
            log.location_name || ''
        );
    }
    
    function viewImage(imageUrl, employeeName) {
        document.getElementById('imageEmployeeName').textContent = employeeName;
        document.getElementById('imageModalImage').src = imageUrl;
        document.getElementById('imageModal').style.display = 'flex';
    }
    
    function closeImageModal() {
        document.getElementById('imageModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const locationModal = document.getElementById('locationModal');
        const imageModal = document.getElementById('imageModal');
        const logsModal = document.getElementById('logsModal');
        if (event.target == locationModal) {
            closeLocationModal();
        }
        if (event.target == imageModal) {
            closeImageModal();
        }
        if (event.target == logsModal) {
            closeLogsModal();
        }
    }
    
    function toggleLogs(attendanceId, btn) {
        const row = btn.closest('tr');
        const nextRow = row.nextElementSibling;
        const isExpanded = nextRow && nextRow.classList.contains('logs-expanded-row');
        
        if (isExpanded) {
            nextRow.remove();
            btn.setAttribute('aria-expanded', 'false');
            btn.querySelector('i').className = 'fas fa-plus';
            return;
        }
        
        const expandedRow = document.createElement('tr');
        expandedRow.className = 'logs-expanded-row';
        expandedRow.setAttribute('data-attendance-logs', attendanceId);
        expandedRow.innerHTML = `
            <td colspan="11">
                <div class="logs-expanded-content">
                    <div class="logs-expanded-title">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Memuat log absensi...</span>
                    </div>
                </div>
            </td>
        `;
        row.insertAdjacentElement('afterend', expandedRow);
        btn.setAttribute('aria-expanded', 'true');
        btn.querySelector('i').className = 'fas fa-minus';
        
        axios.get(`/admin/attendance-history/${attendanceId}/logs`)
            .then(response => {
                const { attendance, logs } = response.data;
                const container = expandedRow.querySelector('.logs-expanded-content');
                
                if (!logs || logs.length === 0) {
                    container.innerHTML = `
                        <div class="logs-expanded-title" style="color: #6b7280;">
                            <i class="fas fa-info-circle"></i>
                            <span>Tidak ada log absensi untuk absensi ini.</span>
                        </div>
                    `;
                    return;
                }
                
                window._logsByAttendance = window._logsByAttendance || {};
                window._logsByAttendance[attendanceId] = { logs, attendance };
                
                let tableRows = '';
                logs.forEach((log, index) => {
                    const checkInTime = new Date(log.check_in_time).toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                    const statusBadgeClass = log.status === 'WFA' ? 'badge-wfa' : (log.status === 'WFO' ? 'badge-wfo' : 'badge-wfh');
                    const hasLocation = log.latitude && log.longitude;
                    const locBtn = hasLocation ? `
                        <button type="button" class="btn-view-location" onclick="viewLocationForLog(${attendanceId}, ${index})">
                            <i class="fas fa-map-marker-alt"></i> Lokasi
                        </button>
                    ` : '<span style="color: #9ca3af;">-</span>';
                    
                    const locDisplay = log.location_name ? String(log.location_name).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : '-';
                    tableRows += `
                        <tr>
                            <td style="width: 40px;">${index + 1}</td>
                            <td>${checkInTime}</td>
                            <td><span class="badge ${statusBadgeClass}">${log.status}</span></td>
                            <td>${locDisplay}</td>
                            <td>${locBtn}</td>
                        </tr>
                    `;
                });
                
                container.innerHTML = `
                    <div class="logs-expanded-title">
                        <i class="fas fa-list"></i>
                        <span>Log Absensi</span>
                    </div>
                    <table class="log-items-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Waktu</th>
                                <th>Jenis</th>
                                <th>Lokasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                    </table>
                `;
            })
            .catch(error => {
                const container = expandedRow.querySelector('.logs-expanded-content');
                container.innerHTML = `
                    <div class="logs-expanded-title" style="color: #dc2626;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Gagal memuat log absensi. Silakan coba lagi.</span>
                    </div>
                `;
                console.error('Error loading logs:', error);
            });
    }
    
    function closeLogsModal() {
        const m = document.getElementById('logsModal');
        if (m) m.style.display = 'none';
    }
    
    // Export Monthly Summary
    function exportMonthly() {
        const yearSelect = document.getElementById('year');
        const monthSelect = document.getElementById('month');
        const year = yearSelect ? yearSelect.value : '';
        const month = monthSelect ? monthSelect.value : '';
        
        // Bisa export meskipun "Semua" dipilih
        const url = '{{ route("admin.attendance-history.export-monthly") }}?year=' + (year || '') + '&month=' + (month || '');
        window.location.href = url;
    }
</script>
@endsection

