<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('client_model');

// ========================
// 1. Read Filters from URL
// ========================
$draw = $CI->input->post('draw');
$length = $CI->input->post('length');
$start = $CI->input->post('start');
$search = $CI->input->post('search')['value'] ?? '';



$branch_ids = [];
if (!empty($branch_segment)) {
    $branch_ids = explode(',', $branch_segment);
    $branch_ids = array_filter(array_map('intval', $branch_ids));
}

$from_sql = $consulted_from_date ? to_sql_date($consulted_from_date) : null;
$to_sql = $consulted_to_date ? to_sql_date($consulted_to_date) : null;

// ============================
// 2. Get all Doctors by Role
// ============================
$CI->db->select('roleid')->from(db_prefix() . 'roles')
    ->group_start()
    ->where('LOWER(name)', 'doctor')
    ->or_where('LOWER(name)', 'service doctor')
    ->group_end();
$role_query = $CI->db->get();
$role_id = $role_query->num_rows() > 0 ? $role_query->row()->roleid : 0;

$CI->db->select([
    'doctor.staffid',
    'doctor.firstname',
    'doctor.lastname',
    '(SELECT COUNT(*) FROM tblappointment AS a WHERE a.enquiry_doctor_id = doctor.staffid' .
        ($from_sql && $to_sql ? " AND DATE(a.consulted_date) BETWEEN '$from_sql' AND '$to_sql'" : '') .
        (!empty($branch_ids) ? " AND a.branch_id IN (" . implode(',', $branch_ids) . ")" : '') .
    ') AS appointment_count',

    '(SELECT COUNT(*) FROM tblappointment AS a WHERE a.enquiry_doctor_id = doctor.staffid AND a.visit_status=1' .
        ($from_sql && $to_sql ? " AND DATE(a.consulted_date) BETWEEN '$from_sql' AND '$to_sql'" : '') .
        (!empty($branch_ids) ? " AND a.branch_id IN (" . implode(',', $branch_ids) . ")" : '') .
    ') AS visits_count',

    '(SELECT COUNT(*) FROM tblappointment AS a WHERE a.enquiry_doctor_id = doctor.staffid AND a.visit_status=0' .
        ($from_sql && $to_sql ? " AND DATE(a.consulted_date) BETWEEN '$from_sql' AND '$to_sql'" : '') .
        (!empty($branch_ids) ? " AND a.branch_id IN (" . implode(',', $branch_ids) . ")" : '') .
    ') AS missed_consultation'
]);

$CI->db->from(db_prefix() . 'staff AS doctor');
$CI->db->where('doctor.role', $role_id);
$CI->db->order_by('appointment_count', 'DESC');

if (!empty($length) && $length != -1) {
    $CI->db->limit($length, $start);
}

$query = $CI->db->get();
$results = $query->result_array();

// ========================
// 3. Map Doctor-Patients
// ========================
$doctor_patients = [];

$CI->db->distinct();
$CI->db->select('userid, enquiry_doctor_id')->from('tblappointment');
if ($from_sql && $to_sql) {
    $CI->db->where("DATE(consulted_date) >=", $from_sql);
    $CI->db->where("DATE(consulted_date) <=", $to_sql);
}
if (!empty($branch_ids)) {
    $CI->db->where_in("branch_id", $branch_ids);
}
$CI->db->where("enquiry_doctor_id IS NOT NULL", null, false);
$doctor_map = $CI->db->get()->result_array();

foreach ($doctor_map as $row) {
    $doctor_id = $row['enquiry_doctor_id'];
    $user_id = $row['userid'];
    if (!isset($doctor_patients[$doctor_id])) $doctor_patients[$doctor_id] = [];
    if (!in_array($user_id, $doctor_patients[$doctor_id])) {
        $doctor_patients[$doctor_id][] = $user_id;
    }
}

// ========================
// 4. Compile Final Output
// ========================
$output = [
    'draw' => intval($draw),
    'recordsTotal' => count($results),
    'recordsFiltered' => count($results),
    'data' => []
];

foreach ($results as $aRow) {
    $doctor_id = $aRow['staffid'];
    $total = 0; $paid = 0; $due = 0; $registered = 0;
    $seen_invoice_ids = [];

    if (isset($doctor_patients[$doctor_id])) {
        foreach ($doctor_patients[$doctor_id] as $userid) {
            $packageDetailsList = $CI->client_model->get_patient_package_details($userid);
            if ($packageDetailsList) $registered++;
            foreach ($packageDetailsList as $package) {
                $total += $package['total'];
                if (!in_array($package['invoice_id'], $seen_invoice_ids)) {
                    $paid += $package['paid'];
                    $due  += $package['due'];
                    $seen_invoice_ids[] = $package['invoice_id'];
                }
            }
        }
    }

    $row = [];
    $row[] = $aRow['firstname'] . ' ' . $aRow['lastname'];

    $row[] = '<a href="' . admin_url("client/ownership_details/appointment/{$doctor_id}") . '" style="color:blue;">' . $aRow['appointment_count'] . '</a>';
    $row[] = '<a href="' . admin_url("client/ownership_details/visit/{$doctor_id}") . '" style="color:blue;">' . $aRow['visits_count'] . '</a>';
    $row[] = $registered;
    $row[] = format_money($total, '');
    $row[] = format_money($paid, '');
    $row[] = format_money($due, '');
    $row[] = $aRow['missed_consultation'];
    $row[] = $aRow['visits_count'] - $registered;

    $output['data'][] = $row;
}

echo json_encode($output);
exit;
