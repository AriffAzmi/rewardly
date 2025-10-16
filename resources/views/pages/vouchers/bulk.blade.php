@extends('layouts.app')

@section('title', 'Import Vouchers')

@section('content')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Voucher Import</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Vouchers</a></li>
                    <li class="breadcrumb-item active">Bulk Upload</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="ri-upload-cloud-line align-middle me-2"></i>Upload Bulk Vouchers
                </h4>
                <a href="{{ route('vouchers.import.history') }}" class="btn btn-soft-secondary btn-sm">
                    <i class="ri-history-line align-middle me-1"></i> View Import History
                </a>
            </div>
            <div class="card-body">
                <!-- Success Message -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="ri-check-double-line label-icon"></i>
                        <strong>Success!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Error Message -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="ri-error-warning-line label-icon"></i>
                        <strong>Error!</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="ri-error-warning-line label-icon"></i>
                        <strong>Validation Errors</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Upload Form -->
                <form action="{{ route('vouchers.import.upload') }}" method="POST" enctype="multipart/form-data" id="voucherUploadForm">
                    @csrf

                    <!-- Merchant Selection -->
                    <div class="mb-3">
                        <label for="merchant_id" class="form-label">
                            <i class="ri-store-2-line align-middle me-1"></i>Select Merchant 
                            <span class="text-muted">(Optional)</span>
                        </label>
                        <select name="merchant_id" id="merchant_id" class="form-select" data-choices data-choices-search-true>
                            <option value="">-- Select Merchant (Optional) --</option>
                            @foreach($merchants as $merchant)
                                <option value="{{ $merchant->id }}" {{ old('merchant_id') == $merchant->id ? 'selected' : '' }}>
                                    {{ $merchant->company_name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            <i class="ri-information-line"></i> Leave empty if vouchers are not merchant-specific
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="file" class="form-label">
                            <i class="ri-file-excel-2-line align-middle me-1"></i>Upload Voucher File 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               class="form-control @error('file') is-invalid @enderror" 
                               id="file" 
                               name="file" 
                               accept=".xlsx,.xls,.csv" 
                               required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="ri-file-excel-2-line"></i> Accepted formats: <strong>.xlsx, .xls, .csv</strong> (Max: 20MB)
                        </div>
                    </div>

                    <!-- File Preview -->
                    <div id="filePreview" class="mb-3" style="display: none;">
                        <div class="alert alert-info mb-0 border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="ri-file-excel-2-line fs-1 text-info"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 text-info">Selected File:</h6>
                                    <p class="mb-0 fw-semibold" id="fileName"></p>
                                    <small class="text-muted" id="fileSize"></small>
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn-close" onclick="clearFile()"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Excel Format Guide -->
                    <div class="mb-3">
                        <div class="card border border-info mb-0">
                            <div class="card-header bg-info bg-opacity-10">
                                <h6 class="card-title mb-0 text-info">
                                    <i class="ri-information-line align-middle me-1"></i> Excel Format Guide
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">Your Excel file should contain the following columns:</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-3">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="12%">Column Name</th>
                                                <th width="28%">Accepted Variations</th>
                                                <th width="25%">Description</th>
                                                <th width="15%">Example</th>
                                                <th width="20%">Required</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><code class="text-primary fw-bold">NAME</code></td>
                                                <td>
                                                    <small class="text-muted">
                                                        Name, Description, Voucher Name
                                                    </small>
                                                </td>
                                                <td>Voucher description/name</td>
                                                <td><small>Petronas RM50</small></td>
                                                <td><span class="badge bg-danger">Always Required</span></td>
                                            </tr>
                                            <tr>
                                                <td><code class="text-primary fw-bold">DENO</code></td>
                                                <td>
                                                    <small class="text-muted">
                                                        Deno, Denomination, Price, Value, Amount, Retail Price
                                                    </small>
                                                </td>
                                                <td>Denomination/Retail price (numeric)</td>
                                                <td><small>50.00</small></td>
                                                <td>
                                                    <span class="badge bg-warning text-dark">
                                                        Either DENO or PERCENTAGE
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><code class="text-primary fw-bold">PERCENTAGE</code></td>
                                                <td>
                                                    <small class="text-muted">
                                                        Percentage, Percent, Discount
                                                    </small>
                                                </td>
                                                <td>Discount percentage (numeric, without % sign)</td>
                                                <td><small>50.00 (for 50%)</small></td>
                                                <td>
                                                    <span class="badge bg-warning text-dark">
                                                        Either DENO or PERCENTAGE
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><code class="text-primary fw-bold">UNIQUE_KEY</code></td>
                                                <td>
                                                    <small class="text-muted">
                                                        Unique_Key, Code, Voucher Code, Key
                                                    </small>
                                                </td>
                                                <td>Unique voucher code (must be unique)</td>
                                                <td><small>ABC01DEF331</small></td>
                                                <td><span class="badge bg-danger">Always Required</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Important Notes -->
                                <div class="alert alert-warning border-0 mb-0">
                                    <h6 class="alert-heading">
                                        <i class="ri-alert-line align-middle me-1"></i>Important Rules:
                                    </h6>
                                    <ul class="mb-0 small">
                                        <li>Column names are <strong>case-insensitive</strong> and <strong>flexible</strong> - the system accepts multiple variations (see "Accepted Variations" above).</li>
                                        <li>The first row <strong>must contain column headers</strong>. Data starts from row 2.</li>
                                        <li><strong>NAME</strong> and <strong>UNIQUE_KEY</strong> are always required for each row.</li>
                                        <li>At least one of <strong>DENO</strong> or <strong>PERCENTAGE</strong> must have a value in each row.</li>
                                        <li><strong>If only DENO is provided:</strong> No discount applied (cost price = retail price).</li>
                                        <li><strong>If only PERCENTAGE is provided:</strong> Prices set to 0 (manual adjustment needed).</li>
                                        <li><strong>If both provided:</strong> Cost price auto-calculated: DENO - (DENO × PERCENTAGE / 100).</li>
                                        <li>Each <strong>UNIQUE_KEY</strong> must be unique across all existing vouchers.</li>
                                        <li>Empty rows and rows missing required fields will be skipped automatically.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calculation Examples -->
                    <div class="mb-3">
                        <div class="card border border-success mb-0">
                            <div class="card-header bg-success bg-opacity-10">
                                <h6 class="card-title mb-0 text-success">
                                    <i class="ri-calculator-line align-middle me-1"></i> Calculation Examples
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <div class="p-3 bg-success bg-opacity-10 rounded border border-success">
                                            <h6 class="text-success mb-2">
                                                <i class="ri-check-line align-middle me-1"></i>Both DENO and PERCENTAGE
                                            </h6>
                                            <div class="small">
                                                <p class="mb-1"><strong>Input:</strong> DENO = 50.00, PERCENTAGE = 50.00</p>
                                                <p class="mb-1"><strong>Result:</strong></p>
                                                <ul class="mb-0">
                                                    <li>Retail Price: <strong>RM 50.00</strong></li>
                                                    <li>Discount: <strong>50%</strong></li>
                                                    <li>Cost Price: <strong>RM 25.00</strong> <small class="text-muted">(50 - 50% of 50)</small></li>
                                                    <li class="text-success"><i class="ri-check-fill"></i> Recommended format</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-warning bg-opacity-10 rounded border border-warning">
                                            <h6 class="text-warning mb-2">
                                                <i class="ri-information-line align-middle me-1"></i>Only DENO Provided
                                            </h6>
                                            <div class="small">
                                                <p class="mb-1"><strong>Input:</strong> DENO = 50.00, PERCENTAGE = (empty)</p>
                                                <p class="mb-1"><strong>Result:</strong></p>
                                                <ul class="mb-0">
                                                    <li>Retail Price: <strong>RM 50.00</strong></li>
                                                    <li>Discount: <strong>0%</strong></li>
                                                    <li>Cost Price: <strong>RM 50.00</strong> <small class="text-muted">(no discount)</small></li>
                                                    <li class="text-warning"><i class="ri-alert-fill"></i> No discount applied</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Template Download -->
                    <div class="mb-3">
                        <div class="card border border-primary mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-primary-subtle text-primary rounded fs-2">
                                                <i class="ri-download-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">
                                            <i class="ri-file-excel-line align-middle me-1"></i>Download Sample Template
                                        </h6>
                                        <p class="text-muted mb-2 small">
                                            Get our pre-formatted Excel template with sample data and all correct column headers
                                        </p>
                                        <a href="{{ asset('templates/voucher-import-sample.xlsx') }}" 
                                           class="btn btn-primary btn-sm" 
                                           download>
                                            <i class="ri-file-excel-2-line align-middle me-1"></i> Download Sample Template
                                        </a>
                                        <button type="button" 
                                                class="btn btn-soft-secondary btn-sm ms-2" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Contains 10 sample voucher rows with correct format">
                                            <i class="ri-information-line align-middle"></i> Template Info
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Processing Info -->
                    <div class="mb-4">
                        <div class="alert alert-info border-0 mb-0">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="ri-time-line fs-2 text-info"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="alert-heading text-info">
                                        <i class="ri-cloud-line align-middle me-1"></i>Background Processing
                                    </h6>
                                    <p class="mb-2">
                                        Your file will be uploaded and processed completely in the <strong>background</strong>. 
                                        This means:
                                    </p>
                                    <ul class="mb-2 small">
                                        <li><strong>No browser timeout</strong> - Works with files containing 50,000+ rows</li>
                                        <li><strong>Real-time progress tracking</strong> - Monitor status in Import History</li>
                                        <li><strong>Can close this page</strong> - Processing continues even if you navigate away</li>
                                        <li><strong>Email notification</strong> - You'll be notified when import completes (if enabled)</li>
                                    </ul>
                                    <p class="mb-0">
                                        Track progress in 
                                        <a href="{{ route('vouchers.import.history') }}" class="alert-link fw-semibold">
                                            <i class="ri-history-line align-middle"></i> Import History
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('vouchers.index') }}" class="btn btn-light">
                            <i class="ri-arrow-left-line align-middle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="ri-upload-cloud-line align-middle me-1"></i> Upload & Process Vouchers
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal (shown after upload) -->
<div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-subtle border-0">
                <h5 class="modal-title text-primary" id="progressModalLabel">
                    <i class="ri-upload-cloud-line align-middle me-2"></i>Uploading Vouchers
                </h5>
            </div>
            <div class="modal-body text-center py-5">
                <div class="mb-4">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <h5 class="mb-2 text-primary">Processing your file...</h5>
                <p class="text-muted mb-3">Please wait while we upload and queue your vouchers for processing.</p>
                <div class="alert alert-info bg-info-subtle border-0 text-start">
                    <small>
                        <i class="ri-information-line align-middle me-1"></i>
                        <strong>Tip:</strong> You'll be redirected to the import details page where you can track real-time progress.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Voucher import page loaded', {
        user: 'AriffAzmi',
        timestamp: '2025-10-14 09:08:30',
        page: 'bulk-upload'
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const fileInput = document.getElementById('file');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const form = document.getElementById('voucherUploadForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // File preview
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            filePreview.style.display = 'block';
            
            console.log('File selected', {
                filename: file.name,
                size: file.size,
                type: file.type,
                user: 'AriffAzmi',
                timestamp: '2025-10-14 09:08:30'
            });
            
            // Validate file size (20MB)
            if (file.size > 20 * 1024 * 1024) {
                showAlert('File size exceeds 20MB. Please choose a smaller file.', 'danger');
                fileInput.value = '';
                filePreview.style.display = 'none';
                return;
            }
            
            // Validate file extension
            const validExtensions = ['xlsx', 'xls', 'csv'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (!validExtensions.includes(fileExtension)) {
                showAlert('Invalid file format. Please upload an Excel (.xlsx, .xls) or CSV (.csv) file.', 'danger');
                fileInput.value = '';
                filePreview.style.display = 'none';
                return;
            }
        } else {
            filePreview.style.display = 'none';
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate file
        if (!fileInput.files[0]) {
            showAlert('Please select a file to upload.', 'warning');
            return;
        }
        
        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';
        
        // Show progress modal
        const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
        progressModal.show();
        
        // Log upload attempt
        console.log('Voucher import submission', {
            filename: fileInput.files[0].name,
            filesize: fileInput.files[0].size,
            merchant_id: document.getElementById('merchant_id').value,
            user: 'AriffAzmi',
            timestamp: '2025-10-14 09:08:30'
        });
        
        // Submit form
        form.submit();
    });
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    // Show alert
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="ri-${type === 'danger' ? 'error-warning' : type === 'warning' ? 'alert' : 'information'}-line label-icon"></i>
                <strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Insert alert at the top of card-body
        const cardBody = form.parentElement;
        cardBody.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            const alert = cardBody.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
    
    // Clear file function
    window.clearFile = function() {
        fileInput.value = '';
        filePreview.style.display = 'none';
        
        console.log('File cleared', {
            user: 'AriffAzmi',
            timestamp: '2025-10-14 09:08:30'
        });
    };
});
</script>
@endpush