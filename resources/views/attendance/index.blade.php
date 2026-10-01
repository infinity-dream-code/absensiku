@extends('layouts.app')

@section('title', 'Absensi - Sistem Absensi')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #locationConfirmMap {
        height: 300px;
        width: 100%;
        border-radius: 8px;
        margin: 15px 0;
        z-index: 1;
    }
    .swal2-popup {
        max-width: 90% !important;
        width: 500px !important;
    }
    @media (max-width: 640px) {
        .swal2-popup {
            width: 95% !important;
        }
        #locationConfirmMap {
            height: 250px;
        }
    }
    .attendance-container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 80vh;
        padding: 2rem 1rem;
    }
    /* Ensure inputs/textarea don't overflow the card */
    .form-control,
    .form-textarea {
        box-sizing: border-box;
        max-width: 100%;
    }
    .attendance-wrapper {
        width: 100%;
        max-width: 42rem;
    }
    .summary-card {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-top: 1rem;
    }
    .summary-header {
        padding: 1.25rem 1.25rem 1rem;
        background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 55%, #ffffff 100%);
        border-bottom: 1px solid #eef2ff;
        display: flex;
        flex-direction: column;
        gap: 0.875rem;
    }
    .summary-card.collapsed .summary-header {
        border-bottom: 0;
        padding-bottom: 1.25rem;
    }
    .summary-card.collapsed .summary-collapsible {
        display: none;
    }
    .summary-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .summary-title-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }
    .summary-toggle {
        border: 0;
        background: #ede9fe;
        color: #4f46e5;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .summary-card.collapsed .summary-toggle i {
        transform: rotate(-90deg);
    }
    .summary-filters {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    .summary-filters select {
        width: 100%;
        padding: 0.6rem 0.75rem;
        border: 1px solid #c7d2fe;
        border-radius: 0.65rem;
        font-size: 0.875rem;
        background: #fff;
        color: #111827;
    }
    .summary-filters select:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }
    .summary-period {
        font-size: 0.8125rem;
        color: #6b7280;
        margin: 0.2rem 0 0;
    }
    .summary-body {
        padding: 1rem 1.25rem 1.25rem;
    }
    .summary-leave-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 1rem;
        border-radius: 0.85rem;
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
        border: 1px solid #bbf7d0;
        margin-bottom: 0.9rem;
    }
    .summary-leave-box .leave-meta {
        flex: 1;
        min-width: 0;
    }
    .summary-leave-box .leave-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: #047857;
        margin: 0 0 0.2rem;
    }
    .summary-leave-box .leave-sub {
        font-size: 0.72rem;
        color: #059669;
        margin: 0;
    }
    .summary-leave-box .leave-value {
        text-align: right;
        flex-shrink: 0;
    }
    .summary-leave-box .leave-value strong {
        display: block;
        font-size: 1.65rem;
        line-height: 1;
        color: #065f46;
        font-weight: 700;
    }
    .summary-leave-box .leave-value span {
        font-size: 0.7rem;
        color: #059669;
    }
    .summary-leave-bar {
        height: 6px;
        border-radius: 999px;
        background: #d1fae5;
        overflow: hidden;
        margin-top: 0.55rem;
    }
    .summary-leave-bar > span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #10b981, #34d399);
    }
    .summary-section-label {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #9ca3af;
        margin: 0 0 0.55rem;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.65rem;
    }
    .summary-stat {
        border: 1px solid #e5e7eb;
        border-radius: 0.85rem;
        padding: 0.85rem 0.9rem;
        background: #fff;
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .summary-stat:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .summary-stat .stat-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    .summary-stat .stat-text {
        min-width: 0;
    }
    .summary-stat .label {
        font-size: 0.72rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 0.15rem;
    }
    .summary-stat .value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.15;
    }
    .summary-stat.green .stat-icon { background: #d1fae5; color: #059669; }
    .summary-stat.red .stat-icon { background: #fee2e2; color: #dc2626; }
    .summary-stat.amber .stat-icon { background: #fef3c7; color: #d97706; }
    .summary-stat.blue .stat-icon { background: #dbeafe; color: #2563eb; }
    .summary-stat.violet .stat-icon { background: #ede9fe; color: #7c3aed; }
    .summary-stat.slate .stat-icon { background: #e2e8f0; color: #475569; }
    .summary-stat.green { border-color: #a7f3d0; background: #f0fdf4; }
    .summary-stat.red { border-color: #fecaca; background: #fef2f2; }
    .summary-stat.amber { border-color: #fde68a; background: #fffbeb; }
    .summary-stat.blue { border-color: #bfdbfe; background: #eff6ff; }
    .summary-stat.violet { border-color: #ddd6fe; background: #f5f3ff; }
    .summary-stat.slate { border-color: #e2e8f0; background: #f8fafc; }
    .summary-detail {
        margin-top: 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.85rem;
        overflow: hidden;
        background: #fff;
    }
    .summary-detail + .summary-detail {
        margin-top: 0.55rem;
    }
    .summary-detail-toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.8rem 0.95rem;
        border: none;
        background: #f8fafc;
        cursor: pointer;
        text-align: left;
        color: #111827;
        font: inherit;
    }
    .summary-detail-toggle:hover {
        background: #f1f5f9;
    }
    .summary-detail-toggle .toggle-left {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }
    .summary-detail-toggle .toggle-icon {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        flex-shrink: 0;
    }
    .summary-detail-toggle .toggle-icon.late {
        background: #fee2e2;
        color: #dc2626;
    }
    .summary-detail-toggle .toggle-icon.leave {
        background: #dbeafe;
        color: #2563eb;
    }
    .summary-detail-toggle .toggle-title {
        font-size: 0.84rem;
        font-weight: 600;
        margin: 0;
    }
    .summary-detail-toggle .toggle-sub {
        font-size: 0.7rem;
        color: #6b7280;
        margin: 0.1rem 0 0;
    }
    .summary-detail-toggle .toggle-chevron {
        color: #9ca3af;
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }
    .summary-detail.open .toggle-chevron {
        transform: rotate(180deg);
    }
    .summary-detail-panel {
        display: none;
        border-top: 1px solid #e5e7eb;
        padding: 0.65rem;
        background: #fff;
    }
    .summary-detail.open .summary-detail-panel {
        display: block;
    }
    .summary-detail-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.65rem 0.7rem;
        border-radius: 0.65rem;
        background: #f9fafb;
    }
    .summary-detail-item + .summary-detail-item {
        margin-top: 0.45rem;
    }
    .summary-detail-item .item-main {
        min-width: 0;
    }
    .summary-detail-item .item-date {
        font-size: 0.82rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }
    .summary-detail-item .item-meta {
        font-size: 0.72rem;
        color: #6b7280;
        margin: 0.15rem 0 0;
    }
    .summary-detail-item .item-badge {
        flex-shrink: 0;
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.2rem 0.45rem;
        border-radius: 999px;
        line-height: 1.2;
    }
    .summary-detail-item .item-badge.late {
        background: #fee2e2;
        color: #b91c1c;
    }
    .summary-detail-item .item-badge.sakit {
        background: #fef3c7;
        color: #b45309;
    }
    .summary-detail-item .item-badge.izin {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .summary-detail-item .item-badge.cuti {
        background: #ede9fe;
        color: #6d28d9;
    }
    .summary-detail-empty {
        font-size: 0.78rem;
        color: #9ca3af;
        text-align: center;
        padding: 0.75rem 0.5rem;
        margin: 0;
    }
    .kpi-view-card {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-top: 1rem;
    }
    .kpi-view-header {
        padding: 1.25rem 1.25rem 1rem;
        background: linear-gradient(135deg, #f5f3ff 0%, #f8fafc 55%, #ffffff 100%);
        border-bottom: 1px solid #ede9fe;
        display: flex;
        flex-direction: column;
        gap: 0.875rem;
    }
    .kpi-view-body {
        padding: 1rem 1.25rem 1.25rem;
    }
    .kpi-score-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border-radius: 0.85rem;
        background: linear-gradient(135deg, #f5f3ff 0%, #eef2ff 100%);
        border: 1px solid #ddd6fe;
        margin-bottom: 0.9rem;
    }
    .kpi-score-row .kpi-meta {
        min-width: 0;
    }
    .kpi-score-row .kpi-cat {
        font-size: 0.78rem;
        color: #6d28d9;
        margin: 0 0 0.15rem;
        font-weight: 600;
    }
    .kpi-score-row .kpi-penilai {
        font-size: 0.72rem;
        color: #6b7280;
        margin: 0;
    }
    .kpi-score-row .kpi-score strong {
        display: block;
        font-size: 1.65rem;
        line-height: 1;
        color: #5b21b6;
        text-align: right;
    }
    .kpi-score-row .kpi-score span {
        font-size: 0.7rem;
        color: #7c3aed;
    }
    .kpi-table-mini {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8rem;
    }
    .kpi-table-mini th,
    .kpi-table-mini td {
        border: 1px solid #e5e7eb;
        padding: 0.55rem 0.6rem;
        text-align: left;
    }
    .kpi-table-mini th {
        background: #f5f3ff;
        color: #312e81;
        font-size: 0.72rem;
    }
    .kpi-table-mini td.num {
        text-align: right;
        font-weight: 600;
        white-space: nowrap;
    }
    .kpi-feedback {
        margin-top: 0.9rem;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .kpi-feedback .label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #9ca3af;
        margin: 0 0 0.4rem;
    }
    .kpi-feedback p {
        margin: 0;
        font-size: 0.84rem;
        color: #374151;
        white-space: pre-wrap;
    }
    .kpi-empty {
        font-size: 0.84rem;
        color: #6b7280;
        text-align: center;
        padding: 1.25rem 0.5rem;
        margin: 0;
    }
    @media (min-width: 480px) {
        .summary-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    .card-main {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .header-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .icon-box {
        width: 2.5rem;
        height: 2.5rem;
        background: #eef2ff;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card-body {
        padding: 1.5rem;
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    .form-control {
        width: 100%;
        padding: 0.625rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .form-textarea {
        width: 100%;
        padding: 0.625rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        resize: none;
        transition: all 0.2s;
    }
    .form-textarea:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .btn-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }
    .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.875rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }
    .btn-success {
        background: #10b981;
        color: white;
    }
    .btn-success:hover:not(:disabled) {
        background: #059669;
    }
    .btn-danger {
        background: #f43f5e;
        color: white;
    }
    .btn-danger:hover:not(:disabled) {
        background: #e11d48;
    }
    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .status-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        margin-top: 1.25rem;
    }
    .status-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
    }
    .status-item {
        background: #f9fafb;
        border-radius: 0.75rem;
        padding: 1rem;
    }
    .preview-image {
        max-width: 100%;
        height: auto;
        max-height: 12rem;
        border-radius: 0.5rem;
        margin-top: 0.75rem;
    }
    .text-small {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.375rem;
    }
    
    /* SweetAlert2 Custom Styling */
    .swal2-popup {
        border-radius: 1rem !important;
    }
    
    .swal2-confirm,
    .swal2-confirm-custom {
        background-color: #6366f1 !important;
        border: none !important;
        border-radius: 0.5rem !important;
        padding: 0.75rem 2rem !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important;
        transition: all 0.2s !important;
        opacity: 1 !important;
        visibility: visible !important;
        display: inline-block !important;
        cursor: pointer !important;
    }
    
    .swal2-confirm:hover,
    .swal2-confirm-custom:hover {
        background-color: #4f46e5 !important;
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4) !important;
        transform: translateY(-1px) !important;
    }
    
    .swal2-confirm:focus,
    .swal2-confirm-custom:focus {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3) !important;
        outline: none !important;
    }
    
    .swal2-cancel,
    .swal2-cancel-custom {
        background-color: #ef4444 !important;
        border: none !important;
        border-radius: 0.5rem !important;
        padding: 0.75rem 2rem !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
        transition: all 0.2s !important;
        opacity: 1 !important;
        visibility: visible !important;
        display: inline-block !important;
        cursor: pointer !important;
    }
    
    .swal2-cancel:hover,
    .swal2-cancel-custom:hover {
        background-color: #dc2626 !important;
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4) !important;
        transform: translateY(-1px) !important;
    }
    
    .swal2-cancel:focus,
    .swal2-cancel-custom:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.3) !important;
        outline: none !important;
    }
    
    .swal2-actions {
        gap: 0.75rem !important;
        margin-top: 1.5rem !important;
    }
    
    .swal2-title {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        color: #111827 !important;
        margin-bottom: 0.5rem !important;
    }
    
    .swal2-html-container {
        font-size: 0.875rem !important;
        color: #6b7280 !important;
        margin-top: 0.5rem !important;
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }
    
    .swal2-loading-popup {
        text-align: center;
    }
    
    .swal2-loading-popup .swal2-loader {
        border: 4px solid #f3f4f6;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
        display: block;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .swal2-loading-popup .swal2-html-container {
        padding: 0 !important;
        text-align: center;
    }
    
    .swal2-loading-html {
        text-align: center !important;
    }
    
    /* Image Source Selection Modal Styles */
    .image-source-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: flex-end;
        justify-content: center;
    }
    
    .image-source-modal.active {
        display: flex;
    }
    
    .image-source-container {
        background: white;
        border-radius: 1.5rem 1.5rem 0 0;
        width: 100%;
        max-width: 500px;
        padding: 1.5rem;
        padding-bottom: 2rem;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
        animation: slideUp 0.3s ease-out;
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        }
    }
    
    .image-source-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 1.5rem 0;
        text-align: center;
    }
    
    .image-source-options {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .image-source-option {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
        width: 100%;
    }
    
    .image-source-option:hover {
        background: #f9fafb;
        border-color: #6366f1;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
    }
    
    .image-source-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .image-source-icon.camera {
        background: #dbeafe;
        color: #2563eb;
    }
    
    .image-source-icon.file {
        background: #fef3c7;
        color: #d97706;
    }
    
    .image-source-text {
        flex: 1;
        font-size: 1rem;
        font-weight: 500;
        color: #111827;
    }
    
    .image-source-cancel {
        margin-top: 0.5rem;
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: #f9fafb;
        cursor: pointer;
        text-align: center;
        font-weight: 500;
        color: #6b7280;
        transition: all 0.2s;
    }
    
    .image-source-cancel:hover {
        background: #f3f4f6;
        color: #374151;
    }
    
    /* Camera Modal Styles */
    .camera-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        align-items: center;
        justify-content: center;
    }
    
    .camera-modal.active {
        display: flex;
    }
    
    .camera-container {
        position: relative;
        width: 90%;
        max-width: 600px;
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .camera-preview {
        width: 100%;
        border-radius: 0.5rem;
        overflow: hidden;
        background: #000;
        position: relative;
    }
    
    .camera-preview video,
    .camera-preview canvas {
        width: 100%;
        height: auto;
        display: block;
    }
    
    .camera-controls {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    
    .camera-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }
    
    .camera-btn-capture {
        background: #10b981;
        color: white;
    }
    
    .camera-btn-capture:hover {
        background: #059669;
    }
    
    .camera-btn-cancel {
        background: #ef4444;
        color: white;
    }
    
    .camera-btn-cancel:hover {
        background: #dc2626;
    }
    
    .camera-btn-retake {
        background: #6b7280;
        color: white;
    }
    
    .camera-btn-retake:hover {
        background: #4b5563;
    }
    
    .camera-btn-use {
        background: #6366f1;
        color: white;
    }
    
    .camera-btn-use:hover {
        background: #4f46e5;
    }
    
    @media (max-width: 640px) {
        .camera-container {
            width: 95%;
            padding: 1rem;
        }
        
        .camera-controls {
            flex-wrap: wrap;
        }
        
        .camera-btn {
            flex: 1;
            min-width: 120px;
        }
    }
</style>
@endsection

@section('content')
<div class="attendance-container">
    <div class="attendance-wrapper">
        <div class="card-main">
            <div class="card-header">
                <div class="header-content">
                    <div class="icon-box">
                        <i class="fas fa-calendar-day" style="color: #6366f1;"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1.25rem; font-weight: 600; color: #111827; margin: 0;">Absensi Hari Ini</h3>
                        <p style="font-size: 0.875rem; color: #6b7280; margin: 0.25rem 0 0 0;">
                            {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <form id="attendanceForm">
                    <div class="form-group">
                        <label for="work_type" class="form-label">Jenis Kerja</label>
                        <select class="form-control" id="work_type" name="work_type" required>
                            <option value="">Pilih jenis kerja</option>
                            <option value="WFA" {{ old('work_type', $attendance->work_type ?? '') == 'WFA' ? 'selected' : '' }}>WFA (Work From Anywhere)</option>
                            <option value="WFO" {{ old('work_type', $attendance->work_type ?? '') == 'WFO' ? 'selected' : '' }}>WFO (Work From Office)</option>
                            <option value="WFH" {{ old('work_type', $attendance->work_type ?? '') == 'WFH' ? 'selected' : '' }}>WFH (Work From Home)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-textarea" id="notes" name="notes" rows="3" placeholder="Tambahkan catatan (opsional)">{{ old('notes', $attendance->notes ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="image" class="form-label">Gambar (Opsional)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" style="cursor: pointer; display: none;">
                        <button type="button" id="imageSelectBtn" class="btn" style="background: #6366f1; color: white; padding: 0.625rem 1rem; border: none; border-radius: 0.5rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 500; width: 100%; justify-content: center;">
                            <i class="fas fa-image"></i>
                            <span>Pilih Gambar</span>
                        </button>
                        <p class="text-small">Format: JPG, PNG, GIF (Max: 5MB)</p>
                        <div id="imagePreview"></div>
                    </div>

                    <div class="form-group" id="locationGroup" style="display: none;">
                        <label class="form-label" style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                            <span>
                                <i class="fas fa-map-marker-alt" style="margin-right: 8px; color: #6366f1;"></i>Lokasi Saat Ini
                            </span>
                            <button type="button" id="refreshLocationBtn" style="padding: 6px 10px; background: #6366f1; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                <i class="fas fa-sync-alt"></i>
                                <span>Refresh</span>
                            </button>
                        </label>
                        <div id="locationMap" style="height: 220px; width: 100%; border-radius: 0.5rem; margin-bottom: 0.75rem; border: 1px solid #e5e7eb;"></div>
                        <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.75rem;">
                            <div id="locationInfo" style="font-size: 0.875rem; color: #374151;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <i class="fas fa-spinner fa-spin" style="color: #6366f1;"></i>
                                    <span>Mengambil lokasi...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn-grid">
                        <button type="button" id="checkInBtn" class="btn btn-success" {{ isset($attendance) && $attendance->check_out ? 'disabled' : '' }}>
                            <i class="fas fa-sign-in-alt"></i>
                            <span id="checkInBtnText">
                                @if(isset($attendance) && $attendance->check_out)
                                    Sudah Check-Out
                                @elseif(isset($attendance) && $attendance->check_in)
                                    Check-In Lagi
                                @else
                                    Check-In
                                @endif
                            </span>
                        </button>
                        
                        <button type="button" id="checkOutBtn" class="btn btn-danger" {{ isset($attendance) && $attendance->check_out ? 'disabled' : '' }}>
                            <i class="fas fa-sign-out-alt"></i>
                            <span>{{ isset($attendance) && $attendance->check_out ? 'Sudah Check-Out' : 'Check-Out' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(isset($attendance))
        <div class="status-card">
            <h6 style="font-size: 1rem; font-weight: 600; color: #111827; margin: 0 0 1rem 0;">Status Absensi</h6>
            <div class="status-grid">
                <div class="status-item">
                    <p style="font-size: 0.75rem; font-weight: 500; color: #6b7280; margin: 0 0 0.25rem 0;">Check-In</p>
                    <p style="font-size: 1.25rem; font-weight: 600; margin: 0;">
                        @if($attendance->check_in)
                            <span style="color: #10b981;">{{ \Carbon\Carbon::parse($attendance->check_in)->setTimezone('Asia/Jakarta')->format('H:i:s') }}</span>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </p>
                </div>
                <div class="status-item">
                    <p style="font-size: 0.75rem; font-weight: 500; color: #6b7280; margin: 0 0 0.25rem 0;">Check-Out</p>
                    <p style="font-size: 1.25rem; font-weight: 600; margin: 0;">
                        @if($attendance->check_out)
                            <span style="color: #10b981;">{{ \Carbon\Carbon::parse($attendance->check_out)->setTimezone('Asia/Jakarta')->format('H:i:s') }}</span>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </p>
                </div>
            </div>
            @if($attendance->logs && $attendance->logs->count() > 0)
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <p style="font-size: 0.75rem; font-weight: 500; color: #6b7280; margin: 0 0 0.5rem 0;">Riwayat Check-In</p>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    @foreach($attendance->logs->sortBy('check_in_time') as $log)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background: #f9fafb; border-radius: 0.375rem;">
                        <div>
                            <span style="font-size: 0.875rem; font-weight: 600; color: #374151;">{{ $log->status }}</span>
                            <span style="font-size: 0.75rem; color: #6b7280; margin-left: 0.5rem;">{{ \Carbon\Carbon::parse($log->check_in_time)->setTimezone('Asia/Jakarta')->format('H:i:s') }}</span>
                        </div>
                        @if($log->notes)
                        <span style="font-size: 0.75rem; color: #6b7280;" title="{{ $log->notes }}">
                            <i class="fas fa-sticky-note"></i>
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        <div class="summary-card" id="summaryCard">
            <div class="summary-header">
                <div class="summary-title-row">
                    <div class="summary-title-left">
                        <div class="icon-box">
                            <i class="fas fa-chart-pie" style="color: #6366f1;"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0;">Ringkasan Absensi</h3>
                            <p class="summary-period">Periode: {{ $summary['period_label'] }}</p>
                        </div>
                    </div>
                    <button type="button" class="summary-toggle" id="summaryToggle" aria-label="Tampilkan/sembunyikan ringkasan">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="summary-collapsible">
                <div class="summary-header" style="padding-top:0; background:transparent; border-bottom:0;">
                <form method="GET" action="/attendance" class="summary-filters" id="summaryFilterForm">
                    <input type="hidden" name="kpi_year" value="{{ $kpiView['year'] }}">
                    <input type="hidden" name="kpi_month" value="{{ $kpiView['month'] }}">
                    <div>
                        <label for="summary_year" class="form-label">Tahun</label>
                        <select name="year" id="summary_year" onchange="this.form.submit()">
                            @foreach($summary['years'] as $yearOption)
                                <option value="{{ $yearOption }}" {{ (int) $summary['year'] === (int) $yearOption ? 'selected' : '' }}>
                                    {{ $yearOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="summary_month" class="form-label">Bulan</label>
                        <select name="month" id="summary_month" onchange="this.form.submit()">
                            <option value="all" {{ $summary['all_months'] ? 'selected' : '' }}>Semua bulan</option>
                            @foreach($summary['months'] as $monthNum => $monthName)
                                <option value="{{ $monthNum }}" {{ !$summary['all_months'] && (int) $summary['month'] === (int) $monthNum ? 'selected' : '' }}>
                                    {{ $monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
                </div>
            <div class="summary-body">
                @php
                    $cutiPercent = $summary['cuti_max'] > 0
                        ? min(100, round(($summary['cuti_used_year'] / $summary['cuti_max']) * 100))
                        : 0;
                @endphp
                <div class="summary-leave-box">
                    <div class="leave-meta">
                        <p class="leave-title"><i class="fas fa-umbrella-beach" style="margin-right:6px;"></i>Sisa Cuti {{ $summary['year'] }}</p>
                        <p class="leave-sub">Terpakai {{ $summary['cuti_used_year'] }} dari {{ $summary['cuti_max'] }} hari</p>
                        <div class="summary-leave-bar"><span style="width: {{ $cutiPercent }}%;"></span></div>
                    </div>
                    <div class="leave-value">
                        <strong>{{ $summary['sisa_cuti'] }}</strong>
                        <span>hari tersisa</span>
                    </div>
                </div>

                <p class="summary-section-label">Kehadiran</p>
                <div class="summary-grid" style="margin-bottom: 0.9rem;">
                    <div class="summary-stat green">
                        <div class="stat-icon"><i class="fas fa-check"></i></div>
                        <div class="stat-text">
                            <div class="label">Tepat Waktu</div>
                            <div class="value">{{ $summary['tepat_waktu'] }}</div>
                        </div>
                    </div>
                    <div class="summary-stat red">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-text">
                            <div class="label">Terlambat</div>
                            <div class="value">{{ $summary['terlambat'] }}</div>
                        </div>
                    </div>
                    <div class="summary-stat slate">
                        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="stat-text">
                            <div class="label">Total Hadir</div>
                            <div class="value">{{ $summary['total_hadir'] }}</div>
                        </div>
                    </div>
                </div>

                <p class="summary-section-label">Perizinan</p>
                <div class="summary-grid">
                    <div class="summary-stat amber">
                        <div class="stat-icon"><i class="fas fa-notes-medical"></i></div>
                        <div class="stat-text">
                            <div class="label">Sakit</div>
                            <div class="value">{{ $summary['sakit'] }}</div>
                        </div>
                    </div>
                    <div class="summary-stat blue">
                        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="stat-text">
                            <div class="label">Izin</div>
                            <div class="value">{{ $summary['izin'] }}</div>
                        </div>
                    </div>
                    <div class="summary-stat violet">
                        <div class="stat-icon"><i class="fas fa-plane-departure"></i></div>
                        <div class="stat-text">
                            <div class="label">Cuti</div>
                            <div class="value">{{ $summary['cuti'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="summary-detail" data-summary-detail>
                    <button type="button" class="summary-detail-toggle" data-summary-toggle aria-expanded="false">
                        <span class="toggle-left">
                            <span class="toggle-icon late"><i class="fas fa-clock"></i></span>
                            <span>
                                <p class="toggle-title">Detail Terlambat</p>
                                <p class="toggle-sub">{{ min(10, count($summary['latest_late'])) }} data terakhir dari {{ $summary['terlambat'] }}</p>
                            </span>
                        </span>
                        <i class="fas fa-chevron-down toggle-chevron"></i>
                    </button>
                    <div class="summary-detail-panel">
                        @forelse($summary['latest_late'] as $late)
                            <div class="summary-detail-item">
                                <div class="item-main">
                                    <p class="item-date">{{ $late['date'] }}</p>
                                    <p class="item-meta">Check-in {{ $late['time'] }} · {{ $late['work_type'] }}</p>
                                </div>
                                <span class="item-badge late">Terlambat</span>
                            </div>
                        @empty
                            <p class="summary-detail-empty">Belum ada data terlambat.</p>
                        @endforelse
                    </div>
                </div>

                <div class="summary-detail" data-summary-detail>
                    <button type="button" class="summary-detail-toggle" data-summary-toggle aria-expanded="false">
                        <span class="toggle-left">
                            <span class="toggle-icon leave"><i class="fas fa-calendar-times"></i></span>
                            <span>
                                <p class="toggle-title">Detail Perizinan</p>
                                <p class="toggle-sub">{{ min(10, count($summary['latest_leaves'])) }} data terakhir dari {{ $summary['total_izin'] }}</p>
                            </span>
                        </span>
                        <i class="fas fa-chevron-down toggle-chevron"></i>
                    </button>
                    <div class="summary-detail-panel">
                        @forelse($summary['latest_leaves'] as $leave)
                            <div class="summary-detail-item">
                                <div class="item-main">
                                    <p class="item-date">{{ $leave['date'] }}</p>
                                    <p class="item-meta">{{ $leave['notes'] ?: 'Tanpa catatan' }}</p>
                                </div>
                                <span class="item-badge {{ $leave['type_key'] }}">{{ $leave['type'] }}</span>
                            </div>
                        @empty
                            <p class="summary-detail-empty">Belum ada data perizinan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            </div>
        </div>

        <div class="kpi-view-card">
            <div class="kpi-view-header">
                <div class="summary-title-row">
                    <div class="summary-title-left">
                        <div class="icon-box">
                            <i class="fas fa-chart-line" style="color: #7c3aed;"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0;">Hasil KPI</h3>
                            <p class="summary-period">Periode: {{ $kpiView['period_label'] }}</p>
                        </div>
                    </div>
                </div>
                <form method="GET" action="/attendance" class="summary-filters" id="kpiFilterForm">
                    <input type="hidden" name="year" value="{{ $summary['year'] }}">
                    <input type="hidden" name="month" value="{{ $summary['all_months'] ? 'all' : $summary['month'] }}">
                    <div>
                        <label for="kpi_year" class="form-label">Tahun</label>
                        <select name="kpi_year" id="kpi_year" onchange="this.form.submit()">
                            @foreach($kpiView['years'] as $yearOption)
                                <option value="{{ $yearOption }}" {{ (int) $kpiView['year'] === (int) $yearOption ? 'selected' : '' }}>
                                    {{ $yearOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="kpi_month" class="form-label">Bulan</label>
                        <select name="kpi_month" id="kpi_month" onchange="this.form.submit()">
                            @foreach($kpiView['months'] as $monthNum => $monthName)
                                <option value="{{ $monthNum }}" {{ (int) $kpiView['month'] === (int) $monthNum ? 'selected' : '' }}>
                                    {{ $monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="kpi-view-body">
                @if($kpiView['exists'])
                    <div class="kpi-score-row">
                        <div class="kpi-meta">
                            <p class="kpi-cat">{{ $kpiView['kategori'] ?: 'Penilaian KPI' }}</p>
                            <p class="kpi-penilai">
                                Dinilai oleh {{ $kpiView['penilai'] ?: '-' }}
                                @if($kpiView['role']) · {{ $kpiView['role'] }} @endif
                            </p>
                        </div>
                        <div class="kpi-score">
                            <strong>{{ $kpiView['skor_akhir'] }}</strong>
                            <span>skor akhir</span>
                        </div>
                    </div>

                    <div style="overflow-x:auto;">
                        <table class="kpi-table-mini">
                            <thead>
                                <tr>
                                    <th>Indikator</th>
                                    <th style="width:70px;">Bobot</th>
                                    <th style="width:70px;">Skor</th>
                                    <th style="width:80px;">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kpiView['details'] as $detail)
                                    <tr>
                                        <td>{{ $detail['nama'] }}</td>
                                        <td class="num">{{ $detail['bobot'] }}%</td>
                                        <td class="num">{{ $detail['skor'] }}</td>
                                        <td class="num">{{ $detail['nilai_akhir'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="kpi-feedback">
                        <p class="label">Umpan balik</p>
                        <p>{{ $kpiView['rekomendasi'] ?: 'Belum ada umpan balik.' }}</p>
                    </div>
                @else
                    <p class="kpi-empty">Belum ada penilaian KPI untuk {{ $kpiView['period_label'] }}.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Image Source Selection Modal -->
<div id="imageSourceModal" class="image-source-modal">
    <div class="image-source-container">
        <h3 class="image-source-title">Pilih Media dan File</h3>
        <div class="image-source-options">
            <button type="button" id="selectFromCameraBtn" class="image-source-option">
                <div class="image-source-icon camera">
                    <i class="fas fa-camera"></i>
                </div>
                <span class="image-source-text">Ambil Foto atau Video</span>
            </button>
            <button type="button" id="selectFromFileBtn" class="image-source-option">
                <div class="image-source-icon file">
                    <i class="fas fa-upload"></i>
                </div>
                <span class="image-source-text">Unggah dari File</span>
            </button>
        </div>
        <button type="button" id="cancelSourceBtn" class="image-source-cancel">
            Batal
        </button>
    </div>
</div>

<!-- Camera Modal -->
<div id="cameraModal" class="camera-modal">
    <div class="camera-container">
        <div class="camera-preview" id="cameraPreview">
            <video id="cameraVideo" autoplay playsinline style="display: none;"></video>
            <canvas id="cameraCanvas" style="display: none;"></canvas>
        </div>
        <div class="camera-controls" id="cameraControls">
            <button type="button" class="camera-btn camera-btn-cancel" id="cameraCancelBtn">
                <i class="fas fa-times"></i>
                <span>Batal</span>
            </button>
            <button type="button" class="camera-btn camera-btn-capture" id="cameraCaptureBtn">
                <i class="fas fa-camera"></i>
                <span>Ambil Foto</span>
            </button>
        </div>
        <div class="camera-controls" id="previewControls" style="display: none;">
            <button type="button" class="camera-btn camera-btn-retake" id="cameraRetakeBtn">
                <i class="fas fa-redo"></i>
                <span>Ulangi</span>
            </button>
            <button type="button" class="camera-btn camera-btn-use" id="cameraUseBtn">
                <i class="fas fa-check"></i>
                <span>Gunakan Foto</span>
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Global variables untuk menyimpan lokasi
    let currentLatitude = null;
    let currentLongitude = null;
    let currentLocationName = null;
    let currentAccuracy = null;
    let locationMap = null;
    let locationMarker = null;

    function showLocationGroup() {
        document.getElementById('locationGroup').style.display = 'block';
        document.getElementById('locationInfo').innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <i class="fas fa-spinner fa-spin" style="color: #6366f1;"></i>
                <span>Mengambil lokasi...</span>
            </div>
        `;
    }

    // Generic fungsi ambil lokasi, return Promise
    function fetchLocation(highAccuracy = false) {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                return reject({ message: 'Browser tidak mendukung geolocation.' });
            }

            navigator.geolocation.getCurrentPosition(
                position => {
                    resolve(position);
                },
                error => {
                    let errorMessage = 'Tidak dapat mendapatkan lokasi.';
                    if (error.code === error.PERMISSION_DENIED) {
                        errorMessage = 'Akses lokasi ditolak. Mohon izinkan akses lokasi.';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMessage = 'Informasi lokasi tidak tersedia.';
                    } else if (error.code === error.TIMEOUT) {
                        errorMessage = 'Waktu permintaan lokasi habis.';
                    }
                    reject({ message: errorMessage, code: error.code });
                },
                {
                    enableHighAccuracy: highAccuracy,
                    timeout: highAccuracy ? 12000 : 8000,
                    maximumAge: highAccuracy ? 0 : 300000
                }
            );
        });
    }

    // Ambil dan tampilkan lokasi (untuk semua jenis kerja)
    function requestLocationAndDisplay(highAccuracy = false) {
        showLocationGroup();
        fetchLocation(highAccuracy)
            .then(position => {
                currentLatitude = position.coords.latitude;
                currentLongitude = position.coords.longitude;
                currentAccuracy = position.coords.accuracy;

                return getLocationName(currentLatitude, currentLongitude)
                    .then(locationName => {
                        currentLocationName = locationName;
                        displayLocationWithMap(currentLatitude, currentLongitude, currentLocationName, currentAccuracy);
                    })
                    .catch(() => {
                        displayLocationWithMap(currentLatitude, currentLongitude, null, currentAccuracy);
                    });
            })
            .catch(err => {
                document.getElementById('locationInfo').innerHTML = `
                    <div style="color: #dc2626; font-size: 0.875rem;">
                        <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i>
                        ${err.message || 'Gagal mendapatkan lokasi.'}
                    </div>
                `;
            });
    }

    // Get location name via reverse geocoding
    function getLocationName(lat, lng) {
        return new Promise((resolve, reject) => {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                headers: {
                    'User-Agent': 'AbsensiICT/1.0'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    resolve(data.display_name);
                } else {
                    reject('Location name not available');
                }
            })
            .catch(() => {
                reject('Failed to get location name');
            });
        });
    }

    // Display location information with map
    function displayLocationWithMap(lat, lng, locationName, accuracy) {
        // Update location info
        let locationHtml = `
            <div style="margin-bottom: 0.5rem;">
                <div style="font-weight: 600; color: #111827; margin-bottom: 0.25rem;">
                    <i class="fas fa-map-marker-alt" style="color: #10b981; margin-right: 0.5rem;"></i>
                    ${locationName || 'Lokasi tidak tersedia'}
                </div>
                <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem;">
                    <i class="fas fa-info-circle" style="margin-right: 0.25rem;"></i>
                    Koordinat: ${lat.toFixed(7)}, ${lng.toFixed(7)}
                </div>
                <div style="font-size: 0.75rem; color: #6b7280;">
                    <i class="fas fa-crosshairs" style="margin-right: 0.25rem;"></i>
                    Akurasi: ±${Math.round(accuracy)} meter
                </div>
            </div>
        `;
        document.getElementById('locationInfo').innerHTML = locationHtml;

        // Initialize or update map
        if (!locationMap) {
            locationMap = L.map('locationMap').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(locationMap);
        } else {
            locationMap.setView([lat, lng], 15);
        }

        // Remove existing marker if any
        if (locationMarker) {
            locationMap.removeLayer(locationMarker);
        }

        // Add marker
        const userIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color:#10b981; width:32px; height:32px; border-radius:50%; border:3px solid white; display:flex; align-items:center; justify-content:center; color:white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="fas fa-map-marker-alt" style="font-size: 16px;"></i></div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        locationMarker = L.marker([lat, lng], {icon: userIcon}).addTo(locationMap)
            .bindPopup(`<b>Lokasi Anda</b><br>${locationName || 'Koordinat: ' + lat.toFixed(7) + ', ' + lng.toFixed(7)}`)
            .openPopup();

        // Add office location and circle if available
        const officeLat = {{ $settings->latitude ?? 'null' }};
        const officeLng = {{ $settings->longitude ?? 'null' }};
        const officeRadius = {{ $settings->radius ?? 100 }};

        if (officeLat && officeLng && officeRadius) {
            // Remove existing office circle and marker if any
            locationMap.eachLayer(function(layer) {
                if (layer instanceof L.Circle || (layer instanceof L.Marker && layer !== locationMarker)) {
                    locationMap.removeLayer(layer);
                }
            });

            // Add office circle
            L.circle([officeLat, officeLng], {
                radius: officeRadius,
                color: '#667eea',
                fillColor: '#667eea',
                fillOpacity: 0.2,
                weight: 2
            }).addTo(locationMap);

            // Add office marker
            const officeIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background-color:#667eea; width:32px; height:32px; border-radius:50%; border:3px solid white; display:flex; align-items:center; justify-content:center; color:white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="fas fa-building" style="font-size: 16px;"></i></div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });

            L.marker([officeLat, officeLng], {icon: officeIcon}).addTo(locationMap)
                .bindPopup(`<b>Lokasi Kantor</b><br>Radius: ${officeRadius}m`);

            // Fit bounds to show both locations
            const group = new L.featureGroup([
                locationMarker,
                L.marker([officeLat, officeLng])
            ]);
            locationMap.fitBounds(group.getBounds().pad(0.2));
        }

        // Recalculate map size
        setTimeout(() => {
            locationMap.invalidateSize();
        }, 100);
    }

    // Event listener untuk work type dropdown - selalu tampilkan lokasi untuk semua work type
    document.getElementById('work_type').addEventListener('change', function() {
        // Jika belum ada lokasi, request lokasi
        if (!currentLatitude || !currentLongitude) {
            requestLocationAndDisplay();
        } else {
            // Pastikan section terlihat untuk semua work type
            document.getElementById('locationGroup').style.display = 'block';
        }
    });

    // On load: langsung minta lokasi sekali untuk semua work type
    document.addEventListener('DOMContentLoaded', function() {
        requestLocationAndDisplay();
    });

    // Tombol refresh di atas map
    document.getElementById('refreshLocationBtn').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span> Refresh...</span>';

        requestLocationAndDisplay(true);

        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }, 1500);
    });

    // Image source selection functionality
    const imageSourceModal = document.getElementById('imageSourceModal');
    const imageSelectBtn = document.getElementById('imageSelectBtn');
    const selectFromFileBtn = document.getElementById('selectFromFileBtn');
    const selectFromCameraBtn = document.getElementById('selectFromCameraBtn');
    const cancelSourceBtn = document.getElementById('cancelSourceBtn');
    const imageInput = document.getElementById('image');

    // Open image source selection modal
    imageSelectBtn.addEventListener('click', function() {
        imageSourceModal.classList.add('active');
    });

    // Select from file
    selectFromFileBtn.addEventListener('click', function() {
        imageSourceModal.classList.remove('active');
        imageInput.click();
    });

    // Select from camera
    selectFromCameraBtn.addEventListener('click', function() {
        imageSourceModal.classList.remove('active');
        openCamera();
    });

    // Cancel source selection
    cancelSourceBtn.addEventListener('click', function() {
        imageSourceModal.classList.remove('active');
    });

    // Close on outside click or overlay
    imageSourceModal.addEventListener('click', function(e) {
        if (e.target === imageSourceModal) {
            imageSourceModal.classList.remove('active');
        }
    });

    // Camera functionality
    let cameraStream = null;
    const cameraModal = document.getElementById('cameraModal');
    const cameraVideo = document.getElementById('cameraVideo');
    const cameraCanvas = document.getElementById('cameraCanvas');
    const cameraPreview = document.getElementById('cameraPreview');
    const cameraCancelBtn = document.getElementById('cameraCancelBtn');
    const cameraCaptureBtn = document.getElementById('cameraCaptureBtn');
    const cameraRetakeBtn = document.getElementById('cameraRetakeBtn');
    const cameraUseBtn = document.getElementById('cameraUseBtn');
    const cameraControls = document.getElementById('cameraControls');
    const previewControls = document.getElementById('previewControls');

    // Open camera modal
    function openCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Tersedia',
                text: 'Browser Anda tidak mendukung akses kamera. Silakan gunakan opsi upload file.',
                confirmButtonColor: '#6366f1'
            });
            return;
        }

        cameraModal.classList.add('active');
        cameraVideo.style.display = 'block';
        cameraCanvas.style.display = 'none';
        cameraControls.style.display = 'flex';
        previewControls.style.display = 'none';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Tersedia',
                text: 'Browser Anda tidak mendukung akses kamera. Silakan gunakan opsi upload file.',
                confirmButtonColor: '#6366f1'
            });
            return;
        }

        cameraModal.classList.add('active');
        cameraVideo.style.display = 'block';
        cameraCanvas.style.display = 'none';
        cameraControls.style.display = 'flex';
        previewControls.style.display = 'none';

        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment', // Use back camera on mobile
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        })
        .then(function(stream) {
            cameraStream = stream;
            cameraVideo.srcObject = stream;
        })
        .catch(function(error) {
            console.error('Error accessing camera:', error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mengakses Kamera',
                text: 'Pastikan Anda memberikan izin akses kamera atau gunakan opsi upload file.',
                confirmButtonColor: '#6366f1'
            });
            closeCamera();
        });
    }

    // Capture photo
    cameraCaptureBtn.addEventListener('click', function() {
        const context = cameraCanvas.getContext('2d');
        cameraCanvas.width = cameraVideo.videoWidth;
        cameraCanvas.height = cameraVideo.videoHeight;
        context.drawImage(cameraVideo, 0, 0);
        
        cameraVideo.style.display = 'none';
        cameraCanvas.style.display = 'block';
        cameraControls.style.display = 'none';
        previewControls.style.display = 'flex';
    });

    // Retake photo
    cameraRetakeBtn.addEventListener('click', function() {
        cameraVideo.style.display = 'block';
        cameraCanvas.style.display = 'none';
        cameraControls.style.display = 'flex';
        previewControls.style.display = 'none';
    });

    // Use captured photo
    cameraUseBtn.addEventListener('click', function() {
        cameraCanvas.toBlob(function(blob) {
            const file = new File([blob], 'camera-photo.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            imageInput.files = dataTransfer.files;
            
            // Trigger preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                preview.innerHTML = `
                    <div style="position: relative; display: inline-block; margin-top: 0.75rem;">
                        <img src="${e.target.result}" alt="Preview" style="max-width: 100%; height: auto; max-height: 12rem; border-radius: 0.5rem;">
                        <button type="button" onclick="this.parentElement.parentElement.innerHTML=''; document.getElementById('image').value='';" style="position: absolute; top: 0.5rem; right: 0.5rem; background: #f43f5e; color: white; border-radius: 9999px; width: 1.75rem; height: 1.75rem; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer;">
                            <i class="fas fa-times" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }, 'image/jpeg', 0.9);
        
        closeCamera();
    });

    // Cancel camera
    cameraCancelBtn.addEventListener('click', function() {
        closeCamera();
    });

    // Close camera function
    function closeCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        cameraModal.classList.remove('active');
        cameraVideo.srcObject = null;
    }

    // Close on outside click
    cameraModal.addEventListener('click', function(e) {
        if (e.target === cameraModal) {
            closeCamera();
        }
    });

    // File input change handler
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                preview.innerHTML = `
                    <div style="position: relative; display: inline-block; margin-top: 0.75rem;">
                        <img src="${e.target.result}" alt="Preview" style="max-width: 100%; height: auto; max-height: 12rem; border-radius: 0.5rem;">
                        <button type="button" onclick="this.parentElement.parentElement.innerHTML=''; document.getElementById('image').value='';" style="position: absolute; top: 0.5rem; right: 0.5rem; background: #f43f5e; color: white; border-radius: 9999px; width: 1.75rem; height: 1.75rem; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer;">
                            <i class="fas fa-times" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('checkInBtn').addEventListener('click', function() {
        const workType = document.getElementById('work_type').value;
        
        if (!workType) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Silakan pilih jenis kerja terlebih dahulu!',
                confirmButtonColor: '#6366f1'
            });
            return;
        }

        // Pastikan lokasi sudah ada; jika belum, ambil dulu (untuk semua work type)
        if (!currentLatitude || !currentLongitude) {
            Swal.fire({
                title: 'Mengambil Lokasi',
                html: '<div style="text-align: center; padding: 12px 0;"><div class="swal2-loader" style="margin: 0 auto 12px;"></div><p style="margin-top: 8px; color: #6b7280; font-size: 14px; margin-bottom: 0;">Mohon izinkan akses lokasi untuk absensi</p></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal2-loading-popup',
                    htmlContainer: 'swal2-loading-html'
                }
            });

            fetchLocation(true)
                .then(position => {
                    currentLatitude = position.coords.latitude;
                    currentLongitude = position.coords.longitude;
                    currentAccuracy = position.coords.accuracy;

                    return getLocationName(currentLatitude, currentLongitude)
                        .then(locationName => {
                            currentLocationName = locationName;
                            displayLocationWithMap(currentLatitude, currentLongitude, currentLocationName, currentAccuracy);
                        })
                        .catch(() => {
                            displayLocationWithMap(currentLatitude, currentLongitude, null, currentAccuracy);
                        });
                })
                .then(() => {
                    Swal.close();
                    // Untuk semua work type, langsung submit (validasi hanya di backend untuk WFO)
                    submitCheckIn(currentLatitude, currentLongitude, true);
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gagal Mengambil Lokasi',
                        html: '<p style="margin-bottom: 12px;">Tidak dapat mendapatkan lokasi.</p><p style="font-size: 13px; color: #6b7280;">Lanjutkan check-in tanpa lokasi?</p>',
                        showCancelButton: true,
                        confirmButtonColor: '#6366f1',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitCheckIn(null, null, true);
                        }
                    });
                });
        } else {
            // Lokasi sudah ada, langsung submit (validasi hanya di backend untuk WFO)
            submitCheckIn(currentLatitude, currentLongitude, true);
        }
    });

    // Fungsi untuk menghitung jarak (Haversine formula)
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radius bumi dalam km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c; // Jarak dalam km
    }


    function compressCheckInImage(file) {
        return new Promise(function (resolve) {
            if (!file) {
                resolve(null);
                return;
            }
            var img = new Image();
            var objectUrl = URL.createObjectURL(file);
            img.onload = function () {
                var maxSide = 1600;
                var width = img.width;
                var height = img.height;
                if (width > maxSide || height > maxSide) {
                    if (width > height) {
                        height = Math.round(height * maxSide / width);
                        width = maxSide;
                    } else {
                        width = Math.round(width * maxSide / height);
                        height = maxSide;
                    }
                }
                var canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                canvas.toBlob(function (blob) {
                    URL.revokeObjectURL(objectUrl);
                    if (!blob) {
                        resolve(file);
                        return;
                    }
                    resolve(new File([blob], 'checkin.jpg', { type: 'image/jpeg' }));
                }, 'image/jpeg', 0.72);
            };
            img.onerror = function () {
                URL.revokeObjectURL(objectUrl);
                resolve(file);
            };
            img.src = objectUrl;
        });
    }

    function postAttendance(url, payload) {
        function applyToken(token) {
            if (!token) {
                return;
            }
            var meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                meta.setAttribute('content', token);
            }
            if (window.axios) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            }
            if (payload instanceof FormData) {
                payload.set('_token', token);
            }
        }

        function send(attempt) {
            return axios.post(url, payload, {
                withCredentials: true,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (response) {
                var data = response.data;
                if (data && typeof data === 'object' && data.success === true) {
                    return response;
                }
                if (attempt < 1 && response.status !== 400) {
                    return refreshAndRetry(attempt);
                }
                return Promise.reject({ response: response });
            }, function (error) {
                var status = error.response && error.response.status;
                if (attempt < 1 && (!status || status === 419 || status === 401 || status === 422 || status === 500 || status === 503)) {
                    return refreshAndRetry(attempt);
                }
                return Promise.reject(error);
            });
        }

        function refreshAndRetry(attempt) {
            var refresh = window.refreshCsrfToken ? window.refreshCsrfToken() : Promise.resolve(null);
            return Promise.resolve(refresh).then(function (token) {
                applyToken(token);
                return send(attempt + 1);
            });
        }

        var meta = document.querySelector('meta[name="csrf-token"]');
        applyToken(meta ? meta.getAttribute('content') : '');
        return send(0);
    }

    function submitCheckIn(latitude, longitude, locationValid = true) {
        const checkInBtn = document.getElementById('checkInBtn');
        if (checkInBtn) {
            checkInBtn.disabled = true;
        }
        const formData = new FormData();
        const workType = document.getElementById('work_type').value;
        formData.append('work_type', workType);
        formData.append('notes', document.getElementById('notes').value);
        formData.append('client_token', Date.now().toString(36) + Math.random().toString(36).slice(2));
        
        // Simpan lokasi untuk semua work type (WFA, WFH, WFO)
        if (latitude && longitude) {
            formData.append('latitude', latitude);
            formData.append('longitude', longitude);
        }
        
        const imageFile = document.getElementById('image').files[0];

        Swal.fire({
            title: 'Memproses...',
            html: '<div style="display: flex; justify-content: center; align-items: center; padding: 20px 0;"><div class="swal2-loader"></div></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        compressCheckInImage(imageFile).then(function (compressed) {
            if (compressed) {
                formData.append('image', compressed);
            }
            return postAttendance('/attendance/checkin', formData);
        })
        .then(response => {
            let icon = 'success';
            let title = 'Berhasil!';
            let text = response.data.message;
            
            // Show warning if location is invalid HANYA untuk WFO
            // WFA dan WFH tidak perlu validasi lokasi
            if (workType === 'WFO' && (response.data.location_valid === false || locationValid === false)) {
                icon = 'warning';
                title = 'Peringatan!';
                text = 'Check-in berhasil! Namun lokasi Anda berada di luar jangkauan kantor. Hubungi admin jika Anda merasa salah.';
            }
            
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                timer: icon === 'warning' ? 5000 : 2000,
                showConfirmButton: icon === 'warning',
                confirmButtonColor: '#6366f1'
            }).then(() => {
                // Update button text to show "Check-In Lagi" after first check-in
                document.getElementById('checkInBtnText').textContent = 'Check-In Lagi';
                
                // Clear form for next check-in
                document.getElementById('notes').value = '';
                document.getElementById('image').value = '';
                
                // Reload to update status card
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
                title: 'Gagal',
                text: message,
                confirmButtonColor: '#6366f1'
            });
        })
        .finally(function () {
            if (checkInBtn) {
                checkInBtn.disabled = false;
            }
        });
    }

    document.getElementById('checkOutBtn').addEventListener('click', function() {
        Swal.fire({
            title: 'Yakin ingin check-out?',
            text: 'Pastikan Anda sudah menyelesaikan pekerjaan hari ini',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6366f1',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Check-Out',
            cancelButtonText: 'Batal',
            buttonsStyling: true,
            reverseButtons: false,
            focusConfirm: false,
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                confirmButton: 'swal2-confirm-custom',
                cancelButton: 'swal2-cancel-custom'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    html: '<div style="display: flex; justify-content: center; align-items: center; padding: 20px 0;"><div class="swal2-loader"></div></div>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                postAttendance('/attendance/checkout')
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
                    });
            }
        });
    });

    document.querySelectorAll('[data-summary-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var detail = btn.closest('[data-summary-detail]');
            if (!detail) return;
            var isOpen = detail.classList.toggle('open');
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    (function () {
        var card = document.getElementById('summaryCard');
        var toggle = document.getElementById('summaryToggle');
        if (!card || !toggle) return;

        if (localStorage.getItem('absensiSummaryCollapsed') === '1') {
            card.classList.add('collapsed');
        }

        toggle.addEventListener('click', function () {
            card.classList.toggle('collapsed');
            localStorage.setItem('absensiSummaryCollapsed', card.classList.contains('collapsed') ? '1' : '0');
        });
    })();
</script>
@endsection