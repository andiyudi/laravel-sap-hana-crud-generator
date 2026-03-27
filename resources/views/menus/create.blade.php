@extends('layouts.app')

@section('title', 'Create Menu')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="mb-2">
                <i class="bi bi-plus-circle me-2"></i>
                Create Menu
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('menus.index') }}">Menus</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">Error!</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('menus.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Menu Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Display name for the menu item</small>
                    </div>

                    <div class="mb-3">
                        <label for="table_name" class="form-label">Database Table <span class="text-danger">*</span></label>
                        <select class="form-select @error('table_name') is-invalid @enderror"
                                id="table_name" name="table_name" required>
                            <option value="">Select a table...</option>
                            @foreach($tables as $table)
                                <option value="{{ $table }}" {{ old('table_name') == $table ? 'selected' : '' }}>
                                    {{ $table }}
                                </option>
                            @endforeach
                        </select>
                        @error('table_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">The database table to manage</small>
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon</label>
                        <input type="text" class="form-control @error('icon') is-invalid @enderror"
                               id="icon" name="icon" value="{{ old('icon', 'bi-table') }}"
                               placeholder="bi-table">
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Bootstrap icon class (e.g., bi-box-seam, bi-people)</small>
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">Order</label>
                        <input type="number" class="form-control @error('order') is-invalid @enderror"
                               id="order" name="order" value="{{ old('order', 0) }}" min="0">
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Display order in sidebar (lower numbers appear first)</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active (show in sidebar)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Create Menu
                        </button>
                        <a href="{{ route('menus.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Help
                </h5>
            </div>
            <div class="card-body">
                <h6>How it works:</h6>
                <ol class="small">
                    <li>Select a database table</li>
                    <li>System auto-detects columns</li>
                    <li>Menu appears in sidebar</li>
                    <li>CRUD interface is generated</li>
                </ol>

                <h6 class="mt-3">Icon Examples:</h6>
                <ul class="small list-unstyled">
                    <li><i class="bi bi-box-seam"></i> bi-box-seam</li>
                    <li><i class="bi bi-people"></i> bi-people</li>
                    <li><i class="bi bi-cart"></i> bi-cart</li>
                    <li><i class="bi bi-file-text"></i> bi-file-text</li>
                </ul>

                <a href="https://icons.getbootstrap.com/" target="_blank" class="btn btn-sm btn-outline-primary">
                    Browse Icons
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
