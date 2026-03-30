@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="mb-2">
                <i class="bi bi-person-circle me-2"></i>
                My Profile
            </h1>
            <p class="text-muted">Manage your account settings</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        {{-- Profile Information --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person me-2"></i>
                    Profile Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $user->name) }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text"
                               class="form-control @error('username') is-invalid @enderror"
                               id="username"
                               name="username"
                               value="{{ old('username', $user->username) }}"
                               required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email', $user->email) }}"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <div>
                            @foreach($user->getRoleNames() as $role)
                                <span class="badge bg-primary">{{ ucfirst($role) }}</span>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>
                        Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        {{-- Change Password --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-key me-2"></i>
                    Change Password
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               id="current_password"
                               name="current_password"
                               required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password"
                               class="form-control"
                               id="password_confirmation"
                               name="password_confirmation"
                               required>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-lock me-2"></i>
                        Change Password
                    </button>
                </form>
            </div>
        </div>

        {{-- Account Information --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Account Information
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Member Since:</strong><br>
                    {{ $user->created_at->format('d M Y') }}
                </p>
                <p class="mb-0">
                    <strong>Last Updated:</strong><br>
                    {{ $user->updated_at->format('d M Y H:i') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
