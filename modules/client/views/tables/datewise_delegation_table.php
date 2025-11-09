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

// Normalize branch filters for CSV stored branch IDs
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
// Base columns + last 7 days
// ====================
$base_columns = ['s.staffid', 's.firstname'];
$date_columns = [];
for ($i = 0; $i <= 6; $i++) {
    $date_columns[] = date('Y-m-d', strtotime("-$i days"));
}
$columns = array_merge($base_columns, ['branch_name', 'role_name', 'active_tasks_count'], $date_columns);

$order_column = $columns[$order_column_index] ?? 's.staffid';

// ====================
// Total staff count
// ====================
$totalRecords = $CI->db->count_all(db_prefix().'staff');

// ====================
// Filtered count
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

$results = $CI->db->get()->result_array();

// ====================
// Output formatting
// ====================
$output = [
    "draw"            => $draw,
    "recordsTotal"    => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data"            => []
];

$i = $start + 1;
$totalActive = 0;
$completedActive = 0;
foreach ($results as $row) {
    $dataRow = [];

    // s_no
    $dataRow[] = $i++;

    // Assignee
    $assignee = '<a href="' . admin_url('staff/member/' . $row['staffid']) . '">' 
              . e($row['firstname'] . ' ' . $row['lastname']) . '</a>';
    $dataRow[] = $assignee;

    // Branch
    $dataRow[] = e($row['branch_name'] ?? '');

    // Role
    $dataRow[] = e($row['role_name'] ?? '');

    // Total Active Tasks Count (status != COMPLETE)
    $totalActive = $CI->db->select('COUNT(t.id) as total')
        ->from(db_prefix().'tasks t')
        ->join(db_prefix().'task_assigned ta', 'ta.taskid = t.id', 'inner')
        ->where('ta.staffid', $row['staffid'])
        ->where('t.status !=', $CI->tasks_model::STATUS_COMPLETE)
        ->get()->row()->total;
	
	$completedActive = $CI->db->select('COUNT(t.id) as total')
        ->from(db_prefix().'tasks t')
        ->join(db_prefix().'task_assigned ta', 'ta.taskid = t.id', 'inner')
        ->where('ta.staffid', $row['staffid'])
        ->where('t.status', $CI->tasks_model::STATUS_COMPLETE)
        ->get()->row()->total;
		
    $dataRow[] = $totalActive;

    // Task counts for last 7 days (status != COMPLETE)
    foreach ($date_columns as $date) {
        $count = $CI->db->select('COUNT(t.id) as total')
            ->from(db_prefix().'tasks t')
            ->join(db_prefix().'task_assigned ta', 'ta.taskid = t.id', 'inner')
            ->where('ta.staffid', $row['staffid'])
            ->where('DATE(t.dateadded)', $date)
            ->where('t.status !=', $CI->tasks_model::STATUS_COMPLETE)
            ->get()->row()->total;

        $dataRow[] = $count;
    }
	if (($totalActive + $completedActive) > 0) {
		$percentage = round(($completedActive / ($totalActive + $completedActive)) * 100, 2) . '%';
	} else {
		$percentage = '0%';
	}

	$dataRow[] = $percentage;
    $output['data'][] = $dataRow;
}

echo json_encode($output);
exit;
