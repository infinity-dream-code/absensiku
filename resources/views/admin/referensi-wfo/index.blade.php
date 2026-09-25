@extends('admin.layouts.app')

@section('title', 'Referensi WFO')

@section('styles')
<style>
    .page-header { margin-bottom: 24px; }
    .page-title { font-size: 28px; font-weight: 700; color: #1f2937; margin-bottom: 6px; }
    .page-subtitle { color: #6b7280; font-size: 14px; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 20px; margin-bottom: 20px; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: end; }
    .form-group { display: flex; flex-direction: column; }
    .form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .form-select { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 14px; }
    .days-box { display: none; grid-column: 1 / -1; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 14px; }
    .days-box.active { display: block; }
    .days-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin-top: 8px; }
    .day-item { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #374151; }
    .btn-save { background: #4f46e5; color: #fff; border: 0; border-radius: 8px; padding: 10px 14px; cursor: pointer; font-weight: 600; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 10px; border-bottom: 1px solid #e5e7eb; font-size: 13px; text-align: left; }
    th { color: #6b7280; text-transform: uppercase; font-size: 11px; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Referensi WFO</h1>
    <p class="page-subtitle">Atur karyawan WFO harian atau berjadwal (Senin-Jumat).</p>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.referensi-wfo.save') }}" class="form-grid">
        @csrf
        <div class="form-group">
            <label class="form-label" for="user_id">Karyawan</label>
            <select id="user_id" name="user_id" class="form-select" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ (int) old('user_id', $selectedUserId) === (int) $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }} ({{ $employee->nip ?: $employee->nik }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="type">Type</label>
            <select id="type" name="type" class="form-select" required>
                @php
                    $selectedType = old('type', $selectedReference->type ?? 'daily');
                    $selectedDays = old('hari', $selectedReference ? $selectedReference->hari_array : []);
                @endphp
                <option value="daily" {{ $selectedType === 'daily' ? 'selected' : '' }}>daily</option>
                <option value="berjadwal" {{ $selectedType === 'berjadwal' ? 'selected' : '' }}>berjadwal</option>
            </select>
        </div>

        <div class="days-box {{ $selectedType === 'berjadwal' ? 'active' : '' }}" id="days-box">
            <div style="font-size: 13px; color: #475569; margin-bottom: 4px;">Pilih hari WFO (khusus Senin - Jumat):</div>
            <div class="days-grid">
                @php
                    $dayMap = [
                        'monday' => 'Senin',
                        'tuesday' => 'Selasa',
                        'wednesday' => 'Rabu',
                        'thursday' => 'Kamis',
                        'friday' => 'Jumat',
                    ];
                @endphp
                @foreach($dayMap as $dayValue => $dayLabel)
                    <label class="day-item">
                        <input type="checkbox" name="hari[]" value="{{ $dayValue }}" @checked(in_array($dayValue, $selectedDays, true))>
                        <span>{{ $dayLabel }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn-save">Simpan</button>
        </div>
    </form>
</div>

<div class="card">
    <div style="font-weight: 700; color: #1f2937; margin-bottom: 12px;">Daftar Referensi WFO</div>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Type</th>
                    <th>Hari</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                @forelse($references as $ref)
                    @php
                        $days = $ref->hari_array;
                        $labelMap = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat'];
                        $dayLabels = array_map(fn ($d) => $labelMap[$d] ?? $d, $days);
                    @endphp
                    <tr>
                        <td>{{ $ref->user->name ?? '-' }} ({{ $ref->user->nip ?? ($ref->user->nik ?? '-') }})</td>
                        <td>{{ $ref->type }}</td>
                        <td>{{ $ref->type === 'daily' ? 'Setiap hari kerja' : implode(', ', $dayLabels) }}</td>
                        <td>{{ optional($ref->updated_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center; color:#6b7280;">Belum ada referensi WFO.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type');
    const daysBox = document.getElementById('days-box');
    if (!typeSelect || !daysBox) return;

    const toggleDays = () => {
        if (typeSelect.value === 'berjadwal') {
            daysBox.classList.add('active');
        } else {
            daysBox.classList.remove('active');
        }
    };

    typeSelect.addEventListener('change', toggleDays);
    toggleDays();
});
</script>
@endsection
