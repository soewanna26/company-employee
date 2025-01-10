@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit User</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('user') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        @include('message')
        <div class="container-fluid">
            <form action="{{ route('userUpdate', $user->id) }}" method="post">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Name" value="{{ old('name', $user->name) }}">
                                </div>
                                @error('name')
                                    <p class="invalid-feedback">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="Email" value="{{ old('email', $user->email) }}">
                                </div>
                                @error('email')
                                    <p class="invalid-feedback">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        @if (!empty($roles) && $roles->count() > 0)
                            <div class="mb-3">
                                <!-- Select All Checkbox -->
                                <div class="mb-2">
                                    <input type="checkbox" id="select-all" class="rounded" onclick="toggleSelectAll(this)">
                                    <label for="select-all"><strong>Select All</strong></label>
                                </div>
                            </div>
                            <div class="grid grid-cols-4 mb-3">

                                @php
                                    $columns = 3; // Number of columns
                                    $chunkSize = ceil($roles->count() / $columns);
                                    $rolesChunks = $roles->chunk($chunkSize);
                                @endphp
                                <div class="row">
                                    @foreach ($rolesChunks as $chunk)
                                        <div class="col-md-4"> <!-- Each column -->
                                            @foreach ($chunk as $role)
                                                <div class="mb-2">
                                                    <input type="checkbox" name="role[]"
                                                        id="role-{{ $role->id }}"
                                                        value="{{ $role->name }}" class="rounded role-checkbox"
                                                        {{ $hasRoles->contains($role->name) ? 'checked' : '' }}>
                                                    <label
                                                        for="role-{{ $role->id }}">{{ $role->name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p>No roles available.</p>
                        @endif
                    </div>
                </div>
        </div>
        <div class="pb-5 pt-3">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('user') }}" class="btn btn-outline-dark ml-3">Cancel</a>
        </div>
        </form>
        </div>
        <!-- /.card -->
    </section>
    <script>
        function toggleSelectAll(selectAllCheckbox) {
            const roleCheckboxes = document.querySelectorAll('.role-checkbox');
            roleCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }
    </script>
@endsection
