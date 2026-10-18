@extends('layouts.app')

@section('title', 'Lembur - Sistem Absensi')

@section('styles')
<style>
    .lembur-container {
        display: flex;
        justify-content: center;
        padding: 2rem 1rem;
    }
    .lembur-wrapper {
        width: 100%;
        max-width: 48rem;
    }
    .card-main {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 0.875rem;
    }
    .icon-box {
        width: 2.5rem;
        height: 2.5rem;
        background: #eef2ff;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6366f1;
    }
    .card-header h2 {
        margin: 0;
        font-size: 1.125rem;
        color: #111827;
    }
    .card-header p {
        margin: 0.15rem 0 0;
        font-size: 0.8125rem;
        color: #6b7280;
    }
    .card-body { padding: 1.5rem; }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    @media (max-width: 640px) {
        .form-row { grid-template-columns: 1fr; }
    }
    .form-group { margin-bottom: 1.1rem; }
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    .form-control, .form-textarea {
        width: 100%;
        padding: 0.625rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        box-sizing: border-box;
    }
    .form-control:focus, .form-textarea:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .form-textarea { resize: vertical; min-height: 4rem; }
    .btn-submit {
        width: 100%;
        padding: 0.875rem 1rem;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    .btn-submit:hover:not(:disabled) { background: #4f46e5; }
    .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
    .rekap-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    .rekap-toolbar label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-right: 0.5rem;
    }
    .month-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }
    @media (max-width: 640px) {
        .month-row { grid-template-columns: repeat(3, 1fr); }
    }
    .month-chip {
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 999px;
        padding: 0.55rem 0.4rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        text-align: center;
    }
    .month-chip.active {
        background: #6366f1;
        border-color: #6366f1;
        color: #fff;
    }
    .month-chip .count {
        display: block;
        font-size: 0.7rem;
        font-weight: 500;
        opacity: 0.85;
        margin-top: 0.1rem;
    }
    .detail-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.75rem;
    }
    .detail-list { display: flex; flex-direction: column; gap: 0.65rem; }
    .detail-item {
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
        background: #fafafa;
    }
    .detail-top {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: flex-start;
    }
    .detail-date {
        font-weight: 600;
        color: #111827;
        font-size: 0.9rem;
    }
    .detail-day {
        color: #6b7280;
        font-size: 0.8rem;
        margin-top: 0.15rem;
    }
    .detail-time {
        font-weight: 600;
        color: #4f46e5;
        font-size: 0.875rem;
        white-space: nowrap;
    }
    .detail-meta {
        margin-top: 0.45rem;
        font-size: 0.8rem;
        color: #6b7280;
    }
    .detail-actions { margin-top: 0.55rem; }
    .btn-delete {
        background: transparent;
        border: 1px solid #fecaca;
        color: #dc2626;
        border-radius: 0.5rem;
        padding: 0.3rem 0.65rem;
        font-size: 0.75rem;
        cursor: pointer;
    }
    .empty-state {
        text-align: center;
        color: #9ca3af;
        font-size: 0.875rem;
        padding: 1.5rem 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="lembur-container">
    <div class="lembur-wrapper">
        <div class="card-main">
            <div class="card-header">
                <div class="icon-box"><i class="fas fa-business-time"></i></div>
                <div>
                    <h2>Input Lembur</h2>
                    <p>Isi tanggal serta jam mulai dan selesai</p>
                </div>
            </div>
            <div class="card-body">
                <form id="lemburForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="overtime_date">Tanggal</label>
                        <input type="date" id="overtime_date" name="overtime_date" class="form-control" required value="{{ now('Asia/Jakarta')->toDateString() }}">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="start_time">Dari jam</label>
                            <input type="time" id="start_time" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="end_time">Sampai jam</label>
                            <input type="time" id="end_time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="notes">Catatan <span style="color:#9ca3af;font-weight:400;">(opsional)</span></label>
                        <textarea id="notes" name="notes" class="form-textarea" maxlength="500" placeholder="Keterangan lembur"></textarea>
                    </div>
                    <button type="submit" class="btn-submit" id="lemburSubmitBtn">
                        <i class="fas fa-save"></i>
                        <span>Simpan Lembur</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="card-main">
            <div class="card-header">
                <div class="icon-box"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <h2>Rekap Lembur</h2>
                    <p>Pilih bulan untuk melihat detail</p>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('lembur.index') }}" class="rekap-toolbar" id="yearFilterForm">
                    <div>
                        <label for="year">Tahun</label>
                        <select name="year" id="year" class="form-control" style="width:auto;display:inline-block;" onchange="this.form.submit()">
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    </div>
                </form>

                <div class="month-row">
                    @foreach($monthNames as $m => $label)
                        <button type="button"
                                class="month-chip {{ (int)$selectedMonth === (int)$m ? 'active' : '' }}"
                                data-month="{{ $m }}">
                            {{ \Illuminate\Support\Str::substr($label, 0, 3) }}
                            <span class="count">{{ $monthCounts[$m] ?? 0 }}x</span>
                        </button>
                    @endforeach
                </div>

                <h3 class="detail-title">{{ $monthNames[$selectedMonth] ?? '' }} {{ $year }}</h3>
                <div class="detail-list">
                    @php $items = $byMonth[$selectedMonth] ?? collect(); @endphp
                    @forelse($items as $item)
                        <div class="detail-item" data-id="{{ $item->id }}">
                            <div class="detail-top">
                                <div>
                                    <div class="detail-date">{{ $item->overtime_date->locale('id')->isoFormat('D MMMM YYYY') }}</div>
                                    <div class="detail-day">{{ $item->overtime_date->locale('id')->isoFormat('dddd') }}</div>
                                </div>
                                <div class="detail-time">
                                    {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}
                                    –
                                    {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}
                                </div>
                            </div>
                            <div class="detail-meta">
                                Durasi: {{ $item->durationLabel() }}
                                @if($item->notes)
                                    · {{ $item->notes }}
                                @endif
                            </div>
                            <div class="detail-actions">
                                <button type="button" class="btn-delete" data-delete="{{ $item->id }}">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">Belum ada data lembur di bulan ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    document.querySelectorAll('.month-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var params = new URLSearchParams(window.location.search);
            params.set('year', document.getElementById('year').value);
            params.set('month', this.dataset.month);
            window.location.href = '{{ route('lembur.index') }}?' + params.toString();
        });
    });

    var form = document.getElementById('lemburForm');
    var submitBtn = document.getElementById('lemburSubmitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitBtn.disabled = true;

        var formData = new FormData(form);
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            formData.set('_token', meta.getAttribute('content'));
        }

        Swal.fire({
            title: 'Menyimpan...',
            allowOutsideClick: false,
            didOpen: function () { Swal.showLoading(); }
        });

        axios.post('{{ route('lembur.store') }}', formData, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (response) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: response.data.message,
                timer: 1800,
                showConfirmButton: false
            }).then(function () {
                var date = formData.get('overtime_date');
                var d = new Date(date + 'T00:00:00');
                var params = new URLSearchParams();
                params.set('year', d.getFullYear());
                params.set('month', d.getMonth() + 1);
                window.location.href = '{{ route('lembur.index') }}?' + params.toString();
            });
        })
        .catch(function (error) {
            var message = 'Gagal menyimpan data lembur.';
            if (error.response && error.response.data) {
                if (error.response.data.message) {
                    message = error.response.data.message;
                } else if (error.response.data.errors) {
                    message = Object.values(error.response.data.errors).flat().join(' ');
                }
            }
            Swal.fire({ icon: 'error', title: 'Gagal', text: message, confirmButtonColor: '#6366f1' });
        })
        .finally(function () {
            submitBtn.disabled = false;
        });
    });

    document.querySelectorAll('[data-delete]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-delete');
            Swal.fire({
                title: 'Hapus data lembur?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                axios.delete('/lembur/' + id, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: response.data.message,
                        timer: 1400,
                        showConfirmButton: false
                    }).then(function () { window.location.reload(); });
                })
                .catch(function () {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat menghapus data.', confirmButtonColor: '#6366f1' });
                });
            });
        });
    });
})();
</script>
@endsection
