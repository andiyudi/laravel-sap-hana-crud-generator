@extends('layouts.app')

@section('title', 'Edit ' . $menu->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="mb-2">
                <i class="bi bi-pencil me-2"></i>
                Edit {{ $menu->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dynamic.index', $menu->id) }}">{{ $menu->name }}</a>
                    </li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                @php
                    // Handle both array and object for record ID
                    $recordId = is_array($record) ? $record['id'] : $record->id;
                @endphp
                <form method="POST" action="{{ route('dynamic.update', [$menu->id, $recordId]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @foreach($menu->getFieldDefinitions() as $field)
                        @if(!in_array($field['name'], ['id', 'created_at', 'updated_at']))
                            <div class="mb-3">
                                <label for="{{ $field['name'] }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $field['name'])) }}
                                    @if(!$field['nullable'])
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @php
                                    // Handle both array and object
                                    $fieldValue = is_array($record)
                                        ? ($record[$field['name']] ?? '')
                                        : ($record->{$field['name']} ?? '');

                                    $isForeignKey = $menu->isForeignKey($field['name']);
                                @endphp

                                @if($isForeignKey && isset($relatedData[$field['name']]))
                                    {{-- Dropdown for foreign key --}}
                                    <select class="form-select @error($field['name']) is-invalid @enderror"
                                            id="{{ $field['name'] }}"
                                            name="{{ $field['name'] }}"
                                            {{ !$field['nullable'] ? 'required' : '' }}>
                                        <option value="">-- Select {{ ucwords(str_replace('_', ' ', str_replace('_id', '', $field['name']))) }} --</option>
                                        @foreach($relatedData[$field['name']]['data'] as $item)
                                            @php
                                                $itemId = is_array($item) ? $item['id'] : $item->id;
                                                $displayCol = $relatedData[$field['name']]['display_column'];
                                                $displayValue = is_array($item) ? $item[$displayCol] : $item->$displayCol;
                                            @endphp
                                            <option value="{{ $itemId }}" {{ old($field['name'], $fieldValue) == $itemId ? 'selected' : '' }}>
                                                {{ $displayValue }}
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif($field['type'] === 'textarea')
                                    <textarea class="form-control @error($field['name']) is-invalid @enderror"
                                              id="{{ $field['name'] }}"
                                              name="{{ $field['name'] }}"
                                              rows="4"
                                              {{ !$field['nullable'] ? 'required' : '' }}>{{ old($field['name'], $fieldValue) }}</textarea>
                                @elseif($field['type'] === 'checkbox')
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="{{ $field['name'] }}" value="0">
                                        <input class="form-check-input @error($field['name']) is-invalid @enderror"
                                               type="checkbox"
                                               id="{{ $field['name'] }}"
                                               name="{{ $field['name'] }}"
                                               value="1"
                                               {{ old($field['name'], $fieldValue) ? 'checked' : '' }}>
                                    </div>
                                @elseif($field['type'] === 'image')
                                    @if($fieldValue)
                                        <div class="mb-2">
                                            <label class="form-label text-muted small">Current Image:</label>
                                            <div>
                                                <img src="{{ asset('storage/' . $fieldValue) }}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                            </div>
                                        </div>
                                    @endif
                                    <input type="file"
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           id="{{ $field['name'] }}"
                                           name="{{ $field['name'] }}"
                                           accept="image/*"
                                           onchange="previewImage(this, '{{ $field['name'] }}')">
                                    <small class="text-muted">Accepted: JPEG, PNG, JPG, GIF, WEBP (Max: 2MB) - Leave empty to keep current image</small>
                                    <div id="{{ $field['name'] }}_preview" class="mt-2"></div>
                                @elseif($field['type'] === 'file')
                                    @if($fieldValue)
                                        <div class="mb-2">
                                            <label class="form-label text-muted small">Current File:</label>
                                            <div>
                                                <a href="{{ asset('storage/' . $fieldValue) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download me-1"></i>
                                                    {{ basename($fieldValue) }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    <input type="file"
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           id="{{ $field['name'] }}"
                                           name="{{ $field['name'] }}">
                                    <small class="text-muted">Max file size: 5MB - Leave empty to keep current file</small>
                                @else
                                    <input type="{{ $field['type'] }}"
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           id="{{ $field['name'] }}"
                                           name="{{ $field['name'] }}"
                                           value="{{ old($field['name'], $fieldValue) }}"
                                           @if($field['type'] === 'number')
                                               step="0.01"
                                           @endif
                                           {{ !$field['nullable'] ? 'required' : '' }}>
                                @endif

                                @error($field['name'])
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    @endforeach

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Update
                        </button>
                        <a href="{{ route('dynamic.index', $menu->id) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input, fieldName) {
    const preview = document.getElementById(fieldName + '_preview');
    preview.innerHTML = '';

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <label class="form-label text-muted small">New Image Preview:</label>
                <div>
                    <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                </div>
            `;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
