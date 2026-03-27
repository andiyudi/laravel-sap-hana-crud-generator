@extends('layouts.app')

@section('title', 'Role Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-shield-check me-2"></i>
                    Role Details
                </h5>
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-light">
                    <i class="bi bi-pencil me-1"></i>
                    Edit
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Role Name:</div>
                    <div class="col-md-8">{{ $role->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Permissions:</div>
                    <div class="col-md-8">
                        @forelse($role->permissions as $permission)
                            <span class="badge bg-secondary me-1 mb-1">{{ $permission->name }}</span>
                        @empty
                            <span class="text-muted">No permissions assigned</span>
                        @endforelse
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Users:</div>
                    <div class="col-md-8">
                        @forelse($role->users as $user)
                            <span class="badge bg-primary me-1 mb-1">{{ $user->name }}</span>
                        @empty
                            <span class="text-muted">No users assigned</span>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
