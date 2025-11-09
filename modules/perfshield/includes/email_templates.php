<?php

/*
 * Inject email template for perfshield module
 */
hooks()->add_action('after_email_templates', 'add_email_template_for_perfshield');
function add_email_template_for_perfshield()
{
    $viewData['hasPermissionEdit']    = has_permission('email_templates', '', 'edit');
    $viewData['perfshield']           = get_instance()->emails_model->get([
        'type'     => 'perfshield',
        'language' => 'english',
    ]);
    get_instance()->load->view(PERFSHIELD_MODULE . '/mail_lists/perfshield_email_templates_list', $viewData, false);
}

/**
 * Email template for unrecognized login detected (sent to staff).
 */
$unrecognizedLoginDetected = nl2br('Dear {staff_firstname} {staff_lastname}, <br><br> An unrecognized login attempt was made on your account. <br><br> From following details: <br><br> Internet service provider: {isp} <br><br> Ip Address: {ip_address} <br><br> Country: {country} <br><br> If you did not attempt to log in, please contact administrator as soon as possible. <br><br> Thank you for your cooperation in maintaining the security of your account. <br><br> Sincerely, <br><br> {companyname}', false);
create_email_template('Unrecognized Login Detected', $unrecognizedLoginDetected, 'perfshield', 'Unrecognized Login Detected (sent to staff)', 'unrecognized-login-detected');

/**
 * Email template for unrecognized login detected (sent to admin).
 */
$unrecognizedLoginDetectedToAdmin = nl2br('An unrecognized login attempt was found on following staff\'s account : <br><br>Firstname: {staff_firstname} <br><br> Lastname: {staff_lastname} <br><br> Email: {staff_email} <br><br> From the following details: <br><br> Internet service provider: {isp} <br><br> Ip Address: {ip_address} <br><br> Country: {country}', false);
create_email_template('Unrecognized Login Detected', $unrecognizedLoginDetectedToAdmin, 'perfshield', 'Unrecognized Login Detected (sent to admin)', 'unrecognized-login-detected-to-admin');

/**
 * Email template for multiple failed attempts (sent to staff).
 */
$multipleFailedLoginAttempts = nl2br('Dear {staff_firstname} {staff_lastname}, <br><br> We have detected multiple failed login attempts on your account from the following details: <br><br> Internet service provider: {isp} <br><br> Ip Address: {ip_address} <br><br> Country: {country} <br><br> which may indicate an unauthorized person is trying to access your account. <br><br> If you have any questions or concerns, please contact administrator. <br><br> Thank you for your cooperation in ensuring the security of our system. <br><br> Best regards, <br><br> {companyname}', false);
create_email_template('Security Alert: Multiple Failed Login Attempts', $multipleFailedLoginAttempts, 'perfshield', 'Multiple Failed Login Attempts (sent to staff)', 'multiple-failed-login-attempts');

register_merge_fields(PERFSHIELD_MODULE . '/merge_fields/perfshield_merge_fields');

hooks()->add_filter('available_merge_fields', 'useStaffAndOtherMergeFields');
function useStaffAndOtherMergeFields($fields)
{
    foreach ($fields as $key => $value) {
        if (isset($value['other'])) {
            foreach ($value['other'] as $s_key => $s_value) {
                if (!empty($value['other'][$s_key]['available'])) {
                    array_push($value['other'][$s_key]['available'], 'perfshield');
                }
            }
        }
        if (isset($value['staff'])) {
            foreach ($value['staff'] as $s_key => $s_value) {
                if (!empty($value['staff'][$s_key]['available'])) {
                    array_push($value['staff'][$s_key]['available'], 'perfshield');
                }
            }
        }
        $final_fields[$key] = $value;
    }

    return $final_fields;
}