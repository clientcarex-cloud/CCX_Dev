<?php defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('lead_call_log/lead_call_log_model');
$CI->load->helper('client/custom');


//$lead_id = $CI->input->get('lead_id');
$start = (int) $CI->input->post('start');
$length = (int) $CI->input->post('length');
$draw = (int) $CI->input->post('draw');
$order = $CI->input->post('order');
$search = $CI->input->post('search')['value'] ?? '';

$order_column_index = $order[0]['column'] ?? 0;
$order_dir = $order[0]['dir'] ?? 'asc';

$aColumns = [
    'l.id',
    'l.created_date',
    'treatment.treatment_name',
    'branch.name',
    'l.enquired_by',
    'l.appointment_date',
    'l.doctor_id',
    'l.slot_time',
    'l.followup_date',
    'leads.addedfrom',
    'res.patient_response_name',
    'l.comments'
];

$order_column_name = $aColumns[$order_column_index];


// Subquery: total payments per invoice
$CI->db->select('invoiceid, SUM(amount) as paid_total, paymentmode');
$CI->db->from(db_prefix() . 'invoicepaymentrecords');
$CI->db->group_by('invoiceid');
$payments_subquery = $CI->db->get_compiled_select();

// SELECT & JOIN
$CI->db->select("
    l.id,
    l.created_date,
    branch.name AS branch_name,
    l.enquired_by,
	treatment.description AS treatment_name,
    appointment.appointment_date,
    appointment.enquiry_doctor_id,
	inv.total AS invoice_total,
	inv.status AS payment_status,
    pay.paid_total,
    pay.paid_total AS paid_amount,
    (inv.total - IFNULL(pay.paid_total,0)) AS due_amount,
    pay_user.paymentmode,
    itm.description AS item_description,
	payment_mode.name as payment_type,
    l.followup_date,
    leads.addedfrom,
    res.name AS response_name,
    l.comments
");
$CI->db->from(db_prefix() . 'lead_call_logs l');
$CI->db->join(db_prefix() . 'leads leads', 'leads.id = l.leads_id', 'left');
$CI->db->join(db_prefix() . 'customers_groups branch', 'branch.id = l.branch_id', 'left');
$CI->db->join(db_prefix() . 'leads_status res', 'res.id = l.patient_response_id', 'left');
$CI->db->join(db_prefix() . 'appointment appointment', 'appointment.appointment_id = l.appointment_id', 'left');

$CI->db->join(db_prefix() . 'items treatment', 'treatment.id = appointment.treatment_id', 'left');
$CI->db->join(db_prefix() . 'invoices inv', 'inv.id = appointment.invoice_id', 'left');

$CI->db->join("({$payments_subquery}) pay", 'pay.invoiceid = inv.id', 'left');
$CI->db->join(db_prefix() . 'invoicepaymentrecords pay_user', 'pay_user.invoiceid = inv.id', 'left');
$CI->db->join(db_prefix() . 'itemable itm', 'itm.rel_id = inv.id', 'left');
$CI->db->join(db_prefix() . 'payment_modes payment_mode', 'payment_mode.id = pay.paymentmode', 'left');

if (!empty($lead_id)) {
    $CI->db->where('l.leads_id', $lead_id);
}

// Filter total count before filtering
$total_records = $CI->db->count_all_results('', false);

// Search
if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('branch.name', $search);
    $CI->db->or_like('res.patient_response_name', $search);
    $CI->db->or_like('l.comments', $search);
    $CI->db->group_end();
}

// Ordering and Pagination
$CI->db->order_by($order_column_name, $order_dir);
$CI->db->limit($length, $start);
// Final result
$results = $CI->db->get()->result_array();

// Format Output
$data = [];
$serial = $start + 1;
foreach ($results as $aRow) {
    $row = [];
    $row[] = $serial++;
    $row[] = _dt($aRow['created_date']);
	$row[] = $aRow['treatment_name'] ?? '';
	$row[] = $aRow['response_name'] ?? '';
    if (!empty($aRow['appointment_date'])) {
		$row[] = get_staff_full_name($aRow['enquiry_doctor_id']);
		$row[] = _d($aRow['appointment_date']);
	} else {
		$row[] = ''; // leave doctor name empty
		$row[] = ''; // leave appointment date empty
	}

	$row[] = $aRow['invoice_total'] ?? '';
	$row[] = format_invoice_status_custom($aRow['payment_status']) ?? '';
    $row[] = !empty($aRow['followup_date']) ? _d($aRow['followup_date']) : '';
	$row[] = $aRow['comments'] ?? '';
    $row[] = $aRow['branch_name'] ?? '';
    $row[] = get_staff_full_name($aRow['addedfrom']);
    
    
    $data[] = $row;
}

// Return JSON
echo json_encode([
    'draw' => $draw,
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records, // update if applying search filtering separately
    'aaData' => $data
]);
exit();
