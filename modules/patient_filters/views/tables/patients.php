<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

// DataTables POST variables
$draw    = $CI->input->post('draw');
$start   = $CI->input->post('start');
$length  = intval($CI->input->post('length')) > 0 ? intval($CI->input->post('length')) : 10;
$search  = $CI->input->post('search')['value'] ?? '';
$order   = $CI->input->post('order');
$columns = $CI->input->post('columns');

// Ordering
$order_column_index = $order[0]['column'] ?? 0;
$order_column_name  = $columns[$order_column_index]['data'] ?? 'c.userid';
$order_dir          = $order[0]['dir'] ?? 'desc';

// Base query
$CI->db->select('
    c.*,
    new.*,
    cg.groupid,
    latest_call.created_date as last_calling_date,
    latest_call.next_calling_date,
    treatment.treatment_name,
    staff.firstname,
    staff.lastname,
    latest_treatment.*, 
    latest_casesheet.*,
    new.patient_status as p_status
');
$CI->db->from(db_prefix() . 'clients c');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
$CI->db->join(db_prefix() . 'customer_groups cg', 'cg.customer_id = c.userid', 'left');

// Subquery for latest appointment
$CI->db->join("(SELECT a1.*
    FROM " . db_prefix() . "appointment a1
    INNER JOIN (
        SELECT userid, MAX(appointment_id) AS max_id
        FROM " . db_prefix() . "appointment
        GROUP BY userid
    ) latest ON a1.appointment_id = latest.max_id
) as latest_appointment", 'latest_appointment.userid = c.userid', 'left');

// Related joins
$CI->db->join(db_prefix() . 'treatment treatment', 'treatment.treatment_id = latest_appointment.treatment_id', 'left');
$CI->db->join(db_prefix() . 'staff staff', 'staff.staffid = latest_appointment.enquiry_doctor_id', 'left');

// Subquery for latest call log
$CI->db->join("(SELECT c1.*
    FROM " . db_prefix() . "patient_call_logs c1
    INNER JOIN (
        SELECT patientid, MAX(id) AS max_id
        FROM " . db_prefix() . "patient_call_logs
        GROUP BY patientid
    ) latest_call ON c1.id = latest_call.max_id
) as latest_call", 'latest_call.patientid = c.userid', 'left');

// Subquery for latest treatment
$CI->db->join("(SELECT t1.*
    FROM " . db_prefix() . "patient_treatment t1
    INNER JOIN (
        SELECT userid, MAX(id) AS max_id
        FROM " . db_prefix() . "patient_treatment
        GROUP BY userid
    ) t2 ON t1.id = t2.max_id
) as latest_treatment", 'latest_treatment.userid = c.userid', 'left');

// Subquery for latest casesheet
$CI->db->join("(SELECT cs1.*
    FROM " . db_prefix() . "casesheet cs1
    INNER JOIN (
        SELECT userid, MAX(id) AS max_id
        FROM " . db_prefix() . "casesheet
        GROUP BY userid
    ) cs2 ON cs1.id = cs2.max_id
) as latest_casesheet", 'latest_casesheet.userid = c.userid', 'left');


// Search filter
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('c.company', $search);
    $CI->db->or_like('c.phonenumber', $search);
    $CI->db->or_like('new.mr_no', $search);
    $CI->db->or_like('new.email_id', $search);
    $CI->db->group_end();
}

// WHERE filters
$where = [];

// Filter by treatments
if (!isset($treatments) || empty($treatments)) {
    $treatments = [''];
}
if ($treatments && !in_array('', $treatments) && count($treatments) > 0) {
    $where_treatment = 'treatment.treatment_id IN ("' . implode('","', $treatments) . '")';
    if (in_array(-1, $treatments)) {
        $where_treatment .= ' OR treatment.treatment_id IS NULL';
    }
    $where[] = '(' . $where_treatment . ')';
}

// Filter by date ranges
$report_months = $CI->input->post('report_months');
$report_from = null;
$report_to = null;

switch ($report_months) {
    case 'today':
        $report_from = date('Y-m-d');
        $report_to = date('Y-m-d');
        break;
    case 'this_week':
        $report_from = date('Y-m-d', strtotime('monday this week'));
        $report_to = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'last_week':
        $report_from = date('Y-m-d', strtotime('monday last week'));
        $report_to = date('Y-m-d', strtotime('sunday last week'));
        break;
    case 'this_month':
        $report_from = date('Y-m-01');
        $report_to = date('Y-m-t');
        break;
    case '1':
        $report_from = date('Y-m-01', strtotime('-1 month'));
        $report_to = date('Y-m-t', strtotime('-1 month'));
        break;
    case 'this_year':
        $report_from = date('Y-01-01');
        $report_to = date('Y-12-31');
        break;
    case 'last_year':
        $report_from = date('Y-01-01', strtotime('-1 year'));
        $report_to = date('Y-12-31', strtotime('-1 year'));
        break;
    case '3':
        $report_from = date('Y-m-01', strtotime('-2 months'));
        $report_to = date('Y-m-t');
        break;
    case '6':
        $report_from = date('Y-m-01', strtotime('-5 months'));
        $report_to = date('Y-m-t');
        break;
    case '12':
        $report_from = date('Y-m-01', strtotime('-11 months'));
        $report_to = date('Y-m-t');
        break;
    default:
        break;
}

if (!empty($report_from) && !empty($report_to)) {
    $where[] = "(c.datecreated BETWEEN '$report_from' AND '$report_to')";
}


$followup_months = $CI->input->post('followup_months');
$followup_from = null;
$followup_to = null;

switch ($followup_months) {
    case 'today':
        $followup_from = date('Y-m-d');
        $followup_to = date('Y-m-d');
        break;
    case 'this_week':
        $followup_from = date('Y-m-d', strtotime('monday this week'));
        $followup_to = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'last_week':
        $followup_from = date('Y-m-d', strtotime('monday last week'));
        $followup_to = date('Y-m-d', strtotime('sunday last week'));
        break;
    case 'this_month':
        $followup_from = date('Y-m-01');
        $followup_to = date('Y-m-t');
        break;
    case '1':
        $followup_from = date('Y-m-01', strtotime('-1 month'));
        $followup_to = date('Y-m-t', strtotime('-1 month'));
        break;
    case 'this_year':
        $followup_from = date('Y-01-01');
        $followup_to = date('Y-12-31');
        break;
    case 'last_year':
        $followup_from = date('Y-01-01', strtotime('-1 year'));
        $followup_to = date('Y-12-31', strtotime('-1 year'));
        break;
    case '3':
        $followup_from = date('Y-m-01', strtotime('-2 months'));
        $followup_to = date('Y-m-t');
        break;
    case '6':
        $followup_from = date('Y-m-01', strtotime('-5 months'));
        $followup_to = date('Y-m-t');
        break;
    case '12':
        $followup_from = date('Y-m-01', strtotime('-11 months'));
        $followup_to = date('Y-m-t');
        break;
    default:
        break;
}

// ✅ Apply follow-up date filter if selected
if (!empty($followup_from) && !empty($followup_to)) {
    $where[] = "(latest_treatment.treatment_followup_date BETWEEN '$followup_from' AND '$followup_to')";
}



// Apply WHERE conditions
if (!empty($where)) {
    foreach ($where as $condition) {
        $CI->db->where($condition);
    }
}

// Clone query for count before limit
$builder = clone $CI->db;
$total_records = $builder->count_all_results();

// Apply ordering and limit
$CI->db->order_by($order_column_name, $order_dir);
$CI->db->limit($length, $start);
// Get paginated results
$results = $CI->db->get()->result_array();



// Prepare data
$data = [];
$serial = $start + 1;

foreach ($results as $row) {
    $dataRow = [];

    $dataRow[] = $serial++;
    $dataRow[] = e($row['mr_no']);
    $dataRow[] = e($row['company']);
    $dataRow[] = e($row['phonenumber']);
    $dataRow[] = e($row['age']);
    $dataRow[] = ucfirst(e($row['gender']));
    $dataRow[] = e($row['email_id']);
    $dataRow[] = e($row['city']);
    $dataRow[] = e($row['treatment_name']);
    $dataRow[] = e($row['firstname'] . ' ' . $row['lastname']);
    $dataRow[] = _d($row['last_calling_date']);
    $dataRow[] = _d($row['next_calling_date']);
    $dataRow[] = format_registration_status_custom($row['current_status']);
    $dataRow[] = _d($row['registration_start_date']);
    $dataRow[] = _d($row['registration_end_date']);
    $dataRow[] = e($row['p_status']);
    $dataRow[] = e($row['duration_value']);
    $dataRow[] = e($row['medicine_days']);
    $dataRow[] = _d($row['followup_date']);
    $dataRow[] = e($row['bp']);
    $dataRow[] = e($row['pulse']);
    $dataRow[] = e($row['weight']);
    $dataRow[] = e($row['height']);
    $dataRow[] = e($row['temperature']);
    $dataRow[] = e($row['bmi']);

    $data[] = $dataRow;
}

// Output
echo json_encode([
    'draw' => intval($draw),
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records,
    'aaData' => $data,
]);

function format_registration_status_custom($status)
{
    if (!empty($status) && strtolower($status) === 'registered') {
        return '<span class="label label-success">Registered</span>';
    } else {
        return '<span class="label label-danger">Not Registered</span>';
    }
}

exit;
