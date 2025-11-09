<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search_value = $_POST['search']['value'] ?? '';

$from_date = !empty($consulted_from_date) ? to_sql_date($consulted_from_date) : null;
$to_date   = !empty($consulted_to_date) ? to_sql_date($consulted_to_date) : null;
$selected_branch_id = isset($selected_branch_id) && is_array($selected_branch_id) ? $selected_branch_id : [];

// =====================
// 1. Get all lead sources
// =====================
$CI->db->select('id, name');
if ($search_value) {
    $CI->db->like('name', $search_value);
}
$sources = $CI->db->get(db_prefix() . 'leads_sources')->result_array();
$filtered_sources = array_slice($sources, $start, $length);

$output = [
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
    'recordsTotal' => count($sources),
    'recordsFiltered' => count($sources),
    'aaData' => [],
];

$total_enquiries = $total_appointments = $total_visits = $total_consultations = 0;
$total_registrations = $total_consult_fee = $total_package = $total_paid = $total_due = 0;

// =====================
// Loop sources
// =====================
foreach ($filtered_sources as $source) {
    $source_id   = $source['id'];
    $source_name = $source['name'];

    // -------------------------
    // Step 1: Get all userids for this source + branch
    // -------------------------
    $CI->db->select('new.userid');
    $CI->db->from(db_prefix() . 'clients_new_fields new');
    if (!empty($selected_branch_id)) {
        $CI->db->join(db_prefix() . 'customer_groups cg', 'cg.customer_id = new.userid', 'inner');
        $CI->db->where_in('cg.groupid', $selected_branch_id);
    }
    $CI->db->where('new.patient_source_id', $source_id);
    $all_users = $CI->db->get()->result_array();
    $user_ids = array_column($all_users, 'userid');

    if (empty($user_ids)) {
        // No patients for this source+branch, push empty row
        $output['aaData'][] = [$source_name, 0, 0, 0, 0, 0, app_format_money_custom(0, 1), app_format_money_custom(0, 1), app_format_money_custom(0, 1), app_format_money_custom(0, 1)];
        continue;
    }

    // -------------------------
    // Step 2: Enquiries
    // -------------------------
    $CI->db->distinct();
    $CI->db->select('leadid');
    $CI->db->from(db_prefix() . 'clients c');
    $CI->db->where_in('c.userid', $user_ids);
    if ($from_date) $CI->db->where('c.datecreated >=', $from_date);
    if ($to_date)   $CI->db->where('c.datecreated <=', $to_date);
    $enquiries = $CI->db->count_all_results();

    // -------------------------
    // Step 3: Appointments / Visits / Consultations
    // -------------------------
    $CI->db->select('a.*');
    $CI->db->from(db_prefix() . 'appointment a');
    $CI->db->where_in('a.userid', $user_ids);
    if ($from_date) $CI->db->where('a.appointment_date >=', $from_date . ' 00:00:00');
    if ($to_date)   $CI->db->where('a.appointment_date <=', $to_date . ' 23:59:59');
    $appointments_data = $CI->db->get()->result_array();

    $appointments = $visits = $consultations = 0;
    $consultation_userids = [];
    foreach ($appointments_data as $a) {
        $appointments++;
        if ((int)$a['visit_status'] === 1 && empty($a['consulted_date'])) {
            $visits++;
        }
        if ((int)$a['visit_status'] === 1 && !empty($a['consulted_date']) && $a['consulted_date'] !== '0000-00-00') {
            $consultations++;
            $consultation_userids[] = $a['userid'];
        }
    }

    // -------------------------
    // Step 4: Registrations
    // -------------------------
    $CI->db->select('new.userid');
    $CI->db->from(db_prefix() . 'clients_new_fields new');
    $CI->db->where_in('new.userid', $user_ids);
    $CI->db->where("new.mr_no IS NOT NULL AND new.mr_no != ''");
    if ($from_date) $CI->db->where('new.registration_start_date >=', $from_date);
    if ($to_date)   $CI->db->where('new.registration_start_date <=', $to_date);
    $registrations = $CI->db->count_all_results();

    // -------------------------
    // Step 5 + 6: Consultation Fee & Package totals
    // -------------------------
    $CI->db->select("
        SUM(CASE WHEN itemable.description = 'Consultation Fee' 
                 THEN (itemable.qty * itemable.rate) ELSE 0 END) AS consult_total,
        SUM(CASE WHEN itemable.description != 'Consultation Fee' 
                 THEN (inv.total) ELSE 0 END) AS package_total,
        SUM(CASE WHEN itemable.description = 'Consultation Fee' 
                 THEN IFNULL(payments.amount, 0) ELSE 0 END) AS consult_paid,
        SUM(CASE WHEN itemable.description != 'Consultation Fee' 
                 THEN IFNULL(payments.amount, 0) ELSE 0 END) AS package_paid,
        SUM(CASE WHEN itemable.description = 'Consultation Fee' 
                 THEN (itemable.qty * itemable.rate) - IFNULL(payments.amount, 0) ELSE 0 END) AS consult_due,
        SUM(CASE WHEN itemable.description != 'Consultation Fee' 
                 THEN (inv.total) - IFNULL(payments.amount, 0) ELSE 0 END) AS package_due
    ", false);
    $CI->db->from(db_prefix() . 'invoices inv');
    $CI->db->join(db_prefix() . 'itemable itemable', 'itemable.rel_id = inv.id AND itemable.rel_type="invoice"', 'left');
    $CI->db->join("(SELECT invoiceid, SUM(amount) as amount FROM tblinvoicepaymentrecords GROUP BY invoiceid) AS payments", 'payments.invoiceid = inv.id', 'left');
    $CI->db->where_in('inv.clientid', $user_ids);
    if ($from_date) $CI->db->where('inv.date >=', $from_date);
    if ($to_date)   $CI->db->where('inv.date <=', $to_date);
    $financials = $CI->db->get()->row_array();

    $consult_fee_total = $financials['consult_total'] ?? 0;
    $package_total     = $financials['package_total'] ?? 0;
    $package_paid      = $financials['package_paid'] ?? 0;
    $package_due       = $financials['package_due'] ?? 0;

    // -------------------------
    // Step 7: Totals
    // -------------------------
    $total_enquiries     += $enquiries;
    $total_appointments  += $appointments;
    $total_visits        += $visits;
    $total_consultations += $consultations;
    $total_registrations += $registrations;
    $total_consult_fee   += $consult_fee_total;
    $total_package       += $package_total;
    $total_paid          += $package_paid;
    $total_due           += $package_due;
	
	
	$base_url = admin_url('client/reports/source_enquiry_report_detail');

	// Create links
	$enquiries_link     = '<a href="'.$base_url.'/NULL/'.$from_date.'/'.$to_date.'/null/1/NULL/enquiries/'.$source_id.'" target="_blank">'.$enquiries.'</a>';
	$appointments_link  = '<a href="'.$base_url.'/NULL/'.$from_date.'/'.$to_date.'/null/1/NULL/appointments/'.$source_id.'" target="_blank">'.$appointments.'</a>';
	$visits_link        = '<a href="'.$base_url.'/NULL/'.$from_date.'/'.$to_date.'/null/1/NULL/visits/'.$source_id.'" target="_blank">'.$visits.'</a>';
	$consultations_link = '<a href="'.$base_url.'/NULL/'.$from_date.'/'.$to_date.'/null/1/NULL/consultations/'.$source_id.'" target="_blank">'.$consultations.'</a>';
	$registrations_link = '<a href="'.$base_url.'/NULL/'.$from_date.'/'.$to_date.'/null/1/NULL/registrations/'.$source_id.'" target="_blank">'.$registrations.'</a>';


    // -------------------------
    // Step 8: Add to table
    // -------------------------
    $output['aaData'][] = [
		$source_name,
		$enquiries_link,
		$appointments_link,
		$visits_link,
		$consultations_link,
		$registrations_link,
		app_format_money_custom($consult_fee_total, 1),
		app_format_money_custom($package_total, 1),
		app_format_money_custom($package_paid, 1),
		app_format_money_custom($package_due, 1),
	];
}

// =====================
// Total row
// =====================
$output['aaData'][] = [
    '<strong>Total</strong>',
    "<strong>{$total_enquiries}</strong>",
    "<strong>{$total_appointments}</strong>",
    "<strong>{$total_visits}</strong>",
    "<strong>{$total_consultations}</strong>",
    "<strong>{$total_registrations}</strong>",
    '<strong>' . app_format_money_custom($total_consult_fee, 1) . '</strong>',
    '<strong>' . app_format_money_custom($total_package, 1) . '</strong>',
    '<strong>' . app_format_money_custom($total_paid, 1) . '</strong>',
    '<strong>' . app_format_money_custom($total_due, 1) . '</strong>',
];

echo json_encode($output);
exit;
