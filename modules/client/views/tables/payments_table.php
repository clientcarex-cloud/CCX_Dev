<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

// DataTables parameters
$draw    = $CI->input->post('draw');
$start   = $CI->input->post('start');
$length  = $CI->input->post('length');
$search  = $CI->input->post('search')['value'] ?? '';
$order   = $CI->input->post('order');
$columns = $CI->input->post('columns');

$order_column_index = $order[0]['column'] ?? 0;
$order_column_name  = $columns[$order_column_index]['data'] ?? 'payment.id';
$order_dir          = $order[0]['dir'] ?? 'desc';

$client_id = $client_id ?? 0;

// Main query
$CI->db->select('payment.id, payment.date, payment.amount, mode.name as payment_mode, staff.firstname, staff.lastname, payment.transactionid, payment.utr_no, branch.name as branch_name');
$CI->db->from(db_prefix() . 'invoicepaymentrecords payment');
$CI->db->join(db_prefix() . 'invoices inv', 'inv.id = payment.invoiceid', 'left');
$CI->db->join(db_prefix() . 'payment_modes mode', 'mode.id = payment.paymentmode', 'left');
$CI->db->join(db_prefix() . 'customers_groups as branch', 'branch.id = inv.branch_id', 'left');
$CI->db->join(db_prefix() . 'staff staff', 'staff.staffid = payment.received_by', 'left');
$CI->db->where('inv.clientid', $client_id);

// Search
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('payment.id', $search);
    $CI->db->like('payment.transactionid', $search);
    $CI->db->or_like('mode.name', $search);
    $CI->db->or_like('payment.date', $search);
    $CI->db->or_like('staff.firstname', $search);
    $CI->db->or_like('staff.lastname', $search);
    $CI->db->or_like('payment.amount', $search);
    $CI->db->group_end();
}

// Total count before filtering
$total_records = $CI->db->count_all_results('', false);

// Apply ordering and pagination
$CI->db->order_by("payment.date", "DESC");
$CI->db->limit($length, $start);

// Get result
$results = $CI->db->get()->result_array();

// Prepare output
$data = [];
$serial = $start + 1;

foreach ($results as $row) {
    $data[] = [
        e($row['id']),
        e($row['branch_name']),
        e($row['utr_no']),
        e($row['transactionid']),
        e($row['payment_mode']),
        _d($row['date']),
        e($row['firstname'] . ' ' . $row['lastname']),
        app_format_money_custom($row['amount'], '1'),
        '<a href="' . admin_url('payments/pdf/' . $row['id'] . '?print=true') . '" target="_blank"><i class="fa fa-print"></i></a>',
    ];
}

// Return response
echo json_encode([
    'draw' => intval($draw),
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records,
    'aaData' => $data,
]);

exit;
