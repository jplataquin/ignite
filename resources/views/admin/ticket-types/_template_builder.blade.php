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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hiddenInput = document.getElementById('template_json');
    const table = document.getElementById('template-builder-table');
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

    function serializeTemplate() {
        const result = { fields: {} };
        let hasActive = false;

        rows.forEach(row => {
            const fieldName = row.getAttribute('data-field');
            const enableCheckbox = row.querySelector('.field-enable');
            const valueInput = row.querySelector('.field-value');
            const lockCheckbox = row.querySelector('.field-lock');

            if (enableCheckbox.checked) {
                hasActive = true;
                result.fields[fieldName] = {
                    value: valueInput.value,
                    locked: lockCheckbox.checked
                };
            }
        });

        hiddenInput.value = hasActive ? JSON.stringify(result) : '';
    }

    // Attach to form submit to ensure latest state is serialized
    const form = hiddenInput.closest('form');
    if (form) {
        form.addEventListener('submit', serializeTemplate);
    }
});
</script>
