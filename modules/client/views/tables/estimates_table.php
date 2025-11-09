<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

// DataTables parameters
$draw    = $CI->input->post('draw');
$start   = $CI->input->post('start');
$length  = $CI->input->post('length');
$search  = $CI->input->post('search')['value'] ?? '';
$order   = $CI->input->post('order');
$columns = $CI->input->post('columns');

$order_column_index = $order[0]['column'] ?? 0;
$order_column_name  = $columns[$order_column_index]['data'] ?? 'tblestimates.id';
$order_dir          = $order[0]['dir'] ?? 'desc';

$client_id = $client_id ?? 0;

// Main query
$CI->db->select('
    tblestimates.id,
    tblestimates.date,
    tblestimates.expirydate,
    tblestimates.status,
    tblestimates.total,
    payment_category.appointment_type_name,
    branch.name AS branch_name,
    tblestimates.adminnote,
    tblestimates.formatted_number,
    GROUP_CONCAT(DISTINCT treatment.description SEPARATOR ", ") AS treatment_name
', false); // 👈 important: prevent CodeIgniter from auto-escaping
$CI->db->group_by('tblestimates.id');

$CI->db->from(db_prefix() . 'estimates');
$CI->db->join(db_prefix() . 'appointment_type as payment_category', 'payment_category.appointment_type_id = tblestimates.appointment_type_id', 'left');
$CI->db->join(db_prefix() . 'clients as clients', 'clients.userid = tblestimates.clientid', 'left');
$CI->db->join(db_prefix() . 'customer_groups as group', 'group.customer_id = clients.userid', 'left');
$CI->db->join(db_prefix() . 'customers_groups as branch', 'branch.id = group.groupid', 'left');
$CI->db->join(db_prefix() . 'projects', 'tblprojects.id = tblestimates.project_id', 'left');
$CI->db->join(db_prefix() . 'patient_treatment as patient_treatment', 'patient_treatment.estimation_id = tblestimates.id', 'left');
$CI->db->join(db_prefix() . 'items treatment', 'treatment.id = patient_treatment.treatment_type_id', 'left');
$CI->db->where('tblestimates.clientid', $client_id);
$CI->db->where('tblestimates.status !=', 5);

// Search
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('tblestimates.formatted_number', $search);
    $CI->db->or_like('treatment.description', $search);
    $CI->db->or_like('tblestimates.date', $search);
    $CI->db->or_like('tblestimates.expirydate', $search);
    $CI->db->or_like('tblestimates.total', $search);
    $CI->db->group_end();
}

// Total count before filtering
$total_records = $CI->db->count_all_results('', false);

// Apply ordering and pagination
//$CI->db->order_by($order_column_name, $order_dir);
$CI->db->order_by("tblestimates.date", "DESC");
$CI->db->limit($length, $start);

// Get result
$results = $CI->db->get()->result_array();

// Append description from tblitemable
foreach ($results as &$estimate) {
    $CI->db->select('description');
    $CI->db->where('rel_type', 'estimate');
    $CI->db->where('rel_id', $estimate['id']);
    $item = $CI->db->get(db_prefix() . 'itemable')->row();
    $estimate['description'] = $item->description ?? '-';
}

// Prepare output
$data = [];
$serial = $start + 1;

foreach ($results as $row) {
    $data[] = [
		e($row['formatted_number']),
		app_format_money_custom($row['total'], '1'),
		$row['branch_name'],
		$row['treatment_name'],
		$row['appointment_type_name'],
		_d($row['date']),
		_d($row['expirydate']),
		format_estimate_status($row['status']),
		$row['adminnote'],
		($row['status'] != 4 ? '
			<a href="' . admin_url('estimates/estimate/' . $row['id']) . '" class="btn btn-sm btn-warning" title="' . _l('edit') . '">
				<i class="fa fa-edit"></i>
			</a>
			<button class="btn btn-sm btn-success" onclick="convertToInvoice1(' . $row['id'] . ')">
				' . _l('accept') . '
			</button>' : '')
			
	];

}

// Return response
echo json_encode([
    'draw' => intval($draw),
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records,
    'aaData' => $data,
]);

exit;
