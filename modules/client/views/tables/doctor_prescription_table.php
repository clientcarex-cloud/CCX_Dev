<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

// DataTables input
$draw    = $CI->input->post('draw');
$start   = $CI->input->post('start');
$length  = $CI->input->post('length');
$search  = $CI->input->post('search')['value'] ?? '';
$order   = $CI->input->post('order');
$columns = $CI->input->post('columns');

// Order settings
$order_column_index = $order[0]['column'] ?? 0;
$order_column_name  = $columns[$order_column_index]['data'] ?? 'created_datetime';
$order_dir          = $order[0]['dir'] ?? 'desc';

// Get current client ID
$client_id = $client_id ?? 0;

// Query base
$CI->db->from(db_prefix() . 'patient_prescription');
$CI->db->where('userid', $client_id);

// Search logic
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('prescription_data', $search);
    $CI->db->or_like('created_datetime', $search);
    $CI->db->group_end();
}

// Get total before limit
$total_records = $CI->db->count_all_results('', false);

// Apply ordering and limit
//$CI->db->order_by($order_column_name, $order_dir);
$CI->db->order_by("created_datetime", "DESC");
$CI->db->limit($length, $start);

// Fetch result rows
$results = $CI->db->get()->result_array();

// Prepare DataTable output
$data = [];
$serial = $start + 1;

foreach ($results as $row) {
    $prescription_id = $row['patient_prescription_id'];
    $casesheet_id = $row['casesheet_id'];

    // Parse prescription items
    $items = array_values(array_filter(array_map('trim', explode('|', $row['prescription_data'] ?? ''))));

    // Parse remarks
    $remarks = array_map('trim', explode('|', $row['medicine_remarks'] ?? ''));
    $remarks = array_pad($remarks, count($items), '');

    // Get casesheet data
    $casesheet = $CI->db->get_where(db_prefix() . 'casesheet', ['id' => $casesheet_id])->row();
    $medicine_days = $casesheet->medicine_days ?? '';
    $followup_date = $casesheet->followup_date ?? '';

    // Build view button with all required data attributes
    $view_button = '<button 
        class="btn btn-sm btn-outline-info toggle-medicines" 
        data-id="' . $prescription_id . '"
        data-casesheet-id="' . $casesheet_id . '"
        data-prescription="' . htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8') . '"
        data-remarks="' . htmlspecialchars(json_encode($remarks), ENT_QUOTES, 'UTF-8') . '"
        data-medicine-days="' . htmlspecialchars($medicine_days, ENT_QUOTES, 'UTF-8') . '"
        data-followup-date="' . htmlspecialchars($followup_date, ENT_QUOTES, 'UTF-8') . '">
        <i class="fa fa-eye"></i>
    </button>';

    // Prepare individual row
    $data[] = [
        $serial++,
        get_staff_full_name($row['created_by']),
        date("d-m-Y", strtotime($row['created_datetime'])),
        $row['medicine_given_by'] ? get_staff_full_name($row['medicine_given_by']) : '',
        $row['medicine_given_date'] ? _dt($row['medicine_given_date']) : '',
        $view_button,
    ];
}



// Final response
echo json_encode([
    'draw' => (int) $draw,
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records,
    'aaData' => $data,
]);

exit;
