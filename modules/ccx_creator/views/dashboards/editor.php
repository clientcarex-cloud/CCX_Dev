<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open(current_full_url(), ['id' => 'ccx-dashboard-form']); ?>
        <div class="row">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold mbot15">Dashboard Details</h4>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo html_escape($dashboard['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" class="form-control" value="<?php echo html_escape($dashboard['slug'] ?? ''); ?>" placeholder="auto-generated if empty">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3" class="form-control"><?php echo html_escape($dashboard['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Visibility</label>
                            <select name="visibility" id="dashboard-visibility" class="form-control">
                                <?php $visibility = $dashboard['visibility'] ?? 'private'; ?>
                                <option value="private" <?php echo $visibility === 'private' ? 'selected' : ''; ?>>Private (admins only)</option>
                                <option value="internal" <?php echo $visibility === 'internal' ? 'selected' : ''; ?>>Internal (share link)</option>
                                <option value="public" <?php echo $visibility === 'public' ? 'selected' : ''; ?>>Public (embed)</option>
                            </select>
                        </div>
                        <div class="form-group <?php echo ($visibility ?? 'private') === 'private' ? 'hide' : ''; ?>" id="share-wrapper">
                            <label>Share link</label>
                            <?php if (! empty($shareToken)) : ?>
                                <div class="input-group">
                                    <input type="text" readonly class="form-control" id="share-link" value="<?php echo site_url('ccx_creator_public/dashboard/' . $shareToken); ?>">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" id="ccx-copy-link"><i class="fa fa-copy"></i></button>
                                    </span>
                                </div>
                            <?php else : ?>
                                <p class="text-muted">Save the dashboard, then generate a link.</p>
                            <?php endif; ?>
                            <?php if (! empty($dashboard['id'])) : ?>
                                <button type="button" class="btn btn-default btn-block mtop10" id="ccx-generate-share" data-id="<?php echo (int) $dashboard['id']; ?>">
                                    <i class="fa fa-share-alt"></i> Generate share link
                                </button>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mtop15">
                            <i class="fa fa-save"></i> Save dashboard
                        </button>
                        <a href="<?php echo admin_url('ccx_creator/dashboards'); ?>" class="btn btn-default btn-block mtop10">Back</a>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix mtop10 mbot15">
                            <h4 class="pull-left bold">Widgets</h4>
                            <div class="pull-right">
                                <button type="button" class="btn btn-default" id="ccx-add-widget">
                                    <i class="fa fa-plus"></i> Add widget
                                </button>
                            </div>
                        </div>
                        <p class="text-muted">Combine stats and tables from any CCX Creator form. Drag cards to reorder.</p>
                        <div id="ccx-widgets"></div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="widgets_payload" id="widgets_payload">
        <?php echo form_close(); ?>
    </div>
</div>

<style>
.ccx-widget-card {
    border: 1px solid #e4e5e7;
    border-radius: 4px;
    background: #fff;
    margin-bottom: 15px;
    cursor: move;
}
.ccx-widget-card .panel-heading {
    padding: 10px 15px;
    background: #f7f7f7;
    border-bottom: 1px solid #e4e5e7;
}
.ccx-widget-card.dragging {
    opacity: 0.5;
}
</style>

<script>
(function () {
    const adminUrl = '<?php echo admin_url(); ?>';
    const dashboardId = <?php echo isset($dashboard['id']) ? (int) $dashboard['id'] : 'null'; ?>;
    const widgetsContainer = document.getElementById('ccx-widgets');
    const addWidgetBtn = document.getElementById('ccx-add-widget');
    const hiddenWidgets = document.getElementById('widgets_payload');
    const visibilitySelect = document.getElementById('dashboard-visibility');
    const shareWrapper = document.getElementById('share-wrapper');
    const initialWidgets = <?php echo json_encode(array_values($widgets ?? [])); ?>;
    const formsConfig = <?php echo json_encode(array_values($forms ?? [])); ?>;
    const dashboardForm = document.getElementById('ccx-dashboard-form');
    let draggedCard = null;

    const getCsrf = () => {
        const tokenInput = document.querySelector('input[name="csrf_token_name"]');
        return tokenInput ? { csrf_token_name: tokenInput.value } : {};
    };

    const getFieldsForForm = (formId) => {
        const form = formsConfig.find((item) => parseInt(item.id, 10) === parseInt(formId, 10));
        if (!form || !Array.isArray(form.fields)) {
            return [];
        }
        return form.fields;
    };

    const renderFieldOptions = (formId, selectedKey) => {
        const options = getFieldsForForm(formId)
            .map((field) => `<option value="${field.field_key}" ${field.field_key === selectedKey ? 'selected' : ''}>${field.label}</option>`)
            .join('');
        return `<option value="">-- Any field --</option>${options}`;
    };

    const createWidgetCard = (widget) => {
        const card = document.createElement('div');
        card.className = 'ccx-widget-card panel';
        card.draggable = true;
        card.dataset.uid = widget.uid || 'widget_' + Math.random().toString(36).substring(2);

        card.innerHTML = `
            <div class="panel-heading clearfix">
                <span class="widget-title">${widget.title || 'Untitled widget'}</span>
                <div class="pull-right">
                    <button type="button" class="btn btn-xs btn-danger ccx-widget-remove"><i class="fa fa-trash"></i></button>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" class="form-control widget-title-input" value="${widget.title || ''}" placeholder="e.g. Pending approvals">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Form</label>
                            <select class="form-control widget-form">
                                ${formsConfig.map((form) => `<option value="${form.id}" ${parseInt(widget.form_id, 10) === parseInt(form.id, 10) ? 'selected' : ''}>${form.name}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control widget-type">
                                <option value="stat" ${widget.type === 'table' ? '' : 'selected'}>Stat</option>
                                <option value="table" ${widget.type === 'table' ? 'selected' : ''}>Table</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row widget-stat-options" ${widget.type === 'table' ? 'style="display:none;"' : ''}>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Metric</label>
                            <select class="form-control widget-metric">
                                <option value="count" ${widget.metric === 'count' ? 'selected' : ''}>Count</option>
                                <option value="sum" ${widget.metric === 'sum' ? 'selected' : ''}>Sum</option>
                                <option value="avg" ${widget.metric === 'avg' ? 'selected' : ''}>Average</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Field</label>
                            <select class="form-control widget-field">
                                ${renderFieldOptions(widget.form_id, widget.field_key)}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Color</label>
                            <input type="color" class="form-control widget-color" value="${widget.color || '#4a90e2'}">
                        </div>
                    </div>
                </div>
                <div class="row widget-table-options" ${widget.type === 'table' ? '' : 'style="display:none;"'}>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Show rows</label>
                            <input type="number" min="1" max="50" class="form-control widget-limit" value="${widget.limit || 5}">
                        </div>
                    </div>
                </div>
            </div>
        `;

        const titleInput = card.querySelector('.widget-title-input');
        const formSelect = card.querySelector('.widget-form');
        const typeSelect = card.querySelector('.widget-type');
        const metricSelect = card.querySelector('.widget-metric');
        const fieldSelect = card.querySelector('.widget-field');
        const colorInput = card.querySelector('.widget-color');
        const limitInput = card.querySelector('.widget-limit');
        const removeBtn = card.querySelector('.ccx-widget-remove');
        const titleLabel = card.querySelector('.widget-title');
        const statOptions = card.querySelector('.widget-stat-options');
        const tableOptions = card.querySelector('.widget-table-options');

        titleInput.addEventListener('input', () => {
            titleLabel.innerText = titleInput.value || 'Untitled widget';
        });

        formSelect.addEventListener('change', () => {
            fieldSelect.innerHTML = renderFieldOptions(formSelect.value, '');
        });

        typeSelect.addEventListener('change', () => {
            if (typeSelect.value === 'table') {
                statOptions.style.display = 'none';
                tableOptions.style.display = '';
            } else {
                statOptions.style.display = '';
                tableOptions.style.display = 'none';
            }
        });

        removeBtn.addEventListener('click', () => card.remove());

        card.addEventListener('dragstart', () => {
            draggedCard = card;
            card.classList.add('dragging');
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            draggedCard = null;
        });

        card._refs = {
            titleInput,
            formSelect,
            typeSelect,
            metricSelect,
            fieldSelect,
            colorInput,
            limitInput,
        };

        return card;
    };

    const addWidget = (widget = {}) => {
        if (!widget.form_id && formsConfig.length) {
            widget.form_id = formsConfig[0].id;
        }
        const card = createWidgetCard(Object.assign({
            title: '',
            type: 'stat',
            metric: 'count',
            color: '#4a90e2',
            limit: 5,
        }, widget));
        widgetsContainer.appendChild(card);
    };

    widgetsContainer.addEventListener('dragover', (event) => {
        event.preventDefault();
        const targetCard = event.target.closest('.ccx-widget-card');
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

    addWidgetBtn.addEventListener('click', () => addWidget());

    if (initialWidgets.length) {
        initialWidgets.forEach((widget) => addWidget(widget));
    } else {
        addWidget();
    }

    const collectWidgets = () => {
        const payload = [];
        widgetsContainer.querySelectorAll('.ccx-widget-card').forEach((card) => {
            const refs = card._refs;
            payload.push({
                title: refs.titleInput.value,
                form_id: refs.formSelect.value,
                type: refs.typeSelect.value,
                metric: refs.metricSelect ? refs.metricSelect.value : 'count',
                field_key: refs.fieldSelect ? refs.fieldSelect.value : null,
                color: refs.colorInput ? refs.colorInput.value : '#4a90e2',
                limit: refs.limitInput ? parseInt(refs.limitInput.value || 5, 10) : 5,
            });
        });

        return payload.filter((widget) => parseInt(widget.form_id, 10) > 0);
    };

    dashboardForm.addEventListener('submit', () => {
        hiddenWidgets.value = JSON.stringify(collectWidgets());
    });

    visibilitySelect.addEventListener('change', () => {
        if (visibilitySelect.value === 'private') {
            shareWrapper.classList.add('hide');
        } else {
            shareWrapper.classList.remove('hide');
        }
    });

    const attachCopyHandler = () => {
        const copyBtn = document.getElementById('ccx-copy-link');
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                const input = document.getElementById('share-link');
                input.select();
                document.execCommand('copy');
            });
        }
    };

    attachCopyHandler();

    const ensureShareInput = (url) => {
        let input = document.getElementById('share-link');
        if (!input) {
            const group = document.createElement('div');
            group.className = 'input-group mtop10';
            group.innerHTML = `
                <input type="text" readonly class="form-control" id="share-link" value="${url}">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="button" id="ccx-copy-link"><i class="fa fa-copy"></i></button>
                </span>`;
            shareWrapper.insertBefore(group, shareWrapper.lastElementChild);
            attachCopyHandler();
        } else {
            input.value = url;
        }
    };

    const shareBtn = document.getElementById('ccx-generate-share');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            $.post(adminUrl + 'ccx_creator/dashboard_share/' + this.dataset.id, getCsrf(), function (response) {
                if (!response || !response.success) {
                    alert('Unable to generate share link.');
                    return;
                }
                ensureShareInput(response.url);
                alert('Public link: ' + response.url);
            }, 'json');
        });
    }
})();
</script>
<?php init_tail(); ?>
