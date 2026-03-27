@extends('layouts.app')

@section('title', 'Edit Menu')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="mb-2">
                <i class="bi bi-pencil me-2"></i>
                Edit Menu
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('menus.index') }}">Menus</a></li>
                    <li class="breadcrumb-item active">Edit: {{ $menu->name }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('menus.update', $menu) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Menu Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $menu->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="table_name" class="form-label">Database Table <span class="text-danger">*</span></label>
                        <select class="form-select @error('table_name') is-invalid @enderror"
                                id="table_name" name="table_name" required>
                            <option value="">Select a table...</option>
                            @foreach($tables as $table)
                                <option value="{{ $table }}" {{ old('table_name', $menu->table_name) == $table ? 'selected' : '' }}>
                                    {{ $table }}
                                </option>
                            @endforeach
                        </select>
                        @error('table_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon</label>
                        <input type="text" class="form-control @error('icon') is-invalid @enderror"
                               id="icon" name="icon" value="{{ old('icon', $menu->icon) }}">
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">Order</label>
                        <input type="number" class="form-control @error('order') is-invalid @enderror"
                               id="order" name="order" value="{{ old('order', $menu->order) }}" min="0">
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $menu->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active (show in sidebar)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Update Menu
                        </button>
                        <a href="{{ route('menus.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Display Columns Configuration --}}
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-eye me-2"></i>
                    Display Columns in List
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Select which columns to display in the list view and drag to reorder (maximum 6 columns recommended)
                </p>

                <form method="POST" action="{{ route('menus.update-display-columns', $menu) }}" id="displayColumnsForm">
                    @csrf
                    @method('PUT')

                    <div id="sortable-columns" class="list-group mb-3">
                        @php
                            $fields = $menu->getFieldDefinitions();
                            // Sort fields: display_in_list first, then by original order
                            usort($fields, function($a, $b) {
                                $aDisplay = $a['display_in_list'] ?? false;
                                $bDisplay = $b['display_in_list'] ?? false;
                                if ($aDisplay === $bDisplay) return 0;
                                return $aDisplay ? -1 : 1;
                            });
                        @endphp
                        @foreach($fields as $index => $field)
                            <div class="list-group-item d-flex align-items-center" data-field-name="{{ $field['name'] }}">
                                <i class="bi bi-grip-vertical text-muted me-3" style="cursor: grab;"></i>
                                <div class="form-check flex-grow-1">
                                    <input class="form-check-input column-checkbox" type="checkbox"
                                           name="display_columns[]"
                                           value="{{ $field['name'] }}"
                                           id="display_{{ $index }}"
                                           {{ ($field['display_in_list'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="display_{{ $index }}">
                                        <strong>{{ $field['name'] }}</strong>
                                        <span class="badge bg-secondary ms-1">{{ $field['type'] }}</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Tips:</strong> Drag the grip icon to reorder columns. Only checked columns will be displayed in the list.
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Save Display Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortableList = document.getElementById('sortable-columns');

    if (sortableList) {
        new Sortable(sortableList, {
            animation: 150,
            handle: '.bi-grip-vertical',
            ghostClass: 'bg-light',
            dragClass: 'opacity-50',
            onEnd: function() {
                console.log('Order changed');
            }
        });
    }
});
</script>
@endpush
@endsection
