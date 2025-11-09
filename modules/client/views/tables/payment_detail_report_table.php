<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI =& get_instance();
$CI->load->model('client_model');

// ===== Get Filters and Pagination Parameters =====
$from_date = !empty($consulted_from_date) ? to_sql_date($consulted_from_date) : date('Y-m-01');
$to_date = !empty($consulted_to_date) ? to_sql_date($consulted_to_date) : date('Y-m-d');
$selected_branch_id = isset($selected_branch_id) ? (array)$selected_branch_id : [];

$start = $CI->input->post('start');
$length = $CI->input->post('length');
$search = $CI->input->post('search');

$search_value = '';
if (isset($search['value']) && $search['value'] != '') {
    $search_value = $search['value'];
}

// ===== Step 1: Get patients for branch =====
$branch_patients = [];
if (!empty($selected_branch_id)) {
    $CI->db->select('customer_id AS userid');
    $CI->db->from(db_prefix() . 'customer_groups');
    $CI->db->where_in('groupid', $selected_branch_id);
    $branch_patients = $CI->db->get()->result_array();
}

$branch_patient_ids = array_column($branch_patients, 'userid');

// If branch is selected and no patients match → return empty
if (!empty($selected_branch_id) && empty($branch_patient_ids)) {
    echo json_encode([
        'draw' => intval($CI->input->post('draw')),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'aaData' => [],
    ]);
    exit;
}

// ===== Step 2: Get Total Filtered Records Count =====
$CI->db->select('COUNT(DISTINCT(payment.id)) as total_filtered');
$CI->db->from(db_prefix() . 'invoicepaymentrecords payment');
$CI->db->join(db_prefix() . 'invoices inv', 'inv.id = payment.invoiceid', 'left');
$CI->db->join(db_prefix() . 'clients c', 'c.userid = inv.clientid', 'left');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
$CI->db->join(db_prefix() . 'customer_groups cc', 'cc.customer_id = c.userid', 'left');
$CI->db->join(db_prefix() . 'customers_groups branch', 'branch.id = cc.groupid', 'left');
$CI->db->join(db_prefix() . 'leads_sources sources', 'sources.id = new.patient_source_id', 'left');
$CI->db->join(db_prefix() . 'itemable item', 'item.rel_id = inv.id AND item.rel_type = "invoice"', 'left');

$CI->db->where('payment.date >=', $from_date);
$CI->db->where('payment.date <=', $to_date);

if (!empty($branch_patient_ids)) {
    $CI->db->where_in('c.userid', $branch_patient_ids);
}

// Add search condition
if (!empty($search_value)) {
    $CI->db->group_start();
    $CI->db->like('c.company', $search_value);
    $CI->db->or_like('new.mr_no', $search_value);
    $CI->db->or_like('branch.name', $search_value);
    $CI->db->or_like('sources.name', $search_value);
    $CI->db->or_like('item.description', $search_value);
    $CI->db->group_end();
}
$total_filtered = $CI->db->get()->row()->total_filtered;

// ===== Step 3: Get Payment Records with Joins and Pagination =====
$CI->db->select('
    DISTINCT(payment.id),
    c.company,
    c.userid,
    item.description as package,
    new.mr_no,
    payment.id,
    payment.date,
    payment.amount as paid,
    payment.received_by,
    mode.name as payment_mode,
    staff.firstname,
    staff.lastname,
    payment.transactionid,
    sources.name as patient_source,
    inv.addedfrom,
    inv.datecreated,
    branch.name as branch_name,
    inv.total as total,
    payment.invoiceid,
    payment.utr_no,
	payment_category.appointment_type_name,
');
$CI->db->from(db_prefix() . 'invoicepaymentrecords payment');
$CI->db->join(db_prefix() . 'invoices inv', 'inv.id = payment.invoiceid', 'left');
$CI->db->join(db_prefix() . 'itemable item', 'item.rel_id = inv.id AND item.rel_type = "invoice"', 'left');
$CI->db->join(db_prefix() . 'payment_modes mode', 'mode.id = payment.paymentmode', 'left');
$CI->db->join(db_prefix() . 'staff staff', 'staff.staffid = payment.received_by', 'left');
$CI->db->join(db_prefix() . 'clients c', 'c.userid = inv.clientid', 'left');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
$CI->db->join(db_prefix() . 'customer_groups cc', 'cc.customer_id = c.userid', 'left');
$CI->db->join(db_prefix() . 'customers_groups branch', 'branch.id = cc.groupid', 'left');
$CI->db->join(db_prefix() . 'leads_sources sources', 'sources.id = new.patient_source_id', 'left');

$CI->db->join(db_prefix() . 'appointment_type as payment_category', 'payment_category.appointment_type_id = inv.appointment_type_id', 'left');

$CI->db->where('payment.date >=', $from_date);
$CI->db->where('payment.date <=', $to_date);

if (!empty($branch_patient_ids)) {
    $CI->db->where_in('c.userid', $branch_patient_ids);
}

// Add search condition
if (!empty($search_value)) {
    $CI->db->group_start();
    $CI->db->like('c.company', $search_value);
    $CI->db->or_like('new.mr_no', $search_value);
    $CI->db->or_like('branch.name', $search_value);
   $CI->db->or_like('sources.name', $search_value);
   $CI->db->or_like('item.description', $search_value);
    $CI->db->group_end();
}

$CI->db->group_by('payment.id');
$CI->db->order_by('payment.date', 'DESC');
if ($length != -1) {
    $CI->db->limit($length, $start);
}
$results = $CI->db->get()->result_array();

// ===== Step 4: Build Output Rows =====
$data = [];
$total_package = 0;
$total_paid = 0;
$total_due = 0;

foreach ($results as $row) {
    $userid = $row['userid'];
    $invoiceid = $row['invoiceid'];
    $payment_id = $row['id'];
    $payment_date = $row['date'];
    $current_paid = (float) $row['paid'];

    // Total package
    $package_total = (float) $row['total'];

    // Cumulative paid until this payment
    $CI->db->select_sum('amount');
    $CI->db->from(db_prefix() . 'invoicepaymentrecords');
    $CI->db->where('invoiceid', $invoiceid);
    $CI->db->group_start();
    $CI->db->where('date <', $payment_date);
    $CI->db->or_group_start();
    $CI->db->where('date', $payment_date);
    $CI->db->where('id <=', $payment_id);
    $CI->db->group_end();
    $CI->db->group_end();
    $cumulative_paid_row = $CI->db->get()->row();
    $cumulative_paid = (float) ($cumulative_paid_row->amount ?? 0);

    // Due
    $due = $package_total - $cumulative_paid;

    // Patient package count
    $packageDetailsList = $CI->client_model->get_patient_package_details($userid);
    $packageCount = count($packageDetailsList);

    $CI->db->select('a.*, t.description as treatment_name, type.appointment_type_name')
        ->from(db_prefix() . 'appointment a')
        ->join(db_prefix() . 'items t', 't.id = a.treatment_id', 'left')
        ->join(db_prefix() . 'appointment_type type', 'type.appointment_type_id = a.appointment_type_id', 'left')
        ->where('a.userid', $userid);

    // Match only the DATE part of created_at with $payment_date
    $CI->db->group_start()
        ->where('DATE(a.appointment_date)', $payment_date)
        ->or_where('DATE(a.created_at)', $payment_date)
        ->group_end();

    $CI->db->order_by('a.appointment_date', 'DESC')
        ->limit(1);

    $latestAppointment = $CI->db->get()->row();

    $treatmentName = $latestAppointment ? $latestAppointment->treatment_name : '';
    $appointment_type_name = $latestAppointment ? $latestAppointment->appointment_type_name : '';

    $url = admin_url('client/reports/' . $type . '/' . $row['userid']);
    $company = '<a target="_blank" href="' . $url . '" class="tw-font-medium">' . format_name($row['company']) . '</a>';

    // Totals
    $total_package += $package_total;
    $total_paid += $current_paid;
    $total_due += $due;

    if ($packageCount > 0) {
        $renewal_count = $packageCount - 1;
    } else {
        $renewal_count = 0;
    }

    // Row
    $data[] = [
        $company,
        $row['mr_no'],
        $row['branch_name'],
        $renewal_count,
        $row['patient_source'],
        $row['appointment_type_name'],
        $treatmentName,
        $appointment_type_name,
        get_staff_full_name($row['addedfrom']),
        _d($row['datecreated']),
        get_staff_full_name($row['received_by']),
        _d($row['date']),
        e($row['payment_mode']),
        e($row['utr_no']),
        e($row['package']),
        e(app_format_money_custom($package_total, 1)),
        e(app_format_money_custom($current_paid, 1)),
        e(app_format_money_custom($due, 1)),
    ];
}

// ===== Step 5: Add Totals Row and Return JSON =====
$data[] = [
    '<strong>Total</strong>',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '<strong>' . app_format_money_custom($total_paid, 1) . '</strong>',
    ''
];

echo json_encode([
    'draw' => intval($CI->input->post('draw')),
    'recordsTotal' => $total_filtered,
    'recordsFiltered' => $total_filtered,
    'aaData' => $data,
]);
exit;