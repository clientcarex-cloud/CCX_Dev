<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// ---- Filters ----
$search_value = $CI->input->post('search')['value'] ?? '';
$from_date = !empty($consulted_from_date) ? to_sql_date($consulted_from_date) : '2011-01-01';
$to_date = !empty($consulted_to_date) ? to_sql_date($consulted_to_date) : '2025-01-31';

$output = [
    'data' => []
];

// ---- Query Branches ----
$CI->db->start_cache();
$CI->db->from(db_prefix() . 'customers_groups cg');
if (!empty($search_value)) {
    $CI->db->like('cg.name', $search_value);
}
$CI->db->stop_cache();

$results = $CI->db->get()->result_array();
$CI->db->flush_cache();

// ---- Initialize Totals ----
$totals = array_fill_keys([
    'main_goal', 'gt', 'prog', 'np_visit', 'np_reg', 'reg_percent', 'con_fee', 'np_paid',
    'enq_visit', 'enq_reg', 'enq_gt', 'enq_paid', 'enq_due', 'enq_goal', 'enq_tv', 'ren_visited', 'renewed', 'renewed_percent',
    'ren_paid', 'ren_due', 'ren_gt', 'ren_goal', 'ren_tv', 'ref_visited', 'ref_reg',
    'ref_reg_percent', 'ref_paid', 'ref_due', 'ref_gt', 'ref_goal', 'ref_tv', 'refund_amount'
], 0);

// ======================= LOOP PER BRANCH =======================
foreach ($results as $row) {
    $branch_id = $row['id'];
    $branch_name = $row['name'];

    // ---- Collect customers in this branch ----
    $CI->db->select('customer_id');
    $CI->db->from(db_prefix() . 'customer_groups');
    $CI->db->where('groupid', $branch_id);
    $customer_ids = array_column($CI->db->get()->result_array(), 'customer_id');

    // ---- Init Metrics ----
    $main_goal = $gt = $prog = $np_visit = $np_reg = $reg_percent = 0;
    $con_fee = $np_paid = $enq_visit = $enq_reg = $enq_gt = $enq_paid = $enq_due = $enq_goal = $enq_tv = 0;
    $ren_visited = $renewed = $renewed_percent = $ren_paid = $ren_due = 0;
    $ren_gt = $ren_goal = $ren_tv = 0;
    $ref_visited = $ref_reg = $ref_reg_percent = $ref_paid = $ref_due = 0;
    $ref_gt = $ref_goal = $ref_tv = $refund_amount = 0;

    if (!empty($customer_ids)) {
        // ---------------- Invoices ----------------
        $CI->db->select('inv.id, inv.clientid, inv.total, item.description');
        $CI->db->from(db_prefix() . 'invoices inv');
        $CI->db->join(db_prefix() . 'itemable item', 'item.rel_id=inv.id AND item.rel_type="invoice"', 'left');
        $CI->db->where_in('inv.clientid', $customer_ids);
        $CI->db->where('inv.date >=', $from_date);
        $CI->db->where('inv.date <=', $to_date);
        $invoices = $CI->db->get()->result_array();

        foreach ($invoices as $inv) {
            if ($inv['description'] == 'Consultation Fee') {
                $con_fee += $inv['total'];
            } else {
                $gt += $inv['total'];
            }
        }

        $prog = $gt + $con_fee;
        $appointment_type_id = [18, 2];

        // Step 1: NP Visit Clients
        $np_visit_query = $CI->db->select('userid')
            ->from('tblappointment')
            ->where('visit_status', 1)
            ->where('branch_id', $branch_id)
            ->where_in('appointment_type_id', $appointment_type_id)
            ->where('appointment_date >=', $from_date . " 00:00:00")
            ->where('appointment_date <=', $to_date . " 23:59:00")
            //->group_by('userid')
            ->get()
            ->result_array();
		
        $np_visit_clients = array_column($np_visit_query, 'userid');
        $np_visit = count($np_visit_clients);
        // Step 2: NP Reg Clients (only those who are in NP Visit list)
        $np_reg_query = [];
        $np_reg = 0;
		if (!empty($np_visit_clients)) {
			$np_reg_query = $CI->db->select('a.userid')
				->from('tblappointment as a')
				->join('tblinvoices as inv', 'inv.clientid = a.userid', 'inner')
				->join('tblitemable as item', 'item.rel_id = inv.id AND item.rel_type="invoice"', 'inner')
				->where('item.description !=', 'Consultation Fee')
				//->where_in('a.userid', $np_visit_clients)
				->where('a.visit_status', 1)
				->where('a.branch_id', $branch_id)
				->where_in('a.appointment_type_id', [18, 2])
				->where('a.appointment_date >=', $from_date . " 00:00:00")
				->where('a.appointment_date <=', $to_date . " 23:59:00")
				->where('inv.date >=', $from_date)
				->where('inv.date <=', $to_date)
				//->group_by('a.userid')
				->get()
				->result_array();
				

			$np_reg = count($np_reg_query);
		}


        // Extract only client IDs from the result
        $np_reg_client_ids = array_column($np_reg_query, 'userid');
		//print_r($np_reg_client_ids);

        // Calculate NP Paid
        $np_paid = 0;
        if (!empty($np_reg_client_ids)) {
           $np_paid = $CI->db->select('IFNULL(SUM(pay.amount),0) as total_paid')
                ->from('tblinvoicepaymentrecords as pay')
                ->join('tblinvoices as inv', 'inv.id = pay.invoiceid')
				->join('tblitemable as item', 'item.rel_id = inv.id AND item.rel_type="invoice"', 'inner')
				->where('item.description !=', 'Consultation Fee')
                ->where_in('inv.clientid', $np_reg_client_ids)
                ->where('pay.date >=', $from_date)
                ->where('pay.date <=', $to_date)
                ->get()
                ->row()
                ->total_paid;
        }

        if ($np_reg > 0) {
            $reg_percent = $np_visit > 0
			? round(($np_reg / $np_visit) * 100)
			: 0;

        } else {
            $reg_percent = 0;
        }

        // ---------------- Enquiry Related Calculations ----------------
        if (!empty($np_reg_client_ids)) {
            $enq_invoice_data = $CI->db->select('SUM(inv.total) as total_gt, SUM(IFNULL(pay.amount,0)) as total_paid')
                ->from(db_prefix() . 'invoices as inv')
                ->join(db_prefix() . 'invoicepaymentrecords as pay', 'pay.invoiceid = inv.id', 'left')
                ->where_in('inv.clientid', $np_reg_client_ids)
                ->where('inv.date >=', $from_date)
                ->where('inv.date <=', $to_date)
                ->get()
                ->row();

            $enq_gt = $enq_invoice_data->total_gt ?? 0;
            $enq_paid = $enq_invoice_data->total_paid ?? 0;
            $enq_due = $enq_gt - $enq_paid;
        }

        $appointment_type_id = [6, 11, 17, 24, 32];
        $ren_visit_query = $CI->db->select('userid')
            ->from('tblappointment')
            ->where('visit_status', 1)
            ->where('branch_id', $branch_id)
            ->where_in('appointment_type_id', $appointment_type_id)
            ->where('appointment_date >=', $from_date . " 00:00:00")
            ->where('appointment_date <=', $to_date . " 23:59:00")
            //->group_by('userid')
            ->get()
            ->result_array();
        
        $ren_visit_clients = array_column($ren_visit_query, 'userid');
        $ren_visited = count($ren_visit_query);
        
        if (!empty($ren_visit_clients)) {
            $ren_reg_query = $CI->db->select('a.userid')
				->from('tblappointment as a')
				->join('tblinvoices as inv', 'inv.clientid = a.userid', 'inner')
				->join('tblitemable as item', 'item.rel_id = inv.id AND item.rel_type="invoice"', 'inner')
				->where('item.description !=', 'Consultation Fee')
				->where_in('a.userid', $ren_visit_clients)
				->where('a.visit_status', 1)
				->where('a.branch_id', $branch_id)
				->where_in('a.appointment_type_id', [6, 11, 17, 24, 32])
				->where('a.appointment_date >=', $from_date . " 00:00:00")
				->where('a.appointment_date <=', $to_date . " 23:59:00")
				->where('inv.date >=', $from_date)
				->where('inv.date <=', $to_date)
				->group_by('a.userid')
				->get()
				->result_array();

            $ren_registered = count($ren_reg_query);
            
            $ren_sum = $CI->db->select('
                SUM(inv.total) as gt,
                SUM(inv.total) - IFNULL(SUM(pay.amount),0) as due,
                IFNULL(SUM(pay.amount),0) as paid
            ')
                ->from(db_prefix() . 'invoices as inv')
                ->join(db_prefix() . 'itemable as item', 'item.rel_id = inv.id AND item.rel_type="invoice"')
                ->join(db_prefix() . 'invoicepaymentrecords as pay', 'pay.invoiceid = inv.id', 'left')
                ->where('item.description !=', 'Consultation Fee')
                ->where_in('inv.clientid', $ren_visit_clients)
                ->where('inv.date >=', $from_date)
                ->where('inv.date <=', $to_date)
                ->get()
                ->row();

            $ren_paid = $ren_sum->paid ?? 0;
            $ren_due = $ren_sum->due ?? 0;
            $ren_gt = $ren_sum->gt ?? 0;
        }
        
        $con_fee = $CI->db->select('IFNULL(SUM(pay.amount),0) as total_consultation_fee', false)
		->from(db_prefix() . 'invoices as inv')
		->join(db_prefix() . 'itemable as item', 'item.rel_id = inv.id AND item.rel_type = "invoice"', 'inner')
		->join(db_prefix() . 'invoicepaymentrecords as pay', 'pay.invoiceid = inv.id', 'inner')
		->where('item.description', 'Consultation Fee')
		->where_in('inv.clientid', $customer_ids)
		->where('inv.date >=', $from_date)
		->where('inv.date <=', $to_date)
		->get()
		->row()
		->total_consultation_fee;
		
		

        $refund_amount = 0;

        $ref_users = $CI->db->select('c.userid')
            ->from(db_prefix() . 'leads l')
            ->join(db_prefix() . 'clients c', 'c.leadid = l.id', 'left')
            ->where('l.refer_id >', 0)
            ->where_in('c.userid', $customer_ids)
            ->where('c.datecreated >=', $from_date . ' 00:00:00')
            ->where('c.datecreated <=', $to_date . ' 23:59:00')
            ->get()
            ->result_array();
        $ref_user_ids = array_column($ref_users, 'userid');
        $ref_visited = $ref_reg = $ref_reg_percent = $ref_paid = $refered = $ref_due = $ref_gt = 0;
        $refered = COUNT($ref_user_ids);
        if (!empty($ref_user_ids)) {
            // Referred visits
            $ref_visit_query = $CI->db->select('userid')
                ->from('tblappointment')
                ->where('visit_status', 1)
                ->where_in('userid', $ref_user_ids)
                ->where('appointment_date >=', $from_date . ' 00:00:00')
                ->where('appointment_date <=', $to_date . ' 23:59:00')
                ->group_by('userid')
                ->get()
                ->result_array();

            $ref_visited = count($ref_visit_query);

            // Referred registrations (only 1 valid invoice excluding Consultation Fee)
            $ref_reg_query = $CI->db->select('inv.clientid')
                ->from(db_prefix() . 'invoices as inv')
                ->join(db_prefix() . 'itemable as item', 'item.rel_id = inv.id AND item.rel_type="invoice"')
                ->join(db_prefix() . 'invoicepaymentrecords as pay', 'pay.invoiceid = inv.id', 'inner')
                ->where('item.description !=', 'Consultation Fee')
                ->where_in('inv.clientid', $ref_user_ids)
                ->where('inv.date >=', $from_date)
                ->where('inv.date <=', $to_date)
                ->group_by('inv.clientid')
                ->having('COUNT(DISTINCT inv.id) = 1', null, false)
                ->get()
                ->result_array();

            $ref_reg = count($ref_reg_query);

            // % calculation
            $ref_reg_percent = ($ref_visited > 0)
                ? round(($ref_reg / $ref_visited) * 100)
                : 0;

            // Invoice sums (total, paid, due) for referred clients
            $ref_sum = $CI->db->select('
                SUM(inv.total) as gt,
                SUM(inv.total) - IFNULL(SUM(pay.amount),0) as due,
                IFNULL(SUM(pay.amount),0) as paid
            ')
                ->from(db_prefix() . 'invoices as inv')
                ->join(db_prefix() . 'itemable as item', 'item.rel_id = inv.id AND item.rel_type="invoice"')
                ->join(db_prefix() . 'invoicepaymentrecords as pay', 'pay.invoiceid = inv.id', 'left')
                ->where('item.description !=', 'Consultation Fee')
                ->where_in('inv.clientid', $ref_user_ids)
                ->where('inv.date >=', $from_date)
                ->where('inv.date <=', $to_date)
                ->get()
                ->row();

            $ref_paid = $ref_sum->paid ?? 0;
            $ref_due = $ref_sum->due ?? 0;
            $ref_gt = $ref_sum->gt ?? 0;
        }

        // 8. Format outputs
        $ref_reg_percent = $ref_reg_percent;
    }
    if ($ren_visited > 0) {
        $renewed_percent = round(($ren_registered / $ren_visited) * 100);
    }
    
    if ($np_reg > 0) {
        $enq_tv = ($con_fee + $np_paid)/$np_reg;
    } else {
        $enq_tv = 0;
    }
    
    if ($ren_registered > 0) {
        $ren_tv = $ren_paid / $ren_registered;
    } else {
        $ren_tv = 0;
    }
    
    if ($ref_reg > 0) {
        $ref_tv = $ref_paid / $ref_reg;
    } else {
        $ref_tv = 0;
    }
    
    // ---------------- Build Row ----------------
    $output['data'][] = [
        $branch_name,
        $main_goal,
        $gt,
        $prog,
        '<a style="color: blue" href="' . admin_url("client/reports/gt_summary_details/NULL/{$consulted_from_date}/{$consulted_to_date}/NULL/{$branch_id}/NULL/np_visit/NULL") . '" target="_blank">' . $np_visit . '</a>',
        '<a style="color: blue" href="' . admin_url("client/reports/gt_summary_details/NULL/{$consulted_from_date}/{$consulted_to_date}/NULL/{$branch_id}/NULL/np_reg/NULL") . '" target="_blank">' . $np_reg . '</a>',
        $reg_percent,
		'<a style="color: blue" href="' . admin_url("client/reports/gt_consult_fee_report/NULL/{$consulted_from_date}/{$consulted_to_date}/NULL/{$branch_id}/NULL/np_reg/NULL") . '" target="_blank">' . number_format($con_fee, 0) . '</a>',
        
		'<a style="color: blue" href="' . admin_url("client/reports/gt_summary_details/NULL/{$consulted_from_date}/{$consulted_to_date}/NULL/{$branch_id}/NULL/np_reg/NULL") . '" target="_blank">' . number_format($np_paid, 0) . '</a>',
        number_format(($np_paid + $con_fee), 0),
        number_format($enq_due, 0),
        number_format($enq_goal, 0),
        number_format($enq_tv, 0),
        '<a style="color: blue" href="' . admin_url("client/reports/gt_summary_details/NULL/{$consulted_from_date}/{$consulted_to_date}/NULL/{$branch_id}/NULL/ren_visited/NULL") . '" target="_blank">' . $ren_visited . '</a>',
        '<a style="color: blue" href="' . admin_url("client/reports/gt_summary_details/NULL/{$consulted_from_date}/{$consulted_to_date}/NULL/{$branch_id}/NULL/ren_registered/NULL") . '" target="_blank">' . $ren_registered . '</a>',
        number_format($renewed_percent, 0),
        number_format($ren_paid, 0),
        number_format($ren_due, 0),
        number_format($ren_gt, 0),
        number_format($ren_goal, 0),
        number_format($ren_tv, 0),
        '<a style="color: blue" href="' . admin_url("client/reports/gt_summary_details/NULL/{$consulted_from_date}/{$consulted_to_date}/NULL/{$branch_id}/NULL/ref_visited/NULL") . '" target="_blank">' . $ref_visited . '</a>',
        '<a style="color: blue" href="' . admin_url("client/reports/gt_summary_details/NULL/{$consulted_from_date}/{$consulted_to_date}/NULL/{$branch_id}/NULL/ref_reg/NULL") . '" target="_blank">' . $ref_reg . '</a>',
        number_format($ref_reg_percent, 0),
        number_format($ref_paid, 0),
        number_format($ref_due, 0),
        number_format($ref_gt, 0),
        number_format($ref_goal, 0),
        number_format($ref_tv, 0),
        number_format($refund_amount, 0),
    ];

    // ---- Accumulate Totals ----
    $totals['gt'] += $gt;
    $totals['prog'] += $prog;
    $totals['con_fee'] += $con_fee;
    $totals['np_visit'] += $np_visit;
    $totals['np_reg'] += $np_reg;
    $totals['np_paid'] += $np_paid;
    $totals['enq_visit'] += $enq_visit;
    $totals['enq_reg'] += $enq_reg;
    $totals['enq_gt'] += $enq_gt;
    $totals['enq_paid'] += $enq_paid;
    $totals['enq_due'] += $enq_due;
    $totals['refund_amount'] += $refund_amount;
    $totals['ren_visited'] += $ren_visited;
    $totals['renewed'] += $renewed;
    $totals['ren_paid'] += $ren_paid;
    $totals['ren_due'] += $ren_due;
    $totals['ren_gt'] += $ren_gt;
}

// ================== ADD TOTALS ROW ==================
$output['data'][] = [
    '<b style="color:#963aae;">Total</b>',
    '<span style="color:#963aae;"><b>' . $totals['main_goal'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['gt'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['prog'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['np_visit'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['np_reg'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['reg_percent'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['con_fee'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['np_paid'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['enq_visit'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['enq_reg'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['enq_gt'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['enq_paid'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['enq_due'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['enq_goal'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['enq_tv'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ren_visited'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['renewed'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['renewed_percent'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ren_paid'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ren_due'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ren_gt'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ren_goal'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ren_tv'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ref_visited'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ref_reg'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ref_reg_percent'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ref_paid'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ref_due'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ref_gt'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ref_goal'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['ref_tv'] . '</b></span>',
    '<span style="color:#963aae;"><b>' . $totals['refund_amount'] . '</b></span>',
];


header('Content-Type: application/json');
echo json_encode($output);
exit;

?>