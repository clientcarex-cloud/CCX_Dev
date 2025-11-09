<?php
$tableValue = $join['table_name'] ?? '';
$aliasValue = $join['alias'] ?? '';
$onValue    = $join['on'] ?? '';
$typeValue  = strtoupper($join['type'] ?? 'INNER');
$types      = [
    'INNER'      => ccx_lang('ccx_template_join_type_inner', 'Inner Join'),
    'LEFT'       => ccx_lang('ccx_template_join_type_left', 'Left Join'),
    'RIGHT'      => ccx_lang('ccx_template_join_type_right', 'Right Join'),
    'LEFT OUTER' => ccx_lang('ccx_template_join_type_left_outer', 'Left Outer Join'),
    'RIGHT OUTER'=> ccx_lang('ccx_template_join_type_right_outer', 'Right Outer Join'),
];
?>
<div class="ccx-join-row tw-bg-neutral-50 tw-rounded tw-p-3 tw-mb-2">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label><?= html_escape(ccx_lang('ccx_template_join_table', 'Join Table')); ?></label>
                <select name="columns[<?= html_escape($columnIndex); ?>][joins][table_name][]" class="form-control">
                    <option value=""><?= html_escape(ccx_lang('ccx_template_field_table_placeholder', 'Select a table')); ?></option>
                    <?php foreach ($tableOptions as $tableKey => $tableLabel) { ?>
                        <option value="<?= html_escape($tableKey); ?>" <?= $tableValue === (string) $tableKey ? 'selected' : ''; ?>>
                            <?= html_escape($tableLabel); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label><?= html_escape(ccx_lang('ccx_template_join_alias', 'Alias')); ?></label>
                <input type="text" name="columns[<?= html_escape($columnIndex); ?>][joins][alias][]" class="form-control" value="<?= html_escape($aliasValue); ?>" placeholder="tbl_alias">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label><?= html_escape(ccx_lang('ccx_template_join_type', 'Type')); ?></label>
                <select name="columns[<?= html_escape($columnIndex); ?>][joins][type][]" class="form-control">
                    <?php foreach ($types as $key => $label) { ?>
                        <option value="<?= html_escape($key); ?>" <?= $typeValue === $key ? 'selected' : ''; ?>>
                            <?= html_escape($label); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label><?= html_escape(ccx_lang('ccx_template_join_on', 'Join Condition')); ?></label>
                <input type="text" name="columns[<?= html_escape($columnIndex); ?>][joins][on][]" class="form-control" value="<?= html_escape($onValue); ?>" placeholder="tbl.field = alias.field">
            </div>
        </div>
    </div>
    <div class="tw-text-right">
        <button class="btn btn-danger btn-sm ccx-remove-join" type="button">
            <i class="fa fa-times"></i>
        </button>
    </div>
</div>
