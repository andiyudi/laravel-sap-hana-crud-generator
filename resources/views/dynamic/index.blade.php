@extends('layouts.app')

@section('title', $menu->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi {{ $menu->icon ?? 'bi-table' }} me-2"></i>
                {{ $menu->name }}
            </h1>
            <div class="d-flex gap-2">
                @if(!$data->isEmpty())
                    <a href="{{ route('dynamic.export', array_merge(['menu' => $menu->id], request()->query())) }}"
                       class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-2"></i>
                        Export Excel
                    </a>
                @endif
                <a href="{{ route('dynamic.create', $menu->id) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add New
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                {{-- Search & Filter Bar --}}
                <form method="GET" action="{{ route('dynamic.index', $menu->id) }}" class="mb-4">
                    <div class="row g-3">
                        {{-- Search Box --}}
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="Search..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>
                                    Search
                                </button>
                                @if(request()->hasAny(['search', 'filter', 'sort_by']))
                                    <a href="{{ route('dynamic.index', $menu->id) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Clear
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Preserve sort parameters --}}
                    @if(request('sort_by'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                        <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                    @endif
                </form>

                @if($data->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3">No records found.</p>
                        @if(request()->hasAny(['search', 'filter']))
                            <p class="text-muted">Try adjusting your search or filters.</p>
                            <a href="{{ route('dynamic.index', $menu->id) }}" class="btn btn-outline-primary mt-2">
                                <i class="bi bi-x-circle me-2"></i>
                                Clear Filters
                            </a>
                        @else
                            <a href="{{ route('dynamic.create', $menu->id) }}" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-circle me-2"></i>
                                Add First Record
                            </a>
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    @php
                                        $fields = $menu->getFieldDefinitions();
                                        // Filter only fields marked for display in list
                                        $displayFields = array_filter($fields, function($field) {
                                            return $field['display_in_list'] ?? false;
                                        });
                                        // If no fields marked, fallback to first 6 non-system columns
                                        if (empty($displayFields)) {
                                            $displayFields = array_filter($fields, function($field) {
                                                return !in_array($field['name'], ['id', 'created_at', 'updated_at']);
                                            });
                                            $displayFields = array_slice($displayFields, 0, 6);
                                        }
                                        $currentSort = request('sort_by');
                                        $currentOrder = request('sort_order', 'desc');
                                    @endphp
                                    @foreach($displayFields as $field)
                                        <th>
                                            <a href="{{ route('dynamic.index', array_merge(request()->query(), [
                                                'menu' => $menu->id,
                                                'sort_by' => $field['name'],
                                                'sort_order' => ($currentSort === $field['name'] && $currentOrder === 'asc') ? 'desc' : 'asc'
                                            ])) }}"
                                               class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                                <span>{{ ucwords(str_replace('_', ' ', $field['name'])) }}</span>
                                                @if($currentSort === $field['name'])
                                                    <i class="bi bi-arrow-{{ $currentOrder === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                                @else
                                                    <i class="bi bi-arrow-down-up ms-1 text-muted opacity-50"></i>
                                                @endif
                                            </a>
                                        </th>
                                    @endforeach
                                    <th style="width: 150px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $record)
                                    <tr>
                                        @foreach($displayFields as $field)
                                            <td>
                                                @php
                                                    // Handle both array and object
                                                    $value = is_array($record)
                                                        ? ($record[$field['name']] ?? '-')
                                                        : ($record->{$field['name']} ?? '-');

                                                    // Check if this is a foreign key with display value
                                                    $displayKey = $field['name'] . '_display';
                                                    $hasDisplay = is_array($record)
                                                        ? isset($record[$displayKey])
                                                        : isset($record->$displayKey);

                                                    if ($hasDisplay) {
                                                        // Show related data instead of ID
                                                        $displayValue = is_array($record) ? $record[$displayKey] : $record->$displayKey;
                                                        echo $displayValue ?: '-';
                                                    } elseif ($field['type'] === 'checkbox') {
                                                        echo $value ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-danger"></i>';
                                                    } elseif ($field['type'] === 'image') {
                                                        if ($value && $value !== '-') {
                                                            echo '<img src="' . asset('storage/' . $value) . '" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">';
                                                        } else {
                                                            echo '-';
                                                        }
                                                    } elseif ($field['type'] === 'file') {
                                                        if ($value && $value !== '-') {
                                                            echo '<a href="' . asset('storage/' . $value) . '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>';
                                                        } else {
                                                            echo '-';
                                                        }
                                                    } elseif (in_array($field['type'], ['date', 'datetime-local'])) {
                                                        echo $value !== '-' ? date('d M Y', strtotime($value)) : '-';
                                                    } else {
                                                        echo strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                                                    }
                                                @endphp
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @php
                                                    $recordId = is_array($record) ? $record['id'] : $record->id;
                                                @endphp
                                                <a href="{{ route('dynamic.edit', [$menu->id, $recordId]) }}" class="btn btn-outline-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('dynamic.destroy', [$menu->id, $recordId]) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this record?')">
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
                        <x-pagination :paginator="$data" />
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
