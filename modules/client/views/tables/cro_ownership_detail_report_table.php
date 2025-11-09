<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('client_model');


$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$order  = $_POST['order'][0] ?? null;
$columns = $_POST['columns'] ?? [];


// Get filter inputs

$CI->db->select([
    'patients.company',
    'new.mr_no',
    'treatment.description as treatment_name',
    'source.name as lead_source',
    'enquiry_type.enquiry_type_name',
    'appointments.visit_status',
    'appointments.consulted_date',
    'staff.firstname',
    'staff.lastname',
    'appointment_type.appointment_type_name',
    'appointments.appointment_id',
    'consultation_fee.description as consultation_fee_name',
    'appointments.visit_id',
    'appointments.userid',
    'appointments.appointment_date',
    'patients.phonenumber as patient_mobile',
    'new.registration_start_date',
]);

$CI->db->from(db_prefix() . 'appointment appointments');
$CI->db->join(db_prefix() . 'staff staff', 'appointments.enquiry_doctor_id = staff.staffid', 'left');
$CI->db->join(db_prefix() . 'appointment_type appointment_type', 'appointment_type.appointment_type_id = appointments.appointment_type_id', 'left');
$CI->db->join(db_prefix() . 'items consultation_fee', 'consultation_fee.id = appointments.consultation_fee_id', 'left');
$CI->db->join(db_prefix() . 'clients patients', 'patients.userid = appointments.userid', 'left');
$CI->db->join(db_prefix() . 'enquiry_type enquiry_type', 'enquiry_type.enquiry_type_id = patients.enquiry_type_id', 'left');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = patients.userid', 'left');
$CI->db->join(db_prefix() . 'leads_sources source', 'source.id = new.patient_source_id', 'left');
$CI->db->join(db_prefix() . 'items treatment', 'treatment.id = appointments.treatment_id', 'left');
$CI->db->join(db_prefix() . 'leads leads', 'leads.id = patients.leadid', 'left');

// === Filters ===

// Filter by single consulted date
if (!empty($consulted_date)) {
    $sql_date = to_sql_date($consulted_date);
    $CI->db->group_start();
    $CI->db->like('appointments.consulted_date', $sql_date, 'after');
    $CI->db->or_like('appointments.appointment_date', $sql_date, 'after');
    $CI->db->group_end();
}
// Filter by branch
if (!empty($selected_branch_id)) {
    // Handle when it's a single numeric ID
    if (is_numeric($selected_branch_id)) {
        $CI->db->where('appointments.branch_id', intval($selected_branch_id));
    }
    // Handle when it's an array of IDs
    elseif (is_array($selected_branch_id)) {
        // Sanitize the array values to ensure they're all integers
        $branch_ids = array_map('intval', $selected_branch_id);
        $CI->db->where_in('appointments.branch_id', $branch_ids);
    }
    // Handle when it's a comma-separated string
    elseif (is_string($selected_branch_id) && strpos($selected_branch_id, ',') !== false) {
        $branch_ids = array_map('intval', explode(',', $selected_branch_id));
        $CI->db->where_in('appointments.branch_id', $branch_ids);
    }
    // Handle any other unexpected cases
    else {
        // Optionally log an error or handle unexpected input
        //log_activity('Unexpected branch_id format: ' . print_r($selected_branch_id, true));
    }
}

// Filter by doctor
if (!empty($doctor_id)) {
    // Handle when it's a single numeric ID
    if (is_numeric($doctor_id)) {
        $CI->db->where('appointments.enquiry_doctor_id', intval($doctor_id));
    }
    // Handle when it's an array of IDs
    elseif (is_array($doctor_id)) {
        $doctor_ids = array_map('intval', $doctor_id);
        $CI->db->where_in('appointments.enquiry_doctor_id', $doctor_ids);
    }
    // Handle comma-separated string
    elseif (is_string($doctor_id) && strpos($doctor_id, ',') !== false) {
        $doctor_ids = array_map('intval', explode(',', $doctor_id));
        $CI->db->where_in('appointments.enquiry_doctor_id', $doctor_ids);
    }
    // Optionally handle other formats
    else {
        // log_activity('Unexpected doctor_id format: ' . print_r($doctor_id, true));
    }
}


// Filter by consulted date range
if (!empty($consulted_from_date) && !empty($consulted_to_date)) {
    $from_date = to_sql_date($consulted_from_date);
    $to_date   = to_sql_date($consulted_to_date);

    $CI->db->group_start();

    // Convert to individual LIKE conditions per date
    $period = new DatePeriod(
        new DateTime($from_date),
        new DateInterval('P1D'),
        (new DateTime($to_date))->modify('+1 day')
    );

    foreach ($period as $date) {
        $d = $date->format('Y-m-d');
        $CI->db->or_like('appointments.consulted_date', $d, 'after');
        $CI->db->or_like('appointments.appointment_date', $d, 'after');
    }

    $CI->db->group_end();
} elseif (!empty($consulted_from_date)) {
    $sql_date = to_sql_date($consulted_from_date);
    $CI->db->group_start();
    $CI->db->like('appointments.consulted_date', $sql_date, 'after');
    $CI->db->or_like('appointments.appointment_date', $sql_date, 'after');
    $CI->db->group_end();
}


// Global search
$search_value = $_POST['search']['value'] ?? '';

if (!empty($search_value)) {
    $CI->db->group_start(); // Open bracket for search conditions
    $CI->db->like('patients.company', $search_value);
    $CI->db->or_like('new.mr_no', $search_value);
    $CI->db->or_like('appointments.visit_id', $search_value);
    $CI->db->or_like('appointments.userid', $search_value);
    $CI->db->or_like('appointments.appointment_id', $search_value);
    $CI->db->or_like('staff.firstname', $search_value);
    $CI->db->or_like('staff.lastname', $search_value);
    $CI->db->or_like('treatment.description', $search_value);
    $CI->db->or_like('source.name', $search_value);
    $CI->db->or_like('enquiry_type.enquiry_type_name', $search_value);
    $CI->db->or_like('consultation_fee.consultation_fee_name', $search_value);
    $CI->db->group_end(); // Close bracket
}


$CI->db->order_by('appointments.appointment_date', 'DESC');
$tempdb = clone $CI->db;
$total_filtered = $tempdb->count_all_results();
if ($length != -1) {
    $CI->db->limit($length, $start);
}

$query = $CI->db->get();
$results = $query->result_array();


$CI->db->from(db_prefix() . 'appointment'); // base table only
$total_records = $CI->db->count_all_results();

// === DataTables Response Preparation ===

$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

$output = [
    'draw' => $draw,
    'recordsTotal' => $total_records,
    'recordsFiltered' => $total_filtered,
    'aaData' => [],
];


// === Output Formatting ===

foreach ($results as $aRow) {
    $packageDetailsList = $CI->client_model->get_patient_package_details($aRow['userid']);
    $packageCount = count($packageDetailsList);

    $total = $paid = $due = 0;
    foreach ($packageDetailsList as $p) {
        $total += $p['total'];
        $paid  += $p['paid'];
        $due   += $p['due'];
    }

    $toggleId = 'pkg_' . $aRow['userid'] . '_' . $aRow['appointment_id'];
    
	$url = admin_url('client/reports/' . $type . '/' . $aRow['userid']);
    $nameLink = '<a href="' . $url . '" target="blank">' . e(format_name($aRow['company'])) . '</a>';


    $row = [];
    $row[] = $nameLink;
    $row[] = $aRow['mr_no'];
    $row[] = "";
    $row[] = $aRow['lead_source'] ?? $aRow['enquiry_type_name'];
    $row[] = $aRow['treatment_name'];
	$row[] = date("d-m-Y", strtotime($aRow['appointment_date']));
	$row[] = date("d-m-Y", strtotime($aRow['appointment_date']));
	$row[] = date("d-m-Y", strtotime($aRow['consulted_date']));
    $row[] = date("d-m-Y", strtotime($aRow['consulted_date']));
    $row[] = date("d-m-Y", strtotime($aRow['registration_start_date']));
	
    $row[] = e(app_format_money_custom($total, 1));
    $row[] = e(app_format_money_custom($paid, 1));
    $row[] = e(app_format_money_custom($due, 1));

    $output['aaData'][] = $row;
}

echo json_encode($output);
exit;
