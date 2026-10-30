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
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="tpl_enable_approvers">
                        <label class="form-check-label fw-semibold text-dark small" for="tpl_enable_approvers">Default Approvers Checklist</label>
                    </div>
                    <div class="form-check d-inline-block">
                        <input class="form-check-input" type="checkbox" id="tpl_lock_approvers" disabled>
                        <label class="form-check-label small text-muted" for="tpl_lock_approvers">Lock Approvers (Prevent user edits)</label>
                    </div>
                </div>

                <div id="tpl-approver-controls" class="p-3 bg-light rounded border mb-2" style="display: none;">
                    <label class="form-label fw-semibold text-dark small">Add Approver to Checklist</label>
                    <div class="input-group mb-3">
                        <select id="tpl_approver_selector" class="form-select form-select-sm" disabled>
                            <option value="">Select User to Add as Approver...</option>
                            @if(isset($users))
                                @foreach($users as $userOption)
                                    <option value="{{ $userOption->id }}" data-name="{{ $userOption->name }}">{{ $userOption->name }} ({{ ucfirst($userOption->user_type) }})</option>
                                @endforeach
                            @endif
                        </select>
                        <button type="button" id="tpl_btn_add_approver" class="btn btn-sm btn-primary" disabled>Add to Checklist</button>
                    </div>

                    <label class="form-label fw-semibold text-dark small">Checklist Sequence</label>
                    <ul id="tpl-approvers-list" class="list-group list-group-flush border rounded bg-white">
                        <li id="tpl-no-approvers-placeholder" class="list-group-item text-muted small py-3 text-center">No approvers added to template yet.</li>
                    </ul>
                </div>
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
            li.className = 'list-group-item d-flex align-items-center justify-content-between py-2 tpl-approver-item';
            li.innerHTML = `
                <div class="d-flex align-items-center">
                    <span class="badge bg-secondary me-2">#${index + 1}</span>
                    <span class="fw-semibold text-dark small">${escapeHtml(appr.name)}</span>
                </div>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary py-0 px-2 btn-move-up" data-index="${index}" title="Move Up" ${index === 0 ? 'disabled' : ''}>&uarr;</button>
                    <button type="button" class="btn btn-outline-secondary py-0 px-2 btn-move-down" data-index="${index}" title="Move Down" ${index === tplApprovers.length - 1 ? 'disabled' : ''}>&darr;</button>
                    <button type="button" class="btn btn-outline-danger py-0 px-2 btn-remove-tpl-approver" data-index="${index}" title="Remove">&times;</button>
                </div>
            `;
            list.appendChild(li);
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

    const tplApproverSelector = document.getElementById('tpl_approver_selector');
    const tplBtnAddApprover = document.getElementById('tpl_btn_add_approver');

    if (tplBtnAddApprover && tplApproverSelector) {
        tplBtnAddApprover.addEventListener('click', function () {
            const userId = tplApproverSelector.value;
            const opt = tplApproverSelector.options[tplApproverSelector.selectedIndex];
            if (!userId) return;

            if (tplApprovers.some(a => a.id == userId)) {
                alert('This user is already in the approvers checklist.');
                return;
            }

            tplApprovers.push({
                id: userId,
                name: opt.getAttribute('data-name') || opt.text
            });

            tplApproverSelector.value = '';
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
    const lockApprovers = document.getElementById('tpl_lock_approvers');
    const approverControls = document.getElementById('tpl-approver-controls');

    if (enableApprovers) {
        enableApprovers.addEventListener('change', function () {
            const isEnabled = this.checked;
            if (approverControls) approverControls.style.display = isEnabled ? 'block' : 'none';
            if (tplApproverSelector) tplApproverSelector.disabled = !isEnabled;
            if (tplBtnAddApprover) tplBtnAddApprover.disabled = !isEnabled;
            if (lockApprovers) {
                lockApprovers.disabled = !isEnabled;
                if (!isEnabled) lockApprovers.checked = false;
            }
            serializeTemplate();
        });
    }

    if (lockApprovers) {
        lockApprovers.addEventListener('change', serializeTemplate);
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
            if (tplApproverSelector) tplApproverSelector.disabled = false;
            if (tplBtnAddApprover) tplBtnAddApprover.disabled = false;
            if (lockApprovers) {
                lockApprovers.disabled = false;
                lockApprovers.checked = !!approvalData.approvers_locked;
            }
            if (Array.isArray(approvalData.approvers)) {
                tplApprovers = approvalData.approvers.map(a => ({ id: a.id, name: a.name }));
                renderTplApproversList();
            }
        }
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

            if (enableApprovers && enableApprovers.checked && tplApprovers.length > 0) {
                hasActive = true;
                approvalObj.has_approvers = true;
                approvalObj.approvers = tplApprovers.map((a, idx) => ({
                    id: a.id,
                    name: a.name,
                    sequence_order: idx + 1
                }));
                approvalObj.approvers_locked = lockApprovers ? lockApprovers.checked : false;
            }

            if (Object.keys(approvalObj).length > 0) {
                result.approval = approvalObj;
            }
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
