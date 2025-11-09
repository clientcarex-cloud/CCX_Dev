<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-12 mbot10">
                <h2>
                    <?php echo $title; ?>
                </h2>
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                            <div class="scroller arrow-left" style="display: none;"><i class="fa fa-angle-left"></i>
                            </div>
                            <div class="scroller arrow-right" style="display: none;"><i class="fa fa-angle-right"></i>
                            </div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#customers" aria-controls="customers" role="tab" data-toggle="tab"
                                           aria-expanded="true"><?php echo _l('customers'); ?></a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#leads" aria-controls="leads" role="tab" data-toggle="tab"
                                           aria-expanded="true"><?php echo _l('leads'); ?></a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#staff" aria-controls="staff" role="tab" data-toggle="tab"
                                           aria-expanded="true"><?php echo _l('staff'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <?php echo form_open(admin_url('mailflow/sendEmails'), ['id' => 'mailflow-form']); ?>
                        <div class="tab-content" style="background-color: lavender;">

                            <div class="campaign-settings">
							<div class="row">
                                <div class="col-md-12">
                                    <?php echo render_select('send_newsletter_to[]', [['id' => 'customers', 'name' => _l('customers')], ['id' => 'leads', 'name' => _l('leads')], ['id' => 'staff', 'name' => _l('staff')]], ['id', 'name'], 'emailflow_send_newsletter_to', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_yes_no_option('send_campaign_to_emails', 'mailflow_send_campaign_to_emails'); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_yes_no_option('send_campaign_to_sms', 'mailflow_send_campaign_to_sms'); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_yes_no_option('send_campaign_to_whatsapp', 'mailflow_send_campaign_to_whatsapp'); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_select('email_smtp_integration', mailflow_get_email_integrations(), ['id', 'name'], 'mailflow_send_emails_with_integration', '', ['data-actions-box' => true], [], '', '', false); ?>
                                </div>

                                <div class="col-md-6">
                                    <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1"
                                       data-toggle="tooltip"
                                       data-title="<?php echo _l('mailflow_schedule_campaign_input_tooltip'); ?>"></i>
                                    <?php echo render_datetime_input('schedule_campaign', 'mailflow_schedule_campaign_input'); ?>
                                </div>
                            </div>
                            </div>

                            <hr class="-tw-mx-4"/>

                            <div role="tabpanel" class="tab-pane active" id="customers">

                                <div class="col-md-12">
                                    <?php echo render_select('customers_status', [['id' => 'active', 'name' => _l('mailflow_customers_active')], ['id' => 'inactive', 'name' => _l('mailflow_customers_inactive')]], ['id', 'name'], 'emailflow_customers_db_status', '', [], [], '', '', false); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_select('customer_groups[]', $clientGroups, ['id', 'name'], 'emailflow_customers_group_list', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_select('customers_country[]', get_all_countries(), ['country_id', ['short_name'], 'iso2'], 'mailflow_customers_country', '', ['multiple' => true], [], '', '', false); ?>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="leads">

                                <div class="col-md-12">
                                    <?php echo render_select('lead_groups[]', $lead_statuses, ['id', 'name'], 'emailflow_leads_group_list', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_select('leads_source[]', $lead_sources, ['id', 'name'], 'mailflow_lead_sources', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_select('leads_assigned_to_staff[]', $staff_members, ['staffid', ['firstname', 'lastname']], 'mailflow_leads_assiged_to', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                                <div class="col-md-12">
                                    <?php echo render_select('leads_country[]', get_all_countries(), ['country_id', ['short_name'], 'iso2'], 'mailflow_leads_country', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                            </div>

                            <div role="tabpanel" class="tab-pane" id="staff">

                                <div class="row">
                                <div class="col-md-12">
                                    <?php echo render_select('roles[]', $roles, ['roleid', 'name'], _l('staff_roles'), '', ['multiple' => true], [], '', '', false); ?>
                                </div>
                                </div>


                            </div>
                        </div>

                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">

                        <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                            <div class="scroller arrow-left" style="display: none;"><i class="fa fa-angle-left"></i>
                            </div>
                            <div class="scroller arrow-right" style="display: none;"><i class="fa fa-angle-right"></i>
                            </div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#email_list" aria-controls="email_list" role="tab" data-toggle="tab"
                                           aria-expanded="true"><?php echo _l('mailflow_emails_list'); ?></a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#sms_list" aria-controls="sms_list" role="tab" data-toggle="tab"
                                           aria-expanded="true"><?php echo _l('mailflow_phone_numbers_list'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content">

                            <div role="tabpanel" class="tab-pane active" id="email_list">

                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">
                                            <?php echo _l('mailflow_total_customers_emails'); ?>
                                            <span id="totalCustomers" class="badge badge-primary">0</span>
                                            <?php echo _l('mailflow_total_leads_emails'); ?>
                                            <span id="totalLeads" class="badge badge-success">0</span>
											
											<?php echo _l('mailflow_total_staff_emails'); ?>
                                            <span id="totalStaff" class="badge badge-success">0</span>
                                        </h4>
                                    </div>
                                </div>
                                <hr class="hr">
                                <table class="table table-hover tw-text-sm" id="email-table">
                                    <thead style="background-color: lavender">
                                    <tr>
                                        <th><?php echo _l('mailflow_emails_list'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>

                            </div>

                            <div role="tabpanel" class="tab-pane" id="sms_list">

                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">
                                            <?php echo _l('mailflow_total_customers_phone_numbers'); ?>
                                            <span id="totalSmsCustomers" class="badge badge-primary">0</span>
                                            <?php echo _l('mailflow_total_leads_phone_numbers'); ?>
                                            <span id="totalSmsLeads" class="badge badge-success">0</span>
                                        </h4>
                                    </div>
                                </div>
                                <hr class="hr">
                                <table class="table table-hover tw-text-sm" id="sms-table">
                                    <thead style="background-color: lavender">
                                    <tr>
                                        <th><?php echo _l('mailflow_phone_numbers_list'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>

                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <div class="col-md-6">

                <div class="panel_s">
                    <div class="panel-body">

                        <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                            <div class="scroller arrow-left" style="display: none;"><i class="fa fa-angle-left"></i>
                            </div>
                            <div class="scroller arrow-right" style="display: none;"><i class="fa fa-angle-right"></i>
                            </div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#email_campaign" aria-controls="email_campaign" role="tab"
                                           data-toggle="tab"
                                           aria-expanded="true"><?php echo _l('mailflow_email_campaign'); ?></a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#sms_campaign" aria-controls="sms_campaign" role="tab"
                                           data-toggle="tab"
                                           aria-expanded="true"><?php echo _l('mailflow_sms_campaign'); ?></a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#whatsapp_campaign" aria-controls="whatsapp_campaign" role="tab"
                                           data-toggle="tab"
                                           aria-expanded="true"><?php echo _l('mailflow_whatsapp_campaign'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="email_campaign">

                                <div class="col-md-12">
                                    <?php echo render_select('newsletter_template', $template_list, ['id', 'template_name'], 'mailflow_select_template'); ?>
                                    <hr>
                                </div>

                                <div class="col-md-12">
                                    <?php echo render_input('email_subject', 'mailflow_email_subject'); ?>
                                </div>

                                <div class="col-md-12">
                                    <?php echo render_textarea('email_content', '', '', ['rows' => 10], [], '', 'tinymce'); ?>
                                </div>

                                <div class="col-md-12">
                                    <p class="bold text-right no-mbot"><a href="#"
                                                                          onclick="slideToggle('.avilable_merge_fields'); return false;"><?php echo _l('available_merge_fields'); ?></a>
                                    </p>
                                    <div class=" avilable_merge_fields mtop15 hide">
                                        <ul class="list-group">
                                            <?php
                                            echo '<li class="list-group-item"><b>Unsubscribe Link</b>  <a href="#" class="pull-right" onclick="insert_merge_field(this); return false">{{unsubscribe_link}}</a></li>';
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="sms_campaign">

                                <div class="col-md-12">
                                    <?php
                                    if ($sms_active_integration === false) {
                                        ?>
                                        <div class="alert alert-info"><?php echo _l('mailflow_no_active_sms_provider'); ?></div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="alert alert-success"><?php echo _l('mailflow_current_active_sms_provider_is', $sms_active_integration['name']); ?></div>
                                        <?php
                                    }
                                    ?>
                                </div>

                                <div class="col-md-12">
                                    <?php echo render_select('sms_template', $template_list, ['id', 'template_name'], 'mailflow_select_template'); ?>
                                    <hr>
                                </div>

                                <div class="col-md-12">
									 <?php echo render_input('sms_template_id', 'sms_template_id'); ?>
                                </div>
                                <div class="col-md-12">
                                    <?php echo render_textarea('sms_template_content', _l('sms_template_content'), '', ['rows' => 10, 'id' => 'sms_template_content']); ?>
                                </div>

                            </div>
                            <div role="tabpanel" class="tab-pane" id="whatsapp_campaign">

                                <div class="col-md-12">
                                   
									<div class="alert alert-success"><?php echo _l('whatsapp_campaign'); ?></div>
                                </div>

                                <div class="col-md-12">
                                    <?php echo render_select('whatsapp_template', $template_list, ['id', 'template_name'], 'mailflow_select_template'); ?>
                                    <hr>
                                </div>

                                 <div class="col-md-12">
									 <?php echo render_input('whatsapp_template_name', 'whatsapp_template_name'); ?>
                                </div>
                                <div class="col-md-12">
                                    <?php echo render_textarea('whatsapp_template_content', _l('whatsapp_template_content'), '', ['rows' => 10, 'id' => 'whatsapp_template_content']); ?>
                                </div>

                            </div>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit"
                                    class="btn btn-primary pull-right sendNewsletter"><?php echo _l('mailflow_sends_newsletter'); ?></button>
                        </div>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php echo _l('mailflow_send_test_email_heading'); ?></h4>
                        <p class="text-muted"><?php echo _l('mailflow_settings_send_test_email_string'); ?></p>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="email" class="form-control" name="test_email" data-ays-ignore="true"
                                       placeholder="<?php echo _l('mailflow_settings_send_test_email_string'); ?>">
                                <div class="input-group-btn">
                                    <button type="button" onclick="testEmailTemplate()" class="btn btn-info">Test
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="hr">
                    <div class="panel-body">
                        <h4><?php echo _l('mailflow_send_test_sms_heading'); ?></h4>
                        <p class="text-muted"><?php echo _l('mailflow_settings_send_test_sms_string'); ?></p>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="tel" class="form-control" name="test_sms" data-ays-ignore="true"
                                       placeholder="<?php echo _l('mailflow_settings_send_test_sms_string'); ?>">
                                <div class="input-group-btn">
                                    <button type="button" onclick="testSmsTemplate()" class="btn btn-info">Test SMS</button>
                                </div>
                            </div>
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
    'use strict';

    $(document).ready(function () {

        $("#mailflow-form").appFormValidator({
            rules: {
                'send_newsletter_to[]': "required",
                'settings[send_campaign_to_emails]': "required",
                'settings[send_campaign_to_sms]': "required",
            }
        });

        sendRequest();

        var formData = $('#mailflow-form').serialize();

        $('#mailflow-form input, #mailflow-form select').on('input change', function () {
            sendRequest();
        });

        $('#newsletter_template').on('input change', function () {
            let template_id = $('#newsletter_template').val();
            useTemplate(template_id);
        });

        $('#sms_template').on('input change', function () {
            let template_id = $('#sms_template').val();
            useSmsTemplate(template_id);
        });

        $('#whatsapp_template').on('input change', function () {
            let template_id = $('#whatsapp_template').val();
            useWhatsAppTemplate(template_id);
        });

        function sendRequest() {
            var newFormData = $('#mailflow-form').serialize();

            if (formData !== newFormData) {
                formData = newFormData;

                $.ajax({
                    url: '<?php echo admin_url('mailflow/totalEmailsFound') ?>',
                    type: 'POST',
                    data: newFormData,
                    success: function (response) {
                        response = JSON.parse(response);

                        $('#email-table tbody').empty();

                        if (Array.isArray(response.leads_list)) {
                            response.leads_list.forEach(function (email) {
                                $('#email-table tbody').append('<tr><td>' + email + ' - <strong>LEAD</strong></td></tr>');
                            });
                        }

                        if (Array.isArray(response.customers_list)) {
                            response.customers_list.forEach(function (email) {
                                $('#email-table tbody').append('<tr><td>' + email + ' - <strong>CUSTOMER</strong></td></tr>');
                            });
                        }
                        if (Array.isArray(response.staff_list)) {
                            response.staff_list.forEach(function (email) {
                                $('#email-table tbody').append('<tr><td>' + email + ' - <strong>STAFF</strong></td></tr>');
                            });
                        }

                        $('#sms-table tbody').empty();

                        if (Array.isArray(response.leads_list)) {
                            response.leads_phone_number_list.forEach(function (email) {
                                $('#sms-table tbody').append('<tr><td>' + email + ' - <strong>LEAD</strong></td></tr>');
                            });
                        }

                        if (Array.isArray(response.customers_list)) {
                            response.customers_phone_number_list.forEach(function (email) {
                                $('#sms-table tbody').append('<tr><td>' + email + ' - <strong>CUSTOMER</strong></td></tr>');
                            });
                        }
                        if (Array.isArray(response.staff_list)) {
                            response.staff_phone_number_list.forEach(function (email) {
                                $('#sms-table tbody').append('<tr><td>' + email + ' - <strong>STAFF</strong></td></tr>');
                            });
                        }

                        $('#totalStaff').text(response.total_staff);
                        $('#totalLeads').text(response.total_leads);
                        $('#totalCustomers').text(response.total_customers);
                        $('#totalSmsCustomers').text(response.total_customers_phone_numbers);
                        $('#totalSmsLeads').text(response.total_leads_phone_numbers);
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            }
        }

        function useTemplate(id) {
            $.ajax({
                url: '<?php echo admin_url('mailflow/getTemplate') ?>',
                type: 'POST',
                data: {newsletter_template: id},
                success: function (response) {
                    response = JSON.parse(response);

                    $('#email_subject').val(response.template_data.template_subject)

                    var editor = tinymce.get('email_content');
                    if (editor) {
                        editor.setContent(response.template_data.template_content);
                    }

                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        }

        function useSmsTemplate(id) {
            $.ajax({
                url: '<?php echo admin_url('mailflow/getTemplate') ?>',
                type: 'POST',
                data: {newsletter_template: id},
                success: function (response) {
                    response = JSON.parse(response);
					$('#sms_template_content').val(response.template_data.sms_template_content);
					$('#sms_template_id').val(response.template_data.sms_template_id);


                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        }
		
        function useWhatsAppTemplate(id) {
            $.ajax({
                url: '<?php echo admin_url('mailflow/getTemplate') ?>',
                type: 'POST',
                data: {newsletter_template: id},
                success: function (response) {
                    response = JSON.parse(response);
					$('#whatsapp_template_content').val(response.template_data.whatsapp_template_content);
					$('#whatsapp_template_name').val(response.template_data.whatsapp_template_name);


                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        }

        $('#mailflow-form').submit(function (e) {
            if ($('.text-danger').length === 0) {
                $('.sendNewsletter').prop('disabled', true);
            }
        });

    });

    function insert_merge_field(field) {
        var key = $(field).text();
        tinymce.get('email_content').execCommand('mceInsertContent', false, key);
    }

    function testEmailTemplate() {
        var email = $('input[name="test_email"]').val();
        var test_content = tinymce.get('email_content').getContent();

        if (email != '') {

            $(this).attr('disabled', true);

            $.post(admin_url + 'mailflow/send_email_test/', {
                test_email: email,
                test_content: test_content,
            }).done(function (data) {
                var response = JSON.parse(data);

                if (response.status === true || response.status == 'true' || $.isNumeric(response.status)) {
                    alert_float('success', response.message);
                } else {
                    alert_float('danger', response.message);
                }

            });
        }
    }

    function testSmsTemplate() {
        var number = $('input[name="test_sms"]').val();
		var sms_template_id = $('#sms_template_id').val();
		var content = $('#sms_template_content').val();
		
        if (number != '') {

            $(this).attr('disabled', true);

            $.post(admin_url + 'mailflow/send_sms_test/', {
                number: number,
                sms_template_id: sms_template_id,
                content: content
            }).done(function (data) {
                var response = JSON.parse(data);

                if (response.status === true || response.status == 'true' || $.isNumeric(response.status)) {
                    alert_float('success', response.message);
                } else {
                    alert_float('danger', response.message);
                }
            });
        }
    }
</script>
