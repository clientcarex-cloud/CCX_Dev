<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();
$CI->load->helper('invoices');

// DataTables params
$draw    = $CI->input->post('draw');
$start   = $CI->input->post('start');
$length  = $CI->input->post('length');
$search  = $CI->input->post('search')['value'] ?? '';
$order   = $CI->input->post('order');
$columns = $CI->input->post('columns');

$order_column_index = $order[0]['column'] ?? 0;
$order_column_name  = $columns[$order_column_index]['data'] ?? 'inv.date';
$order_dir          = $order[0]['dir'] ?? 'desc';

// Set client_id properly
$client_id = $client_id ?? 0;

// Build query
$CI->db->select('inv.id, inv.number, inv.total, inv.currency, inv.date, inv.duedate, inv.status, item.description, branch.name as branch_name, payment_category.appointment_type_name,');
$CI->db->from(db_prefix() . 'invoices inv');
$CI->db->join(db_prefix() . 'itemable item', 'item.rel_id = inv.id AND item.rel_type = "invoice"', 'left');
$CI->db->join(db_prefix() . 'customers_groups as branch', 'branch.id = inv.branch_id', 'left');

$CI->db->join(db_prefix() . 'appointment_type as payment_category', 'payment_category.appointment_type_id = inv.appointment_type_id', 'left');
$CI->db->where('inv.clientid', $client_id);

// Search
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('inv.number', $search);
    $CI->db->or_like('inv.date', $search);
    $CI->db->or_like('item.description', $search);
    $CI->db->group_end();
}

// Count before limiting
$total_records = $CI->db->count_all_results('', false);

// Ordering & pagination
//$CI->db->order_by($order_column_name, $order_dir);
$CI->db->order_by("inv.date", "DESC");
$CI->db->limit($length, $start);

// Execute query
$results = $CI->db->get()->result_array();

// Prepare response data
$data = [];
$serial = $start + 1;

foreach ($results as $row) {
    $due = get_invoice_total_left_to_pay($row['id'], $row['total']);
    $status_label = format_invoice_status_custom($row['status']);

    $pay_button = ($due > 0)
        ? '<button class="btn btn-sm btn-primary" onclick="openPaymentForm(' . $row['id'] . ')">' . _l('pay') . '</button>'
        : '';

    // Show edit icon if status is not 2
    /* $edit_button = ($row['status'] != 2)
        ? '<a href="' . admin_url('invoices/invoice/' . $row['id']) . '" class="btn btn-sm btn-default mleft5" title="' . _l('edit') . '">
                <i class="fa fa-edit"></i>
           </a>'
        : ''; */
	$edit_button = '';
    $action_buttons = $pay_button . ' ' . $edit_button;

    $data[] = [
        $serial++,
        e($row['number']),
        e($row['branch_name']),
        e($row['appointment_type_name']),
        app_format_money_custom($row['total'], $row['currency']),
        app_format_money_custom(($row['total'] - $due), $row['currency']),
        app_format_money_custom($due, $row['currency']),
        _d($row['date']),
        e($row['description']),
        _d($row['duedate']),
        $status_label,
        $action_buttons,
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
