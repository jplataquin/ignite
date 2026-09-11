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

                <!-- Existing Attachments -->
                @if($ticket->attachments->count() > 0)
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark small">Existing Attachments</label>
                    <div class="row row-cols-1 g-2">
                        @foreach($ticket->attachments as $index => $attachment)
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-2" id="existing-attachment-{{ $attachment->id }}" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 border bg-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; overflow: hidden; border-radius: 8px; border-color: #cbd5e1 !important;">
                                        @php
                                            $isImage = str_starts_with($attachment->mime_type ?? '', 'image/') || 
                                                       in_array(strtolower(pathinfo($attachment->file_name ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        @endphp
                                        @if($isImage)
                                            <img src="{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}" class="w-100 h-100" style="object-fit: cover;">
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text text-secondary" viewBox="0 0 16 16">
                                                <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                                                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="overflow-hidden">
                                        <span class="d-block fw-bold text-dark text-truncate" style="max-width: 250px; font-size: 0.88rem; color: #1e293b !important;">
                                            {{ $attachment->file_name }}
                                        </span>
                                        <span class="text-muted d-block mt-0.5" style="font-size: 0.72rem; color: #64748b !important;">{{ round($attachment->file_size / 1024, 1) }} KB</span>
                                    </div>
                                </div>
                                
                                <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center p-0 rounded-circle" style="width: 32px; height: 32px;" onclick="removeExistingAttachment({{ $attachment->id }})" title="Remove attachment">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                        <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-1.995 0L3.83 3.5h8.34zM5 5.033V13h1V5.033zm4 0V13h1V5.033z"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="deleted_attachments" id="deleted_attachments" value="[]">
                </div>
                @endif

                <!-- File Drop Zone for New Attachments -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark small">Add Attachments (Photos, PDF, Excel, Documents)</label>
                    <div id="drop-zone" class="border border-2 border-dashed rounded p-4 text-center bg-light" style="border-style: dashed !important; transition: background-color 0.2s, border-color 0.2s; cursor: pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-cloud-upload text-secondary mb-2" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.562 14.384 11 12.362 11H4.378C2.261 11 0 9.286 0 7.117c0-2.117 2.134-3.51 4.406-3.51a.54.54 0 0 1 .494.314l.056.109.057-.109a2.524 2.524 0 0 1 2.215-1.378c.84 0 1.572.41 1.996 1.053a.5.5 0 0 1-.84.54C7.79 3.593 7.218 3.25 6.64 3.25a1.524 1.524 0 0 0-1.314.806.5.5 0 0 1-.868-.04 3.411 3.411 0 0 0-3.14 2.457.5.5 0 0 1-.368.354A2.5 2.5 0 0 0 1 7.117c0 1.536 1.547 2.383 3.378 2.383h7.984c1.482 0 2.638-.973 2.638-2.227 0-1.254-1.156-2.227-2.638-2.227a.5.5 0 0 1-.482-.364 3.52 3.52 0 0 0-3.416-2.509.5.5 0 0 1-.487-.354A4.5 4.5 0 0 0 8 1a4.5 4.5 0 0 0-4.084 2.766.5.5 0 0 1-.908-.424l.053-.112z"/>
                            <path fill-rule="evenodd" d="M7.646 5.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2z"/>
                        </svg>
                        <p class="mb-1 fw-semibold text-dark small">Drag & drop files here, or click to browse</p>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Allowed formats: .jpg, .png, .gif, .pdf, .xls, .xlsx, .doc, .docx, .txt, .csv</p>
                        <input type="file" id="file-input" class="d-none" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xls,.xlsx,.csv,.doc,.docx,.odt,.txt,.rtf">
                    </div>

                    <!-- Dynamic List of Upload Progresses -->
                    <div id="upload-progress-list" class="mt-3">
                        <!-- Progress rows will be appended here dynamically -->
                    </div>

                    <!-- Hidden Field for All Completed Attachments -->
                    <input type="hidden" name="attachments_json" id="attachments_json">
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

        // --- EXISTING ATTACHMENTS REMOVAL LOGIC ---
        let deletedAttachments = [];
        window.removeExistingAttachment = function(id) {
            if (confirm("Are you sure you want to remove this attachment?")) {
                const item = document.getElementById(`existing-attachment-${id}`);
                if (item) {
                    item.remove();
                }
                deletedAttachments.push(id);
                const input = document.getElementById('deleted_attachments');
                if (input) {
                    input.value = JSON.stringify(deletedAttachments);
                }
            }
        };

        // --- CHUNKED MULTI-FILE UPLOADER LOGIC ---
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const progressList = document.getElementById('upload-progress-list');
        const attachmentsJsonInput = document.getElementById('attachments_json');
        const submitBtn = document.querySelector('button[type="submit"]');

        const CHUNK_SIZE = 2 * 1024 * 1024; // 2MB chunks
        const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'csv', 'doc', 'docx', 'odt', 'txt', 'rtf'];

        let completedAttachments = [];
        let activeUploadsCount = 0;

        // Handle Click to Browse
        dropZone.addEventListener('click', () => fileInput.click());

        // Handle Drag & Drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-dark', 'text-white', 'opacity-75');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('bg-dark', 'text-white', 'opacity-75');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-dark', 'text-white', 'opacity-75');
            if (e.dataTransfer.files.length > 0) {
                handleFiles(e.dataTransfer.files);
            }
        });

        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                handleFiles(this.files);
            }
        });

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                const extension = file.name.split('.').pop().toLowerCase();
                if (!ALLOWED_EXTENSIONS.includes(extension)) {
                    alert(`File "${file.name}" is not allowed. Allowed types are photos, pdf, excel, and documents.`);
                    return;
                }
                handleFile(file);
            });
        }

        function handleFile(file) {
            const identifier = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            // Increment active uploads and disable submit button
            activeUploadsCount++;
            submitBtn.disabled = true;

            // Append progress row to progress list
            const progressRowId = `progress-row-${identifier}`;
            const rowHTML = `
                <div id="${progressRowId}" class="p-3 mb-2 bg-white rounded border shadow-sm d-flex flex-column attachment-row" data-identifier="${identifier}">
                    <div class="d-flex gap-3 align-items-center w-100">
                        <!-- Icon / Thumbnail Container -->
                        <div id="preview-${identifier}" class="flex-shrink-0 border rounded bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; overflow: hidden;">
                            <!-- Populate via JS -->
                        </div>

                        <!-- Progress Info -->
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-dark small fw-semibold text-truncate" style="max-width: 250px;">
                                    <span class="badge bg-secondary me-1 attachment-number">Attachment #1</span>
                                    ${escapeHtml(file.name)}
                                </span>
                                <span id="percentage-${identifier}" class="text-muted small fw-semibold">0%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div id="bar-${identifier}" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div id="status-${identifier}" class="text-muted mt-1" style="font-size: 0.75rem;">Preparing upload...</div>
                        </div>

                        <!-- Delete Button -->
                        <div class="flex-shrink-0 ms-2">
                            <button type="button" id="delete-${identifier}" class="btn btn-sm btn-outline-danger d-none d-flex align-items-center justify-content-center p-0 rounded-circle" style="width: 32px; height: 32px;" onclick="deleteAttachment('${identifier}')" title="Delete attachment">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-1.995 0L3.83 3.5h8.34zM5 5.033V13h1V5.033zm4 0V13h1V5.033z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Note Input (Visible on complete) -->
                    <div id="note-container-${identifier}" class="mt-2 d-none">
                        <label for="note-${identifier}" class="form-label text-muted small fw-semibold mb-1">File Note (Optional)</label>
                        <textarea id="note-${identifier}" class="form-control form-control-sm" rows="2" placeholder="Enter an optional note/description for this file..."></textarea>
                    </div>
                </div>
            `;
            progressList.insertAdjacentHTML('beforeend', rowHTML);
            updateAttachmentNumbers();

            // Populate preview container
            const isImage = file.type.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(file.name.split('.').pop().toLowerCase());
            const previewContainer = document.getElementById(`preview-${identifier}`);
            let previewUrl = '';
            if (isImage) {
                const imgUrl = URL.createObjectURL(file);
                previewContainer.innerHTML = `<img src="${imgUrl}" class="w-100 h-100" style="object-fit: cover;">`;
                previewUrl = imgUrl;
            } else {
                previewContainer.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-earmark-text text-secondary" viewBox="0 0 16 16">
                        <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                        <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
                    </svg>
                `;
            }

            previewContainer.classList.add('previewable-attachment');
            previewContainer.style.cursor = 'pointer';
            previewContainer.dataset.url = previewUrl;
            previewContainer.dataset.name = file.name;
            previewContainer.dataset.mime = file.type;

            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            uploadNextChunk(file, identifier, 1, totalChunks);
        }

        function uploadNextChunk(file, identifier, chunkNumber, totalChunks) {
            const start = (chunkNumber - 1) * CHUNK_SIZE;
            const end = Math.min(start + CHUNK_SIZE, file.size);
            const chunk = file.slice(start, end);

            const formData = new FormData();
            formData.append('file', chunk);
            formData.append('resumableFilename', file.name);
            formData.append('resumableIdentifier', identifier);
            formData.append('resumableChunkNumber', chunkNumber);
            formData.append('resumableTotalChunks', totalChunks);

            const statusText = document.getElementById(`status-${identifier}`);
            const progressBar = document.getElementById(`bar-${identifier}`);
            const percentageLabel = document.getElementById(`percentage-${identifier}`);

            if (statusText) {
                statusText.textContent = `Uploading chunk ${chunkNumber} of ${totalChunks}...`;
            }

            fetch('/tickets/upload-chunk', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Upload error');
                }
                return response.json();
            })
            .then(data => {
                const percentComplete = Math.round((chunkNumber / totalChunks) * 100);
                if (progressBar) progressBar.style.width = percentComplete + '%';
                if (percentageLabel) percentageLabel.textContent = percentComplete + '%';

                if (chunkNumber < totalChunks) {
                    uploadNextChunk(file, identifier, chunkNumber + 1, totalChunks);
                } else {
                    // Upload Completed
                    if (statusText) {
                        statusText.innerHTML = '<span class="text-success fw-bold">✓ Upload Complete</span>';
                    }
                    if (progressBar) {
                        progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                    }
                    const deleteBtn = document.getElementById(`delete-${identifier}`);
                    if (deleteBtn) {
                        deleteBtn.classList.remove('d-none');
                    }
                    
                    // Save to completedAttachments array
                    completedAttachments.push({
                        temp_token: identifier,
                        total_chunks: totalChunks,
                        file_name: file.name,
                        mime_type: file.type || 'application/octet-stream',
                        note: ''
                    });

                    // Update Hidden Input with Serialized JSON
                    attachmentsJsonInput.value = JSON.stringify(completedAttachments);

                    // Show the Note Input container
                    const noteContainer = document.getElementById(`note-container-${identifier}`);
                    if (noteContainer) {
                        noteContainer.classList.remove('d-none');
                    }

                    // Attach input change listener to Note Input
                    const noteInput = document.getElementById(`note-${identifier}`);
                    if (noteInput) {
                        noteInput.addEventListener('input', function() {
                            const val = this.value;
                            const att = completedAttachments.find(item => item.temp_token === identifier);
                            if (att) {
                                att.note = val;
                                attachmentsJsonInput.value = JSON.stringify(completedAttachments);
                            }
                        });
                    }

                    // Decrement active uploads and check if we can re-enable the submit button
                    activeUploadsCount--;
                    if (activeUploadsCount === 0) {
                        submitBtn.disabled = false;
                    }
                }
            })
            .catch(error => {
                console.error(error);
                if (statusText) {
                    statusText.innerHTML = '<span class="text-danger fw-bold">✗ Upload Failed. Please try again.</span>';
                }
                const deleteBtn = document.getElementById(`delete-${identifier}`);
                if (deleteBtn) {
                    deleteBtn.classList.remove('d-none');
                }
                
                // Decrement active uploads and check if we can re-enable the submit button
                activeUploadsCount--;
                if (activeUploadsCount === 0) {
                    submitBtn.disabled = false;
                }
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

        function updateAttachmentNumbers() {
            const rows = document.querySelectorAll('#upload-progress-list .attachment-row');
            rows.forEach((row, index) => {
                const numberLabel = row.querySelector('.attachment-number');
                if (numberLabel) {
                    numberLabel.textContent = `Attachment #${index + 1}`;
                }
            });
        }

        window.deleteAttachment = function(identifier) {
            if (confirm("Are you sure you want to remove this attachment?")) {
                const row = document.getElementById(`progress-row-${identifier}`);
                if (row) {
                    row.remove();
                }
                completedAttachments = completedAttachments.filter(item => item.temp_token !== identifier);
                attachmentsJsonInput.value = JSON.stringify(completedAttachments);
                updateAttachmentNumbers();
            }
        };

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