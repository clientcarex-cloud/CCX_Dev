<?php

/**
 * Get the client IP address
 *
 * @return string The client IP address
 */
if (!function_exists('getClientIp')) {
    function getClientIp()
    {
        $ipAddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipAddress = 'UNKNOWN';
        }
        return $ipAddress;
    }
}

/**
 * Check if an IP address is blocked
 *
 * @param string $ipAddress The IP address to check
 * @return bool
 */
if (!function_exists('isIpBlocked')) {
    function isIpBlocked($ipAddress)
    {
        // From Blocked IP list
        $blacklistIps = get_instance()->db->get_where(db_prefix() . 'blacklist', ['type' => 'ip'])->result_array();
        $list = array_column($blacklistIps, 'ip_email');

        $match = false;
        foreach ($list as $item) {

            if (strpos($item, '/') !== false || strpos($item, '-') !== false || strpos($item, '*') !== false) {
                //the IP in the list is in one of the acceptable range formats
                $match = ip_in_range($ipAddress, $item);
            } elseif ($ipAddress == $item) {
                $match = true;
            }

            //break as soon as a match is found
            if ($match) break;
        }

        return $match;
    }
}


/**
 * Check if an Email address is blocked
 *
 * @param string $ipAddress The IP address to check
 * @return bool
 */
if (!function_exists('isEmailBlocked')) {
    function isEmailBlocked($email)
    {
        // From Blocked emails list
        $blacklistEmails = get_instance()->db->get_where(db_prefix() . 'blacklist', ['ip_email' => $email, 'type' => 'email'])->row();

        return !empty($blacklistEmails);
    }
}

/**
 * Removes blank values from an array
 *
 * @param  array Array that needs to be checked
 * @param  string Key of an array
 * @return array
 */
if (!function_exists('remove_blank_value')) {
    function remove_blank_value($var, $key_to_check)
    {
        $data = [];
        foreach ($var as $key => $value) {
            if ('' === $value[$key_to_check]) {
                unset($var[$key]);
                continue;
            }
            $data[] = $value;
        }

        return $data;
    }
}

if (!function_exists('isLockedUser')) {
    function isLockedUser($email, $ipAddress)
    {
        // Staff Login
        $staff = get_instance()->db->where('email', $email)->get(db_prefix() . 'staff')->row();
        if(!empty($staff->last_login)){
            get_instance()->db->where('time >', strtotime($staff->last_login), FALSE);
        }
        $time_condition = !empty(RESET_RETRIES) ? RESET_RETRIES * 3600 : 3600;

        $res = get_instance()->db
                                ->where('email', $email)
                                ->where('ip', $ipAddress)
                                ->where('time >', time() - ($time_condition), FALSE)
                                ->order_by("time", "DESC")
                                ->get(db_prefix() . 'perfshield_logs');
        return $res->result();
    }
}

if (!function_exists('getStaffExpiryDateByEmail')) {
    function getStaffExpiryDateByEmail($email)
    {
        $staffDetails = get_instance()->db->get_where(db_prefix() . 'staff', ['email' => $email])->row();
        return (!empty($staffDetails)) ? $staffDetails->expiry_date : false;
    }
}

if (!function_exists('getExpiredStaff')) {
    function getExpiredStaff()
    {
        $staffDetails = get_instance()->db->get(db_prefix() . 'staff')->result_array();
        $currentDate  = strtotime(date('Y-m-d'));

        return array_filter($staffDetails, function($value) use ($currentDate) {
            return $currentDate > strtotime($value['expiry_date'] ?? '') && !empty($value['expiry_date']);
        });
    }
}

if (!function_exists('getLockoutCycleCount')) {
    function getLockoutCycleCount($count)
    {
        $lockoutCycleCount = 0;
        // Loop through the records
        for ($i = 1; $i <= $count; $i++) {
            // Check if the current record is a multiple of maximun number of retries.
            if ($i % MAX_RETRIES == 0) {
                $lockoutCycleCount++; // Increment the lockoutCycleCount variable
            }
        }

        return $lockoutCycleCount;
    }
}

if (!function_exists('getStaffDetailsByEmail')) {
    function getStaffDetailsByEmail($email)
    {
        $staffDetails = get_instance()->db->get_where(db_prefix() . 'staff', ['email' => $email])->row_array();

        return (!empty($staffDetails)) ? $staffDetails : false;
    }
}

if (!function_exists('getAdminEmail')) {
    function getAdminEmail()
    {
        $adminEmail = get_instance()->db->get_where(db_prefix() . 'staff', ['admin' => '1', 'role' => NULL, 'active' => '1'])->row();
        return (!empty($adminEmail)) ? $adminEmail->email : '';
    }
}
