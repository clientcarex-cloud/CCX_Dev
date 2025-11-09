<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

// DataTables Params
$draw    = $CI->input->post('draw');
$start   = $CI->input->post('start');
$length  = $CI->input->post('length');
$search  = $CI->input->post('search')['value'] ?? '';
$order   = $CI->input->post('order');
$columns = $CI->input->post('columns');

// Order column setup
$order_column_index = $order[0]['column'] ?? 0;
$order_column_name  = 'p.id';
//$order_dir          = $order[0]['dir'] ?? 'desc';
$order_dir          = 'desc';

// Patient ID

$client_id = $client_id ?? 0;

// Base Query
$CI->db->select('p.*, type.appointment_type_name, criteria.criteria_name');
$CI->db->from(db_prefix() . 'patient_call_logs p');
$CI->db->join(db_prefix() . 'appointment_type type', 'type.appointment_type_id = p.appointment_type_id', 'left');
$CI->db->join(db_prefix() . 'criteria criteria', 'criteria.criteria_id = p.criteria_id', 'left');
$CI->db->where('p.patientid', $client_id);

// Search Filter
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('p.comments', $search);
    $CI->db->or_like('p.better_patient', $search);
    $CI->db->or_like('criteria.criteria_name', $search);
    $CI->db->group_end();
}

// Total count before limit
$total_records = $CI->db->count_all_results('', false);

// Order & Pagination
$CI->db->order_by("p.created_date", "DESC");
$CI->db->limit($length, $start);

$results = $CI->db->get()->result_array();

// Prepare Data
$data = [];
$serial = $start + 1;

foreach ($results as $row) {
    $data[] = [
        $serial++,
        e(get_staff_full_name($row['called_by'])),
        e($row['criteria_name']),
        _d($row['next_calling_date']),
        e($row['better_patient']),
        e($row['pharmacy_medicine_days']),
        e($row['patient_took_medicine_days']),
        // Uncomment below if you want to include appointment_type and appointment_date again
        // e($row['appointment_type_name']),
        // _d($row['appointment_date']),
        _d($row['created_date']),
        e($row['comments']),
    ];
}

// Return JSON
echo json_encode([
    'draw' => intval($draw),
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records,
    'aaData' => $data,
]);

exit;
