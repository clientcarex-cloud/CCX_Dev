<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$draw = intval($CI->input->post('draw'));
$start = intval($CI->input->post('start'));
$length = intval($CI->input->post('length'));
$search = $CI->input->post('search')['value'] ?? '';
$order_column_index = $CI->input->post('order')[0]['column'] ?? 0;
$order_dir = $CI->input->post('order')[0]['dir'] ?? 'asc';

$columns = ['id', 'display_name', 'queue_type', 'doctor_info', 'media_type', 'display_patient_info', 'created_at'];
$order_column = $columns[$order_column_index] ?? 'id';

// Total count
$totalRecords = $CI->db->count_all(db_prefix() . 'display_config');

// Base query
$CI->db->select('id, display_name, queue_type, doctor_info, media_type, display_patient_info, created_at');
$CI->db->from(db_prefix() . 'display_config');

if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('display_name', $search);
    $CI->db->or_like('queue_type', $search);
    $CI->db->or_like('media_type', $search);
    $CI->db->or_like('display_patient_info', $search);
    $CI->db->group_end();
}

// Filtered records
$filteredQuery = clone $CI->db;
$filteredRecords = $filteredQuery->count_all_results();

// Ordering and Pagination
$CI->db->order_by($order_column, $order_dir);
if ($length != -1) {
    $CI->db->limit($length, $start);
}

$results = $CI->db->get()->result_array();

// Prepare output
$output = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => []
];

$i = $start + 1;

foreach ($results as $row) {
    $dataRow = [];

    $dataRow[] = $i++;
    $dataRow[] = '<strong>' . e($row['display_name']) . '</strong>';
    $dataRow[] = e($row['queue_type']);
    $dataRow[] = ($row['doctor_info'] == 1) ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>';
    $dataRow[] = e($row['media_type']);
    $dataRow[] = e($row['display_patient_info']);
    $dataRow[] = _dt($row['created_at']);

    $actions = '<a href="' . admin_url('token_system/edit_display/' . $row['id']) . '" class="btn btn-sm btn-primary" style="color: #fff">' . _l('edit') . '</a>';


    $dataRow[] = $actions;

    $output['data'][] = $dataRow;
}

echo json_encode($output);
exit;
