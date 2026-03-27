@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi bi-people me-2"></i>
                Users
            </h1>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Add User
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                @if($users->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3">No users found.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th style="width: 150px;">Created</th>
                                    <th style="width: 180px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $i => $user)
                                    <tr>
                                        <td class="text-muted">{{ $users->firstItem() + $i }}</td>
                                        <td>
                                            <strong>{{ $user->name }}</strong>
                                            @if($user->id === auth()->id())
                                                <span class="badge bg-info ms-1">You</span>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="bi bi-envelope me-1 text-muted"></i>
                                            {{ $user->email }}
                                        </td>
                                        <td>
                                            @forelse($user->roles as $role)
                                                <span class="badge bg-primary">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-muted">-</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $user->created_at->format('d M Y') }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('users.show', $user) }}" class="btn btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($user->id !== auth()->id())
                                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <x-pagination :paginator="$users" />
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
