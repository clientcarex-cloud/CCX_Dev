<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI =& get_instance();
$CI->load->database();
$CI->load->helper('datatables');

// Get DataTables parameters
$start  = $CI->input->post('start') ?? 0;
$length = $CI->input->post('length') ?? 10;
$search_value = $CI->input->post('search')['value'] ?? '';


// ---------- COUNT TOTAL MATCHING RECORDS ----------
$CI->db->select('b.id');
$CI->db->from(db_prefix() . 'customers_groups b');
$CI->db->join(db_prefix() . 'appointment a', 'a.branch_id = b.id', 'left');

if (!empty($search_value)) {
    $CI->db->like('b.name', $search_value);
}

if ($consulted_from_date && $consulted_to_date) {
    $from_date = to_sql_date($consulted_from_date);
    $to_date = to_sql_date($consulted_to_date);

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

$CI->db->group_by('b.id');
$total_rows = $CI->db->get()->num_rows();

// ---------- MAIN QUERY ----------
$CI->db->select('
    b.name as branch_name,
    SUM(CASE WHEN a.visit_status = 1 THEN 1 ELSE 0 END) as total_visits,
    COUNT(DISTINCT CASE WHEN a.enquiry_doctor_id IS NOT NULL THEN a.userid ELSE NULL END) as enquiry_visits,
    SUM(CASE WHEN a.visit_status = 1 THEN 1 ELSE 0 END) as consultations,
    COUNT(DISTINCT CASE WHEN a.visit_status = 0 AND DATE(a.appointment_date) < CURDATE() THEN a.userid ELSE NULL END) as missed_consultations,
    ROUND(
        AVG(
            TIMESTAMPDIFF(
                MINUTE,
                a.consulted_date,
                (
                    SELECT MIN(p.created_datetime)
                    FROM tblpatient_prescription p
                    WHERE 
                        p.userid = a.userid 
                        AND DATE(p.created_datetime) = DATE(a.consulted_date)
                )
            )
        ), 2
    ) AS visits_to_prescription_avg_time
');
$CI->db->from(db_prefix() . 'customers_groups b');
$CI->db->join(db_prefix() . 'appointment a', 'a.branch_id = b.id', 'left');

if (!empty($search_value)) {
    $CI->db->like('b.name', $search_value);
}

if ($consulted_from_date && $consulted_to_date) {
    $from_date = to_sql_date($consulted_from_date);
    $to_date = to_sql_date($consulted_to_date);

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

$CI->db->group_by('b.id');
$CI->db->limit($length, $start);
$results = $CI->db->get()->result_array();

// ---------- FORMAT OUTPUT ----------
$output = [
    "draw" => intval($CI->input->post('draw')),
    "recordsTotal" => $total_rows,
    "recordsFiltered" => $total_rows,
    "aaData" => []
];

foreach ($results as $row) {
    $output['aaData'][] = [
        $row['branch_name'],
        $row['total_visits'],
        $row['enquiry_visits'],
        $row['consultations'],
        $row['missed_consultations'],
        $row['visits_to_prescription_avg_time'] !== null ? $row['visits_to_prescription_avg_time'] . ' mins' : '-',
    ];
}

header('Content-Type: application/json');
echo json_encode($output);
exit;
