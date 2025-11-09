<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('client_model');

// Get filter inputs

// Get DataTables parameters
$draw = intval($CI->input->post('draw'));
$start = intval($CI->input->post('start'));
$length = intval($CI->input->post('length'));

// Apply filters and joins
$CI->db->select("SQL_CALC_FOUND_ROWS
    appointments.appointment_id,
    patients.company,
    new.mr_no,
    new.current_status,
    new.registration_start_date,
    new.registration_end_date,
    treatment.treatment_name,
    leads_sources.name as lead_source,
    enquiry_type.enquiry_type_name,
    appointments.visit_status,
    appointments.consulted_date,
    staff.firstname,
    staff.lastname,
    appointment_type.appointment_type_name,
    appointments.visit_id,
    appointments.userid,
    appointments.appointment_date,
    appointments.consultation_fee_id,
    consultation_fee.consultation_fee_name,
    patients.phonenumber as patient_mobile
", false);


$CI->db->from(db_prefix() . 'appointment appointments');
$CI->db->join(db_prefix() . 'staff staff', 'appointments.enquiry_doctor_id = staff.staffid', 'left');
$CI->db->join(db_prefix() . 'appointment_type appointment_type', 'appointment_type.appointment_type_id = appointments.appointment_type_id', 'left');
$CI->db->join(db_prefix() . 'consultation_fee consultation_fee', 'consultation_fee.consultation_fee_id = appointments.consultation_fee_id', 'left');
$CI->db->join(db_prefix() . 'clients patients', 'patients.userid = appointments.userid', 'left');
$CI->db->join(db_prefix() . 'enquiry_type enquiry_type', 'enquiry_type.enquiry_type_id = patients.enquiry_type_id', 'left');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = patients.userid', 'left');
$CI->db->join(db_prefix() . 'treatment treatment', 'treatment.treatment_id = appointments.treatment_id', 'left');
$CI->db->join(db_prefix() . 'leads leads', 'leads.id = patients.leadid', 'left');
$CI->db->join(db_prefix() . 'leads_sources leads_sources', 'leads_sources.id = leads.source', 'left');

$CI->db->order_by('appointments.appointment_id', 'DESC');

// Apply pagination
if ($length != -1) {
    $CI->db->limit($length, $start);
}

// Execute query
$query = $CI->db->get();
$results = $query->result_array();

// Total records after filtering
$totalFiltered = $CI->db->query("SELECT FOUND_ROWS() as count")->row()->count;

// Total records without filtering
$CI->db->from(db_prefix() . 'appointment');
$totalRecords = $CI->db->count_all_results();

// Prepare output
$output = [
    'draw' => $draw,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $totalFiltered,
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

   
    $row = [];
    //$row[] = $nameLink;
    $row[] = '<a href="javascript:void(0);" onclick="showJourneyLog(' . $aRow['userid'] . ')" class="text-primary">' . format_name($aRow['company']) . '</a>';

    $row[] = $aRow['mr_no'];
    $row[] = $aRow['treatment_name'];
    $row[] = $aRow['current_status'];
    $row[] = _d($aRow['registration_start_date']);
    $row[] = _d($aRow['registration_end_date']);
    $row[] = e(app_format_money_custom($aRow['consultation_fee_name'], 1));
    $row[] = e(app_format_money_custom($total, 1));
    $row[] = e(app_format_money_custom($paid, 1));
    $row[] = e(app_format_money_custom($due, 1));

    $appointmentDate = date('Y-m-d', strtotime($aRow['appointment_date']));
    $today = date('Y-m-d');

    if ($aRow['visit_status'] == 1) {
        $row[] = '<span class="btn btn-success btn-sm text-white">Visited</span> 
            <a href="' . admin_url('estimates/estimate') . '?customer_id=' . $aRow['userid'] . '" class="btn btn-info btn-sm text-white">
                <i class="fa fa-plus"></i> Add Package
            </a>';
    } else {
        if ($appointmentDate < $today) {
            $row[] = '<span class="btn btn-danger btn-sm text-white">Missed</span>';
        } elseif ($appointmentDate > $today) {
            $row[] = '<span class="btn btn-info btn-sm text-white">Upcoming</span>';
        } else {
            $row[] = '<a href="javascript:void(0);" onclick="confirmBooking(' . $aRow['appointment_id'] . ')" class="btn btn-warning btn-sm text-white">
                Confirm Visit
            </a>';
        }
    }

    $output['aaData'][] = $row;
}

echo json_encode($output);
exit;
