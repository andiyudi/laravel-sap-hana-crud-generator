@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi bi-key me-2"></i>
                Permissions
            </h1>
            <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Add Permission
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Permission Name</th>
                                <th style="width: 120px;">Roles</th>
                                <th style="width: 180px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $i => $permission)
                                <tr>
                                    <td class="text-muted">{{ $permissions->firstItem() + $i }}</td>
                                    <td>
                                        <code>{{ $permission->name }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $permission->roles_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('permissions.show', $permission) }}" class="btn btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('permissions.destroy', $permission) }}" class="d-inline" onsubmit="return confirm('Delete this permission?')">
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
                    <x-pagination :paginator="$permissions" />
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
