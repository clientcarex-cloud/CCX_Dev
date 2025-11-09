<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

$draw    = $CI->input->post('draw');
$start   = $CI->input->post('start');
$length  = $CI->input->post('length');
$search  = $CI->input->post('search')['value'] ?? '';
$order   = $CI->input->post('order');
$columns = $CI->input->post('columns');

$order_column_index = $order[0]['column'] ?? 1;
$order_column_name  = 'c.id';
$order_dir          = 'desc';

$client_id = $client_id ?? 0;

// ======================== COUNT TOTAL RECORDS (DISTINCT) ========================
$CI->db->select('COUNT(DISTINCT c.id) as total_records', false);
$CI->db->from(db_prefix() . 'casesheet c');
$CI->db->join(db_prefix() . 'patient_treatment t', 't.casesheet_id = c.id', 'left');

$CI->db->join(db_prefix() . 'items treatment', 'treatment.id = t.treatment_type_id', 'left');
$CI->db->join(db_prefix() . 'suggested_diagnostics suggested_diagnostics', 'suggested_diagnostics.suggested_diagnostics_id = t.suggested_diagnostics_id', 'left');
$CI->db->join(
    '(SELECT a1.* FROM ' . db_prefix() . 'appointment a1
     INNER JOIN (
        SELECT userid, MAX(appointment_date) as max_date
        FROM ' . db_prefix() . 'appointment
        GROUP BY userid
     ) a2 ON a1.userid = a2.userid AND a1.appointment_date = a2.max_date
    ) latest_appointment',
    'latest_appointment.userid = c.userid',
    'left'
);
$CI->db->join(
    db_prefix() . 'appointment_type at',
    'at.appointment_type_id = latest_appointment.appointment_type_id',
    'left'
);
$CI->db->where('c.userid', $client_id);

if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('c.clinical_observation', $search);
    $CI->db->or_like('treatment.description', $search);
    $CI->db->or_like('t.improvement', $search);
    $CI->db->group_end();
}

$total_records_row = $CI->db->get()->row();
$total_records = $total_records_row->total_records ?? 0;

// ======================== FETCH ACTUAL PAGINATED DATA ========================
$CI->db->select("
    c.id,
    c.date,
    c.staffid,
    c.clinical_observation,
    c.medicine_days,
    c.doctor_medicine_days,
    t.improvement,
    t.duration_value,
    t.treatment_status,
    c.created_at,
    treatment.description as treatment_name,
    at.appointment_type_name,
	suggested_diagnostics.suggested_diagnostics_name
");
$CI->db->from(db_prefix() . 'casesheet c');
$CI->db->join(db_prefix() . 'patient_treatment t', 't.casesheet_id = c.id', 'left');

$CI->db->join(db_prefix() . 'items treatment', 'treatment.id = t.treatment_type_id', 'left');
$CI->db->join(db_prefix() . 'suggested_diagnostics suggested_diagnostics', 'suggested_diagnostics.suggested_diagnostics_id = t.suggested_diagnostics_id', 'left');
$CI->db->join(
    '(SELECT a1.* FROM ' . db_prefix() . 'appointment a1
     INNER JOIN (
        SELECT userid, MAX(appointment_date) as max_date
        FROM ' . db_prefix() . 'appointment
        GROUP BY userid
     ) a2 ON a1.userid = a2.userid AND a1.appointment_date = a2.max_date
    ) latest_appointment',
    'latest_appointment.userid = c.userid',
    'left'
);
$CI->db->join(
    db_prefix() . 'appointment_type at',
    'at.appointment_type_id = latest_appointment.appointment_type_id',
    'left'
);
$CI->db->where('c.userid', $client_id);

if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('c.clinical_observation', $search);
    $CI->db->or_like('treatment.treatment_name', $search);
    $CI->db->or_like('t.improvement', $search);
    $CI->db->group_end();
}

$CI->db->group_by('c.id');
$CI->db->order_by("c.date", "DESC");
$CI->db->limit($length, $start);
$query   = $CI->db->get();
$results = $query->result_array();

// ======================== Render Output ========================
$CI->load->model('client_model');
$appointment_data = $CI->client_model->get_appointment_data($client_id);
$patient_prescriptions = $CI->client_model->get_patient_prescription($client_id);

// Map prescriptions to casesheet ID
$prescription_map = [];
foreach ($patient_prescriptions as $p) {
    if (!empty($p['casesheet_id'])) {
        $prescription_map[$p['casesheet_id']][] = $p['prescription_data'];
    }
}

$data = [];
$serial = $start + 1;

foreach ($results as $row) {
    $formatted_prescription = '<em>No prescription</em>';
    if (isset($prescription_map[$row['id']])) {
        $i = 1;
        $formatted_prescription = '';
        foreach ($prescription_map[$row['id']] as $raw_prescription) {
            $lines = explode('|', $raw_prescription);
            foreach ($lines as $line) {
                $formatted_prescription .= '<div>' . $i++ . ') ' . htmlspecialchars(trim(str_replace(';', ', ', $line))) . '</div>';
            }
        }
    }

    $action_buttons = '';
    $created_date = date('Y-m-d', strtotime($row['created_at']));
    $today = date('Y-m-d');

    if ($created_date === $today && staff_can('edit_casesheet', 'customers')) {
        $action_buttons .= '<a href="' . admin_url('client/edit_casesheet/' . $row['id'] . '/' . $client_id) . '"><button type="button" class="btn btn-sm edit-button" style="background-color: black; color: white;"><i class="fas fa-pencil-alt"></i></button></a> ';
    }

    $action_buttons .= '<a href="' . admin_url('client/view_casesheet/' . $row['id'] . '/' . $client_id) . '"><button type="button" class="btn btn-sm view-button" style="background-color: black; color: white;" title="View Casesheet"><i class="fas fa-eye"></i></button></a>';

    $CI->db->select('COUNT(*) as total_appointments');
    $CI->db->from(db_prefix() . 'appointment');
    $CI->db->where('userid', $client_id);
    $appointment_count_result = $CI->db->get()->row();
    $total_appointments = $appointment_count_result ? (int)$appointment_count_result->total_appointments : 0;

    $appointment_label = ($total_appointments <= 1)
        ? '<span class="label label-info">First Appointment (' . $total_appointments . ')</span>'
        : '<span class="label label-success">Follow up Appointment (' . $total_appointments . ')</span>';

    $data[] = [
        $serial++,
        _d($row['date']),
        get_staff_full_name($row['staffid']),
        $row['clinical_observation'],
        $row['suggested_diagnostics_name'],
        //$row['appointment_type_name'] ?? '-',
        $row['doctor_medicine_days'],
        $row['medicine_days'],
        $formatted_prescription,
        $action_buttons
    ];
}

echo json_encode([
    'draw' => intval($draw),
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records,
    'aaData' => $data
]);

exit();
