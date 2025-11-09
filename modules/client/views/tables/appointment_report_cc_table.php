<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('client_model'); // adjust if different

// Input from DataTables POST
$draw = $CI->input->post('draw');
$length = $CI->input->post('length');
$start = $CI->input->post('start');
$search = $CI->input->post('search')['value'] ?? '';
$order = $CI->input->post('order');
$columns = $CI->input->post('columns');

// Base query
$CI->db->select("
    c.userid,
    c.phonenumber,
    c.company as patient_name,
    new.mr_no,
    treatment.description as treatment_name,
    enquiry.enquiry_type_name,
    a.consulted_date as visited_date,
    CASE WHEN a.visit_status = 1 THEN 'Visited' ELSE 'Missed' END as visited_status,
    a.created_at as enquiry_date,
    a.updated_at,
    a.remarks,
    a.appointment_date as appointment_datetime,
    appointment_type.appointment_type_name,
    staff_created.firstname as created_firstname,
    staff_created.lastname as created_lastname,
    staff_updated.firstname as updated_firstname,
    staff_updated.lastname as updated_lastname,
    staff.firstname as enquiry_firstname,
    staff.lastname as enquiry_lastname,
	branch.name as branch_name,
	source.name as source_name,
");
$CI->db->from(db_prefix() . 'appointment a');
$CI->db->join(db_prefix() . 'clients c', 'c.userid = a.userid', 'left');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
$CI->db->join(db_prefix() . 'customers_groups branch', 'branch.id = a.branch_id', 'left');
$CI->db->join(db_prefix() . 'staff staff', 'staff.staffid = a.enquiry_doctor_id', 'left');
$CI->db->join(db_prefix() . 'staff staff_created', 'staff_created.staffid = a.created_by', 'left');
$CI->db->join(db_prefix() . 'staff staff_updated', 'staff_updated.staffid = a.updated_by', 'left');
$CI->db->join(db_prefix() . 'items treatment', 'treatment.id = a.treatment_id', 'left');
$CI->db->join(db_prefix() . 'leads_sources source', 'source.id = new.patient_source_id', 'left');
$CI->db->join(db_prefix() . 'enquiry_type enquiry', 'enquiry.enquiry_type_id = a.enquiry_type_id', 'left');
$CI->db->join(db_prefix() . 'appointment_type appointment_type', 'appointment_type.appointment_type_id = a.appointment_type_id', 'left');

if ($consulted_from_date && $consulted_to_date) {
    $from_date = to_sql_date($consulted_from_date);
    $to_date = to_sql_date($consulted_to_date);

    // Apply date range filter on either consulted_date or appointment_date
    $CI->db->group_start();
    $CI->db->where("DATE(a.consulted_date) >=", $from_date);
    $CI->db->where("DATE(a.consulted_date) <=", $to_date);
    $CI->db->or_group_start();
    $CI->db->where("DATE(a.appointment_date) >=", $from_date);
    $CI->db->where("DATE(a.appointment_date) <=", $to_date);
    $CI->db->group_end();
    $CI->db->group_end();
} elseif ($consulted_from_date) {
    $sql_date = to_sql_date($consulted_from_date);

    $CI->db->group_start();
    $CI->db->where("DATE(a.consulted_date)", $sql_date);
    $CI->db->or_where("DATE(a.appointment_date)", $sql_date);
    $CI->db->group_end();
}

if (!empty($selected_branch_id) && is_numeric($selected_branch_id)) {
	
    $CI->db->where('a.branch_id', intval($selected_branch_id));
}

// Search filter (apply only if search value is present)
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('c.company', $search);
    $CI->db->or_like('new.mr_no', $search);
    $CI->db->or_like('treatment.treatment_name', $search);
    $CI->db->or_like('enquiry.enquiry_type_name', $search);
    $CI->db->or_like('staff.firstname', $search);
    $CI->db->or_like('staff.lastname', $search);
    $CI->db->or_like('staff_created.firstname', $search);
    $CI->db->or_like('staff_created.lastname', $search);
    $CI->db->group_end();
}

// Get total filtered count (before limit)
$filteredQuery = clone $CI->db;
$filtered_count = $filteredQuery->count_all_results('', false); // false = do not reset query

// Ordering
/* if ($order && is_array($order)) {
    $orderColumnIndex = $order[0]['column'];
    $orderDir = $order[0]['dir'];

    $orderColumn = $columns[$orderColumnIndex]['data'] ?? null;

    // Map column data keys to actual DB columns
    $orderableColumnsMap = [
        'patient_name' => 'c.company',
        'mr_no' => 'new.mr_no',
        'treatment_name' => 'treatment.treatment_name',
        'enquiry_type_name' => 'enquiry.enquiry_type_name',
        'visited_date' => 'a.consulted_date',
        'visited_status' => 'a.visit_status',
        'enquiry_doctor' => 'staff.firstname', // combined firstname + lastname not sortable, pick firstname
        'enquiry_date' => 'a.created_at',
        'appointment_datetime' => 'a.appointment_date',
        'appointment_type_name' => 'appointment_type.appointment_type_name',
        'created_by' => 'staff_created.firstname', // same as above
    ];

    if (isset($orderableColumnsMap[$orderColumn])) {
        $CI->db->order_by($orderableColumnsMap[$orderColumn], $orderDir);
    }
} else {
    // Default order
    $CI->db->order_by('a.appointment_datetime', 'desc');
} */

$CI->db->order_by('a.appointment_date', 'desc');

// Pagination: apply limit and offset
if (isset($length) && $length != -1) {
    $CI->db->limit(intval($length), intval($start));
}

/* echo $CI->db->get_compiled_select();
exit; */ 
// Execute the query
$query = $CI->db->get();

$data = $query->result_array();

// Get total record count without filters (for recordsTotal)
$CI->db->from(db_prefix() . 'appointment a');
$total_count = $CI->db->count_all_results();

// Prepare output
$output = [
    'draw' => intval($draw),
    'recordsTotal' => $total_count,
    'recordsFiltered' => $filtered_count,
    'data' => []
];

// Format rows as per your datatable columns
foreach ($data as $row) {
	$packageDetailsList = $CI->client_model->get_patient_package_details($row['userid']);
    $packageCount = count($packageDetailsList);

    $total = $paid = $due = 0;
    foreach ($packageDetailsList as $p) {
        $total += $p['total'];
        $paid  += $p['paid'];
        $due   += $p['due'];
    }
    $dataRow = [];

    // Build link for patient name, assuming $type is defined in your controller
    $type = $CI->input->post('type') ?? 'default'; // fallback if needed
    $url = admin_url('client/calling/' . $type . '/' . $row['userid']);
    $company = '<a href="' . $url . '" class="tw-font-medium">' . $row['patient_name'] . '</a>';

    $dataRow[] = $company;
    $dataRow[] = (staff_can('mobile_masking', 'customers') && !is_admin()) 
        ? mask_last_5_digits_1($row['phonenumber']) 
        : $row['phonenumber'];
    $dataRow[] = $row['mr_no'];
    $dataRow[] = $row['branch_name'];
    $dataRow[] = $row['treatment_name'];
    $dataRow[] = $row['source_name'];
    
    $dataRow[] = _d($row['visited_date']);
    $dataRow[] = $row['visited_status'];
    $dataRow[] = trim($row['enquiry_firstname'] . ' ' . $row['enquiry_lastname']);
    $dataRow[] = _dt($row['enquiry_date']);
    $dataRow[] = _dt($row['appointment_datetime']);
	$dataRow[] = _d($row['updated_at']);
    $dataRow[] = trim($row['updated_firstname'] . ' ' . $row['updated_lastname']);
    $dataRow[] = $row['appointment_type_name'];
    $dataRow[] = trim($row['created_firstname'] . ' ' . $row['created_lastname']);
	$dataRow[] = $row['remarks'];
    $dataRow[] = e(app_format_money_custom($total, 1));
    $dataRow[] = e(app_format_money_custom($paid, 1));
    $dataRow[] = e(app_format_money_custom($due, 1));
	
    $output['data'][] = $dataRow;
}

// Output JSON response for DataTables
echo json_encode($output);
exit;
