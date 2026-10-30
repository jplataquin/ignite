<div class="border rounded p-3 mb-4 bg-light">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
            <h6 class="fw-bold text-dark mb-0">Ticket Form Template (Visual Builder)</h6>
            <small class="text-muted">Define prefilled values for new tickets of this type. Locked fields cannot be changed by users.</small>
        </div>
        <span class="badge bg-secondary text-white">Optional Template</span>
    </div>

    <input type="hidden" name="template_json" id="template_json" value="{{ old('template_json', isset($ticketType) && $ticketType->template_json ? (is_array($ticketType->template_json) ? json_encode($ticketType->template_json) : $ticketType->template_json) : '') }}">

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-sm align-middle bg-white mb-0" id="template-builder-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 25%;">Field</th>
                    <th style="width: 15%; text-align: center;">Include in Template</th>
                    <th style="width: 45%;">Default Value</th>
                    <th style="width: 15%; text-align: center;">Lock Field</th>
                </tr>
            </thead>
            <tbody>
                <!-- Title -->
                <tr data-field="title">
                    <td class="fw-semibold text-dark small">Title</td>
                    <td class="text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input field-enable" type="checkbox" id="tpl_enable_title">
                        </div>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm field-value" placeholder="Default ticket title..." disabled>
                    </td>
                    <td class="text-center">
                        <div class="form-check d-inline-block">
                            <input class="form-check-input field-lock" type="checkbox" id="tpl_lock_title" disabled>
                            <label class="form-check-label small text-muted" for="tpl_lock_title">Locked</label>
                        </div>
                    </td>
                </tr>

                <!-- Description -->
                <tr data-field="description">
                    <td class="fw-semibold text-dark small">Description</td>
                    <td class="text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input field-enable" type="checkbox" id="tpl_enable_description">
                        </div>
                    </td>
                    <td>
                        <textarea class="form-control form-control-sm field-value" rows="2" placeholder="Default template instructions/content..." disabled></textarea>
                    </td>
                    <td class="text-center">
                        <div class="form-check d-inline-block">
                            <input class="form-check-input field-lock" type="checkbox" id="tpl_lock_description" disabled>
                            <label class="form-check-label small text-muted" for="tpl_lock_description">Locked</label>
                        </div>
                    </td>
                </tr>

                <!-- Priority -->
                <tr data-field="priority_option_id">
                    <td class="fw-semibold text-dark small">Priority</td>
                    <td class="text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input field-enable" type="checkbox" id="tpl_enable_priority">
                        </div>
                    </td>
                    <td>
                        <select class="form-select form-select-sm field-value" disabled>
                            <option value="">Select Priority...</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="form-check d-inline-block">
                            <input class="form-check-input field-lock" type="checkbox" id="tpl_lock_priority" disabled>
                            <label class="form-check-label small text-muted" for="tpl_lock_priority">Locked</label>
                        </div>
                    </td>
                </tr>

                <!-- Division -->
                <tr data-field="division_id">
                    <td class="fw-semibold text-dark small">Division</td>
                    <td class="text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input field-enable" type="checkbox" id="tpl_enable_division">
                        </div>
                    </td>
                    <td>
                        <select class="form-select form-select-sm field-value" id="tpl_select_division" disabled>
                            <option value="">Select Division...</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="form-check d-inline-block">
                            <input class="form-check-input field-lock" type="checkbox" id="tpl_lock_division" disabled>
                            <label class="form-check-label small text-muted" for="tpl_lock_division">Locked</label>
                        </div>
                    </td>
                </tr>

                <!-- Department -->
                <tr data-field="department_id">
                    <td class="fw-semibold text-dark small">Department</td>
                    <td class="text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input field-enable" type="checkbox" id="tpl_enable_department">
                        </div>
                    </td>
                    <td>
                        <select class="form-select form-select-sm field-value" id="tpl_select_department" disabled>
                            <option value="">Select Department...</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" data-division-id="{{ $department->division_id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="form-check d-inline-block">
                            <input class="form-check-input field-lock" type="checkbox" id="tpl_lock_department" disabled>
                            <label class="form-check-label small text-muted" for="tpl_lock_department">Locked</label>
                        </div>
                    </td>
                </tr>

                <!-- Location -->
                <tr data-field="location_id">
                    <td class="fw-semibold text-dark small">Location</td>
                    <td class="text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input field-enable" type="checkbox" id="tpl_enable_location">
                        </div>
                    </td>
                    <td>
                        <select class="form-select form-select-sm field-value" disabled>
                            <option value="">Select Location...</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="form-check d-inline-block">
                            <input class="form-check-input field-lock" type="checkbox" id="tpl_lock_location" disabled>
                            <label class="form-check-label small text-muted" for="tpl_lock_location">Locked</label>
                        </div>
                    </td>
                </tr>

                @if(isset($categories) && $categories->count() > 0)
                <!-- Category 1 -->
                <tr data-field="category_1_id">
                    <td class="fw-semibold text-dark small">Category 1</td>
                    <td class="text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input field-enable" type="checkbox" id="tpl_enable_category_1">
                        </div>
                    </td>
                    <td>
                        <select class="form-select form-select-sm field-value" disabled>
                            <option value="">Select Category 1...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="form-check d-inline-block">
                            <input class="form-check-input field-lock" type="checkbox" id="tpl_lock_category_1" disabled>
                            <label class="form-check-label small text-muted" for="tpl_lock_category_1">Locked</label>
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Approval & Clearance Checklist Template (Visible only when prototype is approval_clearance) -->
    <div id="tpl-approval-checklist-section" class="mt-4 pt-3 border-top" style="display: none;">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h6 class="fw-bold text-dark mb-0">Approval & Clearance Checklist</h6>
                <small class="text-muted">Define the default sign-off sequence and approvers for clearance tickets of this type.</small>
            </div>
            <span class="badge bg-primary text-white">Approval Prototype</span>
        </div>

        <div class="card p-3 mb-2 bg-white border">
            <!-- Approval Sign-off Mode -->
            <div class="row g-3 align-items-center mb-3">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="tpl_enable_approval_mode">
                        <label class="form-check-label fw-semibold text-dark small" for="tpl_enable_approval_mode">Default Sign-off Mode</label>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tpl_approval_mode" id="tpl_mode_seq" value="sequential" checked disabled>
                            <label class="form-check-label small" for="tpl_mode_seq">
                                <strong>Sequential</strong> (1-by-1)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tpl_approval_mode" id="tpl_mode_par" value="parallel" disabled>
                            <label class="form-check-label small" for="tpl_mode_par">
                                <strong>Parallel</strong> (Any order)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <div class="form-check d-inline-block">
                        <input class="form-check-input" type="checkbox" id="tpl_lock_approval_mode" disabled>
                        <label class="form-check-label small text-muted" for="tpl_lock_approval_mode">Lock Mode</label>
                    </div>
                </div>
            </div>

            <hr class="my-2 text-muted">

            <!-- Approvers Checklist -->
            <div class="mt-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="tpl_enable_approvers">
                        <label class="form-check-label fw-semibold text-dark small" for="tpl_enable_approvers">Default Approvers Checklist</label>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check d-inline-block">
                            <input class="form-check-input" type="checkbox" id="tpl_lock_add_approvers" disabled>
                            <label class="form-check-label small text-muted" for="tpl_lock_add_approvers">Lock Adding Users (Do not allow adding more users)</label>
                        </div>
                        <div class="form-check d-inline-block">
                            <input class="form-check-input" type="checkbox" id="tpl_lock_approvers" disabled>
                            <label class="form-check-label small text-muted" for="tpl_lock_approvers">Lock Approvers (Prevent user edits)</label>
                        </div>
                    </div>
                </div>

                <div id="tpl-approver-controls" class="p-3 bg-light rounded border mb-2" style="display: none;">
                    <div class="row g-2 mb-3 align-items-start">
                        <div class="col-md-5">
                            <label for="tpl_approver_search" class="form-label fw-semibold text-dark small mb-1">Add Approver</label>
                            <div class="position-relative">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted small py-1 px-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                        </svg>
                                    </span>
                                    <input type="text" id="tpl_approver_search" class="form-control form-control-sm" placeholder="Type to search users..." autocomplete="off" disabled>
                                    <input type="hidden" id="tpl_approver_id">
                                </div>
                                <ul id="tpl-approver-autocomplete" class="dropdown-menu w-100 shadow border-0 py-0" style="max-height: 220px; overflow-y: auto; z-index: 1050; font-size: 0.85rem; position: absolute; top: 100%; left: 0; display: none;"></ul>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="tpl_approver_desc" class="form-label fw-semibold text-dark small mb-1">Checklist Description</label>
                            <textarea id="tpl_approver_desc" class="form-control form-control-sm" rows="2" placeholder="Sign-off description / requirements (e.g. IT Equipment, Finance clearance)..." disabled></textarea>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" id="tpl_btn_add_approver" class="btn btn-sm btn-primary w-100 text-nowrap" style="margin-top: 24px;" disabled>Add to Checklist</button>
                        </div>
                    </div>

                    <label class="form-label fw-semibold text-dark small">Checklist Sequence</label>
                    <ul id="tpl-approvers-list" class="list-group list-group-flush border rounded bg-white">
                        <li id="tpl-no-approvers-placeholder" class="list-group-item text-muted small py-3 text-center">No approvers added to template yet.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Custom Stage Route Configuration -->
        <div id="tpl-stage-route-section" class="mt-4 border-top pt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h6 class="fw-bold text-dark mb-0">Custom Stage Route</h6>
                    <small class="text-muted">Define a custom sequence of stages for tickets of this type.</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="tpl_enable_stage_route">
                    <label class="form-check-label fw-semibold text-dark small" for="tpl_enable_stage_route">Enable Custom Route</label>
                </div>
            </div>

            <div id="tpl-stage-route-controls" class="p-3 bg-light rounded border mb-2" style="display: none;">
                <div class="row g-2 mb-3 align-items-start">
                    <div class="col-md-9">
                        <label for="tpl_stage_selector" class="form-label fw-semibold text-dark small mb-1">Select Stage to Add</label>
                        <select id="tpl_stage_selector" class="form-select form-select-sm" disabled>
                            <option value="">Choose a stage...</option>
                            @if(isset($ticketStages))
                                @foreach($ticketStages as $stg)
                                    <option value="{{ $stg->id }}" data-name="{{ $stg->name }}" data-color="{{ $stg->color_code }}">{{ $stg->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="tpl_btn_add_stage" class="btn btn-sm btn-primary w-100 text-nowrap" style="margin-top: 24px;" disabled>Add to Route</button>
                    </div>
                </div>

                <label class="form-label fw-semibold text-dark small">Stage Route Sequence</label>
                <ul id="tpl-stage-route-list" class="list-group list-group-flush border rounded bg-white">
                    <li id="tpl-no-stages-placeholder" class="list-group-item text-muted small py-3 text-center">No stages added to the route yet.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hiddenInput = document.getElementById('template_json');
    const table = document.getElementById('template-builder-table');
    const prototypeSelect = document.getElementById('prototype');
    const approvalSection = document.getElementById('tpl-approval-checklist-section');

    if (!hiddenInput || !table) return;

    let initialTemplate = {};
    try {
        if (hiddenInput.value) {
            initialTemplate = JSON.parse(hiddenInput.value) || {};
        }
    } catch (e) {
        console.error('Failed to parse template_json:', e);
    }

    const fieldsData = initialTemplate.fields || {};
    const rows = table.querySelectorAll('tbody tr[data-field]');

    rows.forEach(row => {
        const fieldName = row.getAttribute('data-field');
        const enableCheckbox = row.querySelector('.field-enable');
        const valueInput = row.querySelector('.field-value');
        const lockCheckbox = row.querySelector('.field-lock');

        if (fieldsData[fieldName]) {
            enableCheckbox.checked = true;
            valueInput.disabled = false;
            valueInput.value = fieldsData[fieldName].value ?? '';
            lockCheckbox.disabled = false;
            lockCheckbox.checked = !!fieldsData[fieldName].locked;
        }

        enableCheckbox.addEventListener('change', function () {
            const isEnabled = this.checked;
            valueInput.disabled = !isEnabled;
            lockCheckbox.disabled = !isEnabled;
            if (!isEnabled) {
                lockCheckbox.checked = false;
            }
            serializeTemplate();
        });

        valueInput.addEventListener('input', serializeTemplate);
        valueInput.addEventListener('change', serializeTemplate);
        lockCheckbox.addEventListener('change', serializeTemplate);
    });

    // Approval & Clearance Checklist Logic
    let tplApprovers = [];

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function syncPrototype() {
        const isApproval = prototypeSelect && prototypeSelect.value === 'approval_clearance';
        if (approvalSection) {
            approvalSection.style.display = isApproval ? 'block' : 'none';
        }
        const stageRouteSection = document.getElementById('tpl-stage-route-section');
        if (stageRouteSection) {
            stageRouteSection.style.display = isApproval ? 'none' : 'block';
        }
    }

    if (prototypeSelect) {
        prototypeSelect.addEventListener('change', function () {
            syncPrototype();
            serializeTemplate();
        });
        syncPrototype();
    }

    function renderTplApproversList() {
        const list = document.getElementById('tpl-approvers-list');
        const placeholder = document.getElementById('tpl-no-approvers-placeholder');
        if (!list) return;

        list.querySelectorAll('.tpl-approver-item').forEach(el => el.remove());

        if (tplApprovers.length === 0) {
            if (placeholder) placeholder.style.display = 'block';
            return;
        }
        if (placeholder) placeholder.style.display = 'none';

        tplApprovers.forEach((appr, index) => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex flex-wrap align-items-start justify-content-between gap-2 py-2 tpl-approver-item';
            li.innerHTML = `
                <div class="d-flex align-items-start flex-grow-1 gap-2 flex-wrap">
                    <span class="badge bg-secondary mt-1">#${index + 1}</span>
                    <span class="fw-semibold text-dark small mt-1" style="min-width: 140px;">${escapeHtml(appr.name)}</span>
                    <div class="flex-grow-1" style="min-width: 260px; max-width: 450px;">
                        <textarea class="form-control form-control-sm tpl-signoff-desc" rows="2" data-index="${index}" placeholder="Sign-off description / instructions...">${escapeHtml(appr.signoff_description || '')}</textarea>
                    </div>
                </div>
                <div class="btn-group btn-group-sm mt-1">
                    <button type="button" class="btn btn-outline-secondary py-0 px-2 btn-move-up" data-index="${index}" title="Move Up" ${index === 0 ? 'disabled' : ''}>&uarr;</button>
                    <button type="button" class="btn btn-outline-secondary py-0 px-2 btn-move-down" data-index="${index}" title="Move Down" ${index === tplApprovers.length - 1 ? 'disabled' : ''}>&darr;</button>
                    <button type="button" class="btn btn-outline-danger py-0 px-2 btn-remove-tpl-approver" data-index="${index}" title="Remove">&times;</button>
                </div>
            `;
            list.appendChild(li);
        });

        list.querySelectorAll('.tpl-signoff-desc').forEach(input => {
            input.addEventListener('input', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                if (tplApprovers[idx]) {
                    tplApprovers[idx].signoff_description = this.value;
                }
                serializeTemplate();
            });
        });

        list.querySelectorAll('.btn-move-up').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                if (idx > 0) {
                    const temp = tplApprovers[idx];
                    tplApprovers[idx] = tplApprovers[idx - 1];
                    tplApprovers[idx - 1] = temp;
                    renderTplApproversList();
                    serializeTemplate();
                }
            });
        });

        list.querySelectorAll('.btn-move-down').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                if (idx < tplApprovers.length - 1) {
                    const temp = tplApprovers[idx];
                    tplApprovers[idx] = tplApprovers[idx + 1];
                    tplApprovers[idx + 1] = temp;
                    renderTplApproversList();
                    serializeTemplate();
                }
            });
        });

        list.querySelectorAll('.btn-remove-tpl-approver').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                tplApprovers.splice(idx, 1);
                renderTplApproversList();
                serializeTemplate();
            });
        });
    }

    @php
        $allTplUsersData = isset($users) ? $users->map(fn($u) => [
            'id' => (string)$u->id,
            'name' => $u->name,
            'user_type' => ucfirst($u->user_type ?? 'User'),
        ])->values() : collect();
    @endphp
    const allTplUsers = {!! json_encode($allTplUsersData) !!};

    const tplApproverSearch = document.getElementById('tpl_approver_search');
    const tplApproverId = document.getElementById('tpl_approver_id');
    const tplApproverAutocomplete = document.getElementById('tpl-approver-autocomplete');
    const tplApproverDesc = document.getElementById('tpl_approver_desc');
    const tplBtnAddApprover = document.getElementById('tpl_btn_add_approver');

    function renderTplUserAutocomplete() {
        if (!tplApproverSearch || !tplApproverAutocomplete) return;
        const query = tplApproverSearch.value.trim().toLowerCase();
        const filtered = query
            ? allTplUsers.filter(u => u.name.toLowerCase().includes(query))
            : allTplUsers;

        if (filtered.length === 0) {
            tplApproverAutocomplete.innerHTML = '<li class="dropdown-item text-muted disabled py-2">No users found</li>';
            tplApproverAutocomplete.style.display = 'block';
            return;
        }

        let html = '';
        filtered.slice(0, 50).forEach(u => {
            html += `
                <li class="dropdown-item py-1.5 px-3 border-bottom tpl-user-suggestion-item" style="cursor: pointer;" data-id="${u.id}" data-name="${escapeHtml(u.name)}">
                    <div class="fw-semibold text-dark small">${escapeHtml(u.name)}</div>
                    <small class="text-muted" style="font-size: 0.75rem;">${escapeHtml(u.user_type)}</small>
                </li>
            `;
        });
        tplApproverAutocomplete.innerHTML = html;
        tplApproverAutocomplete.style.display = 'block';

        tplApproverAutocomplete.querySelectorAll('.tpl-user-suggestion-item').forEach(item => {
            item.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                tplApproverId.value = id;
                tplApproverSearch.value = name;
                tplApproverAutocomplete.style.display = 'none';
            });
        });
    }

    if (tplApproverSearch) {
        tplApproverSearch.addEventListener('focus', function () {
            if (!this.disabled) {
                renderTplUserAutocomplete();
            }
        });

        tplApproverSearch.addEventListener('input', function () {
            tplApproverId.value = '';
            renderTplUserAutocomplete();
        });

        document.addEventListener('click', function (e) {
            if (tplApproverAutocomplete && !tplApproverSearch.contains(e.target) && !tplApproverAutocomplete.contains(e.target)) {
                tplApproverAutocomplete.style.display = 'none';
            }
        });
    }

    if (tplBtnAddApprover) {
        tplBtnAddApprover.addEventListener('click', function () {
            const userId = tplApproverId ? tplApproverId.value : '';
            const userName = tplApproverSearch ? tplApproverSearch.value.trim() : '';
            if (!userId) {
                alert('Please search and select a user to add as approver.');
                if (tplApproverSearch) tplApproverSearch.focus();
                return;
            }

            if (tplApprovers.some(a => a.id == userId)) {
                alert('This user is already in the approvers checklist.');
                return;
            }

            const signoffDesc = tplApproverDesc ? tplApproverDesc.value.trim() : '';

            tplApprovers.push({
                id: userId,
                name: userName,
                signoff_description: signoffDesc,
            });

            if (tplApproverId) tplApproverId.value = '';
            if (tplApproverSearch) tplApproverSearch.value = '';
            if (tplApproverDesc) tplApproverDesc.value = '';
            renderTplApproversList();
            serializeTemplate();
        });
    }

    const enableMode = document.getElementById('tpl_enable_approval_mode');
    const lockMode = document.getElementById('tpl_lock_approval_mode');
    const modeRadios = document.querySelectorAll('input[name="tpl_approval_mode"]');

    if (enableMode) {
        enableMode.addEventListener('change', function () {
            const isEnabled = this.checked;
            modeRadios.forEach(r => r.disabled = !isEnabled);
            if (lockMode) {
                lockMode.disabled = !isEnabled;
                if (!isEnabled) lockMode.checked = false;
            }
            serializeTemplate();
        });
    }

    modeRadios.forEach(r => {
        r.addEventListener('change', serializeTemplate);
    });

    if (lockMode) {
        lockMode.addEventListener('change', serializeTemplate);
    }

    const enableApprovers = document.getElementById('tpl_enable_approvers');
    const lockAddApprovers = document.getElementById('tpl_lock_add_approvers');
    const lockApprovers = document.getElementById('tpl_lock_approvers');
    const approverControls = document.getElementById('tpl-approver-controls');

    if (enableApprovers) {
        enableApprovers.addEventListener('change', function () {
            const isEnabled = this.checked;
            if (approverControls) approverControls.style.display = isEnabled ? 'block' : 'none';
            if (tplApproverSearch) tplApproverSearch.disabled = !isEnabled;
            if (tplApproverDesc) tplApproverDesc.disabled = !isEnabled;
            if (tplBtnAddApprover) tplBtnAddApprover.disabled = !isEnabled;
            if (!isEnabled && tplApproverAutocomplete) tplApproverAutocomplete.style.display = 'none';
            if (lockAddApprovers) {
                lockAddApprovers.disabled = !isEnabled || (lockApprovers && lockApprovers.checked);
                if (!isEnabled) lockAddApprovers.checked = false;
            }
            if (lockApprovers) {
                lockApprovers.disabled = !isEnabled;
                if (!isEnabled) lockApprovers.checked = false;
            }
            serializeTemplate();
        });
    }

    if (lockApprovers) {
        lockApprovers.addEventListener('change', function () {
            if (this.checked) {
                if (lockAddApprovers) {
                    lockAddApprovers.checked = true;
                    lockAddApprovers.disabled = true;
                }
            } else {
                if (lockAddApprovers && enableApprovers && enableApprovers.checked) {
                    lockAddApprovers.disabled = false;
                }
            }
            serializeTemplate();
        });
    }

    if (lockAddApprovers) {
        lockAddApprovers.addEventListener('change', serializeTemplate);
    }

    // Load initial approval data if present
    const approvalData = initialTemplate.approval || {};
    if (approvalData.has_mode) {
        if (enableMode) {
            enableMode.checked = true;
            modeRadios.forEach(r => {
                r.disabled = false;
                if (r.value === approvalData.mode) r.checked = true;
            });
            if (lockMode) {
                lockMode.disabled = false;
                lockMode.checked = !!approvalData.mode_locked;
            }
        }
    }

    if (approvalData.has_approvers) {
        if (enableApprovers) {
            enableApprovers.checked = true;
            if (approverControls) approverControls.style.display = 'block';
            if (tplApproverSearch) tplApproverSearch.disabled = false;
            if (tplApproverDesc) tplApproverDesc.disabled = false;
            if (tplBtnAddApprover) tplBtnAddApprover.disabled = false;
            if (lockAddApprovers) {
                lockAddApprovers.disabled = false;
                lockAddApprovers.checked = !!approvalData.lock_add_approvers || !!approvalData.approvers_locked;
            }
            if (lockApprovers) {
                lockApprovers.disabled = false;
                lockApprovers.checked = !!approvalData.approvers_locked;
                if (lockApprovers.checked && lockAddApprovers) {
                    lockAddApprovers.disabled = true;
                }
            }
            if (Array.isArray(approvalData.approvers)) {
                tplApprovers = approvalData.approvers.map(a => ({
                    id: a.id,
                    name: a.name,
                    signoff_description: a.signoff_description || ''
                }));
                renderTplApproversList();
            }
        }
    }

    // --- STAGE ROUTE MANAGEMENT ---
    let tplStages = [];
    const enableStageRoute = document.getElementById('tpl_enable_stage_route');
    const stageRouteControls = document.getElementById('tpl-stage-route-controls');
    const tplStageSelector = document.getElementById('tpl_stage_selector');
    const tplBtnAddStage = document.getElementById('tpl_btn_add_stage');
    const stageRouteList = document.getElementById('tpl-stage-route-list');
    const noStagesPlaceholder = document.getElementById('tpl-no-stages-placeholder');

    function renderTplStagesList() {
        if (!stageRouteList) return;
        const items = stageRouteList.querySelectorAll('.tpl-stage-item');
        items.forEach(el => el.remove());

        if (tplStages.length === 0) {
            if (noStagesPlaceholder) noStagesPlaceholder.style.display = 'block';
            return;
        }
        if (noStagesPlaceholder) noStagesPlaceholder.style.display = 'none';

        tplStages.forEach((stg, index) => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex flex-wrap align-items-center justify-content-between gap-2 py-2 tpl-stage-item';
            li.innerHTML = `
                <div class="d-flex align-items-center flex-grow-1 gap-2 flex-wrap">
                    <span class="badge bg-secondary">#${index + 1}</span>
                    <span class="badge rounded-pill px-3 py-1.5 fw-semibold text-white" style="background-color: ${stg.color_code || '#6b7280'};">
                        ${escapeHtml(stg.name)}
                    </span>
                </div>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary py-0 px-2 btn-move-up" data-index="${index}" title="Move Up" ${index === 0 ? 'disabled' : ''}>&uarr;</button>
                    <button type="button" class="btn btn-outline-secondary py-0 px-2 btn-move-down" data-index="${index}" title="Move Down" ${index === tplStages.length - 1 ? 'disabled' : ''}>&darr;</button>
                    <button type="button" class="btn btn-outline-danger py-0 px-2 btn-remove-tpl-stage" data-index="${index}" title="Remove">&times;</button>
                </div>
            `;
            stageRouteList.appendChild(li);
        });

        stageRouteList.querySelectorAll('.btn-move-up').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                if (idx > 0) {
                    const temp = tplStages[idx];
                    tplStages[idx] = tplStages[idx - 1];
                    tplStages[idx - 1] = temp;
                    renderTplStagesList();
                    serializeTemplate();
                }
            });
        });

        stageRouteList.querySelectorAll('.btn-move-down').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                if (idx < tplStages.length - 1) {
                    const temp = tplStages[idx];
                    tplStages[idx] = tplStages[idx + 1];
                    tplStages[idx + 1] = temp;
                    renderTplStagesList();
                    serializeTemplate();
                }
            });
        });

        stageRouteList.querySelectorAll('.btn-remove-tpl-stage').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                tplStages.splice(idx, 1);
                renderTplStagesList();
                serializeTemplate();
            });
        });
    }

    if (enableStageRoute) {
        enableStageRoute.addEventListener('change', function () {
            const isEnabled = this.checked;
            if (stageRouteControls) stageRouteControls.style.display = isEnabled ? 'block' : 'none';
            if (tplStageSelector) tplStageSelector.disabled = !isEnabled;
            if (tplBtnAddStage) tplBtnAddStage.disabled = !isEnabled;
            serializeTemplate();
        });
    }

    if (tplBtnAddStage && tplStageSelector) {
        tplBtnAddStage.addEventListener('click', function () {
            const stageId = tplStageSelector.value;
            const opt = tplStageSelector.options[tplStageSelector.selectedIndex];
            if (!stageId) return;

            if (tplStages.some(s => String(s.id) === String(stageId))) {
                alert('This stage is already added to the route.');
                return;
            }

            tplStages.push({
                id: stageId,
                name: opt.getAttribute('data-name') || opt.text,
                color_code: opt.getAttribute('data-color') || '#6b7280',
            });

            tplStageSelector.value = '';
            renderTplStagesList();
            serializeTemplate();
        });
    }

    // Load initial stage route if present
    if (Array.isArray(initialTemplate.stages) && initialTemplate.stages.length > 0) {
        if (enableStageRoute) {
            enableStageRoute.checked = true;
            if (stageRouteControls) stageRouteControls.style.display = 'block';
            if (tplStageSelector) tplStageSelector.disabled = false;
            if (tplBtnAddStage) tplBtnAddStage.disabled = false;
        }
        tplStages = initialTemplate.stages.map(s => ({
            id: s.id,
            name: s.name,
            color_code: s.color_code || '#6b7280'
        }));
        renderTplStagesList();
    }

    function serializeTemplate() {
        const result = { fields: {} };
        let hasActive = false;

        rows.forEach(row => {
            const fieldName = row.getAttribute('data-field');
            const enableCheckbox = row.querySelector('.field-enable');
            const valueInput = row.querySelector('.field-value');
            const lockCheckbox = row.querySelector('.field-lock');

            if (enableCheckbox && enableCheckbox.checked) {
                hasActive = true;
                result.fields[fieldName] = {
                    value: valueInput ? valueInput.value : '',
                    locked: lockCheckbox ? lockCheckbox.checked : false
                };
            }
        });

        const isApproval = prototypeSelect && prototypeSelect.value === 'approval_clearance';
        if (isApproval) {
            const approvalObj = {};

            if (enableMode && enableMode.checked) {
                hasActive = true;
                const selectedMode = document.querySelector('input[name="tpl_approval_mode"]:checked')?.value || 'sequential';
                approvalObj.has_mode = true;
                approvalObj.mode = selectedMode;
                approvalObj.mode_locked = lockMode ? lockMode.checked : false;
            }

            if (enableApprovers && enableApprovers.checked) {
                const isAddLocked = lockAddApprovers ? lockAddApprovers.checked : false;
                const isAllLocked = lockApprovers ? lockApprovers.checked : false;

                if (tplApprovers.length > 0 || isAddLocked || isAllLocked) {
                    hasActive = true;
                    approvalObj.has_approvers = true;
                    approvalObj.approvers = tplApprovers.map((a, idx) => ({
                        id: a.id,
                        name: a.name,
                        sequence_order: idx + 1,
                        signoff_description: a.signoff_description || ''
                    }));
                    approvalObj.lock_add_approvers = isAddLocked || isAllLocked;
                    approvalObj.approvers_locked = isAllLocked;
                }
            }

            if (Object.keys(approvalObj).length > 0) {
                result.approval = approvalObj;
            }
        }

        if (!isApproval && enableStageRoute && enableStageRoute.checked && tplStages.length > 0) {
            hasActive = true;
            result.stages = tplStages.map((s, idx) => ({
                id: parseInt(s.id),
                name: s.name,
                color_code: s.color_code,
                sequence_order: idx + 1
            }));
        }

        hiddenInput.value = hasActive ? JSON.stringify(result) : '';
    }

    // Attach to form submit to ensure latest state is serialized
    const form = hiddenInput.closest('form');
    if (form) {
        form.addEventListener('submit', serializeTemplate);
    }
});
</script>
