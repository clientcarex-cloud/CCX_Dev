<?php defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->helper('client/custom');

$start = (int) $CI->input->post('start');
$length = (int) $CI->input->post('length');
$draw = (int) $CI->input->post('draw');
$order = $CI->input->post('order');
$search = $CI->input->post('search')['value'] ?? '';

$order_column_index = $order[0]['column'] ?? 0;
$order_dir = $order[0]['dir'] ?? 'asc';

// Define columns
$aColumns = [
    'ml.id',
    's.name AS status_name',
    'ml.message_type',
    'ml.message',
    'ml.response',
    'ml.datetime'
];

$order_column_name = $aColumns[$order_column_index];

// Build query
$CI->db->select('
    ml.id,
    s.name AS status_name,
    ml.message_type,
    ml.message,
    ml.response,
    ml.datetime
');
$CI->db->from(db_prefix() . 'message_log ml');
$CI->db->join(db_prefix() . 'leads_status s', 's.id = ml.status', 'left');

// Filter by leadid or userid
$userid = $userid;
//$userid = $CI->input->get('userid');

if (!empty($lead_id)) {
    $CI->db->where('ml.leadid', $lead_id);
} elseif (!empty($userid)) {
    $CI->db->where('ml.userid', $userid);
}

// Get total before filter
$total_records = $CI->db->count_all_results('', false);

// Search
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('s.name', $search);
    $CI->db->or_like('ml.message_type', $search);
    $CI->db->or_like('ml.message', $search);
    $CI->db->or_like('ml.response', $search);
    $CI->db->group_end();
}

// Order & paginate
$CI->db->order_by($order_column_name, $order_dir);
$CI->db->limit($length, $start);

// Get results
$results = $CI->db->get()->result_array();

// Format
$data = [];
$serial = $start + 1;
foreach ($results as $row) {
    $serial_number = $serial++;

    $status_name = $row['status_name'] ?? '';
    $message_type = ucfirst($row['message_type'] ?? '');
    $message = $row['message'] ?? '';
    $response = $row['response'] ?? '';
    $datetime = _dt($row['datetime']) ?? '';

    // Handle SMS message response separately
    if (strtolower($row['message_type']) === 'sms') {
        $decoded_response = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decoded_response['message'][0])) {
            $response = $decoded_response['message'][0];
        }
    }

    $data[] = [
        $serial_number,
        $status_name,
        $message_type,
        $message,
        $response,
        $datetime
    ];
}


// Output
echo json_encode([
    'draw' => $draw,
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records,
    'aaData' => $data
]);
exit();
