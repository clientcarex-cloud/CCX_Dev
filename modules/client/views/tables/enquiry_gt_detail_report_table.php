<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Load database if not already loaded
$CI->load->database();

// DataTables request variables
$draw = intval($CI->input->post('draw') ?? 1);
$search_value = $CI->input->post('search')['value'] ?? '';
$start = intval($CI->input->post('start') ?? 0);
$length = intval($CI->input->post('length') ?? 10);

// URL parameters
$from_date = $consulted_from_date;
$to_date = $consulted_to_date;
$branch_id = $selected_branch_id;
$report_type = $category;

// Start building the query
$CI->db->select([
    'c.company as patient_name',
    'c.userid',
    'cn.mr_no',
    'ls.name as source_name',
    't.description as treatment_name',
    'c.datecreated as created_date',
    'a.appointment_date',
    'a.visited_date',
    'a.consulted_date',
    'cn.registration_start_date as registered_date',
    'inv.total as package_amount',
    '(SELECT SUM(amount) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = inv.id) as paid_amount'
]);

$CI->db->from(db_prefix() . 'clients c');
$CI->db->join(db_prefix() . 'clients_new_fields cn', 'cn.userid = c.userid', 'left');
$CI->db->join(db_prefix() . 'leads l', 'l.id = c.leadid', 'left');
$CI->db->join(db_prefix() . 'leads_sources ls', 'ls.id = l.source', 'left');
$CI->db->join(db_prefix() . 'appointment a', 'a.userid = c.userid', 'left');
$CI->db->join(db_prefix() . 'items t', 't.id = a.treatment_id', 'left');
$CI->db->join(db_prefix() . 'invoices inv', 'inv.clientid = c.userid', 'left');
$CI->db->join(db_prefix() . 'customer_groups cg', 'cg.customer_id = c.userid', 'left');

// Base filtering
$CI->db->where_in('cg.groupid', $branch_id);

// Dynamic filtering based on report type
switch ($report_type) {
    case 'visit_np':
        $CI->db->where('a.visit_status', 1);
        $CI->db->where('a.appointment_date >=', $from_date . ' 00:00:00');
        $CI->db->where('a.appointment_date <=', $to_date . ' 23:59:59');
        $CI->db->where('(l.refer_id IS NULL OR l.refer_id = 0)', null, false);
        break;

    case 'visit_ref':
        $CI->db->where('a.visit_status', 1);
        $CI->db->where('a.appointment_date >=', $from_date . ' 00:00:00');
        $CI->db->where('a.appointment_date <=', $to_date . ' 23:59:59');
        $CI->db->where('l.refer_id >', 0);
        break;

    case 'visit_total':
        $CI->db->where('a.visit_status', 1);
        $CI->db->where('a.appointment_date >=', $from_date . ' 00:00:00');
        $CI->db->where('a.appointment_date <=', $to_date . ' 23:59:59');
        break;

    case 'reg_np':
        $CI->db->where('cn.registration_start_date >=', $from_date . ' 00:00:00');
        $CI->db->where('cn.registration_start_date <=', $to_date . ' 23:59:59');
        $CI->db->where('(cn.mr_no IS NOT NULL AND cn.mr_no != "")', null, false);
        $CI->db->where('(l.refer_id IS NULL OR l.refer_id = 0)', null, false);
        break;

    case 'reg_ref':
        $CI->db->where('cn.registration_start_date >=', $from_date . ' 00:00:00');
        $CI->db->where('cn.registration_start_date <=', $to_date . ' 23:59:59');
        $CI->db->where('(cn.mr_no IS NOT NULL AND cn.mr_no != "")', null, false);
        $CI->db->where('l.refer_id >', 0);
        break;

    case 'reg_total':
        $CI->db->where('cn.registration_start_date >=', $from_date . ' 00:00:00');
        $CI->db->where('cn.registration_start_date <=', $to_date . ' 23:59:59');
        $CI->db->where('(cn.mr_no IS NOT NULL AND cn.mr_no != "")', null, false);
        break;

    default:
        break;
}

// Add search filter
if (!empty($search_value)) {
    $CI->db->group_start();
    $CI->db->like('c.company', $search_value);
    $CI->db->or_like('cn.mr_no', $search_value);
    $CI->db->group_end();
}

// Group and order
$CI->db->group_by('c.userid');
$CI->db->order_by('a.appointment_date', 'DESC');

// Count total records
$totalQuery = clone $CI->db;
$total_records = $totalQuery->count_all_results('', false);


// Apply limit for pagination
$CI->db->limit($length, $start);

// Execute final query
$results = $CI->db->get()->result_array();
$data = [];
foreach ($results as $row) {
    $due_amount = ($row['package_amount'] ?? 0) - ($row['paid_amount'] ?? 0);

    $data[] = [
        $row['patient_name'],
        $row['mr_no'],
        $row['source_name'],
        $row['treatment_name'],
        _d($row['created_date']),
        _d($row['appointment_date']),
        _d($row['visited_date']),
        _d($row['consulted_date']),
        _d($row['registered_date']),
        app_format_money_custom($row['package_amount'] ?? 0, 1),
        app_format_money_custom($row['paid_amount'] ?? 0, 1),
        app_format_money_custom($due_amount, 1),
    ];
}

$output = [
    'draw' => $draw,
    'recordsTotal' => $total_records,
    'recordsFiltered' => $total_records,
    'data' => $data
];

echo json_encode($output);
exit;
?>
