@extends('layouts.app')
@section('title', 'View Merchant')
@section('content')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Merchant</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                    <li class="breadcrumb-item active">View Merchant</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">View Merchant</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('merchants.update', $merchant->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="merchantName" class="form-label">Merchant Name</label>
                        <input type="text" class="form-control" id="merchantName" placeholder="Enter merchant name" value="{{ $merchant->name }}" >
                    </div>
                    <!-- submit button -->
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Update Merchant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
