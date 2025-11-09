<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">

            <?php

            if (isset($template_data)) {
                $requestUrl = 'mailflow/create_template/'.$template_data->id;
            } else {
                $requestUrl = 'mailflow/create_template';
            }

            echo form_open(admin_url($requestUrl));
            ?>
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="col-md-6">
                            <?php echo render_input('template_name', 'mailflow_template_name', $template_data->template_name ?? ''); ?>
                        </div>

                        <div class="col-md-6">
                            <?php echo render_input('template_subject', 'mailflow_template_subject', $template_data->template_subject ?? ''); ?>
                        </div>

						
						 <div class="col-md-12">
                            <strong><h4><?php echo _l('email_content') ?> :</h4></strong>
                            
                        </div>
						
						
                        <div class="col-md-12">
                            <?php echo render_textarea('template_content', '', $template_data->template_content ?? '', ['rows' => 10], [], '', 'tinymce'); ?>
                        </div>
						
						
						 <div class="col-md-12">
                            <strong><h4><?php echo _l('sms_content') ?> :</h4></strong>
                            
                        </div>
                        <div class="col-md-12">
                            <?php echo render_input('sms_template_id', 'mailflow_sms_template', $template_data->sms_template_id ?? ''); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo render_textarea('sms_template_content', _l('sms_template_content'), $template_data->sms_template_content ?? '', ['rows' => 10], [], '', 'sms_template_content'); ?>
							<label id="toggle_merge_fields_sms" style="cursor: pointer;color: blue">Available merge fields</label>
                        </div>
						<div class="col-md-12" id="sms_merge_fields" style="display: none;">
							{patient_name}, {mobile}, {email}, {link}
						</div>
						
						<div class="col-md-12">
                            <strong><h4><?php echo _l('whatsapp_content') ?> :</h4></strong>
                            
                        </div>
                        <div class="col-md-12">
                            <?php echo render_input('whatsapp_template_name', 'mailflow_whatsapp_template', $template_data->whatsapp_template_name ?? ''); ?>
                        </div>
                        <div class="col-md-12">
						
                            <?php echo render_textarea('whatsapp_template_content', _l('whatsapp_template_content'), $template_data->whatsapp_template_content ?? '', ['rows' => 10], [], '', 'whatsapp_template_content'); ?>
							<label id="toggle_merge_fields" style="cursor: pointer;color: blue">Available merge fields</label>
                        </div>
						
						<div class="col-md-12" id="whatsapp_merge_fields" style="display: none;">
							{patient_name}, {mobile}, {email}, {link}
						</div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    $(document).ready(function () {
        $('#toggle_merge_fields').on('click', function () {
            $('#whatsapp_merge_fields').slideToggle();
        }); 
		
		$('#toggle_merge_fields_sms').on('click', function () {
            $('#sms_merge_fields').slideToggle();
        });
    });
</script>