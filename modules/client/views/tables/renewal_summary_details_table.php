<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

// --- DataTables request variables
$draw   = intval($CI->input->post('draw') ?? 1);
$search_value = $CI->input->post('search')['value'] ?? '';
$start  = intval($CI->input->post('start') ?? 0);
$length = intval($CI->input->post('length') ?? 10);

// --- Inputs
$from_date  = $consulted_from_date;
$to_date    = $consulted_to_date;
$branch_id  = $selected_branch_id[0];
$type       = $category; // total_renewals | active_renewals | inactive_renewals

$count = 0;
$client_ids = [];

// --- Step 1: Get customer IDs for the branch
$customer_ids = array_column(
    $CI->db->select('customer_id')
        ->where('groupid', $branch_id)
        ->get(db_prefix() . 'customer_groups')
        ->result_array(),
    'customer_id'
);

if (!empty($customer_ids)) {
	
	if($type === 'expected_active_renewals'){
		$today = date('Y-m-d');
			$expected_active_query = $CI->db->select('DISTINCT inv.clientid', false)
			->from(db_prefix() . 'invoices as inv')
			->join(db_prefix() . 'itemable as item', 'item.rel_id = inv.id AND item.rel_type = "invoice"')
			->where('inv.duedate >=', $from_date)
			->where('inv.duedate <=', $to_date)
			->where('inv.duedate >=', $today) // exclude future duedates
			->where('item.description !=', 'Consultation Fee')
			->where_in('inv.clientid', $customer_ids)
			->where("NOT EXISTS (
				SELECT 1 FROM " . db_prefix() . "invoices f_inv
				WHERE f_inv.clientid = inv.clientid
				AND f_inv.duedate > '{$to_date}'
			)", null, false)
			->get();

		$client_ids   = array_column($expected_active_query->result_array(), 'clientid');
		$expected_active_renewals  = count($client_ids);
		
	}else{
		
		// --- Step 2: Previous invoices
		$subquery_prev_inv = $CI->db->select('inv_prev.clientid, MAX(inv_prev.duedate) AS previous_duedate')
			->from(db_prefix() . 'invoices as inv_prev')
			->join(db_prefix() . 'itemable as item_prev', 'item_prev.rel_id = inv_prev.id AND item_prev.rel_type = "invoice"')
			->where('inv_prev.date <', $from_date)
			->where('item_prev.description !=', 'Consultation Fee')
			->where_in('inv_prev.clientid', $customer_ids)
			->group_by('inv_prev.clientid')
			->get_compiled_select();

		// --- Step 3: Renewal summary
		$renewal_data_query = $CI->db->select('
			COUNT(inv.id) AS total_renewals,
			SUM(CASE WHEN inv.date <= T_prev.previous_duedate THEN 1 ELSE 0 END) AS active_renewals,
			SUM(CASE WHEN inv.date > T_prev.previous_duedate THEN 1 ELSE 0 END) AS inactive_renewals
		', FALSE)
			->from(db_prefix() . 'invoices as inv')
			->join('(' . $subquery_prev_inv . ') AS T_prev', 'T_prev.clientid = inv.clientid', FALSE)
			->join(db_prefix() . 'itemable as item', 'item.rel_id = inv.id AND item.rel_type = "invoice"')
			->where('inv.date >=', $from_date)
			->where('inv.date <=', $to_date)
			->where('item.description !=', 'Consultation Fee')
			->where_in('inv.clientid', $customer_ids)
			->get();

		$renewal_data = $renewal_data_query->row();

		// --- Step 4: Filter client IDs based on type
		$client_ids_query = $CI->db->select('DISTINCT inv.clientid', FALSE)
			->from(db_prefix() . 'invoices as inv')
			->join('(' . $subquery_prev_inv . ') AS T_prev', 'T_prev.clientid = inv.clientid', FALSE)
			->join(db_prefix() . 'itemable as item', 'item.rel_id = inv.id AND item.rel_type = "invoice"')
			->where('inv.date >=', $from_date)
			->where('inv.date <=', $to_date)
			->where('item.description !=', 'Consultation Fee')
			->where_in('inv.clientid', $customer_ids);

		if ($type === 'active_renewals') {
			$count = $renewal_data->active_renewals;
			$client_ids_query->where('inv.date <= T_prev.previous_duedate', NULL, FALSE);
		} elseif ($type === 'inactive_renewals') {
			$count = $renewal_data->inactive_renewals;
			$client_ids_query->where('inv.date > T_prev.previous_duedate', NULL, FALSE);
		} else {
			$count = $renewal_data->total_renewals;
		}

		$client_ids = array_column($client_ids_query->get()->result_array(), 'clientid');
		
	}
    
}

// --- Step 5: Fetch patient/appointment details
$CI->db->select([
    'patients.userid AS patient_userid',
    'patients.company',
    'new.mr_no',
    'new.registration_start_date',
    'enquiry_type.enquiry_type_name',
    'source.name AS lead_source',
    'inv.id as invoiceid',
    'inv.total as invoiceamount',
    'inv.date as invoicedate',
]);

$CI->db->from(db_prefix() . 'clients AS patients');
$CI->db->join(db_prefix() . 'clients_new_fields AS new', 'new.userid = patients.userid', 'left');
$CI->db->join(db_prefix() . 'enquiry_type AS enquiry_type', 'enquiry_type.enquiry_type_id = patients.enquiry_type_id', 'left');
$CI->db->join(db_prefix() . 'leads_sources AS source', 'source.id = new.patient_source_id', 'left');
$CI->db->join(db_prefix() . 'invoices AS inv', 'inv.clientid = patients.userid', 'left');
$CI->db->join(db_prefix() . 'itemable AS it', 'it.rel_id = inv.id AND it.rel_type = "invoice"', 'left');
$CI->db->join(db_prefix() . 'customer_groups AS cg', 'cg.customer_id = patients.userid', 'left');

$CI->db->where_in('patients.userid', $client_ids);
$CI->db->where("(new.mr_no IS NOT NULL AND new.mr_no != '')", null, false);
$CI->db->where('it.description !=', 'Consultation Fee');

if (!empty($selected_branch_id)) {
    $CI->db->where_in('cg.groupid', (array)$selected_branch_id);
}

if($type === 'expected_active_renewals'){
	if (!empty($consulted_from_date) && !empty($consulted_to_date)) {
		$today = date('Y-m-d');
		$CI->db->where('inv.duedate >=', $consulted_from_date);
		$CI->db->where('inv.duedate <=', $consulted_to_date);
		$CI->db->where('inv.duedate >=', $today);
	}
}else{
	if (!empty($consulted_from_date) && !empty($consulted_to_date)) {
		$CI->db->where('inv.date >=', $consulted_from_date);
		$CI->db->where('inv.date <=', $consulted_to_date);
	}
}
if (!empty($search_value)) {
    $CI->db->group_start();
    $CI->db->like('patients.company', $search_value);
    $CI->db->or_like('new.mr_no', $search_value);
    $CI->db->group_end();
}

// Count total records
$totalQuery = clone $CI->db;
$total_records = $totalQuery->count_all_results('', false);

// Apply pagination
$CI->db->limit($length, $start);
$CI->db->order_by('inv.date', 'DESC');

// Run final query
$clients = $CI->db->get()->result_array();

// --- Step 6: Format for DataTables
$data = [];
foreach ($clients as $client) {
    $userid = $client['patient_userid'];
    $invoiceid = $client['invoiceid'];

    // Current invoice data
    $inv_data = $CI->db->get_where(db_prefix() . 'invoices', ['id' => $invoiceid])->row();

    // Previous invoice (latest one before current invoice) for the same client, excluding Consultation Fee
    $prev_inv = $CI->db->select('inv.id, inv.duedate')
        ->from(db_prefix() . 'invoices AS inv')
        ->join(db_prefix() . 'itemable AS it', 'it.rel_id = inv.id AND it.rel_type="invoice"', 'inner')
        ->where('inv.clientid', $userid)
        ->where('inv.id !=', $invoiceid)
        ->where('it.description !=', 'Consultation Fee')
        ->where('inv.date <', $inv_data->date) // Only invoices before current invoice
        ->order_by('inv.date', 'DESC')
        ->limit(1)
        ->get()
        ->row();

    // Calculate delay days
    $delay_days = 0;
    if ($prev_inv) {
        $prev_duedate = new DateTime($prev_inv->duedate);
        $curr_date    = new DateTime($inv_data->date);
        $interval     = $prev_duedate->diff($curr_date);
        $delay_days   = $interval->days;
        if ($curr_date < $prev_duedate) {
            $delay_days = 0; // no delay if current invoice is before previous due date
        }
    }

    // Latest appointment info
    $latest_appointment = $CI->db->select([
        'a.appointment_id',
        'a.consulted_date',
        'a.created_at',
        'a.appointment_date',
        'a.visited_date',
        'treatment.description as treatment_name',
    ])
    ->from(db_prefix() . 'appointment AS a')
    ->join(db_prefix() . 'items AS treatment', 'treatment.id = a.treatment_id', 'left')
    ->where('a.userid', $userid)
    ->order_by('a.appointment_id', 'DESC')
    ->limit(1)
    ->get()
    ->row_array();

    // Total paid for current invoice
    $total_paid = $CI->db->select('IFNULL(SUM(amount), 0) as total_paid')
        ->from(db_prefix() . 'invoicepaymentrecords')
        ->where('invoiceid', $invoiceid)
        ->get()
        ->row()
        ->total_paid;

    // Generate URL
    $url = admin_url('client/get_patient_list/' . $userid .'/'.$from_date.'/'.$to_date.'/NULL/'.$branch_id.'/NULL/'.$type.'/NULL');
    $company = '<a target="_blank" href="' . $url . '" class="tw-font-medium">' . format_name($client['company']) . '</a>';

    // Duration in days for current invoice
    $invoice_date = new DateTime($inv_data->date);
    $due_date     = new DateTime($inv_data->duedate);
    $interval = $invoice_date->diff($due_date);
    $duration_in_days = $interval->days;

    // Prepare row
    $row = [];
    $row[] = $company;
    $row[] = $client['mr_no'];
    $row[] = $delay_days; // ✅ actual delay days from previous invoice
    $row[] = _d($inv_data->date);
    $row[] = _d($inv_data->duedate);
    $row[] = $duration_in_days;
    $row[] = e(app_format_money_custom($client['invoiceamount'], 1));
    $row[] = e(app_format_money_custom($total_paid, 1));
    $row[] = e(app_format_money_custom($client['invoiceamount'] - $total_paid, 1));

    $data[] = $row;
}


// --- Step 7: Output JSON
$output = [
    'draw' => $draw,
    'recordsTotal' => $total_records,
    'recordsFiltered' => $total_records,
    'data' => $data,
    'count' => $count,
    'client_ids' => $client_ids
];

echo json_encode($output);
exit;
