@extends('admin.layouts.app')

@section('title', 'History Perizinan')

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
    }
    
    tbody tr:hover {
        background: #f9fafb;
    }
    
    .badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    
    .badge-cuti {
        background: #fef3c7;
        color: #92400e;
    }
    
    .badge-izin {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .badge-sakit {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .pagination-wrapper {
        padding: 16px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: center;
    }

    .pagination {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .pagination-link {
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #374151;
        background: #ffffff;
        font-size: 13px;
        font-weight: 600;
    }

    .pagination-link:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .pagination-link.active {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
    }

    .pagination-link.disabled {
        color: #9ca3af;
        background: #f9fafb;
        border-color: #e5e7eb;
        pointer-events: none;
    }
    
    .empty-state {
        padding: 48px;
        text-align: center;
        color: #6b7280;
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
    
    .btn-action {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .btn-edit {
        background: #eef2ff;
        color: #667eea;
    }
    
    .btn-edit:hover {
        background: #e0e7ff;
        transform: translateY(-1px);
    }
    
    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
    }
    
    .btn-delete:hover {
        background: #fee2e2;
        transform: translateY(-1px);
    }
    
    .edit-modal {
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
    
    .edit-modal.active {
        display: flex;
    }
    
    .edit-modal-content {
        background: white;
        border-radius: 12px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .edit-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .edit-modal-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
    }
    
    .edit-modal-close {
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
    
    .edit-modal-close:hover {
        background: #f3f4f6;
        color: #1f2937;
    }
    
    .edit-modal-body {
        padding: 24px;
    }
    
    .edit-modal-footer {
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    
    .btn-save {
        padding: 10px 20px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-save:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    
    .btn-cancel {
        padding: 10px 20px;
        background: #f3f4f6;
        color: #374151;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-cancel:hover {
        background: #e5e7eb;
    }
    
    .image-modal {
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
    
    .image-modal-content {
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
    
    .image-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .image-modal-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
    }
    
    .image-modal-close {
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
    
    .image-modal-close:hover {
        background: #f3f4f6;
        color: #1f2937;
    }
    
    .image-modal-body {
        padding: 24px;
        overflow-y: auto;
        text-align: center;
    }
    
    .image-modal-body img {
        max-width: 100%;
        max-height: 70vh;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
    <h1 class="page-title">History Perizinan</h1>
    <p class="page-subtitle">Riwayat perizinan semua karyawan</p>
</div>

<!-- Filter Card -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.leave-history.index') }}" class="filter-form">
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
            <label for="leave_type" class="form-label">Jenis Izin</label>
            <select id="leave_type" name="leave_type" class="form-select">
                <option value="all" {{ request('leave_type') == 'all' || !request('leave_type') ? 'selected' : '' }}>Semua</option>
                <option value="cuti" {{ request('leave_type') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                <option value="izin" {{ request('leave_type') == 'izin' ? 'selected' : '' }}>Izin</option>
                <option value="sakit" {{ request('leave_type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="search" class="form-label">Cari (Nama/NIK)</label>
            <input type="text" 
                   id="search" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Cari nama atau NIK"
                   class="form-input">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn-filter">
                <i class="fas fa-search"></i>
                <span>Filter</span>
            </button>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Jenis Izin</th>
                    <th>Keterangan</th>
                    <th>Lampiran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($leave->leave_date)->locale('id')->isoFormat('D MMM YYYY') }}</td>
                    <td style="font-weight: 600;">{{ $leave->user->nik }}</td>
                    <td>{{ $leave->user->name }}</td>
                    <td>
                        <span class="badge badge-{{ $leave->leave_type }}">
                            {{ ucfirst($leave->leave_type) }}
                        </span>
                    </td>
                    <td>{{ $leave->notes ?: '-' }}</td>
                    <td>
                        @if($leave->attachment)
                            <button type="button" 
                                    class="btn-view-image" 
                                    onclick="viewImage('{{ $leave->attachment }}', '{{ $leave->user->name }}')">
                                <i class="fas fa-image"></i> Foto
                            </button>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <button type="button" 
                                    class="btn-action btn-edit" 
                                    onclick="openEditModal({{ $leave->id }}, '{{ $leave->leave_date }}', '{{ $leave->leave_type }}')">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </button>
                            <form action="{{ route('admin.leave-history.destroy', $leave) }}" 
                                  method="POST" 
                                  style="display: inline;" 
                                  onsubmit="return confirm('Yakin ingin menghapus perizinan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete">
                                    <i class="fas fa-trash"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        Tidak ada data perizinan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($leaves->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination">
            @if($leaves->onFirstPage())
                <span class="pagination-link disabled">
                    <i class="fas fa-chevron-left"></i>
                </span>
            @else
                <a class="pagination-link" href="{{ $leaves->previousPageUrl() }}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            @php
                $start = max(1, $leaves->currentPage() - 2);
                $end = min($leaves->lastPage(), $leaves->currentPage() + 2);
            @endphp

            @for($page = $start; $page <= $end; $page++)
                <a href="{{ $leaves->url($page) }}" class="pagination-link {{ $page === $leaves->currentPage() ? 'active' : '' }}">
                    {{ $page }}
                </a>
            @endfor

            @if($leaves->hasMorePages())
                <a class="pagination-link" href="{{ $leaves->nextPageUrl() }}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="pagination-link disabled">
                    <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Modal View Image -->
<div id="imageModal" class="image-modal">
    <div class="image-modal-content">
        <div class="image-modal-header">
            <h3>Lampiran Perizinan</h3>
            <button type="button" class="image-modal-close" onclick="closeImageModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="image-modal-body">
            <img id="imageModalImage" src="" alt="Lampiran Perizinan" style="max-width: 100%; max-height: 70vh; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="margin-top: 16px; padding: 16px; background: #f9fafb; border-radius: 8px;">
                <p style="margin: 0; font-size: 14px; color: #374151;"><strong>Nama:</strong> <span id="imageEmployeeName"></span></p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Leave -->
<div id="editModal" class="edit-modal">
    <div class="edit-modal-content">
        <div class="edit-modal-header">
            <h3>Edit Perizinan</h3>
            <button type="button" class="edit-modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editLeaveForm">
            <div class="edit-modal-body">
                <input type="hidden" id="editLeaveId" name="leave_id">
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="editLeaveDate" class="form-label">Tanggal</label>
                    <input type="date" 
                           id="editLeaveDate" 
                           name="leave_date" 
                           class="form-input" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="editLeaveType" class="form-label">Jenis Izin</label>
                    <select id="editLeaveType" 
                            name="leave_type" 
                            class="form-select" 
                            required>
                        <option value="">Pilih jenis izin</option>
                        <option value="cuti">Cuti</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                    </select>
                </div>
            </div>
            <div class="edit-modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">
                    Batal
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
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
        const imageModal = document.getElementById('imageModal');
        const editModal = document.getElementById('editModal');
        if (event.target == imageModal) {
            closeImageModal();
        }
        if (event.target == editModal) {
            closeEditModal();
        }
    }
    
    function openEditModal(leaveId, leaveDate, leaveType) {
        document.getElementById('editLeaveId').value = leaveId;
        document.getElementById('editLeaveDate').value = leaveDate;
        document.getElementById('editLeaveType').value = leaveType;
        document.getElementById('editModal').classList.add('active');
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
        document.getElementById('editLeaveForm').reset();
    }
    
    document.getElementById('editLeaveForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const leaveId = document.getElementById('editLeaveId').value;
        const formData = {
            leave_date: document.getElementById('editLeaveDate').value,
            leave_type: document.getElementById('editLeaveType').value,
            _token: '{{ csrf_token() }}',
            _method: 'PUT'
        };
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Menyimpan...</span>';
        
        axios.put(`/admin/leave-history/${leaveId}`, formData)
            .then(response => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.data.message,
                    timer: 2000,
                    showConfirmButton: false,
                    confirmButtonColor: '#6366f1'
                }).then(() => {
                    location.reload();
                });
            })
            .catch(error => {
                let message = 'Terjadi kesalahan!';
                if (error.response && error.response.data && error.response.data.message) {
                    message = error.response.data.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: message,
                    confirmButtonColor: '#6366f1'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            });
    });
</script>
@endsection
