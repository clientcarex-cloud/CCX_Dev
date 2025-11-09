<?php

defined('BASEPATH') or exit('No direct script access allowed');

function lead_rollercoaster($lead_source = null, $manual_assigned = null) {
	
    if (get_option('lead_rollercoaster_enabled') != 1) {
        return $manual_assigned ?? 1; // Respect manual input or fallback
    }

    $CI =& get_instance();
    $CI->load->database();

    $settings = $CI->db->get(db_prefix() . 'lead_rollercoaster_settings')->row();
    if (!$settings) {
        return 1;
    }
	
	$selected_sources = json_decode($settings->selected_sources ?? '[]');

    // 🔒 Step 1: Check if current lead source is in selected sources
    if ($lead_source && !in_array($lead_source, $selected_sources)) {
        return $manual_assigned ?? 1;
    }

    $selected_roles = json_decode($settings->selected_roles ?? '[]');
    $fallback_option = $settings->fallback_option ?? 'last_logged';

    // Step 1: Get active logged-in users from selected roles
    $CI->db->select(db_prefix().'staff.staffid');
    $CI->db->from(db_prefix().'staff');
    $CI->db->join(db_prefix().'roles', db_prefix().'roles.roleid = '.db_prefix().'staff.role', 'inner');
    $CI->db->where_in(db_prefix().'roles.name', $selected_roles);
    $CI->db->where(db_prefix().'staff.is_logged_in', 1);
    $logged_in_staff = $CI->db->get()->result_array();

    if (!empty($logged_in_staff)) {
        $staff_ids = array_column($logged_in_staff, 'staffid');

        // Get lead count for each
        $lead_counts = [];
        foreach ($staff_ids as $staffid) {
            $CI->db->where('assigned', $staffid);
            $lead_counts[$staffid] = $CI->db->count_all_results(db_prefix().'leads');
        }

        // Return least loaded staff
        asort($lead_counts);
        return key($lead_counts);
    }

    // Step 2: Fallback logic
    switch ($fallback_option) {
        case 'last_logged':
            $CI->db->select('staffid');
            $CI->db->from(db_prefix().'staff');
            $CI->db->order_by('last_activity', 'DESC');
            $CI->db->limit(1);
            $last_logged = $CI->db->get()->row();
            return $last_logged->staffid ?? 1;

        case 'reporting_manager':
        case 'manager_and_public':
        case 'specific_user':
            $fallback_ids = json_decode($settings->fallback_employee_id ?? '[]');
            if (!empty($fallback_ids)) {
                // Get lead count for fallback users
                $lead_counts = [];
                foreach ($fallback_ids as $staffid) {
                    $CI->db->where('assigned', $staffid);
                    $lead_counts[$staffid] = $CI->db->count_all_results(db_prefix().'leads');
                }
                asort($lead_counts);
                return key($lead_counts);
            }
            return 1;

        case 'business_timing_then_manager':
            $from = $settings->business_timing_from ?? '09:00';
            $to   = $settings->business_timing_to ?? '18:00';
            $current_time = date('H:i');
            if ($current_time >= $from && $current_time <= $to) {
                // Try assigning from logged in users if still active (shouldn't reach here, but fallback)
                return 1;
            } else {
                $fallback_ids = json_decode($settings->fallback_employee_id ?? '[]');
                if (!empty($fallback_ids)) {
                    $lead_counts = [];
                    foreach ($fallback_ids as $staffid) {
                        $CI->db->where('assigned', $staffid);
                        $lead_counts[$staffid] = $CI->db->count_all_results(db_prefix().'leads');
                    }
                    asort($lead_counts);
                    return key($lead_counts);
                }
                return 1;
            }

        default:
            return 1;
    }
}