<?php defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('tasks_model');

// DataTables request inputs
$draw   = intval($CI->input->post('draw'));
$start  = intval($CI->input->post('start'));
$length = intval($CI->input->post('length'));

// Load statuses
$statuses = $CI->tasks_model->get_statuses();

// Total tasks
$total_tasks = $CI->db->count_all(db_prefix().'tasks');

// Prepare rows
$data = [];
$i = $start + 1;
$grand_count = 0;

foreach ($statuses as $status) {
    $count = $CI->db->where('status', $status['id'])
        ->count_all_results(db_prefix().'tasks');

    $grand_count += $count;

    $percentage = $total_tasks > 0
        ? round(($count / $total_tasks) * 100, 2) . '%'
        : '0%';

    $row = [];
    $row[] = $i++;
    $row[] = e($status['name']);
    $row[] = $count;
    $row[] = $percentage;

    $data[] = $row;
}

// Add totals row
$totals_row = [];
$totals_row[] = ''; // empty for S.No
$totals_row[] = '<strong>'._l('total').'</strong>';
$totals_row[] = '<strong>'.$grand_count.'</strong>';
$totals_row[] = $total_tasks > 0 ? '<strong>100%</strong>' : '<strong>0%</strong>';

$data[] = $totals_row;

// DataTables response
$output = [
    "draw" => $draw,
    "recordsTotal" => count($statuses) + 1, // include totals row
    "recordsFiltered" => count($statuses) + 1,
    "data" => $data,
];

echo json_encode($output);
exit;
