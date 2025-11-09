<?php defined('BASEPATH') or exit('No direct script access allowed');
hooks()->do_action('before_sms_gateways_settings');

$gateways = $this->app_sms->get_gateways();
$triggers = $this->app_sms->get_available_triggers();
$total_gateways = count($gateways);
?>
<?php init_head(); ?>
    <div id="wrapper">
    <div class="content">
    <div class="row">
    <div class="col-md-12">
        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
            <?php echo $title; ?>
        </h4>
        <div class="panel_s">
            <div class="panel-body">

                <?php
                if ($total_gateways > 1) { ?>
                    <div class="alert alert-info">
                        <?php echo _l('notice_only_one_active_sms_gateway'); ?>
                    </div>
                <?php } ?>

                <?php
                echo form_open(admin_url('mailflow/sms_integrations'), ['class' => 'integration-form']);
                ?>
                <div class="panel-group" id="sms_gateways_options" role="tablist" aria-multiselectable="false">
                    <?php foreach ($gateways as $gateway) { ?>
                        <div class="panel panel-default">
                            <div class="panel-heading" role="tab" id="<?php echo 'heading' . $gateway['id']; ?>">
                                <h4 class="panel-title">
                                    <a role="button" data-toggle="collapse" data-parent="#sms_gateways_options"
                                       href="#sms_<?php echo $gateway['id']; ?>" aria-expanded="true"
                                       aria-controls="sms_<?php echo $gateway['id']; ?>">
                                        <?php echo $gateway['name']; ?> <span class="pull-right"><i
                                                    class="fa fa-sort-down"></i></span>
                                    </a>
                                </h4>
                            </div>
                            <div id="sms_<?php echo $gateway['id']; ?>"
                                 class="panel-collapse collapse<?php if ($this->app_sms->get_option($gateway['id'], 'active') == 1 || $total_gateways == 1) {
                                     echo ' in';
                                 } ?>" role="tabpanel" aria-labelledby="<?php echo 'heading' . $gateway['id']; ?>">
                                <div class="panel-body">
                                    <?php
                                    if (isset($gateway['info']) && $gateway['info'] != '') {
                                        echo $gateway['info'];
                                    }

                                    foreach ($gateway['options'] as $g_option) {
                                        $type = isset($g_option['field_type']) ? $g_option['field_type'] : 'text';
                                        if ($type == 'text') {
                                            echo render_input(
                                                'settings[' . $this->app_sms->option_name($gateway['id'], $g_option['name']) . ']',
                                                $g_option['label'],
                                                $this->app_sms->get_option($gateway['id'], $g_option['name']),
                                                'text',
                                                [],
                                                [],
                                                isset($g_option['info']) ? 'mbot5' : 'mbot15'
                                            );
                                        } elseif ($type == 'radio') {
                                            ?>
                                            <div class="form-group">
                                                <p><?php echo $g_option['label']; ?></p>
                                                <?php
                                                foreach ($g_option['options'] as $option) {
                                                    ?>
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio"
                                                               name="settings[<?php echo $optionName = $this->app_sms->option_name($gateway['id'], $g_option['name']); ?>]"
                                                               value="<?php echo $option['value']; ?>"
                                                               id="<?php echo $option['value'] . '-' . $optionName; ?>" <?php if ($this->app_sms->get_option($gateway['id'], $g_option['name']) == $option['value']) {
                                                            echo ' checked';
                                                        } ?>>
                                                        <label
                                                                for="<?php echo $option['value'] . '-' . $optionName; ?>"><?php echo $option['label']; ?></label>
                                                    </div>
                                                    <?php
                                                } ?>
                                            </div>
                                            <?php
                                        }

                                        if (isset($g_option['info'])) { ?>
                                            <div class="mbot15">
                                                <?php echo $g_option['info']; ?>
                                            </div>
                                        <?php }
                                    }
                                    echo '<div class="sms_gateway_active">';

                                    echo render_yes_no_option($this->app_sms->option_name($gateway['id'], 'active'), 'Active');

                                    echo '</div>';
                                    if (get_option($this->app_sms->option_name($gateway['id'], 'active')) == '1') {
                                        echo '<hr />';
                                        echo '<h4 class="mbot15">' . _l('test_sms_config') . '</h4>';
                                        echo '<div class="form-group"><input type="text" placeholder="' . _l('staff_add_edit_phonenumber') . '" class="form-control test-phone" data-id="' . $gateway['id'] . '"></div>';
                                        echo '<div class="form-group"><textarea class="form-control sms-gateway-test-message" placeholder="' . _l('test_sms_message') . '" data-id="' . $gateway['id'] . '" rows="4"></textarea></div>';
                                        echo '<button type="button" class="btn btn-primary send-test-sms" data-id="' . $gateway['id'] . '">' . _l('send_test_sms') . '</button>';
                                        echo '<div id="sms_test_response" data-id="' . $gateway['id'] . '"></div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="btn-bottom-toolbar text-right">
                    <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
<?php init_tail(); ?>