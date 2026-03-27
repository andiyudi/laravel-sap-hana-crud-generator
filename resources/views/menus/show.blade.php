@extends('layouts.app')

@section('title', 'Menu Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="mb-2">
                <i class="bi bi-eye me-2"></i>
                Menu Details
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('menus.index') }}">Menus</a></li>
                    <li class="breadcrumb-item active">{{ $menu->name }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Menu Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 150px;">Name:</th>
                        <td>{{ $menu->name }}</td>
                    </tr>
                    <tr>
                        <th>Table:</th>
                        <td><code>{{ $menu->table_name }}</code></td>
                    </tr>
                    <tr>
                        <th>Icon:</th>
                        <td>
                            <i class="bi {{ $menu->icon ?? 'bi-table' }} fs-5 me-2"></i>
                            <code>{{ $menu->icon ?? 'bi-table' }}</code>
                        </td>
                    </tr>
                    <tr>
                        <th>Order:</th>
                        <td>{{ $menu->order ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($menu->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $menu->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Updated:</th>
                        <td>{{ $menu->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('dynamic.index', $menu->id) }}" class="btn btn-primary">
                        <i class="bi bi-table me-2"></i>
                        View Data
                    </a>
                    <a href="{{ route('menus.edit', $menu) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-2"></i>
                        Edit
                    </a>
                    <a href="{{ route('menus.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-columns me-2"></i>
                    Field Definitions ({{ count($menu->getFieldDefinitions()) }})
                </h5>
            </div>
            <div class="card-body">
                @if(empty($menu->getFieldDefinitions()))
                    <p class="text-muted">No field definitions found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Field Name</th>
                                    <th>Type</th>
                                    <th>Nullable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($menu->getFieldDefinitions() as $field)
                                    <tr>
                                        <td><code>{{ $field['name'] }}</code></td>
                                        <td>
                                            <span class="badge bg-info">{{ $field['type'] }}</span>
                                        </td>
                                        <td>
                                            @if($field['nullable'])
                                                <i class="bi bi-check-circle text-success"></i>
                                            @else
                                                <i class="bi bi-x-circle text-danger"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
