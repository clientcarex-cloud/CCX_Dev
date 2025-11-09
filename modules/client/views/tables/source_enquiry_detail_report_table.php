<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('client_model');

$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$order  = $_POST['order'][0] ?? null;
$columns = $_POST['columns'] ?? [];

$source_id = $CI->input->get('source_id') ?? $CI->input->post('source_id') ?? null;
$type = $CI->input->get('type') ?? $CI->input->post('type') ?? null;
$from_date = $CI->input->get('from_date') ?? $CI->input->post('from_date') ?? null;
$to_date   = $CI->input->get('to_date') ?? $CI->input->post('to_date') ?? null;

$CI->db->select([
    'patients.userid',
    'source.name as source_name',
    'patients.company',
    'new.mr_no',
    'appointments.enquiry_doctor_id',
    'treatment.description as treatment_name',
    'appointments.created_by',
    'appointments.created_at',
    'appointments.appointment_date',
    'appointments.appointment_date as visited_date',
    'appointments.consulted_date',
    'new.registration_start_date',
]);

$CI->db->from(db_prefix() . 'appointment appointments');
$CI->db->join(db_prefix() . 'appointment_type appointment_type', 'appointment_type.appointment_type_id = appointments.appointment_type_id', 'left');
$CI->db->join(db_prefix() . 'items consultation_fee', 'consultation_fee.id = appointments.consultation_fee_id', 'left');
$CI->db->join(db_prefix() . 'clients patients', 'patients.userid = appointments.userid', 'left');
$CI->db->join(db_prefix() . 'enquiry_type enquiry_type', 'enquiry_type.enquiry_type_id = patients.enquiry_type_id', 'left');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = patients.userid', 'left');
$CI->db->join(db_prefix() . 'leads_sources source', 'source.id = new.patient_source_id', 'left');
$CI->db->join(db_prefix() . 'items treatment', 'treatment.id = appointments.treatment_id', 'left');
$CI->db->join(db_prefix() . 'leads leads', 'leads.id = patients.leadid', 'left');

if (!empty($source_id)) {
    $CI->db->where('new.patient_source_id', $source_id);
}

if (!empty($type)) {
    switch ($type) {
        case 'enquiries':
            $CI->db->where('appointments.id IS NULL'); // returns empty, handled separately
            break;
        case 'appointments':
            // no filter
            break;
        case 'visits':
            $CI->db->where('appointments.visit_status', 1);
            $CI->db->where('appointments.consulted_date IS NULL');
            break;
        case 'consultations':
            $CI->db->where('appointments.visit_status', 1);
            $CI->db->where('appointments.consulted_date IS NOT NULL');
            break;
        case 'registrations':
            $CI->db->where('new.mr_no IS NOT NULL');
            $CI->db->where('new.registration_start_date IS NOT NULL');
            break;
    }
}

if (!empty($from_date) && !empty($to_date)) {
    $from = to_sql_date($from_date);
    $to   = to_sql_date($to_date);
    $CI->db->group_start();
    $CI->db->where('appointments.appointment_date >=', $from);
    $CI->db->where('appointments.appointment_date <=', $to);
    $CI->db->group_end();
} elseif (!empty($from_date)) {
    $CI->db->where('appointments.appointment_date >=', to_sql_date($from_date));
} elseif (!empty($to_date)) {
    $CI->db->where('appointments.appointment_date <=', to_sql_date($to_date));
}

$search_value = $_POST['search']['value'] ?? '';

if (!empty($search_value)) {
    $CI->db->group_start();
    $CI->db->like('patients.company', $search_value);
    $CI->db->or_like('new.mr_no', $search_value);
    $CI->db->or_like('appointments.visit_id', $search_value);
    $CI->db->or_like('appointments.userid', $search_value);
    $CI->db->or_like('appointments.appointment_id', $search_value);
    $CI->db->or_like('treatment.description', $search_value);
    $CI->db->or_like('source.name', $search_value);
    $CI->db->group_end();
}

$CI->db->order_by('appointments.appointment_date', 'DESC');
$tempdb = clone $CI->db;
$total_filtered = $tempdb->count_all_results();
if ($length != -1) {
    $CI->db->limit($length, $start);
}

$query = $CI->db->get();
$results = $query->result_array();

$CI->db->from(db_prefix() . 'appointment');
if (!empty($source_id)) {
    $CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = appointment.userid', 'left');
    $CI->db->where('new.patient_source_id', $source_id);
}
$total_records = $CI->db->count_all_results();

$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

$output = [
    'draw' => $draw,
    'recordsTotal' => $total_records,
    'recordsFiltered' => $total_filtered,
    'aaData' => [],
];

foreach ($results as $aRow) {
    $packageDetailsList = $CI->client_model->get_patient_package_details($aRow['userid']);

    $total = $paid = $due = 0;
    foreach ($packageDetailsList as $p) {
        $total += $p['total'];
        $paid  += $p['paid'];
        $due   += $p['due'];
    }

    $url = admin_url('client/reports/source_enquiry_detail_report/' . $aRow['userid']);
    $nameLink = '<a href="' . $url . '" target="_blank">' . e(format_name($aRow['company'])) . '</a>';

    $row = [];
    $row[] = $nameLink;
    $row[] = $aRow['mr_no'];
    $row[] = $aRow['source_name'];
    $row[] = get_staff_full_name($aRow['enquiry_doctor_id']);
    $row[] = $aRow['treatment_name'];
    $row[] = get_staff_full_name($aRow['created_by']);
    $row[] = _d($aRow['created_at']);
    $row[] = _d($aRow['appointment_date']);
    $row[] = _d($aRow['visited_date']);
    $row[] = _d($aRow['consulted_date']);
    $row[] = _d($aRow['registration_start_date']);
    $row[] = e(app_format_money_custom($total, 1));
    $row[] = e(app_format_money_custom($paid, 1));
    $row[] = e(app_format_money_custom($due, 1));

    $output['aaData'][] = $row;
}

echo json_encode($output);
exit;