<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open(current_full_url(), ['id' => 'ccx-builder-form']); ?>
        <div class="row">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold mbot15">Builder Details</h4>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo html_escape($form['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" class="form-control" name="slug" value="<?php echo html_escape($form['slug'] ?? ''); ?>" placeholder="auto-generated if empty">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3" class="form-control"><?php echo html_escape($form['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="checkbox checkbox-primary mtop15">
                            <input type="checkbox" id="status" name="status" value="1" <?php echo empty($form) || ! empty($form['status']) ? 'checked' : ''; ?>>
                            <label for="status">Active / accepting submissions</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mtop20">
                            <i class="fa fa-save"></i> Save Builder
                        </button>
                        <a href="<?php echo admin_url('ccx_creator'); ?>" class="btn btn-default btn-block mtop10">Back</a>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold mbot10">Reusable Blocks</h4>
                        <p class="text-muted">Snapshot your favorite field groups and drop them into other builders.</p>
                        <button type="button" class="btn btn-default btn-block" id="ccx-save-block">
                            <i class="fa fa-bookmark"></i> Save current fields as block
                        </button>
                        <ul class="list-group mtop15" id="ccx-blocks-list">
                            <?php foreach ($blocks as $block) : ?>
                                <li class="list-group-item" data-block-id="<?php echo (int) $block['id']; ?>">
                                    <div class="pull-right">
                                        <button type="button" class="btn btn-xs btn-default ccx-block-insert" data-block-id="<?php echo (int) $block['id']; ?>">
                                            <i class="fa fa-level-down"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-danger ccx-block-delete" data-block-id="<?php echo (int) $block['id']; ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <strong><?php echo html_escape($block['name']); ?></strong>
                                    <?php if (! empty($block['description'])) : ?>
                                        <br><small class="text-muted"><?php echo html_escape($block['description']); ?></small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                            <?php if (empty($blocks)) : ?>
                                <li class="list-group-item text-muted text-center">No blocks yet.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <?php if (! empty($form['id'])) : ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold mbot10">Versioning</h4>
                        <p class="text-muted">Every save creates a snapshot automatically.</p>
                        <a href="<?php echo admin_url('ccx_creator/versions/' . $form['id']); ?>" class="btn btn-default btn-block mtop10">
                            <i class="fa fa-history"></i> View history
                        </a>
                        <button type="button" class="btn btn-default btn-block mtop10" id="ccx-save-template">
                            <i class="fa fa-star"></i> Save as template
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold mbot10">Template Library</h4>
                        <?php if (! empty($templateLibrary)) : ?>
                            <div class="form-group">
                                <label>Choose template</label>
                                <select id="ccx-template-picker" class="form-control">
                                    <option value="">-- Select template --</option>
                                    <?php foreach ($templateLibrary as $template) : ?>
                                        <option value="<?php echo (int) $template['id']; ?>">
                                            <?php echo html_escape($template['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" class="btn btn-default btn-block" id="ccx-apply-template">
                                <i class="fa fa-layer-group"></i> Apply template
                            </button>
                        <?php else : ?>
                            <p class="text-muted">No templates yet. Save one from any builder to reuse it.</p>
                        <?php endif; ?>
                        <a href="<?php echo admin_url('ccx_creator/templates'); ?>" class="btn btn-link btn-block">Browse library</a>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold mbot10">Documentation</h4>
                        <p class="text-muted">Need a refresher? Read playbooks covering automation, approvals, and embeds.</p>
                        <a href="<?php echo admin_url('ccx_creator/docs'); ?>" class="btn btn-default btn-block" target="_blank">
                            <i class="fa fa-book"></i> Open docs
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#ccx-tab-fields" aria-controls="ccx-tab-fields" role="tab" data-toggle="tab">Fields</a></li>
                            <li role="presentation"><a href="#ccx-tab-automation" aria-controls="ccx-tab-automation" role="tab" data-toggle="tab">Automation</a></li>
                            <li role="presentation"><a href="#ccx-tab-approvals" aria-controls="ccx-tab-approvals" role="tab" data-toggle="tab">Approvals</a></li>
                        </ul>

                        <div class="tab-content mtop15">
                            <div role="tabpanel" class="tab-pane active" id="ccx-tab-fields">
                                <div class="clearfix mtop10 mbot15">
                                    <h4 class="pull-left bold">Visual Form Builder</h4>
                                    <div class="pull-right btn-group">
                                        <button type="button" class="btn btn-default" id="ccx-add-text"><i class="fa fa-font"></i> Text</button>
                                        <button type="button" class="btn btn-default" id="ccx-add-select"><i class="fa fa-list"></i> Dropdown</button>
                                        <button type="button" class="btn btn-default" id="ccx-add-date"><i class="fa fa-calendar"></i> Date</button>
                                        <button type="button" class="btn btn-default" id="ccx-add-field"><i class="fa fa-plus"></i> Generic</button>
                                    </div>
                                </div>
                                <p class="text-muted">Drag cards to reorder. Field keys auto-generate but can be overridden.</p>
                                <div id="ccx-fields" class="ccx-fields-area"></div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="ccx-tab-automation">
                                <h4 class="bold">Submission Workflow</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Recipients (comma separated)</label>
                                            <input type="text" id="workflow-recipients" class="form-control" value="<?php echo html_escape($workflow['recipients'] ?? ''); ?>" placeholder="name@example.com, team@example.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Subject</label>
                                            <input type="text" id="workflow-subject" class="form-control" value="<?php echo html_escape($workflow['subject'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Message (supports {{form_name}}, {{record_id}}, {{record_table}})</label>
                                    <textarea id="workflow-message" rows="4" class="form-control"><?php echo html_escape($workflow['message'] ?? ''); ?></textarea>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" id="workflow-include" <?php echo ! empty($workflow['include_data']) ? 'checked' : ''; ?>>
                                    <label for="workflow-include">Append submitted data table</label>
                                </div>
                                <hr>
                                <h4 class="bold">Custom Logic</h4>
                                <p class="text-muted small">Scripts run server-side with access to <code>$ccx</code> helper:
                                    <code>$ccx->get('field_key')</code>, <code>$ccx->set('field_key', 'value')</code>, <code>$ccx->error('Message')</code>.</p>
                                <div class="form-group">
                                    <label>Before Submit Script</label>
                                    <textarea id="logic-before" rows="6" class="form-control" placeholder="$ccx->set('total', $ccx->get('qty') * $ccx->get('price'));"><?php echo html_escape($logic['before_submit'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>After Submit Script</label>
                                    <textarea id="logic-after" rows="6" class="form-control" placeholder="// Runs after record is stored."><?php echo html_escape($logic['after_submit'] ?? ''); ?></textarea>
                                </div>
                                <hr>
                                <h4 class="bold">Webhooks</h4>
                                <p class="text-muted">Notify external services when submissions happen. Include optional headers for authentication.</p>
                                <div id="ccx-webhooks-list"></div>
                                <button type="button" class="btn btn-default mtop10" id="ccx-add-webhook">
                                    <i class="fa fa-plus"></i> Add webhook
                                </button>
                                <hr>
                                <h4 class="bold">API Tokens</h4>
                                <?php if (! empty($form['id'])) : ?>
                                    <p class="text-muted">Use tokens to pull submissions into other systems (GET <?php echo site_url('ccx_creator/api/{token}'); ?>).</p>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="ccx-api-tokens-table">
                                            <thead>
                                                <tr>
                                                    <th>Label</th>
                                                    <th>Token</th>
                                                    <th>Scopes</th>
                                                    <th>Created</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-default" id="ccx-generate-token">
                                        <i class="fa fa-key"></i> Generate API Token
                                    </button>
                                <?php else : ?>
                                    <div class="alert alert-info">
                                        Save the form first to generate API tokens.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="ccx-tab-approvals">
                                <h4 class="bold">Approval Steps</h4>
                                <p class="text-muted">Define sequential checks. When a step is approved the next one activates automatically.</p>
                                <div id="ccx-approvals-list"></div>
                                <button type="button" class="btn btn-default mtop15" id="ccx-add-approval">
                                    <i class="fa fa-plus"></i> Add approval step
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="fields_payload" id="fields_payload">
        <input type="hidden" name="workflow_payload" id="workflow_payload">
        <input type="hidden" name="logic_payload" id="logic_payload">
        <input type="hidden" name="approvals_payload" id="approvals_payload">
        <input type="hidden" name="webhooks_payload" id="webhooks_payload">
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
    opacity: 0.5;
}
.ccx-field-card .panel-heading {
    padding: 10px 15px;
    background: #f7f7f7;
    border-bottom: 1px solid #e4e5e7;
}
.ccx-field-options {
    border-top: 1px dashed #ddd;
    margin-top: 10px;
    padding-top: 10px;
}
.ccx-approval-row {
    border: 1px solid #e4e5e7;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 10px;
    background: #fafafa;
    position: relative;
}
.ccx-approval-row .ccx-approval-remove {
    position: absolute;
    top: 10px;
    right: 10px;
}
</style>

<script>
(function () {
    const adminUrl = '<?php echo admin_url(); ?>';
    const fieldsContainer = document.getElementById('ccx-fields');
    const approvalsContainer = document.getElementById('ccx-approvals-list');
    const form = document.getElementById('ccx-builder-form');
    const hiddenFields = document.getElementById('fields_payload');
    const hiddenWorkflow = document.getElementById('workflow_payload');
    const hiddenLogic = document.getElementById('logic_payload');
    const hiddenApprovals = document.getElementById('approvals_payload');
    const hiddenWebhooks = document.getElementById('webhooks_payload');
    const saveBlockBtn = document.getElementById('ccx-save-block');
    const blocksList = document.getElementById('ccx-blocks-list');
    const addApprovalBtn = document.getElementById('ccx-add-approval');
    const initialFields = <?php echo json_encode(array_values($fields ?? [])); ?>;
    const initialApprovals = <?php echo json_encode(array_values($approvals ?? [])); ?>;
    const workflowDefaults = <?php echo json_encode($workflow ?? []); ?>;
    const logicDefaults = <?php echo json_encode($logic ?? []); ?>;
    const staffOptions = <?php echo json_encode($staffOptions ?? []); ?>;
    const initialWebhooks = <?php echo json_encode($webhooks ?? []); ?>;
    const apiTokens = <?php echo json_encode($apiTokens ?? []); ?>;
    const templateLibrary = <?php echo json_encode($templateLibrary ?? []); ?>;
    const formId = <?php echo isset($form['id']) ? (int) $form['id'] : 'null'; ?>;
    const builderFormBaseUrl = '<?php echo admin_url('ccx_creator/form' . (! empty($form['id']) ? '/' . (int) $form['id'] : '')); ?>';
    const emptyState = document.createElement('div');
    let draggedCard = null;

    emptyState.className = 'text-center text-muted ptop10';
    emptyState.innerText = 'No fields yet. Use the buttons above to add one.';

    const slugify = (value) =>
        (value || '')
            .toString()
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9\s_]/g, '')
            .replace(/\s+/g, '_');

    const uid = () => 'uid_' + Math.random().toString(36).substring(2, 10);

    const toggleEmptyState = () => {
        if (!fieldsContainer.querySelector('.ccx-field-card')) {
            if (!fieldsContainer.contains(emptyState)) {
                fieldsContainer.appendChild(emptyState);
            }
        } else if (fieldsContainer.contains(emptyState)) {
            fieldsContainer.removeChild(emptyState);
        }
    };

    const renderTypeOptions = (selected) => {
        const types = [
            { value: 'text', label: 'Text' },
            { value: 'textarea', label: 'Paragraph' },
            { value: 'number', label: 'Number' },
            { value: 'email', label: 'Email' },
            { value: 'date', label: 'Date' },
            { value: 'select', label: 'Dropdown' },
        ];
        return types
            .map((type) => `<option value="${type.value}" ${selected === type.value ? 'selected' : ''}>${type.label}</option>`)
            .join('');
    };

    const createFieldCard = (field) => {
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
                    <button type="button" class="btn btn-xs btn-danger ccx-field-remove"><i class="fa fa-trash"></i></button>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" class="form-control field-label" value="${field.label || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Field key</label>
                            <input type="text" class="form-control field-key" value="${field.field_key || ''}" placeholder="auto" data-manual="${field.field_key ? '1' : '0'}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control field-type">${renderTypeOptions(field.type)}</select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
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
                    <textarea rows="3" class="form-control field-options">${(field.options || []).join('\n')}</textarea>
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
        const removeBtn = card.querySelector('.ccx-field-remove');
        const title = card.querySelector('.field-title');

        labelInput.addEventListener('input', () => {
            title.innerText = labelInput.value || 'Untitled field';
            if (keyInput.dataset.manual !== '1') {
                keyInput.value = slugify(labelInput.value);
            }
        });

        keyInput.addEventListener('input', () => {
            keyInput.dataset.manual = '1';
        });

        typeSelect.addEventListener('change', () => {
            optionsWrapper.style.display = typeSelect.value === 'select' ? '' : 'none';
        });

        removeBtn.addEventListener('click', () => {
            card.remove();
            toggleEmptyState();
        });

        card.addEventListener('dragstart', () => {
            draggedCard = card;
            card.classList.add('dragging');
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            draggedCard = null;
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

    const addField = (type) => {
        if (fieldsContainer.contains(emptyState)) {
            fieldsContainer.removeChild(emptyState);
        }
        const card = createFieldCard({
            uid: uid(),
            label: 'New ' + (type || 'text') + ' field',
            field_key: '',
            type: type || 'text',
            required: false,
            placeholder: '',
            options: [],
        });
        fieldsContainer.appendChild(card);
        card.querySelector('.field-label').focus();
    };

    fieldsContainer.addEventListener('dragover', (event) => {
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

    document.getElementById('ccx-add-field').addEventListener('click', () => addField('text'));
    document.getElementById('ccx-add-text').addEventListener('click', () => addField('text'));
    document.getElementById('ccx-add-select').addEventListener('click', () => addField('select'));
    document.getElementById('ccx-add-date').addEventListener('click', () => addField('date'));

    const initializeFields = () => {
        if (!initialFields.length) {
            fieldsContainer.appendChild(emptyState);
            return;
        }
        initialFields.forEach((field, index) => {
            const config = field.configuration || {};
            field.uid = 'existing_' + (field.id || index);
            field.placeholder = config.placeholder || '';
            field.options = config.options || [];
            fieldsContainer.appendChild(createFieldCard(field));
        });
    };

    const collectFields = () => {
        const payload = [];
        fieldsContainer.querySelectorAll('.ccx-field-card').forEach((card) => {
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
        return payload;
    };

    const workflowDefaultsPayload = () => ({
        recipients: document.getElementById('workflow-recipients').value,
        subject: document.getElementById('workflow-subject').value,
        message: document.getElementById('workflow-message').value,
        include_data: document.getElementById('workflow-include').checked,
    });

    document.getElementById('workflow-recipients').value = workflowDefaults.recipients || '';
    document.getElementById('workflow-subject').value = workflowDefaults.subject || '';
    document.getElementById('workflow-message').value = workflowDefaults.message || '';
    document.getElementById('workflow-include').checked = !!workflowDefaults.include_data;
    document.getElementById('logic-before').value = logicDefaults.before_submit || '';
    document.getElementById('logic-after').value = logicDefaults.after_submit || '';

    const getCsrf = () => {
        const tokenInput = document.querySelector('input[name="csrf_token_name"]');
        return tokenInput ? { csrf_token_name: tokenInput.value } : {};
    };

    const requestBlockDefinition = (blockId, callback) => {
        $.get(adminUrl + 'ccx_creator/blocks_load/' + blockId, (response) => {
            if (response && response.success && Array.isArray(response.definition)) {
                callback(response.definition);
            } else {
                alert('Unable to load block definition.');
            }
        }, 'json');
    };

    blocksList.addEventListener('click', (event) => {
        const insertBtn = event.target.closest('.ccx-block-insert');
        if (insertBtn) {
            const blockId = insertBtn.dataset.blockId;
            requestBlockDefinition(blockId, (definition) => {
                definition.forEach((field) => {
                    field.uid = uid();
                    fieldsContainer.appendChild(createFieldCard(field));
                });
                toggleEmptyState();
            });
            return;
        }

        const deleteBtn = event.target.closest('.ccx-block-delete');
        if (deleteBtn && confirm('Delete this block from the library?')) {
            $.post(
                adminUrl + 'ccx_creator/blocks_delete/' + deleteBtn.dataset.blockId,
                getCsrf(),
                (response) => {
                    if (response && response.success) {
                        deleteBtn.closest('li').remove();
                    } else {
                        alert('Unable to delete block.');
                    }
                },
                'json'
            );
        }
    });

    saveBlockBtn.addEventListener('click', () => {
        const definition = collectFields();
        if (!definition.length) {
            alert('Add at least one field before saving a block.');
            return;
        }
        const name = prompt('Block name');
        if (!name) {
            return;
        }
        const description = prompt('Optional description') || '';

        const payload = Object.assign({
            name: name,
            description: description,
            definition: JSON.stringify(definition),
        }, getCsrf());

        $.post(adminUrl + 'ccx_creator/blocks_save', payload, (response) => {
            if (!response || !response.success) {
                alert('Unable to save block, please try again.');
                return;
            }
            location.reload();
        }, 'json');
    });

    const createApprovalRow = (step) => {
        const row = document.createElement('div');
        row.className = 'ccx-approval-row';
        if (step.id) {
            row.dataset.id = step.id;
        }

        row.innerHTML = `
            <button type="button" class="btn btn-xs btn-danger ccx-approval-remove"><i class="fa fa-times"></i></button>
            <div class="form-group">
                <label>Step label</label>
                <input type="text" class="form-control approval-label" value="${step.label || ''}" placeholder="e.g. Finance review">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Assignee type</label>
                        <select class="form-control approval-type">
                            <option value="any" ${step.assignee_type === 'any' ? 'selected' : ''}>Anyone with access</option>
                            <option value="staff" ${step.assignee_type === 'staff' ? 'selected' : ''}>Specific staff</option>
                            <option value="role" ${step.assignee_type === 'role' ? 'selected' : ''}>Role (all staff)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group approval-staff-group" style="${step.assignee_type === 'staff' ? '' : 'display:none;'}">
                        <label>Staff member</label>
                        <select class="form-control approval-staff">
                            <option value="">Select staff</option>
                            ${staffOptions.map((member) => `<option value="${member.id}" ${parseInt(step.assignee_value || 0, 10) === member.id ? 'selected' : ''}>${member.name}</option>`).join('')}
                        </select>
                    </div>
                </div>
            </div>
        `;

        const removeBtn = row.querySelector('.ccx-approval-remove');
        const typeSelect = row.querySelector('.approval-type');
        const staffGroup = row.querySelector('.approval-staff-group');

        removeBtn.addEventListener('click', () => row.remove());

        typeSelect.addEventListener('change', () => {
            if (typeSelect.value === 'staff') {
                staffGroup.style.display = '';
            } else {
                staffGroup.style.display = 'none';
            }
        });

        row._refs = {
            label: row.querySelector('.approval-label'),
            type: row.querySelector('.approval-type'),
            staff: row.querySelector('.approval-staff'),
        };

        return row;
    };

    const addApprovalRow = (step = {}) => {
        approvalsContainer.appendChild(createApprovalRow(step));
    };

    const collectApprovals = () => {
        const payload = [];
        approvalsContainer.querySelectorAll('.ccx-approval-row').forEach((row) => {
            const refs = row._refs;
            payload.push({
                id: row.dataset.id ? parseInt(row.dataset.id, 10) : null,
                label: refs.label.value,
                assignee_type: refs.type.value,
                assignee_value: refs.type.value === 'staff' ? refs.staff.value : (refs.type.value === 'role' ? 'staff' : null),
            });
        });
        return payload;
    };

    const webhooksContainer = document.getElementById('ccx-webhooks-list');
    const addWebhookBtn = document.getElementById('ccx-add-webhook');

    const createWebhookRow = (hook = {}) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'panel panel-default';
        wrapper.innerHTML = `
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>URL</label>
                            <input type="text" class="form-control webhook-url" value="${hook.url || ''}" placeholder="https://example.com/webhooks">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Event</label>
                            <select class="form-control webhook-event">
                                <option value="on_submit" ${hook.event === 'on_submit' ? 'selected' : ''}>On submit</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1 text-right">
                        <button type="button" class="btn btn-danger btn-sm ccx-webhook-remove" style="margin-top:25px;"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Headers (JSON or key:value per line)</label>
                            <textarea rows="2" class="form-control webhook-headers">${formatHeadersField(hook.headers)}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="checkbox checkbox-primary" style="margin-top:25px;">
                            <input type="checkbox" class="webhook-active" ${hook.is_active === 0 ? '' : 'checked'} id="webhook-active-${Math.random().toString(36).substring(2)}">
                            <label>Active</label>
                        </div>
                    </div>
                </div>
            </div>
        `;

        wrapper.querySelector('.ccx-webhook-remove').addEventListener('click', () => wrapper.remove());

        wrapper._refs = {
            url: wrapper.querySelector('.webhook-url'),
            event: wrapper.querySelector('.webhook-event'),
            headers: wrapper.querySelector('.webhook-headers'),
            active: wrapper.querySelector('.webhook-active'),
        };

        return wrapper;
    };

    const formatHeadersField = (headers) => {
        if (!headers) {
            return '';
        }
        if (Array.isArray(headers)) {
            return headers.join('\n');
        }
        if (typeof headers === 'object') {
            return Object.entries(headers).map(([key, value]) => `${key}: ${value}`).join('\n');
        }
        return headers;
    };

    const parseHeadersField = (value) => {
        if (!value) {
            return [];
        }
        const lines = value.split('\n');
        const map = {};
        lines.forEach((line) => {
            const parts = line.split(':');
            if (parts.length >= 2) {
                const key = parts.shift().trim();
                map[key] = parts.join(':').trim();
            }
        });
        return map;
    };

    const collectWebhooks = () => {
        const payload = [];
        webhooksContainer.querySelectorAll('.panel').forEach((row) => {
            const refs = row._refs;
            payload.push({
                url: refs.url.value,
                event: refs.event.value,
                headers: parseHeadersField(refs.headers.value),
                is_active: refs.active.checked ? 1 : 0,
            });
        });
        return payload;
    };

    const renderWebhooks = () => {
        if (!initialWebhooks.length) {
            webhooksContainer.innerHTML = '<p class="text-muted">No webhooks yet.</p>';
            return;
        }
        webhooksContainer.innerHTML = '';
        initialWebhooks.forEach((hook) => {
            const row = createWebhookRow(hook);
            webhooksContainer.appendChild(row);
        });
    };

    addWebhookBtn.addEventListener('click', () => {
        if (webhooksContainer.querySelector('p')) {
            webhooksContainer.innerHTML = '';
        }
        webhooksContainer.appendChild(createWebhookRow({ event: 'on_submit', is_active: 1 }));
    });

    addApprovalBtn.addEventListener('click', () => addApprovalRow({ assignee_type: 'any' }));

    initialApprovals.forEach((step) => addApprovalRow(step));

    renderWebhooks();

    const tokensTable = document.querySelector('#ccx-api-tokens-table tbody');
    const formatTokenRow = (token) => `
        <tr data-token-id="${token.id}">
            <td>${token.label}</td>
            <td><code>${token.token}</code></td>
            <td>${Array.isArray(token.scopes) ? token.scopes.join(', ') : (token.scopes || 'read')}</td>
            <td>${token.created_at || ''}</td>
            <td class="text-right">
                <button type="button" class="btn btn-xs btn-danger ccx-token-revoke">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>`;

    const hydrateTokenTable = () => {
        if (!tokensTable) {
            return;
        }
        tokensTable.innerHTML = '';
        apiTokens.forEach((token) => {
            if (typeof token.scopes === 'string') {
                try {
                    token.scopes = JSON.parse(token.scopes);
                } catch (e) {
                    token.scopes = ['read'];
                }
            }
            tokensTable.insertAdjacentHTML('beforeend', formatTokenRow(token));
        });
    };

    hydrateTokenTable();

    const bindTokenActions = () => {
        if (!tokensTable) {
            return;
        }
        tokensTable.addEventListener('click', function (event) {
            const btn = event.target.closest('.ccx-token-revoke');
            if (!btn) {
                return;
            }
            const row = btn.closest('tr');
            const tokenId = row.dataset.tokenId;
            if (!confirm('Revoke this API token?')) {
                return;
            }
            $.post(adminUrl + 'ccx_creator/revoke_token/' + tokenId, getCsrf(), function (response) {
                if (response && response.success) {
                    row.remove();
                } else {
                    alert('Unable to revoke token.');
                }
            }, 'json');
        });
    };

    bindTokenActions();

    const generateTokenBtn = document.getElementById('ccx-generate-token');
    if (generateTokenBtn && formId) {
        generateTokenBtn.addEventListener('click', () => {
            const label = prompt('Token label', 'Integration #' + Math.floor(Math.random() * 1000));
            if (!label) {
                return;
            }
            const payload = Object.assign({ label: label }, getCsrf());
            $.post(adminUrl + 'ccx_creator/create_token/' + formId, payload, function (response) {
                if (!response || !response.success) {
                    alert('Unable to create token.');
                    return;
                }
                apiTokens.unshift(response.token);
                tokensTable.insertAdjacentHTML('afterbegin', formatTokenRow(response.token));
            }, 'json');
        });
    }

    const templatePicker = document.getElementById('ccx-template-picker');
    const applyTemplateBtn = document.getElementById('ccx-apply-template');
    const saveTemplateBtn = document.getElementById('ccx-save-template');

    if (saveTemplateBtn && formId) {
        saveTemplateBtn.addEventListener('click', () => {
            const name = prompt('Template name');
            if (!name) {
                return;
            }
            const description = prompt('Description (optional)', '') || '';
            const category = prompt('Category (optional)', '') || '';
            const tags = prompt('Tags (comma separated)', '') || '';

            const payload = Object.assign({
                name: name,
                description: description,
                category: category,
                tags: tags,
            }, getCsrf());

            $.post(adminUrl + 'ccx_creator/template_save/' + formId, payload, function (response) {
                if (response && response.success) {
                    alert('Template saved.');
                } else {
                    alert('Unable to save template.');
                }
            }, 'json');
        });
    }

    if (applyTemplateBtn && templatePicker) {
        applyTemplateBtn.addEventListener('click', () => {
            if (!templatePicker.value) {
                alert('Select a template first.');
                return;
            }
            if (formId && !confirm('Apply template and overwrite the current builder? Unsaved changes will be lost.')) {
                return;
            }

            const target = formId
                ? builderFormBaseUrl + '?template=' + templatePicker.value
                : adminUrl + 'ccx_creator/form?template=' + templatePicker.value;

            window.location = target;
        });
    }

    initializeFields();
    toggleEmptyState();

    form.addEventListener('submit', () => {
        hiddenFields.value = JSON.stringify(collectFields());
        hiddenWorkflow.value = JSON.stringify(workflowDefaultsPayload());
        hiddenLogic.value = JSON.stringify({
            before_submit: document.getElementById('logic-before').value,
            after_submit: document.getElementById('logic-after').value,
        });
        hiddenApprovals.value = JSON.stringify(collectApprovals());
        hiddenWebhooks.value = JSON.stringify(collectWebhooks());
    });
})();
</script>
<?php init_tail(); ?>
