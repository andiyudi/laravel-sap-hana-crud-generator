@extends('layouts.app')

@section('title', 'Add Role')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-shield-plus me-2"></i>
                    Add New Role
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('roles.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="bi bi-tag me-1"></i>
                            Role Name <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="e.g., Manager, Editor"
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-key me-1"></i>
                            Permissions
                        </label>
                        <div class="border rounded p-3">
                            @foreach($permissions as $group => $perms)
                                <div class="mb-3">
                                    <h6 class="text-primary">{{ ucfirst($group) }}</h6>
                                    <div class="row">
                                        @foreach($perms as $permission)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $permission->name }}"
                                                        id="perm_{{ $permission->id }}"
                                                    >
                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Create Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
