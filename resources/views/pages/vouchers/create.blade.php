@extends('layouts.app')
@section('title', 'Create Voucher')
@section('content')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Voucher</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                    <li class="breadcrumb-item active">Create Voucher</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Create New Voucher</h4>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label for="merchant" class="form-label">Select Merchant</label>
                        <select name="merchant" id="merchant" class="form-control">
                            <option value="">Select Merchant</option>
                            <option value="merchant1">Merchant 1</option>
                            <option value="merchant2">Merchant 2</option>
                            <option value="merchant3">Merchant 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="voucherCode" class="form-label">Voucher Code</label>
                        <input type="text" class="form-control" id="voucherCode" placeholder="Enter voucher code">
                    </div>
                    <div class="mb-3">
                        <label for="expiryDate" class="form-label">Expiry Date</label>
                        <input type="text" class="form-control" id="expiryDate" placeholder="Enter expiry date">
                    </div>
                    <!-- submit button -->
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Create Voucher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->
@endsection
