<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();
$CI->load->helper('lead_call_log/custom');
$CI->load->model('leads_model');
$statuses = $CI->leads_model->get_status();

$draw   = $CI->input->post('draw');
$start  = $CI->input->post('start');
$length = $CI->input->post('length');
$search = $CI->input->post('search')['value'] ?? '';
$order  = $CI->input->post('order');

$order_col = $order[0]['column'] ?? 0;
$order_dir = $order[0]['dir'] ?? 'desc';
$columns   = $CI->input->post('columns');
$order_col_name = $columns[$order_col]['data'] ?? 'a.appointment_id';

$lead_id = $lead_id ?? 0;
$today   = date('Y-m-d');

// Subquery: total payments per invoice
$CI->db->select('invoiceid, SUM(amount) as paid_total, paymentmode');
$CI->db->from(db_prefix() . 'invoicepaymentrecords');
$CI->db->group_by('invoiceid');
$payments_subquery = $CI->db->get_compiled_select();

// Main Query
$CI->db->select("
    a.*,
    inv.formatted_number AS invoice_number,
    inv.total AS invoice_total,
	inv.Status As payment_status,
    pay.paid_total,
    pay.paid_total AS paid_amount,
    (inv.total - IFNULL(pay.paid_total,0)) AS due_amount,
    pay_user.paymentmode,
    itm.description AS item_description,
	payment_mode.name as payment_type,
	lead.status as lead_status,
	appointment_type.appointment_type_name as appointment_type_name,
	leads_status.name as current_status,
");
$CI->db->from(db_prefix() . 'appointment a');
$CI->db->join(db_prefix() . 'clients c', 'c.userid = a.userid', 'left');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
$CI->db->join(db_prefix() . 'invoices inv', 'inv.id = a.invoice_id', 'right');
$CI->db->join(db_prefix() . 'leads lead', 'lead.id = c.leadid', 'left');
$CI->db->join(db_prefix() . 'leads_status leads_status', 'leads_status.id = lead.status', 'left');
$CI->db->join("({$payments_subquery}) pay", 'pay.invoiceid = inv.id', 'left');
$CI->db->join(db_prefix() . 'appointment_type appointment_type', 'appointment_type.appointment_type_id = a.appointment_type_id', 'left');
$CI->db->join(db_prefix() . 'invoicepaymentrecords pay_user', 'pay_user.invoiceid = inv.id', 'left');
$CI->db->join(db_prefix() . 'itemable itm', 'itm.rel_id = inv.id', 'left');
$CI->db->join(db_prefix() . 'payment_modes payment_mode', 'payment_mode.id = pay.paymentmode', 'left');
$CI->db->where('c.leadid', $lead_id);

if (!empty($search)) {
    $CI->db->group_start();
    $CI->db->like('a.visit_id', $search);
    $CI->db->or_like('inv.formatted_number', $search);
    $CI->db->or_like('a.appointment_date', $search);
    $CI->db->group_end();
}

$total_records = $CI->db->count_all_results('', false);

$CI->db->order_by("a.appointment_id", "desc");
$CI->db->limit($length, $start);

//echo $CI->db->get_compiled_select();
$results = $CI->db->get()->result_array();

$data     = [];
$serial   = $start + 1;

foreach ($results as $row) {
    $aptDate = $row['appointment_date'];
    $branch_id = $row['branch_id'];

    $get_missed_appointment = $CI->db->get_where(db_prefix() . 'master_settings', [
        'title'     => 'missed_appointment',
        'branch_id' => $branch_id
    ])->row();
    $missed_appointment = $get_missed_appointment ? (int)$get_missed_appointment->options : 0;

    // Status Calculation
    $now = strtotime(date('Y-m-d H:i:s'));
    $apt_time = strtotime($aptDate);
    $missed_buffer_time = $apt_time + ($missed_appointment * 60); // add minutes

    /* if (!empty($row['consulted_date'])) {
        $status_key = 'visited';
    } elseif ($now > $missed_buffer_time) {
        $status_key = 'missed';
    } else {
        $status_key = 'upcoming';
    }
	if($row['visit_status'] == 1){
		$status_key = 'visited';
	} */
    // Remaining code untouched...

    $lead_status_name = 'More';
    foreach ($statuses as $s) {
        if ($s['id'] == $row['lead_status']) {
            $lead_status_name = $s['name'];
            break;
        }
    }

    // Dropdown
    $dropdown = '<div class="btn-group" id="lead-more-btn" style="margin-top: 10px">';
    $dropdown .= '<a href="#" class="btn btn-default dropdown-toggle lead-top-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    $dropdown .= $lead_status_name . ' <span class="caret"></span></a>';
    $dropdown .= '<ul class="dropdown-menu dropdown-menu-right" id="lead-more-dropdown">';
    
	if($row['current_status'] == "Missed Appointment"){
		$allowed_statuses = ['no feedback', 'lost', 'prospect']; // lowercase for case-insensitive match
	}else{
		$allowed_statuses = ['lost']; // lowercase for case-insensitive match
	}


	foreach ($statuses as $status) {
		$label = $status['name'];
		$status_id = $status['id'];
		$icon = '';

		// Filter only allowed statuses
		if (!in_array(strtolower($label), $allowed_statuses)) {
			continue;
		}

		switch (strtolower($label)) {
			case 'lost': $icon = 'fa-times-circle'; break;
			case 'prospect': $icon = 'fa-star'; break;
			case 'no feedback': $icon = 'fa-question-circle'; break;
			default: $icon = 'fa-tag';
		}

		$dropdown .= '<li><a href="' . admin_url("lead_call_log/change_status_direct/$lead_id/$status_id") . '">
						 <i class="fa ' . $icon . '"></i> ' . $label . '</a></li>';
	}

    $dropdown .= '</ul></div>';

    // Attachment HTML
    $attachment_html = '';
    if (!empty($row['attachment'])) {
        $attachment_path = base_url($row['attachment']);
        $attachment_html = '<a href="' . $attachment_path . '" target="_blank">
                                <img src="' . $attachment_path . '" alt="Attachment" style="height:40px; border-radius:4px;">
                            </a>';
    }

    // Action Buttons
    $save_send_url = admin_url('lead_call_log/send_payment_request/' . $row['appointment_id'].'/'.$lead_id);
    $reschedule_url = admin_url('client/edit_appointment/' . $row['appointment_id'] . '/lead/' . $lead_id);

    $action_column = '
        <div class="tw-space-y-1">
            <!--<a style="color: #fff;" href="' . $save_send_url . '" class="btn btn-sm btn-primary">Save & Send</a>-->
            <a style="color: #fff;" href="' . $reschedule_url . '" class="btn btn-sm btn-warning">Reschedule Appointment</a>
            ' . $dropdown . '
        </div>';
	
	$status_label = format_invoice_status_custom($row['payment_status']);
    
	$data[] = [
        $serial++,
        e($row['item_description']),
        $row['paid_amount'],
        $row['due_amount'],
        $status_label,
        e($row['payment_type']),
        $attachment_html,
        $row['appointment_type_name'],
        _dt($row['appointment_date']),
        e($row['remarks'] ?? ''),
        format_appointment_status_custom($row['current_status']),
        $action_column
    ];
}



echo json_encode([
    'draw' => intval($draw),
    'iTotalRecords' => $total_records,
    'iTotalDisplayRecords' => $total_records,
    'aaData' => $data,
]);
exit;

