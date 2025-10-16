@extends('layouts.app')

@section('title', 'Import History')

@section('content')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Import History</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Vouchers</a></li>
                    <li class="breadcrumb-item active">Import History</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Imports</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="{{ count($imports) }}">0</span>
                        </h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                            <i class="ri-upload-cloud-line text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Completed</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="{{ collect($imports)->where('status', 'completed')->count() }}">0</span>
                        </h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="ri-check-double-line text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Processing</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="{{ collect($imports)->whereIn('status', ['processing', 'pending'])->count() }}">0</span>
                        </h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                            <i class="ri-loader-4-line text-warning"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Failed</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="{{ collect($imports)->where('status', 'failed')->count() }}">0</span>
                        </h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-danger-subtle rounded fs-3">
                            <i class="ri-error-warning-line text-danger"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-history-line align-middle me-2"></i>Import History
                    </h5>
                    <div class="flex-shrink-0">
                        <a href="{{ route('vouchers.create') }}" class="btn btn-primary">
                            <i class="ri-upload-cloud-line align-middle me-1"></i> New Import
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body border border-dashed border-end-0 border-start-0">
                <!-- Filters -->
                <form method="GET" action="{{ route('vouchers.import.history') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-xxl-5 col-sm-6">
                            <select name="merchant_id" class="form-select" data-choices data-choices-search-true onchange="this.form.submit()">
                                <option value="">All Merchants</option>
                                @foreach($merchants as $merchant)
                                    <option value="{{ $merchant->id }}" {{ request('merchant_id') == $merchant->id ? 'selected' : '' }}>
                                        {{ $merchant->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xxl-2 col-sm-6">
                            <button type="button" class="btn btn-light w-100" onclick="window.location='{{ route('vouchers.import.history') }}'">
                                <i class="ri-refresh-line align-middle me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <div class="table-responsive table-card mb-1">
                    <table class="table align-middle" id="importHistoryTable">
                        <thead class="table-light text-muted">
                            <tr>
                                <th>Import ID</th>
                                <th>Filename</th>
                                <th>Merchant</th>
                                <th>Total Rows</th>
                                <th>Processed</th>
                                <th>Failed</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($imports as $import)
                            <tr>
                                <td>
                                    <code class="text-primary fw-semibold">{{ $import['import_id'] }}</code>
                                </td>
                                <td>
                                    <i class="ri-file-excel-2-line text-success align-middle me-1"></i>
                                    {{ Str::limit($import['filename'], 30) }}
                                </td>
                                <td>
                                    @if($import['merchant'])
                                        <span class="badge bg-info-subtle text-info">
                                            <i class="ri-store-2-line align-middle me-1"></i>{{ $import['merchant']['name'] }}
                                        </span>
                                    @else
                                        <span class="text-muted small">No merchant</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($import['total_rows']) }}</span>
                                </td>
                                <td>
                                    <span class="text-success fw-semibold">{{ number_format($import['processed_rows']) }}</span>
                                </td>
                                <td>
                                    <span class="text-danger fw-semibold">{{ number_format($import['failed_rows']) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 me-2">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $import['status'] === 'completed' ? 'bg-success' : ($import['status'] === 'failed' ? 'bg-danger' : 'bg-primary') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $import['progress_percentage'] }}%"
                                                     aria-valuenow="{{ $import['progress_percentage'] }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <span class="fw-semibold">{{ $import['progress_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($import['status'] === 'completed')
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="ri-checkbox-circle-line align-middle me-1"></i>Completed
                                        </span>
                                    @elseif($import['status'] === 'processing')
                                        <span class="badge bg-primary-subtle text-primary">
                                            <i class="ri-loader-4-line align-middle me-1"></i>Processing
                                        </span>
                                    @elseif($import['status'] === 'pending')
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="ri-time-line align-middle me-1"></i>Pending
                                        </span>
                                    @elseif($import['status'] === 'failed')
                                        <span class="badge bg-danger-subtle text-danger">
                                            <i class="ri-error-warning-line align-middle me-1"></i>Failed
                                        </span>
                                    @elseif($import['status'] === 'partial')
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="ri-alert-line align-middle me-1"></i>Partial
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="ri-calendar-line align-middle me-1"></i>
                                        {{ \Carbon\Carbon::parse($import['created_at'])->format('d M Y H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('vouchers.import.details', $import['import_log_id']) }}" 
                                           class="btn btn-sm btn-soft-primary"
                                           data-bs-toggle="tooltip"
                                           title="View Details">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        
                                        @if(in_array($import['status'], ['processing', 'pending']))
                                        <form action="{{ route('vouchers.import.cancel', $import['import_log_id']) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Cancel this import?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-soft-warning"
                                                    data-bs-toggle="tooltip"
                                                    title="Cancel Import">
                                                <i class="ri-stop-circle-line"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        <form action="{{ route('vouchers.import.delete', $import['import_log_id']) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Delete this import and all associated vouchers? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-soft-danger"
                                                    data-bs-toggle="tooltip"
                                                    title="Delete Import">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                            colors="primary:#405189,secondary:#0ab39c" style="width:72px;height:72px">
                                        </lord-icon>
                                        <h5 class="mt-4">No import history found</h5>
                                        <p class="text-muted mb-3">
                                            You haven't imported any vouchers yet. Start by uploading your first file!
                                        </p>
                                        <a href="{{ route('vouchers.import.upload-bulk-vouchers') }}" class="btn btn-primary">
                                            <i class="ri-upload-cloud-line align-middle me-1"></i> Import Vouchers
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Import history page loaded', {
        user: 'AriffAzmi',
        timestamp: '2025-10-15 03:53:21',
        total_imports: {{ count($imports) }}
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Counter animation
    document.querySelectorAll('.counter-value').forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const duration = 1500;
        const step = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.textContent = Math.ceil(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        updateCounter();
    });

    // Auto-refresh for processing imports
    const processingImports = {{ collect($imports)->whereIn('status', ['processing', 'pending'])->count() }};
    
    if (processingImports > 0) {
        console.log('Auto-refresh enabled - processing imports detected', {
            count: processingImports,
            user: 'AriffAzmi',
            timestamp: '2025-10-15 03:53:21'
        });
        
        // Refresh page every 5 seconds if there are processing imports
        setTimeout(() => {
            location.reload();
        }, 5000);
    }
});
</script>
@endpush