<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('tasks_model');

// ====================
// Inputs
// ====================
$draw   = intval($CI->input->post('draw'));
$start  = intval($CI->input->post('start'));
$length = intval($CI->input->post('length'));
$search = $CI->input->post('search')['value'] ?? '';
$selected_roles     = $doctor_id;
$order_column_index = $CI->input->post('order')[0]['column'] ?? 0;
$order_dir          = $CI->input->post('order')[0]['dir'] ?? 'asc';

// Normalize branch filters to support CSV-stored values
$normalizeBranchIds = static function ($value) {
    if (empty($value)) {
        return [];
    }
    if (!is_array($value)) {
        $value = explode(',', (string) $value);
    }
    $value = array_map('intval', array_filter($value, function ($item) {
        return $item !== '' && $item !== null;
    }));
    return array_values(array_filter($value, function ($item) {
        return $item > 0;
    }));
};

$selectedBranchIds = $normalizeBranchIds(isset($selected_branch_id) ? $selected_branch_id : []);
$branchFilterSql   = '';
if (!empty($selectedBranchIds)) {
    $clauses = array_map(function ($branchId) {
        return 'FIND_IN_SET(' . $branchId . ', s.branch_id)';
    }, $selectedBranchIds);
    $branchFilterSql = '(' . implode(' OR ', $clauses) . ')';
}

$branchNameSelect = '(SELECT GROUP_CONCAT(name ORDER BY name SEPARATOR ", ")
    FROM ' . db_prefix() . 'customers_groups
    WHERE FIND_IN_SET(' . db_prefix() . 'customers_groups.id, s.branch_id)
)';

// ====================
// Get statuses dynamically
// ====================
$statuses = $CI->tasks_model->get_statuses(); // Adjust if different
$status_columns = array_map(function($s) { return $s['name']; }, $statuses);

// ====================
// Columns for ordering
// ====================
$columns = array_merge(['staffid', 'firstname', 'branch_name', 'role_name'], $status_columns);
$order_column = $columns[$order_column_index] ?? 'staffid';

// ====================
// Total staff count
// ====================
$totalRecords = $CI->db->count_all(db_prefix().'staff');

// ====================
// Filtered staff
// ====================
$CI->db->reset_query();
$CI->db->select('s.staffid');
$CI->db->from(db_prefix().'staff s');

if (!empty($branchFilterSql)) {
    $CI->db->where($branchFilterSql, null, false);
}
if (!empty($selected_roles)) {
    $CI->db->where_in('s.role', $selected_roles);
}
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('s.firstname', $search);
    $CI->db->or_like('s.lastname', $search);
    $CI->db->or_like('s.email', $search);
    $CI->db->group_end();
}

$filteredRecords = $CI->db->count_all_results();

// ====================
// Main staff query
// ====================
$CI->db->reset_query();
$CI->db->select('s.staffid, s.firstname, s.lastname, s.branch_id, s.role, ' . $branchNameSelect . ' as branch_name, r.name as role_name', false);
$CI->db->from(db_prefix().'staff s');
$CI->db->join(db_prefix().'roles r', 'r.roleid = s.role', 'left');

if (!empty($branchFilterSql)) {
    $CI->db->where($branchFilterSql, null, false);
}
if (!empty($selected_roles)) {
    $CI->db->where_in('s.role', $selected_roles);
}
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('s.firstname', $search);
    $CI->db->or_like('s.lastname', $search);
    $CI->db->or_like('s.email', $search);
    $CI->db->group_end();
}

$CI->db->order_by($order_column, $order_dir);
if ($length != -1) {
    $CI->db->limit($length, $start);
}

$staffList = $CI->db->get()->result_array();

// ====================
// Prepare output
// ====================
$output = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => []
];

$i = $start + 1;
foreach ($staffList as $s) {
    $dataRow = [];

    // S.No
    $dataRow[] = $i++;

    // Assignee
    $dataRow[] = '<a href="'.admin_url('staff/member/'.$s['staffid']).'">'.e($s['firstname'].' '.$s['lastname']).'</a>';

    // Branch
    $dataRow[] = e($s['branch_name'] ?? '');

    // Role
    $dataRow[] = e($s['role_name'] ?? '');
	
	$total_tasks = 0;
	$completed_tasks = 0;
    // Task counts by status
    foreach ($statuses as $status) {
        $count = $CI->db->from(db_prefix().'tasks t')
            ->join(db_prefix().'task_assigned ta', 'ta.taskid = t.id', 'inner')
            ->where('ta.staffid', $s['staffid'])
            ->where('t.status', $status['id']) // assuming status ID stored in tasks.status
            ->count_all_results();

        $dataRow[] = $count;
		$total_tasks += $count;
		if (strtolower($status['name']) === 'complete') {
            $completed_tasks += $count;
        }
    }
	if ($total_tasks > 0) {
		$percentage = round(($completed_tasks / $total_tasks) * 100, 2) . '%';
	} else {
		$percentage = '0%';
	}

	$dataRow[] = $percentage;
	 
    $output['data'][] = $dataRow;
}

echo json_encode($output);
exit;
