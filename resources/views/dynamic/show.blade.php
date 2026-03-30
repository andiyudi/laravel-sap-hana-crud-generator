@extends('layouts.app')

@section('title', $menu->name . ' - Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">
                        <i class="bi {{ $menu->icon ?? 'bi-table' }} me-2"></i>
                        {{ $menu->name }} Details
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dynamic.index', $menu->id) }}">{{ $menu->name }}</a></li>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    @php
                        $menuSlug = strtolower(str_replace(' ', '_', $menu->name));
                        $canEdit = auth()->user()->hasRole('admin') || auth()->user()->can("{$menuSlug}.edit");
                    @endphp
                    @if($canEdit)
                        <a href="{{ route('dynamic.edit', [$menu->id, $recordId]) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-2"></i>
                            Edit
                        </a>
                    @endif
                    <a href="{{ route('dynamic.index', $menu->id) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Tabs Navigation --}}
        <ul class="nav nav-tabs mb-3" id="detailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
                    <i class="bi bi-info-circle me-2"></i>
                    Details
                </button>
            </li>
            @if(!empty($hasMany))
                @foreach($hasMany as $relation)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="{{ $relation['table'] }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $relation['table'] }}" type="button" role="tab">
                            <i class="bi bi-list-ul me-2"></i>
                            {{ $relation['label'] }}
                            <span class="badge bg-primary ms-1">{{ $relation['count'] }}</span>
                        </button>
                    </li>
                @endforeach
            @endif
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                    <i class="bi bi-clock-history me-2"></i>
                    History
                </button>
            </li>
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content" id="detailTabsContent">
            {{-- Details Tab --}}
            <div class="tab-pane fade show active" id="details" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                @foreach($fields as $field)
                                    @if(!in_array($field['name'], ['id', 'created_at', 'updated_at']))
                                        <tr>
                                            <th style="width: 30%;">{{ ucwords(str_replace('_', ' ', $field['name'])) }}</th>
                                            <td>
                                                @php
                                                    $value = is_array($record) ? ($record[$field['name']] ?? '-') : ($record->{$field['name']} ?? '-');

                                                    // Check if this is a foreign key with display value
                                                    $displayKey = $field['name'] . '_display';
                                                    $hasDisplay = is_array($record) ? isset($record[$displayKey]) : isset($record->$displayKey);

                                                    if ($hasDisplay) {
                                                        $displayValue = is_array($record) ? $record[$displayKey] : $record->$displayKey;
                                                        echo '<strong>' . ($displayValue ?: '-') . '</strong>';
                                                    } elseif ($field['type'] === 'checkbox') {
                                                        if ($value) {
                                                            echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>';
                                                        } else {
                                                            echo '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Inactive</span>';
                                                        }
                                                    } elseif ($field['type'] === 'image') {
                                                        if ($value && $value !== '-') {
                                                            echo '<img src="' . asset('storage/' . $value) . '" class="img-thumbnail" style="max-width: 200px;">';
                                                        } else {
                                                            echo '-';
                                                        }
                                                    } elseif ($field['type'] === 'file') {
                                                        if ($value && $value !== '-') {
                                                            echo '<a href="' . asset('storage/' . $value) . '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>Download File</a>';
                                                        } else {
                                                            echo '-';
                                                        }
                                                    } elseif (in_array($field['type'], ['date', 'datetime-local'])) {
                                                        echo $value !== '-' ? date('d M Y H:i', strtotime($value)) : '-';
                                                    } else {
                                                        echo $value;
                                                    }
                                                @endphp
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Related Records Tabs --}}
            @if(!empty($hasMany))
                @foreach($hasMany as $relation)
                    <div class="tab-pane fade" id="{{ $relation['table'] }}" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ $relation['label'] }} ({{ $relation['count'] }})</h5>
                                @php
                                    // Check permission for related menu
                                    $relatedMenu = \App\Models\Menu::find($relation['menu_id']);
                                    $relatedMenuSlug = strtolower(str_replace(' ', '_', $relatedMenu->name));
                                    $canCreateRelated = auth()->user()->hasRole('admin') || auth()->user()->can("{$relatedMenuSlug}.create");
                                @endphp
                                @if($canCreateRelated)
                                    <a href="{{ route('dynamic.create', $relation['menu_id']) }}?{{ $relation['foreign_key'] }}={{ $recordId }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Add {{ $relation['singular'] }}
                                    </a>
                                @endif
                            </div>
                            <div class="card-body">
                                @if($relation['count'] > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    @foreach($relation['display_fields'] as $displayField)
                                                        <th>{{ ucwords(str_replace('_', ' ', $displayField)) }}</th>
                                                    @endforeach
                                                    <th style="width: 100px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($relation['records'] as $relatedRecord)
                                                    <tr>
                                                        @foreach($relation['display_fields'] as $displayField)
                                                            <td>
                                                                @php
                                                                    $val = is_array($relatedRecord) ? ($relatedRecord[$displayField] ?? '-') : ($relatedRecord->$displayField ?? '-');
                                                                    echo strlen($val) > 50 ? substr($val, 0, 50) . '...' : $val;
                                                                @endphp
                                                            </td>
                                                        @endforeach
                                                        <td>
                                                            @php
                                                                $relId = is_array($relatedRecord) ? $relatedRecord['id'] : $relatedRecord->id;
                                                                $canEditRelated = auth()->user()->hasRole('admin') || auth()->user()->can("{$relatedMenuSlug}.edit");
                                                            @endphp
                                                            <a href="{{ route('dynamic.show', [$relation['menu_id'], $relId]) }}" class="btn btn-sm btn-outline-info" title="View">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            @if($canEditRelated)
                                                                <a href="{{ route('dynamic.edit', [$relation['menu_id'], $relId]) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2">No {{ strtolower($relation['label']) }} found.</p>
                                        @if($canCreateRelated)
                                            <a href="{{ route('dynamic.create', $relation['menu_id']) }}?{{ $relation['foreign_key'] }}={{ $recordId }}" class="btn btn-primary mt-2">
                                                <i class="bi bi-plus-circle me-2"></i>
                                                Add First {{ $relation['singular'] }}
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- History Tab --}}
            <div class="tab-pane fade" id="history" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Change History
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            // Get activity logs for this record
                            // Since HANA doesn't support JSON operations, we filter in PHP
                            $activities = \Spatie\Activitylog\Models\Activity::with('causer')
                                ->orderBy('created_at', 'desc')
                                ->get()
                                ->filter(function($activity) use ($menu, $recordId) {
                                    $props = $activity->properties;
                                    return isset($props['table']) &&
                                           $props['table'] === $menu->table_name &&
                                           isset($props['record_id']) &&
                                           $props['record_id'] == $recordId;
                                });
                        @endphp

                        @if($activities->count() > 0)
                            <div class="timeline">
                                @foreach($activities as $activity)
                                    <div class="timeline-item mb-4 pb-4 border-bottom">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <small class="text-muted">
                                                    {{ $activity->created_at->format('d M Y') }}<br>
                                                    {{ $activity->created_at->format('H:i:s') }}
                                                </small>
                                            </div>
                                            <div class="col-md-10">
                                                <div class="d-flex align-items-start mb-2">
                                                    @php
                                                        $iconClass = match($activity->description) {
                                                            'created' => 'bi-plus-circle text-success',
                                                            'updated' => 'bi-pencil-square text-warning',
                                                            'deleted' => 'bi-trash text-danger',
                                                            default => 'bi-circle text-secondary'
                                                        };
                                                        $badgeClass = match($activity->description) {
                                                            'created' => 'bg-success',
                                                            'updated' => 'bg-warning',
                                                            'deleted' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <i class="bi {{ $iconClass }} me-2" style="font-size: 1.5rem;"></i>
                                                    <div class="flex-grow-1">
                                                        <span class="badge {{ $badgeClass }} me-2">
                                                            {{ ucfirst($activity->description) }}
                                                        </span>
                                                        @if($activity->causer)
                                                            by <strong>{{ $activity->causer->name }}</strong>
                                                        @else
                                                            by <span class="text-muted">System</span>
                                                        @endif

                                                        @php
                                                            $props = $activity->properties;
                                                        @endphp

                                                        @if($activity->description === 'created')
                                                            <p class="mt-2 mb-0 text-muted">Record was created</p>

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
                                                                    <div class="mt-2">
                                                                        <small class="text-muted">Changed {{ count($changes) }} field(s):</small>
                                                                        <ul class="list-unstyled mt-1 mb-0">
                                                                            @foreach($changes as $field => $change)
                                                                                @if(!in_array($field, ['updated_at']))
                                                                                    <li class="mb-1">
                                                                                        <strong>{{ ucwords(str_replace('_', ' ', $field)) }}:</strong>
                                                                                        <span class="text-danger"><del>{{ $change['old'] ?: '(empty)' }}</del></span>
                                                                                        <i class="bi bi-arrow-right mx-1"></i>
                                                                                        <span class="text-success"><strong>{{ $change['new'] ?: '(empty)' }}</strong></span>
                                                                                    </li>
                                                                                @endif
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                            @endif

                                                        @elseif($activity->description === 'deleted')
                                                            <p class="mt-2 mb-0 text-danger">Record was deleted</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No history found for this record.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Record Information
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>ID:</strong> {{ $recordId }}</p>
                @php
                    $createdAt = is_array($record) ? ($record['created_at'] ?? null) : ($record->created_at ?? null);
                    $updatedAt = is_array($record) ? ($record['updated_at'] ?? null) : ($record->updated_at ?? null);
                @endphp
                @if($createdAt)
                    <p class="mb-2"><strong>Created:</strong> {{ date('d M Y H:i', strtotime($createdAt)) }}</p>
                @endif
                @if($updatedAt)
                    <p class="mb-0"><strong>Updated:</strong> {{ date('d M Y H:i', strtotime($updatedAt)) }}</p>
                @endif
            </div>
        </div>

        @if(!empty($hasMany))
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-diagram-3 me-2"></i>
                        Related Data Summary
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach($hasMany as $relation)
                            <li class="mb-2">
                                <i class="bi bi-arrow-right-circle me-2 text-primary"></i>
                                <strong>{{ $relation['count'] }}</strong> {{ $relation['label'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
