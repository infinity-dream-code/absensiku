@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

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
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border-left: 4px solid;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .stat-card.blue {
        border-left-color: #3b82f6;
    }
    
    .stat-card.green {
        border-left-color: #10b981;
    }
    
    .stat-card.purple {
        border-left-color: #8b5cf6;
    }
    
    .stat-card.emerald {
        border-left-color: #10b981;
    }
    
    .stat-card.red {
        border-left-color: #ef4444;
    }
    
    .stat-info {
        flex: 1;
    }
    
    .stat-label {
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        margin-bottom: 8px;
    }
    
    .stat-value {
        font-size: 32px;
        font-weight: bold;
        color: #1f2937;
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    
    .stat-icon.blue {
        background: #dbeafe;
        color: #2563eb;
    }
    
    .stat-icon.green {
        background: #d1fae5;
        color: #059669;
    }
    
    .stat-icon.purple {
        background: #ede9fe;
        color: #7c3aed;
    }
    
    .stat-icon.emerald {
        background: #d1fae5;
        color: #059669;
    }
    
    .stat-icon.red {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .quick-actions {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .quick-actions-title {
        font-size: 20px;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 20px;
    }
    
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }
    
    .action-card.blue {
        background: #f0f9ff;
    }
    
    .action-card.blue:hover {
        background: #e0f2fe;
    }
    
    .action-icon.blue {
        background: #0ea5e9;
    }
    
    .action-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .action-card.primary {
        background: #eef2ff;
    }
    
    .action-card.primary:hover {
        background: #e0e7ff;
    }
    
    .action-card.success {
        background: #f0fdf4;
    }
    
    .action-card.success:hover {
        background: #dcfce7;
    }
    
    .action-card.purple {
        background: #faf5ff;
    }
    
    .action-card.purple:hover {
        background: #f3e8ff;
    }
    
    .action-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }
    
    .action-icon.primary {
        background: #667eea;
    }
    
    .action-icon.success {
        background: #10b981;
    }
    
    .action-icon.purple {
        background: #8b5cf6;
    }
    
    .action-content {
        flex: 1;
    }
    
    .action-title {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }
    
    .action-desc {
        font-size: 14px;
        color: #6b7280;
    }
    
    .recent-activities {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-top: 32px;
    }
    
    .recent-activities-title {
        font-size: 20px;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 20px;
    }
    
    .activities-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .activities-table thead {
        background: #f9fafb;
    }
    
    .activities-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .activities-table td {
        padding: 12px 16px;
        font-size: 14px;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .activities-table tbody tr:hover {
        background: #f9fafb;
    }
    
    .activities-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .activity-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .activity-badge.checkin {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .activity-badge.checkout {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-badge.on-time {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-badge.late {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .status-badge.done {
        background: #e0e7ff;
        color: #3730a3;
    }
    
    .empty-activities {
        text-align: center;
        padding: 40px;
        color: #9ca3af;
        font-size: 14px;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .actions-grid {
            grid-template-columns: 1fr;
        }
        
        .page-title {
            font-size: 24px;
        }
        
        .activities-table {
            font-size: 12px;
        }
        
        .activities-table th,
        .activities-table td {
            padding: 8px 12px;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var card = document.getElementById('report-absensi-card');
        var dropdown = document.getElementById('report-absensi-dropdown');
        var validationCard = document.getElementById('validasi-card');
        var validationDropdown = document.getElementById('validasi-dropdown');

        if (card && dropdown) {
            card.addEventListener('click', function () {
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            });
        }

        if (validationCard && validationDropdown) {
            validationCard.addEventListener('click', function () {
                validationDropdown.style.display = validationDropdown.style.display === 'block' ? 'none' : 'block';
            });
        }

        document.addEventListener('click', function (e) {
            if (card && dropdown && !card.contains(e.target)) {
                dropdown.style.display = 'none';
            }
            if (validationCard && validationDropdown && !validationCard.contains(e.target)) {
                validationDropdown.style.display = 'none';
            }
        });
    });
</script>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Ringkasan absensi hari ini</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <!-- Total Karyawan -->
    <div class="stat-card blue">
        <div class="stat-info">
            <p class="stat-label">Total Karyawan</p>
            <p class="stat-value">{{ $totalEmployees }}</p>
        </div>
        <div class="stat-icon blue">
            <i class="fas fa-users"></i>
        </div>
    </div>
    
    <!-- Total Check-In -->
    <div class="stat-card green">
        <div class="stat-info">
            <p class="stat-label">Sudah Check-In</p>
            <p class="stat-value">{{ $totalCheckIn }}</p>
        </div>
        <div class="stat-icon green">
            <i class="fas fa-sign-in-alt"></i>
        </div>
    </div>
    
    <!-- Total Check-Out -->
    <div class="stat-card purple">
        <div class="stat-info">
            <p class="stat-label">Sudah Check-Out</p>
            <p class="stat-value">{{ $totalCheckOut }}</p>
        </div>
        <div class="stat-icon purple">
            <i class="fas fa-sign-out-alt"></i>
        </div>
    </div>
    
    <!-- Tepat Waktu -->
    <div class="stat-card emerald">
        <div class="stat-info">
            <p class="stat-label">Tepat Waktu</p>
            <p class="stat-value">{{ $totalOnTime }}</p>
        </div>
        <div class="stat-icon emerald">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
    
    <!-- Terlambat -->
    <div class="stat-card red">
        <div class="stat-info">
            <p class="stat-label">Terlambat</p>
            <p class="stat-value">{{ $totalLate }}</p>
        </div>
        <div class="stat-icon red">
            <i class="fas fa-clock"></i>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <h2 class="quick-actions-title">Quick Actions</h2>
    <div class="actions-grid">
        <a href="{{ route('admin.settings.index') }}" class="action-card primary">
            <div class="action-icon primary">
                <i class="fas fa-clock"></i>
            </div>
            <div class="action-content">
                <p class="action-title">Set Waktu</p>
                <p class="action-desc">Atur jam kerja</p>
            </div>
        </a>
        
        <a href="{{ route('admin.employees.index') }}" class="action-card success">
            <div class="action-icon success">
                <i class="fas fa-users"></i>
            </div>
            <div class="action-content">
                <p class="action-title">Kelola Karyawan</p>
                <p class="action-desc">Tambah/edit karyawan</p>
            </div>
        </a>
        
        <div class="action-card purple" style="position: relative; cursor: pointer;" id="report-absensi-card">
            <div class="action-icon purple">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="action-content">
                <p class="action-title">Report Absensi</p>
                <p class="action-desc">History, summary, validasi, WFO</p>
            </div>
            <div style="margin-left:auto; color:#6b7280;">
                <i class="fas fa-chevron-down"></i>
            </div>
            <div id="report-absensi-dropdown" style="display:none; position:absolute; left:0; right:0; top:100%; margin-top:8px; background:#ffffff; border-radius:8px; box-shadow:0 10px 20px rgba(15,23,42,0.15); padding:8px 0; z-index:20;">
                <a href="{{ route('admin.attendance-history.index') }}" style="display:flex; align-items:center; gap:10px; padding:10px 16px; text-decoration:none; color:#111827; font-size:14px;">
                    <i class="fas fa-history" style="width:18px; text-align:center;"></i>
                    <span>History Absensi</span>
                </a>
                <a href="{{ route('admin.attendance-summary.index') }}" style="display:flex; align-items:center; gap:10px; padding:10px 16px; text-decoration:none; color:#111827; font-size:14px;">
                    <i class="fas fa-chart-bar" style="width:18px; text-align:center;"></i>
                    <span>Summary Absensi</span>
                </a>
            </div>
        </div>

        <div class="action-card purple" style="position: relative; cursor: pointer;" id="validasi-card">
            <div class="action-icon purple">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="action-content">
                <p class="action-title">Validasi</p>
                <p class="action-desc">Validasi Alpha & WFO</p>
            </div>
            <div style="margin-left:auto; color:#6b7280;">
                <i class="fas fa-chevron-down"></i>
            </div>
            <div id="validasi-dropdown" style="display:none; position:absolute; left:0; right:0; top:100%; margin-top:8px; background:#ffffff; border-radius:8px; box-shadow:0 10px 20px rgba(15,23,42,0.15); padding:8px 0; z-index:20;">
                <a href="{{ route('admin.attendance-validation.index') }}" style="display:flex; align-items:center; gap:10px; padding:10px 16px; text-decoration:none; color:#111827; font-size:14px;">
                    <i class="fas fa-calendar-check" style="width:18px; text-align:center;"></i>
                    <span>Validasi Alpha</span>
                </a>
                <a href="{{ route('admin.wfo-validation.index') }}" style="display:flex; align-items:center; gap:10px; padding:10px 16px; text-decoration:none; color:#111827; font-size:14px;">
                    <i class="fas fa-user-check" style="width:18px; text-align:center;"></i>
                    <span>Validasi WFO</span>
                </a>
            </div>
        </div>
        
        <a href="{{ route('admin.referensi-wfo.index') }}" class="action-card blue">
            <div class="action-icon blue">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="action-content">
                <p class="action-title">Referensi WFO</p>
                <p class="action-desc">Atur jadwal WFO karyawan</p>
            </div>
        </a>
        
        <a href="{{ route('admin.location.index') }}" class="action-card blue">
            <div class="action-icon blue">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="action-content">
                <p class="action-title">Set Lokasi</p>
                <p class="action-desc">Atur lokasi kantor</p>
            </div>
        </a>
    </div>
</div>

<!-- Recent Activities -->
<div class="recent-activities">
    <h2 class="recent-activities-title">5 Aktivitas Terbaru</h2>
    
    @if($recentActivities->count() > 0)
    <table class="activities-table">
        <thead>
            <tr>
                <th>Nama Karyawan</th>
                <th>Aktivitas</th>
                <th>Jam</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentActivities as $activity)
            <tr>
                <td style="font-weight: 600;">{{ $activity['user_name'] }}</td>
                <td>
                    <span class="activity-badge {{ strtolower($activity['activity']) === 'check-in' ? 'checkin' : 'checkout' }}">
                        {{ $activity['activity'] }}
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($activity['time'])->setTimezone('Asia/Jakarta')->format('H:i:s') }}</td>
                <td>
                    @if($activity['status'] === 'Tepat Waktu')
                        <span class="status-badge on-time">Tepat Waktu</span>
                    @elseif($activity['status'] === 'Terlambat')
                        <span class="status-badge late">Terlambat</span>
                    @else
                        <span class="status-badge done">Selesai</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-activities">
        <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
        <p>Belum ada aktivitas hari ini</p>
    </div>
    @endif
</div>
@endsection
