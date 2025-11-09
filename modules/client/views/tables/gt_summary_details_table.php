<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

// DataTables request variables
$draw   = intval($CI->input->post('draw') ?? 1);
$search_value = $CI->input->post('search')['value'] ?? '';
$start  = intval($CI->input->post('start') ?? 0);
$length = intval($CI->input->post('length') ?? 10);

// URL parameters passed from controller
$from_date   = $consulted_from_date;
$to_date     = $consulted_to_date;
$branch_id   = $selected_branch_id;
$report_type = $category;

/**
 * ======================================================
 * Helper functions (same as gt_report, to keep logic same)
 * ======================================================
 */
function apply_np_visit_conditions($CI, $from_date, $to_date, $branch_id)
{
    $appointment_type_id = [18, 2];
    $CI->db->where('a.visit_status', 1);
    $CI->db->where_in('a.appointment_type_id', $appointment_type_id);
    $CI->db->where('a.appointment_date >=', $from_date . ' 00:00:00');
    $CI->db->where('a.appointment_date <=', $to_date . ' 23:59:59');
    $CI->db->where_in('a.branch_id', $branch_id);
}

function apply_np_reg_conditions($CI, $from_date, $to_date, $branch_id)
{
    $appointment_type_id = [18, 2];
    $CI->db->join('tblinvoices as inv', 'inv.clientid = a.userid', 'inner');
    $CI->db->join(db_prefix().'itemable item', 'item.rel_id = inv.id AND item.rel_type="invoice"', 'inner');
    $CI->db->where('a.appointment_date >=', $from_date . ' 00:00:00');
    $CI->db->where('a.appointment_date <=', $to_date . ' 23:59:59');
    $CI->db->where('inv.date >=', $from_date);
    $CI->db->where('inv.date <=', $to_date);
    $CI->db->where_in('a.appointment_type_id', $appointment_type_id);
    $CI->db->where_in('a.branch_id', $branch_id);
    $CI->db->where('a.visit_status', 1);
    $CI->db->where('item.description !=', 'Consultation Fee');
   // $CI->db->group_by('inv.clientid');
    //$CI->db->having('COUNT(DISTINCT inv.id) = 1', null, false);
}

function apply_ren_visit_conditions($CI, $from_date, $to_date, $branch_id)
{
    $appointment_type_id = [6, 11, 17, 24, 32];
    $CI->db->where('a.visit_status', 1);
    $CI->db->where_in('a.appointment_type_id', $appointment_type_id);
    $CI->db->where('a.appointment_date >=', $from_date . ' 00:00:00');
    $CI->db->where('a.appointment_date <=', $to_date . ' 23:59:59');
    $CI->db->where_in('a.branch_id', $branch_id);
}

function apply_ren_reg_conditions($CI, $from_date, $to_date, $branch_id)
{
    $appointment_type_id = [6, 11, 17, 24, 32];
	$CI->db->join('tblinvoices as inv', 'inv.clientid = a.userid', 'inner');
    $CI->db->join(db_prefix().'itemable item', 'item.rel_id = inv.id AND item.rel_type="invoice"', 'inner');
    $CI->db->join(db_prefix().'invoicepaymentrecords pay', 'pay.invoiceid = inv.id', 'inner');
    $CI->db->where('a.appointment_date >=', $from_date . ' 00:00:00');
    $CI->db->where('a.appointment_date <=', $to_date . ' 23:59:59');
	$CI->db->where('inv.date >=', $from_date);
    $CI->db->where('inv.date <=', $to_date);
    $CI->db->where_in('a.appointment_type_id', $appointment_type_id);
    $CI->db->where_in('a.branch_id', $branch_id);
	$CI->db->where('a.visit_status', 1);
    $CI->db->where('item.description !=', 'Consultation Fee');
}

function apply_ref_visited_conditions($CI, $from_date, $to_date, $branch_id, $ref_user_ids = [])
{
	$CI->db->join(db_prefix().'leads l', 'l.id = c.leadid', 'left');
    $CI->db->where('a.visit_status', 1);
    $CI->db->where('a.appointment_date >=', $from_date . ' 00:00:00');
    $CI->db->where('a.appointment_date <=', $to_date . ' 23:59:59');
    $CI->db->where('l.refer_id >', 0);
    $CI->db->where_in('a.branch_id', $branch_id);

    // Only for referred clients
    if (!empty($ref_user_ids)) {
        $CI->db->where_in('a.userid', $ref_user_ids);
    }
}

/**
 * Apply conditions for referred registrations (ref_reg)
 */
function apply_ref_reg_conditions($CI, $from_date, $to_date, $branch_id, $ref_user_ids = [])
{
	$CI->db->join(db_prefix().'leads l', 'l.id = c.leadid', 'left');
	$CI->db->join('tblinvoices as inv', 'inv.clientid = a.userid', 'inner');
    $CI->db->join(db_prefix().'itemable item', 'item.rel_id = inv.id AND item.rel_type="invoice"', 'inner');
    $CI->db->join(db_prefix().'invoicepaymentrecords pay', 'pay.invoiceid = inv.id', 'inner');

    $CI->db->where('a.appointment_date >=', $from_date . ' 00:00:00');
    $CI->db->where('a.appointment_date <=', $to_date . ' 23:59:59');
    $CI->db->where('inv.date >=', $from_date);
    $CI->db->where('inv.date <=', $to_date);
    $CI->db->where_in('a.branch_id', $branch_id);
    $CI->db->where('a.visit_status', 1);
	$CI->db->where('l.refer_id >', 0);
    $CI->db->where('item.description !=', 'Consultation Fee');

    // Only for referred clients
    if (!empty($ref_user_ids)) {
        $CI->db->where_in('a.userid', $ref_user_ids);
    }
}

/**
 * ======================================================
 * Base SELECT
 * ======================================================
 */
$CI->db->select([
    'c.company as patient_name',
    'c.userid',
    'cn.mr_no',
    'c.phonenumber',
    'source.name as source_name',
    'treatment.description as treatment_name',
    'c.datecreated as created_date',
    'a.appointment_date',
    'a.visited_date',
    'a.consulted_date',
    'cn.registration_start_date as registered_date',
    'cn.registration_end_date as registered_end_date',
]);
$CI->db->from(db_prefix().'appointment a');
$CI->db->join(db_prefix().'clients c', 'a.userid = c.userid', 'left');
$CI->db->join(db_prefix().'clients_new_fields cn', 'cn.userid = c.userid', 'left');
$CI->db->join(db_prefix().'items treatment', 'a.treatment_id = treatment.id', 'left');
$CI->db->join(db_prefix().'leads_sources source', 'cn.patient_source_id = source.id', 'left');

/**
 * ======================================================
 * Apply conditions based on report type
 * ======================================================
 */
switch ($report_type) {
    case 'np_visit':
        apply_np_visit_conditions($CI, $from_date, $to_date, $branch_id);
        break;
    case 'np_reg':
        apply_np_reg_conditions($CI, $from_date, $to_date, $branch_id);
        break;
    case 'ren_visited':
        apply_ren_visit_conditions($CI, $from_date, $to_date, $branch_id);
        break;
    case 'ren_registered':
        apply_ren_reg_conditions($CI, $from_date, $to_date, $branch_id);
        break;
    case 'ref_visited':
        apply_ref_visited_conditions($CI, $from_date, $to_date, $branch_id);
        break;
    case 'ref_reg':
        apply_ref_reg_conditions($CI, $from_date, $to_date, $branch_id);
        break;
    default:
        $CI->db->where_in('a.branch_id', $branch_id);
        break;
}

/**
 * ======================================================
 * Search filter
 * ======================================================
 */
if (!empty($search_value)) {
    $CI->db->group_start();
    $CI->db->like('c.company', $search_value);
    $CI->db->or_like('cn.mr_no', $search_value);
    $CI->db->group_end();
}

// Group and order
$CI->db->group_by('a.appointment_id');
$CI->db->order_by('a.appointment_date', 'DESC');

// Clone query for count
$totalQuery = clone $CI->db;
$total_records = $totalQuery->count_all_results('', false);

// Apply pagination
$CI->db->limit($length, $start);

/* echo $CI->db->get_compiled_select();
exit; */

// Run query
$results = $CI->db->get()->result_array();

/**
 * ======================================================
 * Data formatting for DataTables
 * ======================================================
 */
$data = [];
foreach ($results as $row) {
	$packageDetailsList = $CI->client_model->get_patient_package_details($row['userid']);
    $packageCount = count($packageDetailsList);
	//print_r($packageDetailsList);
    $total = $paid = $due = 0;
    foreach ($packageDetailsList as $p) {
        $total += $p['total'];
        $paid  += $p['paid'];
        $due   += $p['due'];
    }
	$branch_id = $branch_id[0];
	$url = admin_url('client/reports/gt_summary_details/' . $row['userid'] .'/'.$from_date.'/'.$to_date.'/NULL/'.$branch_id.'/NULL/'.$report_type.'/NULL');
    $company = '<a target="_blank" href="' . $url . '" class="tw-font-medium">' . format_name($row['patient_name']) . '</a>';
	
    $data[] = [
        $company,
        $row['mr_no'],
        $row['phonenumber'],
        $row['source_name'],
        $row['treatment_name'],
        _d($row['created_date']),
        _d($row['appointment_date']),
        _d($row['visited_date']),
        _d($row['consulted_date']),
        _d($row['registered_date']),
        _d($row['registered_end_date']),
        e(app_format_money_custom($total, 1)),
        e(app_format_money_custom($paid, 1)),
        e(app_format_money_custom($due, 1)),
    ];
}

// Final output
$output = [
    'draw' => $draw,
    'recordsTotal' => $total_records,
    'recordsFiltered' => $total_records,
    'data' => $data
];

echo json_encode($output);
exit;
