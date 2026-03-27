@extends('layouts.app')

@section('title', 'Create Table')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="mb-2">
                <i class="bi bi-table me-2"></i>
                Create Database Table
            </h1>
            <p class="text-muted">Create a new database table with custom fields</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('tables.store') }}" id="tableForm">
                    @csrf

                    <div class="mb-4">
                        <label for="table_name" class="form-label">Table Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('table_name') is-invalid @enderror"
                               id="table_name" name="table_name" value="{{ old('table_name') }}"
                               placeholder="e.g., products, categories, orders" required>
                        @error('table_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Use lowercase letters and underscores only (e.g., my_table)</small>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Table Fields</h5>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addField()">
                            <i class="bi bi-plus-circle me-1"></i>
                            Add Field
                        </button>
                    </div>

                    <div id="fieldsContainer">
                        <!-- Fields will be added here dynamically -->
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> Fields <code>id</code>, <code>created_at</code>, and <code>updated_at</code> will be added automatically.
                    </div>

                    @error('fields')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Create Table
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
                    Quick Guide
                </h5>
            </div>
            <div class="card-body">
                <h6>Steps:</h6>
                <ol class="small">
                    <li>Enter table name</li>
                    <li>Add fields with types</li>
                    <li>Set field properties</li>
                    <li>Create table</li>
                    <li>Create menu for the table</li>
                </ol>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>
                    Field Types
                </h5>
            </div>
            <div class="card-body">
                <ul class="small list-unstyled">
                    <li><strong>String:</strong> Short text (max 255 chars)</li>
                    <li><strong>Text:</strong> Long text</li>
                    <li><strong>Integer:</strong> Whole numbers</li>
                    <li><strong>Decimal:</strong> Numbers with decimals</li>
                    <li><strong>Boolean:</strong> True/False</li>
                    <li><strong>Date:</strong> Date only</li>
                    <li><strong>Datetime:</strong> Date and time</li>
                    <li><strong>Timestamp:</strong> Auto timestamp</li>
                    <li><strong>File:</strong> File upload (max 5MB)</li>
                    <li><strong>Image:</strong> Image upload (max 2MB)</li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar me-2"></i>
                    Date/Time Defaults
                </h5>
            </div>
            <div class="card-body">
                <p class="small mb-2"><strong>Functions:</strong></p>
                <ul class="small mb-2">
                    <li><code>CURRENT_DATE</code> - Today's date</li>
                    <li><code>CURRENT_TIMESTAMP</code> - Current date & time</li>
                </ul>
                <p class="small mb-2"><strong>Literal:</strong></p>
                <ul class="small mb-0">
                    <li><code>2024-01-01</code></li>
                    <li><code>2024-01-01 10:30:00</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let fieldIndex = 0;

function addField() {
    const container = document.getElementById('fieldsContainer');
    const fieldHtml = `
        <div class="card mb-3 field-card" id="field-${fieldIndex}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Field #${fieldIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeField(${fieldIndex})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Field Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="fields[${fieldIndex}][name]"
                               placeholder="e.g., name, price, description" required>
                        <small class="text-muted">Lowercase, underscores only</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="fields[${fieldIndex}][type]" onchange="toggleLength(${fieldIndex}, this.value)" required>
                            <option value="string">String</option>
                            <option value="text">Text</option>
                            <option value="integer">Integer</option>
                            <option value="decimal">Decimal</option>
                            <option value="boolean">Boolean</option>
                            <option value="date">Date</option>
                            <option value="datetime">Datetime</option>
                            <option value="timestamp">Timestamp</option>
                            <option value="file">File</option>
                            <option value="image">Image</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3" id="length-${fieldIndex}">
                        <label class="form-label">Length</label>
                        <input type="number" class="form-control" name="fields[${fieldIndex}][length]"
                               placeholder="255" min="1">
                        <small class="text-muted">For string type</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Default Value</label>
                        <input type="text" class="form-control field-default" name="fields[${fieldIndex}][default]"
                               placeholder="Optional" data-field-index="${fieldIndex}">
                        <small class="text-muted default-hint-${fieldIndex}">Examples: 0, 'text', true/false</small>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="fields[${fieldIndex}][nullable]"
                                   id="nullable-${fieldIndex}" value="1">
                            <label class="form-check-label" for="nullable-${fieldIndex}">
                                Nullable
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="fields[${fieldIndex}][unique]"
                                   id="unique-${fieldIndex}" value="1">
                            <label class="form-check-label" for="unique-${fieldIndex}">
                                Unique
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', fieldHtml);
    fieldIndex++;
}

function removeField(index) {
    const field = document.getElementById(`field-${index}`);
    if (field) {
        field.remove();
    }
}

function toggleLength(index, type) {
    const lengthField = document.getElementById(`length-${index}`);
    const hintElement = document.querySelector(`.default-hint-${index}`);

    if (lengthField) {
        lengthField.style.display = type === 'string' ? 'block' : 'none';
    }

    // Update default value hint based on field type
    if (hintElement) {
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

        hintElement.textContent = hints[type] || 'Examples: 0, text, true/false';
    }
}

// Add first field on page load
document.addEventListener('DOMContentLoaded', function() {
    addField();
});
</script>
@endpush
@endsection
