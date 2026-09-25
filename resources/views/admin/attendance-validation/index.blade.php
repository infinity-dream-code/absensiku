@extends('admin.layouts.app')

@section('title', 'Validasi Absensi')

@section('styles')
<style>
    .page-header { margin-bottom: 24px; }
    .page-title { font-size: 28px; font-weight: 700; color: #1f2937; margin-bottom: 6px; }
    .page-subtitle { color: #6b7280; font-size: 14px; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 20px; margin-bottom: 20px; }
    .filter-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: end; }
    .form-group { display: flex; flex-direction: column; }
    .form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .form-select { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 14px; }
    .btn-filter { background: #4f46e5; color: #fff; border: 0; border-radius: 8px; padding: 10px 14px; cursor: pointer; font-weight: 600; }
    .legend { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
    .legend-item { display: inline-flex; gap: 6px; align-items: center; font-size: 12px; color: #4b5563; }
    .dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
    .calendar-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 10px; }
    .weekday { text-align: center; font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; padding-bottom: 6px; }
    .day-cell { border-radius: 10px; min-height: 88px; padding: 10px; font-size: 12px; border: 1px solid #e5e7eb; display: flex; flex-direction: column; justify-content: space-between; }
    .day-num { font-size: 18px; font-weight: 700; color: #111827; }
    .day-name { color: #6b7280; font-size: 11px; text-transform: capitalize; }
    .day-status { font-size: 11px; font-weight: 700; margin-top: 8px; }
    .status-ontime { background: #dcfce7; border-color: #86efac; }
    .status-late { background: #ffedd5; border-color: #fdba74; }
    .status-alpha { background: #fee2e2; border-color: #fca5a5; cursor: pointer; }
    .status-disabled, .status-future { background: #f3f4f6; border-color: #d1d5db; color: #9ca3af; }
    .status-leave { background: #fce7f3; border-color: #f9a8d4; }
    .status-alpha:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(239,68,68,.2); }
    .empty-state { color: #6b7280; text-align: center; padding: 30px; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Validasi Absensi</h1>
    <p class="page-subtitle">Klik tanggal status Alpha untuk validasi menjadi terlambat (check-in jam 12:00).</p>
</div>

<div class="card">
    <form method="GET" action="{{ route('admin.attendance-validation.index') }}" class="filter-form">
        <div class="form-group">
            <label for="user_id" class="form-label">Karyawan</label>
            <select id="user_id" name="user_id" class="form-select" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $selectedUserId === $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }} ({{ $employee->nip ?: $employee->nik }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="ref_bulan_id" class="form-label">Bulan</label>
            <select id="ref_bulan_id" name="ref_bulan_id" class="form-select" required>
                @foreach($refMonths as $ref)
                    <option value="{{ $ref->id }}" {{ $selectedRefId === $ref->id ? 'selected' : '' }}>
                        {{ $ref->month_name }} {{ $ref->year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-filter">Tampilkan</button>
        </div>
    </form>
</div>

@if($selectedEmployee && $selectedRef)
<div class="card">
    <div style="margin-bottom: 12px; font-size: 14px; color: #374151;">
        Karyawan: <strong>{{ $selectedEmployee->name }}</strong> ({{ $selectedEmployee->nip ?: $selectedEmployee->nik }}) -
        Bulan: <strong>{{ $selectedRef->month_name }} {{ $selectedRef->year }}</strong>
    </div>

    <div class="legend">
        <span class="legend-item"><span class="dot" style="background:#dcfce7;"></span> Tepat Waktu</span>
        <span class="legend-item"><span class="dot" style="background:#ffedd5;"></span> Terlambat</span>
        <span class="legend-item"><span class="dot" style="background:#fee2e2;"></span> Alpha</span>
        <span class="legend-item"><span class="dot" style="background:#fce7f3;"></span> Izin/Cuti/Sakit</span>
        <span class="legend-item"><span class="dot" style="background:#f3f4f6;"></span> Belum lewat / Libur</span>
    </div>

    <div class="calendar-grid" style="margin-bottom: 8px;">
        @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
            <div class="weekday">{{ $weekday }}</div>
        @endforeach
    </div>

    <div class="calendar-grid">
        @php
            $monthStart = \Carbon\Carbon::create($selectedRef->year, $selectedRef->month_code, 1, 0, 0, 0, 'Asia/Jakarta');
            $firstWeekday = (int) $monthStart->isoWeekday();
        @endphp
        @for($i = 1; $i < $firstWeekday; $i++)
            <div></div>
        @endfor

        @foreach($calendar as $day)
            <div class="day-cell status-{{ $day['status'] }} {{ $day['can_validate'] ? 'js-validate' : '' }}"
                 data-date="{{ $day['date'] }}"
                 data-day="{{ $day['day_name'] }}"
                 data-can-validate="{{ $day['can_validate'] ? '1' : '0' }}">
                <div>
                    <div class="day-num">{{ $day['day_num'] }}</div>
                    <div class="day-name">{{ $day['day_name'] }}</div>
                </div>
                <div class="day-status">{{ $day['status_label'] }}</div>
            </div>
        @endforeach
    </div>
</div>

<form id="validateLateForm" method="POST" action="{{ route('admin.attendance-validation.validate-late') }}" style="display:none;">
    @csrf
    <input type="hidden" name="user_id" value="{{ $selectedUserId }}">
    <input type="hidden" name="ref_bulan_id" value="{{ $selectedRefId }}">
    <input type="hidden" id="validateDateInput" name="date" value="">
</form>
@else
<div class="card">
    <div class="empty-state">Pilih karyawan dan bulan dulu untuk menampilkan kalender validasi.</div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cells = document.querySelectorAll('.js-validate');
    const dateInput = document.getElementById('validateDateInput');
    const form = document.getElementById('validateLateForm');
    if (!cells.length || !dateInput || !form) return;

    cells.forEach((cell) => {
        cell.addEventListener('click', function () {
            if (this.dataset.canValidate !== '1') return;
            const date = this.dataset.date;
            const day = this.dataset.day;
            Swal.fire({
                title: 'Validasi Alpha?',
                text: `${day}, ${date} akan diubah jadi TERLAMBAT (check-in 12:00).`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Validasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#ea580c'
            }).then((result) => {
                if (result.isConfirmed) {
                    dateInput.value = date;
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
