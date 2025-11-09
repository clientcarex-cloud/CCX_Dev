<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Token_system_lib
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    /**
     * Change Token Status
     * 
     * @param int $token_id
     * @param string $status ('Pending', 'Serving', 'Completed')
     * @return bool
     */
    public function update_token_status($token_id, $status)
    {
        if (!$token_id || !$status) {
            return false;
        }

        $this->CI->db->where('token_id', $token_id);
        return $this->CI->db->update(db_prefix() . 'tokens', [
            'token_status' => $status,
        ]);
    }

    /**
     * Complete all currently serving patients
     * 
     * @return bool
     */
    public function complete_current_serving()
    {
        $this->CI->db->where('token_status', 'Serving');
        return $this->CI->db->update(db_prefix() . 'tokens', [
            'token_status' => 'Completed',
        ]);
    }
}
