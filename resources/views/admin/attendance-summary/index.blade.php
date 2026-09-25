@extends('admin.layouts.app')

@section('title', 'Summary Absensi')

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
        align-items: flex-end;
        gap: 16px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
    }
    .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }
    .form-select {
        padding: 10px 14px;
        font-size: 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
    }
    .form-select:focus {
        border-color: #667eea;
    }
    .btn-filter, .btn-export {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-filter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .btn-export {
        background: #10b981;
        color: white;
        text-decoration: none;
    }
    .summary-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .summary-table-wrapper {
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    thead {
        background: #f9fafb;
    }
    th, td {
        padding: 12px 16px;
        font-size: 14px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
    }
    th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        font-weight: 600;
    }
    tbody tr:hover {
        background: #f9fafb;
    }
    .empty-state {
        padding: 40px;
        text-align: center;
        color: #6b7280;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Summary Absensi</h1>
    <p class="page-subtitle">Rekap kinerja absensi per bulan (alpha, terlambat, dan persen)</p>
</div>

<div class="filter-card">
    <form method="GET" action="{{ route('admin.attendance-summary.index') }}" class="filter-form">
        <div class="form-group">
            <label for="ref_bulan_id" class="form-label">Pilih Bulan</label>
            <select id="ref_bulan_id" name="ref_bulan_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- Pilih Bulan --</option>
                @foreach($refMonths as $ref)
                    <option value="{{ $ref->id }}" {{ $selectedRefId == $ref->id ? 'selected' : '' }}>
                        {{ $ref->month_name }} {{ $ref->year }} ({{ $ref->working_days }} hari kerja)
                    </option>
                @endforeach
            </select>
        </div>
        @if($selectedRef)
        <div class="form-group">
            <label class="form-label">Info</label>
            <div style="font-size: 14px; color: #475569;">
                Bulan: <strong>{{ $selectedRef->month_name }} {{ $selectedRef->year }}</strong>,
                Hari kerja: <strong>{{ $selectedRef->working_days }}</strong>
            </div>
        </div>
        @endif
        <div class="form-group" style="margin-left: auto;">
            @if($selectedRef)
            <a href="{{ route('admin.attendance-summary.export', ['ref_bulan_id' => $selectedRefId]) }}" class="btn-export">
                <i class="fas fa-file-excel"></i>
                <span>Export Excel</span>
            </a>
            @endif
        </div>
    </form>
</div>

<div class="summary-card">
    <div class="summary-table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>NIP</th>
                    <th>Nama Karyawan</th>
                    <th>Jumlah Hari Kerja</th>
                    <th>Alpha</th>
                    <th>Terlambat</th>
                    <th>Persen</th>
                    <th>Is Telat WFO</th>
                    <th>Perhitungan Final</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['user']->nip ?: '-' }}</td>
                        <td>{{ $row['user']->name }}</td>
                        <td>{{ $row['worked_days'] }}/{{ $row['working_days'] }}</td>
                        <td>{{ $row['alpha'] }}</td>
                        <td>{{ $row['late'] }}</td>
                        <td>{{ $row['percent'] }}%</td>
                        <td>
                            @if(!empty($row['is_telat_wfo']))
                                <span style="color: #dc2626; font-weight: 600;">Ya</span>
                            @else
                                <span style="color: #6b7280;">Tidak</span>
                            @endif
                        </td>
                        <td style="font-weight: 700;">{{ $row['percent_final'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            @if($refMonths->isEmpty())
                                Belum ada data Ref Bulan. Silakan tekan tombol <strong>Ref Bulan</strong> di Dashboard terlebih dahulu.
                            @else
                                Tidak ada data untuk bulan yang dipilih.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

