<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Client extends AdminController
{
	/**
     * Stores the pusher options.
     *
     * @var array
     */
    protected $pusher_options = [];

    /**
     * Hold Pusher instance.
     *
     * @var object
     */
    protected $pusher;

	private $current_branch_id;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('client_model');
        $this->load->model('master_model');
        $this->load->model('doctor_model');
        $this->load->helper('custom'); // loads custom_helper.php
		$this->current_branch_id = $this->client_model->get_logged_in_staff_branch_id();
		
		$this->pusher_options['app_key'] = get_option('pusher_app_key');
        $this->pusher_options['app_secret'] = get_option('pusher_app_secret');
        $this->pusher_options['app_id'] = get_option('pusher_app_id');
		
		if (get_option('pusher_cluster') != '') {
            $this->pusher_options['cluster'] = get_option('pusher_cluster');
        }
        $this->pusher = new Pusher\Pusher(
            $this->pusher_options['app_key'],
            $this->pusher_options['app_secret'],
            $this->pusher_options['app_id'],
            ['cluster' => $this->pusher_options['cluster']]
        );
		
		error_reporting(0);

    }
    /* List all clients */
    public function index($id = '', $tab = '')
    {   
        if (staff_cant('view', 'customers') && staff_cant('view_own', 'customers')) {
            //if (!have_assigned_customers() && staff_cant('create', 'customers')) {
                access_denied('patients');
           // }
        }

        $this->load->model('contracts_model');
        $data['contract_types'] = $this->contracts_model->get_contract_types();
        $data['groups']         = $this->client_model->get_groups();
        $data['title']          = _l('clients');

        $this->load->model('proposals_model');
        $data['proposal_statuses'] = $this->proposals_model->get_statuses();

        $this->load->model('invoices_model');
        $data['invoice_statuses'] = $this->invoices_model->get_statuses();

        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();

        $this->load->model('projects_model');
        $data['project_statuses'] = $this->projects_model->get_project_statuses();

        $data['customer_admins'] = $this->client_model->get_customers_admin_unique_ids();

        $whereContactsLoggedIn = '';
        if (staff_cant('view', 'customers')) {
            $whereContactsLoggedIn = ' AND userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
        }

        $data['contacts_logged_in_today'] = $this->client_model->get_contacts('', 'last_login LIKE "' . date('Y-m-d') . '%"' . $whereContactsLoggedIn);

        $data['countries'] = $this->client_model->get_clients_distinct_countries();
        $data['table'] = App_table::find('clients');
        
        $data['clientid'] = $id;
        function get_counter_by_doctor_id($doctor_id)
		{
			$CI =& get_instance();
			$CI->db->where('doctor_id', $doctor_id);
			return $CI->db->get(db_prefix() . 'counter')->row(); // returns single row (object)
		}
        if ($id) {
			 $this->load->model('currencies_model');
			 $this->load->model('taxes_model');
			 $this->load->model('invoice_items_model');
			 $this->load->model('estimates_model');
			$data['clientid'] = $id;
            // Fetch the existing patient data
            $client = $this->client_model->get($id);
            $estimates = $this->client_model->get_estimates($id);
			foreach ($estimates as &$estimate) {
				$this->db->select('description');
				$this->db->where('rel_type', 'estimate');
				$this->db->where('rel_id', $estimate['id']);
				$items = $this->db->get('tblitemable')->row();
				
				$estimate['description'] = $items->description; // append to estimate
			}
			
            $customer_new_fields = $this->client_model->get_customer_new_fields($id);
			$currencies= $this->currencies_model->get();
			$taxes = $this->taxes_model->get();
			$items_groups = $this->invoice_items_model->get_groups();
			$staff            = $this->staff_model->get('', ['active' => 1]);
			$estimate_statuses = $this->estimates_model->get_statuses();
			$base_currency = $this->currencies_model->get_base_currency();
			
			$items = $this->invoice_items_model->get_grouped();
            $appointment_data = $this->client_model->get_appointment_data($id);
            $patient_activity_log = $this->client_model->get_patient_activity_log($id);
            $patient_prescription = $this->client_model->get_patient_prescription($id);
			
            $patient_treatment = $this->client_model->get_patient_treatment($id);
            $casesheet = $this->client_model->get_casesheet($id);
            // Fetch patient call logs
            $patient_call_logs = $this->client_model->get_patient_call_logs($id); // NEW
            $invoices = $this->client_model->get_invoices($id); // NEW
            $invoice_payments = $this->client_model->get_invoice_payments($id); // NEW
            $shared_requests = $this->client_model->get_shared_requests($id); // NEW

            // Fetch medicine data (names, potencies, doses, timings)
            $medicines = $this->master_model->get_all('medicine');
            $potencies = $this->master_model->get_all('medicine_potency');
            $doses = $this->master_model->get_all('medicine_dose');
            $timings = $this->master_model->get_all('medicine_timing');
            $appointment_type = $this->master_model->get_all('appointment_type');
            $criteria = $this->master_model->get_all('criteria');
            $treatments = $this->master_model->get_all('treatment');
            $patient_status = $this->master_model->get_all('patient_status');
            $master_settings = $this->master_model->get_all('master_settings');
			$testimonials = $this->client_model->get_testimonial();
			
			function get_estimation_payment_summary($estimation_id)
			{
				$CI =& get_instance();

				// 1. Get the estimate row
				$CI->db->select('total, invoiceid, currency, date, expirydate');
				$CI->db->where('id', $estimation_id);
				$estimate = $CI->db->get(db_prefix() . 'estimates')->row();

				if (!$estimate || !$estimate->invoiceid) {
					return [
						'total' => 0,
						'paid' => 0,
						'dues' => 0,
						'currency' => '',
						'invoice_id' => null,
					];
				}

				// 2. Sum payments from invoicepaymentrecords
				$CI->db->select_sum('amount');
				$CI->db->where('invoiceid', $estimate->invoiceid);
				$paid_row = $CI->db->get(db_prefix() . 'invoicepaymentrecords')->row();
				$paid = $paid_row ? (float)$paid_row->amount : 0;

				return [
					'total'     => (float)$estimate->total,
					'paid'      => $paid,
					'dues'      => (float)$estimate->total - $paid,
					'currency'  => $estimate->currency,
					'invoice_id'=> $estimate->invoiceid,
					'date'=> $estimate->date,
					'expirydate'=> $estimate->expirydate,
				];
			}
			$branch = $this->client_model->get_branch();
            // Pass the data to the view
            $data['client_modal'] = $this->load->view('client_model_popup', [
                'estimates' => $estimates,
                'branch' => $branch,
                'client' => $client,
                'casesheet' => $casesheet,
                'testimonials' => $testimonials,
                'shared_requests' => $shared_requests,
                'master_settings' => $master_settings,
                'customer_new_fields' => $customer_new_fields,
                'currencies' => $currencies,
                'taxes' => $taxes,
                'items' => $items,
                'base_currency' => $base_currency,
                'items_groups' => $items_groups,
                'staff' => $staff,
                'estimate_statuses' => $estimate_statuses,
                'appointment_data' => $appointment_data,
                'patient_activity_log' => $patient_activity_log,
                'patient_call_logs' => $patient_call_logs, // NEW
                'patient_prescriptions' => $patient_prescription, // NEW
                'patient_treatment' => $patient_treatment, // NEW
                'medicines' => $medicines, // NEW
                'potencies' => $potencies, // NEW
                'appointment_type' => $appointment_type, // NEW
                'criteria' => $criteria, // NEW
                'doses' => $doses, // NEW
                'treatments' => $treatments, // NEW
                'patient_status' => $patient_status, // NEW
                'invoices' => $invoices, // NEW
                'invoice_payments' => $invoice_payments, // NEW
                'timings' => $timings // NEW
            ], true);
        }
        
        $this->load->view('manage', $data);
    }

    public function table()
    {
        if (staff_cant('view', 'customers')) {
            if (!have_assigned_customers() && staff_cant('create', 'customers')) {
                ajax_access_denied();
            }
        }

        App_table::find('clients')->output();
    }

    public function all_contacts()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('all_contacts');
        }

        if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }

        $data['title'] = _l('customer_contacts');
        $this->load->view('admin/clients/all_contacts', $data);
    }

    /*add new client*/
    public function add_client($id = '', $type = '')
    {
        if (staff_cant('create', 'customers')) {
            if ($id != '' && !is_customer_admin($id)) {
                access_denied('customers');
            }
        }
		$data['patient_inactive_fields'] = $this->client_model->patient_inactive_fields();
		$data['current_branch_id'] = $this->current_branch_id;
		
		$this->load->model('leads_model');
		//$data['states']  = $this->leads_model->get_state();
		//$data['cities']  = $this->leads_model->get_city();
		//$data['pincodes']  = $this->client_model->get_pincodes();
		
		$this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
		
		$this->load->model('invoice_items_model');
		$data['items'] = $this->invoice_items_model->get_grouped();
		if($id != NULL){
			if($type != NULL){
				if($type == "Patient"){
					$existing_patient = $this->client_model->get($id);
					$data['patient_data'] = $existing_patient;
					$data['master_data'] = $this->load_master_data();
					
					$data['title'] = 'Enquiry Form';
					$this->load->view('client_form', $data);
				}else if($type == "Lead"){
					$existing_patient = $this->client_model->get_lead($id);
					$data['patient_data'] = $existing_patient;
					$data['master_data'] = $this->load_master_data();
					$data['title'] = 'Lead Form';
					$this->load->view('lead_form', $data);
				}
				
			}
			
            
		}else{
			$data['title'] = 'Enquiry Form';
			$this->load->view('client_form', $data);
		}
		

    }
	
	public function new_patient($id = '', $type = '')
    {
        if (staff_cant('create', 'customers')) {
            if ($id != '' && !is_customer_admin($id)) {
                access_denied('customers');
            }
        }
		 $data['contact_number'] = $this->input->get('contact_number');
		$this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
		$this->load->model('invoice_items_model');
		$data['items'] = $this->invoice_items_model->get_grouped();
		
		$data['master_data'] = $this->load_master_data();
		$data['title'] = 'Enquiry Form';
		$data['current_branch_id'] = $this->current_branch_id;
		
		$this->load->model('leads_model');
		$data['states']  = $this->leads_model->get_state();
		$data['cities']  = $this->leads_model->get_city();
		$this->load->view('new_patient_form', $data);
		

    }

    private function load_master_data()
    {
        return $this->client_model->get_master_data();
    }

    public function export($contact_id)
    {
        if (is_admin()) {
            $this->load->library('gdpr/gdpr_contact');
            $this->gdpr_contact->export($contact_id);
        }
    }

    // Used to give a tip to the user if the company exists when new company is created
    public function check_duplicate_customer_name()
    {
        if (staff_can('create',  'customers')) {
            $companyName = trim($this->input->post('company'));
            $response    = [
                'exists'  => (bool) total_rows(db_prefix() . 'clients', ['company' => $companyName]) > 0,
                'message' => _l('company_exists_info', '<b>' . $companyName . '</b>'),
            ];
            echo json_encode($response);
        }
    }

    public function save_longitude_and_latitude($client_id)
    {
        if (staff_cant('edit', 'customers')) {
            if (!is_customer_admin($client_id)) {
                ajax_access_denied();
            }
        }

        $this->db->where('userid', $client_id);
        $this->db->update(db_prefix() . 'clients', [
            'longitude' => $this->input->post('longitude'),
            'latitude'  => $this->input->post('latitude'),
        ]);
        if ($this->db->affected_rows() > 0) {
            echo 'success';
        } else {
            echo 'false';
        }
    }

    public function form_contact($customer_id, $contact_id = '')
    {
        if (staff_cant('view', 'customers')) {
            if (!is_customer_admin($customer_id)) {
                echo _l('access_denied');
                die;
            }
        }
        $data['customer_id'] = $customer_id;
        $data['contactid']   = $contact_id;

        if (is_automatic_calling_codes_enabled()) {
            $clientCountryId = $this->db->select('country')
                ->where('userid', $customer_id)
                ->get('clients')->row()->country ?? null;

            $clientCountry = get_country($clientCountryId);
            
            $callingCode   = $clientCountry->calling_code ? 
                ($clientCountry ? '+' . ltrim($clientCountry->calling_code, '+') : null) : 
                null;
        } else {
            $callingCode = null;
        }

        if ($this->input->post()) {
            $data             = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            if ($callingCode && !empty($data['phonenumber']) && $data['phonenumber'] == $callingCode) {
                $data['phonenumber'] = '';
            }

            unset($data['contactid']);

            if ($contact_id == '') {
                if (staff_cant('create', 'customers')) {
                    if (!is_customer_admin($customer_id)) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
                        echo json_encode([
                            'success' => false,
                            'message' => _l('access_denied'),
                        ]);
                        die;
                    }
                }
                $id      = $this->client_model->add_contact($data, $customer_id);
                $message = '';
                $success = false;
                if ($id) {
                    handle_contact_profile_image_upload($id);
                    $success = true;
                    $message = _l('added_successfully', _l('contact'));
                }
                echo json_encode([
                    'success'             => $success,
                    'message'             => $message,
                    'has_primary_contact' => (total_rows(db_prefix() . 'contacts', ['userid' => $customer_id, 'is_primary' => 1]) > 0 ? true : false),
                    'is_individual'       => is_empty_customer_company($customer_id) && total_rows(db_prefix() . 'contacts', ['userid' => $customer_id]) == 1,
                ]);
                die;
            }
            if (staff_cant('edit', 'customers')) {
                if (!is_customer_admin($customer_id)) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
                    echo json_encode([
                            'success' => false,
                            'message' => _l('access_denied'),
                        ]);
                    die;
                }
            }
            $original_contact = $this->client_model->get_contact($contact_id);
            $success          = $this->client_model->update_contact($data, $contact_id);
            $message          = '';
            $proposal_warning = false;
            $original_email   = '';
            $updated          = false;
            if (is_array($success)) {
                if (isset($success['set_password_email_sent'])) {
                    $message = _l('set_password_email_sent_to_client');
                } elseif (isset($success['set_password_email_sent_and_profile_updated'])) {
                    $updated = true;
                    $message = _l('set_password_email_sent_to_client_and_profile_updated');
                }
            } else {
                if ($success == true) {
                    $updated = true;
                    $message = _l('updated_successfully', _l('contact'));
                }
            }
            if (handle_contact_profile_image_upload($contact_id) && !$updated) {
                $message = _l('updated_successfully', _l('contact'));
                $success = true;
            }
            if ($updated == true) {
                $contact = $this->client_model->get_contact($contact_id);
                if (total_rows(db_prefix() . 'proposals', [
                        'rel_type' => 'customer',
                        'rel_id' => $contact->userid,
                        'email' => $original_contact->email,
                    ]) > 0 && ($original_contact->email != $contact->email)) {
                    $proposal_warning = true;
                    $original_email   = $original_contact->email;
                }
            }
            echo json_encode([
                    'success'             => $success,
                    'proposal_warning'    => $proposal_warning,
                    'message'             => $message,
                    'original_email'      => $original_email,
                    'has_primary_contact' => (total_rows(db_prefix() . 'contacts', ['userid' => $customer_id, 'is_primary' => 1]) > 0 ? true : false),
                ]);
            die;
        }


        $data['calling_code'] = $callingCode;

        if ($contact_id == '') {
            $title = _l('add_new', _l('contact'));
        } else {
            $data['contact'] = $this->client_model->get_contact($contact_id);

            if (!$data['contact']) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
                echo json_encode([
                    'success' => false,
                    'message' => 'Contact Not Found',
                ]);
                die;
            }
            $title = $data['contact']->firstname . ' ' . $data['contact']->lastname;
        }

        $data['customer_permissions'] = get_contact_permissions();
        $data['title']                = $title;
        $this->load->view('admin/clients/modals/contact', $data);
    }

    public function confirm_registration($client_id)
    {
        if (!is_admin()) {
            access_denied('Customer Confirm Registration, ID: ' . $client_id);
        }
        $this->client_model->confirm_registration($client_id);
        set_alert('success', _l('customer_registration_successfully_confirmed'));
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function update_file_share_visibility()
    {
        if ($this->input->post()) {
            $file_id           = $this->input->post('file_id');
            $share_contacts_id = [];

            if ($this->input->post('share_contacts_id')) {
                $share_contacts_id = $this->input->post('share_contacts_id');
            }

            $this->db->where('file_id', $file_id);
            $this->db->delete(db_prefix() . 'shared_customer_files');

            foreach ($share_contacts_id as $share_contact_id) {
                $this->db->insert(db_prefix() . 'shared_customer_files', [
                    'file_id'    => $file_id,
                    'contact_id' => $share_contact_id,
                ]);
            }
        }
    }

    public function delete_contact_profile_image($contact_id)
    {
        $this->client_model->delete_contact_profile_image($contact_id);
    }

    public function mark_as_active($id)
    {
        $this->db->where('userid', $id);
        $this->db->update(db_prefix() . 'clients', [
            'active' => 1,
        ]);
        redirect(admin_url('clients/client/get_patient_list' . $id));
    }

    public function consents($id)
    {
        if (staff_cant('view', 'customers')) {
            if (!is_customer_admin(get_user_id_by_contact_id($id))) {
                echo _l('access_denied');
                die;
            }
        }

        $this->load->model('gdpr_model');
        $data['purposes']   = $this->gdpr_model->get_consent_purposes($id, 'contact');
        $data['consents']   = $this->gdpr_model->get_consents(['contact_id' => $id]);
        $data['contact_id'] = $id;
        $this->load->view('admin/gdpr/contact_consent', $data);
    }

    public function update_all_proposal_emails_linked_to_customer($contact_id)
    {
        $success = false;
        $email   = '';
        if ($this->input->post('update')) {
            $this->load->model('proposals_model');

            $this->db->select('email,userid');
            $this->db->where('id', $contact_id);
            $contact = $this->db->get(db_prefix() . 'contacts')->row();

            $proposals = $this->proposals_model->get('', [
                'rel_type' => 'customer',
                'rel_id'   => $contact->userid,
                'email'    => $this->input->post('original_email'),
            ]);
            $affected_rows = 0;

            foreach ($proposals as $proposal) {
                $this->db->where('id', $proposal['id']);
                $this->db->update(db_prefix() . 'proposals', [
                    'email' => $contact->email,
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }
            }

            if ($affected_rows > 0) {
                $success = true;
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => _l('proposals_emails_updated', [
                _l('contact_lowercase'),
                $contact->email,
            ]),
        ]);
    }

    public function assign_admins($id)
    {
        if (staff_cant('create', 'customers') && staff_cant('edit', 'customers')) {
            access_denied('customers');
        }
        $success = $this->client_model->assign_admins($this->input->post(), $id);
        if ($success == true) {
            set_alert('success', _l('updated_successfully', _l('client')));
        }

        redirect(admin_url('clients/client/' . $id . '?tab=customer_admins'));
    }

    public function delete_customer_admin($customer_id, $staff_id)
    {
        if (staff_cant('create', 'customers') && staff_cant('edit', 'customers')) {
            access_denied('customers');
        }

        $this->db->where('customer_id', $customer_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->delete(db_prefix() . 'customer_admins');
        redirect(admin_url('clients/client/' . $customer_id) . '?tab=customer_admins');
    }

    public function delete_contact($customer_id, $id)
    {
        if (staff_cant('delete', 'customers')) {
            if (!is_customer_admin($customer_id)) {
                access_denied('customers');
            }
        }
        $contact      = $this->client_model->get_contact($id);
        $hasProposals = false;
        if ($contact && is_gdpr()) {
            if (total_rows(db_prefix() . 'proposals', ['email' => $contact->email]) > 0) {
                $hasProposals = true;
            }
        }

        $this->client_model->delete_contact($id);
        if ($hasProposals) {
            $this->session->set_flashdata('gdpr_delete_warning', true);
        }
        redirect(admin_url('clients/client/' . $customer_id . '?group=contacts'));
    }

    public function contacts($client_id)
    {
        $this->app->get_table_data('contacts', [
            'client_id' => $client_id,
        ]);
    }

    public function upload_attachment($id)
    {
        handle_client_attachments_upload($id);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->misc_model->add_attachment_to_database($this->input->post('clientid'), 'customer', $this->input->post('files'), $this->input->post('external'));
        }
    }

    public function delete_attachment($customer_id, $id)
    {
        if (staff_can('delete',  'customers') || is_customer_admin($customer_id)) {
            $this->client_model->delete_attachment($id);
        }
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    /* Delete client */
    public function delete($id)
    {
        if (staff_cant('delete', 'customers')) {
            access_denied('customers');
        }
        if (!$id) {
            redirect(admin_url('clients'));
        }
        $response = $this->client_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('customer_delete_transactions_warning', _l('invoices') . ', ' . _l('estimates') . ', ' . _l('credit_notes')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('client')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('client_lowercase')));
        }
        redirect(admin_url('client/get_patient_list'));
    }

    /* Staff can login as client */
    public function login_as_client($id)
    {
        if (is_admin()) {
            login_as_client($id);
        }
        hooks()->do_action('after_contact_login');
        redirect(site_url());
    }

    public function get_customer_billing_and_shipping_details($id)
    {
        echo json_encode($this->client_model->get_customer_billing_and_shipping_details($id));
    }

    /* Change client status / active / inactive */
    public function change_contact_status($id, $status)
    {
        if (staff_can('edit',  'patients') || is_customer_admin(get_user_id_by_contact_id($id))) {
            if ($this->input->is_ajax_request()) {
                $this->client_model->change_contact_status($id, $status);
            }
        }
    }

    /* Change client status / active / inactive */
    public function change_client_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->client_model->change_client_status($id, $status);
        }
    }

    /* Zip function for credit notes */
    public function zip_credit_notes($id)
    {
        $has_permission_view = staff_can('view',  'credit_notes');

        if (!$has_permission_view && staff_cant('view_own', 'credit_notes')) {
            access_denied('Zip Customer Credit Notes');
        }

        if ($this->input->post()) {
            $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'credit_notes',
                'status'            => $this->input->post('credit_note_zip_status'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=credit_notes'),
            ]);

            $this->app_bulk_pdf_export->set_client_id($id);
            $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
            $this->app_bulk_pdf_export->export();
        }
    }

    public function zip_invoices($id)
    {
        $has_permission_view = staff_can('view',  'invoices');
        if (!$has_permission_view && staff_cant('view_own', 'invoices')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('Zip Customer Invoices');
        }

        if ($this->input->post()) {
            $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'invoices',
                'status'            => $this->input->post('invoice_zip_status'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=invoices'),
            ]);

            $this->app_bulk_pdf_export->set_client_id($id);
            $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
            $this->app_bulk_pdf_export->export();
        }
    }

    /* Since version 1.0.2 zip client estimates */
    public function zip_estimates($id)
    {
        $has_permission_view = staff_can('view',  'estimates');
        if (!$has_permission_view && staff_cant('view_own', 'estimates')
            && get_option('allow_staff_view_estimates_assigned') == '0') {
            access_denied('Zip Customer Estimates');
        }

        if ($this->input->post()) {
            $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'estimates',
                'status'            => $this->input->post('estimate_zip_status'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=estimates'),
            ]);

            $this->app_bulk_pdf_export->set_client_id($id);
            $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
            $this->app_bulk_pdf_export->export();
        }
    }

    public function zip_payments($id)
    {
        $has_permission_view = staff_can('view',  'payments');

        if (!$has_permission_view && staff_cant('view_own', 'invoices')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('Zip Customer Payments');
        }

        $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'payments',
                'payment_mode'      => $this->input->post('paymentmode'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=payments'),
            ]);

        $this->app_bulk_pdf_export->set_client_id($id);
        $this->app_bulk_pdf_export->set_client_id_column(db_prefix() . 'clients.userid');
        $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
        $this->app_bulk_pdf_export->export();
    }

    public function import()
    {
        if (staff_cant('create', 'customers')) {
            access_denied('customers');
        }

        $dbFields = $this->db->list_fields(db_prefix() . 'contacts');
        foreach ($dbFields as $key => $contactField) {
            if ($contactField == 'phonenumber') {
                $dbFields[$key] = 'contact_phonenumber';
            }
        }

        $dbFields = array_merge($dbFields, $this->db->list_fields(db_prefix() . 'clients'));

        $this->load->library('import/import_customers', [], 'import');

        $this->import->setDatabaseFields($dbFields)
                     ->setCustomFields(get_custom_fields('patients'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if ($this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
            $this->import->setSimulation($this->input->post('simulate'))
                          ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                          ->setFilename($_FILES['file_csv']['name'])
                          ->perform();


            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['groups']    = $this->client_model->get_groups();
        $data['title']     = _l('import');
        $data['bodyclass'] = 'dynamic-create-groups';
        $this->load->view('admin/clients/import', $data);
    }

    public function groups()
    {
        if (!is_admin()) {
            access_denied('Customer Groups');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('customers_groups');
        }
        $data['title'] = _l('customer_groups');
        $this->load->view('admin/clients/groups_manage', $data);
    }

    public function group()
    {
        if (!is_admin() && get_option('staff_members_create_inline_customer_groups') == '0') {
            access_denied('Customer Groups');
        }

        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $id      = $this->client_model->add_group($data);
                $message = $id ? _l('added_successfully', _l('customer_group')) : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id'      => $id,
                    'name'    => $data['name'],
                ]);
            } else {
                $success = $this->client_model->edit_group($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('customer_group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    public function delete_group($id)
    {
        if (!is_admin()) {
            access_denied('Delete Customer Group');
        }
        if (!$id) {
            redirect(admin_url('clients/groups'));
        }
        $response = $this->client_model->delete_group($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('customer_group')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('customer_group_lowercase')));
        }
        redirect(admin_url('clients/groups'));
    }

    public function bulk_action()
    {
        hooks()->do_action('before_do_bulk_action_for_customers');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids    = $this->input->post('ids');
            $groups = $this->input->post('groups');

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($this->client_model->delete($id)) {
                            $total_deleted++;
                        }
                    } else {
                        if (!is_array($groups)) {
                            $groups = false;
                        }
                        $this->client_groups_model->sync_customer_groups($id, $groups);
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_clients_deleted', $total_deleted));
        }
    }

    public function vault_entry_create($customer_id)
    {
        $data = $this->input->post();

        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }

        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }

        unset($data['id']);
        $data['creator']      = get_staff_user_id();
        $data['creator_name'] = get_staff_full_name($data['creator']);
        $data['description']  = nl2br($data['description']);
        $data['password']     = $this->encryption->encrypt($this->input->post('password', false));

        if (empty($data['port'])) {
            unset($data['port']);
        }

        $this->client_model->vault_entry_create($data, $customer_id);
        set_alert('success', _l('added_successfully', _l('vault_entry')));
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function vault_entry_update($entry_id)
    {
        $entry = $this->client_model->get_vault_entry($entry_id);

        if ($entry->creator == get_staff_user_id() || is_admin()) {
            $data = $this->input->post();

            if (isset($data['fakeusernameremembered'])) {
                unset($data['fakeusernameremembered']);
            }
            if (isset($data['fakepasswordremembered'])) {
                unset($data['fakepasswordremembered']);
            }

            $data['last_updated_from'] = get_staff_full_name(get_staff_user_id());
            $data['description']       = nl2br($data['description']);

            if (!empty($data['password'])) {
                $data['password'] = $this->encryption->encrypt($this->input->post('password', false));
            } else {
                unset($data['password']);
            }

            if (empty($data['port'])) {
                unset($data['port']);
            }

            $this->client_model->vault_entry_update($entry_id, $data);
            set_alert('success', _l('updated_successfully', _l('vault_entry')));
        }
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function vault_entry_delete($id)
    {
        $entry = $this->client_model->get_vault_entry($id);
        if ($entry->creator == get_staff_user_id() || is_admin()) {
            $this->client_model->vault_entry_delete($id);
        }
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function vault_encrypt_password()
    {
        $id            = $this->input->post('id');
        $user_password = $this->input->post('user_password', false);
        $user          = $this->staff_model->get(get_staff_user_id());

        if (!app_hasher()->CheckPassword($user_password, $user->password)) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error_msg' => _l('vault_password_user_not_correct')]);
            die;
        }

        $vault    = $this->client_model->get_vault_entry($id);
        $password = $this->encryption->decrypt($vault->password);

        $password = html_escape($password);

        // Failed to decrypt
        if (!$password) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
            echo json_encode(['error_msg' => _l('failed_to_decrypt_password')]);
            die;
        }

        echo json_encode(['password' => $password]);
    }

    public function get_vault_entry($id)
    {
        $entry = $this->client_model->get_vault_entry($id);
        unset($entry->password);
        $entry->description = clear_textarea_breaks($entry->description);
        echo json_encode($entry);
    }

    public function statement_pdf()
    {
        $customer_id = $this->input->get('customer_id');

        if (staff_cant('view', 'invoices') && staff_cant('view', 'payments')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('clients/client/' . $customer_id));
        }

        $from = $this->input->get('from');
        $to   = $this->input->get('to');

        $data['statement'] = $this->client_model->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

        try {
            $pdf = statement_pdf($data['statement']);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';
        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(slug_it(_l('customer_statement') . '-' . $data['statement']['client']->company) . '.pdf', $type);
    }

    public function send_statement()
    {
        $customer_id = $this->input->get('customer_id');

        if (staff_cant('view', 'invoices') && staff_cant('view', 'payments')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('clients/client/' . $customer_id));
        }

        $from = $this->input->get('from');
        $to   = $this->input->get('to');

        $send_to = $this->input->post('send_to');
        $cc      = $this->input->post('cc');

        $success = $this->client_model->send_statement_to_email($customer_id, $send_to, $from, $to, $cc);
        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('statement_sent_to_client_success'));
        } else {
            set_alert('danger', _l('statement_sent_to_client_fail'));
        }

        redirect(admin_url('clients/client/' . $customer_id . '?group=statement'));
    }

    public function statement()
    {
        if (staff_cant('view', 'invoices') && staff_cant('view', 'payments')) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
            echo _l('access_denied');
            die;
        }

        $customer_id = $this->input->get('customer_id');
        $from        = $this->input->get('from');
        $to          = $this->input->get('to');

        $data['statement'] = $this->client_model->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

        $data['from'] = $from;
        $data['to']   = $to;

        $viewData['html'] = $this->load->view('admin/clients/groups/_statement', $data, true);

        echo json_encode($viewData);
    }

    /* Customised code*/


    public function save_client()
    {
        if (staff_cant('create', 'customers')) {
            if ($id != '' && !is_customer_admin($id)) {
                access_denied('customers');
            }
        }
        if($this->input->post()){
          $res = $this->client_model->save_client();
			
            if($res==0){
                set_alert('danger', _l('something_went_wrong'));
            }else if($res==1){
                set_alert('success', _l('customer_registration_successfully_confirmed'));
            }else if($res==2){
                set_alert('success', _l('appointment_created'));
            }else if($res==3){
                set_alert('danger', _l('duplicate_appointments'));
            }
        }
        
        redirect('client/get_patient_list');
    }
    private function check_customer_permissions()
    {
        if (staff_cant('view', 'customers')) {
            if (!have_assigned_customers() && staff_cant('create', 'customers')) {
                access_denied('customers');
            }
        }
    }

    public function enquiry_type()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('enquiry_type');
    }

    public function specialization()
    {
        if (!is_admin()) {
            access_denied('specialization');
        }
        $this->_handle_crud('specialization');
    }

    public function shift()
    {
        if (!is_admin()) {
            access_denied('shift');
        }
        $this->_handle_crud('shift');
    }

    public function medicine()
    {
        if (!is_admin()) {
            access_denied('medicine');
        }
        $this->_handle_crud('medicine');
    }

    public function medicine_potency()
    {
        if (!is_admin()) {
            access_denied('medicine_potency');
        }
        $this->_handle_crud('medicine_potency');
    }

    public function medicine_dose()
    {
        if (!is_admin()) {
            access_denied('medicine_dose');
        }
        $this->_handle_crud('medicine_dose');
    }

    public function medicine_timing()
    {
        if (!is_admin()) {
            access_denied('medicine_timing');
        }
        $this->_handle_crud('medicine_timing');
    }

    public function patient_response()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('patient_response');
    }

    public function patient_priority()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('patient_priority');
    }

    public function slots()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('slots');
    }

    public function treatment()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('treatment');
    }

    public function consultation_fee()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('consultation_fee');
    }

    public function patient_status()
    {
        if (!is_admin()) {
            access_denied('patient_status');
        }
        $this->_handle_crud('patient_status');
    }

    private function _handle_crud($table)
    {
        
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data($table);
        } else {
            if ($this->input->post()) {
                if (!is_admin()) {
                    access_denied($table);
                }
                $data = $this->input->post();
                $id_field = $table . '_id';

                if (isset($data[$id_field]) && $data[$id_field] != '') {
                    $success = $this->master_model->update($table, $data[$id_field], $data);
                    if ($success) {
                        set_alert('success', _l('updated_successfully'));
                    }
                } else {
                    $id = $this->master_model->add($table, $data);
                    if ($id) {
                        set_alert('success', _l('added_successfully'));
                    }
                }

                redirect(admin_url('client/' . $table));
            }

            $data['title'] = _l($table);
            $data['slug'] = $table;
            $data['field_name'] = $table . '_name';
            $data['records'] = $this->master_model->get_all($table);
            $data['table'] = App_table::find('clients');
            
            $this->load->view('master', $data);
        }
    }

    public function get_record_by_id($slug)
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->load->model('master_model');
            $record = $this->master_model->get_by_id($slug, $id);

            echo json_encode($record);
        } else {
            show_404();
        }
    }

    public function master_delete($table, $id)
    {
        $this->check_customer_permissions();
        if (!$id) {
            redirect(admin_url('client/' . $table));
        }
        $success = $this->master_model->delete($table, $id);
        if ($success) {
            set_alert('success', _l('deleted'));
        }
        redirect(admin_url('client/' . $table));
    }

    public function edit_client($id = '')
    {
        if (staff_cant('edit', 'customers')) {
                access_denied('customers');
        }

        $data['master_data'] = $this->load_master_data();
        $this->load->model('invoice_items_model');
        $data['items'] = $this->invoice_items_model->get_grouped();
		$this->load->model('leads_model');
		$data['states']  = $this->leads_model->get_state();
		$data['cities']  = $this->leads_model->get_city();
		$data['pincodes']  = $this->client_model->get_pincodes();

        if($id){
            $data['client_data'] = $this->client_model->get($id);
            $data['appointment_data'] = $this->client_model->get_appointment_data($id);
			$data['total_due']  = $this->client_model->get_client_due($id);
        }
        
        $this->load->view('edit_client', $data);

    }

    public function update_client()
    {
        if (staff_can('edit', 'customers')) {
            if (!have_assigned_customers() && staff_cant('edit', 'customers')) {
                access_denied('customers');
            }
        }
        $res = $this->client_model->update_client();
		if($res==0){
			set_alert('danger', _l('something_went_wrong'));
		}else if($res==1){
			set_alert('success', _l('customer_registration_successfully_confirmed'));
		}else if($res==2){
			set_alert('success', _l('appointment_created'));
		}else if($res==3){
			set_alert('danger', _l('duplicate_appointments'));
		}
        
        //redirect('client/get_patient_list');
		redirect(admin_url('client/get_patient_list/#appointments-tab'));
    }


    public function client($id = '')
    {
    if (staff_cant('view', 'customers')) {
        if ($id != '' && !is_customer_admin($id)) {
            access_denied('customers');
        }
    }

    if ($this->input->post() && !$this->input->is_ajax_request()) {
        if ($id == '') {
            if (staff_cant('create', 'customers')) {
                access_denied('customers');
            }

            $data = $this->input->post();

            $save_and_add_contact = false;
            if (isset($data['save_and_add_contact'])) {
                unset($data['save_and_add_contact']);
                $save_and_add_contact = true;
            }
            $id = $this->clients_model->add($data);
            if (staff_cant('view', 'customers')) {
                $assign['customer_admins']   = [];
                $assign['customer_admins'][] = get_staff_user_id();
                $this->clients_model->assign_admins($assign, $id);
            }
            if ($id) {
                set_alert('success', _l('added_successfully', _l('client')));
                if ($save_and_add_contact == false) {
                    redirect(admin_url('clients/client/' . $id));
                } else {
                    redirect(admin_url('clients/client/' . $id . '?group=contacts&new_contact=true'));
                }
            }
        } else {
            if (staff_cant('edit', 'customers')) {
                if (!is_customer_admin($id)) {
                    access_denied('customers');
                }
            }
            $success = $this->clients_model->update($this->input->post(), $id);
            if ($success == true) {
                set_alert('success', _l('updated_successfully', _l('client')));
            }
            redirect(admin_url('clients/client/' . $id));
        }
    }

    $group         = !$this->input->get('group') ? 'profile' : $this->input->get('group');
    $data['group'] = $group;

    if ($group != 'contacts' && $contact_id = $this->input->get('contactid')) {
        redirect(admin_url('clients/client/' . $id . '?group=contacts&contactid=' . $contact_id));
    }

    // Customer groups
    $data['groups'] = $this->clients_model->get_groups();

    if ($id == '') {
        $title = _l('add_new', _l('client'));
    } else {
        $client                = $this->clients_model->get($id);
        $data['customer_tabs'] = get_customer_profile_tabs($id);
        
        $data['customer_tabs']['prescription'] = array(
            'slug' => 'prescription',
            'name' => 'Prescription',
            'icon' => 'fa fa-prescription-bottle', // FontAwesome icon for prescription
            'view' => 'admin/clients/groups/prescription', // Path to the corresponding view
            'position' => 6, // Position of the tab
            'badge' => array(), // Add badge information if required
            'href' => '#', // URL or link
            'children' => array(), // Add submenu items if needed
        );

        if (!$client) {
            show_404();
        }

        $data['contacts'] = $this->clients_model->get_contacts($id);
        $data['tab']      = isset($data['customer_tabs'][$group]) ? $data['customer_tabs'][$group] : null;

        if (!$data['tab']) {
            show_404();
        }

        // Fetch data based on groups
        if ($group == 'profile') {
            $data['customer_groups'] = $this->clients_model->get_customer_groups($id);
            $data['customer_admins'] = $this->clients_model->get_admins($id);
        } elseif ($group == 'attachments') {
            $data['attachments'] = get_all_customer_attachments($id);
        } elseif ($group == 'vault') {
            $data['vault_entries'] = hooks()->apply_filters('check_vault_entries_visibility', $this->clients_model->get_vault_entries($id));

            if ($data['vault_entries'] === -1) {
                $data['vault_entries'] = [];
            }
        } elseif ($group == 'estimates') {
            $this->load->model('estimates_model');
            $data['estimate_statuses'] = $this->estimates_model->get_statuses();
        } elseif ($group == 'invoices') {
            $this->load->model('invoices_model');
            $data['invoice_statuses'] = $this->invoices_model->get_statuses();
        } elseif ($group == 'credit_notes') {
            $this->load->model('credit_notes_model');
            $data['credit_notes_statuses'] = $this->credit_notes_model->get_statuses();
            $data['credits_available']     = $this->credit_notes_model->total_remaining_credits_by_customer($id);
        } elseif ($group == 'payments') {
            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get();
        } elseif ($group == 'notes') {
            $data['user_notes'] = $this->misc_model->get_notes($id, 'customer');
        }elseif ($group == 'prescription') {
            $this->load->model('srini_model');
           $data['user_prescription'] = $this->srini_model->get_prescription();
        } elseif ($group == 'projects') {
            $this->load->model('projects_model');
            $data['project_statuses'] = $this->projects_model->get_project_statuses();
        } elseif ($group == 'statement') {
            if (staff_cant('view', 'invoices') && staff_cant('view', 'payments')) {
                set_alert('danger', _l('access_denied'));
                redirect(admin_url('clients/client/' . $id));
            }

            $data = array_merge($data, prepare_mail_preview_data('customer_statement', $id));
        } elseif ($group == 'map') {
            if (get_option('google_api_key') != '' && !empty($client->latitude) && !empty($client->longitude)) {
                $this->app_scripts->add('map-js', base_url($this->app_scripts->core_file('assets/js', 'map.js')) . '?v=' . $this->app_css->core_version());

                $this->app_scripts->add('google-maps-api-js', [
                    'path'       => 'https://maps.googleapis.com/maps/api/js?key=' . get_option('google_api_key') . '&callback=initMap',
                    'attributes' => [
                        'async',
                        'defer',
                        'latitude'       => "$client->latitude",
                        'longitude'      => "$client->longitude",
                        'mapMarkerTitle' => "$client->company",
                    ],
                    ]);
            }
        }

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);

        $data['client'] = $client;
        $title          = $client->company;

        // Get all active staff members (used to add reminder)
        $data['members'] = $data['staff'];

        if (!empty($data['client']->company)) {
            // Check if is realy empty client company so we can set this field to empty
            // The query where fetch the client auto populate firstname and lastname if company is empty
            if (is_empty_customer_company($data['client']->userid)) {
                $data['client']->company = '';
            }
        }
    }

    $this->load->model('currencies_model');
    $data['currencies'] = $this->currencies_model->get();

    if ($id != '') {
        $customer_currency = $data['client']->default_currency;

        foreach ($data['currencies'] as $currency) {
            if ($customer_currency != 0) {
                if ($currency['id'] == $customer_currency) {
                    $customer_currency = $currency;

                    break;
                }
            } else {
                if ($currency['isdefault'] == 1) {
                    $customer_currency = $currency;

                    break;
                }
            }
        }

        if (is_array($customer_currency)) {
            $customer_currency = (object) $customer_currency;
        }

        $data['customer_currency'] = $customer_currency;

        $slug_zip_folder = (
            $client->company != ''
            ? $client->company
            : get_contact_full_name(get_primary_contact_user_id($client->userid))
        );

        $data['zip_in_folder'] = slug_it($slug_zip_folder);
    }

    $data['bodyclass'] = 'customer-profile dynamic-create-groups';
    $data['title']     = $title;

    $this->load->view('admin/client/client', $data);
}


//Add patient activity log manually
public function add_patient_activity()
{
    if ($this->input->is_ajax_request()) {
        $this->load->model('client_model');

        $patientid = $this->input->post('patientid');
        $activity = $this->input->post('activity');

        if (!$patientid || !$activity) {
            echo json_encode(['success' => false, 'message' => 'Missing patient ID or activity.']);
            return;
        }

        // Save the activity
        $this->client_model->log_patient_activity($patientid, $activity, $custom_activity = 1);

        // ✅ Send redirect URL in response
        echo json_encode([
            'success' => true,
            'redirect' => admin_url('client/get_patient_list/' . $patientid . '/tab_activity'),
        ]);
    }
}

// Add patient call log manually via AJAX
public function add_patient_call_log()
{
    if ($this->input->is_ajax_request()) {
        $this->load->model('client_model');

        $data = $this->input->post();
        $patientid = $data['patientid'];

        /* if (!$patientid || empty($data['comments'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            return;
        } */

        // Save call log in your model (create method accordingly)
        $this->client_model->add_patient_call_log($data);

        // ✅ Send redirect URL in response to reload modal with tab_calls active
        set_alert('success', _l('added_successfully'));
        echo json_encode([
            'success'  => true,
            'redirect' => admin_url('client/get_patient_list/' . $patientid . '/tab_calls'),
        ]);
    }
}

public function save_prescription()
{
    // Check if the request is an AJAX request
    if ($this->input->is_ajax_request()) {
        // Load the model where we will save the prescription data
        $this->load->model('client_model');

        // Get the form data sent via POST
        $data = $this->input->post();
        $patientid = $data['patientid'];

        // Validate the data: Ensure patientid, prescription data, and other required fields are provided
        if (!$patientid || empty($data['medicine_name']) || empty($data['medicine_potency']) || empty($data['medicine_dose'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            return;
        }

        // Save prescription in your model (create a method accordingly)
        $this->client_model->save_prescription($data, $patientid);

        // Send a success response with the redirect URL to view the prescription details
        echo json_encode([
            'success'  => true,
            'redirect' => admin_url('client/get_patient_list/' . $patientid . '/tab_prescription'),
        ]);
    }
    else {
        // If not an AJAX request, show an error message
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }
}

    public function appointments($id = NULL, $consulted_date = NULL, $consulted_to_date = NULL, $enquiry_doctor_id = NULL, $visit_status = NULL, $branch_id = NULL, $selected_branch_id = NULL, $appointment_type_id = NULL)
	{
		// Optional access check
		/* if (staff_cant('view_visits', 'customers')) {
			access_denied('appointments');
		} */

		// 🧼 Normalize incoming values
		$enquiry_doctor_id = ($enquiry_doctor_id === '0' || empty($enquiry_doctor_id)) ? null : $enquiry_doctor_id;
		$branch_id = ($branch_id === '0' || empty($branch_id)) ? null : $branch_id;
		$visit_status = ($visit_status === 'All' || empty($visit_status)) ? null : str_replace('_', ' ', $visit_status);

		$selected_branch_id = urldecode($selected_branch_id); // decode %2C to ,
		$selected_branch_id = explode(',', $selected_branch_id); // split by comma

		// Clean the array to ensure numeric values only
		$selected_branch_id = array_filter($selected_branch_id, fn($id) => is_numeric($id));

		// Optional: cast to int
		$selected_branch_id = array_map('intval', $selected_branch_id);

		$data['selected_branch_id'] = $selected_branch_id;

		// 🔍 Logged-in staff info
		$staff_id = get_staff_user_id();
		$staff_data = $this->db
			->select('s.staffid, r.name as role_name')
			->from(db_prefix() . 'staff s')
			->join(db_prefix() . 'roles r', 'r.roleid = s.role', 'left')
			->where('s.staffid', $staff_id)
			->get()
			->row();

		// 📦 Prepare data array
		$data = [
			'title'               => _l('Appointments'),
			'consulted_from_date'=> $consulted_date,
			'consulted_to_date'  => $consulted_to_date,
			'enquiry_doctor_id'  => $enquiry_doctor_id,
			'branch_id'          => $branch_id,
			'appointment_type_id'          => $appointment_type_id,
			'selected_branch_id'          => $selected_branch_id,
			'visit_status'       => $visit_status,
			'staff_data'         => $staff_data,
		];
		
		// 🚀 Return AJAX table view
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('client', 'tables/appointments_table'), $data);
			return;
		}
		
		function get_counter_by_doctor_id($doctor_id)
		{
			$CI =& get_instance();
			$CI->db->where('doctor_id', $doctor_id);
			return $CI->db->get(db_prefix() . 'counter')->row(); // returns single row (object)
		}
		
		if ($id) {
			 $this->load->model('currencies_model');
			 $this->load->model('taxes_model');
			 $this->load->model('invoice_items_model');
			 $this->load->model('estimates_model');
			$data['clientid'] = $id;
            // Fetch the existing patient data
            $client = $this->client_model->get($id);
            $estimates = $this->client_model->get_estimates($id);
			foreach ($estimates as &$estimate) {
				$this->db->select('description');
				$this->db->where('rel_type', 'estimate');
				$this->db->where('rel_id', $estimate['id']);
				$items = $this->db->get('tblitemable')->row();
				
				$estimate['description'] = $items->description; // append to estimate
			}
			
            $customer_new_fields = $this->client_model->get_customer_new_fields($id);
			$currencies= $this->currencies_model->get();
			$taxes = $this->taxes_model->get();
			$items_groups = $this->invoice_items_model->get_groups();
			$staff            = $this->staff_model->get('', ['active' => 1]);
			$estimate_statuses = $this->estimates_model->get_statuses();
			$base_currency = $this->currencies_model->get_base_currency();
			
			$items = $this->invoice_items_model->get_grouped();
            $appointment_data = $this->client_model->get_appointment_data($id);
            $patient_activity_log = $this->client_model->get_patient_activity_log($id);
            $patient_prescription = $this->client_model->get_patient_prescription($id);
			
            $patient_treatment = $this->client_model->get_patient_treatment($id);
            $casesheet = $this->client_model->get_casesheet($id);
            // Fetch patient call logs
            $patient_call_logs = $this->client_model->get_patient_call_logs($id); // NEW
            $invoices = $this->client_model->get_invoices($id); // NEW
            $invoice_payments = $this->client_model->get_invoice_payments($id); // NEW
            $shared_requests = $this->client_model->get_shared_requests($id); // NEW

            // Fetch medicine data (names, potencies, doses, timings)
            $medicines = $this->master_model->get_all('medicine');
            $potencies = $this->master_model->get_all('medicine_potency');
            $doses = $this->master_model->get_all('medicine_dose');
            $timings = $this->master_model->get_all('medicine_timing');
            $appointment_type = $this->master_model->get_all('appointment_type');
            $criteria = $this->master_model->get_all('criteria');
            $treatments = $this->master_model->get_all('treatment');
            $patient_status = $this->master_model->get_all('patient_status');
            $master_settings = $this->master_model->get_all('master_settings');
			$testimonials = $this->client_model->get_testimonial();
			
			function get_estimation_payment_summary($estimation_id)
			{
				$CI =& get_instance();

				// 1. Get the estimate row
				$CI->db->select('total, invoiceid, currency, date, expirydate');
				$CI->db->where('id', $estimation_id);
				$estimate = $CI->db->get(db_prefix() . 'estimates')->row();

				if (!$estimate || !$estimate->invoiceid) {
					return [
						'total' => 0,
						'paid' => 0,
						'dues' => 0,
						'currency' => '',
						'invoice_id' => null,
					];
				}

				// 2. Sum payments from invoicepaymentrecords
				$CI->db->select_sum('amount');
				$CI->db->where('invoiceid', $estimate->invoiceid);
				$paid_row = $CI->db->get(db_prefix() . 'invoicepaymentrecords')->row();
				$paid = $paid_row ? (float)$paid_row->amount : 0;

				return [
					'total'     => (float)$estimate->total,
					'paid'      => $paid,
					'dues'      => (float)$estimate->total - $paid,
					'currency'  => $estimate->currency,
					'invoice_id'=> $estimate->invoiceid,
					'date'=> $estimate->date,
					'expirydate'=> $estimate->expirydate,
				];
			}
			$branch = $this->client_model->get_branch();
            // Pass the data to the view
            $data['client_modal'] = $this->load->view('client_model_popup', [
                'estimates' => $estimates,
                'client' => $client,
                'branch' => $branch,
                'casesheet' => $casesheet,
                'testimonials' => $testimonials,
                'shared_requests' => $shared_requests,
                'master_settings' => $master_settings,
                'customer_new_fields' => $customer_new_fields,
                'currencies' => $currencies,
                'taxes' => $taxes,
                'items' => $items,
                'base_currency' => $base_currency,
                'items_groups' => $items_groups,
                'staff' => $staff,
                'estimate_statuses' => $estimate_statuses,
                'appointment_data' => $appointment_data,
                'patient_activity_log' => $patient_activity_log,
                'patient_call_logs' => $patient_call_logs, // NEW
                'patient_prescriptions' => $patient_prescription, // NEW
                'patient_treatment' => $patient_treatment, // NEW
                'medicines' => $medicines, // NEW
                'potencies' => $potencies, // NEW
                'appointment_type' => $appointment_type, // NEW
                'criteria' => $criteria, // NEW
                'doses' => $doses, // NEW
                'treatments' => $treatments, // NEW
                'patient_status' => $patient_status, // NEW
                'invoices' => $invoices, // NEW
                'invoice_payments' => $invoice_payments, // NEW
                'timings' => $timings // NEW
            ], true);
        }
       $this->load->view('appointments', $data);
	  
    }
    public function visits($consulted_date = NULL)
    {
        if (staff_can('view_activity_log', 'customers')) {
            //access_denied('appointments');
        } 
		
       //$data['appointments'] = $this->client_model->get_appointments();
        $data['title'] = "Visits";
        $data['consulted_date'] = $consulted_date;
		
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('client', 'tables/visits_table'), $data);
        }
       $this->load->view('visits', $data);
	  
    }
	
    
	
	public function confirm_booking($id = NULL, $direct= NULL)
	{
		if(!$id){
			$id = $this->input->post('id');
		}
		
		if ($id) {
			$this->db->from(db_prefix() . 'appointment');
			$this->db->where(array("visit_status"=>1));
			$this->db->like('DATE(appointment_date)', date('Y-m-d'));
			$count = $this->db->count_all_results();
			
			$branch_id = $this->current_branch_id; // or fetch from session/context if not already available

			$get_branch_code = $this->db->get_where(db_prefix() . 'master_settings', [
				'title'     => 'branch_code',
				'branch_id' => $branch_id
			])->row();
			$branch_code = $get_branch_code ? $get_branch_code->options : '';

			/* $get_branch_short_code = $this->db->get_where(db_prefix() . 'master_settings', [
				'title'     => 'branch_short_code',
				'branch_id' => $branch_id
			])->row();
			$branch_short_code = $get_branch_short_code ? $get_branch_short_code->options : ''; */
			
			if($count){
				$number = $branch_code . '-' . date('Ymd') . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);

			}else{
				$number = $branch_code.'-'.date('Ymd').'-00001';
			}
			
			$formatted_number = str_pad($number, 5, '0', STR_PAD_LEFT);
			$visit_id = "V-".$formatted_number;
			
			$this->db->where('appointment_id', $id);
			$this->db->update(db_prefix() . 'appointment', ['visit_status' => '1', "visit_id"=>$visit_id, "visited_date"=>date('Y-m-d H:i:s')]);
			
			$check = $this->db->get_where(db_prefix() . 'appointment', array('appointment_id'=>$id))->row();
			
			if($check){
				$client_id = $check->userid;
				
				
				$check_status = $this->db->get_where(db_prefix() . 'leads_status', array("name"=>"Visited"))->row();
				if($check_status){
					$status_id = $check_status->id;
				}else{
					$status_id = 1;
				}
				
				$assigned_doctor = get_staff_full_name($check->enquiry_doctor_id);
				$communication_data = [
					'assigned_doctor'   => $assigned_doctor,
					'testimonial_link'   => "https://positiveautism.com/",
				];
				// Optional: Send notification/log event
				$this->client_model->patient_journey_log_event(
					$client_id,
					'visited',
					'visited',
					$communication_data,
					$status_id
				); 
				
				$add_lead_patient_status = array(
					"userid" => $client_id,
					"status" => $status_id,
					"datetime" => date('Y-m-d H:i:s')
				);
				$this->client_model->add_lead_patient_status($add_lead_patient_status);
				
			}
			$description = "visit_confirmed";
            $this->client_model->log_patient_activity($client_id, $description);
			
			//Create Token
			$appointment_data = $this->db->get_where(db_prefix() . 'appointment', array("appointment_id"=>$id))->row();
			
			if ($appointment_data) {
				$this->db->order_by("token_id", "DESC");
				$this->db->limit(1);
				$date = date("Y-m-d");
				$get_token_number = $this->db->get_where(db_prefix() . 'tokens', array("date"=>$date))->row();
				if($get_token_number){
					$token_number = $get_token_number->token_number + 1;
					$token_status = "Pending";
				}else{
					$token_number = 1;
					$token_status = "Serving";
				}
				
				
				$token_data = array(
					'token_number' => $token_number,
					'patient_id'   => $appointment_data->userid,
					'doctor_id'    => $appointment_data->enquiry_doctor_id,
					'date'         => date('Y-m-d'),
					'token_status' => $token_status
				);
				$this->db->insert(db_prefix() . 'tokens', $token_data);
			}

			
			if($direct){
				echo json_encode(['success' => true, 'message' => _l('visit_successfully_confirmed')]);
			}else{
				return true;
			}
			
		} else {
			if($direct){
				echo json_encode(['success' => false, 'message' => _l('something_went_wrong')]);
			}else{
				return false;
			}
			
		}
	}




    public function save_casesheet()
    {
        // Check if the request is an AJAX request
        if ($this->input->is_ajax_request()) {
    
            // Get the form data sent via POST
            $data = $this->input->post();
            $patientid = $data['patientid'];
            // Medicine days temporarily optional
            /*
            $medicineDaysInput = isset($data['medicine_days']) ? trim($data['medicine_days']) : '';
            $validatedMedicineDays = filter_var(
                $medicineDaysInput,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($validatedMedicineDays === false) {
                echo json_encode([
                    'success' => false,
                    'message' => _l('medicine_days') . ' is required.',
                ]);
                return;
            }

            $_POST['medicine_days'] = $validatedMedicineDays;
            */
    
            // Validate the data: Ensure patientid, prescription data, and other required fields are provided
            /*if (!$patientid || empty($data['medicine_name']) || empty($data['medicine_potency']) || empty($data['medicine_dose'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
                return;
            }*/
    
            // Save prescription in your model (create a method accordingly)
            $inserted_id = $this->client_model->save_casesheet($patientid);
    
            // Send a success response with the redirect URL to view the prescription details
            echo json_encode([
				'success' => true,
				'id'      => $inserted_id,
				'patientid' => $patientid,
			]);
        }
        else {
            // If not an AJAX request, show an error message
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        }
    }

    

    public function casesheet_view($id)
	{
		$case = $this->client_model->get_casesheet_by_id($id);
		if (!$case) {
			show_404();
		}

		// Handle PDF download
		if ($this->input->post('casesheetpdf')) {
			try {
				// You need to implement this similar to invoice_pdf()
				$pdf = casesheet_pdf($case);
			} catch (Exception $e) {
				echo $e->getMessage();
				die;
			}

			$case_number = 'CASE-' . $case->id;
			$companyname = get_option('invoice_company_name'); // reuse same config
			if ($companyname != '') {
				$case_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
			}

			$pdf->Output(mb_strtoupper(slug_it($case_number), 'UTF-8') . '.pdf', 'D');
			die();
		}

		// Normal view loading
		$data['case'] = $case;
		$data['client'] = $this->client_model->get($patientid);
		$this->load->view('view_case_sheet_modal', $data);
	}
    public function edit_casesheet($casesheet_id, $patientid)
	{
		if (staff_cant('edit_casesheet', 'customers')) {
            access_denied('customers'); 
        }
		$case = $this->client_model->get_casesheet_by_id($casesheet_id);
		if (!$case) {
			show_404();
		}

		// Handle PDF download
		if ($this->input->post('casesheetpdf')) {
			
		}

		// Normal view loading
		$data['case'] = $case;
		$data['client'] = $this->client_model->get($patientid);
		$data['prescription'] = $this->client_model->get_patient_prescription($patientid, $casesheet_id);
		$data['prev_treatments'] = $this->client_model->prev_treatments($patientid);
		
		// Fetch medicine data (names, potencies, doses, timings)
		$data['medicines'] = $this->master_model->get_all('medicine');
		$data['potencies'] = $this->master_model->get_all('medicine_potency');
		$data['doses'] = $this->master_model->get_all('medicine_dose');
		$data['timings'] = $this->master_model->get_all('medicine_timing');
		$data['suggested_diagnostics'] = $this->master_model->get_all('suggested_diagnostics');
		$this->load->model('invoice_items_model');
		$data['items'] = $this->invoice_items_model->get_grouped();
		
		$data['prev_documents'] = $this->client_model->prev_documents($patientid);
		$data['treatments'] = $this->master_model->get_all('treatment');
		$data['patient_status'] = $this->master_model->get_all('patient_status');
		$this->load->view('edit_casesheet', $data);
	}
	public function view_casesheet($casesheet_id, $patientid)
	{
		$case = $this->client_model->get_casesheet_by_id($casesheet_id);
		if (!$case) {
			show_404();
		}

		// Handle PDF download
		if ($this->input->post('casesheetpdf')) {
			
		}

		// Normal view loading
		$data['case'] = $case;
		$data['client'] = $this->client_model->get($patientid);
		$data['prescription'] = $this->client_model->get_patient_prescription($patientid, $casesheet_id);
		$data['prev_treatments'] = $this->client_model->prev_treatments($patientid);
		
		// Fetch medicine data (names, potencies, doses, timings)
		$data['medicines'] = $this->master_model->get_all('medicine');
		$data['potencies'] = $this->master_model->get_all('medicine_potency');
		$data['doses'] = $this->master_model->get_all('medicine_dose');
		$data['timings'] = $this->master_model->get_all('medicine_timing');
		$data['suggested_diagnostics'] = $this->master_model->get_all('suggested_diagnostics');
		$data['prev_documents'] = $this->client_model->prev_documents($patientid);
		$data['treatments'] = $this->master_model->get_all('treatment');
		$data['patient_status'] = $this->master_model->get_all('patient_status');
		$this->load->view('view_casesheet', $data);
	}
	public function add_casesheet($patientid)
	{
		 $this->load->model('currencies_model');
		 $this->load->model('taxes_model');
		 $this->load->model('invoice_items_model');
		 $this->load->model('estimates_model');
		// Normal view loading
		$data['case'] = $case;
		$data['prescription'] = $this->client_model->get_patient_prescription($patientid, $casesheet_id);
		$data['client'] = $this->client_model->get($patientid);
		$data['prev_treatments'] = $this->client_model->prev_treatments($patientid);
		$data['casesheet'] = $this->client_model->get_casesheet($patientid);
		$data['appointment_data'] = $this->client_model->get_appointment_data($patientid);
		// Fetch medicine data (names, potencies, doses, timings)
		$data['items'] = $this->invoice_items_model->get_grouped();
		$data['patient_treatment'] = $this->client_model->get_patient_treatment($patientid);
		$data['medicines'] = $this->master_model->get_all('medicine');
		$data['potencies'] = $this->master_model->get_all('medicine_potency');
		$data['doses'] = $this->master_model->get_all('medicine_dose');
		$data['timings'] = $this->master_model->get_all('medicine_timing');
		$data['suggested_diagnostics'] = $this->master_model->get_all('suggested_diagnostics');
		$data['prev_documents'] = $this->client_model->prev_documents($patientid);
		$data['treatments'] = $this->master_model->get_all('treatment');
		$data['patient_status'] = $this->master_model->get_all('patient_status');
		$this->load->view('add_casesheet', $data);
	}
	public function update_casesheet()
    {
        // Check if the request is an AJAX request
        if ($this->input->is_ajax_request()) {
    
            // Get the form data sent via POST
            $data = $this->input->post();
            $patientid = $data['patientid'];
    
            // Save prescription in your model (create a method accordingly)
            $this->client_model->update_casesheet();
    
            // Send a success response with the redirect URL to view the prescription details
            echo json_encode([
                'success'  => true,
                'redirect' => admin_url('client/get_patient_list/' . $patientid . '/tab_casesheet'),
            ]);
        }
        else {
            // If not an AJAX request, show an error message
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        }
    }
    
   public function get_dynamic_options()
	{
		log_message('debug', 'Dynamic options AJAX request received');
		$this->load->database();

		$result = $this->db->get('tblcustomers_groups')->result();

		$options = [];
		foreach ($result as $row) {
			$options[] = [
				'value' => $row->id,
				'label' => $row->name
			];
		}

		echo json_encode($options);
	}
	
	public function search_contact_number()
	{
		$contact = $this->input->post('contact');
		$results = $this->client_model->search_by_contact_number($contact);

		$html = '<ul class="dropdown-menu search-results animated fadeIn no-mtop display-block">';
		if (!empty($results)) {
			$html .= '<li class="dropdown-header">Matching Contacts</li>';
			foreach ($results as $row) {
				if($row['type'] == "Patient"){
					$label_class = "success";
				}else{
					$label_class = "warning";
				}
				
				$type_label = '<span class="label label-' . $label_class . '">' . e($row['type']) . '</span>';

			$html .= '<li style="width: 300px;"> 
            <a href="' . admin_url('client/client/add_client/' . $row['id'] . '/' . $row['type']) . '">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong>' . e(format_name($row['company'])) . '</strong>
                    ' . $type_label . '
                </div>
                <div style="margin-top: 4px; color: #555;">' . e($row['phonenumber']) . '</div>
            </a>
          </li>';

			}
		} else {
			$html .= '<li><a href="#">No results found</a></li>';
		}
		$html .= '</ul>';

		echo json_encode(['results' => $html]);
		die;
	}
	
	
	//Reports
	public function appointment_reports($consulted_date = NULL)
    {
        if (staff_cant('appointment_reports', 'reports')) {
            access_denied('appointment_reports');
        } 
        $data['title'] = _l("appointment_reports");
		$data['consulted_date'] = $consulted_date;
		
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('client', 'tables/appointment_reports_table'), $data);
        }
       $this->load->view('reports/appointment_reports', $data);
	  
    }
	
	public function doctor_ownership_reports($consulted_date = NULL)
    {
        if (staff_cant('doctor_ownership_reports', 'reports')) {
            access_denied('doctor_ownership_reports');
        } 
        $data['title'] = _l("doctor_ownership_reports");
		$data['consulted_date'] = $consulted_date;
		
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('client', 'tables/doctor_ownership_reports_table'), $data);
        }
       $this->load->view('reports/doctor_ownership_reports', $data);
	  
    }

	public function ownership_details($type, $doctor_id = NULL, $from_date = NULL,  $to_date = NULL)
	{
		if (staff_cant('ownership_details', 'reports')) {
			access_denied('ownership_details');
		}
		
		$data['title'] = _l("doctor_ownership_details_details");
		$data['type'] = $type;
		$data['doctor_id'] = $doctor_id;
		$data['from_date'] = $from_date;
		$data['to_date'] = $to_date;

		// Pass the dates to the model function
		$data['ownership_details'] = $this->client_model->ownership_details($type, $doctor_id, $from_date, $to_date);

		if ($this->input->is_ajax_request()) {
			// Pass the dates to the table data function as well
			$this->app->get_table_data(module_views_path('client', 'tables/ownership_details_table'), $data);
		}
		$this->load->view('reports/ownership_details', $data);
	}
	
	public function doctor_calendar_view($id=NULL)
    {
        if(staff_cant('view_appointments_calendar', 'customers')){
            access_denied('view_appointments_calendar');
        } 
         $data['title'] = _l('appointments_calendar');
		 function get_counter_by_doctor_id($doctor_id)
			{
				$CI =& get_instance();
				$CI->db->where('doctor_id', $doctor_id);
				return $CI->db->get(db_prefix() . 'counter')->row(); // returns single row (object)
			}
		 if ($id) {
			 $this->load->model('currencies_model');
			 $this->load->model('taxes_model');
			 $this->load->model('invoice_items_model');
			 $this->load->model('estimates_model');
			$data['clientid'] = $id;
            // Fetch the existing patient data
            $client = $this->client_model->get($id);
            $estimates = $this->client_model->get_estimates($id);
			foreach ($estimates as &$estimate) {
				$this->db->select('description');
				$this->db->where('rel_type', 'estimate');
				$this->db->where('rel_id', $estimate['id']);
				$items = $this->db->get('tblitemable')->row();
				
				$estimate['description'] = $items->description; // append to estimate
			}
			
            $customer_new_fields = $this->client_model->get_customer_new_fields($id);
			$currencies= $this->currencies_model->get();
			$taxes = $this->taxes_model->get();
			$items_groups = $this->invoice_items_model->get_groups();
			$staff            = $this->staff_model->get('', ['active' => 1]);
			$estimate_statuses = $this->estimates_model->get_statuses();
			$base_currency = $this->currencies_model->get_base_currency();
			
			$items = $this->invoice_items_model->get_grouped();
            $appointment_data = $this->client_model->get_appointment_data($id);
            $patient_activity_log = $this->client_model->get_patient_activity_log($id);
            $patient_prescription = $this->client_model->get_patient_prescription($id);
			
            $patient_treatment = $this->client_model->get_patient_treatment($id);
            $casesheet = $this->client_model->get_casesheet($id);
            // Fetch patient call logs
            $patient_call_logs = $this->client_model->get_patient_call_logs($id); // NEW
            $invoices = $this->client_model->get_invoices($id); // NEW
            $invoice_payments = $this->client_model->get_invoice_payments($id); // NEW
            $shared_requests = $this->client_model->get_shared_requests($id); // NEW

            // Fetch medicine data (names, potencies, doses, timings)
            $medicines = $this->master_model->get_all('medicine');
            $potencies = $this->master_model->get_all('medicine_potency');
            $doses = $this->master_model->get_all('medicine_dose');
            $timings = $this->master_model->get_all('medicine_timing');
            $appointment_type = $this->master_model->get_all('appointment_type');
            $criteria = $this->master_model->get_all('criteria');
            $treatments = $this->master_model->get_all('treatment');
            $patient_status = $this->master_model->get_all('patient_status');
            $master_settings = $this->master_model->get_all('master_settings');
			$testimonials = $this->client_model->get_testimonial();
			
			function get_estimation_payment_summary($estimation_id)
			{
				$CI =& get_instance();

				// 1. Get the estimate row
				$CI->db->select('total, invoiceid, currency, date, expirydate');
				$CI->db->where('id', $estimation_id);
				$estimate = $CI->db->get(db_prefix() . 'estimates')->row();

				if (!$estimate || !$estimate->invoiceid) {
					return [
						'total' => 0,
						'paid' => 0,
						'dues' => 0,
						'currency' => '',
						'invoice_id' => null,
					];
				}

				// 2. Sum payments from invoicepaymentrecords
				$CI->db->select_sum('amount');
				$CI->db->where('invoiceid', $estimate->invoiceid);
				$paid_row = $CI->db->get(db_prefix() . 'invoicepaymentrecords')->row();
				$paid = $paid_row ? (float)$paid_row->amount : 0;

				return [
					'total'     => (float)$estimate->total,
					'paid'      => $paid,
					'dues'      => (float)$estimate->total - $paid,
					'currency'  => $estimate->currency,
					'invoice_id'=> $estimate->invoiceid,
					'date'=> $estimate->date,
					'expirydate'=> $estimate->expirydate,
				];
			}
			$branch = $this->client_model->get_branch();
            // Pass the data to the view
            $data['client_modal'] = $this->load->view('client_model_popup', [
                'estimates' => $estimates,
                'client' => $client,
                'branch' => $branch,
                'casesheet' => $casesheet,
                'testimonials' => $testimonials,
                'shared_requests' => $shared_requests,
                'master_settings' => $master_settings,
                'customer_new_fields' => $customer_new_fields,
                'currencies' => $currencies,
                'taxes' => $taxes,
                'items' => $items,
                'base_currency' => $base_currency,
                'items_groups' => $items_groups,
                'staff' => $staff,
                'estimate_statuses' => $estimate_statuses,
                'appointment_data' => $appointment_data,
                'patient_activity_log' => $patient_activity_log,
                'patient_call_logs' => $patient_call_logs, // NEW
                'patient_prescriptions' => $patient_prescription, // NEW
                'patient_treatment' => $patient_treatment, // NEW
                'medicines' => $medicines, // NEW
                'potencies' => $potencies, // NEW
                'appointment_type' => $appointment_type, // NEW
                'criteria' => $criteria, // NEW
                'doses' => $doses, // NEW
                'treatments' => $treatments, // NEW
                'patient_status' => $patient_status, // NEW
                'invoices' => $invoices, // NEW
                'invoice_payments' => $invoice_payments, // NEW
                'timings' => $timings // NEW
            ], true);
        }
        $this->load->view('calendar_view', $data);
	  
    }
	public function get_appointments_json()
    {

        $appointments = $this->client_model->get_appointments(); // You must define this method in your model.
		
		$events = [];
		foreach ($appointments as $appointment) {
			if($appointment['appointment_date']){
				$appointment_date = $appointment['appointment_date'];
			}else{
				$appointment_date = "";
			}
			
		  $events[] = [
			'title' => $appointment['company'],
			'start' => $appointment_date,// send as 'start' only
			'visit_status' => $appointment['visit_status'],
			'userid' => $appointment['userid']
		  ];
		}
		echo json_encode($events);
	}

	
	public function get_patient_list($id = NULL, $consulted_date = NULL, $consulted_to_date = NULL, $current_branch_id = null, $selected_branch_id = null, $callback_url = NULL)
    {
       if (staff_can('view', 'customers')) {
            //access_denied('appointments');
        } 
        $data['title'] = "Patients";
		$data['consulted_from_date'] = $consulted_date;
		$data['consulted_to_date'] = $consulted_to_date;
		
		$data['doctors'] = $this->doctor_model->get_doctors();
		$data['master_data'] = $this->client_model->get_master_data();
		
		$decodeParam = static function ($value, $default = '') {
			if ($value === null) {
				return $default;
			}
			$value = (string) $value;
			if ($value === '' || strtolower($value) === 'null') {
				return $default;
			}
			do {
				$decoded = rawurldecode($value);
				if ($decoded === $value) {
					break;
				}
				$value = $decoded;
			} while (true);
			return $value;
		};

		$current_branch_id = $decodeParam($current_branch_id, null);
		$selected_branch_id = $decodeParam($selected_branch_id, '');

		$normalizeBranchIds = static function ($value) {
			if ($value === null || $value === '') {
				return [];
			}

			if (is_array($value)) {
				$list = $value;
			} else {
				$list = explode(',', (string) $value);
			}

			$normalized = [];
			foreach ($list as $item) {
				if ($item === null) {
					continue;
				}
				$item = (string) $item;
				if ($item === '' || strtolower($item) === 'null' || !is_numeric($item)) {
					continue;
				}
				$normalized[] = (int) $item;
			}

			return array_values(array_unique($normalized));
		};

		$selected_branch_ids = $normalizeBranchIds($selected_branch_id);
		$current_branch_ids = $normalizeBranchIds($current_branch_id);
		$staff_branch_ids   = $normalizeBranchIds($this->current_branch_id);

		if (empty($selected_branch_ids) && !empty($current_branch_ids)) {
			$selected_branch_ids = $current_branch_ids;
		}

		if (empty($selected_branch_ids) && !empty($staff_branch_ids)) {
			$selected_branch_ids = $staff_branch_ids;
		}

		if (!empty($staff_branch_ids)) {
			$selected_branch_ids = array_values(array_intersect($selected_branch_ids, $staff_branch_ids));
			if (empty($selected_branch_ids)) {
				$selected_branch_ids = $staff_branch_ids;
			}
		}

		$all_branches = $this->client_model->get_branch();
		$visible_branches = $all_branches;
		if (!empty($staff_branch_ids)) {
			$visible_branches = array_values(array_filter($all_branches, function ($branch) use ($staff_branch_ids) {
				return in_array((int) $branch['id'], $staff_branch_ids, true);
			}));

			if (empty($visible_branches)) {
				$visible_branches = $all_branches;
			}
		}

		$data['branch'] = $visible_branches;
		$data['selected_branch_id'] = $selected_branch_ids;
		$data['branch_filter_ids'] = $selected_branch_ids;
		$data['accessible_branch_ids'] = $staff_branch_ids;
		
		if ($current_branch_id !== null && $current_branch_id !== '') {
			$data['current_branch_id'] = $current_branch_id;
		} else {
			$data['current_branch_id'] = $this->current_branch_id;
		}
		
		
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('client', 'tables/get_patient_list'), $data);
        }
		
		$statuses = $this->leads_model->get_status();
		$data['statuses'] = $statuses;
		function get_counter_by_doctor_id($doctor_id)
		{
			$CI =& get_instance();
			$CI->db->where('doctor_id', $doctor_id);
			return $CI->db->get(db_prefix() . 'counter')->row(); // returns single row (object)
		}
		
		if ($id) {
			 $this->load->model('leads_model');
			 $this->load->model('currencies_model');
			 $this->load->model('taxes_model');
			 $this->load->model('invoice_items_model');
			 $this->load->model('estimates_model');
			$data['clientid'] = $id;
            // Fetch the existing patient data
            $client = $this->client_model->get($id);
            $estimates = $this->client_model->get_estimates($id);
			foreach ($estimates as &$estimate) {
				$this->db->select('description');
				$this->db->where('rel_type', 'estimate');
				$this->db->where('rel_id', $estimate['id']);
				$items = $this->db->get('tblitemable')->row();
				
				$estimate['description'] = $items->description; // append to estimate
			}
			
            $customer_new_fields = $this->client_model->get_customer_new_fields($id);
			$currencies= $this->currencies_model->get();
			$taxes = $this->taxes_model->get();
			$items_groups = $this->invoice_items_model->get_groups();
			$staff            = $this->staff_model->get('', ['active' => 1]);
			$estimate_statuses = $this->estimates_model->get_statuses();
			$base_currency = $this->currencies_model->get_base_currency();
			
			$items = $this->invoice_items_model->get_grouped();
            $first_appointment = $this->client_model->get_first_appointment($id);
            $appointment_data = $this->client_model->get_appointment_data($id);
            $patient_activity_log = $this->client_model->get_patient_activity_log($id);
            $patient_prescription = $this->client_model->get_patient_prescription($id);
			
            $patient_treatment = $this->client_model->get_patient_treatment($id);
            $casesheet = $this->client_model->get_casesheet($id);
            // Fetch patient call logs
            $patient_call_logs = $this->client_model->get_patient_call_logs($id); // NEW
            $invoices = $this->client_model->get_invoices($id); // NEW
            $invoice_payments = $this->client_model->get_invoice_payments($id); // NEW
            $shared_requests = $this->client_model->get_shared_requests($id); // NEW

            // Fetch medicine data (names, potencies, doses, timings)
            $medicines = $this->master_model->get_all('medicine');
            $potencies = $this->master_model->get_all('medicine_potency');
            $doses = $this->master_model->get_all('medicine_dose');
            $timings = $this->master_model->get_all('medicine_timing');
            $appointment_type = $this->master_model->get_all('appointment_type');
            $criteria = $this->master_model->get_all('criteria');
            $treatments = $this->master_model->get_all('treatment');
            $patient_status = $this->master_model->get_all('patient_status');
            $master_settings = $this->master_model->get_all('master_settings');
            $suggested_diagnostics = $this->master_model->get_all('suggested_diagnostics');
			$testimonials = $this->client_model->get_testimonial();
			$latest_casesheet = $this->client_model->get_latest_casesheet($id);
			$latest_casesheet_package = $this->client_model->get_latest_casesheet_package($id);
			
			$payment_modes  = $this->client_model->get_payment_modes();
			
			
			$doctors = $this->doctor_model->get_doctors();
			
			$today_appointment_data = $this->client_model->get_today_appointment_data($id);
			
			$staff_id = get_staff_user_id();

			$staff_data = $this->db
				->select('s.staffid, r.name as role_name')
				->from(db_prefix() . 'staff s')
				->join(db_prefix() . 'roles r', 'r.roleid = s.role', 'left')
				->where('s.staffid', $staff_id)
				->get()
				->row();
			
			function get_estimation_payment_summary($estimation_id)
			{
				$CI =& get_instance();

				// 1. Get the estimate row
				$CI->db->select('total, invoiceid, currency, date, expirydate');
				$CI->db->where('id', $estimation_id);
				$estimate = $CI->db->get(db_prefix() . 'estimates')->row();

				if (!$estimate || !$estimate->invoiceid) {
					return [
						'total' => 0,
						'paid' => 0,
						'dues' => 0,
						'currency' => '',
						'invoice_id' => null,
					];
				}

				// 2. Sum payments from invoicepaymentrecords
				$CI->db->select_sum('amount');
				$CI->db->where('invoiceid', $estimate->invoiceid);
				$paid_row = $CI->db->get(db_prefix() . 'invoicepaymentrecords')->row();
				$paid = $paid_row ? (float)$paid_row->amount : 0;

				return [
					'total'     => (float)$estimate->total,
					'paid'      => $paid,
					'dues'      => (float)$estimate->total - $paid,
					'currency'  => $estimate->currency,
					'invoice_id'=> $estimate->invoiceid,
					'date'=> $estimate->date,
					'expirydate'=> $estimate->expirydate,
				];
			}
			$home_branch_id = $this->client_model->get_client_branch($id);
			$branch = $this->client_model->get_branch();
            // Pass the data to the view
            $data['client_modal'] = $this->load->view('client_model_popup', [
                'latest_casesheet' => $latest_casesheet,
                'latest_casesheet_package' => $latest_casesheet_package,
                'first_appointment' => $first_appointment,
                'callback_url' => $callback_url,
                'home_branch_id' => $home_branch_id,
                'branch' => $branch,
                'doctors' => $doctors,
                'statuses' => $statuses,
                'estimates' => $estimates,
                'payment_modes' => $payment_modes,
                'client' => $client,
                'casesheet' => $casesheet,
                'testimonials' => $testimonials,
                'shared_requests' => $shared_requests,
                'master_settings' => $master_settings,
                'suggested_diagnostics' => $suggested_diagnostics,
                'customer_new_fields' => $customer_new_fields,
                'currencies' => $currencies,
                'staff_data' => $staff_data,
                'taxes' => $taxes,
                'items' => $items,
                'base_currency' => $base_currency,
                'items_groups' => $items_groups,
                'staff' => $staff,
                'estimate_statuses' => $estimate_statuses,
                'appointment_data' => $appointment_data,
                'today_appointment_data' => $today_appointment_data,
                'patient_activity_log' => $patient_activity_log,
                'patient_call_logs' => $patient_call_logs, // NEW
                'patient_prescriptions' => $patient_prescription, // NEW
                'patient_treatment' => $patient_treatment, // NEW
                'medicines' => $medicines, // NEW
                'potencies' => $potencies, // NEW
                'appointment_type' => $appointment_type, // NEW
                'criteria' => $criteria, // NEW
                'doses' => $doses, // NEW
                'treatments' => $treatments, // NEW
                'patient_status' => $patient_status, // NEW
                'invoices' => $invoices, // NEW
                'invoice_payments' => $invoice_payments, // NEW
                'timings' => $timings // NEW
            ], true);
        }
       $this->load->view('patients_list', $data);
    }
	
	
public function ajax_get_invoice_payment_data($id)
{
    $this->load->model('invoices_model');
    $this->load->model('payment_modes_model');

    $invoice = $this->invoices_model->get($id);

    if (!$invoice) {
        echo json_encode(['error' => 'Invoice not found']);
        return;
    }

    $all_modes = $this->payment_modes_model->get('', [], true);

    $allowed_modes = [];
    foreach ($all_modes as $mode) {
        if (is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
            $allowed_modes[] = [
                'id' => $mode['id'],
                'name' => $mode['name']
            ];
        }
    }

    echo json_encode([
        'amount' => $invoice->total_left_to_pay,
        'date' => date('Y-m-d'),
        'modes' => $allowed_modes // only allowed ones
    ]);
}


public function get_invoice_data($invoice_id)
{
	$this->load->model('invoices_model');
	$this->load->model('payment_modes_model');
	$invoice = $this->invoices_model->get($invoice_id);
	if (!$invoice) {
		echo json_encode(['error' => 'Invoice not found']);
		return;
	}

	// Calculate left to pay
	$amount_left = $invoice->total_left_to_pay;

	// Get allowed payment modes for this invoice
	$payment_modes = $this->payment_modes_model->get('', [], true);
	
	
	echo json_encode([
		'amount_left' => $amount_left,
		'date' => date('Y-m-d'),
		'payment_modes' => $payment_modes // each mode ['id' => .., 'name' => ..]
	]);
}
	
	public function share_testimonial()
	{
		if ($this->input->is_ajax_request()) {
			$user_id = $this->input->post('patientid');
			$send_email = $this->input->post('send_email') == '1' ? 1 : 0;
			$send_sms = $this->input->post('send_sms') == '1' ? 1 : 0;
			$send_whatsapp = $this->input->post('send_whatsapp') == '1' ? 1 : 0;
			$feedback_id = $this->input->post('feedback_id');
			$created_by = get_staff_user_id(); // Assuming staff is logged in

			$share_key = $this->_generate_share_key();

			$data = [
				'user_id' => $user_id,
				'type' => 'patient',
				'date_sent' => date('Y-m-d H:i:s'),
				'created_by' => $created_by,
				'status' => 'pending',
				'send_email' => $send_email,
				'send_sms' => $send_sms,
				'feedback_id' => $feedback_id,
				'send_whatsapp' => $send_whatsapp,
				'share_key' => $share_key
			];

			$this->db->insert('tblshare_request', $data);
			
			//Getting Email
			$this->db->select('new.email_id, c.company, c.phonenumber');
			$this->db->from(db_prefix() . 'clients as c');
			$this->db->join(db_prefix() . 'clients_new_fields as new', "new.userid=c.userid");
			$this->db->where(array("c.userid"=>$user_id));
			$email_row = $this->db->get()->row();
			$patient_name = $email_row ? $email_row->company : "User";
			$email = $email_row ? $email_row->email_id : null;
			$phonenumber = $email_row ? $email_row->phonenumber : null;
			
			//Getting feedback id
			$this->db->select('title');
			$this->db->where('id', $feedback_id);
			$title_row = $this->db->get('tblflextestimonial')->row();
			$title = $title_row ? $title_row->title : null;
			
			if($send_email == 1) {
				if($email){
					$this->db->select('email_id');
					$this->db->where('userid', $user_id);
					$email_row = $this->db->get('tblclients_new_fields')->row();
					$email = $email_row ? $email_row->email_id : null;

					if ($email) {
						$share_url = 
						$subject = 'We value your feedback!';
						$feedback_link = site_url('review/dr/' . $share_key); // update route as per actual

						$message = get_option('feedback_template_content');
						
						$replacements = [
							'{patient_name}' => htmlspecialchars($patient_name),
							'{mobile}'       => htmlspecialchars($phonenumber),
							'{email}'        => htmlspecialchars($email),
							'{link}'         => htmlspecialchars($feedback_link),
						];

						$final_message = str_replace(array_keys($replacements), array_values($replacements), $message);


						mailflow_send_email($email, $subject, $final_message, 'system'); // or custom SMTP name
						
						/* log_message_communication([
							'userid'       => $user_id,
							'status'       => $status_id,
							'message_type' => 'email',
							'message'      => $final_message,
							'response'      => $result
						]); */
					}
				}
			}
			
			if($send_sms ==  1){
				
				if($phonenumber){
					$feedback_sms_template_id = get_option('feedback_sms_template_id');
					$feedback_sms_template_content = get_option('feedback_sms_template_content');
					
					$replacements = [
						'{patient_name}' => $patient_name,
						'{mobile}'       => $phonenumber,
						'{email}'        => $email,
						'{link}'         => $feedback_link,
					];

					// Match placeholders in order of appearance
					preg_match_all('/\{(.*?)\}/', $feedback_sms_template_content, $matches);

					// Get placeholder keys
					$found_placeholders = $matches[0]; // Example: ['{patient_name}', '{email}', '{link}']

					// Map them to actual values
					$values = [];
					foreach ($found_placeholders as $placeholder) {
						if (isset($replacements[$placeholder])) {
							$values[] = $replacements[$placeholder];
						}
					}

					// Final pipe-separated string of values
					$final_output = implode('|', $values);
					
					$gateway = $this->app_sms->get_active_gateway();

						if ($gateway !== false) {
							$className = 'sms_' . $gateway['id'];
							

							$message = clear_textarea_breaks($content);
							

							$retval = $this->{$className}->send_fastAPI($phonenumber, $feedback_sms_template_id, $final_output);
							
						}
				}
				

			}
			
			if ($send_whatsapp == 1 && $phonenumber) {
				$feedback_whatsapp_template_name = get_option('feedback_whatsapp_template_name');
				$feedback_whatsapp_template_content = get_option('feedback_whatsapp_template_content');

				$replacements = [
					'name' => $patient_name,
					'mobile'       => $phonenumber,
					'email'        => $email,
					'link'         => $feedback_link,
				];

				// Match placeholders like {patient_name}, {email}
				preg_match_all('/\{(.*?)\}/', $feedback_whatsapp_template_content, $matches);
				$found_keys = $matches[1]; // Extracted keys without braces

				$parameterArray = [];

				foreach ($found_keys as $key) {
					if (isset($replacements[$key])) {
						$parameterArray[$key] = $replacements[$key];
					}
				}
				// Send via API
				$retval = send_message_via_api($phonenumber, $feedback_whatsapp_template_name, $parameterArray);
			}
			
			

			echo json_encode(['success' => true]);
		}
	}
	private function _generate_share_key()
	{
		$length = 5;
		do {
			$key = substr(md5(uniqid(mt_rand(), true)), 0, $length);
			$exists = $this->db->where('share_key', $key)->get('tblshare_request')->num_rows();
		} while ($exists > 0);
		return strtoupper($key);
	}

	public function get_testimonial_responses()
	{
		$id = $this->input->post('id');
		$responses = $this->client_model->get_testimonial_responses(['request_id' => $id]);

		header('Content-Type: application/json');
		echo json_encode($responses);
		exit;
	}
	
	public function edit_appointment($id, $call_back = NULL, $lead_id = NULL)
	{
		$this->load->model('client_model');
		$this->load->model('master_model');
		$this->load->model('invoice_items_model');

		$appointment = $this->client_model->get_appointment_by_id($id);
		if (!$appointment) {
			set_alert('danger', 'Appointment not found');
			redirect(admin_url('clients'));
		}
		$data['items'] = $this->invoice_items_model->get_grouped();
		$data['appointment'] = $appointment;
		$data['branch'] = $this->client_model->get_branch();
		$data['assign_doctor'] = $this->doctor_model->get_doctors();
		$data['treatment'] = $this->master_model->get_all('treatment');
		$data['appointment_type'] = $this->master_model->get_all('appointment_type');
		$data['consultation_fee'] = $this->master_model->get_all('consultation_fee');
		$this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
		
		$data['call_back'] = $call_back ?? null;
		$data['lead_id']   = $lead_id ?? null;

		$this->load->view('appointment_edit', $data);
	}
	
	public function update_appointment()
	{
		if ($this->input->post()) {
			
			$data = $this->input->post();
			$call_back = $data['call_back'] ?? null;
			$lead_id   = $data['lead_id'] ?? null;
			$appointment_id = $data['appointment_id'];
			unset($data['appointment_id']);
			unset($data['call_back']);
			unset($data['lead_id']);

			$success = $this->client_model->update_appointment($appointment_id, $data, $lead_id);

			if ($success) {
				set_alert('success', _l('updated_successfully', _l('appointment')));
			} else {
				set_alert('danger', _l('update_failed'));
			}
			
			if($call_back != NULL){
				if($call_back == "lead"){
					if($lead_id){
						
						
						redirect(admin_url('leads/index/'.$lead_id.'/#appointment'));
						
				}else{
					redirect(admin_url('leads/index/'));
				}
					
				}else{
					redirect(admin_url('client/get_patient_list/#appointments-tab'));
				}
			}else{
				redirect(admin_url('client/get_patient_list/#appointments-tab'));
			}
			
		}
	}
	public function calling($type, $id = NULL, $date_filter = NULL, $from_date = NULL, $to_date = NULL, $selected_branch_id = NULL)
	{
		$data['title'] = _l($type);
		$data['type'] = $type;
		$data['date_filter'] = $date_filter;
		$data['from_date'] = $from_date;
		$data['to_date'] = $to_date;
		$data['master_settings'] = $this->master_model->get_all('master_settings');
		$data['branch'] = $this->client_model->get_branch();
		
		$selected_branch_id = urldecode($selected_branch_id); // decode %2C to ,
		$selected_branch_id = explode(',', $selected_branch_id); // split by comma

		// Clean the array to ensure numeric values only
		$selected_branch_id = array_filter($selected_branch_id, fn($id) => is_numeric($id));

		// Optional: cast to int
		$selected_branch_id = array_map('intval', $selected_branch_id);

		$data['selected_branch_id'] = $selected_branch_id;
		
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('client', 'tables/calling_table'), $data);
		}

		$statuses = $this->leads_model->get_status();
		$data['statuses'] = $statuses;
		function get_counter_by_doctor_id($doctor_id)
		{
			$CI =& get_instance();
			$CI->db->where('doctor_id', $doctor_id);
			return $CI->db->get(db_prefix() . 'counter')->row(); // returns single row (object)
		}
		if ($id) {
			 $this->load->model('leads_model');
			 $this->load->model('currencies_model');
			 $this->load->model('taxes_model');
			 $this->load->model('invoice_items_model');
			 $this->load->model('estimates_model');
			$data['clientid'] = $id;
            // Fetch the existing patient data
            $client = $this->client_model->get($id);
            $estimates = $this->client_model->get_estimates($id);
			foreach ($estimates as &$estimate) {
				$this->db->select('description');
				$this->db->where('rel_type', 'estimate');
				$this->db->where('rel_id', $estimate['id']);
				$items = $this->db->get('tblitemable')->row();
				
				$estimate['description'] = $items->description; // append to estimate
			}
			
            $customer_new_fields = $this->client_model->get_customer_new_fields($id);
			$currencies= $this->currencies_model->get();
			$taxes = $this->taxes_model->get();
			$items_groups = $this->invoice_items_model->get_groups();
			$staff            = $this->staff_model->get('', ['active' => 1]);
			$estimate_statuses = $this->estimates_model->get_statuses();
			$base_currency = $this->currencies_model->get_base_currency();
			
			$items = $this->invoice_items_model->get_grouped();
            $appointment_data = $this->client_model->get_appointment_data($id);
            $patient_activity_log = $this->client_model->get_patient_activity_log($id);
            $patient_prescription = $this->client_model->get_patient_prescription($id);
			
            $patient_treatment = $this->client_model->get_patient_treatment($id);
            $casesheet = $this->client_model->get_casesheet($id);
            // Fetch patient call logs
            $patient_call_logs = $this->client_model->get_patient_call_logs($id); // NEW
            $invoices = $this->client_model->get_invoices($id); // NEW
            $invoice_payments = $this->client_model->get_invoice_payments($id); // NEW
            $shared_requests = $this->client_model->get_shared_requests($id); // NEW

            // Fetch medicine data (names, potencies, doses, timings)
            $medicines = $this->master_model->get_all('medicine');
            $potencies = $this->master_model->get_all('medicine_potency');
            $doses = $this->master_model->get_all('medicine_dose');
            $timings = $this->master_model->get_all('medicine_timing');
            $appointment_type = $this->master_model->get_all('appointment_type');
            $criteria = $this->master_model->get_all('criteria');
            $treatments = $this->master_model->get_all('treatment');
            $patient_status = $this->master_model->get_all('patient_status');
            $master_settings = $this->master_model->get_all('master_settings');
            $suggested_diagnostics = $this->master_model->get_all('suggested_diagnostics');
			$testimonials = $this->client_model->get_testimonial();
			$latest_casesheet = $this->client_model->get_latest_casesheet($id);
			$latest_casesheet_package = $this->client_model->get_latest_casesheet_package($id);
			
			$payment_modes  = $this->client_model->get_payment_modes();
			
			
			$doctors = $this->doctor_model->get_doctors();
			
			$today_appointment_data = $this->client_model->get_today_appointment_data($id);
			
			function get_estimation_payment_summary($estimation_id)
			{
				$CI =& get_instance();

				// 1. Get the estimate row
				$CI->db->select('total, invoiceid, currency, date, expirydate');
				$CI->db->where('id', $estimation_id);
				$estimate = $CI->db->get(db_prefix() . 'estimates')->row();

				if (!$estimate || !$estimate->invoiceid) {
					return [
						'total' => 0,
						'paid' => 0,
						'dues' => 0,
						'currency' => '',
						'invoice_id' => null,
					];
				}

				// 2. Sum payments from invoicepaymentrecords
				$CI->db->select_sum('amount');
				$CI->db->where('invoiceid', $estimate->invoiceid);
				$paid_row = $CI->db->get(db_prefix() . 'invoicepaymentrecords')->row();
				$paid = $paid_row ? (float)$paid_row->amount : 0;

				return [
					'total'     => (float)$estimate->total,
					'paid'      => $paid,
					'dues'      => (float)$estimate->total - $paid,
					'currency'  => $estimate->currency,
					'invoice_id'=> $estimate->invoiceid,
					'date'=> $estimate->date,
					'expirydate'=> $estimate->expirydate,
				];
			}

			$callback_url = "calling/".$type;
			$branch = $this->client_model->get_branch();
            // Pass the data to the view
            $data['client_modal'] = $this->load->view('client_model_popup', [
                'latest_casesheet' => $latest_casesheet,
                'latest_casesheet_package' => $latest_casesheet_package,
                'callback_url' => $callback_url,
                'branch' => $branch,
                'doctors' => $doctors,
                'statuses' => $statuses,
                'estimates' => $estimates,
                'payment_modes' => $payment_modes,
                'client' => $client,
                'casesheet' => $casesheet,
                'testimonials' => $testimonials,
                'shared_requests' => $shared_requests,
                'master_settings' => $master_settings,
                'suggested_diagnostics' => $suggested_diagnostics,
                'customer_new_fields' => $customer_new_fields,
                'currencies' => $currencies,
                'taxes' => $taxes,
                'items' => $items,
                'base_currency' => $base_currency,
                'items_groups' => $items_groups,
                'staff' => $staff,
                'estimate_statuses' => $estimate_statuses,
                'appointment_data' => $appointment_data,
                'today_appointment_data' => $today_appointment_data,
                'patient_activity_log' => $patient_activity_log,
                'patient_call_logs' => $patient_call_logs, // NEW
                'patient_prescriptions' => $patient_prescription, // NEW
                'patient_treatment' => $patient_treatment, // NEW
                'medicines' => $medicines, // NEW
                'potencies' => $potencies, // NEW
                'appointment_type' => $appointment_type, // NEW
                'criteria' => $criteria, // NEW
                'doses' => $doses, // NEW
                'treatments' => $treatments, // NEW
                'patient_status' => $patient_status, // NEW
                'invoices' => $invoices, // NEW
                'invoice_payments' => $invoice_payments, // NEW
                'timings' => $timings // NEW
            ], true);
        }
		$this->load->view('calling', $data);
	}
	
	public function reports($type, $id=NULL, $consulted_date = NULL, $consulted_to_date = NULL, $appointment_type = NULL, $selected_branch_id = NULL, $doctor_id = NULL, $category = NULL, $lead_sourceId = NULL){
		$data['title'] = _l($type);
		$data['type'] = $type;
		$data['category'] = $category;
		$data['consulted_from_date'] = $consulted_date;
		$data['consulted_to_date'] = $consulted_to_date;
		$data['appointment_type_id'] = $appointment_type;
		$selected_branch_id = urldecode($selected_branch_id); // decode %2C to ,
		$selected_branch_id = explode(',', $selected_branch_id); // split by comma
		
		// Clean the array to ensure numeric values only
		$selected_branch_id = array_filter($selected_branch_id, fn($id) => is_numeric($id));

		// Optional: cast to int
		$selected_branch_id = array_map('intval', $selected_branch_id);
		
		$data['selected_branch_id'] = $selected_branch_id;
		
		/* $lead_sourceId = urldecode($lead_sourceIds); // decode %2C to ,
		$lead_sourceId = explode(',', $lead_sourceId); // split by comma

		// Clean the array to ensure numeric values only
		$lead_sourceId = array_filter($lead_sourceId, fn($id) => is_numeric($id));

		// Optional: cast to int
		$lead_sourceId = array_map('intval', $lead_sourceId); */

		$data['lead_sourceId'] = $lead_sourceId;
		
		$doctor_id = urldecode($doctor_id); // decode %2C to ,
		$doctor_id = explode(',', $doctor_id); // split by comma

		// Clean the array to ensure numeric values only
		$doctor_id = array_filter($doctor_id, fn($id) => is_numeric($id));

		// Optional: cast to int
		$doctor_id = array_map('intval', $doctor_id);

		$data['doctor_id'] = $doctor_id;
		
		//Getting payment modes
		$this->db->select('name');
		$this->db->from(db_prefix() . 'payment_modes');
		$this->db->where('active', 1);
		$payment_modes = $this->db->get()->result_array();
		
		$data['payment_modes'] = $payment_modes;
		$data['master_settings'] = $this->master_model->get_all('master_settings');
		$data['branch'] = $this->client_model->get_branch();
		$data['roles'] = $this->client_model->get_roles();
		$data['leads_sources'] = $this->client_model->get_leads_sources();
		$data['appointment_type'] = $this->master_model->get_all('appointment_type');
		$data['doctors'] = $this->doctor_model->get_doctors();
		$staff_id = get_staff_user_id();
        if ($staff_id) {
            $this->db->select('branch_id');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where('staffid', $staff_id);
            $row = $this->db->get()->row();
			if($row){
				$data['branch_id'] = $row->branch_id;
			}
            
        }
		function get_counter_by_doctor_id($doctor_id)
		{
			$CI =& get_instance();
			$CI->db->where('doctor_id', $doctor_id);
			return $CI->db->get(db_prefix() . 'counter')->row(); // returns single row (object)
		}
		
		if ($id && $id != 'NULL') {
			 $this->load->model('leads_model');
			 $this->load->model('currencies_model');
			 $this->load->model('taxes_model');
			 $this->load->model('invoice_items_model');
			 $this->load->model('estimates_model');
			$data['clientid'] = $id;
            // Fetch the existing patient data
            $client = $this->client_model->get($id);
            $estimates = $this->client_model->get_estimates($id);
			foreach ($estimates as &$estimate) {
				$this->db->select('description');
				$this->db->where('rel_type', 'estimate');
				$this->db->where('rel_id', $estimate['id']);
				$items = $this->db->get('tblitemable')->row();
				
				$estimate['description'] = $items->description; // append to estimate
			}
			
            $customer_new_fields = $this->client_model->get_customer_new_fields($id);
			$currencies= $this->currencies_model->get();
			$taxes = $this->taxes_model->get();
			$items_groups = $this->invoice_items_model->get_groups();
			$staff            = $this->staff_model->get('', ['active' => 1]);
			$estimate_statuses = $this->estimates_model->get_statuses();
			$base_currency = $this->currencies_model->get_base_currency();
			
			$items = $this->invoice_items_model->get_grouped();
            $first_appointment = $this->client_model->get_first_appointment($id);
            $appointment_data = $this->client_model->get_appointment_data($id);
            $patient_activity_log = $this->client_model->get_patient_activity_log($id);
            $patient_prescription = $this->client_model->get_patient_prescription($id);
			
            $patient_treatment = $this->client_model->get_patient_treatment($id);
            $casesheet = $this->client_model->get_casesheet($id);
            // Fetch patient call logs
            $patient_call_logs = $this->client_model->get_patient_call_logs($id); // NEW
            $invoices = $this->client_model->get_invoices($id); // NEW
            $invoice_payments = $this->client_model->get_invoice_payments($id); // NEW
            $shared_requests = $this->client_model->get_shared_requests($id); // NEW

            // Fetch medicine data (names, potencies, doses, timings)
            $medicines = $this->master_model->get_all('medicine');
            $potencies = $this->master_model->get_all('medicine_potency');
            $doses = $this->master_model->get_all('medicine_dose');
            $timings = $this->master_model->get_all('medicine_timing');
            $appointment_type = $this->master_model->get_all('appointment_type');
            $criteria = $this->master_model->get_all('criteria');
            $treatments = $this->master_model->get_all('treatment');
            $patient_status = $this->master_model->get_all('patient_status');
            $master_settings = $this->master_model->get_all('master_settings');
            $suggested_diagnostics = $this->master_model->get_all('suggested_diagnostics');
			$testimonials = $this->client_model->get_testimonial();
			$latest_casesheet = $this->client_model->get_latest_casesheet($id);
			$latest_casesheet_package = $this->client_model->get_latest_casesheet_package($id);
			
			$payment_modes  = $this->client_model->get_payment_modes();
			
			
			$doctors = $this->doctor_model->get_doctors();
			
			$today_appointment_data = $this->client_model->get_today_appointment_data($id);
			
			$staff_id = get_staff_user_id();

			$staff_data = $this->db
				->select('s.staffid, r.name as role_name')
				->from(db_prefix() . 'staff s')
				->join(db_prefix() . 'roles r', 'r.roleid = s.role', 'left')
				->where('s.staffid', $staff_id)
				->get()
				->row();
			
			function get_estimation_payment_summary($estimation_id)
			{
				$CI =& get_instance();

				// 1. Get the estimate row
				$CI->db->select('total, invoiceid, currency, date, expirydate');
				$CI->db->where('id', $estimation_id);
				$estimate = $CI->db->get(db_prefix() . 'estimates')->row();

				if (!$estimate || !$estimate->invoiceid) {
					return [
						'total' => 0,
						'paid' => 0,
						'dues' => 0,
						'currency' => '',
						'invoice_id' => null,
					];
				}

				// 2. Sum payments from invoicepaymentrecords
				$CI->db->select_sum('amount');
				$CI->db->where('invoiceid', $estimate->invoiceid);
				$paid_row = $CI->db->get(db_prefix() . 'invoicepaymentrecords')->row();
				$paid = $paid_row ? (float)$paid_row->amount : 0;

				return [
					'total'     => (float)$estimate->total,
					'paid'      => $paid,
					'dues'      => (float)$estimate->total - $paid,
					'currency'  => $estimate->currency,
					'invoice_id'=> $estimate->invoiceid,
					'date'=> $estimate->date,
					'expirydate'=> $estimate->expirydate,
				];
			}
			$home_branch_id = $this->client_model->get_client_branch($id);
			$branch = $this->client_model->get_branch();
            // Pass the data to the view
            $data['client_modal'] = $this->load->view('client_model_popup', [
                'latest_casesheet' => $latest_casesheet,
                'latest_casesheet_package' => $latest_casesheet_package,
                'first_appointment' => $first_appointment,
                'callback_url' => $callback_url,
                'home_branch_id' => $home_branch_id,
                'branch' => $branch,
                'doctors' => $doctors,
                'statuses' => $statuses,
                'estimates' => $estimates,
                'payment_modes' => $payment_modes,
                'client' => $client,
                'casesheet' => $casesheet,
                'testimonials' => $testimonials,
                'shared_requests' => $shared_requests,
                'master_settings' => $master_settings,
                'suggested_diagnostics' => $suggested_diagnostics,
                'customer_new_fields' => $customer_new_fields,
                'currencies' => $currencies,
                'staff_data' => $staff_data,
                'taxes' => $taxes,
                'items' => $items,
                'base_currency' => $base_currency,
                'items_groups' => $items_groups,
                'staff' => $staff,
                'estimate_statuses' => $estimate_statuses,
                'appointment_data' => $appointment_data,
                'today_appointment_data' => $today_appointment_data,
                'patient_activity_log' => $patient_activity_log,
                'patient_call_logs' => $patient_call_logs, // NEW
                'patient_prescriptions' => $patient_prescription, // NEW
                'patient_treatment' => $patient_treatment, // NEW
                'medicines' => $medicines, // NEW
                'potencies' => $potencies, // NEW
                'appointment_type' => $appointment_type, // NEW
                'criteria' => $criteria, // NEW
                'doses' => $doses, // NEW
                'treatments' => $treatments, // NEW
                'patient_status' => $patient_status, // NEW
                'invoices' => $invoices, // NEW
                'invoice_payments' => $invoice_payments, // NEW
                'timings' => $timings // NEW
            ], true);
        }
			
			if ($this->input->is_ajax_request()) {
				$this->app->get_table_data(module_views_path('client', 'tables/'.$type."_table"), $data);
			}
			$this->load->view('reports/'.$type, $data);
		
		
	}
	
	public function source_enquiry_detail_report($source_id, $from = null, $to = null, $subtype = null)
	{
		if ($this->input->is_ajax_request()) {
			$data['source_id'] = $source_id;
			$data['from_date'] = ($from !== '-' ? $from : null);
			$data['to_date']   = ($to !== '-' ? $to : null);
			$data['subtype']   = $subtype;

			return $this->app->get_table_data(module_views_path('client', 'tables/' . $subtype . '_table'), $data);
		}

		$data['source_id'] = $source_id;
		$data['from_date'] = ($from !== '-' ? $from : null);
		$data['to_date']   = ($to !== '-' ? $to : null);
		$data['subtype']   = $subtype;
		$data['title']     = 'Source Enquiry Detail Report';

		$this->load->view('reports/source_enquiry_detail_report', $data);
	}





	
	public function get_branch_report_data()
	{
		$data = [
			[
				'branch_name' => 'TEST BRANCH',
				'total_registrations' => 3,
				'ref_reg' => 0,
				'package_amount' => 0,
				'paid_amount' => 0,
				'due_amount' => 0,
				'walkin_reg' => 0,

				'cc_reg' => 3,
				'enquiry_package_amount' => '38,200',
				'enquiry_paid_amount' => '37,300',
				'enquiry_due_amount' => '900',
				'enquiry_blank1' => '',
				'enquiry_blank2' => '',

				'renewal_reg' => 0,
				'renewal_package_amount' => 0,
				'renewal_paid_amount' => 0,
				'renewal_due_amount' => 0,
			],
			[
				'branch_name' => '<strong>Grand Total</strong>',
				'total_registrations' => 3,
				'ref_reg' => 0,
				'package_amount' => 0,
				'paid_amount' => 0,
				'due_amount' => 0,
				'walkin_reg' => 0,

				'cc_reg' => 3,
				'enquiry_package_amount' => '<strong>38,200</strong>',
				'enquiry_paid_amount' => '<strong>37,300</strong>',
				'enquiry_due_amount' => '<strong>900</strong>',
				'enquiry_blank1' => '',
				'enquiry_blank2' => '',

				'renewal_reg' => 0,
				'renewal_package_amount' => 0,
				'renewal_paid_amount' => 0,
				'renewal_due_amount' => 0,
			],
		];

		echo json_encode($data);
		exit;
	}
	
	public function log_patient_journey()
	{
		$userid = $this->input->post('userid');
		$status = $this->input->post('status');
		$remarks = $this->input->post('remarks');

		$this->client_model->patient_journey_log_event($userid, $status, $remarks);

		echo json_encode(['success' => true]);
		return;
	}

	public function generate_mr_no()
	{
		$userid = $this->input->post('userid');

		$this->client_model->generate_mr_no($userid);

		echo json_encode(['success' => true]);
		return;
	}

	public function register_patient()
	{
		$userid = $this->input->post('userid');
		$invoiceId = $this->input->post('invoiceId');

		$this->client_model->register_patient($userid, $invoiceId);

		echo json_encode(['success' => true]);
		return;
	}

	
	public function get_journey_log($userid)
	{
		$this->db->where('userid', $userid);
		$this->db->order_by('created_at', 'asc');
		$logs = $this->db->get(db_prefix() . 'patient_journey_log')->result_array();

		// Add CSS styling
		$html = '<style>
			.journey-log-table {
				width: 100%;
				border-collapse: collapse;
				margin: 20px 0;
				font-family: Arial, sans-serif;
				box-shadow: 0 0 10px rgba(0,0,0,0.1);
			}
			.journey-log-table thead tr {
				background-color: #2c3e50;
				color: #ffffff;
				text-align: left;
			}
			.journey-log-table th,
			.journey-log-table td {
				padding: 12px 15px;
				border: 1px solid #dddddd;
			}
			.journey-log-table tbody tr {
				border-bottom: 1px solid #dddddd;
			}
			.journey-log-table tbody tr:nth-of-type(even) {
				background-color: #f3f3f3;
			}
			.journey-log-table tbody tr:last-of-type {
				border-bottom: 2px solid #2c3e50;
			}
			.journey-log-table tbody tr:hover {
				background-color: #f1f1f1;
			}
			.tat-row {
				background-color: #e74c3c;
				!color: white;
				font-weight: bold;
			}
			.tat-row td {
				padding: 10px 15px;
			}
		</style>';

		$html .= '<table class="journey-log-table">';
		$html .= '<thead><tr><th>Date</th><th>Status</th></tr></thead><tbody>';

		foreach ($logs as $log) {
			$html .= '<tr>';
			$html .= '<td>' . _dt($log['created_at']) . '</td>';
			$html .= '<td>' . ucfirst(str_replace("_", " ", $log['status'])) . '</td>';
			$html .= '</tr>';
		}

		// Calculate TAT if there are at least 2 logs
		if (count($logs) >= 2) {
			$firstDate = strtotime($logs[0]['created_at']);
			$lastDate = strtotime($logs[count($logs)-1]['created_at']);
			$diff = $lastDate - $firstDate;
			
			$days = floor($diff / (60 * 60 * 24));
			$hours = floor(($diff - ($days * 60 * 60 * 24)) / (60 * 60));
			$minutes = floor(($diff - ($days * 60 * 60 * 24) - ($hours * 60 * 60)) / 60);
			$seconds = $diff % 60;
			
			$tatString = '';
			if ($days > 0) $tatString .= $days . ' day' . ($days > 1 ? 's ' : ' ');
			if ($hours > 0) $tatString .= $hours . ' hour' . ($hours > 1 ? 's ' : ' ');
			if ($minutes > 0) $tatString .= $minutes . ' minute' . ($minutes > 1 ? 's ' : ' ');
			if ($seconds > 0 || $tatString === '') $tatString .= $seconds . ' second' . ($seconds != 1 ? 's' : '');
			
			$html .= '<tr class="tat-row"><td colspan="3">';
			$html .= '<i class="fas fa-clock"></i> Turnaround Time: ' . trim($tatString);
			$html .= '</td></tr>';
		}

		$html .= '</tbody></table>';

		echo $html;
	}

	public function get_client_summary()
	{
		$from = $this->input->post('from_date') ?: "2011-01-01";
		$to   = $this->input->post('to_date') ?: date('Y-m-d');
		$branch_id = $this->input->post('branch_id');

		$decodeParam = static function ($value) {
			if ($value === null) {
				return '';
			}
			$value = (string) $value;
			if ($value === '' || strtolower($value) === 'null') {
				return '';
			}
			do {
				$decoded = rawurldecode($value);
				if ($decoded === $value) {
					break;
				}
				$value = $decoded;
			} while (true);
			return $value;
		};

		$selected_branch_raw = $decodeParam($branch_id);
		$selected_branch_id = array_filter(array_map('intval', array_filter(explode(',', $selected_branch_raw), fn($id) => $id !== '' && is_numeric($id))));
		if (empty($selected_branch_id) && !empty($this->current_branch_id)) {
			$selected_branch_id = is_array($this->current_branch_id) ? array_map('intval', $this->current_branch_id) : [(int) $this->current_branch_id];
		}

		$from_date = $to_date = null;
		if ($from && $to) {
			$from_date = to_sql_date($from);
			$to_date = to_sql_date($to);
		}

		$summary = [
			'registered' => 0,
			'not_registered' => 0,
			'new_patients' => 0,
			'renewal_patients' => 0,
			'no_due_registered_patients' => 0,
			'due_patients' => 0,
		];

		// Status arrays for bulk update
		$registered_patient_ids = [];
		$renewal_due_patient_ids = [];
		$due_patient_ids = [];

		// Step 1: Get client IDs
		$this->db->select('c.userid');
		$this->db->from(db_prefix() . 'clients AS c');
		$this->db->join(db_prefix() . 'customer_groups group', 'group.customer_id = c.userid', 'left');
		$this->db->join(db_prefix() . 'clients_new_fields new', 'c.userid = new.userid', 'left');

		if ($selected_branch_id) {
			$this->db->where_in('group.groupid', $selected_branch_id);
		}
		if ($from_date && $to_date) {
			$this->db->where("DATE(new.registration_start_date) BETWEEN '$from_date' AND '$to_date'");
		}
		$client_ids_result = $this->db->get()->result_array();
		$client_ids = array_column($client_ids_result, 'userid');
		$client_ids = array_values(array_unique(array_column($client_ids_result, 'userid')));

		if (empty($client_ids)) {
			echo json_encode($summary);
			return;
		}

		$this->db->distinct();
		$this->db->select('new.userid, mr_no');
		$this->db->from(db_prefix() . 'clients_new_fields as new');
		$this->db->join(db_prefix() . 'clients c', 'c.userid = new.userid', 'left');
		$this->db->join(db_prefix() . 'customer_groups group', 'group.customer_id = c.userid', 'left');

		if ($selected_branch_id) {
			$this->db->where_in('group.groupid', $selected_branch_id);
		}
		$this->db->where_in('new.userid', $client_ids);
		$this->db->where('mr_no !=', ''); // registered only

		$registered_fields = $this->db->get()->result_array();

		$registered_ids = [];
		foreach ($registered_fields as $rf) {
			$summary['registered']++;
			$summary['new_patients']++;
			$registered_ids[] = $rf['userid'];
			$registered_patient_ids[] = $rf['userid']; // store for bulk update
		}

		// --- Not registered patients (mr_no empty) ---
		$this->db->distinct();
		$this->db->select('new.userid, mr_no');
		$this->db->from(db_prefix() . 'clients_new_fields as new');
		$this->db->join(db_prefix() . 'clients c', 'c.userid = new.userid', 'left');
		$this->db->join(db_prefix() . 'customer_groups group', 'group.customer_id = c.userid', 'left');

		if ($selected_branch_id) {
			$this->db->where_in('group.groupid', $selected_branch_id);
		}
		//$this->db->where_in('new.userid', $client_ids);
		$this->db->group_start();
		$this->db->where('mr_no', '');                       // empty
		$this->db->or_where('mr_no IS NULL', null, false);   // null
		$this->db->group_end();
		// not registered only

		$not_registered_fields = $this->db->get()->result_array();

		foreach ($not_registered_fields as $nrf) {
			$summary['not_registered']++;
		}

		// Step 2.5: Renewal patients
		if (!empty($registered_ids)) {
			$this->db->select('clientid, MAX(duedate) as latest_expiry');
			$this->db->from(db_prefix() . 'invoices');
			$this->db->where_in('clientid', $registered_ids);
			$this->db->where('duedate IS NOT NULL');
			$this->db->group_by('clientid');
			$estimates = $this->db->get()->result_array();

			foreach ($estimates as $est) {
				if (
					!empty($est['latest_expiry']) &&
					$est['latest_expiry'] >= $from_date &&
					$est['latest_expiry'] <= $to_date
				) {
					$summary['renewal_patients']++;
					$renewal_due_patient_ids[] = $est['clientid'];
				}
			}
		}

		// Step 3: Invoice status for due calculation
		if (!empty($registered_ids)) {
			$this->db->select('clientid, status');
			$this->db->from(db_prefix() . 'invoices');
			$this->db->where_in('clientid', $registered_ids);
			if ($from_date && $to_date) {
				$this->db->where("DATE(date) BETWEEN '$from_date' AND '$to_date'");
			}

			$invoices = $this->db->get()->result_array();
			$invoice_map = [];

			foreach ($invoices as $inv) {
				$invoice_map[$inv['clientid']][] = $inv['status'];
			}

			foreach ($registered_ids as $cid) {
				$statuses = $invoice_map[$cid] ?? [];
				if (empty($statuses)) {
					$summary['no_due_registered_patients']++;
				} elseif (count(array_unique($statuses)) === 1 && $statuses[0] == 2) {
					$summary['no_due_registered_patients']++;
				} else {
					$summary['due_patients']++;
					$due_patient_ids[] = $cid;
				}
			}
		}

		echo json_encode($summary);
	}
	public function update_client_statuses($from_date = "2011-01-01", $to_date = null, $branch_id = null) 
{
    $today = date('Y-m-d');
    if (is_null($to_date)) {
        $to_date = $today;
    }

    // Fetch clients with MR No from tblclients_new_fields
    $this->db->select('c.userid, nf.mr_no');
    $this->db->from(db_prefix() . 'clients c');
    $this->db->join(db_prefix() . 'clients_new_fields nf', 'nf.userid = c.userid', 'left');

    if ($branch_id) {
        $this->db->where('c.branch_id', $branch_id);
    }
    $this->db->where('c.datecreated >=', $from_date);
    $this->db->where('c.datecreated <=', $to_date);

    $clients = $this->db->get()->result_array();

    foreach ($clients as $client) {
        $cid = $client['userid'];
        $mr_no = trim($client['mr_no']);

        // Get invoices with items for the client excluding "Consultation Fee"
		$this->db->select('inv.id, inv.status, inv.duedate, item.description');
		$this->db->from(db_prefix() . 'invoices inv');
		$this->db->join(
			db_prefix() . 'itemable item',
			'item.rel_id = inv.id AND item.rel_type = "invoice"',
			'left'
		);
		$this->db->where('inv.clientid', $cid);
		$this->db->where('item.description !=', 'Consultation Fee'); // exclude Consultation Fee
		$invoices = $this->db->get()->result_array();


        $invoice_count = 0; // excluding consultation fee
        $has_dues = false;
        $max_due_date = null;

        foreach ($invoices as $inv) {
            // Any invoice with status != 2 means dues exist
            if ((int)$inv['status'] !== 2) {
                $has_dues = true;
            }

            // Exclude Consultation Fee invoices from registration/renewal count
            if (stripos($inv['description'], 'consultation fee') === false) {
                $invoice_count++;
                if (!empty($inv['duedate'])) {
                    if (is_null($max_due_date) || $inv['duedate'] > $max_due_date) {
                        $max_due_date = $inv['duedate'];
                    }
                }
            }
        }

        // Determine status
        if ($invoice_count === 0 && $mr_no === '' && empty($invoices)) {
            $status = 'Not Registered';
        } elseif ($invoice_count === 1 && !$has_dues && $max_due_date < $today) {
            $status = 'Patient Registered';
        } elseif ($invoice_count > 1 && !$has_dues && $max_due_date < $today) {
            $status = 'Renewal Patient';
        } elseif ($invoice_count > 1 && !$has_dues && $max_due_date >= $today) {
            $status = 'Renewal Pending Patient';
        } elseif ($invoice_count > 1 && $has_dues) {
            $status = 'Renewal Due Patient';
        } elseif ($invoice_count === 1 && $has_dues && $max_due_date < $today) {
            $status = 'Due Patient';
        } else {
            $status = 'Patient Registered';
        }

        // Update in tblclients_new_fields.current_status
        $this->db->where('userid', $cid);
        $this->db->update(db_prefix() . 'clients_new_fields', ['current_status' => $status]);
    }

    return true;
}





	public function get_appointment_summary()
	{
		$from = $this->input->post('from_date');
		$to = $this->input->post('to_date');
		$enquiry_doctor_id = $this->input->post('enquiry_doctor_id');
		$branch_id = $this->input->post('branch_id');
		$appointment_type_id = $this->input->post('appointment_type_id');
		if($branch_id == 0){
			//$branch_id = $this->current_branch_id;
		}
		$decodeParam = static function ($value) {
			if ($value === null) {
				return '';
			}
			$value = (string) $value;
			if ($value === '' || strtolower($value) === 'null') {
				return '';
			}
			do {
				$decoded = rawurldecode($value);
				if ($decoded === $value) {
					break;
				}
				$value = $decoded;
			} while (true);
			return $value;
		};
		$selected_branch_decoded = $decodeParam($branch_id);
		$selected_branch_id = array_filter(array_map('intval', array_filter(explode(',', $selected_branch_decoded), fn($id) => $id !== '' && is_numeric($id))));
 		
		//$selected_branch_id = $selected_branch_id ?: $this->current_branch_id;
		
		$staff_id = get_staff_user_id();

		$staff_data = $this->db
			->select('s.staffid, r.name as role_name')
			->from(db_prefix() . 'staff s')
			->join(db_prefix() . 'roles r', 'r.roleid = s.role', 'left')
			->where('s.staffid', $staff_id)
			->get()
			->row();

		$from_date = $from ? to_sql_date($from) : null;
		$to_date = $to ? to_sql_date($to) : null;

		$this->db->from(db_prefix() . 'appointment');

		if ($from_date && $to_date) {
			$this->db->where("DATE(appointment_date) BETWEEN '$from_date' AND '$to_date'");
		} elseif ($from_date) {
			$this->db->where('DATE(appointment_date)', $from_date);
		}else{
			$from_date = date('Y-m-d');
			$to_date = date('Y-m-d');
			$this->db->where("DATE(appointment_date) BETWEEN '$from_date' AND '$to_date'");
		}

		if (!staff_can('view_global_appointments', 'customers')) {
			if (!empty($staff_data) && in_array(strtolower($staff_data->role_name), ['doctor'])) {
				$this->db->where('enquiry_doctor_id', $staff_data->staffid);	
			}else{
				if (!empty($enquiry_doctor_id)) {
					$this->db->where('enquiry_doctor_id', $enquiry_doctor_id);
				}
			}
		}
		if (!empty($appointment_type_id)) {
			$this->db->where('appointment_type_id', $appointment_type_id);
		}
		if (!empty($selected_branch_id)) {
			if($branch_id != 0){
				$this->db->where_in('branch_id', $selected_branch_id);
			}
			
		}
		//echo $this->db->get_compiled_select();
		$total_appointments = $this->db->count_all_results();

		// Missed
		$this->db->from(db_prefix() . 'appointment');
		$this->db->where('visit_status', 0);

		// Case 1: If date range is given, filter within that range AND < today
		if ($from_date && $to_date) {
			$this->db->where("DATE(appointment_date) BETWEEN '$from_date' AND '$to_date'");
			$this->db->where('DATE(appointment_date) <', date('Y-m-d'));
		} 
		// Case 2: Only from date given
		elseif ($from_date) {
			$this->db->where('DATE(appointment_date)', $from_date);
			$this->db->where('DATE(appointment_date) <', date('Y-m-d'));
		} 
		// Case 3: No date range given — just get all before today
		else {
			$this->db->where('DATE(appointment_date) <', date('Y-m-d'));
		}

		if (!empty($staff_data) && in_array(strtolower($staff_data->role_name), ['doctor'])) {
			$this->db->where('enquiry_doctor_id', $staff_data->staffid);
		} elseif (!empty($enquiry_doctor_id)) {
			$this->db->where('enquiry_doctor_id', $enquiry_doctor_id);
		}

		if (!empty($selected_branch_id)) {
			if($branch_id != 0){
				$this->db->where_in('branch_id', $selected_branch_id);
			}
		}
		if (!empty($appointment_type_id)) {
			$this->db->where('appointment_type_id', $appointment_type_id);
		}

		$missed = $this->db->count_all_results();

		//$missed = 0;

		// Consulted
		$this->db->from(db_prefix() . 'appointment');
		$this->db->where('visit_status', 1);
		$this->db->where('consulted_date IS NOT NULL', null, false);

		if ($from_date && $to_date) {
			$this->db->where("DATE(appointment_date) BETWEEN '$from_date' AND '$to_date'");
		} elseif ($from_date) {
			$this->db->where('DATE(appointment_date)', $from_date);
		}

		
		
		if (!empty($staff_data) && in_array(strtolower($staff_data->role_name), ['doctor'])) {
			$this->db->where('enquiry_doctor_id', $staff_data->staffid);
		}else{
			if (!empty($enquiry_doctor_id)) {
				$this->db->where('enquiry_doctor_id', $enquiry_doctor_id);
			}
		}
		if (!empty($selected_branch_id)) {
			if($branch_id != 0){
				$this->db->where_in('branch_id', $selected_branch_id);
			}
		}
		if (!empty($appointment_type_id)) {
			$this->db->where('appointment_type_id', $appointment_type_id);
		}

		$consulted = $this->db->count_all_results();

		echo json_encode([
			'total' => $total_appointments,
			'missed' => $missed,
			'consulted' => $consulted
		]);
	}

	
	public function add_estimation()
	{
		if ($this->input->post()) {

			// Get form data (adjust field names as needed)
			$formData = $this->input->post();

			// Example: you might want to do some sanitization or validation here
			// e.g., $this->form_validation->set_rules(...);
			
			if (isset($formData['utr_no'])) {
				$utr_no = $formData['utr_no'];
				unset($formData['utr_no']);
			}
			// Call your model function to insert and return ID
			$insertId = $this->client_model->add_estimation($formData);
			if (isset($formData['paying_amount'])) {
				$paying_amount = $formData['paying_amount'];
				unset($formData['paying_amount']);
			}
			if (isset($formData['paymentmode'])) {
				$paymentmode = $formData['paymentmode'];
				unset($formData['paymentmode']);
			}
			if (isset($formData['clientid'])) {
				$clientid = $formData['clientid'];
				unset($formData['clientid']);
			}
			if($paying_amount>0){
				$payment_data = array(
					"invoiceid"=>$insertId,
					"amount"=>$paying_amount,
					"date"=>date('Y-m-d'),
					"paymentmode"=>$paymentmode,
					"utr_no"=>$utr_no,
				);
				
				$this->load->model('payments_model');
				$id = $this->payments_model->process_payment($payment_data, '');
				
			}
			if (isset($formData['invoice_acknowledge'])) {
				$invoice_acknowledge = $formData['invoice_acknowledge'];
				unset($formData['invoice_acknowledge']);
			}
			
			if($invoice_acknowledge == 'before_payment'){
				
				$branch_address = get_option('invoice_company_address');
				$communication_data = array(
					"assigned_doctor"   => $assigned_doctor ?? 'N/A',
					"appointment_date"  => $appointment_date ?? date('Y-m-d'),
					"appointment_time"  => $appointment_time ?? date('H:i'),
					"date_time"         => $date_time ?? date('Y-m-d H:i:s'),
					"branch_address"    => $branch_address ?? 'Branch address not available',
				);

				$this->client_model->patient_journey_log_event($clientid, 'appointment_created', 'Appointment Created', $communication_data);
			}
			
			if ($insertId) {
				echo json_encode([
					'success' => true,
					'id' => $insertId,
					'message' => 'Estimation saved successfully.'
				]);
			} else {
				echo json_encode([
					'success' => false,
					'message' => 'Failed to save estimation.'
				]);
			}
		} else {
			// Invalid request
			echo json_encode([
				'success' => false,
				'message' => 'No data received.'
			]);
		}
	}

	public function update_treatment_after_estimation()
	{
		$treatment_id = $this->input->post('treatment_id');
		$estimation_id = $this->input->post('estimation_id');
		$expirydate = $this->input->post('expirydate');

		$this->load->model('client_model');
		$updated = $this->client_model->update_treatment_post_estimation($treatment_id, $estimation_id, $expirydate);

		echo json_encode(['success' => $updated]);
	}

	public function get_casesheet_table_data($id)
	{
		if ($this->input->is_ajax_request()) {
			$data['client_id'] = $id;
			echo $this->app->get_table_data(module_views_path('client', 'tables/casesheet_table'), $data);
		}
	}

	public function get_doctor_prescription_table_data($id)
	{
		if ($this->input->is_ajax_request()) {
			$data['client_id'] = $id;
			echo $this->app->get_table_data(module_views_path('client', 'tables/doctor_prescription_table'), $data);
		}
	}

	public function get_invoice_payments_table_data($id)
	{
		if ($this->input->is_ajax_request()) {
			$data['client_id'] = $id;
			echo $this->app->get_table_data(module_views_path('client', 'tables/invoice_payments_table'), $data);
		}
	}

	public function get_payments_table_data($id)
	{
		if ($this->input->is_ajax_request()) {
			$data['client_id'] = $id;
			echo $this->app->get_table_data(module_views_path('client', 'tables/payments_table'), $data);
		}
	}

	public function get_visit_appointment_table_data($id)
	{
		if ($this->input->is_ajax_request()) {
			$data['client_id'] = $id;
			echo $this->app->get_table_data(module_views_path('client', 'tables/visit_appointment_table'), $data);
		}
	}

	public function get_call_logs_table_data($id)
	{
		if ($this->input->is_ajax_request()) {
			$data['client_id'] = $id;
			echo $this->app->get_table_data(module_views_path('client', 'tables/call_logs_table'), $data);
		}
	}

	public function get_estimates_table_data($id)
	{
		if ($this->input->is_ajax_request()) {
			$data['client_id'] = $id;
			echo $this->app->get_table_data(module_views_path('client', 'tables/estimates_table'), $data);
		}
	}

	public function get_feedback_table_data($id)
	{
		if ($this->input->is_ajax_request()) {
			$data['client_id'] = $id;
			echo $this->app->get_table_data(module_views_path('client', 'tables/feedback_table'), $data);
		}
	}
	
	
	public function trigger_communication_event($client_id, $module_name)
	{
		$communication_data = [];

		switch ($module_name) {

			case 'call_back':
				$communication_data = [
					'branch_address' => get_option('invoice_company_address'),
					'call_date' => date('Y-m-d'),
				];
				break;

			case 'call_feedback':
				$communication_data = [
					'branch_address' => get_option('invoice_company_address'),
					'feedback_status' => 'Positive',
				];
				break;

			case 'missed_appointment':
				// Get all patients who missed appointment yesterday
				$this->db->select('a.appointment_id, a.appointment_date, c.userid, c.company AS patient_name, c.phonenumber, cnf.email_id, cnf.whatsapp_number');
				$this->db->from(db_prefix() . 'appointment a');
				$this->db->join(db_prefix() . 'clients c', 'a.userid = c.userid', 'left');
				$this->db->join(db_prefix() . 'clients_new_fields cnf', 'c.userid = cnf.userid', 'left');
				$this->db->where('DATE(a.appointment_date)', date('Y-m-d', strtotime('-1 day')));
				$this->db->where('a.consulted_date IS NULL', null, false);
				$this->db->where('a.userid IS NOT NULL');

				$query = $this->db->get();
				$missed_patients = $query->result_array();

				foreach ($missed_patients as $patient) {
					$communication_data = [
						'assigned_doctor'   => get_staff_full_name($patient['assigned_doctor']),
						'appointment_date'   => $patient['appointment_date'],
						'patient_name'       => $patient['patient_name'],
					];
					// Optional: Send notification/log event
					$this->client_model->patient_journey_log_event(
						$patient['userid'],
						'missed_appointment',
						'Missed Appointment Notification',
						$communication_data
					); 
					
				}
				break;


			case 'reschedule_appointment':
				$communication_data = [
					'old_date' => '2025-06-15',
					'new_date' => '2025-06-18',
				];
				break;

			case 'not_registered_patient_day1':
				$communication_data = [
					'last_followup_date' => date('Y-m-d', strtotime('-1 day')),
				];
				break;

			case 'not_registered_patient_week1':
				$communication_data = [
					'followup_due_date' => date('Y-m-d', strtotime('+7 days')),
				];
				break;

			case 'medicine_appointment':
				$communication_data = [
					'appointment_date' => date('Y-m-d', strtotime('+2 days')),
					'medicine_name' => 'Paracetamol',
				];
				break;

			case 'renewal_reminder':
				$communication_data = [
					'renewal_due_date' => date('Y-m-d', strtotime('+3 days')),
				];
				break;

			case 'doctor_changed':
				$communication_data = [
					'old_doctor' => 'Dr. A',
					'new_doctor' => 'Dr. B',
					'appointment_date' => date('Y-m-d'),
				];
				break;

			case 'employee_absence':
			case 'uninformed_leave_employee':
				$communication_data = [
					'employee_name' => 'John',
					'date' => date('Y-m-d'),
					'reason' => 'Not Available',
				];
				break;

			case 'employee_late_hr':
				$communication_data = [
					'employee_name' => 'Alice',
					'late_time' => '09:30 AM',
					'date' => date('Y-m-d'),
				];
				break;

			case 'uninformed_leave_hr':
			case 'uninformed_leave_manager':
				$communication_data = [
					'employee_name' => 'Raj',
					'date' => date('Y-m-d'),
					'department' => 'Support',
					'manager_name' => 'Manager X',
				];
				break;

			case 'new_joiner_punch_in':
				$communication_data = [
					'employee_name' => 'New Staff',
					'join_date' => date('Y-m-d'),
					'punch_in_time' => '10:00 AM',
				];
				break;

			case 'monthly_attendance_confirmation':
				$communication_data = [
					'employee_name' => 'Mark',
					'month' => date('F'),
					'confirmation_deadline' => date('Y-m-d', strtotime('+3 days')),
				];
				break;

			case 'daily_eod':
			case 'eod_report':
				$communication_data = [
					'employee_name' => 'Eva',
					'date' => date('Y-m-d'),
					'summary_link' => admin_url('daily-eod-summary'),
					'report_link' => admin_url('eod-report'),
				];
				break;

			case 'dr_early_punch_in':
				$communication_data = [
					'doctor_name' => 'Dr. Sharma',
					'punch_in_time' => '07:45 AM',
					'date' => date('Y-m-d'),
				];
				break;

			case 'dr_normal_punch_in':
				$communication_data = [
					'doctor_name' => 'Dr. Rajeev',
					'punch_in_time' => '09:00 AM',
					'date' => date('Y-m-d'),
				];
				break;

			case 'leave_reconsideration':
				$communication_data = [
					'employee_name' => 'Sam',
					'leave_dates' => '2025-06-20 to 2025-06-22',
					'reason' => 'Too many appointments',
				];
				break;

			case 'package_created':
			case 'package_accepted':
				$communication_data = [
					'package_name' => 'Annual Health Plan',
					'package_cost' => '₹5000',
					'paid_amount' => '₹2000',
					'pending_amount' => '₹3000',
				];
				break;

			case 'payment_done':
			 $amount = $this->input->post('amount');
				$communication_data = [
					'paid_amount'   => $amount,
					'paid_date'   => date('d-m-Y'),
					'receipt_link'   => "https://positiveautism.com/",
				];
				// Optional: Send notification/log event
				$this->client_model->patient_journey_log_event(
					$client_id,
					'payment_done',
					'Payment received',
					$communication_data
				); 
				break;

			default:
				// Optional: Log or handle unknown module
				log_message('error', 'Unknown communication module: ' . $module_name);
				return;
		}

		/* // Common additional fields
		$communication_data['branch_address'] = $communication_data['branch_address'] ?? get_option('invoice_company_address');
		$communication_data['vertical_name'] = get_option('vertical_name');
		$communication_data['email'] = 'example@mail.com';
		$communication_data['phonenumber'] = '+91-9876543210';
		$communication_data['patient_name'] = 'Mr. Test';

		// Call your core logging/sending function
		$this->client_model->patient_journey_log_event($client_id, $module_name, ucwords(str_replace('_', ' ', $module_name)), $communication_data); */
	}

	public function get_prescription_items_data($client_id)
	{
		if (!is_staff_logged_in()) {
			access_denied();
		}

		$draw    = $this->input->post('draw');
		$start   = $this->input->post('start');
		$length  = $this->input->post('length');

		$this->db->from(db_prefix() . 'patient_prescription');
		$this->db->where('userid', $client_id);
		$prescriptions = $this->db->get()->result_array();

		$data = [];
		$serial = 1;

		foreach ($prescriptions as $row) {
			$prescription_id = $row['patient_prescription_id'];
			$items = explode('|', $row['prescription_data'] ?? '');
			$remarks = explode('|', $row['medicine_remarks'] ?? '');
			$remarks = array_pad($remarks, count($items), '');

			foreach ($items as $index => $item) {
				$cleaned = trim(preg_replace('/^\d+\.\s*/', '', $item));
				if ($cleaned === '') continue;

				$medicine = htmlspecialchars($cleaned);
				$remark   = htmlspecialchars($remarks[$index] ?? '');

				$data[] = [
					$serial++,
					'<strong>' . $medicine . '</strong><br><small>' .
					'By: ' . get_staff_full_name($row['created_by']) . '<br>' .
					'Date: ' . _d($row['created_datetime']) . '</small>',
					'<textarea class="form-control remark-input" data-id="' . $prescription_id . '" data-index="' . $index . '">' . $remark . '</textarea>',
					'<button class="btn btn-xs btn-success save-remark-btn" data-id="' . $prescription_id . '" data-index="' . $index . '">Save</button>'
				];
			}
		}

		echo json_encode([
			'draw' => intval($draw),
			'iTotalRecords' => $serial - 1,
			'iTotalDisplayRecords' => $serial - 1,
			'aaData' => $data,
		]);
	}

	public function get_medicine_rows($client_id)
	{
		if (!is_staff_logged_in()) {
			access_denied();
		}

		$this->db->where('userid', $client_id);
		$prescriptions = $this->db->get(db_prefix() . 'patient_prescription')->result_array();

		$row_index = 1;
		foreach ($prescriptions as $prescription) {
			$items = explode('|', $prescription['prescription_data'] ?? '');
			$remarks = explode('|', $prescription['medicine_remarks'] ?? '');
			$remarks = array_pad($remarks, count($items), '');

			foreach ($items as $index => $medicine) {
				$cleaned = trim(preg_replace('/^\d+\.\s*/', '', $medicine));
				if ($cleaned === '') continue;

				echo '<tr>';
				echo '<td>' . ($row_index++) . '</td>';
				echo '<td>' . htmlspecialchars($cleaned) . '</td>';
				echo '<td>
						<textarea class="form-control mb-1 remark-input" 
							data-id="' . $prescription['patient_prescription_id'] . '" 
							data-index="' . $index . '">' . htmlspecialchars($remarks[$index]) . '</textarea>
					  </td>';
				echo '<td>
						<button 
							class="btn btn-xs btn-success save-remark-btn" 
							data-id="' . $prescription['patient_prescription_id'] . '" 
							data-index="' . $index . '">Save</button>
					  </td>';
				echo '</tr>';
			}
		}
	}


	public function message_log_table($userid)
	{
		if (!staff_can('view_call_log', 'customers')) {
			ajax_access_denied();
		}
	$data['userid'] = $userid;
		
		echo $this->app->get_table_data(module_views_path('client', 'tables/message_log_table'), $data);
	}


	public function patient_reminders_table($userid)
	{
		if (!staff_can('view_patient_reminders', 'customers')) {
			ajax_access_denied();
		}
	$data['userid'] = $userid;
		
		echo $this->app->get_table_data(module_views_path('client', 'tables/patient_reminders_table'), $data);
	}

	public function update_prescription_remarks()
	{
		$id             = $this->input->post('id');
		$remarks        = $this->input->post('remarks');
		$casesheet_id   = $this->input->post('casesheet_id');
		$medicine_days  = $this->input->post('medicine_days');
		$followup_date  = $this->input->post('followup_date');
		$notify_doctor  = $this->input->post('notify_doctor');

		if (!$id || $remarks === null) {
			echo json_encode(['success' => false, 'message' => 'Invalid input']);
			return;
		}

		// Update prescription table
		$this->db->where('patient_prescription_id', $id);
		$this->db->update(db_prefix() . 'patient_prescription', [
			'medicine_remarks'    => $remarks,
			'medicine_given_by'   => get_staff_user_id(),
			'medicine_given_date' => date('Y-m-d H:i:s'),
		]);

		// Update casesheet
		if ($casesheet_id) {
			$this->db->where('id', $casesheet_id);
			$this->db->update(db_prefix() . 'casesheet', [
				'medicine_days' => $medicine_days,
				'followup_date' => $followup_date,
			]);
		}

		// Send notification to doctor via Pusher
		if ($notify_doctor) {
			

			$prescription = $this->db->get_where(db_prefix() . 'patient_prescription', ['patient_prescription_id' => $id])->row();
			if ($prescription && $prescription->created_by) {
				
				$this->load->library('app_pusher');

				$staff_id = get_staff_user_id();
				$doctor_id = $prescription->created_by;
				$doctor_name = get_staff_full_name($doctor_id);

				$casesheet_link = admin_url('client/edit_casesheet/' . $prescription->casesheet_id . '/' . $prescription->userid);

				$message = 'Pharmacy marked medicine changes for Prescription #' . $id . 
						   '<br><a href="' . $casesheet_link . '" target="_blank" class="btn btn-sm btn-s mt-2">View Casesheet</a>';
				if (file_exists(FCPATH . 'modules/prchat/models/Prchat_model.php')) {
					$this->load->model('prchat/Prchat_model');
					
					$message_data = [
						'sender_id'   => $staff_id,
						'reciever_id' => $doctor_id,
						'message'     => $message,
						'viewed'      => 0,
						'time_sent'   => date("Y-m-d H:i:s"),
					];

					$last_id = $this->Prchat_model->createMessage($message_data, db_prefix() . 'chatmessages');
					
				}


				$this->app_pusher->trigger('presence-mychanel', 'send-event', [
					'message'     => $message,
					'from'        => $staff_id,
					'to'          => $doctor_id,
					'from_name'   => get_staff_full_name($staff_id),
				]);

				$this->app_pusher->trigger('presence-mychanel', 'notify-event', [
					'message'     => $message,
					'from'        => $staff_id,
					'to'          => $doctor_id,
					'from_name'   => get_staff_full_name($staff_id),
				]);

			}
			
		}
		echo json_encode(['success' => true]);
	}


	public function get_cities_by_state($state_id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$this->db->select('city_id, city_name');
		$this->db->from(db_prefix() . 'city');
		$this->db->where('state_id', $state_id);
		$cities = $this->db->get()->result_array();

		echo json_encode($cities);
	}

	public function get_pincodes_by_city($city_id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$this->db->select('pincode_id, pincode_name');
		$this->db->from(db_prefix() . 'pincode');
		$this->db->where('city_id', $city_id);
		$this->db->where('pincode_status', 1);
		$pincodes = $this->db->get()->result_array();

		echo json_encode($pincodes);
	}


	public function ajax_start_token()
	{
		if ($this->input->is_ajax_request()) {
			$patient_id = $this->input->post('patient_id');
			$doctor_id  = $this->input->post('doctor_id');
			$success = $this->client_model->start_token($patient_id, $doctor_id);
			
			echo json_encode(['success' => $success]);
			return;
		}
		show_404();
	}
	
	public function save_and_call_next_patient($current_patient_id, $doctor_id)
	{
		$today = date('Y-m-d');

		// ✅ Update tokens where status is 'Serving' → 'Completed'
		$this->db->where('patient_id', $current_patient_id);

		if (!is_admin()) {
			$this->db->where('doctor_id', $doctor_id);
		}

		$this->db->where('date', $today);
		$this->db->where('token_status', 'Serving'); // only update those in 'Serving' status
		$this->db->update(db_prefix() . 'tokens', ['token_status' => 'Completed']);


		// 2. ✅ Get the next Pending token for same doctor
		$this->db->select('token_id, patient_id');
		$this->db->from(db_prefix() . 'tokens');
		if(!is_admin()){
			$this->db->where('doctor_id', $doctor_id);
		}
		$this->db->where('date', $today);
		$this->db->where_in('token_status', ['Pending', 'Recall']);
		$this->db->order_by('token_id', 'ASC');
		$this->db->limit(1);
		$next = $this->db->get()->row();
		
		if ($next) {
			// 3. ✅ Update next patient to "Serving"
			$this->db->where('token_id', $next->token_id);
			$this->db->update(db_prefix() . 'tokens', ['token_status' => 'Serving']);

			// 4. ✅ Redirect to next patient's case sheet
			redirect(admin_url('client/get_patient_list/' . $next->patient_id . '/tab_casesheet'));
		} else {
			// 5. ❌ No more patients to call
			set_alert('warning', 'No more patients in the queue.');
			redirect(admin_url('client/get_patient_list'));
		}
	}

public function update_counter_status()
{
    $doctor_id = $this->input->post('doctor_id');
    $status    = $this->input->post('status');

    if (!$doctor_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Missing data']);
        return;
    }

    $this->db->where('doctor_id', $doctor_id);
    $this->db->update(db_prefix() . 'counter', ['counter_status' => $status]);

    if ($this->db->affected_rows() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No counter found or already set']);
    }
}


public function add_pincode()
{
    $name = trim($this->input->post('pincode_name'));

    if ($name === '') {
        echo json_encode(['success' => false, 'message' => 'Invalid pincode']);
        return;
    }

    $this->db->insert(db_prefix() . 'pincode_master', ['pincode_name' => $name]);
    $insert_id = $this->db->insert_id();

    echo json_encode([
        'success' => true,
        'insert_id' => $insert_id,
        'pincode_name' => $name
    ]);
}

	public function pharmacy($id = NULL, $consulted_date = NULL, $consulted_to_date = NULL, $branch_id = NULL)
	{
		// Optional access check
		if (staff_cant('view_prescription', 'customers')) {
			access_denied('pharmacy');
		} 

		// 🧼 Normalize incoming values
		$enquiry_doctor_id = $this->input->get('enquiry_doctor_id');
		if ($enquiry_doctor_id === null) {
			$enquiry_doctor_id = $this->input->post('enquiry_doctor_id');
		}
		$visit_status = $this->input->get('visit_status');
		if ($visit_status === null) {
			$visit_status = $this->input->post('visit_status');
		}
		$enquiry_doctor_id = ($enquiry_doctor_id === '0' || empty($enquiry_doctor_id)) ? null : $enquiry_doctor_id;
		$branch_id = ($branch_id === '0' || empty($branch_id)) ? null : $branch_id;
		$visit_status = ($visit_status === 'All' || empty($visit_status)) ? null : str_replace('_', ' ', $visit_status);

		// 🔍 Logged-in staff info
		$staff_id = get_staff_user_id();
		$staff_data = $this->db
			->select('s.staffid, r.name as role_name')
			->from(db_prefix() . 'staff s')
			->join(db_prefix() . 'roles r', 'r.roleid = s.role', 'left')
			->where('s.staffid', $staff_id)
			->get()
			->row();

		// 📦 Prepare data array
		$data = [
			'title'               => _l('Pharmacy'),
			'consulted_from_date'=> $consulted_date,
			'consulted_to_date'  => $consulted_to_date,
			'enquiry_doctor_id'  => $enquiry_doctor_id,
			'branch_id'          => $branch_id,
			'visit_status'       => $visit_status,
			'staff_data'         => $staff_data,
		];
		
		// 🚀 Return AJAX table view
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('client', 'tables/pharmacy_table'), $data);
			return;
		}
		
		function get_counter_by_doctor_id($doctor_id)
		{
			$CI =& get_instance();
			$CI->db->where('doctor_id', $doctor_id);
			return $CI->db->get(db_prefix() . 'counter')->row(); // returns single row (object)
		}
		$data['branch'] = $this->client_model->get_branch();
		$data['current_branch_id'] = $this->current_branch_id;
		if ($id && $id != 'NULL') {
			 $this->load->model('currencies_model');
			 $this->load->model('taxes_model');
			 $this->load->model('invoice_items_model');
			 $this->load->model('estimates_model');
			$data['clientid'] = $id;
            // Fetch the existing patient data
            $client = $this->client_model->get($id);
            $estimates = $this->client_model->get_estimates($id);
			foreach ($estimates as &$estimate) {
				$this->db->select('description');
				$this->db->where('rel_type', 'estimate');
				$this->db->where('rel_id', $estimate['id']);
				$items = $this->db->get('tblitemable')->row();
				
				$estimate['description'] = $items->description; // append to estimate
			}
			
            $customer_new_fields = $this->client_model->get_customer_new_fields($id);
			$currencies= $this->currencies_model->get();
			$taxes = $this->taxes_model->get();
			$items_groups = $this->invoice_items_model->get_groups();
			$staff            = $this->staff_model->get('', ['active' => 1]);
			$estimate_statuses = $this->estimates_model->get_statuses();
			$base_currency = $this->currencies_model->get_base_currency();
			
			$items = $this->invoice_items_model->get_grouped();
            $appointment_data = $this->client_model->get_appointment_data($id);
            $patient_activity_log = $this->client_model->get_patient_activity_log($id);
            $patient_prescription = $this->client_model->get_patient_prescription($id);
			
            $patient_treatment = $this->client_model->get_patient_treatment($id);
            $casesheet = $this->client_model->get_casesheet($id);
            // Fetch patient call logs
            $patient_call_logs = $this->client_model->get_patient_call_logs($id); // NEW
            $invoices = $this->client_model->get_invoices($id); // NEW
            $invoice_payments = $this->client_model->get_invoice_payments($id); // NEW
            $shared_requests = $this->client_model->get_shared_requests($id); // NEW

            // Fetch medicine data (names, potencies, doses, timings)
            $medicines = $this->master_model->get_all('medicine');
            $potencies = $this->master_model->get_all('medicine_potency');
            $doses = $this->master_model->get_all('medicine_dose');
            $timings = $this->master_model->get_all('medicine_timing');
            $appointment_type = $this->master_model->get_all('appointment_type');
            $criteria = $this->master_model->get_all('criteria');
            $treatments = $this->master_model->get_all('treatment');
            $patient_status = $this->master_model->get_all('patient_status');
            $master_settings = $this->master_model->get_all('master_settings');
			$testimonials = $this->client_model->get_testimonial();
			
			function get_estimation_payment_summary($estimation_id)
			{
				$CI =& get_instance();

				// 1. Get the estimate row
				$CI->db->select('total, invoiceid, currency, date, expirydate');
				$CI->db->where('id', $estimation_id);
				$estimate = $CI->db->get(db_prefix() . 'estimates')->row();

				if (!$estimate || !$estimate->invoiceid) {
					return [
						'total' => 0,
						'paid' => 0,
						'dues' => 0,
						'currency' => '',
						'invoice_id' => null,
					];
				}

				// 2. Sum payments from invoicepaymentrecords
				$CI->db->select_sum('amount');
				$CI->db->where('invoiceid', $estimate->invoiceid);
				$paid_row = $CI->db->get(db_prefix() . 'invoicepaymentrecords')->row();
				$paid = $paid_row ? (float)$paid_row->amount : 0;

				return [
					'total'     => (float)$estimate->total,
					'paid'      => $paid,
					'dues'      => (float)$estimate->total - $paid,
					'currency'  => $estimate->currency,
					'invoice_id'=> $estimate->invoiceid,
					'date'=> $estimate->date,
					'expirydate'=> $estimate->expirydate,
				];
			}
			$callback_url = "pharmacy";
			$branch = $this->client_model->get_branch();
            // Pass the data to the view
            $data['client_modal'] = $this->load->view('client_model_popup', [
                'estimates' => $estimates,
                'client' => $client,
                'branch' => $branch,
				'callback_url' => $callback_url,
                'casesheet' => $casesheet,
                'testimonials' => $testimonials,
                'shared_requests' => $shared_requests,
                'master_settings' => $master_settings,
                'customer_new_fields' => $customer_new_fields,
                'currencies' => $currencies,
                'taxes' => $taxes,
                'items' => $items,
                'base_currency' => $base_currency,
                'items_groups' => $items_groups,
                'staff' => $staff,
                'estimate_statuses' => $estimate_statuses,
                'appointment_data' => $appointment_data,
                'patient_activity_log' => $patient_activity_log,
                'patient_call_logs' => $patient_call_logs, // NEW
                'patient_prescriptions' => $patient_prescription, // NEW
                'patient_treatment' => $patient_treatment, // NEW
                'medicines' => $medicines, // NEW
                'potencies' => $potencies, // NEW
                'appointment_type' => $appointment_type, // NEW
                'criteria' => $criteria, // NEW
                'doses' => $doses, // NEW
                'treatments' => $treatments, // NEW
                'patient_status' => $patient_status, // NEW
                'invoices' => $invoices, // NEW
                'invoice_payments' => $invoice_payments, // NEW
                'timings' => $timings // NEW
            ], true);
        }
       $this->load->view('pharmacy', $data);
	  
    }
	
	
	public function branch_summary_details()
	{
		if (staff_cant('branch_summary_details', 'reports')) {
			//access_denied('branch_summary_details');
		}

		$type       = $this->input->get('type');
		$branch_id  = $this->input->get('branch_id');
		$from       = $this->input->get('from');
		$to         = $this->input->get('to');

		$data['title']     = _l("branch_summary_details");
		$data['type']      = $type;
		$data['branch_id'] = $branch_id;
		$data['from']      = $from;
		$data['to']        = $to;

		$this->load->model('client_model'); // If not autoloaded
		$data['summary_data'] = $this->client_model->get_branch_summary_details($type, $branch_id, $from, $to);

		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('client', 'tables/branch_summary_details_table'), $data);
		}

		$this->load->view('reports/branch_summary_details', $data);
	}


}
