<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$CI->load->helper('date'); // if needed


$start  = $CI->input->post('start');  // For pagination offset
$length = $CI->input->post('length'); // For pagination limit
$length = $length === null ? 10 : (int)$length; // default 10
$start  = $start === null ? 0 : (int)$start;

// Base query
$CI->db->select('appointment_type.appointment_type_name, COUNT(appointments.appointment_id) as no_of_appointments');
$CI->db->from(db_prefix() . 'appointment appointments');
$CI->db->join(db_prefix() . 'appointment_type appointment_type', 'appointment_type.appointment_type_id = appointments.appointment_type_id', 'left');

// Branch filter
if (!empty($selected_branch_id) && is_numeric($selected_branch_id)) {
    $CI->db->where('appointments.branch_id', $selected_branch_id);
}

// Date filter (consulted_date or appointment_date in range)
if ($consulted_from_date && $consulted_to_date) {
    $from_date = date('Y-m-d', strtotime($consulted_from_date));
    $to_date = date('Y-m-d', strtotime($consulted_to_date));
    
    $CI->db->group_start();
    $CI->db->where("DATE(appointments.consulted_date) >=", $from_date);
    $CI->db->where("DATE(appointments.consulted_date) <=", $to_date);
    $CI->db->or_group_start();
    $CI->db->where("DATE(appointments.appointment_date) >=", $from_date);
    $CI->db->where("DATE(appointments.appointment_date) <=", $to_date);
    $CI->db->group_end();
    $CI->db->group_end();
} elseif ($consulted_from_date) {
    $date = date('Y-m-d', strtotime($consulted_from_date));
    $CI->db->group_start();
    $CI->db->where("DATE(appointments.consulted_date)", $date);
    $CI->db->or_where("DATE(appointments.appointment_date)", $date);
    $CI->db->group_end();
}

// Group by appointment type
$CI->db->group_by('appointments.appointment_type_id');

// Total count before limit for pagination
$totalQuery = clone $CI->db;
$totalResults = $totalQuery->get()->num_rows();

// Add pagination limit and offset
$CI->db->limit($length, $start);

// Optional: order by appointment type name ascending
$CI->db->order_by('appointment_type.appointment_type_name', 'ASC');

// Get final result
$query = $CI->db->get();
$results = $query->result_array();

// Prepare output for DataTables
$output = [
    "draw" => intval($CI->input->post('draw')), // for DataTables draw count
    "recordsTotal" => $totalResults,
    "recordsFiltered" => $totalResults,
    "data" => [],
];

foreach ($results as $row) {
    $output['data'][] = [
        $row['appointment_type_name'],
        $row['no_of_appointments'],
    ];
}

// Return output as JSON or use as needed
echo json_encode($output);
exit;
