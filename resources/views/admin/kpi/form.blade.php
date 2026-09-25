@extends('admin.layouts.app')

@section('title', 'Form Penilaian KPI')

@section('styles')
<style>
    .page-header { margin-bottom: 20px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center; }
    .page-title { font-size: 28px; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .page-subtitle { color: #6b7280; font-size: 14px; }
    .kpi-card { background:#fff; border-radius:14px; box-shadow:0 1px 3px rgba(0,0,0,.08); margin-bottom:16px; overflow:hidden; border:1px solid #eef2ff; }
    .kpi-card-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 18px; background:linear-gradient(90deg, #f5f3ff 0%, #ffffff 100%); border-bottom:1px solid #ede9fe; cursor:pointer; user-select:none; }
    .kpi-card-header h3 { margin:0; font-size:15px; font-weight:700; color:#312e81; display:flex; align-items:center; gap:8px; }
    .kpi-card-toggle { border:0; background:#ede9fe; color:#4f46e5; width:32px; height:32px; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; }
    .kpi-card.collapsed .kpi-card-body { display:none; }
    .kpi-card.collapsed .kpi-card-toggle i { transform: rotate(-90deg); }
    .kpi-card-body { padding:18px; }
    .form-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
    .form-control, .form-select, .form-textarea { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:14px; background:#fff; }
    .form-textarea { min-height:90px; resize:vertical; }
    .btn { border:0; border-radius:8px; padding:10px 14px; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
    .btn-primary { background:#4f46e5; color:#fff; }
    .btn-success { background:#059669; color:#fff; }
    .btn-export { background:#0f766e; color:#fff; }
    .btn-ghost { background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; }
    .employee-banner { display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:16px; padding:14px 16px; border-radius:12px; background:#f8fafc; border:1px solid #e2e8f0; }
    .score-box { background:#f5f3ff; border:2px solid #8b5cf6; border-radius:12px; padding:10px 16px; font-weight:700; min-width:130px; text-align:center; }
    .score-box .score-value { font-size:26px; color:#5b21b6; line-height:1.1; }
    .muted { color:#6b7280; font-size:13px; }
    .kpi-table { width:100%; border-collapse:collapse; }
    .kpi-table th, .kpi-table td { border:1px solid #e5e7eb; padding:10px; font-size:13px; }
    .kpi-table th { background:#ede9fe; text-align:left; color:#312e81; }
    .kpi-table tbody tr:nth-child(even) { background:#fafafa; }
    .actions-row { margin-top:16px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .filter-row { display:flex; gap:10px; flex-wrap:wrap; align-items:end; }
    .filter-row .field { flex:1; min-width:180px; }
    .filter-row .field-year { flex:0 0 120px; min-width:110px; }
    .month-row { display:flex; flex-wrap:wrap; gap:6px; margin-top:12px; padding-top:12px; border-top:1px solid #f3f4f6; }
    .month-chip { border:1px solid #e5e7eb; background:#fff; color:#374151; border-radius:999px; padding:6px 12px; font-size:12px; font-weight:600; cursor:pointer; line-height:1.2; }
    .month-chip:hover { border-color:#a5b4fc; color:#4338ca; background:#f5f3ff; }
    .month-chip.active { background:#4f46e5; border-color:#4f46e5; color:#fff; }
    .badge-individu { display:inline-block; font-size:10px; font-weight:700; padding:2px 6px; border-radius:999px; background:#fef3c7; color:#92400e; margin-left:6px; }
    .individu-table-divider td { background:#fef3c7; color:#92400e; font-size:12px; text-transform:uppercase; letter-spacing:.03em; }
    .individu-list { margin:0; padding:0; list-style:none; }
    .individu-list li { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13px; }
    .individu-add { display:grid; grid-template-columns:2fr 100px auto; gap:8px; margin-top:12px; align-items:end; }
    .individu-add.individu-add-penilai { grid-template-columns:1fr auto; }
    .individu-actions { display:flex; gap:6px; align-items:center; }
    .btn-danger { background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; padding:6px 10px; font-size:12px; }
    .btn-edit { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; padding:6px 10px; font-size:12px; }
    .bobot-locked { display:inline-block; min-width:48px; padding:8px 10px; border-radius:8px; background:#f3f4f6; color:#374151; font-weight:600; text-align:center; }
    @media (max-width: 700px) { .individu-add { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')
@php
    $currentYear = (int) now('Asia/Jakarta')->year;
    $monthShort = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
@endphp
<div class="page-header">
    <div>
        <h1 class="page-title">Form Penilaian KPI</h1>
        <p class="page-subtitle">
            Input atau lihat detail penilaian KPI
            @if(!$isAdmin) — Divisi {{ $auth->kpiRole->role ?? '-' }} @endif
        </p>
    </div>
    <a class="btn btn-ghost" href="{{ route($routePrefix . '.index') }}">
        <i class="fas fa-list"></i> Kembali ke Daftar
    </a>
</div>

<div class="kpi-card" id="card-filter">
    <div class="kpi-card-header" data-toggle-card="card-filter">
        <h3><i class="fas fa-filter"></i> Pilih Karyawan & Periode</h3>
        <button type="button" class="kpi-card-toggle" aria-label="Toggle"><i class="fas fa-chevron-down"></i></button>
    </div>
    <div class="kpi-card-body">
        <form method="GET" action="{{ route($routePrefix . '.form') }}" id="kpi-form-filter">
            <div class="filter-row">
                <div class="field">
                    <label class="form-label" for="user_id">Karyawan</label>
                    <select name="user_id" id="user_id" class="form-select" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (int)$selectedUserId === (int)$employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} ({{ $employee->nip ?: $employee->nik }})
                                @if($employee->kpiRole) — {{ $employee->kpiRole->role }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field-year">
                    <label class="form-label" for="tahun">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select" required>
                        @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                            <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <input type="hidden" name="bulan" id="bulan" value="{{ $bulan ?: now('Asia/Jakarta')->month }}">
            <div class="month-row">
                @for($m = 1; $m <= 12; $m++)
                    <button type="button" class="month-chip {{ (int)$bulan === $m ? 'active' : '' }}" data-month="{{ $m }}">{{ $monthShort[$m] }}</button>
                @endfor
            </div>
        </form>
    </div>
</div>

@if($selectedEmployee && count($indicators))
<div class="kpi-card" id="card-form">
    <div class="kpi-card-header" data-toggle-card="card-form">
        <h3><i class="fas fa-edit"></i> Detail & Input Penilaian</h3>
        <button type="button" class="kpi-card-toggle" aria-label="Toggle"><i class="fas fa-chevron-down"></i></button>
    </div>
    <div class="kpi-card-body">
        <div class="employee-banner">
            <div>
                <div style="font-weight:700; font-size:16px;">{{ $selectedEmployee->name }}</div>
                <div class="muted">Role: {{ $selectedEmployee->kpiRole->role ?? '-' }} | Periode: {{ \App\Services\KpiCalculator::monthName($bulan) }} {{ $tahun }}</div>
            </div>
            @if($existing)
                <div class="score-box">
                    <div class="muted">Skor Akhir</div>
                    <div class="score-value">{{ $existing->skor_akhir }}</div>
                    <div class="muted">{{ $existing->kategori }}</div>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route($routePrefix . '.store') }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $selectedEmployee->id }}">
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <div style="overflow-x:auto;">
                <table class="kpi-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Indikator KPI</th>
                            <th style="width:100px;">Bobot (%)</th>
                            <th style="width:140px;">Skor (1-10, boleh koma)</th>
                            <th style="width:120px;">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $individuIndicators = collect($indicators)->filter(fn ($row) => !empty($row['is_individu']));
                            $rowNo = 0;
                        @endphp
                        @foreach($indicators as $i => $indicator)
                            @if(!empty($indicator['is_individu'])) @continue @endif
                            @php
                                $rowNo++;
                                $isAbsensi = $absensiIndex !== null && (int) $i === (int) $absensiIndex;
                                $skor = (float) old('skor.'.$i, $scoreMap[$i] ?? 0);
                                $nilai = $skor > 0 ? \App\Services\KpiCalculator::nilaiAkhir((int) $indicator['bobot'], $skor) : 0;
                            @endphp
                            <tr>
                                <td>{{ $rowNo }}</td>
                                <td>
                                    {{ $indicator['nama'] }}
                                    @if($isAbsensi && $absensiInfo)
                                        <div class="muted" style="margin-top:4px;">
                                            Otomatis dari absensi: Perhitungan Final {{ rtrim(rtrim(number_format($absensiInfo['percent_final'], 1, '.', ''), '0'), '.') }}%
                                            → Skor {{ \App\Services\KpiCalculator::formatSkor($absensiInfo['skor']) }}
                                            (alpha {{ $absensiInfo['alpha'] }}, telat {{ $absensiInfo['late'] }})
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($isAdmin)
                                        <input type="number"
                                               name="bobot[{{ $i }}]"
                                               class="form-control bobot-input"
                                               min="0"
                                               max="100"
                                               data-index="{{ $i }}"
                                               value="{{ (int)($indicator['bobot'] ?? 0) }}">
                                    @else
                                        <span class="bobot-locked">{{ (int)($indicator['bobot'] ?? 0) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <input type="text" inputmode="decimal" name="skor[{{ $i }}]" class="form-control skor-input" required data-bobot="{{ (int)($indicator['bobot'] ?? 0) }}" data-index="{{ $i }}" value="{{ $skor ? \App\Services\KpiCalculator::formatSkor($skor) : '' }}" placeholder="7,5" @if($isAbsensi) readonly style="background:#f3f4f6; cursor:not-allowed;" @endif>
                                </td>
                                <td><span class="nilai-akhir" id="nilai-{{ $i }}">{{ $nilai ?: '-' }}</span></td>
                            </tr>
                        @endforeach
                        <tr class="individu-table-divider"><td colspan="5"><strong>Indikator Individu</strong></td></tr>
                        @if($individuIndicators->isNotEmpty())
                            @foreach($indicators as $i => $indicator)
                                @if(empty($indicator['is_individu'])) @continue @endif
                                @php
                                    $rowNo++;
                                    $skor = (float) old('skor.'.$i, $scoreMap[$i] ?? 0);
                                    $nilai = $skor > 0 ? \App\Services\KpiCalculator::nilaiAkhir((int) $indicator['bobot'], $skor) : 0;
                                @endphp
                                <tr>
                                    <td>{{ $rowNo }}</td>
                                    <td>{{ $indicator['nama'] }} <span class="badge-individu">Individu</span></td>
                                    <td>
                                        @if($isAdmin)
                                            <input type="number"
                                                   name="bobot[{{ $i }}]"
                                                   class="form-control bobot-input"
                                                   min="0"
                                                   max="100"
                                                   data-index="{{ $i }}"
                                                   value="{{ (int)($indicator['bobot'] ?? 0) }}">
                                        @else
                                            <span class="bobot-locked">{{ (int)($indicator['bobot'] ?? 0) }}</span>
                                        @endif
                                    </td>
                                    <td><input type="text" inputmode="decimal" name="skor[{{ $i }}]" class="form-control skor-input" required data-bobot="{{ (int)($indicator['bobot'] ?? 0) }}" data-index="{{ $i }}" value="{{ $skor ? \App\Services\KpiCalculator::formatSkor($skor) : '' }}" placeholder="7,5"></td>
                                    <td><span class="nilai-akhir" id="nilai-{{ $i }}">{{ $nilai ?: '-' }}</span></td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="5" class="muted" style="text-align:center; font-style:italic;">Belum ada indikator individu. Tambahkan di form bawah.</td></tr>
                        @endif
                        <tr>
                            <td></td>
                            <td><strong>TOTAL</strong></td>
                            <td><strong id="total-bobot">{{ collect($indicators)->sum('bobot') }}</strong></td>
                            <td></td>
                            <td><strong id="skor-akhir-preview">{{ $existing->skor_akhir ?? '-' }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">
                <label class="form-label" for="rekomendasi">Rekomendasi / Umpan Balik</label>
                <textarea name="rekomendasi" id="rekomendasi" class="form-textarea">{{ old('rekomendasi', $existing->rekomendasi ?? '') }}</textarea>
            </div>
            @if($isAdmin)
                <p class="muted" style="margin-top:8px;">Bobot dapat disesuaikan per karyawan. Perubahan bobot tersimpan untuk penilaian berikutnya.</p>
            @else
                <p class="muted" style="margin-top:8px;">Bobot dikunci. Hanya admin yang dapat mengubah bobot indikator.</p>
            @endif
            <div class="actions-row">
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Penilaian</button>
                @if($existing)
                    <a class="btn btn-export" href="{{ route($routePrefix . '.export', ['id' => $existing->id]) }}"><i class="fas fa-file-excel"></i> Export Excel</a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($selectedEmployee && $selectedEmployee->id_role)
<div class="kpi-card" id="card-individu">
    <div class="kpi-card-header" data-toggle-card="card-individu">
        <h3><i class="fas fa-user-tag"></i> Indikator Individu — {{ $selectedEmployee->name }}</h3>
        <button type="button" class="kpi-card-toggle" aria-label="Toggle"><i class="fas fa-chevron-down"></i></button>
    </div>
    <div class="kpi-card-body">
        @php $customIndicators = $selectedEmployee->kpi_indikator_individu ?? []; @endphp
        @if(count($customIndicators) > 0)
            <ul class="individu-list">
                @foreach($customIndicators as $idx => $custom)
                    <li>
                        <span>
                            <strong>{{ $custom['nama'] }}</strong>
                            @if(!empty($custom['bobot']))<span class="muted"> — bobot {{ $custom['bobot'] }}%</span>@endif
                        </span>
                        <div class="individu-actions">
                            <button type="button" class="btn btn-edit btn-edit-individu" data-index="{{ $idx }}" data-nama="{{ $custom['nama'] }}" data-bobot="{{ $custom['bobot'] ?? '' }}"><i class="fas fa-pen"></i></button>
                            <form method="POST" action="{{ route($routePrefix . '.indikator.destroy') }}" onsubmit="return confirm('Hapus indikator ini?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="user_id" value="{{ $selectedEmployee->id }}">
                                <input type="hidden" name="index" value="{{ $idx }}">
                                <input type="hidden" name="bulan" value="{{ $bulan }}">
                                <input type="hidden" name="tahun" value="{{ $tahun }}">
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="muted">Belum ada indikator individu.</p>
        @endif

        <form method="POST" action="{{ route($routePrefix . '.indikator.store') }}" class="individu-add{{ $isAdmin ? '' : ' individu-add-penilai' }}" id="individu-add-form">
            @csrf
            <input type="hidden" name="user_id" value="{{ $selectedEmployee->id }}">
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <div><label class="form-label">Nama indikator</label><input type="text" name="nama_indikator" id="individu-nama" class="form-control" required maxlength="255"></div>
            @if($isAdmin)
                <div><label class="form-label">Bobot (%) <span class="muted" style="font-weight:400;">opsional</span></label><input type="number" name="bobot" id="individu-bobot" class="form-control" min="1" max="50"></div>
            @endif
            <div><button type="submit" class="btn btn-primary" id="individu-submit-btn"><i class="fas fa-plus"></i> Tambah</button></div>
        </form>

        <form method="POST" action="{{ route($routePrefix . '.indikator.update') }}" class="individu-add{{ $isAdmin ? '' : ' individu-add-penilai' }}" id="individu-edit-form" style="display:none; margin-top:8px;">
            @csrf @method('PUT')
            <input type="hidden" name="user_id" value="{{ $selectedEmployee->id }}">
            <input type="hidden" name="index" id="edit-index" value="">
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <div><label class="form-label">Edit nama</label><input type="text" name="nama_indikator" id="edit-nama" class="form-control" required maxlength="255"></div>
            @if($isAdmin)
                <div><label class="form-label">Bobot (%)</label><input type="number" name="bobot" id="edit-bobot" class="form-control" min="1" max="50"></div>
            @endif
            <div style="display:flex; gap:6px;">
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                <button type="button" class="btn btn-ghost" id="cancel-edit-individu">Batal</button>
            </div>
        </form>
    </div>
</div>
@endif
@elseif($selectedUserId)
<div class="kpi-card"><div class="kpi-card-body"><p class="muted" style="margin:0;">Karyawan belum punya Role KPI / indikator.</p></div></div>
@endif
@endsection

@section('scripts')
<script>
document.querySelectorAll('[data-toggle-card]').forEach((header) => {
    header.addEventListener('click', function (e) {
        if (e.target.closest('a, input, select, button:not(.kpi-card-toggle)')) return;
        const card = document.getElementById(this.getAttribute('data-toggle-card'));
        if (card) card.classList.toggle('collapsed');
    });
});

const formFilter = document.getElementById('kpi-form-filter');
const bulanInput = document.getElementById('bulan');
const userSelect = document.getElementById('user_id');
const tahunSelect = document.getElementById('tahun');

function submitFilter() {
    if (!bulanInput.value) bulanInput.value = {{ (int) now('Asia/Jakarta')->month }};
    if (userSelect.value) formFilter.submit();
}
userSelect.addEventListener('change', submitFilter);
tahunSelect.addEventListener('change', submitFilter);
document.querySelectorAll('.month-chip').forEach((chip) => {
    chip.addEventListener('click', function () {
        if (!userSelect.value) { alert('Pilih karyawan dulu.'); return; }
        bulanInput.value = this.dataset.month;
        formFilter.submit();
    });
});

document.querySelectorAll('.skor-input').forEach((input) => input.addEventListener('input', recalculate));
document.querySelectorAll('.bobot-input').forEach((input) => input.addEventListener('input', onBobotChange));

function onBobotChange() {
    const idx = this.dataset.index;
    const skorInput = document.querySelector('.skor-input[data-index="' + idx + '"]');
    if (skorInput) {
        skorInput.dataset.bobot = this.value || '0';
    }
    recalculate();
}

function parseSkor(value) {
    const skor = parseFloat(String(value || '0').replace(',', '.'));
    return Number.isFinite(skor) ? skor : 0;
}

function recalculate() {
    let total = 0;
    let totalBobot = 0;
    document.querySelectorAll('.skor-input').forEach((input) => {
        const idx = input.dataset.index;
        const bobotInput = document.querySelector('.bobot-input[data-index="' + idx + '"]');
        const bobot = parseInt((bobotInput ? bobotInput.value : input.dataset.bobot) || '0', 10);
        const skor = parseSkor(input.value);
        let nilai = 0;
        if (skor >= 1 && skor <= 10) { nilai = Math.round((bobot * skor) / 10 * 10) / 10; total += nilai; }
        totalBobot += bobot;
        const el = document.getElementById('nilai-' + idx);
        if (el) el.textContent = nilai ? nilai : '-';
    });
    const preview = document.getElementById('skor-akhir-preview');
    if (preview) preview.textContent = total ? (Math.round(total * 10) / 10) : '-';
    const bobotEl = document.getElementById('total-bobot');
    if (bobotEl) bobotEl.textContent = totalBobot;
}
recalculate();

document.querySelectorAll('.btn-edit-individu').forEach((btn) => {
    btn.addEventListener('click', function () {
        document.getElementById('individu-add-form').style.display = 'none';
        document.getElementById('individu-edit-form').style.display = 'grid';
        document.getElementById('edit-index').value = this.dataset.index;
        document.getElementById('edit-nama').value = this.dataset.nama;
        const editBobot = document.getElementById('edit-bobot');
        if (editBobot) editBobot.value = this.dataset.bobot || '';
    });
});
document.getElementById('cancel-edit-individu')?.addEventListener('click', function () {
    document.getElementById('individu-edit-form').style.display = 'none';
    document.getElementById('individu-add-form').style.display = 'grid';
});
</script>
@endsection
