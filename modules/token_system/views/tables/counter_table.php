<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$draw   = intval($CI->input->post('draw'));
$start  = intval($CI->input->post('start'));
$length = intval($CI->input->post('length'));
$search = $CI->input->post('search')['value'] ?? '';
$order_column_index = $CI->input->post('order')[0]['column'] ?? 0;
$order_dir = $CI->input->post('order')[0]['dir'] ?? 'asc';

$columns = ['c.counter_id', 'c.counter_name', 'doctor_name', 'dis.display_name', 'c.counter_status', 'c.auth_code', 'c.counter_id']; // last for public_url

$order_column = $columns[$order_column_index] ?? 'c.counter_id';

// Total records count
$totalRecords = $CI->db->count_all(db_prefix() . 'counter');

// Base query with joins
$CI->db->select('c.counter_id, c.counter_name, CONCAT(s.firstname, " ", s.lastname) as doctor_name, dis.display_name, c.counter_status, c.auth_code');
$CI->db->from(db_prefix() . 'counter AS c');
$CI->db->join(db_prefix() . 'staff AS s', 's.staffid = c.doctor_id', 'left');
$CI->db->join(db_prefix() . 'display_config AS dis', 'dis.id = c.display_id', 'left');

// Search
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('c.counter_name', $search);
    $CI->db->or_like('c.auth_code', $search);
    $CI->db->or_like('dis.display_name', $search);
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
    $dataRow[] = e($row['counter_name']);
    $dataRow[] = e($row['doctor_name']);
    $dataRow[] = e($row['display_name']);
    $dataRow[] = e($row['counter_status']);
    $dataRow[] = e($row['auth_code']);
	
	$publicUrl = admin_url('token_system/Public_view/index/' . $row['counter_id']);

$publicUrl = admin_url('token_system/Public_view/index/' . $row['counter_id']);

$copyButton = '<button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboardUrl(\'' . $publicUrl . '\', this)">Copy URL</button>';
$dataRow[] = $copyButton;



    $actions = '<a target="_blank" href="' . admin_url('token_system/display_tokens_view/' . $row['counter_id']) . '" class="btn btn-sm btn-success" style="color: #fff">Doctor View</a> ';
    $actions .= '<a target="_blank" href="' . admin_url('token_system/display_tokens_public_view/' . $row['counter_id']) . '" class="btn btn-sm btn-success" style="color: #fff">View</a> ';
    $actions .= '<a href="' . admin_url('token_system/edit_counter/' . $row['counter_id']) . '" class="btn btn-sm btn-primary" style="color: #fff">Edit</a>';

    $dataRow[] = $actions;

    $output['data'][] = $dataRow;
}

echo json_encode($output);
exit;
