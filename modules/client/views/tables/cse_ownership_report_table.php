<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// DataTables inputs
$draw   = intval($CI->input->post('draw'));
$start  = intval($CI->input->post('start'));
$length = intval($CI->input->post('length'));
$search = $CI->input->post('search')['value'] ?? '';

$start  = is_numeric($start) ? (int) $start : 0;
$length = is_numeric($length) ? (int) $length : 10;

// Step 1: Total count without filter
$CI->db->from(db_prefix() . 'customers_groups');
$totalRecords = $CI->db->count_all_results();

// Step 2: Count filtered records
$CI->db->from(db_prefix() . 'customers_groups');
if (!empty($search)) {
    $CI->db->like('name', $search);
}
$filteredRecords = $CI->db->count_all_results();

// Step 3: Apply limit, offset, and search again for data fetch
$CI->db->select('id, name');
$CI->db->from(db_prefix() . 'customers_groups');
if (!empty($search)) {
    $CI->db->like('name', $search);
}
$CI->db->limit($length, $start);
$branches = $CI->db->get()->result_array();

// Step 4: Prepare data for DataTables
$data = [];
foreach ($branches as $branch) {
    $data[] = [$branch['name']];
}

// Step 5: Output JSON
$output = [
    'draw' => $draw,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data' => $data
];

echo json_encode($output);
exit;
