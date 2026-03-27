@extends('layouts.app')

@section('title', 'Permission Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-key me-2"></i>
                    Permission Details
                </h5>
                <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-sm btn-light">
                    <i class="bi bi-pencil me-1"></i>
                    Edit
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Name:</div>
                    <div class="col-md-8"><code>{{ $permission->name }}</code></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Assigned to Roles:</div>
                    <div class="col-md-8">
                        @forelse($permission->roles as $role)
                            <span class="badge bg-primary me-1 mb-1">{{ $role->name }}</span>
                        @empty
                            <span class="text-muted">Not assigned to any role</span>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
