@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Role</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('role') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        @include('message')
        <div class="container-fluid">
            <form action="{{ route('roleStore') }}" method="post">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Name">
                                </div>
                                @error('name')
                                    <p class="invalid-feedback">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        @if (!empty($permissions) && $permissions->count() > 0)
                            @php
                                $columns = 3; // Number of columns
                                $chunkSize = ceil($permissions->count() / $columns);
                                $permissionsChunks = $permissions->chunk($chunkSize);
                            @endphp

                            <div class="row">
                                @foreach ($permissionsChunks as $chunk)
                                    <div class="col-md-4"> <!-- Each column -->
                                        @foreach ($chunk as $permission)
                                            <div class="mb-2">
                                                <input type="checkbox" class="rounded" name="permission[]"
                                                    id="permission-{{ $permission->id }}" value="{{ $permission->name }}">
                                                <label
                                                    for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="pb-5 pt-3">
                    <button class="btn btn-primary">Create</button>
                    <a href="{{ route('role') }}" class="btn btn-outline-dark ml-3">Cancel</a>
                </div>
            </form>
        </div>
        <!-- /.card -->
    </section>
@endsection
