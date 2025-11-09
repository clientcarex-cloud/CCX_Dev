<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI =& get_instance();

// ===== Get Filters =====
$from_date = !empty($consulted_from_date) ? to_sql_date($consulted_from_date) : date('Y-m-01');
$to_date   = !empty($consulted_to_date) ? to_sql_date($consulted_to_date) : date('Y-m-d');
$selected_branch_id = isset($selected_branch_id) ? (array)$selected_branch_id : [];

// ===== Step 1: Get patients for branch =====
$branch_patients = [];
if (!empty($selected_branch_id)) {
    $CI->db->select('customer_id AS userid');
    $CI->db->from(db_prefix() . 'customer_groups');
    $CI->db->where_in('groupid', $selected_branch_id);
    $branch_patients = $CI->db->get()->result_array();
}

$branch_patient_ids = array_column($branch_patients, 'userid');

// If branch is selected and no patients match → return empty result
if (!empty($selected_branch_id) && empty($branch_patient_ids)) {
    echo json_encode([
        'draw' => intval($CI->input->post('draw')),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'aaData' => [],
    ]);
    exit;
}

// ===== Step 2: Get latest casesheet per patient in branch =====
$CI->db->select('c.userid, t.treatment_status');
$CI->db->from(db_prefix() . 'casesheet AS c');
$CI->db->join(db_prefix() . 'patient_treatment AS t', 't.casesheet_id = c.id', 'left');
$CI->db->join(
    "(SELECT userid, MAX(id) AS latest_id
      FROM " . db_prefix() . "casesheet
      WHERE `date` BETWEEN '" . to_sql_date($from_date) . "' AND '" . to_sql_date($to_date) . "'
      " . (!empty($branch_patient_ids) ? " AND userid IN (" . implode(',', array_map('intval', $branch_patient_ids)) . ")" : "") . "
      GROUP BY userid
    ) AS latest",
    'latest.latest_id = c.id',
    'inner'
);

$latestCases = $CI->db->get()->result_array();

if (empty($latestCases)) {
    echo json_encode([
        'draw' => intval($CI->input->post('draw')),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'aaData' => [],
    ]);
    exit;
}

$userids = array_column($latestCases, 'userid');

// ===== Step 3: Get invoice totals for these patients =====
$invoices = [];
if (!empty($userids)) {
    $CI->db->select('clientid AS userid, 
                     SUM(total) AS total_amount, 
                     SUM(COALESCE(paid_tbl.paid_amount, 0)) AS paid_amount,
                     SUM(total) - SUM(COALESCE(paid_tbl.paid_amount, 0)) AS due_amount');
    $CI->db->from(db_prefix() . 'invoices inv');
    $CI->db->join(
        "(SELECT invoiceid, SUM(amount) AS paid_amount 
          FROM " . db_prefix() . "invoicepaymentrecords 
          GROUP BY invoiceid) AS paid_tbl",
        'paid_tbl.invoiceid = inv.id',
        'left'
    );

    $CI->db->where_in('clientid', $userids);
    $CI->db->where('inv.date >=', to_sql_date($from_date));
    $CI->db->where('inv.date <=', to_sql_date($to_date));

    $CI->db->group_by('clientid');

    $invData = $CI->db->get()->result_array();

    foreach ($invData as $inv) {
        $invoices[$inv['userid']] = [
            'total' => (float) $inv['total_amount'],
            'paid'  => (float) $inv['paid_amount'],
            'due'   => (float) $inv['due_amount'],
        ];
    }
}

// ===== Step 4: Group by treatment_status =====
$grouped = [];
foreach ($latestCases as $case) {
    $status = $case['treatment_status'] ?: 'N/A';
    $userid = $case['userid'];

    if (!isset($grouped[$status])) {
        $grouped[$status] = [
            'userids' => [],
            'total'   => 0,
            'paid'    => 0,
            'due'     => 0,
        ];
    }

    if (!in_array($userid, $grouped[$status]['userids'])) {
        $grouped[$status]['userids'][] = $userid;
        if (isset($invoices[$userid])) {
            $grouped[$status]['total'] += $invoices[$userid]['total'];
            $grouped[$status]['paid']  += $invoices[$userid]['paid'];
            $grouped[$status]['due']   += $invoices[$userid]['due'];
        }
    }
}

// ===== Step 5: Prepare DataTables output =====
$output = [
    'draw' => intval($CI->input->post('draw')),
    'recordsTotal' => count($grouped),
    'recordsFiltered' => count($grouped),
    'aaData' => [],
];

$total_patients = 0;
$total_package  = 0;
$total_paid     = 0;
$total_due      = 0;

foreach ($grouped as $status => $data) {
    $patient_count = count($data['userids']);
    $total_patients += $patient_count;
    $total_package  += $data['total'];
    $total_paid     += $data['paid'];
    $total_due      += $data['due'];

    $output['aaData'][] = [
        $status,
        $patient_count,
        app_format_money_custom($data['total'], 1),
        app_format_money_custom($data['paid'], 1),
        app_format_money_custom($data['due'], 1),
    ];
}

$output['aaData'][] = [
    '<strong>Total</strong>',
    "<strong>{$total_patients}</strong>",
    '<strong>' . app_format_money_custom($total_package, 1) . '</strong>',
    '<strong>' . app_format_money_custom($total_paid, 1) . '</strong>',
    '<strong>' . app_format_money_custom($total_due, 1) . '</strong>',
];

echo json_encode($output);
exit;
