@extends('layouts.app')

@section('title', 'Record History')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="mb-2">
                <i class="bi bi-clock-history me-2"></i>
                Record History
            </h1>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        @if($activities->count() > 0)
            <div class="timeline">
                @foreach($activities as $activity)
                    <div class="timeline-item mb-4">
                        <div class="row">
                            <div class="col-md-2 text-end">
                                <small class="text-muted">
                                    {{ $activity->created_at->format('d M Y') }}<br>
                                    {{ $activity->created_at->format('H:i:s') }}
                                </small>
                            </div>
                            <div class="col-md-1 text-center">
                                @php
                                    $iconClass = match($activity->description) {
                                        'created' => 'bi-plus-circle text-success',
                                        'updated' => 'bi-pencil-square text-warning',
                                        'deleted' => 'bi-trash text-danger',
                                        default => 'bi-circle text-secondary'
                                    };
                                @endphp
                                <i class="bi {{ $iconClass }}" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="col-md-9">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                @php
                                                    $badgeClass = match($activity->description) {
                                                        'created' => 'bg-success',
                                                        'updated' => 'bg-warning',
                                                        'deleted' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} me-2">
                                                    {{ ucfirst($activity->description) }}
                                                </span>
                                                @if($activity->causer)
                                                    <strong>{{ $activity->causer->name }}</strong>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </div>
                                        </div>

                                        @php
                                            $props = $activity->properties;
                                        @endphp

                                        @if($activity->description === 'created')
                                            <p class="mb-2">Record was created</p>
                                            @if(isset($props['attributes']))
                                                <details>
                                                    <summary class="text-primary" style="cursor: pointer;">View created data</summary>
                                                    <div class="mt-2">
                                                        <table class="table table-sm table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Field</th>
                                                                    <th>Value</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($props['attributes'] as $key => $value)
                                                                    @if(!in_array($key, ['created_at', 'updated_at']))
                                                                        <tr>
                                                                            <td><strong>{{ ucwords(str_replace('_', ' ', $key)) }}</strong></td>
                                                                            <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </details>
                                            @endif

                                        @elseif($activity->description === 'updated')
                                            @if(isset($props['old']) && isset($props['attributes']))
                                                @php
                                                    $changes = [];
                                                    foreach($props['attributes'] as $key => $newValue) {
                                                        if(isset($props['old'][$key]) && $props['old'][$key] != $newValue) {
                                                            $changes[$key] = [
                                                                'old' => $props['old'][$key],
                                                                'new' => $newValue
                                                            ];
                                                        }
                                                    }
                                                @endphp

                                                @if(count($changes) > 0)
                                                    <p class="mb-2">{{ count($changes) }} field(s) were changed:</p>
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Field</th>
                                                                <th>Old Value</th>
                                                                <th></th>
                                                                <th>New Value</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($changes as $field => $change)
                                                                @if(!in_array($field, ['updated_at']))
                                                                    <tr>
                                                                        <td><strong>{{ ucwords(str_replace('_', ' ', $field)) }}</strong></td>
                                                                        <td class="text-danger">
                                                                            <del>{{ is_array($change['old']) ? json_encode($change['old']) : ($change['old'] ?: '(empty)') }}</del>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <i class="bi bi-arrow-right"></i>
                                                                        </td>
                                                                        <td class="text-success">
                                                                            <strong>{{ is_array($change['new']) ? json_encode($change['new']) : ($change['new'] ?: '(empty)') }}</strong>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    <p class="text-muted">No field changes detected</p>
                                                @endif
                                            @else
                                                <p class="mb-0">Record was updated</p>
                                            @endif

                                        @elseif($activity->description === 'deleted')
                                            <p class="mb-2 text-danger">Record was deleted</p>
                                            @if(isset($props['old']))
                                                <details>
                                                    <summary class="text-primary" style="cursor: pointer;">View deleted data</summary>
                                                    <div class="mt-2">
                                                        <table class="table table-sm table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Field</th>
                                                                    <th>Value</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($props['old'] as $key => $value)
                                                                    @if(!in_array($key, ['created_at', 'updated_at']))
                                                                        <tr>
                                                                            <td><strong>{{ ucwords(str_replace('_', ' ', $key)) }}</strong></td>
                                                                            <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </details>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No history found for this record.</p>
            </div>
        @endif
    </div>
</div>

<style>
.timeline-item {
    position: relative;
}
.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 50%;
    top: 3rem;
    bottom: -2rem;
    width: 2px;
    background: #dee2e6;
    transform: translateX(-50%);
}
@media (min-width: 768px) {
    .timeline-item:not(:last-child)::after {
        left: calc(16.666% + 50%);
    }
}
</style>
@endsection
