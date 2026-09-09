@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Create New Ticket</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Back to Tickets
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card fd-card p-4 shadow-sm mb-4">
            <h5 class="fw-bold mb-3 text-dark">Ticket Details</h5>
            <p class="text-muted small mb-4">Fill out the details below to open a new support incident or service request.</p>

            <form method="POST" action="{{ route('tickets.store') }}">
                @csrf

                <!-- Title -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold text-dark small">Ticket Title</label>
                    <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required placeholder="Describe the issue briefly...">
                    @error('title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Description of Findings -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold text-dark small">Description of Findings</label>
                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" rows="4" placeholder="Provide detailed findings or description of the issue...">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
                    <!-- Ticket Type -->
                    <div>
                        <label for="ticket_type_id" class="form-label fw-semibold text-dark small">Ticket Type</label>
                        <select id="ticket_type_id" class="form-select @error('ticket_type_id') is-invalid @enderror" name="ticket_type_id" required>
                            <option value="">Select Type</option>
                            @foreach($ticketTypes as $type)
                                <option value="{{ $type->id }}" {{ old('ticket_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('ticket_type_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Severity -->
                    <div>
                        <label for="priority_id" class="form-label fw-semibold text-dark small">Severity</label>
                        <select id="priority_id" class="form-select @error('priority_id') is-invalid @enderror" name="priority_id" required>
                            <option value="">Select Severity</option>
                            @foreach($severities as $severity)
                                <option value="{{ $severity->id }}" {{ old('priority_id') == $severity->id ? 'selected' : '' }}>{{ $severity->name }}</option>
                            @endforeach
                        </select>
                        @error('priority_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
                    <!-- Priority -->
                    <div>
                        <label for="priority_option_id" class="form-label fw-semibold text-dark small">Priority</label>
                        <select id="priority_option_id" class="form-select @error('priority_option_id') is-invalid @enderror" name="priority_option_id" required>
                            <option value="">Select Priority</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->id }}" {{ old('priority_option_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                            @endforeach
                        </select>
                        @error('priority_option_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-md-3 g-3 mb-3">
                    <!-- Category 1 -->
                    <div>
                        <label for="category_1_id" class="form-label fw-semibold text-dark small">Category 1</label>
                        <select id="category_1_id" class="form-select @error('category_1_id') is-invalid @enderror" name="category_1_id" required disabled>
                            <option value="">Select Category 1</option>
                        </select>
                        @error('category_1_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Category 2 -->
                    <div>
                        <label for="category_2_id" class="form-label fw-semibold text-dark small">Category 2</label>
                        <select id="category_2_id" class="form-select @error('category_2_id') is-invalid @enderror" name="category_2_id" disabled>
                            <option value="">Select Category 2</option>
                        </select>
                        @error('category_2_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Category 3 -->
                    <div>
                        <label for="category_3_id" class="form-label fw-semibold text-dark small">Category 3</label>
                        <select id="category_3_id" class="form-select @error('category_3_id') is-invalid @enderror" name="category_3_id" disabled>
                            <option value="">Select Category 3</option>
                        </select>
                        @error('category_3_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
                    <!-- Division -->
                    <div>
                        <label for="division_id" class="form-label fw-semibold text-dark small">Division</label>
                        <select id="division_id" class="form-select @error('division_id') is-invalid @enderror" name="division_id" required>
                            <option value="">Select Division</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                            @endforeach
                        </select>
                        @error('division_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div>
                        <label for="department_id" class="form-label fw-semibold text-dark small">Department</label>
                        <select id="department_id" class="form-select @error('department_id') is-invalid @enderror" name="department_id" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <!-- Submit and Cancel Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Open Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ticketTypeSelect = document.getElementById('ticket_type_id');
        const category1Select = document.getElementById('category_1_id');
        const category2Select = document.getElementById('category_2_id');
        const category3Select = document.getElementById('category_3_id');

        // Initial setup/load if values exist (e.g. on validation error)
        if (ticketTypeSelect.value) {
            loadCategories(ticketTypeSelect.value, null, category1Select, '{{ old('category_1_id') }}').then(() => {
                if (category1Select.value) {
                    loadCategories(null, category1Select.value, category2Select, '{{ old('category_2_id') }}').then(() => {
                        if (category2Select.value) {
                            loadCategories(null, category2Select.value, category3Select, '{{ old('category_3_id') }}');
                        }
                    });
                }
            });
        }

        // On Ticket Type Change
        ticketTypeSelect.addEventListener('change', function () {
            const ticketTypeId = this.value;
            resetSelect(category1Select, 'Category 1');
            resetSelect(category2Select, 'Category 2');
            resetSelect(category3Select, 'Category 3');

            if (ticketTypeId) {
                loadCategories(ticketTypeId, null, category1Select);
            }
        });

        // On Category 1 Change
        category1Select.addEventListener('change', function () {
            const category1Id = this.value;
            resetSelect(category2Select, 'Category 2');
            resetSelect(category3Select, 'Category 3');

            if (category1Id) {
                loadCategories(null, category1Id, category2Select);
            }
        });

        // On Category 2 Change
        category2Select.addEventListener('change', function () {
            const category2Id = this.value;
            resetSelect(category3Select, 'Category 3');

            if (category2Id) {
                loadCategories(null, category2Id, category3Select);
            }
        });

        function resetSelect(selectEl, label) {
            selectSelectDisabled(selectEl, true);
            selectEl.innerHTML = `<option value="">Select ${label}</option>`;
        }

        function selectSelectDisabled(selectEl, isDisabled) {
            selectEl.disabled = isDisabled;
            if (isDisabled) {
                selectEl.removeAttribute('required');
            } else {
                // Category 1 is required, Category 2 and 3 are optional
                if (selectEl.id === 'category_1_id') {
                    selectEl.setAttribute('required', 'required');
                }
            }
        }

        function loadCategories(ticketTypeId, parentId, selectEl, selectedValue = '') {
            let url = `/api/categories?`;
            if (ticketTypeId) {
                url += `ticket_type_id=${ticketTypeId}`;
            } else if (parentId) {
                url += `parent_id=${parentId}`;
            }

            return fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        selectSelectDisabled(selectEl, false);
                        let options = `<option value="">Select ${selectEl.id === 'category_1_id' ? 'Category 1' : (selectEl.id === 'category_2_id' ? 'Category 2' : 'Category 3')}</option>`;
                        data.forEach(item => {
                            const selected = selectedValue == item.id ? 'selected' : '';
                            options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });
                        selectEl.innerHTML = options;
                    } else {
                        resetSelect(selectEl, selectEl.id === 'category_1_id' ? 'Category 1' : (selectEl.id === 'category_2_id' ? 'Category 2' : 'Category 3'));
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                });
        }
    });
</script>
@endsection
