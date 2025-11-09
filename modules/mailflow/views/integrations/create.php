<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">

            <?php

            if (isset($integration_data)) {
                $requestUrl = 'mailflow/create_integration/'.$integration_data->id;
            } else {
                $requestUrl = 'mailflow/create_integration';
            }

            echo form_open(admin_url($requestUrl), ['class' => 'integration-form']);
            ?>
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                        <input type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1" />
                        <input type="password" class="fake-autofill-field" name="fakepasswordremembered" value='' tabindex="-1" />
                        <h4 style="margin-top:-20px;" class="tw-font-semibold"><?php echo _l('settings_smtp_settings_heading'); ?></h4>
                        <hr>
                        <?php echo render_input('name', 'mailflow_integration_name', $integration_data->name ?? ''); ?>
                        <hr />
                        <div class="form-group">
                            <?php if (get_option('email_protocol') == 'mail') { ?>
                                <div class="alert alert-warning">
                                    The "mail" protocol is not the recommended protocol to send emails, you should strongly consider
                                    configuring the "SMTP" protocol to avoid any distruptions and delivery issues.
                                </div>
                            <?php } ?>
                            <div class="form-group mtop15">
                                <label for="email_encryption"><?php echo _l('smtp_encryption'); ?></label><br />
                                <select name="email_encryption" class="selectpicker" data-width="100%">
                                    <option value="" <?php if (isset($integration_data->email_encryption) && $integration_data->email_encryption == '') {
                                        echo 'selected';
                                    } ?>><?php echo _l('smtp_encryption_none'); ?></option>
                                    <option value="ssl" <?php if (isset($integration_data->email_encryption) && $integration_data->email_encryption == 'ssl') {
                                        echo 'selected';
                                    } ?>>SSL</option>
                                    <option value="tls" <?php if (isset($integration_data->email_encryption) && $integration_data->email_encryption == 'tls') {
                                        echo 'selected';
                                    } ?>>TLS</option>
                                </select>
                            </div>
                            <?php echo render_input('smtp_host', 'settings_email_host', $integration_data->smtp_host ?? ''); ?>
                            <?php echo render_input('smtp_port', 'settings_email_port', $integration_data->smtp_port ?? 465); ?>
                        </div>
                        <?php echo render_input('email', 'settings_email', $integration_data->email ?? ''); ?>
                        <div class="smtp-fields<?php if (isset($integration_data->email_encryption) && $integration_data->email_encryption == 'mail') {
                            echo ' hide';
                        } ?>">
                            <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip"
                               data-title="<?php echo _l('smtp_username_help'); ?>"></i>
                            <?php echo render_input('smtp_username', 'smtp_username', $integration_data->smpt_username ?? ''); ?>
                            <?php
                            $ps = $integration_data->smtp_password ?? '';
                            if (!empty($ps)) {
                                if (false == $this->encryption->decrypt($ps)) {
                                    $ps = $ps;
                                } else {
                                    $ps = $this->encryption->decrypt($ps);
                                }
                            }
                            echo render_input('smtp_password', 'settings_email_password', $ps, 'password', ['autocomplete' => 'off']); ?>
                        </div>
                        <?php echo render_input('email_charset', 'settings_email_charset', $integration_data->email_charset ?? 'utf-8'); ?>
                        <hr />
                        <h4><?php echo _l('settings_send_test_email_heading'); ?></h4>
                        <p class="text-muted"><?php echo _l('settings_send_test_email_subheading'); ?></p>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="email" class="form-control" name="test_email" data-ays-ignore="true"
                                       placeholder="<?php echo _l('settings_send_test_email_string'); ?>">
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-info test_email">Test</button>
                                </div>
                            </div>
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
    'use strict';

    $("#integration-form").appFormValidator({
        rules: {
            name: "required",
            'email_encryption': "required",
            'smtp_host': "required",
            'smtp_port': "required",
            'email': "required",
            'smtp_password': "required",
            'email_charset': "required"
        },
    });
</script>

