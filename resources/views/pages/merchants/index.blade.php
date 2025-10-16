@extends('layouts.app')
@section('title', 'Redeemed Vouchers')
@section('content')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Merchants</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                    <li class="breadcrumb-item active">Merchant List</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"></h4>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Merchant Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($merchants as $merchant)
                                <tr>
                                    <th scope="row">{{ $merchant->id }}</th>
                                    <td>{{ $merchant->company_name }}</td>
                                    <td>{{ $merchant->user->email }}</td>
                                    <td>{{ $merchant->contact_person }}</td>
                                    <td><span class="badge bg-success">{{ $merchant->status }}</span></td>
                                    <td>
                                        <a href="{{ route('merchants.edit', $merchant->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('merchants.destroy', $merchant->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                        <a href="{{ route('merchants.show', $merchant->id) }}" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $merchants->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->
@endsection