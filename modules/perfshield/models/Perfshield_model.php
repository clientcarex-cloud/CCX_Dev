<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Perfshield_model extends App_Model
{

    public function saveSettings($postData)
    {
        // Loop through the new settings and update them in the database
        $this->load->model(['payment_modes_model', 'settings_model']);
        $success = $this->settings_model->update($postData);

        // Return true indicating settings are updated
        return true;
    }

    /**
     * Add a batch of IP addresses or Email Addresses to the blacklist.
     *
     * @param array $postData The array of data to be inserted.
     * @return array Returns an array containing the type of operation ('success' or 'danger') and a message indicating the status of the operation.
     */
    public function addIpOrEmailToBlacklist($postData)
    {
        // Insert the data into the database
        $insert = $this->db->insert_batch(db_prefix() . 'blacklist', $postData);

        // Check if any records were inserted
        $type = $insert ? 'success' : 'danger';

        // Set the appropriate message based on the type of data being blacklisted
        $message = ($postData[0]['type'] == 'ip') ? _l('ip_added_to_blacklist') : _l('email_added_to_blacklist');

        // Return an array with the operation type and status message
        return [
            'type'    => $type,
            'message' => $insert ? $message : _l('something_went_wrong')
        ];
    }

    /**
     * Retrieves the list of available users
     *
     * @return array List of available users
     */
    public function getUsers()
    {
        // Get list of users that are already added in blacklist
        $blacklist = $this->db->get_where(db_prefix() . 'blacklist', ['email !=' => null])->result_array();

        // Exclude users that are already in blacklist
        if (!empty($blacklist)) {
            $this->db->where_not_in('email', array_column($blacklist, 'email'));
        }

        // Return list of available users
        return $this->db->get(db_prefix() . 'staff')->result_array();
    }

    /**
     * Removes a IP or Email from the blacklist.
     *
     * @param string $category ('ip' or 'email')
     * @return int $id ID of the record to be deleted.
     */
    public function removeIpOrEmailFromBlacklist($category, $id)
    {
        // Delete IP or email from the blacklist
        $delete = $this->db->delete(db_prefix() . 'blacklist', ['id' => $id]);

        $message = ($category == 'ip') ? _l('ip_removed_from_blacklist') : _l('email_removed_from_blacklist');

        // Return result of the operation and a message to display
        return [
            'type'    => 'danger',
            'message' => $delete ? $message : _l('something_went_wrong')
        ];
    }

    /**
     * Update IP Address in the blacklist.
     *
     * @param  array $postData Details of the IP Address
     * @return array Returns an array containing the type of operation ('success' or 'danger') and a message indicating the status of the operation.
     */
    public function updateIPAddress($postData)
    {
        $res = $this->db->update(db_prefix() . 'blacklist', $postData, ['id' => $postData['id']]);

        return [
            'type'    => $res ? 'success' : 'danger',
            'message' => $res ? _l('ip_updated') : _l('something_went_wrong')
        ];
    }

    public function isUniqueIpOrEmail($ipEmail, $type)
    {
        $blacklistData = $this->db->where_in('ip_email', $ipEmail)->where('type', $type)->get(db_prefix() . 'blacklist')->result_array();

        if (!empty($blacklistData)) {
            return [
                'success' => false,
                'message' => ($type == 'ip') ? _l('ip_address_already_exists') : _l('email_address_already_exists'),
                'ipEmail' => array_column($blacklistData, 'ip_email')
            ];
        }

        return [
            'success' => true,
            'message' => ''
        ];
    }

    public function addStaffExpiry($postData)
    {
        $postData['expiry_date'] = to_sql_date($postData['expiry_date']);

        $res = $this->db->update(db_prefix() . 'staff', $postData, ['staffid' => $postData['staffid']]);

        return [
            'type'    => $res ? 'success' : 'danger',
            'message' => $res ? _l('expiry_date_added') : _l('something_went_wrong')
        ];
    }

    public function removeStaffExpiry($staffID)
    {
        $res = $this->db->update(db_prefix() . 'staff', ['expiry_date' => NULL], ['staffid' => $staffID]);

        return [
            'type'    => $res ? 'success' : 'danger',
            'message' => $res ? _l('expiry_date_removed') : _l('something_went_wrong')
        ];
    }

    public function clearLogs()
    {
        $res = $this->db->truncate(db_prefix() . 'perfshield_logs');

        return [
            'type'    => 'danger',
            'message' => $res ? _l('logs_cleared') : _l('something_went_wrong'),
        ];
    }
}

/* End of file perfshield_model.php */
/* Location: ./application/models/perfshield_model.php */