<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// DataTables params
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$draw   = intval($_POST['draw'] ?? 1);
$search_value = $_POST['search']['value'] ?? '';

$branch_id = $selected_branch_id;
$type      = $category;
$from_date = !empty($consulted_from_date) ? to_sql_date($consulted_from_date) : '2025-01-01';
$to_date   = !empty($consulted_to_date) ? to_sql_date($consulted_to_date) : '2025-01-31';

$exclude_desc = ['Consultation Fee', 'Consulation Fee'];

/**
 * Step 1: Clients with >1 invoices (excluding consultation fee)
 */
$CI->db->reset_query();
$clients_with_2plus = $CI->db
    ->select('i.clientid', false)
    ->from(db_prefix().'invoices i')
    ->join(db_prefix().'itemable itm', 'itm.rel_id = i.id AND itm.rel_type="invoice"', 'inner')
    ->join(db_prefix().'customer_groups cg', 'cg.customer_id = i.clientid', 'inner')
    ->where_in('cg.groupid', $branch_id)
    ->where_not_in('itm.description', $exclude_desc)
    ->group_by('i.clientid')
    ->having('COUNT(DISTINCT i.id) >', 1)
    ->get_compiled_select();

/**
 * Step 2: Latest invoice per client in date range
 */
$CI->db->reset_query();
$latest_due_per_client = $CI->db
    ->select('i.clientid, MAX(i.duedate) AS latest_due', false)
    ->from(db_prefix().'invoices i')
    ->join(db_prefix().'itemable itm', 'itm.rel_id = i.id AND itm.rel_type="invoice"', 'inner')
    ->join(db_prefix().'customer_groups cg', 'cg.customer_id = i.clientid', 'inner')
    ->where_in('cg.groupid', $branch_id)
    ->where('i.duedate >=', $from_date)
    ->where('i.duedate <=', $to_date)
    ->where_not_in('itm.description', $exclude_desc)
    ->group_by('i.clientid')
    ->get_compiled_select();

/**
 * Step 3: Pull the latest invoice rows for those clients
 */
$CI->db->reset_query();
$CI->db->select([
    'i.id AS invoiceid',
    'i.clientid',
    'i.date AS invoice_date',
    'i.duedate AS due_date',
    'i.total AS package_amount',
    'c.company AS patient',
    'nf.mr_no',
    '(SELECT IFNULL(SUM(amount),0) FROM '.db_prefix().'invoicepaymentrecords WHERE invoiceid=i.id) AS paid_amount',
    '(i.total - (SELECT IFNULL(SUM(amount),0) FROM '.db_prefix().'invoicepaymentrecords WHERE invoiceid=i.id)) AS due_amount'
], false);
$CI->db->from(db_prefix().'invoices i');
$CI->db->join("($latest_due_per_client) lir", 'lir.clientid=i.clientid AND lir.latest_due=i.duedate', 'inner');
$CI->db->join("($clients_with_2plus) rc", 'rc.clientid=i.clientid', 'inner');
$CI->db->join(db_prefix().'itemable itm', 'itm.rel_id=i.id AND itm.rel_type="invoice"', 'inner');
$CI->db->join(db_prefix().'clients c', 'c.userid=i.clientid', 'inner');
$CI->db->join(db_prefix().'clients_new_fields nf', 'nf.userid=i.clientid', 'left');
$CI->db->where_not_in('itm.description', $exclude_desc);
$CI->db->where("(nf.mr_no IS NOT NULL AND nf.mr_no != '')", null, false);

// TYPE filter
switch ($type) {
    case 'active':
		$CI->db->where("i.date <= (
			SELECT MAX(prev.duedate)
			FROM ".db_prefix()."invoices prev
			JOIN ".db_prefix()."itemable it2 
			  ON it2.rel_id=prev.id AND it2.rel_type='invoice'
			WHERE prev.clientid=i.clientid
			  AND prev.date < i.date
			  AND it2.description NOT IN ('Consultation Fee','Consulation Fee')
		)", null, false);
		break;
    case 'inactive':
        $CI->db->where("i.date > (
            SELECT MAX(prev.duedate)
            FROM ".db_prefix()."invoices prev
            JOIN ".db_prefix()."itemable it2 
              ON it2.rel_id=prev.id AND it2.rel_type='invoice'
            WHERE prev.clientid=i.clientid
              AND prev.date < i.date
              AND it2.description NOT IN ('Consultation Fee','Consulation Fee')
        )", null, false);
        break;
    case 'acute':
        $CI->db->where("DATEDIFF(i.duedate,i.date) < 45", null, false);
        break;
    case 'total':
    default:
        break;
}

// Search filter
if (!empty($search_value)) {
    $CI->db->group_start();
    $CI->db->like('c.company', $search_value);
    $CI->db->or_like('nf.mr_no', $search_value);
    $CI->db->group_end();
}

// Count before pagination
$totalQuery = clone $CI->db;
$total_count = $totalQuery->count_all_results('', false);

// Pagination
$CI->db->order_by('i.date', 'DESC');
$CI->db->limit($length, $start);
$rows = $CI->db->get()->result_array();

// DataTable response
$output = [
    'draw' => $draw,
    'recordsTotal' => $total_count,
    'recordsFiltered' => $total_count,
    'aaData' => [],
];

foreach ($rows as $row) {
    $output['aaData'][] = [
        $row['patient'],
        $row['mr_no'],
        app_format_money($row['package_amount'], get_base_currency()),
        app_format_money($row['paid_amount'], get_base_currency()),
        app_format_money($row['due_amount'], get_base_currency()),
    ];
}

echo json_encode($output);
exit;
