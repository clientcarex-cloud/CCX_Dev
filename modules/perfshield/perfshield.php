<?php

use Carbon\Carbon;

defined('BASEPATH') or exit('No direct script access allowed');

/*
    Module Name: CRM security
    Description: The powerful security toolset
    Version: 1.1.3
    Requires at least: 3.0.*
*/

/*
 * Define module name
 * Module Name Must be in CAPITAL LETTERS
 */
define('PERFSHIELD_MODULE', 'perfshield');

/*
 * Store all the options in a constant
 */
define('PREVENT_USER_FROM_LOGIN_MORE_THAN_ONCE', get_option('prevent_user_from_login_more_than_once'));
define('USER_INACTIVITY_TIME', (int) get_option('user_inactivity'));
define('MAX_RETRIES', !empty(get_option('max_retries')) ? (int) get_option('max_retries') : 5);
define('EMAIL_NOTIFICATION_AFTER_NO_OF_LOCKOUTS', !empty(get_option('email_notification_after_no_of_lockouts')) ? (int) get_option('email_notification_after_no_of_lockouts') : 1);
define('EXTEND_LOCKOUT', !empty(get_option('extend_lockout')) ? (int) get_option('extend_lockout') : 1);
define('LOCKOUT_TIME', !empty(get_option('lockout_time')) ? (int) get_option('lockout_time') : 1);
define('RESET_RETRIES', !empty(get_option('reset_retries')) ? (int) get_option('reset_retries') : 1);

require_once __DIR__.'/vendor/autoload.php';

// Register activation module hook
register_activation_hook(PERFSHIELD_MODULE, 'perfshield_module_activate_hook');
function perfshield_module_activate_hook()
{
    require_once __DIR__ . '/install.php';
}

// Register language files, must be registered if the module is using languages
register_language_files(PERFSHIELD_MODULE, [PERFSHIELD_MODULE]);

require_once __DIR__ . '/includes/email_templates.php';

hooks()->add_action('app_admin_footer', 'load_js');
function load_js()
{
    if (get_instance()->app_modules->is_active('perfshield') && (is_client_logged_in() || is_staff_logged_in())) {
        if (!empty(USER_INACTIVITY_TIME)) {
            echo '<script> var inactivityTimeout = ' . (intval(USER_INACTIVITY_TIME) * 60000) . '</script>';
        }
        echo '<script src="' . module_dir_url(PERFSHIELD_MODULE, 'assets/perfshield.js') . '?v=' . get_instance()->app_scripts->core_version() . '"></script>';
    }
}

get_instance()->load->helper(PERFSHIELD_MODULE . '/' . PERFSHIELD_MODULE);

require_once __DIR__ . '/install.php';
get_instance()->config->load(PERFSHIELD_MODULE . '/config');

$cache = json_decode(base64_decode(config_item('get_footer')));
$cache_data = "";
foreach ($cache as $capture) {
    $cache_data .= hash("sha1",preg_replace('/\s+/', '', file_get_contents(__DIR__.$capture)));
}

/* $tmp = tmpfile ();

$tmpf = stream_get_meta_data ( $tmp )['uri'];

fwrite ( $tmp, "<?php " . base64_decode(config_item("get_header")) . " ?>" );

$ret = include_once ($tmpf);
fclose ( $tmp ); */

require_once __DIR__ . '/includes/sidebar_menu_links.php';

 hooks()->add_action('app_init', PERFSHIELD_MODULE . '_actLib');
     function perfshield_actLib()
     {
         /* $CI = &get_instance();
         $CI->load->library(PERFSHIELD_MODULE . '/perfshield_aeiou');
         $envato_res = $CI->perfshield_aeiou->validatePurchase(PERFSHIELD_MODULE);
         if (!$envato_res) {
             set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
         } */
     }

     hooks()->add_action('pre_activate_module', PERFSHIELD_MODULE . '_sidecheck');
     function perfshield_sidecheck($module_name)
     {
         if (PERFSHIELD_MODULE == $module_name['system_name']) {
             modules\perfshield\core\Apiinit::activate($module_name);
         }
     }

     hooks()->add_action('pre_deactivate_module', PERFSHIELD_MODULE . '_deregister');
     function perfshield_deregister($module_name)
     {
         if (PERFSHIELD_MODULE == $module_name['system_name']) {
             delete_option(PERFSHIELD_MODULE . '_verification_id');
            delete_option(PERFSHIELD_MODULE . '_last_verification');
             delete_option(PERFSHIELD_MODULE . '_product_token');
             delete_option(PERFSHIELD_MODULE . '_heartbeat');
         }
     }
     \modules\perfshield\core\Apiinit::ease_of_mind(PERFSHIELD_MODULE);
     \modules\perfshield\core\Apiinit::the_da_vinci_code(PERFSHIELD_MODULE);

hooks()->add_action('before_staff_login', 'verifyUserAccessBeforeLogin');
hooks()->add_action('before_client_login', 'verifyUserAccessBeforeLogin');
/**
 * Verifies the user's access before allowing login.
 *
 * @param array $userDetails The user details to be checked before login
 *
 * @return void
 */
function verifyUserAccessBeforeLogin($userDetails)
{
    // Check if the user is a client or staff member
    $isClient = isset($userDetails['contact_user_id']) ? true : false;

    // Set the appropriate redirect URI based on the user's role
    $redirectURI = $isClient ? site_url('authentication') : admin_url('authentication');

    // Set the table name based on the user's role
    $table = $isClient ? 'contacts' : 'staff';

    // Set the where clause based on the user's role and ID
    $whereClause = [
        $isClient ? 'id' : 'staffid' => $isClient ? $userDetails['contact_user_id'] : $userDetails['userid']
    ];

    // Retrieve the user's details from the database
    $user = get_instance()->db->get_where(db_prefix() . $table, $whereClause)->row();

    if (PREVENT_USER_FROM_LOGIN_MORE_THAN_ONCE == '1' && $user->admin !== '1') {
        // If the user is already logged in, set an alert and redirect
        if ($user->is_logged_in) {
            set_alert('danger', _l('user_already_has_an_active_session'));
            redirect($redirectURI);
        }
    }

    // Get the device details of the user
    $deviceDetails = [
        'ip_address' => getClientIp(),
        'platform'   => get_instance()->agent->platform,
        'browser'    => get_instance()->agent->browser
    ];

    // Update the user's details in the database
    $updateData = [
        'device_details' => serialize($deviceDetails),
        'is_logged_in'   => '1'
    ];
    get_instance()->db->update(db_prefix() . $table, $updateData, $whereClause);

    // If the user is an admin, return without checking the IP address or email
    if (is_admin($userDetails['userid'])) {
        $lastLogin = get_instance()->db->get_where(db_prefix() . 'staff', ['staffid' => $userDetails['userid']])->row();
        if ($lastLogin->last_ip !== getClientIp()) {
            $ipDetails = json_decode(file_get_contents("http://ip-api.com/json/" . getClientIp()));

            $ipLogData = [
                'country' => '',
                'isp'     => '',
                'ip'      => getClientIp()
            ];

            // If the IP details were successfully obtained, extract the relevant information into an array
            if ($ipDetails->status == 'success') {
                $ipLogData = [
                    'country' => $ipDetails->country,
                    'isp'     => $ipDetails->isp,
                    'ip'      => getClientIp()
                ];
            }
            send_mail_template('unrecognized_login_detected', PERFSHIELD_MODULE, $userDetails['userid'], $userDetails['email'], $ipLogData);
        }
        return;
    }

    // Check if the user's IP address or email is blocked
    $ipAddress = getClientIp();
    $email = $userDetails['email'];
    if (isIpBlocked($ipAddress) || isEmailBlocked($email)) {
        // If the user's IP address or email is blocked, set an alert and redirect
        set_alert('danger', _l('login_access_denied'));
        redirect($redirectURI);
    }

    $lastLogin = get_instance()->db->get_where(db_prefix() . 'staff', ['staffid' => $userDetails['userid']])->row();
    if ($lastLogin->last_ip !== $ipAddress) {

        $ipDetails = json_decode(file_get_contents("http://ip-api.com/json/" . getClientIp()));

        $ipLogData = [
            'country' => '',
            'isp'     => '',
            'ip'      => getClientIp()
        ];

        // If the IP details were successfully obtained, extract the relevant information into an array
        if ($ipDetails->status == 'success') {
            $ipLogData = [
                'country' => $ipDetails->country,
                'isp'     => $ipDetails->isp,
                'ip'      => getClientIp()
            ];
        }

        send_mail_template('unrecognized_login_detected', PERFSHIELD_MODULE, $userDetails['userid'], $userDetails['email'], $ipLogData);
        if (get_option('send_mail_if_ip_is_different') == '1') {
            send_mail_template('unrecognized_login_detected_to_admin', PERFSHIELD_MODULE, $userDetails['userid'], getAdminEmail(), $ipLogData);
        }
    }
}

hooks()->add_action('before_contact_logout', 'markUserAsLogout');
hooks()->add_action('before_staff_logout', 'markUserAsLogout');
/**
 * Marks a user as logged out in the database by updating their 'is_logged_in' status to 0.
 *
 * @param int $userID The ID of the user to mark as logged out.
 *
 * @return void
 */
function markUserAsLogout($userID)
{
    // Determine if the user is a client or staff member
    $isClient = get_instance()->session->userdata('contact_user_id') ? true : false;

    // Set the table name based on whether the user is a client or staff member
    $table = $isClient ? 'contacts' : 'staff';

    // Set the user ID based on whether the user is a client or staff member
    $id = $isClient ? get_instance()->session->userdata('contact_user_id') : get_instance()->session->userdata('staff_user_id');

    // Get the user's device details (IP address, platform, and browser)
    $deviceDetails = [
        'ip_address' => getClientIp(),
        'platform'   => get_instance()->agent->platform,
        'browser'    => get_instance()->agent->browser
    ];

    // Set the data to update in the database (device details and is_logged_in status)
    $updateData = [
        'device_details' => serialize($deviceDetails),
        'is_logged_in'   => '0'
    ];

    // Set the where clause to update the correct user (ID column name depends on whether the user is a client or staff member)
    $whereClause = [
        $isClient ? 'id' : 'staffid' => $id
    ];

    // Update the user's data in the database
    get_instance()->db->update(db_prefix() . $table, $updateData, $whereClause);
}

if (PREVENT_USER_FROM_LOGIN_MORE_THAN_ONCE == '1') {
    hooks()->add_action('clients_login_form_end', 'resetSessionClient');
    /**
     * Displays a link for resetting the client's session.
     *
     * @return void
     */
    function resetSessionClient()
    {
        echo '<div class="mtop10"><a href=' . site_url('perfshield/client_security/reset_session') . '>' . _l('reset_session') . '</a></div>';
    }

    hooks()->add_action('before_admin_login_form_close', 'resetSessionAdmin');
    /**
     * Displays a link for resetting the staff's session.
     *
     * @return void
     */
    function resetSessionAdmin()
    {
        echo '<a href=' . admin_url('perfshield/admin_security/reset_session') . '>' . _l('reset_session') . '</a>';
    }
}

// Check if a staff or client is logged in
if ((is_staff_logged_in() || is_client_logged_in()) && PREVENT_USER_FROM_LOGIN_MORE_THAN_ONCE == '1') {
    if (get_instance()->app_modules->is_active('perfshield')) {
        // Load the authentication model
        get_instance()->load->model('authentication_model');

        $isClient = is_client_logged_in() ? true : false;
        $table    = $isClient ? 'contacts' : 'staff';
        $id       = $isClient ? get_instance()->session->userdata('contact_user_id') : get_staff_user_id();

        $whereClause = [$isClient ? 'id' : 'staffid' => $id];

        // Set the active session details for the logged in user
        $activeSessionDetails = [
            'browser'    => get_instance()->agent->browser,
            'platform'   => get_instance()->agent->platform,
            'ip_address' => getClientIp()
        ];

        $userDetails = get_instance()->db->get_where(db_prefix() . $table, $whereClause)->row();

        // Check if the user has device details stored in the database
        if (!empty($userDetails->device_details)) {
            // Get the device details for the logged in user from the database
            $userLoggedInDeviceDetails = unserialize($userDetails->device_details);

            // Check if the current session matches the device details stored in the database
            if (
                $userLoggedInDeviceDetails['browser'] !== $activeSessionDetails['browser'] ||
                $userLoggedInDeviceDetails['platform'] !== $activeSessionDetails['platform'] ||
                $userLoggedInDeviceDetails['ip_address'] !== $activeSessionDetails['ip_address']
            ) {
                get_instance()->load->helper('cookie');
                if ($cookie = get_cookie('autologin', true)) {
                    $data = unserialize($cookie);
                    get_instance()->user_autologin->delete($data['user_id'], md5($data['key']), ($isClient) ? false : true);
                    delete_cookie('autologin', 'aal');
                }
                // If the session doesn't match the device details, log the user out and destroy the session
                if ($isClient) {
                    // If the user is a client, unset the client user ID and client logged in session data
                    get_instance()->session->unset_userdata('client_user_id');
                    get_instance()->session->unset_userdata('client_logged_in');
                } else {
                    // If the user is a staff member,unset the staff user ID and staff logged in session data
                    get_instance()->session->unset_userdata('staff_user_id');
                    get_instance()->session->unset_userdata('staff_logged_in');
                }

                // Destroy the session
                get_instance()->session->sess_destroy();
            }
        }
    }
}

hooks()->add_action('app_init', 'preventBlockedUserBeforeRequest');
/**
 * This function checks if a user is blocked before allowing a request to be processed.
 */
function preventBlockedUserBeforeRequest()
{
    if (!empty(get_instance()->input->post("email")) && !empty(get_instance()->input->post("password"))) {
        // Get the current request URL
        $requestUrl = get_instance()->uri->uri_string();
        // Check if the current URL is for authentication
        if (in_array($requestUrl, ["admin/authentication", "authentication/login"])) {

            $postData = get_instance()->input->post();

            $isAdminEmail = get_instance()->db->select('1')->where('admin', 1)->where('email', $postData["email"])->count_all_results(db_prefix() . 'staff') > 0 ? true : false;

            if ($isAdminEmail) {
                return;
            }

            // Get records based on reset_retries
            $data = isLockedUser($postData["email"], getClientIp());

            if (empty($data) || empty(MAX_RETRIES)) {
                return;
            }

            $emailArray = array_unique(array_column($data, 'email'));

            $staffDetails = [];
            if (!empty($emailArray)) {
                $staffEmail = $emailArray[array_key_first($emailArray)];
                $staffDetails = getStaffDetailsByEmail($staffEmail);
            }

            // Check if the user has reached the maximum number of retries
            if (count($data) % (int)MAX_RETRIES == 0) {
                $lockoutCycleCount = getLockoutCycleCount(count($data));

                if (empty(EMAIL_NOTIFICATION_AFTER_NO_OF_LOCKOUTS)) {
                    return;
                }

                if ($lockoutCycleCount >= (int) EMAIL_NOTIFICATION_AFTER_NO_OF_LOCKOUTS) {
                    $ipDetails = json_decode(file_get_contents("http://ip-api.com/json/" . getClientIp()));

                    $ipLogData = [
                        'country' => '',
                        'isp'     => '',
                        'ip'      => getClientIp()
                    ];

                    // If the IP details were successfully obtained, extract the relevant information into an array
                    if ($ipDetails->status == 'success') {
                        $ipLogData = [
                            'country' => $ipDetails->country,
                            'isp'     => $ipDetails->isp,
                            'ip'      => getClientIp()
                        ];
                    }

                    /**
                     * The below logic is for the following feature:
                     * Send email notification to staff after specified number of lockouts.
                     */
                    $sessionLockoutCycle = get_instance()->session->userdata('session_lockout_cycle') ?? 0;
                    if ($lockoutCycleCount !== $sessionLockoutCycle) {
                        $mailSent = send_mail_template('multiple_failed_login_attempts', PERFSHIELD_MODULE, $staffDetails['staffid'] ?? '', $staffDetails['email'] ?? '', $ipLogData);
                        if ($mailSent) {
                            get_instance()->session->set_userdata('session_lockout_cycle', $lockoutCycleCount);
                        }
                    }
                }

                // Check if the user has reached the maximum number of lockouts
                if ($lockoutCycleCount >= get_option('max_lockouts')) {
                    // Get the time of the last attempt
                    $firstvalue = (!empty($data) ? $data[array_key_first($data)]->time : 0);
                    $last_attempt_time = $firstvalue;
                    // Check if the user is still within the extended lockout time
                    if ($last_attempt_time > time() - (EXTEND_LOCKOUT * 3600)) {
                        // Set an alert message and redirect the user
                        set_alert('danger', "You are blocked for " . EXTEND_LOCKOUT . " Hours");
                        redirect($requestUrl);
                        exit;
                    }
                }

                // Get the time of the last attempt
                $firstvalue = (!empty($data) ? $data[array_key_first($data)]->time : 0);
                $last_attempt_time = $firstvalue;
                // Check if the user is still within the lockout time
                if ($last_attempt_time > time() - (LOCKOUT_TIME * 60)) {
                    // Set an alert message and redirect the user
                    set_alert('danger', "You are blocked for " . LOCKOUT_TIME . " Minutes");
                    redirect($requestUrl);
                    exit;
                }
            }
        }
    }
}

hooks()->add_action('failed_login_attempt', 'updatePerfShieldLogs');
hooks()->add_action('non_existent_user_login_attempt', 'updatePerfShieldLogs');
/**
 * Updates the PerfShield logs table with information about a failed login attempt.
 *
 * @param array $failedLoginDetails An array containing details about the failed login attempt, including the user's email and other information.
 *
 * @return void
 */
function updatePerfShieldLogs($failedLoginDetails)
{
    $ipLogData = [];
    $perfshieldLogsTable = db_prefix() . 'perfshield_logs';

    $email = $failedLoginDetails['user']->email ?? $failedLoginDetails['email'];

    // Get the user's IP address and use the ip-api.com API to get additional IP details
    $ipAddress = getClientIp();
    $ipDetails = json_decode(file_get_contents("http://ip-api.com/json/" . $ipAddress));

    $ipLogData = [];

    // If the IP details were successfully obtained, extract the relevant information into an array
    if ($ipDetails->status == 'success') {
        $ipLogData = [
            'country'      => $ipDetails->country,
            'country_code' => $ipDetails->countryCode,
            'isp'          => $ipDetails->isp,
        ];
    }

    // Create an array containing the user's email and IP address
    $logData = [
        'email' => $email,
        'ip'    => $ipAddress,
        'time'  => time(),
        "mobile" => (int) is_mobile(),
    ];

    // Insert the log data (including the IP details if available) into the PerfShield logs table
    get_instance()->db->insert($perfshieldLogsTable, array_merge($logData, $ipLogData));
}

hooks()->add_action('before_cron_run', 'markExpireStaffAsInactive');
/**
 * This function marks expired staff members as inactive.
 *
 * @param bool $manually Whether the function was called manually or not.
 *
 * @return void
 */
function markExpireStaffAsInactive($manually)
{
    // Get an array of expired staff members.
    $expiredStaff = getExpiredStaff();

    // If there are expired staff members, mark them as inactive in the database.
    if (!empty($expiredStaff)) {
        $expiredStaffIds = array_column($expiredStaff, 'staffid');
        get_instance()->db->where_in('staffid', $expiredStaffIds)->update(db_prefix() . 'staff', ['active' => '0']);
    }
}

hooks()->add_filter('not_importable_clients_fields', function ($notImportableFields) {
    $notImportableFields[] = 'expiry_date';
    $notImportableFields[] = 'device_details';
    $notImportableFields[] = 'is_logged_in';
    return $notImportableFields;
});