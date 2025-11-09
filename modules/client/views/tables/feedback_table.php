<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

// Input from DataTable
$draw    = $CI->input->post('draw');
$start   = $CI->input->post('start');
$length  = $CI->input->post('length');
$search  = $CI->input->post('search')['value'] ?? '';
$order   = $CI->input->post('order');
$columns = $CI->input->post('columns');

// Order setup
$order_column_index = $order[0]['column'] ?? 0;
//$order_column_name  = $columns[$order_column_index]['data'] ?? 'sr.id';
//$order_dir          = $order[0]['dir'] ?? 'desc';

$order_column_name  = 'sr.id';
$order_dir          = 'desc';

// Set client ID (passed from controller or globally defined)
$client_id = $client_id ?? 0;

// Main Query
$CI->db->select('
    sr.id,
    sr.user_id,
    sr.date_sent,
    sr.status,
    sr.send_email,
    sr.send_sms,
    sr.send_whatsapp,
    sr.share_key,
    c.userid,
    c.company,
    t.id AS testimonial_id,
    t.slug AS testimonial_slug,
    t.title AS testimonial_title,
    t.description AS testimonial_description,
	(
        SELECT COUNT(*) 
        FROM ' . db_prefix() . 'flextestimonialresponses tr 
        WHERE tr.request_id = sr.id
    ) AS response_count
');
$CI->db->from(db_prefix() . 'share_request sr');
$CI->db->join(db_prefix() . 'clients c', 'c.userid = sr.user_id', 'left');
$CI->db->join(db_prefix() . 'flextestimonial t', 't.id = sr.feedback_id', 'left');
$CI->db->where([
    'sr.type' => 'patient',
    'sr.user_id' => $client_id,
]);

// Filtering
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('t.title', $search);
    $CI->db->or_like('t.description', $search);
    $CI->db->or_like('c.company', $search);
    $CI->db->group_end();
}

// Total before pagination
$total_records = $CI->db->count_all_results('', false);

// Ordering and Pagination
$CI->db->order_by($order_column_name, $order_dir);
$CI->db->limit($length, $start);

// Fetch result
$results = $CI->db->get()->result_array();

// Prepare DataTables response
$data = [];
foreach ($results as $row) {
    $view_button = '<a href="javascript:void(0);" class="view-feedback" data-title="' . htmlspecialchars($row['id'], ENT_QUOTES) . '">
                    <i class="fa fa-eye"></i>
                </a>';

		if (!empty($row['response_count'])) {
			$view_button .= ' <span class="badge badge-pill badge-success">' . (int)$row['response_count'] . '</span>';
		}


    $data[] = [
        htmlspecialchars($row['testimonial_title'] ?? '-', ENT_QUOTES),
        htmlspecialchars($row['testimonial_description'] ?? '-', ENT_QUOTES),
        $view_button
    ];
}

// Return JSON response
echo json_encode([
    'draw' => intval($draw),
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records,
    'aaData' => $data,
]);
exit;
