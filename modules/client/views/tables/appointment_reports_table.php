<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('client_model');

$consulted_date = $_POST['consulted_date'] ?? null;

if (!empty($consulted_date)) {
    $sql_date = to_sql_date($consulted_date);
    $CI->db->group_start();
    $CI->db->like('appointments.consulted_date', $sql_date, 'after');
    $CI->db->or_like('appointments.appointment_date', $sql_date, 'after');
    $CI->db->group_end();
}

$CI->db->select([
    'patients.company',
    'new.mr_no',
    'treatment.treatment_name',
    'leads_sources.name as lead_source',
    'enquiry_type.enquiry_type_name',
    'appointments.visit_status',
    'appointments.consulted_date',
    'staff.firstname',
    'staff.lastname',
    'appointment_type.appointment_type_name',
    'appointments.appointment_id',
    'consultation_fee.consultation_fee_name',
    'appointments.visit_id',
    'appointments.userid',
    'appointments.appointment_date',
    'patients.phonenumber as patient_mobile',
]);

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

$query = $CI->db->get();
$results = $query->result_array();

$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

$output = [
    'draw' => $draw,
    'recordsTotal' => count($results),         // ideally total from DB without filters
    'recordsFiltered' => count($results),      // total after applying filters
    'data' => []                               // DataTables expects 'data', not 'aaData'
];

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
    $url = admin_url('client/index/' . $aRow['userid']);

    $nameLink = '<a href="' . $url . '">' . e(format_name($aRow['company'])) . '</a>';
    if ($packageCount > 0) {
        $nameLink .= '<br><a href="javascript:void(0);" onclick="$(\'#' . $toggleId . '\').toggle();" class="label label-info">
            Packages: ' . $packageCount . '</a>';

        $nameLink .= '<div id="' . $toggleId . '" style="display:none;margin-top:10px;">
            <table class="table table-bordered small" style="background:#f9f9f9;">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Due</th>
                </tr>
            </thead><tbody>';

        foreach ($packageDetailsList as $p) {
            $nameLink .= '<tr>
                <td>' . $p['invoice_id'] . '</td>
                <td>' . e($p['description']) . '</td>
                <td>' . $p['qty'] . '</td>
                <td>' . app_format_money_custom($p['rate'], 1) . '</td>
                <td>' . app_format_money_custom($p['total'], 1) . '</td>
                <td>' . app_format_money_custom($p['paid'], 1) . '</td>
                <td>' . app_format_money_custom($p['due'], 1) . '</td>
            </tr>';
        }

        $nameLink .= '</tbody></table></div>';
    }

    $row = [];
    $row[] = $nameLink;
    $row[] = $aRow['mr_no'];
    $row[] = $aRow['treatment_name'];
    $row[] = $aRow['lead_source'] ?? $aRow['enquiry_type_name'];
    $row[] = $aRow['visit_status'] == 0 ? "Not Visited" : "Visited";
    $row[] = _d($aRow['consulted_date']);
    $row[] = $aRow['firstname'] . ' ' . $aRow['lastname'];
    $row[] = _d($aRow['appointment_date']);
    $row[] = $aRow['appointment_type_name'];
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
