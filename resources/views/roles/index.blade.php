@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi bi-shield-check me-2"></i>
                Roles
            </h1>
            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Add Role
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
                                <th>Role Name</th>
                                <th style="width: 120px;">Permissions</th>
                                <th style="width: 120px;">Users</th>
                                <th style="width: 180px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $i => $role)
                                <tr>
                                    <td class="text-muted">{{ $roles->firstItem() + $i }}</td>
                                    <td>
                                        <strong>{{ $role->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $role->permissions_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $role->users_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('roles.show', $role) }}" class="btn btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Delete this role?')">
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
                    <x-pagination :paginator="$roles" />
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
