<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI =& get_instance();
$CI->load->database();

$search_value = $_POST['search']['value'] ?? '';
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$draw   = intval($_POST['draw'] ?? 1);
$sno = $start + 1; 

// Get date filters
$from_date = !empty($consulted_from_date) ? to_sql_date($consulted_from_date) : date('Y-m-01');
$to_date   = !empty($consulted_to_date) ? to_sql_date($consulted_to_date) : date('Y-m-d');

$from_date_start = $from_date . ' 00:00:00';
$to_date_end     = $to_date . ' 23:59:59';

$data = [];

if (!empty($search_value)) {
    $CI->db->like('name', $search_value);
}

$CI->db->limit($length, $start);
// Get all branches
$branches = $CI->db->get(db_prefix() . 'customers_groups')->result_array();

$totalBranches = $CI->db->count_all_results(db_prefix() . 'customers_groups');


foreach ($branches as $branch) {
    $branch_id = $branch['id'];
    $branch_name = $branch['name'];

    // 1. Get customer_ids in this branch
    $CI->db->select('customer_id');
    $CI->db->from(db_prefix() . 'customer_groups');
    $CI->db->where('groupid', $branch_id);
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


			$CI->db->group_by('i.id');

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
			$CI->db->group_by('i.id');

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

    $ref_due = $ref_package - $ref_paid;
	
    $walkin_due = $walkin_package - $walkin_paid;
	
    $renewal_due = $renewal_package - $renewal_paid;
	
	$other_due = $other_package - $other_paid;
	
	$cce_due = $cce_package - $cce_paid;

    $data[] = [
		$sno++,
		$branch_name,

		'<a href="' . admin_url("client/reports/branch_registration_detail_report/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/all") . '" target="_blank">' . $total_registrations .'</a>',

		'<a href="' . admin_url("client/reports/branch_registration_detail_report/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/reference") . '" target="_blank">' . count($ref_client_ids) . '</a>',

		number_format($ref_package, 2),
		number_format($ref_paid, 2),
		number_format($ref_due, 2),

		'<a href="' . admin_url("client/reports/branch_registration_detail_report/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/walkin") . '" target="_blank">' . count($walkin_ids) . '</a>',

		number_format($walkin_package, 2),
		number_format($walkin_paid, 2),
		number_format($walkin_due, 2),

		'<a href="' . admin_url("client/reports/branch_registration_detail_report/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/renewal") . '" target="_blank">' . count($renewal_ids) . '</a>',

		number_format($renewal_package, 2),
		number_format($renewal_paid, 2),
		number_format($renewal_due, 2),
		
		  // ✅ CCE
        '<a href="' . admin_url("client/reports/branch_registration_detail_report/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/cce") . '" target="_blank">' . count($cce_client_ids) . '</a>',
        number_format($cce_package, 2),
        number_format($cce_paid, 2),
        number_format($cce_due, 2),
		
		// ✅ new OTHER category
		'<a href="' . admin_url("client/reports/branch_registration_detail_report/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/other") . '" target="_blank">' . count($other_ids) . '</a>',
		number_format($other_package, 2),
		number_format($other_paid, 2),
		number_format($other_due, 2),
	];

}

// Output DataTables format

echo json_encode([
    'draw' => intval($_POST['draw'] ?? 1),
    'recordsTotal' => $CI->db->count_all(db_prefix() . 'customers_groups'),
    'recordsFiltered' => !empty($search_value)
        ? $CI->db->like('name', $search_value)->count_all_results(db_prefix() . 'customers_groups')
        : $CI->db->count_all(db_prefix() . 'customers_groups'),
    'data' => $data,
]);

exit;

