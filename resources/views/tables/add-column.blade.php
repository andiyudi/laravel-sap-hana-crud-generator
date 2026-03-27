@extends('layouts.app')

@section('title', 'Add Column to Table')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="mb-2">
                <i class="bi bi-plus-square me-2"></i>
                Add Column to Existing Table
            </h1>
            <p class="text-muted">Add a new column to an existing database table</p>
        </div>

        {{-- Error Messages --}}
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Error:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('tables.add-column.store') }}" id="addColumnForm">
                    @csrf

                    <div class="mb-4">
                        <label for="table_name" class="form-label">Select Table <span class="text-danger">*</span></label>
                        <select class="form-select @error('table_name') is-invalid @enderror"
                                id="table_name" name="table_name" required>
                            <option value="">-- Select Table --</option>
                            @foreach($tables as $table)
                                <option value="{{ $table }}" {{ old('table_name') == $table ? 'selected' : '' }}>
                                    {{ $table }}
                                </option>
                            @endforeach
                        </select>
                        @error('table_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">New Column Details</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="column_name" class="form-label">Column Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('column_name') is-invalid @enderror"
                                   id="column_name" name="column_name" value="{{ old('column_name') }}"
                                   placeholder="e.g., icon, image, status" required>
                            @error('column_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Use lowercase letters and underscores only</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="column_type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('column_type') is-invalid @enderror"
                                    id="column_type" name="column_type" onchange="toggleLength()" required>
                                <option value="">-- Select Type --</option>
                                <option value="string" {{ old('column_type') == 'string' ? 'selected' : '' }}>String</option>
                                <option value="text" {{ old('column_type') == 'text' ? 'selected' : '' }}>Text</option>
                                <option value="integer" {{ old('column_type') == 'integer' ? 'selected' : '' }}>Integer</option>
                                <option value="decimal" {{ old('column_type') == 'decimal' ? 'selected' : '' }}>Decimal</option>
                                <option value="boolean" {{ old('column_type') == 'boolean' ? 'selected' : '' }}>Boolean</option>
                                <option value="date" {{ old('column_type') == 'date' ? 'selected' : '' }}>Date</option>
                                <option value="datetime" {{ old('column_type') == 'datetime' ? 'selected' : '' }}>Datetime</option>
                                <option value="timestamp" {{ old('column_type') == 'timestamp' ? 'selected' : '' }}>Timestamp</option>
                                <option value="file" {{ old('column_type') == 'file' ? 'selected' : '' }}>File</option>
                                <option value="image" {{ old('column_type') == 'image' ? 'selected' : '' }}>Image</option>
                            </select>
                            @error('column_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3" id="length-field" style="display: none;">
                            <label for="length" class="form-label">Length</label>
                            <input type="number" class="form-control @error('length') is-invalid @enderror"
                                   id="length" name="length" value="{{ old('length', 255) }}"
                                   placeholder="255" min="1">
                            @error('length')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">For string type</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <small><strong>Note:</strong> New columns will be added at the end of the table. HANA does not support column positioning.</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="default" class="form-label">Default Value</label>
                            <input type="text" class="form-control @error('default') is-invalid @enderror"
                                   id="default" name="default" value="{{ old('default') }}"
                                   placeholder="Optional">
                            @error('default')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" id="default-hint">
                                Examples: 0, 'text', true/false
                            </small>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="nullable"
                                       id="nullable" value="1" {{ old('nullable') ? 'checked' : '' }}>
                                <label class="form-check-label" for="nullable">
                                    Nullable
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="unique"
                                       id="unique" value="1" {{ old('unique') ? 'checked' : '' }}>
                                <label class="form-check-label" for="unique">
                                    Unique
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Important:</strong>
                        <ul class="mb-0 mt-2">
                            <li>For tables with existing data, new columns must be <strong>Nullable</strong> OR have a <strong>Default Value</strong></li>
                            <li>HANA database does not allow adding NOT NULL columns without default to tables with data</li>
                            <li>If you uncheck Nullable, make sure to provide a Default Value</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Add Column
                        </button>
                        <a href="{{ route('menus.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightbulb me-2"></i>
                    Important Notes
                </h5>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Select the table you want to modify</li>
                    <li>Enter the new column details</li>
                    <li>New columns will be added at the end of the table</li>
                    <li>After adding column, menu will be automatically refreshed</li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Common Use Cases
                </h5>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li><strong>Add image:</strong> Type = Image, Nullable = Yes</li>
                    <li><strong>Add file:</strong> Type = File, Nullable = Yes</li>
                    <li><strong>Add status:</strong> Type = Boolean, Default = 1</li>
                    <li><strong>Add description:</strong> Type = Text, Nullable = Yes</li>
                    <li><strong>Add price:</strong> Type = Decimal, Default = 0.00</li>
                    <li><strong>Add created date:</strong> Type = Date, Default = CURRENT_DATE</li>
                    <li><strong>Add timestamp:</strong> Type = Timestamp, Default = CURRENT_TIMESTAMP</li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar me-2"></i>
                    Date/Time Default Values
                </h5>
            </div>
            <div class="card-body">
                <p class="small mb-2"><strong>Database Functions (recommended):</strong></p>
                <ul class="small mb-2">
                    <li><code>CURRENT_DATE</code> - Current date (for Date type)</li>
                    <li><code>CURRENT_TIMESTAMP</code> - Current date and time (for Datetime/Timestamp)</li>
                </ul>
                <p class="small mb-2"><strong>Literal Values:</strong></p>
                <ul class="small mb-0">
                    <li><code>2024-01-01</code> - Specific date</li>
                    <li><code>2024-01-01 10:30:00</code> - Specific datetime</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleLength() {
    const type = document.getElementById('column_type').value;
    const lengthField = document.getElementById('length-field');
    const defaultHint = document.getElementById('default-hint');

    lengthField.style.display = type === 'string' ? 'block' : 'none';

    // Update default value hint based on column type
    const hints = {
        'string': 'Example: Hello World',
        'text': 'Example: Long text content',
        'integer': 'Example: 0, 100, -5',
        'decimal': 'Example: 0.00, 99.99, 1234.56',
        'boolean': 'Example: 1 (true) or 0 (false)',
        'date': 'Example: 2024-01-01 or CURRENT_DATE',
        'datetime': 'Example: 2024-01-01 10:30:00 or CURRENT_TIMESTAMP',
        'timestamp': 'Example: 2024-01-01 10:30:00 or CURRENT_TIMESTAMP',
        'file': 'Leave empty (files uploaded by users)',
        'image': 'Leave empty (images uploaded by users)'
    };

    if (defaultHint && hints[type]) {
        defaultHint.textContent = hints[type];
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleLength();
});
</script>
@endpush
@endsection
