<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payments_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
    }

    /**
     * Get payment by ID
     * @param  mixed $id payment id
     * @return object
     */
    public function get($id)
    {
        $this->db->select('*,' . db_prefix() . 'invoicepaymentrecords.id as paymentid');
        $this->db->join(db_prefix() . 'payment_modes', db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode', 'left');
        $this->db->order_by(db_prefix() . 'invoicepaymentrecords.id', 'asc');
        $this->db->where(db_prefix() . 'invoicepaymentrecords.id', $id);
        $payment = $this->db->get(db_prefix() . 'invoicepaymentrecords')->row();
        if (!$payment) {
            return false;
        }
        // Since version 1.0.1
        $this->load->model('payment_modes_model');
        $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
        if (is_null($payment->id)) {
            foreach ($payment_gateways as $gateway) {
                if ($payment->paymentmode == $gateway['id']) {
                    $payment->name = $gateway['name'];
                }
            }
        }

        return $payment;
    }

    /**
     * Get all invoice payments
     * @param  mixed $invoiceid invoiceid
     * @return array
     */
    public function get_invoice_payments($invoiceid)
    {
        $this->db->select('*,' . db_prefix() . 'invoicepaymentrecords.id as paymentid');
        $this->db->join(db_prefix() . 'payment_modes', db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode', 'left');
        $this->db->order_by(db_prefix() . 'invoicepaymentrecords.id', 'asc');
        $this->db->where('invoiceid', $invoiceid);
        $payments = $this->db->get(db_prefix() . 'invoicepaymentrecords')->result_array();
        // Since version 1.0.1
        $this->load->model('payment_modes_model');
        $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
        $i                = 0;
        foreach ($payments as $payment) {
            if (is_null($payment['id'])) {
                foreach ($payment_gateways as $gateway) {
                    if ($payment['paymentmode'] == $gateway['id']) {
                        $payments[$i]['id']   = $gateway['id'];
                        $payments[$i]['name'] = $gateway['name'];
                    }
                }
            }
            $i++;
        }

        return $payments;
    }

    /**
     * Process invoice payment offline or online
     * @since  Version 1.0.1
     * @param  array $data $_POST data
     * @return boolean
     */
    public function process_payment($data, $invoiceid = '')
    {
        // Offline payment mode from the admin side
        if (is_numeric($data['paymentmode'])) {
            if (is_staff_logged_in()) {
                $id = $this->add($data);

                return $id;
            }

            return false;

        // Is online payment mode request by client or staff
        } elseif (!is_numeric($data['paymentmode']) && !empty($data['paymentmode'])) {
            // This request will come from admin area only
            // If admin clicked the button that dont want to pay the invoice from the getaways only want
            if (is_staff_logged_in() && staff_can('create',  'payments')) {
                if (isset($data['do_not_redirect'])) {
                    $id = $this->add($data);

                    return $id;
                }
            }

            if (!is_numeric($invoiceid)) {
                if (!isset($data['invoiceid'])) {
                    die('No invoice specified');
                }
                $invoiceid = $data['invoiceid'];
            }

            if (isset($data['do_not_send_email_template'])) {
                unset($data['do_not_send_email_template']);
                $this->session->set_userdata([
                    'do_not_send_email_template' => true,
                ]);
            }

            $invoice = $this->invoices_model->get($invoiceid);
            // Check if request coming from admin area and the user added note so we can insert the note also when the payment is recorded
            if (isset($data['note']) && $data['note'] != '') {
                $this->session->set_userdata([
                    'payment_admin_note' => $data['note'],
                ]);
            }

            if (get_option('allow_payment_amount_to_be_modified') == 0) {
                $data['amount'] = get_invoice_total_left_to_pay($invoiceid, $invoice->total);
            }

            $data['invoiceid'] = $invoiceid;
            $data['invoice']   = $invoice;
            $data              = hooks()->apply_filters('before_process_gateway_func', $data);

            $this->load->model('payment_modes_model');
            $gateway = $this->payment_modes_model->get($data['paymentmode']);
            $data['gateway_fee'] = $gateway->instance->getFee($data['amount']);

            $this->load->model('payment_attempts_model');

            $data['payment_attempt'] = $this->payment_attempts_model->add([
                'reference' => app_generate_hash(),
                'amount' => $data['amount'],
                'fee' => $data['gateway_fee'],
                'invoice_id' => $data['invoiceid'],
                'payment_gateway' => $gateway->instance->getId()
            ]);

            $data['amount']     += $data['gateway_fee'];
            $gateway->instance->process_payment($data);
        }

        return false;
    }

    /**
     * Check whether payment exist by transaction id for the given invoice
     *
     * @param  int $transactionId
     * @param  int|null $invoiceId
     *
     * @return bool
     */
    public function transaction_exists($transactionId, $invoiceId = null)
    {
        return total_rows('invoicepaymentrecords', array_filter([
            'transactionid' => $transactionId,
            'invoiceid'     => $invoiceId,
        ])) > 0;
    }

    /**
     * Record new payment
     * @param array $data payment data
     * @return boolean
     */
	 
	 function generate_transaction_id($prefix) {
		$today = date('Ymd'); // Use today's date to keep same base for the day
		$random_number = substr($today, -6); // Last 6 digits from date, or your own logic

		// Find last sequence for today with same prefix+base
		$this->db->select('transactionid');
		$this->db->from('tblinvoicepaymentrecords');
		$this->db->like('transactionid', "{$prefix}{$random_number}_r", 'after');
		$this->db->order_by('transactionid', 'DESC');
		$this->db->limit(1);
		$last = $this->db->get()->row();

		if ($last) {
			preg_match('/_r(\d+)$/', $last->transactionid, $matches);
			$seq = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
		} else {
			$seq = 1;
		}
		$seq = uniqid();
		return "{$prefix}{$random_number}_r{$seq}";
	}


    public function add($data, $subscription = false)
    {
        // Check if field do not redirect to payment processor is set so we can unset from the database
        if (isset($data['do_not_redirect'])) {
            unset($data['do_not_redirect']);
        }
		if (isset($data['record_payment'])) {
            unset($data['record_payment']);
        }
		if (isset($data['submit_action'])) {
            unset($data['submit_action']);
        }

        if ($subscription != false) {
            $after_success = get_option('after_subscription_payment_captured');

            if ($after_success == 'nothing' || $after_success == 'send_invoice') {
                $data['do_not_send_email_template'] = true;
            }
        }
		
		$staff_id = get_staff_user_id();
        if ($staff_id) {
            $this->db->select('branch_id');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where('staffid', $staff_id);
            $row = $this->db->get()->row();
            $branch_id = $row ? $row->branch_id : null;
        }else{
			$branch_id = 1;
		}
		$get_branch_code = $this->db->get_where(db_prefix() . 'master_settings', [
			'title'     => 'branch_code',
			'branch_id' => $branch_id
		])->row();
		$branch_code = $get_branch_code ? $get_branch_code->options : '';
		$data['transactionid'] = $this->generate_transaction_id($branch_code);
        if (isset($data['do_not_send_email_template'])) {
            unset($data['do_not_send_email_template']);
            $do_not_send_email_template = true;
        } elseif ($this->session->has_userdata('do_not_send_email_template')) {
            $do_not_send_email_template = true;
            $this->session->unset_userdata('do_not_send_email_template');
        }

        if (is_staff_logged_in()) {
            if (isset($data['date'])) {
                $data['date'] = to_sql_date($data['date']);
            } else {
                $data['date'] = date('Y-m-d H:i:s');
            }
            if (isset($data['note'])) {
                $data['note'] = nl2br($data['note']);
            } elseif ($this->session->has_userdata('payment_admin_note')) {
                $data['note'] = nl2br($this->session->userdata('payment_admin_note'));
                $this->session->unset_userdata('payment_admin_note');
            }
        } else {
            $data['date'] = date('Y-m-d H:i:s');
        }

        $data['daterecorded'] = date('Y-m-d H:i:s');
        $data['received_by'] = get_staff_user_id();
		
        $data                 = hooks()->apply_filters('before_payment_recorded', $data);

        unset($data['amount_with_fee']);
        $this->db->insert(db_prefix() . 'invoicepaymentrecords', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $invoice      = $this->invoices_model->get($data['invoiceid']);
            $force_update = false;

            if (!class_exists('Invoices_model', false)) {
                $this->load->model('invoices_model');
            }
			$invoice_due_date = $invoice->duedate;
            if ($invoice->status == Invoices_model::STATUS_DRAFT) {
                $force_update = true;
                // update invoice number for invoice with draft - V2.7.2
                $this->invoices_model->change_invoice_number_when_status_draft($invoice->id);
            }

            update_invoice_status($data['invoiceid'], $force_update);
            $this->invoices_model->save_formatted_number($invoice->id);

            $activity_lang_key = 'invoice_activity_payment_made_by_staff';
            if (!is_staff_logged_in()) {
                $activity_lang_key = 'invoice_activity_payment_made_by_client';
            }

            $this->invoices_model->log_invoice_activity($data['invoiceid'], $activity_lang_key, !is_staff_logged_in() ? true : false, serialize([
                app_format_money($data['amount'], $invoice->currency_name),
                '<a href="' . admin_url('payments/payment/' . $insert_id) . '" target="_blank">#' . $insert_id . '</a>',
            ]));

            log_activity('Payment Recorded [ID:' . $insert_id . ', Invoice Number: ' . format_invoice_number($invoice->id) . ', Total: ' . app_format_money($data['amount'], $invoice->currency_name) . ']');

            // Send email to the client that the payment is recorded
            $payment               = $this->get($insert_id);
            $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);
            set_mailing_constant();
            $paymentpdf           = payment_pdf($payment);
            $payment_pdf_filename = mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf';
            $attach               = $paymentpdf->Output($payment_pdf_filename, 'S');

            if (!isset($do_not_send_email_template)
                || ($subscription != false && $after_success == 'send_invoice_and_receipt')
                || ($subscription != false && $after_success == 'send_invoice')
            ) {
                $template_name        = 'invoice_payment_recorded_to_customer';
                $pdfInvoiceAttachment = false;
                $attachPaymentReceipt = true;
                $emails_sent          = [];

                $where = ['active' => 1, 'invoice_emails' => 1];

                if ($subscription != false) {
                    $where['is_primary'] = 1;
                    $template_name       = 'subscription_payment_succeeded';

                    if ($after_success == 'send_invoice_and_receipt' || $after_success == 'send_invoice') {
                        $invoice_number = format_invoice_number($payment->invoiceid);
                        set_mailing_constant();
                        $pdfInvoice           = invoice_pdf($payment->invoice_data);
                        $pdfInvoiceAttachment = $pdfInvoice->Output($invoice_number . '.pdf', 'S');

                        if ($after_success == 'send_invoice') {
                            $attachPaymentReceipt = false;
                        }
                    }
                    // Is from settings: Send Payment Receipt
                } else {
                    if (get_option('attach_invoice_to_payment_receipt_email') == 1) {
                        $invoice_number = format_invoice_number($payment->invoiceid);
                        set_mailing_constant();
                        $pdfInvoice           = invoice_pdf($payment->invoice_data);
                        $pdfInvoiceAttachment = $pdfInvoice->Output($invoice_number . '.pdf', 'S');
                    }
                }

                $contacts = $this->clients_model->get_contacts($invoice->clientid, $where);

                foreach ($contacts as $contact) {
                    $template = mail_template(
                        $template_name,
                        $contact,
                        $invoice,
                        $subscription,
                        $payment->paymentid
                    );

                    if ($attachPaymentReceipt) {
                        $template->add_attachment([
                                'attachment' => $attach,
                                'filename'   => $payment_pdf_filename,
                                'type'       => 'application/pdf',
                            ]);
                    }

                    if ($pdfInvoiceAttachment) {
                        $template->add_attachment([
                            'attachment' => $pdfInvoiceAttachment,
                            'filename'   => str_replace('/', '-', $invoice_number) . '.pdf',
                            'type'       => 'application/pdf',
                        ]);
                    }
                    $merge_fields = $template->get_merge_fields();

                    if ($template->send()) {
                        array_push($emails_sent, $contact['email']);
                    }

                    $this->app_sms->trigger(SMS_TRIGGER_PAYMENT_RECORDED, $contact['phonenumber'], $merge_fields);
                }

                if (count($emails_sent) > 0) {
                    $additional_activity_data = serialize([
                       implode(', ', $emails_sent),
                     ]);
                    $activity_lang_key = 'invoice_activity_record_payment_email_to_customer';
                    if ($subscription != false) {
                        $activity_lang_key = 'invoice_activity_subscription_payment_succeeded';
                    }
                    $this->invoices_model->log_invoice_activity($invoice->id, $activity_lang_key, false, $additional_activity_data);
                }
            }

            $this->db->where('staffid', $invoice->addedfrom);
            $this->db->or_where('staffid', $invoice->sale_agent);
            $staff_invoice = $this->db->get(db_prefix() . 'staff')->result_array();

            $notifiedUsers = [];
            foreach ($staff_invoice as $member) {
                if (get_option('notification_when_customer_pay_invoice') == 1) {
                    if (is_staff_logged_in() && $member['staffid'] == get_staff_user_id()) {
                        continue;
                    }
                    // E.q. had permissions create not don't have, so we must re-check this
                    if (user_can_view_invoice($invoice->id, $member['staffid'])) {
                        $notified = add_notification([
                        'fromcompany'     => true,
                        'touserid'        => $member['staffid'],
                        'description'     => 'not_invoice_payment_recorded',
                        'link'            => 'payments/payment/' . $insert_id,
                        'additional_data' => serialize([
                            format_invoice_number($invoice->id),
                        ]),
                    ]);
                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                        send_mail_template(
                            'invoice_payment_recorded_to_staff',
                            $member['email'],
                            $member['staffid'],
                            $invoice,
                            $attach,
                            $payment->paymentid
                        );
                    }
                }
            }

            pusher_trigger_notification($notifiedUsers);

            hooks()->do_action('after_payment_added', $insert_id);
			
			$clientid = $invoice->clientid;
			
			if($data['amount']>0){
				$check = $this->db->get_where(db_prefix() . 'itemable', array("rel_id"=>$invoice->id, "rel_type"=>'invoice', "description !="=>'Consultation Fee'))->row();
				if($check){
					$status_name = "Patient Registered";
					$check_status = $this->db->get_where(db_prefix() . 'leads_status', array("name"=>$status_name))->row();
					$status_id = $check_status->id;
					
					$check_status = $this->db->get_where(db_prefix() . 'lead_patient_journey', array("userid"=>$clientid, "status"=>$status_id))->row();
					if(!$check_status){
						$count = 0;
						$this->load->model('client/client_model');
				
						$add_lead_patient_status = array(
							"userid" => $clientid,
							"status" => $status_id,
							"datetime" => date('Y-m-d H:i:s')
						);
						$this->client_model->add_lead_patient_status($add_lead_patient_status);
						
						$staff_id = get_staff_user_id();
						if ($staff_id) {
							$this->db->select('branch_id');
							$this->db->from(db_prefix() . 'staff');
							$this->db->where('staffid', $staff_id);
							$row = $this->db->get()->row();
							$branch_id = $row->branch_id;
						}else{
							$branch_id = 1;
						}
						
						$this->db->from(db_prefix() . 'clients_new_fields as new');
						$this->db->join(db_prefix() . 'customer_groups as cg', 'cg.customer_id = new.userid', 'inner');

						// Check mr_no is not null and not empty
						$this->db->where('new.mr_no IS NOT NULL', null, false);
						$this->db->where("new.mr_no != ''");

						// Filter by branch
						$this->db->where('cg.groupid', $branch_id);

						$count = $this->db->count_all_results();

						$get_branch_code = $this->db->get_where(db_prefix() . 'master_settings', [
							'title'     => 'branch_code',
							'branch_id' => $branch_id
						])->row();
						$branch_code = $get_branch_code ? $get_branch_code->options : '';

						$get_branch_short_code = $this->db->get_where(db_prefix() . 'master_settings', [
							'title'     => 'branch_short_code',
							'branch_id' => $branch_id
						])->row();
						$branch_short_code = $get_branch_short_code ? $get_branch_short_code->options : '';
						
						if($count){
								$number = $branch_short_code . rand(1,99) . '-' .($count + 1);

							}else{
								$number = $branch_short_code . rand(1,99) . '-' . '1';
							}
						$mr_no = $number;
						
						$this->db->from(db_prefix() . 'clients_new_fields');
						$this->db->where('userid', $clientid);

						// Check for NULL or empty string
						$this->db->group_start();
						$this->db->where('mr_no IS NULL', null, false);
						$this->db->or_where('mr_no', '');
						$this->db->group_end();

						$check_mr_no = $this->db->count_all_results();
						
						$this->db->where('userid', $clientid);
						$this->db->order_by('id', 'DESC'); // latest first
						$this->db->limit(1);
						$treatment = $this->db->get(db_prefix() . 'patient_treatment')->row();
						if($treatment){
							$created_at = $treatment->created_at;
							$duration_value = (int) $treatment->duration_value; // months
							$start_date = new DateTime(); // today
							$end_date = (clone $start_date)->modify("+{$duration_value} months");

							$registration_start_date = $start_date->format('Y-m-d'); // format as needed
							$registration_end_date = $invoice_due_date; // format as needed
							
							
						}else{
							$registration_start_date = date('Y-m-d');
							$registration_end_date = date('Y-m-d');
						}
						
						
						if($check_mr_no){
							$update_client = array(
							"mr_no" => $mr_no,
							"registration_start_date" => $registration_start_date,
							"registration_end_date" => $registration_end_date,
							);
							$this->db->where(array("userid" => $clientid));
							$this->db->update(db_prefix() . 'clients_new_fields', $update_client);
						}
						
						$branch_address = get_option('invoice_company_address');
						$package_cost = $check->rate;
						$paid_amount = $data['amount'];
						$communication_data = array(
						"mr_number" => $mr_no,
						"package_cost" => $package_cost,
						"paid_amount" => $paid_amount,
						"branch_address" => $branch_address,
						);
						$this->client_model->patient_journey_log_event($clientid, $status_name, $status_name, $communication_data, $status_id);
						
					}
					
					
				}
			}
			

            return $insert_id;
        }

        return false;
    }

    /**
     * Update payment
     * @param  array $data payment data
     * @param  mixed $id   paymentid
     * @return boolean
     */
    public function update($data, $id)
    {
        $payment      = $this->get($id);
        $updated      = false;
        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);

        $data = hooks()->apply_filters('before_payment_updated', $data, $id);

        $this->db->where('id', $id);
        $this->db->update('invoicepaymentrecords', $data);

        if ($this->db->affected_rows() > 0) {
            if ($data['amount'] != $payment->amount) {
                update_invoice_status($payment->invoiceid);
            }

            $updated = true;
        }

        hooks()->do_action('after_payment_updated', [
            'id'      => $id,
            'data'    => $data,
            'payment' => $payment,
            'updated' => &$updated,
        ]);

        if ($updated) {
            log_activity('Payment Updated [Number:' . $id . ']');
        }

        return $updated;
    }

    /**
     * Delete payment from database
     * @param  mixed $id paymentid
     * @return boolean
     */
    public function delete($id)
    {
        $current         = $this->get($id);
        $current_invoice = $this->invoices_model->get($current->invoiceid);
        $invoiceid       = $current->invoiceid;
        hooks()->do_action('before_payment_deleted', [
            'paymentid' => $id,
            'invoiceid' => $invoiceid,
        ]);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'invoicepaymentrecords');
        if ($this->db->affected_rows() > 0) {
            update_invoice_status($invoiceid);
            $this->invoices_model->log_invoice_activity($invoiceid, 'invoice_activity_payment_deleted', false, serialize([
                $current->paymentid,
                app_format_money($current->amount, $current_invoice->currency_name),
            ]));
            log_activity('Payment Deleted [ID:' . $id . ', Invoice Number: ' . format_invoice_number($current->id) . ']');

            hooks()->do_action('after_payment_deleted', [
                'paymentid' => $id,
                'invoiceid' => $invoiceid,
            ]);

            return true;
        }

        return false;
    }

    public function add_batch_payment($paymentsData)
    {
        $sendBatchPaymentEmail = true;
        if (isset($paymentsData['do_not_send_invoice_payment_recorded'])) {
            $sendBatchPaymentEmail = false;
        }

        $paymentIds = [];
        foreach ($paymentsData['invoice'] as $data) {
            if (empty($data['invoiceid']) || empty($data['amount']) || empty($data['date']) || empty('paymentmode')) {
                continue;
            }

            $data['date']         = to_sql_date($data['date']);
            $data['daterecorded'] = date('Y-m-d H:i:s');
			$data['received_by'] = get_staff_user_id();
            $data                 = hooks()->apply_filters('before_payment_recorded', $data);

            $this->db->insert(db_prefix() . 'invoicepaymentrecords', $data);
            $insert_id = $this->db->insert_id();

            if ($insert_id) {
                $paymentIds[] = $insert_id;
                $invoice      = $this->invoices_model->get($data['invoiceid']);
                $force_update = false;

                if (!class_exists('Invoices_model', false)) {
                    $this->load->model('invoices_model');
                }
                
                if ($invoice->status == Invoices_model::STATUS_DRAFT) {
                    $force_update = true;
                    // update invoice number for invoice with draft - V2.7.2
                    $this->invoices_model->change_invoice_number_when_status_draft($invoice->id);
                }

                update_invoice_status($data['invoiceid'], $force_update);
                $this->invoices_model->save_formatted_number($invoice->id);

                $this->invoices_model->log_invoice_activity(
                    $data['invoiceid'],
                    'invoice_activity_payment_made_by_staff',
                    false,
                    serialize([
                        app_format_money($data['amount'], $invoice->currency_name),
                        '<a href="' . admin_url('payments/payment/' . $insert_id) . '" target="_blank">#' . $insert_id . '</a>',
                    ])
                );
                log_activity('Payment Recorded [ID:' . $insert_id . ', Invoice Number: ' . format_invoice_number($invoice->id) . ', Total: ' . app_format_money(
                    $data['amount'],
                    $invoice->currency_name
                ) . ']');
            }
            hooks()->do_action('after_payment_added', $insert_id);
        }

        if (count($paymentIds) > 0 && $sendBatchPaymentEmail) {
            $this->send_batch_payment_notification_to_customers($paymentIds);
        }

        return count($paymentIds);
    }

    private function send_batch_payment_notification_to_customers($paymentIds)
    {
        $paymentData = $this->db
            ->select(db_prefix() . 'invoicepaymentrecords.*,' . db_prefix() . 'invoices.currency,' . db_prefix() . 'invoices.clientId,' . db_prefix() . 'invoices.hash')
            ->join(db_prefix() . 'invoices', 'invoicepaymentrecords.invoiceid=invoices.id')
            ->where_in('invoicepaymentrecords.id', $paymentIds)
            ->get(db_prefix() . 'invoicepaymentrecords')
            ->result();

        // used collection groupBy as a workaround for mysql8.0 only full group mode
        $paymentData = collect($paymentData)->groupBy('clientId');

        foreach ($paymentData as $clientId => $payments) {
            $contacts = $this->get_contacts_for_payment_emails($clientId);
            foreach ($contacts as $contact) {
                if (count($payments) === 1) {
                    $this->send_invoice_payment_recorded($payments[0]->id, $contact);
                } else {
                    $template = mail_template('invoice_batch_payments', $payments, $contact);
                    foreach ($payments as $payment) {
                        $payment               = $this->get($payment->id);
                        $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);
                        $template              = $this->_add_payment_mail_attachments_to_template($template, $payment);
                    }
                    $template->send();
                }
            }
        }
    }

    public function send_invoice_payment_recorded($id, $contact)
    {
        if (!class_exists('Invoices_model', false)) {
            $this->load->model('invoices_model');
        }

        // to get structure matching payment_pdf()
        $payment               = $this->get($id);
        $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);
        $template              = mail_template('invoice_payment_recorded_to_customer', (array) $contact, $payment->invoice_data, false, $id);
        $template              = $this->_add_payment_mail_attachments_to_template($template, $payment);

        return $template->send();
    }

    private function _add_payment_mail_attachments_to_template($template, $payment)
    {
        set_mailing_constant();

        $paymentPDF = payment_pdf($payment);
        $filename   = mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf';
        $attach     = $paymentPDF->Output($filename, 'S');
        $template->add_attachment([
            'attachment' => $attach,
            'filename'   => $filename,
            'type'       => 'application/pdf',
        ]);

        if (get_option('attach_invoice_to_payment_receipt_email') == 1) {
            $invoice_number = format_invoice_number($payment->invoiceid);
            set_mailing_constant();
            $pdfInvoice           = invoice_pdf($payment->invoice_data);
            $pdfInvoiceAttachment = $pdfInvoice->Output($invoice_number . '.pdf', 'S');

            $template->add_attachment([
                'attachment' => $pdfInvoiceAttachment,
                'filename'   => str_replace('/', '-', $invoice_number) . '.pdf',
                'type'       => 'application/pdf',
            ]);
        }

        return $template;
    }

    private function get_contacts_for_payment_emails($client_id)
    {
        if (!class_exists('Clients_model', false)) {
            $this->load->model('clients_model');
        }

        return $this->clients_model->get_contacts($client_id, [
            'active' => 1, 'invoice_emails' => 1,
        ]);
    }
}
