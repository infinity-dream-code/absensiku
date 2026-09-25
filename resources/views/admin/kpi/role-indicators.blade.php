@extends('admin.layouts.app')

@section('title', 'Indikator Role KPI')

@section('styles')
<style>
    .page-header { margin-bottom: 20px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center; }
    .page-title { font-size: 28px; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .page-subtitle { color: #6b7280; font-size: 14px; }
    .kpi-card { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); margin-bottom:16px; border:1px solid #e5e7eb; }
    .kpi-card-body { padding: 18px; }
    .form-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
    .form-control, .form-select { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:14px; background:#fff; }
    .btn { border:0; border-radius:8px; padding:10px 14px; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
    .btn-primary { background:#4f46e5; color:#fff; }
    .btn-success { background:#059669; color:#fff; }
    .btn-ghost { background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; }
    .btn-danger { background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; padding:6px 10px; font-size:12px; }
    .kpi-table { width:100%; border-collapse:collapse; margin-top:16px; }
    .kpi-table th, .kpi-table td { border:1px solid #e5e7eb; padding:10px; font-size:13px; }
    .kpi-table th { background:#ede9fe; text-align:left; color:#312e81; }
    .actions-row { margin-top:16px; display:flex; gap:10px; flex-wrap:wrap; }
    .muted { color:#6b7280; font-size:13px; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Indikator Role KPI</h1>
        <p class="page-subtitle">Kelola indikator dan bobot per divisi/role</p>
    </div>
    <a class="btn btn-ghost" href="{{ route('admin.kpi.index') }}">
        <i class="fas fa-arrow-left"></i> Kembali ke Kelola KPI
    </a>
</div>

<div class="kpi-card">
    <div class="kpi-card-body">
        <form method="GET" action="{{ route('admin.kpi.indikator-role') }}" style="max-width:320px;">
            <label class="form-label" for="role_id">Pilih Role</label>
            <select name="role_id" id="role_id" class="form-select" onchange="this.form.submit()">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ (int)$selectedRoleId === (int)$role->id ? 'selected' : '' }}>
                        {{ $role->role }}
                    </option>
                @endforeach
            </select>
        </form>

        @if($selectedRole)
        <form method="POST" action="{{ route('admin.kpi.indikator-role.update') }}" id="role-indicator-form">
            @csrf
            <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">

            <table class="kpi-table" id="indicator-table">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Nama Indikator</th>
                        <th style="width:120px;">Bobot (%)</th>
                        <th style="width:70px;"></th>
                    </tr>
                </thead>
                <tbody id="indicator-rows">
                    @forelse($indicators as $i => $indicator)
                        <tr>
                            <td class="row-no">{{ $i + 1 }}</td>
                            <td>
                                <input type="text" name="nama[]" class="form-control" value="{{ $indicator['nama'] }}" required maxlength="255">
                            </td>
                            <td>
                                <input type="number" name="bobot[]" class="form-control" value="{{ $indicator['bobot'] }}" min="0" max="100">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="row-no">1</td>
                            <td><input type="text" name="nama[]" class="form-control" required maxlength="255"></td>
                            <td><input type="number" name="bobot[]" class="form-control" value="0" min="0" max="100"></td>
                            <td><button type="button" class="btn btn-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="actions-row">
                <button type="button" class="btn btn-primary" id="btn-add-row"><i class="fas fa-plus"></i> Tambah Indikator</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
            </div>
            <p class="muted" style="margin-top:12px;">Bobot boleh 0 untuk indikator tanpa bobot. Total bobot idealnya 100.</p>
        </form>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
const tbody = document.getElementById('indicator-rows');

function renumberRows() {
    tbody.querySelectorAll('tr').forEach((row, index) => {
        const noCell = row.querySelector('.row-no');
        if (noCell) noCell.textContent = index + 1;
    });
}

function bindRemoveButtons() {
    tbody.querySelectorAll('.btn-remove-row').forEach((btn) => {
        btn.onclick = function () {
            const rows = tbody.querySelectorAll('tr');
            if (rows.length <= 1) {
                alert('Minimal satu indikator harus ada.');
                return;
            }
            this.closest('tr').remove();
            renumberRows();
        };
    });
}

document.getElementById('btn-add-row')?.addEventListener('click', function () {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="row-no"></td>
        <td><input type="text" name="nama[]" class="form-control" required maxlength="255"></td>
        <td><input type="number" name="bobot[]" class="form-control" value="0" min="0" max="100"></td>
        <td><button type="button" class="btn btn-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    renumberRows();
    bindRemoveButtons();
});

bindRemoveButtons();
</script>
@endsection
