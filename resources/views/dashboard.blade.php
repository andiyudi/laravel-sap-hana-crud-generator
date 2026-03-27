@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-speedometer2 me-2"></i>
            Dashboard
        </h1>
    </div>
</div>

<div class="row g-4">
    <!-- Welcome Card -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-person-check text-primary me-2"></i>
                    Selamat Datang!
                </h5>
                <p class="card-text text-muted mb-3">
                    Halo, <strong>{{ auth()->user()->name }}</strong>! Anda berhasil login ke sistem.
                </p>
                <p class="card-text">
                    <small class="text-muted">
                        <i class="bi bi-envelope me-1"></i>
                        {{ auth()->user()->email }}
                    </small>
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-lightning text-warning me-2"></i>
                    Quick Actions
                </h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('menus.index') }}" class="btn btn-primary">
                        <i class="bi bi-menu-button-wide me-2"></i>
                        Menu Management
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-info">
                        <i class="bi bi-people me-2"></i>
                        Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-4">
        <div class="card shadow-sm text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Dynamic Menus</h6>
                        <h2 class="card-title mb-0">{{ \App\Models\Menu::count() }}</h2>
                    </div>
                    <i class="bi bi-menu-button-wide fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Total Users</h6>
                        <h2 class="card-title mb-0">{{ \App\Models\User::count() }}</h2>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Database</h6>
                        <h5 class="card-title mb-0">SAP HANA</h5>
                    </div>
                    <i class="bi bi-database fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
