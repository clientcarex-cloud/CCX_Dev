<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix mtop5 mbot20">
                            <h4 class="pull-left bold"><?php echo html_escape($form['name']); ?> · Submission</h4>
                            <div class="pull-right">
                                <a href="<?php echo admin_url('ccx_creator/records/' . $form['id']); ?>" class="btn btn-default">
                                    <i class="fa fa-long-arrow-left"></i> Back to submissions
                                </a>
                            </div>
                        </div>
                        <p class="text-muted">Fill the builder-driven fields below. Required inputs are marked with *.</p>

                        <?php echo form_open(current_full_url()); ?>
                        <?php foreach ($fields as $field) :
                            $config = json_decode($field['configuration'] ?? '[]', true) ?? [];
                            $placeholder = $config['placeholder'] ?? '';
                            $isRequired = ! empty($field['required']);
                            $options = $config['options'] ?? [];
                            ?>
                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo html_escape($field['label']); ?>
                                    <?php if ($isRequired) : ?>
                                        <span class="text-danger">*</span>
                                    <?php endif; ?>
                                </label>
                                <?php echo render_field_input($field, $placeholder, $options, $isRequired); ?>
                            </div>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-send"></i> Submit
                        </button>
                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function render_field_input($field, $placeholder, $options, $required)
{
    $key = html_escape($field['field_key']);
    $requiredAttr = $required ? 'required' : '';

    switch ($field['type']) {
        case 'textarea':
            return '<textarea name="' . $key . '" rows="4" class="form-control" placeholder="' . html_escape($placeholder) . '" ' . $requiredAttr . '></textarea>';
        case 'number':
            return '<input type="number" name="' . $key . '" class="form-control" placeholder="' . html_escape($placeholder) . '" ' . $requiredAttr . '>';
        case 'email':
            return '<input type="email" name="' . $key . '" class="form-control" placeholder="' . html_escape($placeholder) . '" ' . $requiredAttr . '>';
        case 'date':
            return '<input type="date" name="' . $key . '" class="form-control" ' . $requiredAttr . '>';
        case 'select':
            $html = '<select name="' . $key . '" class="form-control" ' . $requiredAttr . '>';
            $html .= '<option value="">-- Select --</option>';
            foreach ($options as $option) {
                $html .= '<option value="' . html_escape($option) . '">' . html_escape($option) . '</option>';
            }
            $html .= '</select>';
            return $html;
        default:
            return '<input type="text" name="' . $key . '" class="form-control" placeholder="' . html_escape($placeholder) . '" ' . $requiredAttr . '>';
    }
}
?>
<?php init_tail(); ?>
