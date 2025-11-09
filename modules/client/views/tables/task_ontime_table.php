<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->db->reset_query();

$selected_roles  = $doctor_id;

$from_date = $consulted_from_date;
$to_date = $consulted_to_date;

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

// -----------------------------
// Subquery: Aggregate tasks per staff
// The date filter for tasks is applied here,
// and we are also getting the earliest start date for the aggregated tasks.
$CI->db->select("
    ta.staffid,
    SUM(IF(t.status=5 AND t.duedate IS NOT NULL AND t.datefinished IS NOT NULL AND DATE(t.datefinished) > DATE(t.duedate),1,0)) AS tasks_delayed,
    SUM(IF(t.status=5 AND t.duedate IS NOT NULL AND t.datefinished IS NOT NULL AND DATE(t.datefinished) <= DATE(t.duedate),1,0)) AS tasks_ontime,
    SUM(IF(t.status=5 AND t.duedate IS NOT NULL,1,0)) AS total_with_duedate,
    MIN(t.startdate) AS earliest_task_start
")
->from(db_prefix().'task_assigned ta')
->join(db_prefix().'tasks t','t.id = ta.taskid','left');

// The date filter is correctly applied here.
if ($from_date && $to_date) {
   $CI->db->where("DATE(t.startdate) BETWEEN '$from_date' AND '$to_date'");
}

$subquery = $CI->db
->group_by('ta.staffid')
->get_compiled_select();

// -----------------------------
// Main query: Staff + branch + role + aggregated tasks
$CI->db->select("
    s.staffid,
    CONCAT(s.firstname, ' ', s.lastname) AS assignee,
    " . $branchNameSelect . " AS branch,
    r.name AS role,
    IFNULL(t.tasks_delayed,0) AS tasks_delayed,
    IFNULL(t.tasks_ontime,0) AS tasks_ontime,
    IFNULL(t.total_with_duedate,0) AS total_with_duedate,
    IFNULL(t.earliest_task_start, '') AS earliest_task_start
", false);

$CI->db->from(db_prefix().'staff s');
$CI->db->join(db_prefix().'roles r', 'r.roleid = s.role', 'left');

$CI->db->join("($subquery) t", "t.staffid = s.staffid", "left");
if (!empty($branchFilterSql)) {
    $CI->db->where($branchFilterSql, null, false);
}
if (!empty($selected_roles)) {
    $CI->db->where_in('s.role', $selected_roles);
}

// -----------------------------
// DataTables server-side search
if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
    $search = $_POST['search']['value'];
    $CI->db->group_start();
    $CI->db->like("CONCAT(s.firstname, ' ', s.lastname)", $search);
    $escapedSearch = $CI->db->escape_like_str($search);
    $CI->db->or_where($branchNameSelect . " LIKE '%" . $escapedSearch . "%'", null, false);
    $CI->db->or_like("r.name", $search);
    $CI->db->group_end();
}

// Ordering
if (isset($_POST['order'][0]['column'])) {
    $columns = ['assignee','branch','role','tasks_delayed','tasks_ontime','total_with_duedate', 'earliest_task_start'];
    $order_col_index = $_POST['order'][0]['column'];
    $order_dir = $_POST['order'][0]['dir'];
    if (isset($columns[$order_col_index])) {
        $CI->db->order_by($columns[$order_col_index], $order_dir);
    }
}

// Pagination
if (isset($_POST['start']) && isset($_POST['length'])) {
    $start  = intval($_POST['start']);
    $length = intval($_POST['length']);

    if ($length != -1) { // -1 means "all records"
        $CI->db->limit($length, $start);
    }
}


$query = $CI->db->get();
$rows = $query->result_array();

// Prepare output
$output = ['data'=>[]];
$i = isset($_POST['start']) ? $_POST['start'] + 1 : 1;

foreach($rows as $row){
    $percentage = ($row['total_with_duedate'] > 0)
        ? round(($row['tasks_ontime'] / $row['total_with_duedate']) * 100, 2) . '%'
        : '0%';

    $output['data'][] = [
        $i++,
        $row['assignee'],
        $row['branch'],
        $row['role'],
        $row['tasks_delayed'],
        $row['tasks_ontime'],
        $row['total_with_duedate'],
        $percentage,
        $row['earliest_task_start']
    ];
}

// Records count for DataTables
$CI->db->from(db_prefix().'staff s');
if (!empty($selected_roles)) {
    $CI->db->where_in('s.role', $selected_roles);
}
if (!empty($branchFilterSql)) {
    $CI->db->where($branchFilterSql, null, false);
}
$recordsTotal = $CI->db->count_all_results();

$output['recordsTotal'] = $recordsTotal;
$output['recordsFiltered'] = $recordsTotal; // optionally adjust if search applied

echo json_encode($output);
exit;
