<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open(current_full_url()); ?>
        <div class="row">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold mbot15">Builder Details</h4>
                        <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo html_escape($form['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Slug</label>
                            <input type="text" class="form-control" name="slug" value="<?php echo html_escape($form['slug'] ?? ''); ?>" placeholder="auto-generated if empty">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Description</label>
                            <textarea name="description" rows="4" class="form-control"><?php echo html_escape($form['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group mtop15">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" id="status" name="status" value="1" <?php echo empty($form) || ! empty($form['status']) ? 'checked' : ''; ?>>
                                <label for="status">Active / Accepting Submissions</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mtop25">
                            <i class="fa fa-save"></i> Save Builder
                        </button>
                        <a href="<?php echo admin_url('ccx_creator'); ?>" class="btn btn-default btn-block mtop10">Back</a>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold mbot10">Submission Workflow</h4>
                        <div class="form-group">
                            <label>Recipients (comma separated)</label>
                            <input type="text" id="workflow-recipients" class="form-control" value="<?php echo html_escape($workflow['recipients'] ?? ''); ?>" placeholder="name@example.com, team@example.com">
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" id="workflow-subject" class="form-control" value="<?php echo html_escape($workflow['subject'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Message (supports {{form_name}}, {{record_id}}, {{record_table}})</label>
                            <textarea id="workflow-message" rows="5" class="form-control"><?php echo html_escape($workflow['message'] ?? ''); ?></textarea>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" id="workflow-include" <?php echo ! empty($workflow['include_data']) ? 'checked' : ''; ?>>
                            <label for="workflow-include">Append table of submitted data</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix mtop10 mbot15">
                            <h4 class="pull-left bold">Visual Form Builder</h4>
                            <div class="pull-right">
                                <button type="button" class="btn btn-default" id="ccx-add-field">
                                    <i class="fa fa-plus"></i> Add field
                                </button>
                            </div>
                        </div>
                        <p class="text-muted mtop0">Drag and drop the cards below to change layout order.</p>
                        <div id="ccx-fields" class="ccx-fields-area"></div>
                        <div class="text-center mtop20">
                            <button type="button" class="btn btn-default" id="ccx-add-text">
                                <i class="fa fa-font"></i> Text field
                            </button>
                            <button type="button" class="btn btn-default" id="ccx-add-select">
                                <i class="fa fa-list"></i> Dropdown
                            </button>
                            <button type="button" class="btn btn-default" id="ccx-add-date">
                                <i class="fa fa-calendar"></i> Date
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="fields_payload" id="fields_payload">
        <input type="hidden" name="workflow_payload" id="workflow_payload">
        <?php echo form_close(); ?>
    </div>
</div>

<style>
    .ccx-fields-area .ccx-field-card {
        border: 1px solid #e4e5e7;
        border-radius: 4px;
        background: #fff;
        margin-bottom: 15px;
        cursor: move;
    }
    .ccx-field-card.dragging {
        opacity: 0.6;
    }
    .ccx-field-card .panel-heading {
        padding: 10px 15px;
        background: #f7f7f7;
        border-bottom: 1px solid #e4e5e7;
    }
    .ccx-field-card .panel-body {
        padding: 15px;
    }
    .ccx-field-options {
        border-top: 1px dashed #ddd;
        margin-top: 10px;
        padding-top: 10px;
    }
</style>

<script>
(function () {
    const fieldsContainer = document.getElementById('ccx-fields');
    const addFieldBtn = document.getElementById('ccx-add-field');
    const addTextBtn = document.getElementById('ccx-add-text');
    const addSelectBtn = document.getElementById('ccx-add-select');
    const addDateBtn = document.getElementById('ccx-add-date');
    const fieldsPayloadInput = document.getElementById('fields_payload');
    const workflowPayloadInput = document.getElementById('workflow_payload');
    const builderForm = document.querySelector('form');
    const initialFields = <?php echo json_encode(array_values($fields ?? [])); ?>;
    const defaultWorkflow = <?php echo json_encode($workflow ?? []); ?>;
    const emptyState = document.createElement('div');
    let draggedCard = null;

    emptyState.className = 'text-center mtop20 text-muted';
    emptyState.innerText = 'No fields yet. Use the buttons above to add one.';

    const slugify = function (value) {
        return (value || '')
            .toString()
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9\s_]/g, '')
            .replace(/\s+/g, '_');
    };

    const uid = function () {
        return 'uid_' + Math.random().toString(36).substring(2, 10);
    };

    const toggleEmptyState = function () {
        if (!fieldsContainer.querySelector('.ccx-field-card')) {
            if (!fieldsContainer.contains(emptyState)) {
                fieldsContainer.appendChild(emptyState);
            }
        } else if (fieldsContainer.contains(emptyState)) {
            fieldsContainer.removeChild(emptyState);
        }
    };

    const createCard = function (field) {
        const card = document.createElement('div');
        card.className = 'ccx-field-card panel';
        card.draggable = true;
        card.dataset.uid = field.uid || uid();
        if (field.id) {
            card.dataset.id = field.id;
        }

        card.innerHTML = `
            <div class="panel-heading clearfix">
                <span class="field-title">${field.label || 'Untitled field'}</span>
                <div class="pull-right">
                    <button type="button" class="btn btn-xs btn-danger remove-field"><i class="fa fa-trash"></i></button>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mbot10">
                            <label>Label</label>
                            <input type="text" class="form-control field-label" value="${field.label || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mbot10">
                            <label>Field key</label>
                            <input type="text" class="form-control field-key" value="${field.field_key || ''}" placeholder="auto" data-manual="${field.field_key ? '1' : '0'}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mbot10">
                            <label>Type</label>
                            <select class="form-control field-type">
                                ${renderTypeOptions(field.type)}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mbot10">
                            <label>Placeholder</label>
                            <input type="text" class="form-control field-placeholder" value="${field.placeholder || ''}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="control-label">Required</label>
                        <div class="checkbox checkbox-primary mtop5">
                            <input type="checkbox" class="field-required" id="req-${card.dataset.uid}" ${field.required ? 'checked' : ''}>
                            <label for="req-${card.dataset.uid}">Required</label>
                        </div>
                    </div>
                </div>
                <div class="ccx-field-options" ${field.type === 'select' ? '' : 'style="display:none;"'}>
                    <label>Options (one per line)</label>
                    <textarea rows="4" class="form-control field-options">${(field.options || []).join('\n')}</textarea>
                </div>
            </div>
        `;

        const labelInput = card.querySelector('.field-label');
        const keyInput = card.querySelector('.field-key');
        const typeSelect = card.querySelector('.field-type');
        const requiredInput = card.querySelector('.field-required');
        const placeholderInput = card.querySelector('.field-placeholder');
        const optionsWrapper = card.querySelector('.ccx-field-options');
        const optionsInput = card.querySelector('.field-options');
        const title = card.querySelector('.field-title');
        const removeBtn = card.querySelector('.remove-field');

        labelInput.addEventListener('input', function () {
            title.innerText = labelInput.value || 'Untitled field';
            if (keyInput.dataset.manual !== '1') {
                keyInput.value = slugify(labelInput.value);
            }
        });

        keyInput.addEventListener('input', function () {
            keyInput.dataset.manual = '1';
        });

        typeSelect.addEventListener('change', function () {
            if (typeSelect.value === 'select') {
                optionsWrapper.style.display = '';
            } else {
                optionsWrapper.style.display = 'none';
            }
        });

        card.addEventListener('dragstart', function () {
            draggedCard = card;
            card.classList.add('dragging');
        });

        card.addEventListener('dragend', function () {
            card.classList.remove('dragging');
            draggedCard = null;
        });

        removeBtn.addEventListener('click', function () {
            card.remove();
            toggleEmptyState();
        });

        card._refs = {
            labelInput,
            keyInput,
            typeSelect,
            requiredInput,
            placeholderInput,
            optionsInput,
        };

        return card;
    };

    const renderTypeOptions = function (selected) {
        const types = [
            { value: 'text', label: 'Text' },
            { value: 'textarea', label: 'Paragraph' },
            { value: 'number', label: 'Number' },
            { value: 'email', label: 'Email' },
            { value: 'date', label: 'Date' },
            { value: 'select', label: 'Dropdown' },
        ];

        return types
            .map(function (type) {
                const isSelected = selected === type.value ? 'selected' : '';
                return `<option value="${type.value}" ${isSelected}>${type.label}</option>`;
            })
            .join('');
    };

    const addField = function (type) {
        const defaults = {
            uid: uid(),
            label: 'New ' + (type || 'text') + ' field',
            field_key: '',
            type: type || 'text',
            required: false,
            placeholder: '',
            options: [],
        };

        if (fieldsContainer.contains(emptyState)) {
            fieldsContainer.removeChild(emptyState);
        }

        const card = createCard(defaults);
        fieldsContainer.appendChild(card);
        card.querySelector('.field-label').focus();
    };

    fieldsContainer.addEventListener('dragover', function (event) {
        event.preventDefault();
        const targetCard = event.target.closest('.ccx-field-card');
        if (!draggedCard || !targetCard || draggedCard === targetCard) {
            return;
        }

        const rect = targetCard.getBoundingClientRect();
        const isAfter = (event.clientY - rect.top) > rect.height / 2;
        if (isAfter) {
            targetCard.parentNode.insertBefore(draggedCard, targetCard.nextSibling);
        } else {
            targetCard.parentNode.insertBefore(draggedCard, targetCard);
        }
    });

    addFieldBtn.addEventListener('click', function () {
        addField('text');
    });
    addTextBtn.addEventListener('click', function () {
        addField('text');
    });
    addSelectBtn.addEventListener('click', function () {
        addField('select');
    });
    addDateBtn.addEventListener('click', function () {
        addField('date');
    });

    builderForm.addEventListener('submit', function () {
        const payload = [];
        document.querySelectorAll('.ccx-field-card').forEach(function (card) {
            const refs = card._refs;
            payload.push({
                id: card.dataset.id ? parseInt(card.dataset.id, 10) : null,
                label: refs.labelInput.value,
                field_key: refs.keyInput.value,
                type: refs.typeSelect.value,
                required: refs.requiredInput.checked,
                placeholder: refs.placeholderInput.value,
                options: refs.optionsInput ? refs.optionsInput.value.split('\n') : [],
            });
        });

        fieldsPayloadInput.value = JSON.stringify(payload);

        const workflow = {
            recipients: document.getElementById('workflow-recipients').value,
            subject: document.getElementById('workflow-subject').value,
            message: document.getElementById('workflow-message').value,
            include_data: document.getElementById('workflow-include').checked,
        };

        workflowPayloadInput.value = JSON.stringify(workflow);
    });

    if (initialFields.length) {
        initialFields.forEach(function (field, index) {
            const config = field.configuration || {};
            field.uid = 'existing_' + (field.id || index);
            field.placeholder = config.placeholder || '';
            field.options = config.options || [];
            fieldsContainer.appendChild(createCard(field));
        });
    } else {
        fieldsContainer.appendChild(emptyState);
    }

    workflowPayloadInput.value = JSON.stringify(defaultWorkflow);
})();
</script>
<?php init_tail(); ?>
