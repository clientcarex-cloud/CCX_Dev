<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('client_model');

$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$order  = $_POST['order'][0] ?? null;
$columns = $_POST['columns'] ?? [];

// Get filter inputs from URL segments or POST
$from_date = !empty($consulted_from_date) ? to_sql_date($consulted_from_date) : null;
$to_date   = !empty($consulted_to_date) ? to_sql_date($consulted_to_date) : null;
$report_type = !empty($type) ? $type : null;
$lead_sourceId = !empty($lead_sourceId) ? $lead_sourceId : null;
$selected_branch_id = isset($selected_branch_id) && is_array($selected_branch_id) ? $selected_branch_id : [];

// Selects
$CI->db->select([
    'patients.userid',
    'patients.company',
    'new.mr_no',
    'appointments.enquiry_doctor_id',
    'treatment.description as treatment_name',
    'appointments.created_by',
    'appointments.created_at',
    'appointments.appointment_date',
    'appointments.appointment_date as visited_date',
    'appointments.consulted_date',
    'appointments.remarks',
    'new.registration_start_date',
    'source.name as source_name',
]);

// Joins
$CI->db->from(db_prefix() . 'clients patients');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = patients.userid', 'left');
$CI->db->join(db_prefix() . 'appointment appointments', 'appointments.userid = patients.userid', 'left');
$CI->db->join(db_prefix() . 'leads leads', 'leads.id = patients.leadid', 'left');
$CI->db->join(db_prefix() . 'leads_sources source', 'source.id = new.patient_source_id', 'left');
$CI->db->join(db_prefix() . 'items treatment', 'treatment.id = appointments.treatment_id', 'left');


// === Filters based on the clicked link type ===

if (!empty($lead_sourceId)) {
    $CI->db->where('new.patient_source_id', $lead_sourceId);
}

// Filter by branch
if (!empty($selected_branch_id)) {
    $CI->db->join(db_prefix() . 'customer_groups cg', 'cg.customer_id = patients.userid', 'inner');
    $CI->db->where_in('cg.groupid', $selected_branch_id);
}

switch ($category) {
    case 'enquiries':
        // A user is an 'enquiry' if they exist in the clients table
        // We will just filter by the source ID and dates
        if ($from_date) {
            $CI->db->where('patients.datecreated >=', $from_date);
        }
        if ($to_date) {
            $CI->db->where('patients.datecreated <=', $to_date);
        }
        break;

    case 'appointments':
        // Filter by appointment date
        if ($from_date) {
            $CI->db->where('appointments.appointment_date >=', $from_date . ' 00:00:00');
        }
        if ($to_date) {
            $CI->db->where('appointments.appointment_date <=', $to_date . ' 23:59:59');
        }
        break;

    case 'visits':
        // Filter by appointments with visit_status = 1 and no consulted date
        $CI->db->where('appointments.visit_status', 1);
        $CI->db->where("appointments.consulted_date IS NULL OR appointments.consulted_date = '0000-00-00 00:00:00'");
        if ($from_date) {
            $CI->db->where('appointments.appointment_date >=', $from_date . ' 00:00:00');
        }
        if ($to_date) {
            $CI->db->where('appointments.appointment_date <=', $to_date . ' 23:59:59');
        }
        break;

    case 'consultations':
        // Filter by appointments with visit_status = 1 and a valid consulted date
        $CI->db->where('appointments.visit_status', 1);
        $CI->db->where('appointments.consulted_date IS NOT NULL');
        $CI->db->where("appointments.consulted_date != '0000-00-00 00:00:00'");
        if ($from_date) {
            $CI->db->where('appointments.consulted_date >=', $from_date . ' 00:00:00');
        }
        if ($to_date) {
            $CI->db->where('appointments.consulted_date <=', $to_date . ' 23:59:59');
        }
        break;

    case 'registrations':
        // Filter by users with an MR number and a registration date
        $CI->db->where("new.mr_no IS NOT NULL AND new.mr_no != ''");
        if ($from_date) {
            $CI->db->where('new.registration_start_date >=', $from_date);
        }
        if ($to_date) {
            $CI->db->where('new.registration_start_date <=', $to_date);
        }
        break;

    default:
        // No specific report type filter applied
        break;
}

// === Other existing filters ===

// Filter by single consulted date
if (!empty($consulted_date)) {
    $sql_date = to_sql_date($consulted_date);
    $CI->db->group_start();
    $CI->db->like('appointments.consulted_date', $sql_date, 'after');
    $CI->db->or_like('appointments.appointment_date', $sql_date, 'after');
    $CI->db->group_end();
}

// Filter by doctor
if (!empty($doctor_id)) {
    if (is_numeric($doctor_id)) {
        $CI->db->where('appointments.enquiry_doctor_id', intval($doctor_id));
    } elseif (is_array($doctor_id)) {
        $doctor_ids = array_map('intval', $doctor_id);
        $CI->db->where_in('appointments.enquiry_doctor_id', $doctor_ids);
    } elseif (is_string($doctor_id) && strpos($doctor_id, ',') !== false) {
        $doctor_ids = array_map('intval', explode(',', $doctor_id));
        $CI->db->where_in('appointments.enquiry_doctor_id', $doctor_ids);
    }
}

// Filter by consulted date range
if (!empty($consulted_from_date) && !empty($consulted_to_date)) {
    $from_date = to_sql_date($consulted_from_date);
    $to_date = to_sql_date($consulted_to_date);
    $CI->db->group_start();
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
    $CI->db->group_start();
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
    $CI->db->group_end();
}


$CI->db->order_by('patients.userid', 'DESC');
$tempdb = clone $CI->db;
$total_filtered = $tempdb->count_all_results();
if ($length != -1) {
    $CI->db->limit($length, $start);
}

$query = $CI->db->get();
$results = $query->result_array();

$CI->db->from(db_prefix() . 'clients');
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
    
    $total = $paid = $due = 0;
    foreach ($packageDetailsList as $p) {
        $total += $p['total'];
        $paid  += $p['paid'];
        $due   += $p['due'];
    }
    
    $url = admin_url('client/reports/' . $report_type . '/' . $aRow['userid']);
    $nameLink = '<a href="' . $url . '" target="blank">' . e(format_name($aRow['company'])) . '</a>';
    
    $row = [];
    $row[] = $nameLink;
    $row[] = $aRow['mr_no'];
    $row[] = $aRow['source_name'];
    $row[] = $aRow['treatment_name'];
    $row[] = _d($aRow['created_at']);
    $row[] = _d($aRow['appointment_date']);
    $row[] = _d($aRow['visited_date']);
    $row[] = _d($aRow['consulted_date']);
    $row[] = get_staff_full_name($aRow['enquiry_doctor_id']);
    $row[] = _d($aRow['registration_start_date']);
    $row[] = $aRow['remarks'];
    $row[] = e(app_format_money_custom($total, 1));
    $row[] = e(app_format_money_custom($paid, 1));
    $row[] = e(app_format_money_custom($due, 1));
    
    $output['aaData'][] = $row;
}

echo json_encode($output);
exit;