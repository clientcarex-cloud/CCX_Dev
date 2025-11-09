<?php
$fieldValue = $condition['field'] ?? '';
$operatorValue = strtoupper($condition['operator'] ?? '=');
$valueValue = $condition['value'] ?? '';
$typeValue = strtolower($condition['type'] ?? 'string');
$operators = ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'IN', 'NOT IN'];
$types = [
    'string' => ccx_lang('ccx_condition_type_string', 'Text'),
    'number' => ccx_lang('ccx_condition_type_number', 'Number'),
    'date'   => ccx_lang('ccx_condition_type_date', 'Date'),
];
?>
<div class="ccx-condition-row tw-bg-neutral-50 tw-rounded tw-p-3 tw-mb-2">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label><?= html_escape(ccx_lang('ccx_template_condition_field', 'Column')); ?></label>
                <input type="text" name="columns[<?= html_escape($columnIndex); ?>][conditions][field][]" class="form-control" value="<?= html_escape($fieldValue); ?>" placeholder="e.g. status">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label><?= html_escape(ccx_lang('ccx_template_condition_operator', 'Operator')); ?></label>
                <select name="columns[<?= html_escape($columnIndex); ?>][conditions][operator][]" class="form-control">
                    <?php foreach ($operators as $operatorOption) { ?>
                        <option value="<?= html_escape($operatorOption); ?>" <?= $operatorValue === $operatorOption ? 'selected' : ''; ?>>
                            <?= html_escape($operatorOption); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label><?= html_escape(ccx_lang('ccx_template_condition_value', 'Value')); ?></label>
                <input type="text" name="columns[<?= html_escape($columnIndex); ?>][conditions][value][]" class="form-control" value="<?= html_escape($valueValue); ?>" placeholder="e.g. paid, 1,2,3 or {{column:col_1}}">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label><?= html_escape(ccx_lang('ccx_template_condition_type', 'Value Type')); ?></label>
                <select name="columns[<?= html_escape($columnIndex); ?>][conditions][type][]" class="form-control">
                    <?php foreach ($types as $key => $label) { ?>
                        <option value="<?= html_escape($key); ?>" <?= $typeValue === $key ? 'selected' : ''; ?>>
                            <?= html_escape($label); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
    <div class="tw-text-right">
        <button class="btn btn-danger btn-sm ccx-remove-condition">
            <i class="fa fa-times"></i>
        </button>
    </div>
</div>
