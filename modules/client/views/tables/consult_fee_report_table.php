<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('client_model');

// DataTables input
$draw   = $CI->input->post('draw');
$length = $CI->input->post('length');
$start  = $CI->input->post('start');
$search = $CI->input->post('search')['value'] ?? '';
//$type   = $CI->input->post('type') ?? 'default';

// ------------------- 1. Total count -------------------
$CI->db->from(db_prefix() . 'itemable item');
$CI->db->join(db_prefix() . 'invoices inv', 'inv.id = item.rel_id', 'right');
$CI->db->join(db_prefix() . 'clients c', 'c.userid = inv.clientid', 'right');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'right');
$CI->db->join(db_prefix() . 'invoicepaymentrecords payment', 'payment.invoiceid = inv.id', 'left');
$CI->db->join(db_prefix() . 'payment_modes mode', 'mode.id = payment.paymentmode', 'left');
$CI->db->join(db_prefix() . 'appointment a', 'a.userid = c.userid', 'right');
$CI->db->where(['item.rel_type' => 'invoice', 'item.description' => 'Consultation Fee']);
$total_count = $CI->db->count_all_results();

// ------------------- 2. Main Query -------------------
$CI->db->select("
    item.id as item_id,
    item.description as item_description,
    item.qty as item_qty,
    item.rate as item_rate,
    c.userid as patient_id,
    c.company as patient_name,
    new.mr_no,
    inv.id as invoice_id,
    inv.total,
    inv.date as invoice_date,
    a.appointment_id,
    a.appointment_date,
    a.consulted_date,
    mode.name as payment_mode,
    payment.date as payment_date,
    payment.id as payment_id
");

$CI->db->from(db_prefix() . 'itemable item');
$CI->db->join(db_prefix() . 'invoices inv', 'inv.id = item.rel_id', 'left');
$CI->db->join(db_prefix() . 'clients c', 'c.userid = inv.clientid', 'left');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
$CI->db->join(db_prefix() . 'invoicepaymentrecords payment', 'payment.invoiceid = inv.id', 'left');
$CI->db->join(db_prefix() . 'payment_modes mode', 'mode.id = payment.paymentmode', 'left');
$CI->db->join(db_prefix() . 'appointment a', 'a.userid = c.userid', 'left');
$CI->db->where(['item.rel_type' => 'invoice', 'item.description' => 'Consultation Fee']);



if (!empty($selected_branch_id)) {
    // Handle when it's a single numeric ID
    if (is_numeric($selected_branch_id)) {
        $CI->db->where('a.branch_id', intval($selected_branch_id));
    }
    // Handle when it's an array of IDs
    elseif (is_array($selected_branch_id)) {
        // Sanitize the array values to ensure they're all integers
        $branch_ids = array_map('intval', $selected_branch_id);
        $CI->db->where_in('a.branch_id', $branch_ids);
    }
    // Handle when it's a comma-separated string
    elseif (is_string($selected_branch_id) && strpos($selected_branch_id, ',') !== false) {
        $branch_ids = array_map('intval', explode(',', $selected_branch_id));
        $CI->db->where_in('a.branch_id', $branch_ids);
    }
    // Handle any other unexpected cases
    else {
        // Optionally log an error or handle unexpected input
        //log_activity('Unexpected branch_id format: ' . print_r($selected_branch_id, true));
    }
}

$CI->db->group_by('inv.id');

if ($consulted_from_date && $consulted_to_date) {
    $from_date = to_sql_date($consulted_from_date);
    $to_date = to_sql_date($consulted_to_date);

    // Apply date range filter on either consulted_date or appointment_date
    $CI->db->group_start();
    $CI->db->where("DATE(payment.date) >=", $from_date);
    $CI->db->where("DATE(payment.date) <=", $to_date);
    $CI->db->or_group_start();
    $CI->db->where("DATE(payment.date) >=", $from_date);
    $CI->db->where("DATE(payment.date) <=", $to_date);
    $CI->db->group_end();
    $CI->db->group_end();
} elseif ($consulted_from_date) {
    $sql_date = to_sql_date($consulted_from_date);

    $CI->db->group_start();
    $CI->db->where("DATE(payment.date)", $sql_date);
    $CI->db->or_where("DATE(payment.date)", $sql_date);
    $CI->db->group_end();
}
// Apply search if needed
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('c.company', $search);
    $CI->db->or_like('new.mr_no', $search);
    $CI->db->or_like('mode.name', $search);
    $CI->db->or_like('payment.date', $search);
    $CI->db->group_end();
}

// Clone the query to count filtered records
$filtered_query = clone $CI->db;
$filtered_count = $filtered_query->count_all_results();

// Pagination
if ($length != -1) {
    $CI->db->limit($length, $start);
}
/* echo $CI->db->get_compiled_select();
exit; */
// Final fetch
$query = $CI->db->get();
$data = $query->result_array();
// Output for DataTables
$output = [
    'draw' => intval($draw),
    'recordsTotal' => $total_count,
    'recordsFiltered' => $filtered_count,
    'data' => []
];

// Build each row
foreach ($data as $row) {
        $dataRow = [];

        $url = admin_url('client/reports/' . $type . '/' . $row['patient_id']);
        $company = '<a href="' . $url . '" class="tw-font-medium">' . $row['patient_name'] . '</a>';

        $dataRow[] = $company;
        $dataRow[] = $row['mr_no'];
        $dataRow[] = _d($row['consulted_date']);
        $dataRow[] = _d($row['consulted_date']);
        $dataRow[] = _d(date("Y-m-d", strtotime($row['appointment_date'])));
        $dataRow[] = $row['total'];
        $dataRow[] = $row['payment_mode'];
        $dataRow[] = _d($row['payment_date']);
        $dataRow[] = "";
        $dataRow[] = '<a href="' . admin_url('payments/pdf/' . $row['payment_id'] . '?print=true') . '" target="_blank"><i class="fa fa-print"></i></a>';

        $output['data'][] = $dataRow;
    
}

echo json_encode($output);
exit;
