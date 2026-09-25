@extends('admin.layouts.app')

@section('title', 'Validasi WFO')

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
    .day-cell {
        border-radius: 10px;
        min-height: 120px;
        padding: 10px;
        font-size: 12px;
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.15s ease;
    }
    .day-num { font-size: 18px; font-weight: 700; color: #111827; }
    .day-name { color: #6b7280; font-size: 11px; text-transform: capitalize; }
    .day-meta { margin-top: 8px; font-size: 11px; color: #334155; line-height: 1.35; }
    .day-action { margin-top: 8px; }
    .status-wfo { background: #dcfce7; border-color: #86efac; }
    .status-mismatch { background: #fee2e2; border-color: #fca5a5; }
    .status-mismatch.clickable { cursor: pointer; }
    .status-wfo-late { background: #ffedd5; border-color: #fdba74; }
    .status-wfo-late.clickable { cursor: pointer; }
    .status-wfo-late.clickable:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(234,88,12,.2); }
    .status-neutral { background: #eef2ff; border-color: #c7d2fe; }
    .status-disabled { background: #f3f4f6; border-color: #d1d5db; color: #9ca3af; }
    .btn-validate {
        background: #ea580c;
        color: #fff;
        border: 0;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 11px;
        cursor: pointer;
        font-weight: 700;
    }
    .empty-state { color: #6b7280; text-align: center; padding: 30px; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Validasi WFO</h1>
    <p class="page-subtitle">Validasi jadwal WFO: ubah non-WFO jadi WFO, atau hapus telat WFO jika absen WFO setelah 10:30.</p>
</div>

<div class="card">
    <form method="GET" action="{{ route('admin.wfo-validation.index') }}" class="filter-form">
        <div class="form-group">
            <label class="form-label" for="user_id">Karyawan</label>
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
            <label class="form-label" for="ref_bulan_id">Bulan</label>
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
        <span class="legend-item"><span class="dot" style="background:#dcfce7;"></span> Sudah WFO (tidak telat)</span>
        <span class="legend-item"><span class="dot" style="background:#ffedd5;"></span> Sudah WFO tapi telat &gt; 10:30</span>
        <span class="legend-item"><span class="dot" style="background:#fee2e2;"></span> Harusnya WFO tapi bukan WFO</span>
        <span class="legend-item"><span class="dot" style="background:#eef2ff;"></span> Data normal lain</span>
        <span class="legend-item"><span class="dot" style="background:#f3f4f6;"></span> Libur/Weekend</span>
    </div>

    <div class="calendar-grid" style="margin-bottom: 8px;">
        @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
            <div class="weekday">{{ $weekday }}</div>
        @endforeach
    </div>

    @if(count($rows) > 0)
        @php
            $monthStart = \Carbon\Carbon::create($selectedRef->year, $selectedRef->month_code, 1, 0, 0, 0, 'Asia/Jakarta');
            $firstWeekday = (int) $monthStart->isoWeekday();
        @endphp
        <div class="calendar-grid">
            @for($i = 1; $i < $firstWeekday; $i++)
                <div></div>
            @endfor

            @foreach($rows as $row)
                @php
                    $statusClass = 'status-neutral';
                    if ($row['is_weekend'] || $row['is_holiday']) {
                        $statusClass = 'status-disabled';
                    } elseif (!empty($row['is_telat_wfo']) && $row['can_validate']) {
                        $statusClass = 'status-wfo-late clickable';
                    } elseif ($row['actual_work_type'] === 'WFO') {
                        $statusClass = 'status-wfo';
                    } elseif ($row['can_validate']) {
                        $statusClass = 'status-mismatch clickable';
                    }
                    $btnLabel = (!empty($row['is_telat_wfo']) && $row['actual_work_type'] === 'WFO')
                        ? 'Hapus Telat WFO'
                        : 'Ubah ke WFO';
                    $confirmText = (!empty($row['is_telat_wfo']) && $row['actual_work_type'] === 'WFO')
                        ? 'Yakin validasi WFO telat pada '.$row['date'].'? Flag telat akan dihapus.'
                        : 'Yakin ubah absensi '.$row['date'].' menjadi WFO?';
                @endphp
                <div class="day-cell {{ $statusClass }} {{ $row['can_validate'] ? 'js-validate-wfo' : '' }}"
                     @if($row['can_validate'])
                         data-attendance-id="{{ $row['attendance_id'] }}"
                         data-date="{{ $row['date'] }}"
                         data-confirm="{{ $confirmText }}"
                     @endif>
                    <div>
                        <div class="day-num">{{ \Carbon\Carbon::parse($row['date'])->format('d') }}</div>
                        <div class="day-name">{{ $row['day_name'] }}</div>
                        <div class="day-meta">
                            <div><strong>Absensi:</strong> {{ $row['actual_work_type'] }}@if(!empty($row['is_telat_wfo'])) <span style="color:#c2410c;">(telat)</span>@endif</div>
                            <div><strong>Harusnya:</strong> {{ $row['expected_work_type'] }}</div>
                        </div>
                    </div>
                    <div class="day-action">
                        @if($row['can_validate'])
                            <button type="button" class="btn-validate">{{ $btnLabel }}</button>
                        @elseif($row['is_weekend'] || $row['is_holiday'])
                            <span style="font-size:11px;">Libur</span>
                        @else
                            <span style="font-size:11px;">-</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">Data tidak tersedia.</div>
    @endif
</div>

<form id="validateWfoForm" method="POST" action="{{ route('admin.wfo-validation.apply') }}" style="display:none;">
    @csrf
    <input type="hidden" name="user_id" value="{{ $selectedUserId }}">
    <input type="hidden" name="ref_bulan_id" value="{{ $selectedRefId }}">
    <input type="hidden" id="attendance_id" name="attendance_id" value="">
</form>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.js-validate-wfo');
    const form = document.getElementById('validateWfoForm');
    const attendanceInput = document.getElementById('attendance_id');
    if (!buttons.length || !form || !attendanceInput) return;

    buttons.forEach((button) => {
        button.addEventListener('click', function () {
            const attendanceId = this.dataset.attendanceId;
            const date = this.dataset.date;

            Swal.fire({
                title: 'Validasi WFO?',
                text: this.dataset.confirm || `Yakin ubah absensi ${date} menjadi WFO?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Validasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#ea580c'
            }).then((result) => {
                if (result.isConfirmed) {
                    attendanceInput.value = attendanceId;
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
