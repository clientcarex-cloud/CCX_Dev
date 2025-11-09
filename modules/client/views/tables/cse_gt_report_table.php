<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI =& get_instance();

// Handle DataTables request variables
$draw   = intval($CI->input->post('draw'));
$start  = $CI->input->post('start');
$length = $CI->input->post('length');
$search_value = $CI->input->post('search')['value'] ?? '';

// Safe fallback defaults
$start  = is_numeric($start) ? (int) $start : 0;
$length = is_numeric($length) ? (int) $length : 10;

$output = [
    'draw' => $draw,
    'recordsTotal' => 0,
    'recordsFiltered' => 0,
    'data' => []
];

// Start building query
$CI->db->start_cache();
$CI->db->from(db_prefix() . 'customers_groups');

if (!empty($search_value)) {
    $CI->db->like('name', $search_value);
}
$CI->db->stop_cache();

// Count filtered results
$recordsFiltered = $CI->db->count_all_results();

// Apply pagination
$CI->db->limit($length, $start);
$query = $CI->db->get();
$results = $query->result_array();

// Count total records (without filtering)
$CI->db->flush_cache();
$CI->db->from(db_prefix() . 'customers_groups');
$recordsTotal = $CI->db->count_all_results();

// Build output
$output['recordsTotal'] = $recordsTotal;
$output['recordsFiltered'] = $recordsFiltered;
foreach ($results as $row) {
    $branch_id = $row['id'];
    $branch_name = $row['name'];

    // Get all customer IDs for this branch
    $CI->db->select('customer_id');
    $CI->db->from(db_prefix() . 'customer_groups');
    $CI->db->where('groupid', $branch_id);
    $customer_ids = array_column($CI->db->get()->result_array(), 'customer_id');

    $current_gt = 0;
    $consult_fee = 0;
    $proj = 0;
    $unit_visit_count = 0;
    $ref_visit_count = 0;
    $reg_count = 0;
	
	$unit_reg_gt = 0;
	$unit_gt_gt = 0;
    $unit_gt_consult = 0;
    $unit_gt_count = 0;

    if (!empty($customer_ids)) {
        // Get all invoice IDs for this branch
        $CI->db->select('id');
        $CI->db->from(db_prefix() . 'invoices');
        $CI->db->where_in('clientid', $customer_ids);
        $invoice_ids = array_column($CI->db->get()->result_array(), 'id');

        // Total Invoice Amount (GT)
        if (!empty($invoice_ids)) {
            $CI->db->select('SUM(total) as proj');
            $CI->db->from(db_prefix() . 'invoices');
            $CI->db->where_in('id', $invoice_ids);
            $CI->db->where('date >=', $consulted_from_date . ' 00:00:00');
            $CI->db->where('date <=', $consulted_to_date . ' 23:59:59');
            $proj = $CI->db->get()->row()->proj ?? 0;

            // Total Paid
            $CI->db->select('SUM(ip.amount) as total_paid');
            $CI->db->from(db_prefix() . 'invoicepaymentrecords as ip');
            $CI->db->where_in('ip.invoiceid', $invoice_ids);
            $CI->db->where('date >=', $consulted_from_date . ' 00:00:00');
            $CI->db->where('date <=', $consulted_to_date . ' 23:59:59');
            $total_paid = $CI->db->get()->row()->total_paid ?? 0;

            // Consultation Fee
            $CI->db->select('SUM(ip.amount) as consult_paid');
            $CI->db->from(db_prefix() . 'invoicepaymentrecords as ip');
            $CI->db->join(db_prefix() . 'itemable as i', 'i.rel_id = ip.invoiceid AND i.rel_type = "invoice"', 'left');
            $CI->db->where_in('ip.invoiceid', $invoice_ids);
            $CI->db->where('i.description', 'Consultation Fee');
            $CI->db->where('date >=', $consulted_from_date . ' 00:00:00');
            $CI->db->where('date <=', $consulted_to_date . ' 23:59:59');
            $consult_paid = $CI->db->get()->row()->consult_paid ?? 0;

            $current_gt = $total_paid - $consult_paid;
            $consult_fee = $consult_paid;
        }

        // Unit Visit Count
        $CI->db->select('COUNT(*) as visit_count');
        $CI->db->from(db_prefix() . 'appointment');
        $CI->db->where_in('userid', $customer_ids);
        $CI->db->where('visit_status', 1);
        $CI->db->where('appointment_date >=', $consulted_from_date . ' 00:00:00');
        $CI->db->where('appointment_date <=', $consulted_to_date . ' 23:59:59');
        $unit_visit_count = $CI->db->get()->row()->visit_count ?? 0;

        // Referral Visit Count
        $CI->db->select('c.userid');
        $CI->db->from(db_prefix() . 'leads as l');
        $CI->db->join(db_prefix() . 'clients as c', 'c.leadid = l.id');
        $CI->db->where('refer_id >', 0);
        $other_referred_client_ids = array_column($CI->db->get()->result_array(), 'userid');

        if (!empty($other_referred_client_ids)) {
            $CI->db->select('COUNT(*) as visit_count');
            $CI->db->from(db_prefix() . 'appointment');
            $CI->db->where_in('userid', $other_referred_client_ids);
            $CI->db->where('visit_status', 1);
            $CI->db->where('appointment_date >=', $consulted_from_date . ' 00:00:00');
            $CI->db->where('appointment_date <=', $consulted_to_date . ' 23:59:59');
            $ref_visit_count = $CI->db->get()->row()->visit_count ?? 0;
        }

        // Registration Count
        $CI->db->select('COUNT(*) as reg_count');
        $CI->db->from(db_prefix() . 'clients_new_fields as nf');
        $CI->db->where_in('nf.userid', $customer_ids);
        $CI->db->where('nf.mr_no IS NOT NULL', null, false);
        $CI->db->where('nf.registration_start_date >=', $consulted_from_date . ' 00:00:00');
        $CI->db->where('nf.registration_start_date <=', $consulted_to_date . ' 23:59:59');
        $reg_count = $CI->db->get()->row()->reg_count ?? 0;
		
		$CI->db->select('SUM(ip.amount) as reg_gt');
        $CI->db->from(db_prefix() . 'invoicepaymentrecords as ip');
        $CI->db->join(db_prefix() . 'invoices inv', 'inv.id = ip.invoiceid', 'left');
        $CI->db->join(db_prefix() . 'clients_new_fields nf', 'nf.userid = inv.clientid', 'left');
        $CI->db->where_in('inv.clientid', $customer_ids);
        $CI->db->where('nf.mr_no IS NOT NULL', null, false);
        $CI->db->where('ip.date >=', $consulted_from_date . ' 00:00:00');
        $CI->db->where('ip.date <=', $consulted_to_date . ' 23:59:59');
        $unit_reg_gt = $CI->db->get()->row()->reg_gt ?? 0;
		
		 // GT (all payments excluding consultation)
        $CI->db->select('SUM(ip.amount) as gt_total');
        $CI->db->from(db_prefix() . 'invoicepaymentrecords as ip');
        $CI->db->join(db_prefix() . 'itemable i', 'i.rel_id = ip.invoiceid AND i.rel_type="invoice"', 'left');
        $CI->db->where_in('ip.invoiceid', $invoice_ids);
        $CI->db->where('i.description !=', 'Consultation Fee');
        $CI->db->where('ip.date >=', $consulted_from_date . ' 00:00:00');
        $CI->db->where('ip.date <=', $consulted_to_date . ' 23:59:59');
        $unit_gt_gt = $CI->db->get()->row()->gt_total ?? 0;

        // Consultation Fee
        $CI->db->select('SUM(ip.amount) as consult_total');
        $CI->db->from(db_prefix() . 'invoicepaymentrecords as ip');
        $CI->db->join(db_prefix() . 'itemable i', 'i.rel_id = ip.invoiceid AND i.rel_type="invoice"', 'left');
        $CI->db->where_in('ip.invoiceid', $invoice_ids);
        $CI->db->where('i.description', 'Consultation Fee');
        $CI->db->where('ip.date >=', $consulted_from_date . ' 00:00:00');
        $CI->db->where('ip.date <=', $consulted_to_date . ' 23:59:59');
        $unit_gt_consult = $CI->db->get()->row()->consult_total ?? 0;

        // Count (invoices paid)
        $CI->db->select('COUNT(DISTINCT ip.invoiceid) as cnt');
        $CI->db->from(db_prefix() . 'invoicepaymentrecords as ip');
        $CI->db->where_in('ip.invoiceid', $invoice_ids);
        $CI->db->where('ip.date >=', $consulted_from_date . ' 00:00:00');
        $CI->db->where('ip.date <=', $consulted_to_date . ' 23:59:59');
        $unit_gt_count = $CI->db->get()->row()->cnt ?? 0;
    }

    // Base URL params
    $base_url = admin_url("client/branch_summary_details");
    $params = "branch_id={$branch_id}&from={$consulted_from_date}&to={$consulted_to_date}";

    // Prepare clickable row
    $output['data'][] = [
        $branch_name,
        // Current
        '<a href="'.$base_url.'&type=current_gt&'.$params.'" style="color:blue;">'.number_format((float)$current_gt,2).'</a>',
        '<a href="'.$base_url.'&type=consult_fee&'.$params.'" style="color:blue;">'.number_format((float)$consult_fee,2).'</a>',
        '<a href="'.$base_url.'&type=current_total&'.$params.'" style="color:blue;">'.number_format((float)$consult_fee + (float)$current_gt,2).'</a>',
        '<a href="'.$base_url.'&type=gt&'.$params.'" style="color:blue;">'.number_format((float)$proj,2).'</a>',
        
        // Unit Visits
        '<a href="'.$base_url.'&type=unit_visit&'.$params.'" style="color:blue;">'.($unit_visit_count - $ref_visit_count).'</a>',
        '<a href="'.$base_url.'&type=ref_visit&'.$params.'" style="color:blue;">'.$ref_visit_count.'</a>',
        '<a href="'.$base_url.'&type=visit_total&'.$params.'" style="color:blue;">'.$unit_visit_count.'</a>',

        // Registrations
        '<a href="'.$base_url.'&type=reg_count&'.$params.'" style="color:blue;">'.$reg_count.'</a>',
        '<a href="'.$base_url.'&type=unit_reg_gt&'.$params.'" style="color:blue;">'.number_format((float)$unit_reg_gt,2).'</a>',

        // Unit Grand Totals
        '<a href="'.$base_url.'&type=unit_gt_gt&'.$params.'" style="color:blue;">'.number_format((float)$unit_gt_gt,2).'</a>',
        '<a href="'.$base_url.'&type=unit_gt_consult&'.$params.'" style="color:blue;">'.number_format((float)$unit_gt_consult,2).'</a>',
        '<a href="'.$base_url.'&type=unit_gt_count&'.$params.'" style="color:blue;">'.$unit_gt_count.'</a>',
    ];

}




// Return JSON
header('Content-Type: application/json');
echo json_encode($output);
exit;
