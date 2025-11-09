<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Perfshield
 *
 * @package     Perfshield
 * @subpackage  Controllers
 * @category    Perfshield
 */
class Perfshield extends AdminController
{
    /**
     * Perfshield constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('perfshield_model');
        $this->load->helper('perfshield');
    }

    /**
     * Index Page for this controller.
     */
    public function index()
    {
        if (!has_permission('perfshield', '', 'view')) {
            access_denied(_l('perfshield'));
        }

        $viewData['title'] = _l('perfshield');
        $this->load->view('perfshield_dashboard', $viewData);
    }

    /**
     * Brute Force Settings Page for this controller.
     */
    public function bruteForceSettings()
    {
        if (!has_permission('perfshield', '', 'view')) {
            access_denied(_l('perfshield'));
        }
        if ($this->input->is_ajax_request()) {
            $res = $this->perfshield_model->saveSettings($this->input->post());
            echo json_encode([
                'type' => 'success',
                'message' => _l('bf_settings_updated')
            ]);
            return;
        }

        $viewData['title'] = _l('brute_force_settings');
        // Do not include admin in this list
        $viewData['staff'] = array_filter($this->staff_model->get(), function ($value) {
            return !is_admin($value['staffid']);
        });

        $this->load->view('brute_force_settings', $viewData);
    }

    /**
     * Get table data for the specified table.
     *
     * @param string $table_name The name of the table.
     */
    public function get_table_data($table_name, $type = '')
    {
        if (!$this->input->is_ajax_request()) {
            return;
        }

        $this->app->get_table_data(module_views_path(PERFSHIELD_MODULE, 'tables/' . $table_name), ['type' => $type]);
    }

    /**
     * Adds email address or ip address to the blacklist.
     *
     * @return void
     */
    public function addIpOrEmailToBlacklist()
    {
        if (!has_permission('perfshield', '', 'create')) {
            access_denied(_l('perfshield'));
        }
        $type = $this->input->post('type');

        // Get the IP addresses or an empty array if none is provided.
        $ip_email_list = $this->input->post('blacklist_ip') ?? $this->input->post('blacklist_email') ?? [];

        $ip_email_list = array_unique($ip_email_list);
        $isUnique = $this->perfshield_model->isUniqueIpOrEmail($ip_email_list, $type);

        if (!$isUnique['success']) {
            foreach ($ip_email_list as $key => $ipEmail) {
                if (in_array($ipEmail, $isUnique['ipEmail'])) {
                    unset($ip_email_list[$key]);
                }
            }
        }

        $error_message = "";
        $postData = [];

        // check for duplication
        foreach ($ip_email_list as $value) {
            if ($type == "ip") {
                $range = array_map('trim', explode('-', $value));
                if (count($range) > 1 && (float) sprintf("%u", ip2long($range[0])) > (float) sprintf("%u", ip2long($range[1]))) {
                    $error_message .= $value . " is invalid ip range";
                }

            }
            if ('' != $value) {
                $postData[] = [
                    'ip_email' => $value,
                    'type' => $type
                ];
            }
        }

        if (!empty($postData) && $error_message == "") {
            // Save the data to the database and return the result as a JSON encoded string.
            $res = $this->perfshield_model->addIpOrEmailToBlacklist($postData);
            echo json_encode(['type' => $res['type'], 'message' => $res['message']]);
            return;
        }

        echo json_encode(['type' => "danger", 'message' => $error_message]);
    }

    /**
     * Retrieves a list of users from the Perfshield Model and returns it as a JSON encoded string.
     *
     * @return void
     */
    public function getUsers()
    {
        if (!$this->input->is_ajax_request()) {
            return;
        }

        $users = $this->perfshield_model->getUsers();

        echo json_encode($users);
    }

    /**
     * Removes a specified IP address or User from the blacklist.
     *
     * @param int $id The ID of the IP address or User to remove.
     * @return void
     */
    public function removeIpOrEmailFromBlacklist($category, $id)
    {
        if (!$this->input->is_ajax_request()) {
            return;
        }

        $res = $this->perfshield_model->removeIpOrEmailFromBlacklist($category, $id);

        echo json_encode($res);
    }

    /**
     * Updates the data of an existing IP address in the database.
     *
     * @return void
     */
    public function updateIPAddress()
    {
        if (!$this->input->is_ajax_request()) {
            return;
        }

        $postData = $this->input->post();
        $res = $this->perfshield_model->updateIPAddress($postData);

        echo json_encode($res);
    }

    public function isUniqueIpOrEmail($type)
    {
        if (!$this->input->is_ajax_request()) {
            return;
        }

        $postData = $this->input->post("blacklist_{$type}");

        $res = $this->perfshield_model->isUniqueIpOrEmail($postData, $type);

        echo ($res['success']) ? "true" : "false";
    }

    public function addStaffExpiry()
    {
        if (!$this->input->is_ajax_request()) {
            return;
        }

        $postData = $this->input->post();
        $res = $this->perfshield_model->addStaffExpiry($postData);

        echo json_encode(['type' => $res['type'], 'message' => $res['message']]);
    }

    public function removeStaffExpiry($staffID)
    {
        if (!$this->input->is_ajax_request()) {
            return;
        }

        $res = $this->perfshield_model->removeStaffExpiry($staffID);

        echo json_encode($res);
    }

    public function saveSingleSessionSettings()
    {
        if (!$this->input->is_ajax_request()) {
            return;
        }

        $postData = $this->input->post();
        $res = $this->perfshield_model->saveSettings($postData);

        echo json_encode([
            'type' => 'success',
            'message' => _l('single_session_settings_updated')
        ]);
    }

    public function clearLogs()
    {
        if (!$this->input->is_ajax_request()) {
            return;
        }

        $res = $this->perfshield_model->clearLogs();

        echo json_encode([
            'type' => $res['type'],
            'message' => $res['message']
        ]);
    }
}

/* End of file Perfshield.php */