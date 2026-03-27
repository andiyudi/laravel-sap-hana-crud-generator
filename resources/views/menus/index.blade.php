@extends('layouts.app')

@section('title', 'Menus')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                Menu Management
            </h1>
            <a href="{{ route('menus.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Create Menu
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                @if($menus->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3">No menus found. Create your first menu to get started.</p>
                        <a href="{{ route('menus.create') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle me-2"></i>
                            Create Menu
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">Order</th>
                                    <th>Name</th>
                                    <th>Table</th>
                                    <th>Icon</th>
                                    <th style="width: 100px;">Status</th>
                                    <th style="width: 180px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($menus as $menu)
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $menu->order ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $menu->name }}</strong>
                                        </td>
                                        <td>
                                            <code>{{ $menu->table_name }}</code>
                                        </td>
                                        <td>
                                            <i class="bi {{ $menu->icon ?? 'bi-table' }} fs-5"></i>
                                        </td>
                                        <td>
                                            @if($menu->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('dynamic.index', $menu->id) }}" class="btn btn-outline-primary" title="View Data">
                                                    <i class="bi bi-table"></i>
                                                </a>
                                                <a href="{{ route('menus.show', $menu) }}" class="btn btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('menus.edit', $menu) }}" class="btn btn-outline-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('menus.destroy', $menu) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this menu?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <x-pagination :paginator="$menus" />
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
