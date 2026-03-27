@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-person-plus me-2"></i>
                    Add New User
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <div class="row">
                        <!-- Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-person me-1"></i>
                                Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                placeholder="Enter full name"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>
                                Email <span class="text-danger">*</span>
                            </label>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                placeholder="user@example.com"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Password -->
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i>
                                Password <span class="text-danger">*</span>
                            </label>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                required
                                placeholder="Min. 8 characters"
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="bi bi-lock-fill me-1"></i>
                                Confirm Password <span class="text-danger">*</span>
                            </label>
                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                placeholder="Repeat password"
                            >
                        </div>
                    </div>

                    <!-- Roles -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-shield-check me-1"></i>
                            Roles
                        </label>
                        <div class="border rounded p-3">
                            @forelse($roles as $role)
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->name }}"
                                        id="role_{{ $role->id }}"
                                        {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No roles available.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
