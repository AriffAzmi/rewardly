@extends('layouts.app')

@section('title', $pageTitle ?? 'Import Details')

@section('content')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Import Details</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Vouchers</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vouchers.import.history') }}">Import History</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Import Information Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-primary-subtle">
                <h5 class="card-title mb-0 text-primary">
                    <i class="ri-information-line align-middle me-2"></i>Import Information
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small mb-1">Import Log ID</label>
                    <p class="mb-0">
                        <code class="text-primary">{{ $import['import_log_id'] }}</code>
                        <button class="btn btn-sm btn-soft-secondary ms-2" onclick="copyToClipboard('{{ $import['import_log_id'] }}')" title="Copy">
                            <i class="ri-file-copy-line"></i>
                        </button>
                    </p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small mb-1">Import ID</label>
                    <p class="mb-0">
                        <code class="text-primary">{{ $import['import_id'] }}</code>
                        <button class="btn btn-sm btn-soft-secondary ms-2" onclick="copyToClipboard('{{ $import['import_id'] }}')" title="Copy">
                            <i class="ri-file-copy-line"></i>
                        </button>
                    </p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small mb-1">Filename</label>
                    <p class="mb-0 fw-semibold">
                        <i class="ri-file-excel-2-line align-middle me-1 text-success"></i>{{ $import['filename'] }}
                    </p>
                </div>

                @if($import['merchant'])
                <div class="mb-3">
                    <label class="text-muted small mb-1">Merchant</label>
                    <p class="mb-0">
                        <span class="badge bg-info-subtle text-info">
                            <i class="ri-store-2-line align-middle me-1"></i>{{ $import['merchant']['name'] }}
                        </span>
                    </p>
                </div>
                @endif

                <div class="mb-3">
                    <label class="text-muted small mb-1">Uploaded By</label>
                    <p class="mb-0">
                        <i class="ri-user-line align-middle me-1"></i>{{ $import['user']['name'] }}
                    </p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small mb-1">Status</label>
                    <p class="mb-0">
                        @if($import['status'] === 'completed')
                            <span class="badge bg-success badge-lg">
                                <i class="ri-checkbox-circle-line align-middle me-1"></i>Completed
                            </span>
                        @elseif($import['status'] === 'processing')
                            <span class="badge bg-primary badge-lg">
                                <i class="ri-loader-4-line align-middle me-1"></i>Processing
                            </span>
                        @elseif($import['status'] === 'pending')
                            <span class="badge bg-warning badge-lg">
                                <i class="ri-time-line align-middle me-1"></i>Pending
                            </span>
                        @elseif($import['status'] === 'failed')
                            <span class="badge bg-danger badge-lg">
                                <i class="ri-error-warning-line align-middle me-1"></i>Failed
                            </span>
                        @elseif($import['status'] === 'partial')
                            <span class="badge bg-warning badge-lg">
                                <i class="ri-alert-line align-middle me-1"></i>Partial
                            </span>
                        @endif
                    </p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small mb-1">Started At</label>
                    <p class="mb-0">
                        @if($import['started_at'])
                            <i class="ri-calendar-line align-middle me-1"></i>{{ \Carbon\Carbon::parse($import['started_at'])->format('d M Y H:i:s') }}
                        @else
                            <span class="text-muted">Not started yet</span>
                        @endif
                    </p>
                </div>

                @if($import['completed_at'])
                <div class="mb-3">
                    <label class="text-muted small mb-1">Completed At</label>
                    <p class="mb-0">
                        <i class="ri-calendar-check-line align-middle me-1"></i>{{ \Carbon\Carbon::parse($import['completed_at'])->format('d M Y H:i:s') }}
                    </p>
                </div>
                @endif

                @if($import['error_message'])
                <div class="mb-3">
                    <label class="text-muted small mb-1">Error Message</label>
                    <div class="alert alert-danger mb-0">
                        <i class="ri-error-warning-line align-middle me-1"></i>{{ $import['error_message'] }}
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="d-grid gap-2 mt-4">
                    @if(in_array($import['status'], ['processing', 'pending']))
                    <form action="{{ route('vouchers.import.cancel', $import['import_log_id']) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to cancel this import?')">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="ri-stop-circle-line align-middle me-1"></i> Cancel Import
                        </button>
                    </form>
                    @endif

                    @if(in_array($import['status'], ['completed', 'partial']) && $import['processed_rows'] > 0)
                    <a href="{{ route('vouchers.import.export-links', $import['import_log_id']) }}" 
                       class="btn btn-success w-100"
                       onclick="console.log('Exporting redemption links', {import_id: '{{ $import['import_id'] }}', voucher_count: {{ $import['processed_rows'] }}, user: 'AriffAzmi', timestamp: '2025-10-15 09:07:45'})">
                        <i class="ri-file-excel-line align-middle me-1"></i> Export Redemption Links ({{ number_format($import['processed_rows']) }})
                    </a>
                    @endif
                    
                    <form action="{{ route('vouchers.import.delete', $import['import_log_id']) }}" 
                          method="POST" 
                          onsubmit="return confirm('Delete this import and all associated vouchers? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="ri-delete-bin-line align-middle me-1"></i> Delete Import
                        </button>
                    </form>
                    
                    <a href="{{ route('vouchers.import.history') }}" class="btn btn-light w-100">
                        <i class="ri-arrow-left-line align-middle me-1"></i> Back to History
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Progress Card -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-success-subtle">
                <h5 class="card-title mb-0 text-success">
                    <i class="ri-bar-chart-line align-middle me-2"></i>Import Progress
                </h5>
            </div>
            <div class="card-body">
                <!-- Statistics Row -->
                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h2 class="text-primary mb-1" id="totalRows">{{ $import['total_rows'] }}</h2>
                            <p class="text-muted mb-0 small">Total Rows</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h2 class="text-success mb-1" id="processedRows">{{ $import['processed_rows'] }}</h2>
                            <p class="text-muted mb-0 small">Processed</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h2 class="text-danger mb-1" id="failedRows">{{ $import['failed_rows'] }}</h2>
                            <p class="text-muted mb-0 small">Failed</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <label class="text-muted small">Progress</label>
                        <span class="text-muted small" id="progressText">{{ $import['progress_percentage'] }}%</span>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar progress-bar-striped {{ in_array($import['status'], ['processing', 'pending']) ? 'progress-bar-animated' : '' }}" 
                             role="progressbar" 
                             id="progressBar"
                             style="width: {{ $import['progress_percentage'] }}%;" 
                             aria-valuenow="{{ $import['progress_percentage'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <span class="fw-semibold">{{ $import['progress_percentage'] }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Vouchers Table -->
                <div id="vouchersContainer">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="ri-ticket-line align-middle me-2"></i>Imported Vouchers
                        </h5>
                        <a href="{{ route('vouchers.index', ['import_id' => $import['import_id']]) }}" class="btn btn-sm btn-primary">
                            <i class="ri-filter-line align-middle me-1"></i> View All in List
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>SKU</th>
                                    <th>Description</th>
                                    <th>Denomination</th>
                                    <th>Discount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="vouchersTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                        <span class="text-muted">Loading vouchers...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="paginationContainer" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const importLogId = '{{ $import["import_log_id"] }}';
const importStatus = '{{ $import["status"] }}';

console.log('Import details page loaded', {
    import_log_id: importLogId,
    import_id: '{{ $import["import_id"] }}',
    status: importStatus,
    user: 'AriffAzmi',
    timestamp: '2025-10-14 07:07:18'
});

// Copy to clipboard function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show toast notification
        showToast('Copied to clipboard!', 'success');
    }).catch(function(err) {
        console.error('Failed to copy:', err);
        showToast('Failed to copy', 'danger');
    });
}

// Show toast notification
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <i class="ri-${type === 'success' ? 'check-line' : 'error-warning-line'} align-middle me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Poll for progress updates if still processing
if (importStatus === 'processing' || importStatus === 'pending') {
    const progressInterval = setInterval(async () => {
        try {
            const response = await fetch(`/vouchers/import/${importLogId}/progress`);
            const data = await response.json();
            
            if (data.success) {
                const progress = data.data;
                
                // Update UI
                document.getElementById('totalRows').textContent = progress.total_rows;
                document.getElementById('processedRows').textContent = progress.processed_rows;
                document.getElementById('failedRows').textContent = progress.failed_rows;
                document.getElementById('progressBar').style.width = progress.progress_percentage + '%';
                document.getElementById('progressBar').setAttribute('aria-valuenow', progress.progress_percentage);
                document.getElementById('progressBar').querySelector('span').textContent = progress.progress_percentage + '%';
                document.getElementById('progressText').textContent = progress.progress_percentage + '%';
                
                console.log('Progress updated', {
                    percentage: progress.progress_percentage,
                    processed: progress.processed_rows,
                    failed: progress.failed_rows,
                    user: 'AriffAzmi',
                    timestamp: '2025-10-14 07:07:18'
                });
                
                // Stop polling if completed
                if (['completed', 'failed', 'partial'].includes(progress.status)) {
                    clearInterval(progressInterval);
                    console.log('Import completed, reloading page', {
                        status: progress.status,
                        user: 'AriffAzmi',
                        timestamp: '2025-10-14 07:07:18'
                    });
                    location.reload(); // Reload to update status badge
                }
            }
        } catch (error) {
            console.error('Failed to fetch progress:', error);
        }
    }, 2000); // Poll every 2 seconds
}

// Load vouchers
async function loadVouchers(page = 1) {
    try {
        const response = await fetch(`/vouchers/import/${importLogId}/vouchers?per_page=10&page=${page}`);
        const data = await response.json();
        
        if (data.success) {
            const vouchers = data.data.data;
            const tbody = document.getElementById('vouchersTableBody');
            
            if (vouchers.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="ri-inbox-line fs-1 text-muted d-block mb-2"></i>
                            <span class="text-muted">No vouchers imported yet. They will appear as processing completes.</span>
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = vouchers.map(voucher => `
                    <tr>
                        <td><code class="text-primary">${voucher.code}</code></td>
                        <td><small class="text-muted">${voucher.sku}</small></td>
                        <td>${voucher.description.substring(0, 30)}${voucher.description.length > 30 ? '...' : ''}</td>
                        <td class="fw-semibold">RM ${parseFloat(voucher.denominations).toFixed(2)}</td>
                        <td>
                            ${voucher.discount_percentage > 0 
                                ? `<span class="badge bg-success-subtle text-success">${parseFloat(voucher.discount_percentage).toFixed(2)}%</span>` 
                                : '<span class="text-muted small">No discount</span>'}
                        </td>
                        <td>
                            ${voucher.status === 1 
                                ? '<span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-line align-middle me-1"></i>Active</span>' 
                                : voucher.status === 0 
                                    ? '<span class="badge bg-secondary-subtle text-secondary"><i class="ri-close-circle-line align-middle me-1"></i>Inactive</span>'
                                    : '<span class="badge bg-warning-subtle text-warning"><i class="ri-shopping-bag-line align-middle me-1"></i>Used</span>'}
                        </td>
                    </tr>
                `).join('');
                
                // Add pagination
                renderPagination(data.data);
            }
            
            console.log('Vouchers loaded', {
                count: vouchers.length,
                page: page,
                user: 'AriffAzmi',
                timestamp: '2025-10-14 07:07:18'
            });
        }
    } catch (error) {
        console.error('Failed to load vouchers:', error);
        document.getElementById('vouchersTableBody').innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-danger">
                    <i class="ri-error-warning-line fs-1 d-block mb-2"></i>
                    Failed to load vouchers. Please refresh the page.
                </td>
            </tr>
        `;
    }
}

function renderPagination(paginationData) {
    const container = document.getElementById('paginationContainer');
    
    if (paginationData.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<nav><ul class="pagination pagination-sm justify-content-center mb-0">';
    
    // Previous button
    html += `
        <li class="page-item ${paginationData.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadVouchers(${paginationData.current_page - 1}); return false;">
                <i class="ri-arrow-left-s-line"></i>
            </a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= paginationData.last_page; i++) {
        html += `
            <li class="page-item ${i === paginationData.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadVouchers(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    // Next button
    html += `
        <li class="page-item ${paginationData.current_page === paginationData.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadVouchers(${paginationData.current_page + 1}); return false;">
                <i class="ri-arrow-right-s-line"></i>
            </a>
        </li>
    `;
    
    html += '</ul></nav>';
    container.innerHTML = html;
}

// Initial load
loadVouchers();

// Auto refresh vouchers if still processing
if (importStatus === 'processing' || importStatus === 'pending') {
    setInterval(() => {
        loadVouchers();
    }, 5000); // Refresh vouchers every 5 seconds
}
</script>
@endpush