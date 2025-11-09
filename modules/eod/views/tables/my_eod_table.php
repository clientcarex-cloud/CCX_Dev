<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('eod_model');  // load your model, adjust name if needed

// You can get POST parameters for filtering, pagination, search etc. here if needed
// e.g. $search = $CI->input->post('search')['value'] ?? '';

// Select columns you want to display
$CI->db->select("
    eod.id,
    eod.date,
    eod.eod_id,
    staff.firstname,
    staff.lastname,
    branch.name as branch,
    roles.name as designation,
    eod.subject,
    eod.activity,
    eod.today_report,
    eod.eod_status
");

$CI->db->from(db_prefix() . 'eod as eod');
$CI->db->join(db_prefix() . 'staff staff', 'staff.staffid = eod.staffid', 'left');
$CI->db->join(db_prefix() . 'customers_groups branch', 'branch.id = eod.branch_id', 'left');
$CI->db->join(db_prefix() . 'roles roles', 'roles.roleid = staff.role', 'left');

// Optional: filter by logged-in user
$CI->db->where('eod.staffid', get_staff_user_id());

// Filtering, searching, ordering logic here based on $_POST inputs can be added

$query = $CI->db->get();
$results = $query->result_array();

$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

$output = [
    "draw" => $draw,
    "recordsTotal" => count($results),   // ideally total records count from DB
    "recordsFiltered" => count($results),// adjust if filters/search used
    "data" => []
];

// Build data rows for DataTables
foreach ($results as $row) {
    $dataRow = [];

    $dataRow[] = _dt($row['date']);
    $dataRow[] = $row['eod_id'];
    $dataRow[] = $row['firstname'] . ' ' . $row['lastname'];
    $dataRow[] = $row['branch'];
    $dataRow[] = $row['designation'];
    $dataRow[] = $row['subject'];
    $dataRow[] = $row['activity'];
    $dataRow[] = $row['today_report'];
    $status = $row['eod_status'];
	$status_label = '';

	if ($status == 'Approved') {
		$status_label = '<span class="label label-success">' . _l('approved') . '</span>';
	} elseif ($status == 'Disapproved') {
		$status_label = '<span class="label label-danger">' . _l('disapproved') . '</span>';
	} else {
		$status_label = '<span class="label label-warning">' . _l('pending') . '</span>';
	}

	$dataRow[] = $status_label;

    // Actions buttons example
    $editUrl = admin_url('eod/edit/' . $row['id']);
    $deleteUrl = admin_url('eod/delete/' . $row['id']);
    $actions = '<a href="' . $editUrl . '" class="btn btn-sm btn-info" style="color: #fff">' . _l('edit') . '</a> ';
    //$actions .= '<a href="' . $deleteUrl . '" class="btn btn-sm btn-danger" onclick="return confirm(\'' . _l('confirm_delete') . '\')">' . _l('delete') . '</a>';

    $dataRow[] = $actions;

    $output['data'][] = $dataRow;
}

echo json_encode($output);
exit;
