@extends('layouts.app')
@section('title', 'Create Merchant')
@section('content')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Merchant</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                    <li class="breadcrumb-item active">Create Merchant</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Create New Merchant</h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('merchants.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="merchantName" class="form-label">Select User</label>
                        <select class="form-select" id="merchantName" name="user_id">
                            <option value="">Choose user...</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="merchantName" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="merchantName" name="company_name" placeholder="Enter company name">
                    </div>
                    <!-- submit button -->
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Create Merchant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->
@endsection
