<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI =& get_instance();

// Handle DataTables request variables
$draw   = intval($CI->input->post('draw'));
$search_value = $CI->input->post('search')['value'] ?? '';

$output = [
    'draw' => $draw,
    'recordsTotal' => 0,
    'recordsFiltered' => 0,
    'data' => []
];

// Start building query for branch groups
$CI->db->from(db_prefix() . 'customers_groups cg');
if (!empty($search_value)) {
    $CI->db->like('cg.name', $search_value);
}
$results = $CI->db->get()->result_array();

$recordsTotal    = count($results);
$recordsFiltered = $recordsTotal;

$output['recordsTotal']    = $recordsTotal;
$output['recordsFiltered'] = $recordsFiltered;

// ---- Totals accumulators ----
$tot_current_gt = $tot_consult_fee = $tot_proj = 0;
$tot_visit_np = $tot_visit_ref = $tot_visit_total = 0;
$tot_reg_np = $tot_reg_ref = $tot_reg_total = 0;
$tot_np_gt = $tot_ref_gt = $tot_total_gt = 0;

// ---- Loop branches ----
foreach ($results as $row) {
    $branch_id   = $row['id'];
    $branch_name = $row['name'];

    // ---- Collect all customer IDs for this branch ----
    $CI->db->select('customer_id');
    $CI->db->from(db_prefix() . 'customer_groups');
    $CI->db->where('groupid', $branch_id);
    $customer_ids = array_column($CI->db->get()->result_array(), 'customer_id');

    // Initialize counters
    $current_gt = 0;
    $consult_fee = 0;
    $proj = 0;
    $visit_np = 0;
    $visit_ref = 0;
    $visit_total = 0;
    $reg_np = 0;
    $reg_ref = 0;
    $reg_total = 0;

    // Package totals
    $np_gt = 0;
    $ref_gt = 0;
    $total_gt = 0;

    if (!empty($customer_ids)) {
        // ---- Invoice IDs for this branch ----
        $CI->db->select('id, clientid');
        $CI->db->from(db_prefix() . 'invoices');
        $CI->db->where_in('clientid', $customer_ids);
        $invoices = $CI->db->get()->result_array();

        $invoice_ids = array_column($invoices, 'id');

        if (!empty($invoice_ids)) {
            // Progressive (GT) = invoice total
            $CI->db->select('SUM(total) as proj');
            $CI->db->from(db_prefix() . 'invoices');
            $CI->db->where_in('id', $invoice_ids);
            $CI->db->where('date >=', $consulted_from_date . ' 00:00:00');
            $CI->db->where('date <=', $consulted_to_date . ' 23:59:59');
            $proj = $CI->db->get()->row()->proj ?? 0;

            // Total Paid
            $CI->db->select('SUM(ip.amount) as total_paid');
            $CI->db->from(db_prefix() . 'invoicepaymentrecords ip');
            $CI->db->where_in('ip.invoiceid', $invoice_ids);
            $CI->db->where('date >=', $consulted_from_date . ' 00:00:00');
            $CI->db->where('date <=', $consulted_to_date . ' 23:59:59');
            $total_paid = $CI->db->get()->row()->total_paid ?? 0;

            // Consultation Fee
            $CI->db->select('SUM(ip.amount) as consult_paid');
            $CI->db->from(db_prefix() . 'invoicepaymentrecords ip');
            $CI->db->join(db_prefix() . 'itemable i', 'i.rel_id = ip.invoiceid AND i.rel_type = "invoice"', 'left');
            $CI->db->where_in('ip.invoiceid', $invoice_ids);
            $CI->db->where('i.description', 'Consultation Fee');
            $CI->db->where('date >=', $consulted_from_date . ' 00:00:00');
            $CI->db->where('date <=', $consulted_to_date . ' 23:59:59');
            $consult_paid = $CI->db->get()->row()->consult_paid ?? 0;

            $current_gt = $total_paid - $consult_paid;
            $consult_fee = $consult_paid;

            // --- Package Totals (NP.GT, Ref.GT, Total) ---
            $CI->db->select('SUM(total) as amount, c.userid, l.refer_id');
            $CI->db->from(db_prefix() . 'invoices inv');
            $CI->db->join(db_prefix() . 'clients c', 'c.userid = inv.clientid', 'left');
            $CI->db->join(db_prefix() . 'leads l', 'l.id = c.leadid', 'left');
			$CI->db->join(db_prefix() . 'itemable i', 'i.rel_id = inv.id AND i.rel_type = "invoice"', 'left');
            $CI->db->where_in('inv.id', $invoice_ids);
			 $CI->db->where('i.description !=', 'Consultation Fee');
            $CI->db->where('inv.date >=', $consulted_from_date . ' 00:00:00');
            $CI->db->where('inv.date <=', $consulted_to_date . ' 23:59:59');
            $packages = $CI->db->get()->result_array();

            foreach ($packages as $pkg) {
                if ($pkg['refer_id'] > 0) {
                    $ref_gt += $pkg['amount'];
                } else {
                    $np_gt += $pkg['amount'];
                }
                $total_gt += $pkg['amount'];
            }
        }

        // ---- Unit Visits ----
        $CI->db->select('COUNT(*) as visit_count');
        $CI->db->from(db_prefix() . 'appointment');
        //$CI->db->where_in('userid', $customer_ids);
		$CI->db->join(db_prefix() . 'clients_new_fields nf', 'nf.userid = appointment.userid', 'left');
        $CI->db->where('visit_status', 1);
        $CI->db->where('branch_id', $branch_id);
        $CI->db->where('registration_start_date >=', $consulted_from_date . ' 00:00:00');
        $CI->db->where('registration_start_date <=', $consulted_to_date . ' 23:59:59');
		
        $visit_total = $CI->db->get()->row()->visit_count ?? 0;

        // REF visits
        $CI->db->select('c.userid');
        $CI->db->from(db_prefix() . 'leads l');
        $CI->db->join(db_prefix() . 'clients c', 'c.leadid = l.id');
        $CI->db->where('refer_id >', 0);
        $ref_client_ids = array_column($CI->db->get()->result_array(), 'userid');

        if (!empty($ref_client_ids)) {
            $CI->db->select('COUNT(*) as visit_count');
            $CI->db->from(db_prefix() . 'appointment');
            $CI->db->where_in('userid', $ref_client_ids);
            $CI->db->where('visit_status', 1);
			$CI->db->where('branch_id', $branch_id);
            $CI->db->where('appointment_date >=', $consulted_from_date . ' 00:00:00');
            $CI->db->where('appointment_date <=', $consulted_to_date . ' 23:59:59');
            $visit_ref = $CI->db->get()->row()->visit_count ?? 0;
        }

        $visit_np = $visit_total - $visit_ref;

        // ---- Registrations ----
		$CI->db->select('COUNT(DISTINCT nf.userid) as reg_count');
		$CI->db->from(db_prefix() . 'clients_new_fields nf');
		$CI->db->join(db_prefix() . 'clients c', 'c.userid = nf.userid', 'left');
		$CI->db->join(db_prefix() . 'leads l', 'l.id = c.leadid', 'left');

		// Join invoices (exclude consultation fee invoices)
		$CI->db->join(
			'(SELECT inv.clientid 
			  FROM ' . db_prefix() . 'invoices inv
			  LEFT JOIN ' . db_prefix() . 'itemable it ON it.rel_id = inv.id AND it.rel_type = "invoice"
			  WHERE it.description != "Consultation Fee"
			  GROUP BY inv.clientid
			  HAVING COUNT(inv.id) = 1
			) as inv_filter',
			'inv_filter.clientid = nf.userid',
			'inner'
		);

		$CI->db->where_in('nf.userid', $customer_ids);
		$CI->db->where('(nf.mr_no IS NOT NULL AND nf.mr_no != "")', null, false);
		$CI->db->where('nf.registration_start_date >=', $consulted_from_date);
		$CI->db->where('nf.registration_start_date <=', $consulted_to_date);

		$reg_np = $CI->db->get()->row()->reg_count ?? 0;


		// --- Registrations without reference (refer_id = 0 or NULL) ---
		/* $CI->db->select('COUNT(*) as reg_np');
		$CI->db->from(db_prefix() . 'clients_new_fields nf');
		$CI->db->join(db_prefix() . 'clients c', 'c.userid = nf.userid', 'left');
		$CI->db->join(db_prefix() . 'leads l', 'l.id = c.leadid', 'left');
		$CI->db->where_in('nf.userid', $customer_ids);
		$CI->db->where('(nf.mr_no IS NOT NULL AND nf.mr_no != "")', null, false);
		$CI->db->where('nf.registration_start_date >=', $consulted_from_date . ' 00:00:00');
		$CI->db->where('nf.registration_start_date <=', $consulted_to_date . ' 23:59:59');
		$CI->db->where('(l.refer_id IS NULL OR l.refer_id = 0)', null, false);
		$reg_np = $CI->db->get()->row()->reg_np ?? 0; */

		// --- Registrations with reference (refer_id != 0) ---
		$CI->db->select('COUNT(*) as reg_ref');
		$CI->db->from(db_prefix() . 'clients_new_fields nf');
		$CI->db->join(db_prefix() . 'clients c', 'c.userid = nf.userid', 'left');
		$CI->db->join(db_prefix() . 'leads l', 'l.id = c.leadid', 'left');
		$CI->db->where_in('nf.userid', $customer_ids);
		$CI->db->where('(nf.mr_no IS NOT NULL AND nf.mr_no != "")', null, false);
		$CI->db->where('nf.registration_start_date >=', $consulted_from_date . ' 00:00:00');
		$CI->db->where('nf.registration_start_date <=', $consulted_to_date . ' 23:59:59');
		$CI->db->where('l.refer_id !=', 0);
		
		$reg_ref = $CI->db->get()->row()->reg_ref ?? 0;
		
		$reg_total = $reg_np + $reg_ref;

    }

    // Base URL params
    $base_url = admin_url("client/reports/branch_summary_details?");
    $id                = 'NULL';
    $appointment_type  = 'NULL';
    $doctor_id         = 'NULL';
    $category          = 'NULL';
    $lead_sourceIds    = 'NULL';

    $output['data'][] = [
		$branch_name,

		// Amounts → plain numbers
		number_format($current_gt + $consult_fee, 2),
		number_format($consult_fee, 2),
		number_format($proj, 2),

		// Visits → clickable
		'<a style="color: blue" href="' . admin_url("client/reports/enquiry_gt_detail_report/{$id}/{$consulted_from_date}/{$consulted_to_date}/{$appointment_type}/{$branch_id}/{$doctor_id}/visit_np/{$lead_sourceIds}") . '" target="_blank">' . $visit_np . '</a>',
		'<a style="color: blue" href="' . admin_url("client/reports/enquiry_gt_detail_report/{$id}/{$consulted_from_date}/{$consulted_to_date}/{$appointment_type}/{$branch_id}/{$doctor_id}/visit_ref/{$lead_sourceIds}") . '" target="_blank">' . $visit_ref . '</a>',
		'<a style="color: blue" href="' . admin_url("client/reports/enquiry_gt_detail_report/{$id}/{$consulted_from_date}/{$consulted_to_date}/{$appointment_type}/{$branch_id}/{$doctor_id}/visit_total/{$lead_sourceIds}") . '" target="_blank">' . $visit_total . '</a>',

		// Registrations → clickable
		'<a style="color: blue" href="' . admin_url("client/reports/enquiry_gt_detail_report/{$id}/{$consulted_from_date}/{$consulted_to_date}/{$appointment_type}/{$branch_id}/{$doctor_id}/reg_np/{$lead_sourceIds}") . '" target="_blank">' . $reg_np . '</a>',
		'<a style="color: blue" href="' . admin_url("client/reports/enquiry_gt_detail_report/{$id}/{$consulted_from_date}/{$consulted_to_date}/{$appointment_type}/{$branch_id}/{$doctor_id}/reg_ref/{$lead_sourceIds}") . '" target="_blank">' . $reg_ref . '</a>',
		'<a style="color: blue" href="' . admin_url("client/reports/enquiry_gt_detail_report/{$id}/{$consulted_from_date}/{$consulted_to_date}/{$appointment_type}/{$branch_id}/{$doctor_id}/reg_total/{$lead_sourceIds}") . '" target="_blank">' . $reg_total . '</a>',

		// Amounts → plain numbers
		number_format($np_gt, 2),
		number_format($ref_gt, 2),
		number_format($total_gt, 2),
	];


    // Accumulate totals
    $tot_current_gt  += ($current_gt + $consult_fee);
    $tot_consult_fee += $consult_fee;
    $tot_proj        += $proj;
    $tot_visit_np    += $visit_np;
    $tot_visit_ref   += $visit_ref;
    $tot_visit_total += $visit_total;
    $tot_reg_np      += $reg_np;
    $tot_reg_ref     += $reg_ref;
    $tot_reg_total   += $reg_total;
    $tot_np_gt       += $np_gt;
    $tot_ref_gt      += $ref_gt;
    $tot_total_gt    += $total_gt;
}

// ---- Append totals row ----
$output['data'][] = [
    '<b>Totals</b>',
    '<b>'.number_format($tot_current_gt,2).'</b>',
    '<b>'.number_format($tot_consult_fee,2).'</b>',
    '<b>'.number_format($tot_proj,2).'</b>',
    '<b>'.$tot_visit_np.'</b>',
    '<b>'.$tot_visit_ref.'</b>',
    '<b>'.$tot_visit_total.'</b>',
    '<b>'.$tot_reg_np.'</b>',
    '<b>'.$tot_reg_ref.'</b>',
    '<b>'.$tot_reg_total.'</b>',
    '<b>'.number_format($tot_np_gt,2).'</b>',
    '<b>'.number_format($tot_ref_gt,2).'</b>',
    '<b>'.number_format($tot_total_gt,2).'</b>',
];

// Return JSON
header('Content-Type: application/json');
echo json_encode($output);
exit;
