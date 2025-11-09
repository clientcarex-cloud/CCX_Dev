<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('client_model');

$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search_value = $_POST['search']['value'] ?? '';
$from_date = $consulted_from_date;
$to_date = $consulted_to_date;
$branch_id = $selected_branch_id;

//$category = $_GET['category'] ?? 'all';
$type     = $_GET['type'] ?? 'branch_registration_detail_report';

// 1. Get customer_ids in this branch
    $CI->db->select('customer_id');
    $CI->db->from(db_prefix() . 'customer_groups');
    $CI->db->where_in('groupid', $branch_id);
    $customer_ids = array_column($CI->db->get()->result_array(), 'customer_id');


	$total_registrations = 0;

	// Refer clients
	$ref_client_ids = [];
	$ref_package = 0;
	$ref_paid = 0;
	$ref_due = 0;

	// Walk-in clients
	$walkin_client_ids = [];
	$walkin_ids = [];
	$walkin_package = 0;
	$walkin_paid = 0;
	$walkin_due = 0;

	// Renewal clients
	$renewal_client_ids = [];
	$renewal_ids = [];
	$renewal_package = 0;
	$renewal_paid = 0;
	$renewal_due = 0;
	
	// CCE clients
	$cce_client_ids = [];
	$cce_ids = [];
	$cce_package = 0;
	$cce_paid = 0;
	$cce_due = 0;

    if (!empty($customer_ids)) {

        // 2. Get total registrations (from client new fields)
        $CI->db->select('c.userid');
		$CI->db->from(db_prefix() . 'clients_new_fields as c');
		$CI->db->join(db_prefix() . 'invoices inv', 'inv.clientid = c.userid', 'left');
		$CI->db->join(db_prefix() . 'itemable ia', 'ia.rel_id = inv.id AND ia.rel_type = "invoice"', 'left');
		$CI->db->where_in('c.userid', $customer_ids);
		$CI->db->where("(c.mr_no IS NOT NULL AND c.mr_no != '')", null, false);
		$CI->db->where('inv.date >=', $from_date);
		$CI->db->where('inv.date <=', $to_date);
		$CI->db->where('ia.description !=', 'Consultation Fee');
		
		$registered_clients = array_column($CI->db->get()->result_array(), 'userid');
		$total_registrations = count($registered_clients);
		
		// Build subquery first
		$subquery = $CI->db->select('i.clientid')
			->from(db_prefix() . 'invoices AS i')
			->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner')
			->where('ia.description !=', 'Consultation Fee')
			->group_by('i.clientid')
			->having('COUNT(DISTINCT i.id) = 1')
			->get_compiled_select();

		// Now build the outer query
		$CI->db->select('c.userid');
		$CI->db->from(db_prefix() . 'leads AS l');
		$CI->db->join(db_prefix() . 'clients AS c', 'c.leadid = l.id');
		$CI->db->join(db_prefix() . 'clients_new_fields AS cn', 'cn.userid = c.userid');
		$CI->db->where('l.refer_id >', 0);
		$CI->db->where("(cn.mr_no IS NOT NULL AND cn.mr_no != '')", null, false);
		$CI->db->where("c.userid IN ($subquery)", null, false); // use as raw WHERE IN subquery

		$ref_client_ids = array_column($CI->db->get()->result_array(), 'userid');

        if (!empty($ref_client_ids)) {

			// 4. Get invoice total for ref clients WITH itemable.description != 'Consultation Fee'
			$CI->db->select('SUM(i.total) as total');
			$CI->db->from(db_prefix() . 'invoices AS i');
			$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
			$CI->db->where_in('i.clientid', $ref_client_ids);
			$CI->db->where('i.date >=', $from_date);
			$CI->db->where('i.date <=', $to_date);
			$CI->db->where('ia.description !=', 'Consultation Fee');
			$CI->db->group_by('i.id'); // prevent duplicate sums due to multiple itemables
			$rows = $CI->db->get()->result_array();
			$ref_package = array_sum(array_column($rows, 'total'));

			// 5. Get paid total for those invoices (with itemable.description != 'Consultation Fee')
			$CI->db->select('SUM(ip.amount) as paid');
			$CI->db->from(db_prefix() . 'invoicepaymentrecords AS ip');
			$CI->db->join(db_prefix() . 'invoices AS i', 'i.id = ip.invoiceid', 'left');
			$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
			$CI->db->where_in('i.clientid', $ref_client_ids);
			$CI->db->where('ip.date >=', $from_date);
			$CI->db->where('ip.date <=', $to_date);
			$CI->db->where('ia.description !=', 'Consultation Fee');
			$CI->db->group_by('i.id');
			$rows = $CI->db->get()->result_array();
			$ref_paid = array_sum(array_column($rows, 'paid'));
		}

		
		// ✅ Step 4: Calculate walk-in totals from `registered_clients` excluding `ref_client_ids`
		$walkin_client_ids = array_diff($registered_clients, $ref_client_ids);

		// Step 5: Get walk-in clients whose source is 'Walkin'
		$CI->db->select('userid');
		$CI->db->from(db_prefix() . 'clients_new_fields AS nf');
		$CI->db->join(db_prefix() . 'leads_sources AS src', 'src.id = nf.patient_source_id', 'left');
		$CI->db->where('src.name', 'Walkin');
		$CI->db->where("(nf.mr_no IS NOT NULL AND nf.mr_no != '')", null, false);
		if (!empty($walkin_client_ids)) {
			$CI->db->where_in('nf.userid', $walkin_client_ids);
		} else {
			$CI->db->where('1 = 0'); // force empty result
		}
		$walkin_ids = array_column($CI->db->get()->result_array(), 'userid');

		// Step 6: Do billing/paid calc for walk-ins
		if (!empty($walkin_ids)) {
			// 1. Get total invoice amount excluding 'Consultation Fee'
			$CI->db->select('SUM(i.total) as total');
			$CI->db->from(db_prefix() . 'invoices AS i');
			$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
			$CI->db->where_in('i.clientid', $walkin_ids);
			$CI->db->where('i.date >=', $from_date);
			$CI->db->where('i.date <=', $to_date);
			$CI->db->where('ia.description !=', 'Consultation Fee');
			$CI->db->group_by('i.id');
			$rows = $CI->db->get()->result_array();
			$walkin_package = array_sum(array_column($rows, 'total'));

			// 2. Get total paid amount excluding 'Consultation Fee'
			$CI->db->select('SUM(ip.amount) as paid');
			$CI->db->from(db_prefix() . 'invoicepaymentrecords AS ip');
			$CI->db->join(db_prefix() . 'invoices AS i', 'i.id = ip.invoiceid', 'left');
			$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
			$CI->db->where_in('i.clientid', $walkin_ids);
			$CI->db->where('ip.date >=', $from_date);
			$CI->db->where('ip.date <=', $to_date);
			$CI->db->where('ia.description !=', 'Consultation Fee');
			$CI->db->group_by('i.id');
			$rows = $CI->db->get()->result_array();
			$walkin_paid = array_sum(array_column($rows, 'paid'));
		}
		
		
		// ✅ Step 7: Get renewal client IDs
		$excluded_ids = array_merge($ref_client_ids, $walkin_ids);
		$renewal_client_ids = array_diff($registered_clients, $excluded_ids);
		
		// Step 8: Get renewal clients who have more than 1 invoice excluding 'Consultation Fee'
		$renewal_ids = [];
		
		if (!empty($renewal_client_ids)) {
			$today = date('Y-m-d');

			$CI->db->select('i.clientid');
			$CI->db->from(db_prefix() . 'invoices AS i');
			$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
			$CI->db->where_in('i.clientid', $renewal_client_ids);
			$CI->db->where('i.date >=', $from_date);
			$CI->db->where('i.date <=', $to_date);
			$CI->db->where('ia.description !=', 'Consultation Fee');

			// Ensure overall invoice count > 1
			$CI->db->where("(
				SELECT COUNT(DISTINCT inv.id)
				FROM " . db_prefix() . "invoices AS inv
				INNER JOIN " . db_prefix() . "itemable AS ia2 
					ON ia2.rel_id = inv.id 
					AND ia2.rel_type = 'invoice'
				WHERE inv.clientid = i.clientid
				  AND ia2.description != 'Consultation Fee'
			) >", 1);


			$CI->db->group_by('i.clientid');

			$renewal_ids = array_column($CI->db->get()->result_array(), 'clientid');
		}

		// Step 9: Do billing/paid calc for renewal clients
		if (!empty($renewal_ids)) {
			// 1. Get total invoice amount
			$CI->db->select('SUM(i.total) as total');
			$CI->db->from(db_prefix() . 'invoices AS i');
			$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
			$CI->db->where_in('i.clientid', $renewal_ids);
			$CI->db->where('i.date >=', $from_date);
			$CI->db->where('i.date <=', $to_date);
			$CI->db->where('ia.description !=', 'Consultation Fee');
			$CI->db->group_by('i.id');
			$rows = $CI->db->get()->result_array();
			$renewal_package = array_sum(array_column($rows, 'total'));

			// 2. Get total paid amount
			$CI->db->select('SUM(ip.amount) as paid');
			$CI->db->from(db_prefix() . 'invoicepaymentrecords AS ip');
			$CI->db->join(db_prefix() . 'invoices AS i', 'i.id = ip.invoiceid', 'left');
			$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
			$CI->db->where_in('i.clientid', $renewal_ids);
			$CI->db->where('ip.date >=', $from_date);
			$CI->db->where('ip.date <=', $to_date);
			$CI->db->where('ia.description !=', 'Consultation Fee');
			$CI->db->group_by('i.id');
			$rows = $CI->db->get()->result_array();
			$renewal_paid = array_sum(array_column($rows, 'paid'));
		}
		
		// ✅ Step 10: CCE category
		$cce_client_ids = [];
		$cce_ids = [];
		$cce_package = 0;
		$cce_paid = 0;
		$cce_due = 0;

		$excluded_ids = array_merge($ref_client_ids, $walkin_ids, $renewal_ids);
		$cce_candidates = array_diff($registered_clients, $excluded_ids);

		if (!empty($cce_candidates)) {
			$CI->db->select('c.userid');
			$CI->db->from(db_prefix() . 'clients AS c');
			$CI->db->join(db_prefix() . 'leads AS l', 'l.id = c.leadid', 'left');
			$CI->db->join(db_prefix() . 'clients_new_fields AS cn', 'cn.userid = c.userid', 'left');
			$CI->db->where_in('c.userid', $registered_clients);
			$CI->db->where('(l.refer_id IS NULL OR l.refer_id = 0)'); // reference empty
			$CI->db->where('(c.leadid IS NOT NULL OR c.leadid != 0)'); // reference empty
			$CI->db->where("(cn.mr_no IS NOT NULL AND cn.mr_no != '')", null, false);
			$cce_client_ids = array_column($CI->db->get()->result_array(), 'userid');

			if (!empty($cce_client_ids)) {
				// Package total
				$CI->db->select('SUM(i.total) as total');
				$CI->db->from(db_prefix() . 'invoices AS i');
				$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
				$CI->db->where_in('i.clientid', $cce_client_ids);
				$CI->db->where('i.date >=', $from_date);
				$CI->db->where('i.date <=', $to_date);
				$CI->db->where('ia.description !=', 'Consultation Fee');
				$CI->db->group_by('i.id');
				$rows = $CI->db->get()->result_array();
				$cce_package = array_sum(array_column($rows, 'total'));

				// Paid total
				$CI->db->select('SUM(ip.amount) as paid');
				$CI->db->from(db_prefix() . 'invoicepaymentrecords AS ip');
				$CI->db->join(db_prefix() . 'invoices AS i', 'i.id = ip.invoiceid', 'left');
				$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
				$CI->db->where_in('i.clientid', $cce_client_ids);
				$CI->db->where('ip.date >=', $from_date);
				$CI->db->where('ip.date <=', $to_date);
				$CI->db->where('ia.description !=', 'Consultation Fee');
				$CI->db->group_by('i.id');
				$rows = $CI->db->get()->result_array();
				$cce_paid = array_sum(array_column($rows, 'paid'));
			}
		}

		
		// ✅ Step 10: OTHER category (clients not in ref, walkin, renewal, or cce)
		$all_matched_ids = array_merge($ref_client_ids, $walkin_ids, $renewal_ids, $cce_client_ids);
		$other_client_ids = array_diff($registered_clients, $all_matched_ids);


		$other_ids = [];
		$other_package = 0;
		$other_paid = 0;
		$other_due = 0;

		if (!empty($other_client_ids)) {
			// Get all OTHER client IDs with invoices excluding 'Consultation Fee'
			$CI->db->select('i.clientid');
			$CI->db->from(db_prefix() . 'invoices AS i');
			$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
			$CI->db->where_in('i.clientid', $other_client_ids);
			$CI->db->where('i.date >=', $from_date);
			$CI->db->where('i.date <=', $to_date);
			$CI->db->where('ia.description !=', 'Consultation Fee');
			$CI->db->group_by('i.clientid');

			$other_ids = array_column($CI->db->get()->result_array(), 'clientid');

			if (!empty($other_ids)) {
				// Package total
				$CI->db->select('SUM(i.total) as total');
				$CI->db->from(db_prefix() . 'invoices AS i');
				$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
				$CI->db->where_in('i.clientid', $other_ids);
				$CI->db->where('i.date >=', $from_date);
				$CI->db->where('i.date <=', $to_date);
				$CI->db->where('ia.description !=', 'Consultation Fee');
				$CI->db->group_by('i.id');
				$rows = $CI->db->get()->result_array();
				$other_package = array_sum(array_column($rows, 'total'));

				// Paid total
				$CI->db->select('SUM(ip.amount) as paid');
				$CI->db->from(db_prefix() . 'invoicepaymentrecords AS ip');
				$CI->db->join(db_prefix() . 'invoices AS i', 'i.id = ip.invoiceid', 'left');
				$CI->db->join(db_prefix() . 'itemable AS ia', 'ia.rel_id = i.id AND ia.rel_type = "invoice"', 'inner');
				$CI->db->where_in('i.clientid', $other_ids);
				$CI->db->where('ip.date >=', $from_date);
				$CI->db->where('ip.date <=', $to_date);
				$CI->db->where('ia.description !=', 'Consultation Fee');
				$CI->db->group_by('i.id');
				$rows = $CI->db->get()->result_array();
				$other_paid = array_sum(array_column($rows, 'paid'));
			}
		}

    }
// Decide which clients to show based on category
switch ($category) {
    case 'ref':
        $filtered_ids = $ref_client_ids;
        break;
    case 'walkin':
        $filtered_ids = $walkin_ids;
        break;
    case 'renewal':
        $filtered_ids = $renewal_ids;
        break;
    case 'cce':
        $filtered_ids = $cce_client_ids;
        break;
    case 'other':
        $filtered_ids = $other_ids;
        break;
    case 'all':
    default:
        $filtered_ids = $registered_clients;
        break;
}

$search_value = $_POST['search']['value'] ?? '';
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$draw   = intval($_POST['draw'] ?? 1);

// If no IDs found → empty output
if (empty($filtered_ids)) {
    $clients = [];
} else {
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
		'inv.duedate as invoiceduedate',
	]);

	$CI->db->from(db_prefix() . 'clients AS patients');
	$CI->db->join(db_prefix() . 'clients_new_fields AS new', 'new.userid = patients.userid', 'left');
	$CI->db->join(db_prefix() . 'enquiry_type AS enquiry_type', 'enquiry_type.enquiry_type_id = patients.enquiry_type_id', 'left');
	$CI->db->join(db_prefix() . 'leads_sources AS source', 'source.id = new.patient_source_id', 'left');
	$CI->db->join(db_prefix() . 'leads AS leads', 'leads.id = patients.leadid', 'left');
	$CI->db->join(db_prefix() . 'invoices AS inv', 'inv.clientid = patients.userid', 'left');

	// ✅ only join itemable table for invoice items
	$CI->db->join(
		db_prefix() . 'itemable AS it',
		'it.rel_id = inv.id AND it.rel_type = "invoice"',
		'left'
	);

	$CI->db->join(db_prefix() . 'customer_groups AS cg', 'cg.customer_id = patients.userid', 'left');

	// ✅ invoice date filter
	if (!empty($consulted_from_date) && !empty($consulted_to_date)) {
		$CI->db->where('inv.date >=', $consulted_from_date);
		$CI->db->where('inv.date <=', $consulted_to_date);
	}

	// ✅ ensure MR number exists
	$CI->db->where("(new.mr_no IS NOT NULL AND new.mr_no != '')", null, false);
	$CI->db->where_in('patients.userid', $filtered_ids);
	// ✅ filter by branch
	if (!empty($selected_branch_id)) {
		$CI->db->where_in('cg.groupid', (array)$selected_branch_id);
	}

	// ✅ exclude consultation fee
	$CI->db->where('it.description !=', 'Consultation fee');

	// ✅ avoid duplicate patient rows
	//$CI->db->group_by('patients.userid');

	if (!empty($search_value)) {
    $CI->db->group_start();
    $CI->db->like('patients.company', $search_value);
    $CI->db->or_like('new.mr_no', $search_value);
    $CI->db->group_end();
}

// Count total records
$totalQuery = clone $CI->db;
$total_count = $totalQuery->count_all_results('', false);

// Apply pagination
$CI->db->limit($length, $start);

// Run final query
$query = $CI->db->get();
$clients = $query->result_array();

}
$output = [
    'draw' => $draw,
    'recordsTotal' => $total_count,
    'recordsFiltered' => $total_count,
    'aaData' => [],
];
foreach ($clients as $client) {
    $userid = $client['patient_userid'];
    $invoiceid = $client['invoiceid'];
    $invoicedate = $client['invoicedate'];

		$latest_appointment = $CI->db->select([
			'a.appointment_id',
			'a.visit_id',
			'a.consulted_date',
			'a.created_at',
			'a.appointment_date',
			'a.visited_date',
			'a.visit_status',
			'staff.firstname',
			'staff.lastname',
			'appointment_type.appointment_type_name',
			'treatment.description as treatment_name',
			'cf.description as consultation_fee_name',
		])
		->from(db_prefix() . 'appointment AS a')
		->join(db_prefix() . 'staff AS staff', 'staff.staffid = a.enquiry_doctor_id', 'left')
		->join(db_prefix() . 'appointment_type AS appointment_type', 'appointment_type.appointment_type_id = a.appointment_type_id', 'left')
		->join(db_prefix() . 'items AS treatment', 'treatment.id = a.treatment_id', 'left')
		->join(db_prefix() . 'items AS cf', 'cf.id = a.consultation_fee_id', 'left')
		->where('a.userid', $userid)
		->group_start()
			->where('DATE(a.created_at)', $invoicedate)
			->or_where('DATE(a.appointment_date)', $invoicedate)
		->group_end()
		->order_by('a.appointment_id', 'DESC')
		->limit(1)
		->get()
		->row_array();

    
	$total_paid = $CI->db->select('IFNULL(SUM(amount), 0) as total_paid')
		->from(db_prefix() . 'invoicepaymentrecords')
		->where('invoiceid', $invoiceid) // pass the invoiceid from your invoice
		->get()
		->row()
		->total_paid;
    $row = [];
    $row[] = '<a href="' . admin_url('client/reports/' . $type . '/' . $userid) . '" target="_blank">' 
             . e(format_name($client['company'])) . '</a>';
    $row[] = $client['mr_no'];
    $row[] = $client['lead_source'] ?? $client['enquiry_type_name'] ?? '-';
    $row[] = $latest_appointment['treatment_name'] ?? '-';
    $row[] = _d($latest_appointment['created_at'] ?? '');
    $row[] = _d($latest_appointment['appointment_date'] ?? '');
    $row[] = _d($latest_appointment['visited_date'] ?? '');
    $row[] = _d($latest_appointment['consulted_date'] ?? '');
    $row[] = _d($client['registration_start_date'] ?? '');
    $row[] = e(app_format_money_custom($client['invoiceamount'], 1));
    $row[] = e(app_format_money_custom($total_paid, 1));
    $row[] = e(app_format_money_custom($client['invoiceamount'] - $total_paid, 1));

    $output['aaData'][] = $row;
}
echo json_encode($output);
exit;
