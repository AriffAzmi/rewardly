@extends('layouts.app')

@section('title', 'Vouchers List')

@section('content')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Vouchers</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Voucher List</li>
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
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Vouchers</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="{{ $vouchers->total() }}">0</span>
                        </h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                            <i class="ri-ticket-2-line text-primary"></i>
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
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Active Vouchers</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="{{ $activeCount ?? 0 }}">0</span>
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
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Used Vouchers</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="{{ $usedCount ?? 0 }}">0</span>
                        </h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                            <i class="ri-shopping-bag-line text-warning"></i>
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
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Expired Vouchers</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="{{ $expiredCount ?? 0 }}">0</span>
                        </h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-danger-subtle rounded fs-3">
                            <i class="ri-time-line text-danger"></i>
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
                        <i class="ri-ticket-line align-middle me-2"></i>Voucher List
                    </h5>
                    <div class="flex-shrink-0">
                        <div class="btn-group" role="group">
                            <a href="{{ route('vouchers.create') }}" class="btn btn-primary">
                                <i class="ri-upload-cloud-line align-middle me-1"></i> Bulk Upload
                            </a>
                            <a href="{{ route('vouchers.import.history') }}" class="btn btn-soft-secondary">
                                <i class="ri-history-line align-middle me-1"></i> Import History
                            </a>
                            <a href="{{ route('vouchers.export', request()->query()) }}" class="btn btn-soft-success">
                                <i class="ri-download-2-line align-middle me-1"></i> Export
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body border border-dashed border-end-0 border-start-0">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="ri-check-double-line label-icon"></i><strong>Success!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="ri-error-warning-line label-icon"></i><strong>Error!</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="ri-information-line label-icon"></i><strong>Info!</strong> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Filters -->
                <form method="GET" action="{{ route('vouchers.index') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-xxl-3 col-sm-6">
                            <div class="search-box">
                                <input type="text" name="search" class="form-control search" 
                                       placeholder="Search by code or SKU..." 
                                       value="{{ request('search') }}">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-sm-6">
                            <select name="merchant_id" class="form-select" data-choices data-choices-search-true>
                                <option value="">All Merchants</option>
                                @foreach($merchants as $merchant)
                                    <option value="{{ $merchant->id }}" {{ request('merchant_id') == $merchant->id ? 'selected' : '' }}>
                                        {{ $merchant->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xxl-2 col-sm-6">
                            <select name="status" class="form-select" data-choices>
                                <option value="">All Status</option>
                                <option value="{{ \App\Models\Voucher::STATUS_ACTIVE }}" {{ request('status') == \App\Models\Voucher::STATUS_ACTIVE ? 'selected' : '' }}>Active</option>
                                <option value="{{ \App\Models\Voucher::STATUS_INACTIVE }}" {{ request('status') == \App\Models\Voucher::STATUS_INACTIVE ? 'selected' : '' }}>Inactive</option>
                                <option value="{{ \App\Models\Voucher::STATUS_USED }}" {{ request('status') == \App\Models\Voucher::STATUS_USED ? 'selected' : '' }}>Used</option>
                            </select>
                        </div>

                        <div class="col-xxl-2 col-sm-6">
                            <input type="date" name="expiry_date" class="form-control" 
                                   placeholder="Filter by expiry" 
                                   value="{{ request('expiry_date') }}">
                        </div>

                        <div class="col-xxl-2 col-sm-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-equalizer-fill me-1 align-bottom"></i> Filter
                                </button>
                                <a href="{{ route('vouchers.index') }}" class="btn btn-light w-100">
                                    <i class="ri-refresh-line me-1 align-bottom"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <div class="table-responsive table-card mb-1">
                    <table class="table align-middle" id="voucherTable">
                        <thead class="table-light text-muted">
                            <tr>
                                <th scope="col" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                    </div>
                                </th>
                                <th class="sort" data-sort="code">Code</th>
                                <th class="sort" data-sort="sku">SKU</th>
                                <th class="sort" data-sort="description">Description</th>
                                <th class="sort" data-sort="merchant">Merchant</th>
                                <th class="sort" data-sort="denomination">Denomination</th>
                                <th class="sort" data-sort="discount">Discount</th>
                                <th class="sort" data-sort="status">Status</th>
                                <th class="sort" data-sort="expiry">Expiry Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all">
                            @forelse($vouchers as $index => $voucher)
                            <tr>
                                <th scope="row">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="voucher_ids[]" value="{{ $voucher->id }}">
                                    </div>
                                </th>
                                <td class="code">
                                    <code class="text-primary fw-semibold">{{ $voucher->code }}</code>
                                </td>
                                <td class="sku">
                                    <span class="text-muted small">{{ $voucher->sku }}</span>
                                </td>
                                <td class="description">
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $voucher->description }}">
                                        {{ Str::limit($voucher->description, 30) }}
                                    </span>
                                </td>
                                <td class="merchant">
                                    @if($voucher->merchant)
                                        <span class="badge bg-info-subtle text-info">
                                            <i class="ri-store-2-line align-middle me-1"></i>{{ $voucher->merchant->company_name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td class="denomination">
                                    <span class="fw-semibold text-dark">{{ $voucher->formatted_denominations }}</span>
                                </td>
                                <td class="discount">
                                    @if($voucher->discount_percentage > 0)
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="ri-price-tag-3-line align-middle me-1"></i>{{ $voucher->formatted_discount_percentage }}
                                        </span>
                                    @else
                                        <span class="text-muted small">No discount</span>
                                    @endif
                                </td>
                                <td class="status">
                                    @if($voucher->status === \App\Models\Voucher::STATUS_ACTIVE)
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="ri-checkbox-circle-line align-middle me-1"></i>Active
                                        </span>
                                    @elseif($voucher->status === \App\Models\Voucher::STATUS_INACTIVE)
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            <i class="ri-close-circle-line align-middle me-1"></i>Inactive
                                        </span>
                                    @elseif($voucher->status === \App\Models\Voucher::STATUS_USED)
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="ri-shopping-bag-line align-middle me-1"></i>Used
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">Unknown</span>
                                    @endif
                                </td>
                                <td class="expiry">
                                    <span class="{{ $voucher->is_expired ? 'text-danger fw-semibold' : 'text-muted' }}">
                                        <i class="ri-calendar-line align-middle me-1"></i>{{ $voucher->expiry_date->format('d M Y') }}
                                        @if($voucher->is_expired)
                                            <i class="ri-error-warning-fill align-middle ms-1 text-danger"></i>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <ul class="list-inline hstack gap-2 mb-0">
                                        <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="View Details">
                                            <a href="{{ route('vouchers.show', $voucher->id) }}" class="text-primary d-inline-block">
                                                <i class="ri-eye-fill fs-16"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item edit" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit Voucher">
                                            <a href="{{ route('vouchers.edit', $voucher->id) }}" class="text-primary d-inline-block edit-item-btn">
                                                <i class="ri-pencil-fill fs-16"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Delete Voucher">
                                            <form action="{{ route('vouchers.destroy', $voucher->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this voucher? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger d-inline-block remove-item-btn border-0 bg-transparent p-0">
                                                    <i class="ri-delete-bin-5-fill fs-16"></i>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                            colors="primary:#405189,secondary:#0ab39c" style="width:72px;height:72px">
                                        </lord-icon>
                                        <h5 class="mt-4">No vouchers found</h5>
                                        <p class="text-muted mb-3">
                                            @if(request()->hasAny(['search', 'merchant_id', 'status', 'expiry_date']))
                                                No vouchers match your filter criteria. Try adjusting your filters.
                                            @else
                                                You haven't uploaded any vouchers yet. Start by uploading your first batch!
                                            @endif
                                        </p>
                                        <a href="{{ route('vouchers.create') }}" class="btn btn-success">
                                            <i class="ri-upload-cloud-line align-middle me-1"></i> Upload Vouchers
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($vouchers->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing <span class="fw-semibold">{{ $vouchers->firstItem() }}</span> to 
                        <span class="fw-semibold">{{ $vouchers->lastItem() }}</span> of 
                        <span class="fw-semibold">{{ $vouchers->total() }}</span> results
                    </div>
                    <div>
                        {{ $vouchers->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger-subtle">
                <h5 class="modal-title text-danger" id="bulkActionModalLabel">
                    <i class="ri-delete-bin-line align-middle me-2"></i>Bulk Delete Vouchers
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('vouchers.bulk-destroy') }}" method="POST" id="bulkActionForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">You are about to delete <span id="selectedCount" class="fw-bold text-danger">0</span> voucher(s).</p>
                    <div class="alert alert-warning border-0">
                        <i class="ri-alert-line align-middle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone. All selected vouchers will be permanently deleted.
                    </div>
                    <div id="selectedVouchersContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line align-middle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you absolutely sure you want to delete selected vouchers? This cannot be undone!')">
                        <i class="ri-delete-bin-line align-middle me-1"></i> Delete Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Voucher index page loaded', {
        user: 'AriffAzmi',
        timestamp: '2025-10-14 06:58:43',
        total_vouchers: {{ $vouchers->total() }},
        current_page: {{ $vouchers->currentPage() }}
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Check all checkboxes
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('input[name="voucher_ids[]"]');
    
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    // Individual checkbox change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActions();
            
            // Update "check all" state
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
            
            if (checkAll) {
                checkAll.checked = allChecked;
                checkAll.indeterminate = someChecked && !allChecked;
            }
        });
    });

    // Update bulk actions
    function updateBulkActions() {
        const selected = document.querySelectorAll('input[name="voucher_ids[]"]:checked');
        const selectedCount = selected.length;
        
        document.getElementById('selectedCount').textContent = selectedCount;
        
        // Update hidden inputs in bulk form
        const container = document.getElementById('selectedVouchersContainer');
        container.innerHTML = '';
        
        selected.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'voucher_ids[]';
            input.value = checkbox.value;
            container.appendChild(input);
        });
    }

    // Show bulk actions modal when items are selected
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    if (bulkActionBtn) {
        bulkActionBtn.addEventListener('click', function() {
            const selected = document.querySelectorAll('input[name="voucher_ids[]"]:checked');
            if (selected.length === 0) {
                showAlert('Please select at least one voucher to delete.', 'warning');
                return;
            }
            
            const modal = new bootstrap.Modal(document.getElementById('bulkActionModal'));
            modal.show();
            
            console.log('Bulk delete modal opened', {
                user: 'AriffAzmi',
                selected_count: selected.length,
                timestamp: '2025-10-14 06:58:43'
            });
        });
    }

    // Show alert function
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="ri-${type === 'danger' ? 'error-warning' : type === 'warning' ? 'alert' : 'information'}-line label-icon"></i>
                <strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        const container = document.querySelector('.card-body');
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        setTimeout(function() {
            const alert = container.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
});

// Counter animation (for statistics)
document.querySelectorAll('.counter-value').forEach(counter => {
    const target = +counter.getAttribute('data-target');
    const duration = 1500; // 1.5 seconds
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
</script>
@endpush