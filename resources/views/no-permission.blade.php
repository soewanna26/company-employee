@extends('admin.layouts.app')

@section('title', 'Access Denied')
@section('content')
    <div class="d-flex justify-content-center align-items-center" style="height: 70vh;">
        <div class="text-center">
            <h1 class="text-danger">403</h1>
            <h2>You don't have permission to view this page</h2>
            <p class="text-muted">Please contact the administrator if you believe this is an error.</p>
        </div>
    </div>
@endsection
