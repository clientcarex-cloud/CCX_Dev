<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

// Get date filters from POST data or set defaults
$from_date = !empty($consulted_from_date) ? to_sql_date($consulted_from_date) : '2011-01-01';
$to_date = !empty($consulted_to_date) ? to_sql_date($consulted_to_date) : '2025-01-31';


$data   = [];
$totals = [
    'total_renewals'    => 0,
    'expected_active_renewals'   => 0,
    'active_renewals'   => 0,
    'inactive_renewals' => 0,
    'visited_clients'   => 0,
    'reg_clients'       => 0,
    'package_amount'    => 0,
    'paid_amount'       => 0,
    'due_amount'        => 0,
    'conversion_rate'   => 0,
];

// Arrays to store client/user IDs per branch
$branch_client_ids  = [];
$branch_visited_ids = [];

// Get all branches
$CI->db->select('id, name');
$branches = $CI->db->get(db_prefix() . 'customers_groups')->result_array();

foreach ($branches as $branch) {
    $branch_id    = $branch['id'];
    $branch_name  = $branch['name'];

    // Get customer IDs for the current branch
    $customer_ids = array_column(
        $CI->db->select('customer_id')
            ->where('groupid', $branch_id)
            ->get(db_prefix() . 'customer_groups')
            ->result_array(),
        'customer_id'
    );

    // Initialize branch values
    $total_renewals    = 0;
    $expected_active_renewals   = 0;
    $active_renewals   = 0;
    $inactive_renewals = 0;
    $visited_clients   = 0;
    $reg_clients       = 0;
    $package_amount    = 0.00;
    $paid_amount       = 0.00;
    $due_amount        = 0.00;
    $conversion_rate   = 0.00;

    // Initialize arrays to store IDs
    $branch_client_ids[$branch_id]  = [];
    $branch_visited_ids[$branch_id] = [];

    if (!empty($customer_ids)) {
		
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

		$expected_active_clients   = array_column($expected_active_query->result_array(), 'clientid');
		$expected_active_renewals  = count($expected_active_clients);


        // --- Step 1: Find previous invoice details for clients
        $subquery_prev_inv = $CI->db->select('inv_prev.clientid, MAX(inv_prev.duedate) AS previous_duedate')
            ->from(db_prefix() . 'invoices as inv_prev')
            ->join(db_prefix() . 'itemable as item_prev', 'item_prev.rel_id = inv_prev.id AND item_prev.rel_type = "invoice"')
            ->where('inv_prev.date <', $from_date)
            ->where('item_prev.description !=', 'Consultation Fee')
            ->where_in('inv_prev.clientid', $customer_ids)
            ->group_by('inv_prev.clientid')
            ->get_compiled_select();
        
        // --- Step 2: Get a list of client IDs with renewals within the date range
        $renewal_client_ids_query = $CI->db->select('DISTINCT inv.clientid', false)
            ->from(db_prefix() . 'invoices as inv')
            ->join('(' . $subquery_prev_inv . ') AS T_prev', 'T_prev.clientid = inv.clientid', 'inner')
            ->join(db_prefix() . 'itemable as item', 'item.rel_id = inv.id AND item.rel_type = "invoice"')
            ->where('inv.date >=', $from_date)
            ->where('inv.date <=', $to_date)
            ->where('item.description !=', 'Consultation Fee')
            ->where_in('inv.clientid', $customer_ids)
            ->get();
        
        $renewal_client_ids = array_column($renewal_client_ids_query->result_array(), 'clientid');
		
        // Store client IDs for this branch
        $branch_client_ids[$branch_id] = $renewal_client_ids;

        if (!empty($renewal_client_ids)) {
            // --- Step 3: Get renewal summary (total, active, inactive)
            $renewal_data_query = $CI->db->select('
                    COUNT(DISTINCT inv.clientid) AS total_renewals,
                    SUM(CASE WHEN inv.date <= T_prev.previous_duedate THEN 1 ELSE 0 END) AS active_renewals,
                    SUM(CASE WHEN inv.date > T_prev.previous_duedate THEN 1 ELSE 0 END) AS inactive_renewals,
                    SUM(inv.total) AS package_amount
                ')
                ->from(db_prefix() . 'invoices as inv')
                ->join('(' . $subquery_prev_inv . ') AS T_prev', 'T_prev.clientid = inv.clientid', 'inner')
                ->join(db_prefix() . 'itemable as item', 'item.rel_id = inv.id AND item.rel_type = "invoice"')
                ->where('inv.date >=', $from_date)
                ->where('inv.date <=', $to_date)
                ->where('item.description !=', 'Consultation Fee')
                ->where_in('inv.clientid', $renewal_client_ids)
                ->get();
            
            $renewal_data = $renewal_data_query->row();

            $total_renewals    = $renewal_data->total_renewals;
            $active_renewals   = $renewal_data->active_renewals;
            $inactive_renewals = $renewal_data->inactive_renewals;
            $package_amount    = $renewal_data->package_amount;
            
            // --- Step 4: Calculate paid amount
            $paid_amount_query = $CI->db->select('SUM(pay.amount) as paid_sum')
                ->from(db_prefix() . 'invoices as inv')
                ->join(db_prefix() . 'invoicepaymentrecords as pay', 'pay.invoiceid = inv.id')
                ->where('inv.date >=', $from_date)
                ->where('inv.date <=', $to_date)
                ->where_in('inv.clientid', $renewal_client_ids)
                ->get();
            
            $paid_amount = $paid_amount_query->row()->paid_sum ?: 0.00;
            
            $due_amount = $package_amount - $paid_amount;

            // --- Step 5: Get unique visited client IDs
            $visit_query = $CI->db->select('userid')
                ->from(db_prefix() . 'appointment')
                ->where('visit_status', 1)
                ->where_in('userid', $renewal_client_ids)
                ->where('branch_id', $branch_id)
                ->where('appointment_date >=', $from_date . ' 00:00:00')
                ->where('appointment_date <=', $to_date . ' 23:59:00')
                ->get();
				
            $visited_client_ids = array_column($visit_query->result_array(), 'userid');
            $visited_clients = count($visited_client_ids);

            // Store visited client IDs
            $branch_visited_ids[$branch_id] = $visited_client_ids;

            // --- Step 6: Determine 'Reg' clients by finding the intersection of renewals and visits
            $reg_client_ids = array_intersect($renewal_client_ids, $visited_client_ids);
            $reg_clients = count($reg_client_ids);
            
            // --- Step 7: Calculate conversion rate
            $conversion_rate = ($visited_clients > 0) ? ($reg_clients / $visited_clients) * 100 : 0;
        }

        // Update totals
        $totals['total_renewals']    += $total_renewals;
        $totals['active_renewals']   += $active_renewals;
        $totals['expected_active_renewals']   += $expected_active_renewals;
        $totals['inactive_renewals'] += $inactive_renewals;
        $totals['visited_clients']   += $visited_clients;
        $totals['reg_clients']       += $reg_clients;
        $totals['package_amount']    += $package_amount;
        $totals['paid_amount']       += $paid_amount;
        $totals['due_amount']        += $due_amount;
    }

    // Add branch row
    $data[] = [
        $branch_name,
        '<a style="color: blue" href="' . admin_url("client/reports/renewal_summary_details/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/total_renewals/NULL") . '" target="_blank">' . $total_renewals . '</a>',
        //'<a style="color: blue" href="' . admin_url("client/reports/renewal_summary_details/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/expected_active_renewals/NULL") . '" target="_blank">' . $expected_active_renewals . '</a>',
        '<a style="color: blue" href="' . admin_url("client/reports/renewal_summary_details/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/active_renewals/NULL") . '" target="_blank">' . $active_renewals . '</a>',
        '<a style="color: blue" href="' . admin_url("client/reports/renewal_summary_details/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/inactive_renewals/NULL") . '" target="_blank">' . $inactive_renewals . '</a>',
        /* '<a style="color: blue" href="' . admin_url("client/reports/renewal_summary_details/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/visited_clients/NULL") . '" target="_blank">' . $visited_clients . '</a>',
        '<a style="color: blue" href="' . admin_url("client/reports/renewal_summary_details/NULL/{$from_date}/{$to_date}/NULL/{$branch_id}/NULL/reg_clients/NULL") . '" target="_blank">' . $reg_clients . '</a>', */
		//$visited_clients,
		//$reg_clients,
        number_format($package_amount, 2),
        number_format($paid_amount, 2),
        number_format($due_amount, 2),
        number_format($conversion_rate, 2) . '%',
    ];
}

// Calculate overall conversion rate safely
$totals['conversion_rate'] = ($totals['visited_clients'] > 0)
    ? ($totals['reg_clients'] / $totals['visited_clients']) * 100
    : 0;

// Add totals row at the end
$data[] = [
    '<b>Totals</b>',
    $totals['total_renewals'],
    //$totals['expected_active_renewals'],
    $totals['active_renewals'],
    $totals['inactive_renewals'],
   // $totals['visited_clients'],
    //$totals['reg_clients'],
    '<b>' . number_format($totals['package_amount'], 2) . '</b>',
    '<b>' . number_format($totals['paid_amount'], 2) . '</b>',
    '<b>' . number_format($totals['due_amount'], 2) . '</b>',
    '<b>' . number_format($totals['conversion_rate'], 2) . '%</b>',
];

// Final JSON (no pagination)
echo json_encode([
    'data'                => $data,
    'branch_client_ids'   => $branch_client_ids,   // client IDs per branch
    'branch_visited_ids'  => $branch_visited_ids,  // visited user IDs per branch
]);

exit;
