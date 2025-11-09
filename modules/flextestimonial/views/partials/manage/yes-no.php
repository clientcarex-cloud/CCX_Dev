<?php
$label = $label ?? '';
$name = $name ?? '';
$value = $value ?? '';
?>
<div class="form-group">
    <label for="<?php echo $name; ?>" class="control-label clearfix">
        <?php echo $label; ?> </label>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="y_opt_1_<?php echo $name; ?>" name="<?php echo $name; ?>" value="1" <?php echo $value == '1' ? 'checked' : ''; ?>>
        <label for="y_opt_1_<?php echo $name; ?>">
            <?php echo _flextestimonial_lang('yes'); ?>
        </label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="y_opt_2_<?php echo $name; ?>" name="<?php echo $name; ?>" value="0" <?php echo $value == '0' ? 'checked' : ''; ?>>
        <label for="y_opt_2_<?php echo $name; ?>">
            <?php echo _flextestimonial_lang('no'); ?>
        </label>
    </div>
</div>