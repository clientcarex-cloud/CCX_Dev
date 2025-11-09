<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Marketing Campaigns For Customers & Leads
Description: Effortlessly send email & sms campaigns to customers. Streamline communication and drive conversions with ease.
Version: 1.2.0
Requires at least: 1.0.*
*/

define('MAILFLOW_MODULE_NAME', 'mailflow');

hooks()->add_action('admin_init', 'mailflow_module_init_menu_items');
hooks()->add_action('admin_init', 'mailflow_permissions');
hooks()->add_action('before_cron_run', 'mailflow_handle_scheduled_campaigns');
hooks()->add_action('mailflow_init', MAILFLOW_MODULE_NAME . '_appint');
// hooks()->add_action('pre_activate_module', MAILFLOW_MODULE_NAME . '_preactivate');
// hooks()->add_action('pre_deactivate_module', MAILFLOW_MODULE_NAME . '_predeactivate');
// hooks()->add_action('pre_uninstall_module', MAILFLOW_MODULE_NAME . '_uninstall');

/**
 * Load the module helper
 */
$CI = & get_instance();
$CI->load->helper(MAILFLOW_MODULE_NAME . '/mailflow'); //on module main file

function mailflow_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete')
    ];

    register_staff_capabilities('mailflow', $capabilities, _l('mailflow'));

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete')
    ];

    register_staff_capabilities('mailflow_integrations', $capabilities, _l('mailflow_integrations'));
}

/**
 * Register activation module hook
 */
register_activation_hook(MAILFLOW_MODULE_NAME, 'mailflow_module_activation_hook');

function mailflow_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(MAILFLOW_MODULE_NAME, [MAILFLOW_MODULE_NAME]);

/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
function mailflow_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('mailflow', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('mailflow', [
            'slug' => 'mailflow',
            'name' => _l('mailflow'),
            'position' => 6,
            'href'     => admin_url('mailflow'),
            'icon' => 'far fa-envelope'
        ]);
    }

    if (has_permission('mailflow', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('mailflow', [
            'slug' => 'mailflow-view',
            'name' => _l('mailflow_newsletter'),
            'icon' => 'fa fa-bullhorn',
            'href' => admin_url('mailflow/manage'),
            'position' => 11,
        ]);
    }

    if (has_permission('mailflow', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('mailflow', [
            'slug' => 'mailflow-scheduled',
            'name' => _l('mailflow_scheduled_campaigns'),
            'icon' => 'far fa-calendar-alt',
            'href' => admin_url('mailflow/scheduled_campaigns'),
            'position' => 11,
        ]);
    }

    if (has_permission('mailflow', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('mailflow', [
            'slug' => 'mailflow-view-history',
            'name' => _l('mailflow_newsletter_history'),
            'icon' => 'fa fa-history',
            'href' => admin_url('mailflow/history'),
            'position' => 11,
        ]);
    }

    if (has_permission('mailflow_integrations', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('mailflow', [
            'slug' => 'mailflow-integrations',
            'name' => _l('mailflow_integrations'),
            'icon' => 'fa fa-plug',
            'href' => admin_url('mailflow/integrations'),
            'position' => 11,
        ]);
    }

    if (has_permission('mailflow', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('mailflow', [
            'slug' => 'mailflow-templates',
            'name' => _l('mailflow_templates'),
            'icon' => 'fa fa-file-alt',
            'href' => admin_url('mailflow/manage_templates'),
            'position' => 11,
        ]);
    }

    if (has_permission('mailflow', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('mailflow', [
            'slug' => 'mailflow-unsub-emails',
            'name' => _l('mailflow_unsub_emails'),
            'icon' => 'fa fa-ban',
            'href' => admin_url('mailflow/manage_unsubscribed_emails'),
            'position' => 11,
        ]);
    }

}

function mailflow_handle_scheduled_campaigns() {
    $CI = &get_instance();

    $CI->load->model('mailflow/mailflow_model');
    $CI->load->model('emails_model');

    $getScheduledCampaigns = $CI->mailflow_model->getScheduledCampaigns(0); // Scheduled Campaigns
    $current_time = date('Y-m-d H:i:s');

    foreach ($getScheduledCampaigns as $campaign) {
        // Check if the scheduled_to time is less than or equal to the current time
        if ($campaign['scheduled_to'] <= $current_time) {
            // Update the campaign status to processing
            $CI->mailflow_model->updateScheduledCampaign($campaign['id'], ['campaign_status' => 1]);

            // Initialize email and SMS counters
            $totalEmailsSent = 0;
            $totalEmailsFailed = 0;
            $emailsToSent = 0;

            $totalSMSSent = 0;
            $totalSMSFailed = 0;
            $smsToSend = 0;

            $emailList = json_decode($campaign['email_list']);
            $emailSubject = $campaign['email_subject'];
            $emailContent = $campaign['email_content'];
            $emailSMTP = $campaign['email_smtp'];

            $smsList = json_decode($campaign['sms_list']);
            $smsContent = $campaign['sms_content'];

            // Process emails if email list exists
            if (is_array($emailList) && count($emailList) > 0 && !empty($emailSubject) && !empty($emailContent) && !empty($emailSMTP)) {
                foreach ($emailList as $email) {
                    ++$emailsToSent;

                    $unsubscribeLink = '<a href="' . base_url('mailflow/mailflowunsubscribe/opt_out/' . mailflow_encryption($email)) . '">Unsubscribe</a>';
                    $emailContent = str_replace('{{unsubscribe_link}}', $unsubscribeLink, $emailContent);

                    if (mailflow_send_email($email, $emailSubject, $emailContent, $emailSMTP)) {
                        ++$totalEmailsSent;
                        log_activity('Scheduled Campaign Email Sent [To: ' . $email . ']');
                    } else {
                        ++$totalEmailsFailed;
                        log_activity('Scheduled Campaign Email Failed [To: ' . $email . ']');
                    }
                }
            }

            // Process SMS if SMS list exists
            if (is_array($smsList) && count($smsList) > 0 && !empty($smsContent)) {
                foreach ($smsList as $phone) {
                    ++$smsToSend;

                    if (mailflow_send_sms($phone, $smsContent, true)) {
                        ++$totalSMSSent;
                        log_activity('Scheduled Campaign SMS Sent [To: ' . $phone . ']');
                    } else {
                        ++$totalSMSFailed;
                        log_activity('Scheduled Campaign SMS Failed [To: ' . $phone . ']');
                    }
                }
            }

            // Check if email data and SMS data match the total count
            $isEmailDataEqual = ($totalEmailsFailed + $totalEmailsSent) === $emailsToSent;
            $isSMSDataEqual = ($totalSMSFailed + $totalSMSSent) === $smsToSend;

            // Handle Email Campaign
            if ($isEmailDataEqual && $emailsToSent > 0 && !$smsToSend) {
                $CI->mailflow_model->add([
                    'sent_by' => get_staff_user_id(),
                    'email_subject' => $emailSubject,
                    'email_content' => $emailContent,
                    'total_emails_to_send' => $emailsToSent,
                    'emails_sent' => $totalEmailsSent,
                    'email_list' => json_encode($emailList),
                    'emails_failed' => $totalEmailsFailed,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                log_activity('Scheduled Email Campaign Sent [Total Emails: ' . $emailsToSent . ' - Emails Sent: ' . $totalEmailsSent . ' - Failed Emails: ' . $totalEmailsFailed . ']');
            }

            // Handle SMS Campaign
            if ($isSMSDataEqual && $smsToSend > 0 && !$emailsToSent) {
                $CI->mailflow_model->add([
                    'sent_by' => get_staff_user_id(),
                    'sms_content' => $smsContent,
                    'total_sms_to_send' => $smsToSend,
                    'sms_sent' => $totalSMSSent,
                    'sms_list' => json_encode($smsList),
                    'sms_failed' => $totalSMSFailed,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                log_activity('Scheduled SMS Campaign Sent [Total SMS: ' . $smsToSend . ' - SMS Sent: ' . $totalSMSSent . ' - Failed SMS: ' . $totalSMSFailed . ']');
            }

            // Handle both Email and SMS Campaign
            if ($isEmailDataEqual && $isSMSDataEqual && $emailsToSent > 0 && $smsToSend > 0) {
                $CI->mailflow_model->add([
                    'sent_by' => get_staff_user_id(),
                    'email_subject' => $emailSubject,
                    'email_content' => $emailContent,
                    'sms_content' => $smsContent,
                    'total_emails_to_send' => $emailsToSent,
                    'total_sms_to_send' => $smsToSend,
                    'emails_sent' => $totalEmailsSent,
                    'sms_sent' => $totalSMSSent,
                    'email_list' => json_encode($emailList),
                    'sms_list' => json_encode($smsList),
                    'emails_failed' => $totalEmailsFailed,
                    'sms_failed' => $totalSMSFailed,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                log_activity('Scheduled Email & SMS Campaign Sent [Total Emails: ' . $emailsToSent . ' - Emails Sent: ' . $totalEmailsSent . ' - Failed Emails: ' . $totalEmailsFailed . '] [Total SMS: ' . $smsToSend . ' - SMS Sent: ' . $totalSMSSent . ' - Failed SMS: ' . $totalSMSFailed . ']');
            }

            $CI->mailflow_model->updateScheduledCampaign($campaign['id'], ['campaign_status' => 2]);
        }
    }
}

function mailflow_appint()
{
    // $CI = &get_instance();
    // require_once 'libraries/leclib.php';
    // $module_api = new MailflowLic();
    // $module_leclib = $module_api->verify_license(true);
    // if (!$module_leclib || ($module_leclib && isset($module_leclib['status']) && !$module_leclib['status'])) {
    //     $CI->app_modules->deactivate(MAILFLOW_MODULE_NAME);
    //     set_alert('danger', "One of your modules failed its verification and got deactivated. Please reactivate or contact support.");
    //     redirect(admin_url('modules'));
    // }
}

function mailflow_preactivate($module_name)
{
    // if ($module_name['system_name'] == MAILFLOW_MODULE_NAME) {
    //     require_once 'libraries/leclib.php';
    //     $module_api = new MailflowLic();
    //     $module_leclib = $module_api->verify_license();
    //     if (!$module_leclib || ($module_leclib && isset($module_leclib['status']) && !$module_leclib['status'])) {
    //         $CI = &get_instance();
    //         $data['submit_url'] = $module_name['system_name'] . '/lecverify/activate';
    //         $data['original_url'] = admin_url('modules/activate/' . MAILFLOW_MODULE_NAME);
    //         $data['module_name'] = MAILFLOW_MODULE_NAME;
    //         $data['title'] = "Module License Activation";
    //         echo $CI->load->view($module_name['system_name'] . '/activate', $data, true);
    //         exit();
    //     }
    // }
}

function mailflow_predeactivate($module_name)
{
    // if ($module_name['system_name'] == MAILFLOW_MODULE_NAME) {
    //     require_once 'libraries/leclib.php';
    //     $mailflow_api = new MailflowLic();
    //     $mailflow_api->deactivate_license();
    // }
}

function mailflow_uninstall($module_name)
{
    // if ($module_name['system_name'] == MAILFLOW_MODULE_NAME) {
    //     require_once 'libraries/leclib.php';
    //     $mailflow_api = new MailflowLic();
    //     $mailflow_api->deactivate_license();
    // }
}