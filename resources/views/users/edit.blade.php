@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>
                    Edit User
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

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
                                value="{{ old('name', $user->name) }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Username -->
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person-badge me-1"></i>
                                Username <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('username') is-invalid @enderror"
                                id="username"
                                name="username"
                                value="{{ old('username', $user->username) }}"
                                required
                            >
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
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
                                value="{{ old('email', $user->email) }}"
                                required
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
                                New Password
                            </label>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                placeholder="Leave blank to keep current"
                            >
                            <small class="text-muted">Leave blank if you don't want to change password</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="bi bi-lock-fill me-1"></i>
                                Confirm New Password
                            </label>
                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Repeat new password"
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
                                        {{ $user->hasRole($role->name) ? 'checked' : '' }}
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
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle me-2"></i>
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
