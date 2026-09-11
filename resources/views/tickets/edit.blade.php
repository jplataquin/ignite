@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Edit Ticket: {{ $ticket->ticket_number }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Back to Ticket
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card fd-card p-4 shadow-sm mb-4">
            <h5 class="fw-bold mb-3 text-dark">Ticket Details</h5>
            <p class="text-muted small mb-4">Update the details of this support incident or service request.</p>

            <form method="POST" action="{{ route('tickets.update', $ticket) }}">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold text-dark small">Ticket Title</label>
                    <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $ticket->title) }}" required placeholder="Describe the issue briefly...">
                    @error('title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold text-dark small">Description</label>
                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" rows="6" placeholder="Provide detailed findings or description of the issue..." required>{{ old('description', $ticket->description) }}</textarea>
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
                                <option value="{{ $type->id }}" {{ old('ticket_type_id', $ticket->ticket_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('ticket_type_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div>
                        <label for="priority_option_id" class="form-label fw-semibold text-dark small">Priority</label>
                        <select id="priority_option_id" class="form-select @error('priority_option_id') is-invalid @enderror" name="priority_option_id" required>
                            <option value="">Select Priority</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->id }}" {{ old('priority_option_id', $ticket->priority_option_id) == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
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

                @php
                    $oldIntendedUser = null;
                    $intendedUserId = old('to_user_id', $ticket->to_user_id);
                    if ($intendedUserId) {
                        $oldIntendedUser = $users->firstWhere('id', $intendedUserId);
                    }
                @endphp

                <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
                    <!-- Division -->
                    <div>
                        <label for="division_id" class="form-label fw-semibold text-dark small">Division</label>
                        <select id="division_id" class="form-select @error('division_id') is-invalid @enderror" name="division_id" required>
                            <option value="">Select Division</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id', $ticket->division_id) == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
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
                                <option value="{{ $department->id }}" {{ old('department_id', $ticket->department_id) == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Intended User (Optional) - Autocomplete -->
                    <div class="position-relative">
                        <label for="user_search" class="form-label fw-semibold text-dark small">Intended User (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small py-1 px-2.5" style="border-right: 0;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" id="user_search" class="form-control @error('to_user_id') is-invalid @enderror" placeholder="Type to search users..." autocomplete="off" value="{{ $oldIntendedUser ? $oldIntendedUser->name : '' }}" style="border-left: 0; border-top-right-radius: 0.375rem; border-bottom-right-radius: 0.375rem;">
                            <input type="hidden" id="to_user_id" name="to_user_id" value="{{ $intendedUserId }}">
                            @error('to_user_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <ul id="autocomplete-results" class="dropdown-menu w-100 shadow border-0 py-0" style="max-height: 250px; overflow-y: auto; z-index: 1000; font-size: 0.9rem; position: absolute; top: 100%; left: 0; display: none;">
                        </ul>
                    </div>
                </div>

                <!-- Submit and Cancel Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Update Ticket</button>
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

        // Initial setup/load using pre-selected values
        const currentTicketTypeId = '{{ old('ticket_type_id', $ticket->ticket_type_id) }}';
        const currentCategory1Id = '{{ old('category_1_id', $ticket->category_1_id) }}';
        const currentCategory2Id = '{{ old('category_2_id', $ticket->category_2_id) }}';
        const currentCategory3Id = '{{ old('category_3_id', $ticket->category_3_id) }}';

        if (currentTicketTypeId) {
            loadCategories(currentTicketTypeId, null, category1Select, currentCategory1Id).then(() => {
                if (category1Select.value) {
                    loadCategories(null, category1Select.value, category2Select, currentCategory2Id).then(() => {
                        if (category2Select.value) {
                            loadCategories(null, category2Select.value, category3Select, currentCategory3Id);
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

        // --- AUTOCOMPLETE INTENDED USER LOGIC ---
        const divisionSelect = document.getElementById('division_id');
        const departmentSelect = document.getElementById('department_id');
        const userSearchInput = document.getElementById('user_search');
        const toUserIdInput = document.getElementById('to_user_id');
        const autocompleteResults = document.getElementById('autocomplete-results');

        let allUsers = []; // Dynamic, loaded when division is selected

        function renderAutocomplete() {
            const divisionId = divisionSelect.value;
            if (!divisionId) {
                autocompleteResults.innerHTML = '<li class="dropdown-item text-muted disabled py-2" style="min-height: auto;">Please select a division first</li>';
                autocompleteResults.style.display = 'block';
                return;
            }

            const query = userSearchInput.value.trim().toLowerCase();
            if (!query) {
                showResults(allUsers);
                return;
            }

            const filtered = allUsers.filter(u => {
                return u.name.toLowerCase().includes(query);
            });

            showResults(filtered);
        }

        function showResults(usersList) {
            if (usersList.length === 0) {
                autocompleteResults.innerHTML = '<li class="dropdown-item text-muted disabled py-2" style="min-height: auto;">No users found</li>';
                autocompleteResults.style.display = 'block';
                return;
            }

            let html = '';
            usersList.forEach(u => {
                html += `
                    <li class="dropdown-item py-2 border-bottom" style="cursor: pointer; min-height: auto;" data-id="${u.id}" data-name="${escapeHtml(u.name)}">
                        <div class="fw-semibold text-dark">${escapeHtml(u.name)}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">Type: ${escapeHtml(u.user_type)}</small>
                    </li>
                `;
            });

            autocompleteResults.innerHTML = html;
            autocompleteResults.style.display = 'block';

            // Click listener for each item
            autocompleteResults.querySelectorAll('li.dropdown-item').forEach(item => {
                if (item.classList.contains('disabled')) return;
                item.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    toUserIdInput.value = id;
                    userSearchInput.value = name;
                    autocompleteResults.style.display = 'none';
                });
            });
        }

        function fetchAndFilterUsers(reset = true) {
            if (reset) {
                toUserIdInput.value = '';
                userSearchInput.value = '';
            }

            const divisionId = divisionSelect.value;
            const departmentId = departmentSelect.value;

            if (!divisionId) {
                allUsers = [];
                return;
            }

            let url = `/api/users?division_id=${divisionId}`;
            if (departmentId) url += `&department_id=${departmentId}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    allUsers = data;
                })
                .catch(err => console.error('Error fetching users:', err));
        }

        // Show suggestions on input, focus, or click
        userSearchInput.addEventListener('input', renderAutocomplete);
        userSearchInput.addEventListener('focus', renderAutocomplete);
        userSearchInput.addEventListener('click', renderAutocomplete);

        // Clear hidden input when search text is cleared completely
        userSearchInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                toUserIdInput.value = '';
            }
        });

        // Hide results when clicking outside the input or list
        document.addEventListener('click', function(e) {
            if (e.target !== userSearchInput && !autocompleteResults.contains(e.target)) {
                autocompleteResults.style.display = 'none';
            }
        });

        divisionSelect.addEventListener('change', () => fetchAndFilterUsers(true));
        departmentSelect.addEventListener('change', () => fetchAndFilterUsers(true));

        // On load, fetch if division/department is pre-selected
        if (divisionSelect.value || departmentSelect.value) {
            fetchAndFilterUsers(false);
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>
@endsection
