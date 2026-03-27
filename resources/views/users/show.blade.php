@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-person me-2"></i>
                    User Details
                </h5>
                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-light">
                    <i class="bi bi-pencil me-1"></i>
                    Edit
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Name:</div>
                    <div class="col-md-8">{{ $user->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Email:</div>
                    <div class="col-md-8">{{ $user->email }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Roles:</div>
                    <div class="col-md-8">
                        @forelse($user->roles as $role)
                            <span class="badge bg-primary me-1">{{ $role->name }}</span>
                        @empty
                            <span class="text-muted">No roles assigned</span>
                        @endforelse
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Permissions:</div>
                    <div class="col-md-8">
                        @php
                            $permissions = $user->getAllPermissions();
                        @endphp
                        @forelse($permissions as $permission)
                            <span class="badge bg-secondary me-1 mb-1">{{ $permission->name }}</span>
                        @empty
                            <span class="text-muted">No permissions</span>
                        @endforelse
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Created:</div>
                    <div class="col-md-8">{{ $user->created_at->format('d M Y H:i') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-4 fw-bold">Last Updated:</div>
                    <div class="col-md-8">{{ $user->updated_at->format('d M Y H:i') }}</div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
