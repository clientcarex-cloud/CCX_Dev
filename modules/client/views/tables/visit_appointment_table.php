<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

$draw    = $CI->input->post('draw');
$start   = $CI->input->post('start');
$length  = $CI->input->post('length');
$search  = $CI->input->post('search')['value'] ?? '';
$order   = $CI->input->post('order');
$columns = $CI->input->post('columns');

$order_column_index = $order[0]['column'] ?? 0;
//$order_column_name  = $columns[$order_column_index]['data'] ?? 'a.appointment_date';
$order_column_name  = 'a.appointment_id';
//$order_dir          = $order[0]['dir'] ?? 'desc';
$order_dir          = 'desc';

$client_id = $client_id ?? 0;
$today = date('Y-m-d');

// Base query
$CI->db->select('a.*, t.description as treatment_name, s.*, atype.appointment_type_name, c.medicine_days');
$CI->db->from(db_prefix() . 'appointment a');
$CI->db->join(db_prefix() . 'items t', 't.id = a.treatment_id', 'left');
$CI->db->join(db_prefix() . 'appointment_type atype', 'atype.appointment_type_id = a.appointment_type_id', 'left');
$CI->db->join(db_prefix() . 'slots s', 's.slots_id = a.slots_id', 'left');
$CI->db->join(
    db_prefix() . 'casesheet c',
    'c.date = DATE(a.appointment_date) AND c.userid = a.userid AND a.visit_status = 1',
    'left'
);

$CI->db->where('a.userid', $client_id);

// Search filter
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('a.visit_id', $search);
    $CI->db->or_like('a.appointment_date', $search);
    $CI->db->or_like('atype.appointment_type_name', $search);
    $CI->db->group_end();
}

$total_records = $CI->db->count_all_results('', false);

$CI->db->order_by("a.appointment_date", "DESC");
$CI->db->limit($length, $start);
$results = $CI->db->get()->result_array();

$data = [];
$serial = $start + 1;

foreach ($results as $row) {
    $appointmentDate = $row['appointment_date'];

    // Appointment Status Logic (exactly preserved)
    if ($row['visit_status'] == 1) {
        $status_key = '1';
    } else {
        if ($appointmentDate < $today) {
            $status_key = 'missed';
        } elseif ($appointmentDate > $today) {
            $status_key = 'upcoming';
        } else {
            $status_key = 'today';
        }
    }

    $data[] = [
		$serial++,
		e($row['visit_id']),
		_d($row['appointment_date']),
		e($row['treatment_name']),
		format_appointment_status_custom($status_key),
		_d($row['consulted_date']),
		$row['medicine_days'],
		e($row['appointment_type_name']),
		!empty($row['consulted_date']) ? e(get_staff_full_name($row['enquiry_doctor_id'])) : '', // Only show if consulted_date is not null
	];

}

echo json_encode([
    'draw' => intval($draw),
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records,
    'aaData' => $data,
]);

exit;
