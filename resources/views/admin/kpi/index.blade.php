@extends('admin.layouts.app')

@section('title', 'Kelola KPI')

@section('styles')
<style>
    .page-header { margin-bottom: 20px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center; }
    .page-title { font-size: 28px; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .page-subtitle { color: #6b7280; font-size: 14px; }
    .kpi-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 16px; border: 1px solid #e5e7eb; }
    .kpi-card-body { padding: 16px; }
    .filter-row { display:flex; gap:10px; flex-wrap:wrap; align-items:end; }
    .filter-row .field { flex:1; min-width:180px; }
    .filter-row .field-year { flex:0 0 120px; min-width:110px; }
    .form-label { display:block; font-size:12px; font-weight:600; color:#6b7280; margin-bottom:6px; }
    .form-select { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:9px 11px; font-size:14px; background:#fff; }
    .btn { border:0; border-radius:8px; padding:9px 12px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-size:13px; }
    .btn-primary { background:#4f46e5; color:#fff; }
    .btn-ghost { background:#fff; color:#374151; border:1px solid #e5e7eb; }
    .btn-export { background:#0f766e; color:#fff; }
    .muted { color:#6b7280; font-size:13px; }

    .month-row {
        display:flex; flex-wrap:wrap; gap:6px; margin-top:12px; padding-top:12px;
        border-top:1px solid #f3f4f6;
    }
    .month-chip {
        border:1px solid #e5e7eb; background:#fff; color:#374151;
        border-radius:999px; padding:6px 12px; font-size:12px; font-weight:600;
        cursor:pointer; line-height:1.2;
    }
    .month-chip:hover { border-color:#a5b4fc; color:#4338ca; background:#f5f3ff; }
    .month-chip.active { background:#4f46e5; border-color:#4f46e5; color:#fff; }

    .kpi-table { width:100%; border-collapse:collapse; }
    .kpi-table th, .kpi-table td { border:1px solid #e5e7eb; padding:10px; font-size:13px; }
    .kpi-table th { background:#f9fafb; text-align:left; color:#374151; }
    .kpi-table tbody tr:nth-child(even) { background:#fafafa; }
    .result-meta { margin-top:14px; display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }
    .pagination-wrapper { margin-top:14px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
    .pagination { display:flex; gap:6px; flex-wrap:wrap; }
    .pagination a, .pagination span {
        display:inline-flex; align-items:center; justify-content:center;
        min-width:32px; padding:7px 10px; border-radius:8px; border:1px solid #e5e7eb;
        text-decoration:none; color:#374151; font-size:12px; font-weight:600; background:#fff;
    }
    .pagination .active span { background:#4f46e5; border-color:#4f46e5; color:#fff; }
    .pagination .disabled span { color:#9ca3af; background:#f9fafb; }
</style>
@endsection

@section('content')
@php
    $currentYear = (int) now('Asia/Jakarta')->year;
    $selectedYear = request('search_tahun', $currentYear);
    $selectedMonth = request('search_bulan', '');
    $monthShort = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
@endphp
<div class="page-header">
    <div>
        <h1 class="page-title">Kelola KPI</h1>
        <p class="page-subtitle">
            Daftar penilaian KPI
            @if(!$isAdmin) — Divisi {{ $auth->kpiRole->role ?? '-' }} @endif
        </p>
    </div>
    <a class="btn btn-primary" href="{{ route($routePrefix . '.form') }}">
        <i class="fas fa-plus"></i> Tambah Penilaian
    </a>
</div>

<div class="kpi-card">
    <div class="kpi-card-body">
        <form method="GET" action="{{ route($routePrefix . '.index') }}" id="kpi-search-form">
            <div class="filter-row">
                <div class="field">
                    <label class="form-label">Karyawan</label>
                    <select name="search_user_id" class="form-select" id="search_user_id">
                        <option value="">Semua karyawan</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string) request('search_user_id') === (string) $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field-year">
                    <label class="form-label">Tahun</label>
                    <select name="search_tahun" id="search_tahun" class="form-select">
                        @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                            <option value="{{ $y }}" {{ (string) $selectedYear === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                @if($hasSearchFilter)
                    <a href="{{ route($routePrefix . '.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </div>

            <input type="hidden" name="search_bulan" id="search_bulan" value="{{ $selectedMonth }}">

            <div class="month-row">
                <button type="button" class="month-chip {{ $selectedMonth === '' || $selectedMonth === null ? 'active' : '' }}" data-month="">Semua</button>
                @for($m = 1; $m <= 12; $m++)
                    <button type="button"
                            class="month-chip {{ (string) $selectedMonth === (string) $m ? 'active' : '' }}"
                            data-month="{{ $m }}">
                        {{ $monthShort[$m] }}
                    </button>
                @endfor
            </div>
        </form>

        <div class="result-meta">
            <p class="muted" style="margin:0;">
                {{ $searchResults->total() }} data
                @if($searchResults->total() > 0)
                    · {{ $searchResults->firstItem() }}–{{ $searchResults->lastItem() }}
                @endif
            </p>
        </div>

        @if($searchResults->isNotEmpty())
            <div style="overflow-x:auto; margin-top:10px;">
                <table class="kpi-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Periode</th>
                            <th>Role</th>
                            <th>Skor Akhir</th>
                            <th>Kategori</th>
                            <th>Penilai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($searchResults as $row)
                            <tr>
                                <td>{{ $row->user->name ?? '-' }}</td>
                                <td>{{ \App\Services\KpiCalculator::monthName($row->bulan) }} {{ $row->tahun }}</td>
                                <td>{{ $row->role->role ?? '-' }}</td>
                                <td><strong>{{ $row->skor_akhir }}</strong></td>
                                <td>{{ $row->kategori }}</td>
                                <td>{{ $row->penilai->name ?? '-' }}</td>
                                <td style="white-space:nowrap;">
                                    <a class="btn btn-primary" style="padding:6px 10px;"
                                       href="{{ route($routePrefix . '.form', ['user_id' => $row->id_user, 'bulan' => $row->bulan, 'tahun' => $row->tahun]) }}">
                                        Detail
                                    </a>
                                    <a class="btn btn-export" style="padding:6px 10px;"
                                       href="{{ route($routePrefix . '.export', ['id' => $row->id]) }}">
                                        Export
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($searchResults->lastPage() > 1)
            <div class="pagination-wrapper">
                <p class="muted" style="margin:0;">Halaman {{ $searchResults->currentPage() }} / {{ $searchResults->lastPage() }}</p>
                <div class="pagination">
                    @if($searchResults->onFirstPage())
                        <span class="disabled"><span>‹</span></span>
                    @else
                        <a href="{{ $searchResults->previousPageUrl() }}">‹</a>
                    @endif

                    @php
                        $start = max(1, $searchResults->currentPage() - 2);
                        $end = min($searchResults->lastPage(), $searchResults->currentPage() + 2);
                    @endphp
                    @foreach(range($start, $end) as $page)
                        @if($page === $searchResults->currentPage())
                            <span class="active"><span>{{ $page }}</span></span>
                        @else
                            <a href="{{ $searchResults->url($page) }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($searchResults->hasMorePages())
                        <a href="{{ $searchResults->nextPageUrl() }}">›</a>
                    @else
                        <span class="disabled"><span>›</span></span>
                    @endif
                </div>
            </div>
            @endif
        @else
            <p class="muted" style="margin-top:12px;">Tidak ada data untuk filter ini.</p>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
const form = document.getElementById('kpi-search-form');
const bulanInput = document.getElementById('search_bulan');

document.querySelectorAll('.month-chip').forEach((chip) => {
    chip.addEventListener('click', function () {
        bulanInput.value = this.dataset.month || '';
        form.submit();
    });
});

document.getElementById('search_tahun').addEventListener('change', function () {
    bulanInput.value = '';
    form.submit();
});

document.getElementById('search_user_id').addEventListener('change', function () {
    form.submit();
});
</script>
@endsection
