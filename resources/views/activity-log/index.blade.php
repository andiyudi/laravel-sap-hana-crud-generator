@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="mb-2">
                <i class="bi bi-clock-history me-2"></i>
                Activity Log
            </h1>
            <p class="text-muted">Track all changes made to your data</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('activity-log.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Action</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="bulk_deleted" {{ request('action') == 'bulk_deleted' ? 'selected' : '' }}>Bulk Deleted</option>
                    <option value="bulk_updated" {{ request('action') == 'bulk_updated' ? 'selected' : '' }}>Bulk Updated</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Table</label>
                <select name="menu_id" class="form-select">
                    <option value="">All Tables</option>
                    @foreach($menus as $menu)
                        <option value="{{ $menu->id }}" {{ request('menu_id') == $menu->id ? 'selected' : '' }}>
                            {{ $menu->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Activity List --}}
<div class="card shadow-sm">
    <div class="card-body">
        @if($activities->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px;">Date & Time</th>
                            <th style="width: 150px;">User</th>
                            <th style="width: 100px;">Action</th>
                            <th style="width: 150px;">Table</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                            <tr>
                                <td>
                                    <small class="text-muted">
                                        {{ $activity->created_at->format('d M Y') }}<br>
                                        {{ $activity->created_at->format('H:i:s') }}
                                    </small>
                                </td>
                                <td>
                                    @if($activity->causer)
                                        <strong>{{ $activity->causer->name }}</strong><br>
                                        <small class="text-muted">{{ $activity->causer->email }}</small>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($activity->description) {
                                            'created' => 'bg-success',
                                            'updated' => 'bg-warning',
                                            'deleted' => 'bg-danger',
                                            'bulk_deleted' => 'bg-danger',
                                            'bulk_updated' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $activity->description)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $props = $activity->properties;
                                        $menuName = $props['menu_name'] ?? $props['table'] ?? 'Unknown';
                                    @endphp
                                    <strong>{{ $menuName }}</strong>
                                </td>
                                <td>
                                    @php
                                        $props = $activity->properties;
                                    @endphp

                                    @if($activity->description === 'created')
                                        <span class="text-success">New record created</span>
                                        @if(isset($props['record_id']))
                                            <small class="text-muted">(ID: {{ $props['record_id'] }})</small>
                                        @endif
                                    @elseif($activity->description === 'updated')
                                        <span class="text-warning">Record updated</span>
                                        @if(isset($props['record_id']))
                                            <small class="text-muted">(ID: {{ $props['record_id'] }})</small>
                                        @endif
                                        @if(isset($props['old']) && isset($props['attributes']))
                                            @php
                                                $changes = array_diff_assoc($props['attributes'], $props['old']);
                                                $changeCount = count($changes);
                                            @endphp
                                            <br><small class="text-muted">{{ $changeCount }} field(s) changed</small>
                                        @endif
                                    @elseif($activity->description === 'deleted')
                                        <span class="text-danger">Record deleted</span>
                                        @if(isset($props['record_id']))
                                            <small class="text-muted">(ID: {{ $props['record_id'] }})</small>
                                        @endif
                                    @elseif($activity->description === 'bulk_deleted')
                                        <span class="text-danger">{{ $props['count'] ?? 0 }} records deleted</span>
                                    @elseif($activity->description === 'bulk_updated')
                                        <span class="text-info">{{ $props['count'] ?? 0 }} records updated</span>
                                        @if(isset($props['field']))
                                            <br><small class="text-muted">Field: {{ $props['field'] }} = {{ $props['value'] }}</small>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $activities->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No activity logs found.</p>
            </div>
        @endif
    </div>
</div>
@endsection
