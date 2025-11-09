<?php

use app\services\utilities\Arr;

defined('BASEPATH') or exit('No direct script access allowed');

class Client_model extends App_Model
{
    private $contact_columns;
    private $current_branch_id;

    public function __construct()
    {
        parent::__construct();

        $this->contact_columns = hooks()->apply_filters('contact_columns', ['firstname', 'lastname', 'email', 'phonenumber', 'title', 'password', 'send_set_password_email', 'donotsendwelcomeemail', 'permissions', 'direction', 'invoice_emails', 'estimate_emails', 'credit_note_emails', 'contract_emails', 'task_emails', 'project_emails', 'ticket_emails', 'is_primary']);

        $this->load->model(['client_vault_entries_model', 'client_groups_model', 'statement_model']);
		
		$this->current_branch_id = $this->get_logged_in_staff_branch_id();
    }

    /**
     * Get client object based on passed clientid if not passed clientid return array of all clients
     * @param  mixed $id    client id
     * @param  array  $where
     * @return mixed
     */

    public function get_master_data()
    {
        $tables = [
            'appointment_type' => 'tblappointment_type',
            'enquiry_type' => 'tblenquiry_type',
            'patient_response' => 'tblpatient_response',
            'patient_priority' => 'tblpatient_priority',
            'branch' => 'tblcustomers_groups',
            'assign_doctor' => 'tblstaff',
            //'slots' => 'tblslots',
            'languages' => 'tbllanguages',
            //'treatment' => 'tbltreatment',
            //'consultation_fee' => 'tblconsultation_fee',
            'patient_source' => 'tblleads_sources',
        ];

        $result = [];
        foreach ($tables as $key => $table) {
            if($table == "tblstaff"){
                $this->db->select('roleid');
				$this->db->from(db_prefix() . 'roles');
				$this->db->where('LOWER(name)', 'doctor');
				$query = $this->db->get();
				if ($query->num_rows() > 0) {
					$role = $query->row()->roleid;
					$this->db->where('role', $role);
				} else {
					// Optional: Handle case when 'Doctor' role is not found
					$this->db->where('role', 0); // or some fallback
				}
            }
            $result[$key] = $this->db->table_exists($table) ? $this->db->get($table)->result_array() : [];
        }
        return $result;
    }
    public function get($id = '', $where = [])
{
    $this->db->select('
        c.*, 
        co.*, 
        ct.*, 
        new.*, 
        cg.groupid,
        cgs.name as branch_name,
        ls.name as status_name,
        ls.color as status_color,
        latest_journey.status as status_id,
        city.city_name as city_name,
        state.state_name as state_name,
        pincode.pincode_name as pincode_name,
        lead_src.name as source_name,
        lead.source as lead_source_id
    '); // Select everything plus latest status and lead source details

    $this->db->from(db_prefix() . 'clients c');
    $this->db->join(db_prefix() . 'countries co', 'co.country_id = c.country', 'left');
    $this->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
    $this->db->join(db_prefix() . 'contacts ct', 'ct.userid = c.userid AND ct.is_primary = 1', 'left');
    $this->db->join(db_prefix() . 'customer_groups cg', 'cg.customer_id = c.userid', 'left');
    $this->db->join(db_prefix() . 'customers_groups cgs', 'cgs.id = cg.groupid', 'left');
    $this->db->join(db_prefix() . 'city city', 'city.city_id = c.city', 'left');
    $this->db->join(db_prefix() . 'pincode pincode', 'pincode.pincode_id = new.pincode', 'left');
    $this->db->join(db_prefix() . 'state state', 'state.state_id = c.state', 'left');
    $this->db->join(db_prefix() . 'leads lead', 'lead.id = c.leadid', 'left');
    $this->db->join(db_prefix() . 'leads_sources lead_src', 'lead_src.id = lead.source', 'left');

    // 👇 Join latest journey per client
    $this->db->join(
        '(SELECT l1.* FROM ' . db_prefix() . 'lead_patient_journey l1
          INNER JOIN (
              SELECT userid, MAX(id) as max_id
              FROM ' . db_prefix() . 'lead_patient_journey
              GROUP BY userid
          ) l2 ON l1.userid = l2.userid AND l1.id = l2.max_id
        ) as latest_journey',
        'latest_journey.userid = c.userid',
        'left'
    );

    // 👇 Join status table
    $this->db->join(db_prefix() . 'leads_status ls', 'ls.id = latest_journey.status', 'left');

    if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
        $this->db->where($where);
    }

    if (is_numeric($id)) {
        $this->db->select('c.*');
        $this->db->where('c.userid', $id);
        $client = $this->db->get()->row();

        if ($client && get_option('company_requires_vat_number_field') == 0) {
            $client->vat = null;
        }

        $GLOBALS['client'] = $client;

        return $client;
    }

    $this->db->order_by('c.company', 'asc');
    return $this->db->get()->result_array();
}



    /**
     * Get customers contacts
     * @param  mixed $customer_id
     * @param  array $where       perform where query
     * @param  array $whereIn     perform whereIn query
     * @return array
     */
    public function get_contacts($customer_id = '', $where = ['active' => 1], $whereIn = [])
    {
        $this->db->where($where);
        if ($customer_id != '') {
            $this->db->where('userid', $customer_id);
        }

        foreach ($whereIn as $key => $values) {
            if (is_string($key) && is_array($values)) {
                $this->db->where_in($key, $values);
            }
        }

        $this->db->order_by('is_primary', 'DESC');

        return $this->db->get(db_prefix() . 'contacts')->result_array();
    }

    /**
     * Get single contacts
     * @param  mixed $id contact id
     * @return object
     */
    public function get_contact($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'contacts')->row();
    }

    /**
     * Get contact by given email
     *
     * @since 2.8.0
     *
     * @param  string $email
     *
     * @return \strClass|null
     */
    public function get_contact_by_email($email)
    {
        $this->db->where('email', $email);
        $this->db->limit(1);

        return $this->db->get('contacts')->row();
    }

    /**
     * @param array $_POST data
     * @param withContact
     *
     * @return integer Insert ID
     *
     * Add new client to database
     */
    public function add($data, $withContact = false)
    {
        $contact_data = [];
        // From Lead Convert to client
        if (isset($data['send_set_password_email'])) {
            $contact_data['send_set_password_email'] = true;
        }

        if (isset($data['donotsendwelcomeemail'])) {
            $contact_data['donotsendwelcomeemail'] = true;
        }

        $data = $this->check_zero_columns($data);

        $data = hooks()->apply_filters('before_client_added', $data);

        foreach ($this->contact_columns as $field) {
            if (!isset($data[$field])) {
                continue;
            }

            $contact_data[$field] = $data[$field];

            // Phonenumber is also used for the company profile
            if ($field != 'phonenumber') {
                unset($data[$field]);
            }
        }

        $groups_in     = Arr::pull($data, 'groups_in') ?? [];
        $custom_fields = Arr::pull($data, 'custom_fields') ?? [];

        // From customer profile register
        if (isset($data['contact_phonenumber'])) {
            $contact_data['phonenumber'] = $data['contact_phonenumber'];
            unset($data['contact_phonenumber']);
        }

        $this->db->insert(db_prefix() . 'clients', array_merge($data, [
            'datecreated' => date('Y-m-d H:i:s'),
            'addedfrom'   => is_staff_logged_in() ? get_staff_user_id() : 0,
        ]));

        $client_id = $this->db->insert_id();

        if ($client_id) {
            if (count($custom_fields) > 0) {
                $_custom_fields = $custom_fields;
                // Possible request from the register area with 2 types of custom fields for contact and for comapny/customer
                if (count($custom_fields) == 2) {
                    unset($custom_fields);
                    $custom_fields['customers']                = $_custom_fields['customers'];
                    $contact_data['custom_fields']['contacts'] = $_custom_fields['contacts'];
                } elseif (count($custom_fields) == 1) {
                    if (isset($_custom_fields['contacts'])) {
                        $contact_data['custom_fields']['contacts'] = $_custom_fields['contacts'];
                        unset($custom_fields);
                    }
                }

                handle_custom_fields_post($client_id, $custom_fields);
            }

            /**
             * Used in Import, Lead Convert, Register
             */
            if ($withContact == true) {
                $contact_id = $this->add_contact($contact_data, $client_id, $withContact);
            }

            foreach ($groups_in as $group) {
                $this->db->insert('customer_groups', [
                        'customer_id' => $client_id,
                        'groupid'     => $group,
                    ]);
            }

            $log = 'ID: ' . $client_id;

            if ($log == '' && isset($contact_id)) {
                $log = get_contact_full_name($contact_id);
            }

            $isStaff = null;

            if (!is_client_logged_in() && is_staff_logged_in()) {
                $log .= ', From Staff: ' . get_staff_user_id();
                $isStaff = get_staff_user_id();
            }

            do_action_deprecated('after_client_added', [$client_id], '2.9.4', 'after_client_created');

            hooks()->do_action('after_client_created', [
                'id'            => $client_id,
                'data'          => $data,
                'contact_data'  => $contact_data,
                'custom_fields' => $custom_fields,
                'groups_in'     => $groups_in,
                'with_contact'  => $withContact,
            ]);

            log_activity('New Client Created [' . $log . ']', $isStaff);
        }

        return $client_id;
    }

    /**
     * @param  array $_POST data
     * @param  integer ID
     * @return boolean
     * Update client informations
     */
    public function update($data, $id, $client_request = false)
    {
        $updated = false;
        $data    = $this->check_zero_columns($data);

        $data = hooks()->apply_filters('before_client_updated', $data, $id);

        $update_all_other_transactions = (bool) Arr::pull($data, 'update_all_other_transactions');
        $update_credit_notes           = (bool) Arr::pull($data, 'update_credit_notes');
        $custom_fields                 = Arr::pull($data, 'custom_fields') ?? [];
        $groups_in                     = Arr::pull($data, 'groups_in') ?? false;

        if (handle_custom_fields_post($id, $custom_fields)) {
            $updated = true;
        }

        $this->db->where('userid', $id);
        $this->db->update(db_prefix() . 'clients', $data);

        if ($this->db->affected_rows() > 0) {
            $updated = true;
        }

        if ($update_all_other_transactions || $update_credit_notes) {
            $transactions_update = [
                'billing_street'   => $data['billing_street'],
                'billing_city'     => $data['billing_city'],
                'billing_state'    => $data['billing_state'],
                'billing_zip'      => $data['billing_zip'],
                'billing_country'  => $data['billing_country'],
                'shipping_street'  => $data['shipping_street'],
                'shipping_city'    => $data['shipping_city'],
                'shipping_state'   => $data['shipping_state'],
                'shipping_zip'     => $data['shipping_zip'],
                'shipping_country' => $data['shipping_country'],
            ];

            if ($update_all_other_transactions) {
                // Update all invoices except paid ones.
                $this->db->where('clientid', $id)
                ->where('status !=', 2)
                ->update('invoices', $transactions_update);

                if ($this->db->affected_rows() > 0) {
                    $updated = true;
                }

                // Update all estimates
                $this->db->where('clientid', $id)
                    ->update('estimates', $transactions_update);
                if ($this->db->affected_rows() > 0) {
                    $updated = true;
                }
            }

            if ($update_credit_notes) {
                $this->db->where('clientid', $id)
                    ->where('status !=', 2)
                    ->update('creditnotes', $transactions_update);

                if ($this->db->affected_rows() > 0) {
                    $updated = true;
                }
            }
        }

        if ($this->client_groups_model->sync_customer_groups($id, $groups_in)) {
            $updated = true;
        }

        do_action_deprecated('after_client_updated', [$id], '2.9.4', 'client_updated');

        hooks()->do_action('client_updated', [
            'id'                            => $id,
            'data'                          => $data,
            'update_all_other_transactions' => $update_all_other_transactions,
            'update_credit_notes'           => $update_credit_notes,
            'custom_fields'                 => $custom_fields,
            'groups_in'                     => $groups_in,
            'updated'                       => &$updated,
        ]);

        if ($updated) {
            log_activity('Customer Info Updated [ID: ' . $id . ']');
        }

        return $updated;
    }

    /**
     * Update contact data
     * @param  array  $data           $_POST data
     * @param  mixed  $id             contact id
     * @param  boolean $client_request is request from customers area
     * @return mixed
     */
    public function update_contact($data, $id, $client_request = false)
    {
        $affectedRows = 0;
        $contact      = $this->get_contact($id);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password']             = app_hash_password($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }

        $send_set_password_email = isset($data['send_set_password_email']) ? true : false;
        $set_password_email_sent = false;

        $permissions        = isset($data['permissions']) ? $data['permissions'] : [];
        $data['is_primary'] = isset($data['is_primary']) ? 1 : 0;

        // Contact cant change if is primary or not
        if ($client_request == true) {
            unset($data['is_primary']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if ($client_request == false) {
            $data['invoice_emails']     = isset($data['invoice_emails']) ? 1 :0;
            $data['estimate_emails']    = isset($data['estimate_emails']) ? 1 :0;
            $data['credit_note_emails'] = isset($data['credit_note_emails']) ? 1 :0;
            $data['contract_emails']    = isset($data['contract_emails']) ? 1 :0;
            $data['task_emails']        = isset($data['task_emails']) ? 1 :0;
            $data['project_emails']     = isset($data['project_emails']) ? 1 :0;
            $data['ticket_emails']      = isset($data['ticket_emails']) ? 1 :0;
        }

        $data = hooks()->apply_filters('before_update_contact', $data, $id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contacts', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if (isset($data['is_primary']) && $data['is_primary'] == 1) {
                $this->db->where('userid', $contact->userid);
                $this->db->where('id !=', $id);
                $this->db->update(db_prefix() . 'contacts', [
                    'is_primary' => 0,
                ]);
            }
        }

        if ($client_request == false) {
            $customer_permissions = $this->roles_model->get_contact_permissions($id);
            if (sizeof($customer_permissions) > 0) {
                foreach ($customer_permissions as $customer_permission) {
                    if (!in_array($customer_permission['permission_id'], $permissions)) {
                        $this->db->where('userid', $id);
                        $this->db->where('permission_id', $customer_permission['permission_id']);
                        $this->db->delete(db_prefix() . 'contact_permissions');
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
                foreach ($permissions as $permission) {
                    $this->db->where('userid', $id);
                    $this->db->where('permission_id', $permission);
                    $_exists = $this->db->get(db_prefix() . 'contact_permissions')->row();
                    if (!$_exists) {
                        $this->db->insert(db_prefix() . 'contact_permissions', [
                            'userid'        => $id,
                            'permission_id' => $permission,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            } else {
                foreach ($permissions as $permission) {
                    $this->db->insert(db_prefix() . 'contact_permissions', [
                        'userid'        => $id,
                        'permission_id' => $permission,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if ($send_set_password_email) {
                $set_password_email_sent = $this->authentication_model->set_password_email($data['email'], 0);
            }
        }

        if (($client_request == true) && $send_set_password_email) {
            $set_password_email_sent = $this->authentication_model->set_password_email($data['email'], 0);
        }

        if ($affectedRows > 0) {
            hooks()->do_action('contact_updated', $id, $data);
        }

        if ($affectedRows > 0 && !$set_password_email_sent) {
            log_activity('Contact Updated [ID: ' . $id . ']');

            return true;
        } elseif ($affectedRows > 0 && $set_password_email_sent) {
            return [
                'set_password_email_sent_and_profile_updated' => true,
            ];
        } elseif ($affectedRows == 0 && $set_password_email_sent) {
            return [
                'set_password_email_sent' => true,
            ];
        }

        return false;
    }

    /**
     * Add new contact
     * @param array  $data               $_POST data
     * @param mixed  $customer_id        customer id
     * @param boolean $not_manual_request is manual from admin area customer profile or register, convert to lead
     */
    public function add_contact($data, $customer_id, $not_manual_request = false)
    {
        $send_set_password_email = isset($data['send_set_password_email']) ? true : false;

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
            unset($data['permissions']);
        }

        $data['email_verified_at'] = date('Y-m-d H:i:s');

        $send_welcome_email = true;

        if (isset($data['donotsendwelcomeemail'])) {
            $send_welcome_email = false;
        }

        if (defined('CONTACT_REGISTERING')) {
            $send_welcome_email = true;

            // Do not send welcome email if confirmation for registration is enabled
            if (get_option('customers_register_require_confirmation') == '1') {
                $send_welcome_email = false;
            }

            // If client register set this contact as primary
            $data['is_primary'] = 1;

            if (is_email_verification_enabled() && !empty($data['email'])) {
                // Verification is required on register
                $data['email_verified_at']      = null;
                $data['email_verification_key'] = app_generate_hash();
            }
        }

        if (isset($data['is_primary'])) {
            $data['is_primary'] = 1;
            $this->db->where('userid', $customer_id);
            $this->db->update(db_prefix() . 'contacts', [
                'is_primary' => 0,
            ]);
        } else {
            $data['is_primary'] = 0;
        }

        $password_before_hash = '';
        $data['userid']       = $customer_id;
        if (isset($data['password'])) {
            $password_before_hash = $data['password'];
            $data['password']     = app_hash_password($data['password']);
        }

        $data['datecreated'] = date('Y-m-d H:i:s');

        if (!$not_manual_request) {
            $data['invoice_emails']     = isset($data['invoice_emails']) ? 1 :0;
            $data['estimate_emails']    = isset($data['estimate_emails']) ? 1 :0;
            $data['credit_note_emails'] = isset($data['credit_note_emails']) ? 1 :0;
            $data['contract_emails']    = isset($data['contract_emails']) ? 1 :0;
            $data['task_emails']        = isset($data['task_emails']) ? 1 :0;
            $data['project_emails']     = isset($data['project_emails']) ? 1 :0;
            $data['ticket_emails']      = isset($data['ticket_emails']) ? 1 :0;
        }

        $data['email'] = trim($data['email']);

        $data = hooks()->apply_filters('before_create_contact', $data);

        $this->db->insert(db_prefix() . 'contacts', $data);
        $contact_id = $this->db->insert_id();

        if ($contact_id) {
            if (isset($custom_fields)) {
                handle_custom_fields_post($contact_id, $custom_fields);
            }
            // request from admin area
            if (!isset($permissions) && $not_manual_request == false) {
                $permissions = [];
            } elseif ($not_manual_request == true) {
                $permissions         = [];
                $_permissions        = get_contact_permissions();
                $default_permissions = @unserialize(get_option('default_contact_permissions'));
                if (is_array($default_permissions)) {
                    foreach ($_permissions as $permission) {
                        if (in_array($permission['id'], $default_permissions)) {
                            array_push($permissions, $permission['id']);
                        }
                    }
                }
            }

            if ($not_manual_request == true) {
                // update all email notifications to 0
                $this->db->where('id', $contact_id);
                $this->db->update(db_prefix() . 'contacts', [
                    'invoice_emails'     => 0,
                    'estimate_emails'    => 0,
                    'credit_note_emails' => 0,
                    'contract_emails'    => 0,
                    'task_emails'        => 0,
                    'project_emails'     => 0,
                    'ticket_emails'      => 0,
                ]);
            }
            foreach ($permissions as $permission) {
                $this->db->insert(db_prefix() . 'contact_permissions', [
                    'userid'        => $contact_id,
                    'permission_id' => $permission,
                ]);

                // Auto set email notifications based on permissions
                if ($not_manual_request == true) {
                    if ($permission == 6) {
                        $this->db->where('id', $contact_id);
                        $this->db->update(db_prefix() . 'contacts', ['project_emails' => 1, 'task_emails' => 1]);
                    } elseif ($permission == 3) {
                        $this->db->where('id', $contact_id);
                        $this->db->update(db_prefix() . 'contacts', ['contract_emails' => 1]);
                    } elseif ($permission == 2) {
                        $this->db->where('id', $contact_id);
                        $this->db->update(db_prefix() . 'contacts', ['estimate_emails' => 1]);
                    } elseif ($permission == 1) {
                        $this->db->where('id', $contact_id);
                        $this->db->update(db_prefix() . 'contacts', ['invoice_emails' => 1, 'credit_note_emails' => 1]);
                    } elseif ($permission == 5) {
                        $this->db->where('id', $contact_id);
                        $this->db->update(db_prefix() . 'contacts', ['ticket_emails' => 1]);
                    }
                }
            }

            if ($send_welcome_email == true && !empty($data['email'])) {
                send_mail_template(
                    'customer_created_welcome_mail',
                    $data['email'],
                    $data['userid'],
                    $contact_id,
                    $password_before_hash
                );
            }

            if ($send_set_password_email) {
                $this->authentication_model->set_password_email($data['email'], 0);
            }

            if (defined('CONTACT_REGISTERING')) {
                $this->send_verification_email($contact_id);
            } else {
                // User already verified because is added from admin area, try to transfer any tickets
                $this->load->model('tickets_model');
                $this->tickets_model->transfer_email_tickets_to_contact($data['email'], $contact_id);
            }

            log_activity('Contact Created [ID: ' . $contact_id . ']');

            hooks()->do_action('contact_created', $contact_id);

            return $contact_id;
        }

        return false;
    }

    /**
     * Add new contact via customers area
     *
     * @param array  $data
     * @param mixed  $customer_id
     */
    public function add_contact_via_customers_area($data, $customer_id)
    {
        $send_welcome_email      = isset($data['donotsendwelcomeemail']) && $data['donotsendwelcomeemail'] ? false : true;
        $send_set_password_email = isset($data['send_set_password_email']) && $data['send_set_password_email'] ? true : false;
        $custom_fields           = $data['custom_fields'];
        unset($data['custom_fields']);

        if (!is_email_verification_enabled()) {
            $data['email_verified_at'] = date('Y-m-d H:i:s');
        } elseif (is_email_verification_enabled() && !empty($data['email'])) {
            // Verification is required on register
            $data['email_verified_at']      = null;
            $data['email_verification_key'] = app_generate_hash();
        }

        $password_before_hash = $data['password'];

        $data = array_merge($data, [
            'datecreated' => date('Y-m-d H:i:s'),
            'userid'      => $customer_id,
            'password'    => app_hash_password(isset($data['password']) ? $data['password'] : time()),
        ]);

        $data = hooks()->apply_filters('before_create_contact', $data);
        $this->db->insert(db_prefix() . 'contacts', $data);

        $contact_id = $this->db->insert_id();

        if ($contact_id) {
            handle_custom_fields_post($contact_id, $custom_fields);

            // Apply default permissions
            $default_permissions = @unserialize(get_option('default_contact_permissions'));

            if (is_array($default_permissions)) {
                foreach (get_contact_permissions() as $permission) {
                    if (in_array($permission['id'], $default_permissions)) {
                        $this->db->insert(db_prefix() . 'contact_permissions', [
                            'userid'        => $contact_id,
                            'permission_id' => $permission['id'],
                        ]);
                    }
                }
            }

            if ($send_welcome_email === true) {
                send_mail_template(
                    'customer_created_welcome_mail',
                    $data['email'],
                    $customer_id,
                    $contact_id,
                    $password_before_hash
                );
            }

            if ($send_set_password_email === true) {
                $this->authentication_model->set_password_email($data['email'], 0);
            }

            log_activity('Contact Created [ID: ' . $contact_id . ']');
            hooks()->do_action('contact_created', $contact_id);

            return $contact_id;
        }

        return false;
    }

    /**
     * Used to update company details from customers area
     * @param  array $data $_POST data
     * @param  mixed $id
     * @return boolean
     */
    public function update_company_details($data, $id)
    {
        $affectedRows = 0;
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        if (isset($data['country']) && $data['country'] == '' || !isset($data['country'])) {
            $data['country'] = 0;
        }
        if (isset($data['billing_country']) && $data['billing_country'] == '') {
            $data['billing_country'] = 0;
        }
        if (isset($data['shipping_country']) && $data['shipping_country'] == '') {
            $data['shipping_country'] = 0;
        }

        // From v.1.9.4 these fields are textareas
        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);
        if (isset($data['billing_street'])) {
            $data['billing_street'] = trim($data['billing_street']);
            $data['billing_street'] = nl2br($data['billing_street']);
        }
        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }

        $data = hooks()->apply_filters('customer_update_company_info', $data, $id);

        $this->db->where('userid', $id);
        $this->db->update(db_prefix() . 'clients', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            hooks()->do_action('customer_updated_company_info', $id);
            log_activity('Customer Info Updated From Clients Area [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get customer staff members that are added as customer admins
     * @param  mixed $id customer id
     * @return array
     */
    public function get_admins($id)
    {
        $this->db->where('customer_id', $id);

        return $this->db->get(db_prefix() . 'customer_admins')->result_array();
    }

    /**
     * Get unique staff id's of customer admins
     * @return array
     */
    public function get_customers_admin_unique_ids()
    {
        return $this->db->query('SELECT DISTINCT(staff_id) FROM ' . db_prefix() . 'customer_admins')->result_array();
    }

    /**
     * Assign staff members as admin to customers
     * @param  array $data $_POST data
     * @param  mixed $id   customer id
     * @return boolean
     */
    public function assign_admins($data, $id)
    {
        $affectedRows = 0;

        if (count($data) == 0) {
            $this->db->where('customer_id', $id);
            $this->db->delete(db_prefix() . 'customer_admins');
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } else {
            $current_admins     = $this->get_admins($id);
            $current_admins_ids = [];
            foreach ($current_admins as $c_admin) {
                array_push($current_admins_ids, $c_admin['staff_id']);
            }
            foreach ($current_admins_ids as $c_admin_id) {
                if (!in_array($c_admin_id, $data['customer_admins'])) {
                    $this->db->where('staff_id', $c_admin_id);
                    $this->db->where('customer_id', $id);
                    $this->db->delete(db_prefix() . 'customer_admins');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            foreach ($data['customer_admins'] as $n_admin_id) {
                if (total_rows(db_prefix() . 'customer_admins', [
                    'customer_id' => $id,
                    'staff_id' => $n_admin_id,
                ]) == 0) {
                    $this->db->insert(db_prefix() . 'customer_admins', [
                        'customer_id'   => $id,
                        'staff_id'      => $n_admin_id,
                        'date_assigned' => date('Y-m-d H:i:s'),
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param  integer ID
     * @return boolean
     * Delete client, also deleting rows from, dismissed client announcements, ticket replies, tickets, autologin, user notes
     */
    public function delete($id)
    {
        $affectedRows = 0;

        if (!is_gdpr() && is_reference_in_table('clientid', db_prefix() . 'invoices', $id)) {
            return [
                'referenced' => true,
            ];
        }

        if (!is_gdpr() && is_reference_in_table('clientid', db_prefix() . 'estimates', $id)) {
            return [
                'referenced' => true,
            ];
        }

        if (!is_gdpr() && is_reference_in_table('clientid', db_prefix() . 'creditnotes', $id)) {
            return [
                'referenced' => true,
            ];
        }

        hooks()->do_action('before_client_deleted', $id);

        $last_activity = get_last_system_activity_id();
        $company       = get_company_name($id);

        $this->db->where('userid', $id);
        $this->db->delete(db_prefix() . 'clients');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            // Delete all user contacts
            $this->db->where('userid', $id);
            $contacts = $this->db->get(db_prefix() . 'contacts')->result_array();
            foreach ($contacts as $contact) {
                $this->delete_contact($contact['id']);
            }

            // Delete all tickets start here
            $this->db->where('userid', $id);
            $tickets = $this->db->get(db_prefix() . 'tickets')->result_array();
            $this->load->model('tickets_model');
            foreach ($tickets as $ticket) {
                $this->tickets_model->delete($ticket['ticketid']);
            }

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'customer');
            $this->db->delete(db_prefix() . 'notes');

            if (is_gdpr() && get_option('gdpr_on_forgotten_remove_invoices_credit_notes') == '1') {
                $this->load->model('invoices_model');
                $this->db->where('clientid', $id);
                $invoices = $this->db->get(db_prefix() . 'invoices')->result_array();
                foreach ($invoices as $invoice) {
                    $this->invoices_model->delete($invoice['id'], true);
                }

                $this->load->model('credit_notes_model');
                $this->db->where('clientid', $id);
                $credit_notes = $this->db->get(db_prefix() . 'creditnotes')->result_array();
                foreach ($credit_notes as $credit_note) {
                    $this->credit_notes_model->delete($credit_note['id'], true);
                }
            } elseif (is_gdpr()) {
                $this->db->where('clientid', $id);
                $this->db->update(db_prefix() . 'invoices', ['deleted_customer_name' => $company]);

                $this->db->where('clientid', $id);
                $this->db->update(db_prefix() . 'creditnotes', ['deleted_customer_name' => $company]);
            }

            $this->db->where('clientid', $id);
            $this->db->update(db_prefix() . 'creditnotes', [
                'clientid'   => 0,
                'project_id' => 0,
            ]);

            $this->db->where('clientid', $id);
            $this->db->update(db_prefix() . 'invoices', [
                'clientid'                 => 0,
                'recurring'                => 0,
                'recurring_type'           => null,
                'custom_recurring'         => 0,
                'cycles'                   => 0,
                'last_recurring_date'      => null,
                'project_id'               => 0,
                'subscription_id'          => 0,
                'cancel_overdue_reminders' => 1,
                'last_overdue_reminder'    => null,
                'last_due_reminder'        => null,
            ]);

            if (is_gdpr() && get_option('gdpr_on_forgotten_remove_estimates') == '1') {
                $this->load->model('estimates_model');
                $this->db->where('clientid', $id);
                $estimates = $this->db->get(db_prefix() . 'estimates')->result_array();
                foreach ($estimates as $estimate) {
                    $this->estimates_model->delete($estimate['id'], true);
                }
            } elseif (is_gdpr()) {
                $this->db->where('clientid', $id);
                $this->db->update(db_prefix() . 'estimates', ['deleted_customer_name' => $company]);
            }

            $this->db->where('clientid', $id);
            $this->db->update(db_prefix() . 'estimates', [
                'clientid'           => 0,
                'project_id'         => 0,
                'is_expiry_notified' => 1,
            ]);

            $this->load->model('subscriptions_model');
            $this->db->where('clientid', $id);
            $subscriptions = $this->db->get(db_prefix() . 'subscriptions')->result_array();
            foreach ($subscriptions as $subscription) {
                $this->subscriptions_model->delete($subscription['id'], true);
            }
            // Get all client contracts
            $this->load->model('contracts_model');
            $this->db->where('client', $id);
            $contracts = $this->db->get(db_prefix() . 'contracts')->result_array();
            foreach ($contracts as $contract) {
                $this->contracts_model->delete($contract['id']);
            }
            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'customers');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            // Get customer related tasks
            $this->db->where('rel_type', 'customer');
            $this->db->where('rel_id', $id);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();

            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id'], false);
            }

            $this->db->where('rel_type', 'customer');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'reminders');

            $this->db->where('customer_id', $id);
            $this->db->delete(db_prefix() . 'customer_admins');

            $this->db->where('customer_id', $id);
            $this->db->delete(db_prefix() . 'vault');

            $this->db->where('customer_id', $id);
            $this->db->delete(db_prefix() . 'customer_groups');

            $this->load->model('proposals_model');
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'customer');
            $proposals = $this->db->get(db_prefix() . 'proposals')->result_array();
            foreach ($proposals as $proposal) {
                $this->proposals_model->delete($proposal['id']);
            }
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'customer');
            $attachments = $this->db->get(db_prefix() . 'files')->result_array();
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            $this->db->where('clientid', $id);
            $expenses = $this->db->get(db_prefix() . 'expenses')->result_array();

            $this->load->model('expenses_model');
            foreach ($expenses as $expense) {
                $this->expenses_model->delete($expense['id'], true);
            }

            $this->db->where('client_id', $id);
            $this->db->delete(db_prefix() . 'user_meta');

            $this->db->where('client_id', $id);
            $this->db->update(db_prefix() . 'leads', ['client_id' => 0]);

            // Delete all projects
            $this->load->model('projects_model');
            $this->db->where('clientid', $id);
            $projects = $this->db->get(db_prefix() . 'projects')->result_array();
            foreach ($projects as $project) {
                $this->projects_model->delete($project['id']);
            }
        }
        if ($affectedRows > 0) {
            hooks()->do_action('after_client_deleted', $id);

            // Delete activity log caused by delete customer function
            if ($last_activity) {
                $this->db->where('id >', $last_activity->id);
                $this->db->delete(db_prefix() . 'activity_log');
            }

            log_activity('Client Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete customer contact
     * @param  mixed $id contact id
     * @return boolean
     */
    public function delete_contact($id)
    {
        hooks()->do_action('before_delete_contact', $id);

        $this->db->where('id', $id);
        $result      = $this->db->get(db_prefix() . 'contacts')->row();
        $customer_id = $result->userid;

        $last_activity = get_last_system_activity_id();

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'contacts');

        if ($this->db->affected_rows() > 0) {
            if (is_dir(get_upload_path_by_type('contact_profile_images') . $id)) {
                delete_dir(get_upload_path_by_type('contact_profile_images') . $id);
            }

            $this->db->where('contact_id', $id);
            $this->db->delete(db_prefix() . 'consents');

            $this->db->where('contact_id', $id);
            $this->db->delete(db_prefix() . 'shared_customer_files');

            $this->db->where('userid', $id);
            $this->db->where('staff', 0);
            $this->db->delete(db_prefix() . 'dismissed_announcements');

            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'contacts');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('userid', $id);
            $this->db->delete(db_prefix() . 'contact_permissions');

            $this->db->where('user_id', $id);
            $this->db->where('staff', 0);
            $this->db->delete(db_prefix() . 'user_auto_login');

            $this->db->select('ticketid');
            $this->db->where('contactid', $id);
            $this->db->where('userid', $customer_id);
            $tickets = $this->db->get(db_prefix() . 'tickets')->result_array();

            $this->load->model('tickets_model');
            foreach ($tickets as $ticket) {
                $this->tickets_model->delete($ticket['ticketid']);
            }

            $this->load->model('tasks_model');

            $this->db->where('addedfrom', $id);
            $this->db->where('is_added_from_contact', 1);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();

            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id'], false);
            }

            // Added from contact in customer profile
            $this->db->where('contact_id', $id);
            $this->db->where('rel_type', 'customer');
            $attachments = $this->db->get(db_prefix() . 'files')->result_array();

            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            // Remove contact files uploaded to tasks
            $this->db->where('rel_type', 'task');
            $this->db->where('contact_id', $id);
            $filesUploadedFromContactToTasks = $this->db->get(db_prefix() . 'files')->result_array();

            foreach ($filesUploadedFromContactToTasks as $file) {
                $this->tasks_model->remove_task_attachment($file['id']);
            }

            $this->db->where('contact_id', $id);
            $tasksComments = $this->db->get(db_prefix() . 'task_comments')->result_array();
            foreach ($tasksComments as $comment) {
                $this->tasks_model->remove_comment($comment['id'], true);
            }

            $this->load->model('projects_model');

            $this->db->where('contact_id', $id);
            $files = $this->db->get(db_prefix() . 'project_files')->result_array();
            foreach ($files as $file) {
                $this->projects_model->remove_file($file['id'], false);
            }

            $this->db->where('contact_id', $id);
            $discussions = $this->db->get(db_prefix() . 'projectdiscussions')->result_array();
            foreach ($discussions as $discussion) {
                $this->projects_model->delete_discussion($discussion['id'], false);
            }

            $this->db->where('contact_id', $id);
            $discussionsComments = $this->db->get(db_prefix() . 'projectdiscussioncomments')->result_array();
            foreach ($discussionsComments as $comment) {
                $this->projects_model->delete_discussion_comment($comment['id'], false);
            }

            $this->db->where('contact_id', $id);
            $this->db->delete(db_prefix() . 'user_meta');

            $this->db->where('(email="' . $result->email . '" OR bcc LIKE "%' . $result->email . '%" OR cc LIKE "%' . $result->email . '%")');
            $this->db->delete(db_prefix() . 'mail_queue');
           
            if (is_gdpr()) {
                if(table_exists('listemails')) {
                    $this->db->where('email', $result->email);
                    $this->db->delete(db_prefix() . 'listemails');
                }
                
                if (!empty($result->last_ip)) {
                    $this->db->where('ip', $result->last_ip);
                    $this->db->delete(db_prefix() . 'knowedge_base_article_feedback');
                }

                $this->db->where('email', $result->email);
                $this->db->delete(db_prefix() . 'tickets_pipe_log');

                $this->db->where('email', $result->email);
                $this->db->delete(db_prefix() . 'tracked_mails');

                $this->db->where('contact_id', $id);
                $this->db->delete(db_prefix() . 'project_activity');

                $this->db->where('(additional_data LIKE "%' . $result->email . '%" OR full_name LIKE "%' . $result->firstname . ' ' . $result->lastname . '%")');
                $this->db->where('additional_data != "" AND additional_data IS NOT NULL');
                $this->db->delete(db_prefix() . 'sales_activity');

                $contactActivityQuery = false;
                if (!empty($result->email)) {
                    $this->db->or_like('description', $result->email);
                    $contactActivityQuery = true;
                }
                if (!empty($result->firstname)) {
                    $this->db->or_like('description', $result->firstname);
                    $contactActivityQuery = true;
                }
                if (!empty($result->lastname)) {
                    $this->db->or_like('description', $result->lastname);
                    $contactActivityQuery = true;
                }

                if (!empty($result->phonenumber)) {
                    $this->db->or_like('description', $result->phonenumber);
                    $contactActivityQuery = true;
                }

                if (!empty($result->last_ip)) {
                    $this->db->or_like('description', $result->last_ip);
                    $contactActivityQuery = true;
                }

                if ($contactActivityQuery) {
                    $this->db->delete(db_prefix() . 'activity_log');
                }
            }

            // Delete activity log caused by delete contact function
            if ($last_activity) {
                $this->db->where('id >', $last_activity->id);
                $this->db->delete(db_prefix() . 'activity_log');
            }

            hooks()->do_action('contact_deleted', $id, $result);

            return true;
        }

        return false;
    }

    /**
     * Get customer default currency
     * @param  mixed $id customer id
     * @return mixed
     */
    public function get_customer_default_currency($id)
    {
        $this->db->select('default_currency');
        $this->db->where('userid', $id);
        $result = $this->db->get(db_prefix() . 'clients')->row();
        if ($result) {
            return $result->default_currency;
        }

        return false;
    }

    /**
     *  Get customer billing details
     * @param   mixed $id   customer id
     * @return  array
     */
    public function get_customer_billing_and_shipping_details($id)
    {
        $this->db->select('billing_street,billing_city,billing_state,billing_zip,billing_country,shipping_street,shipping_city,shipping_state,shipping_zip,shipping_country');
        $this->db->from(db_prefix() . 'clients');
        $this->db->where('userid', $id);

        $result = $this->db->get()->result_array();
        if (count($result) > 0) {
            $result[0]['billing_street']  = clear_textarea_breaks($result[0]['billing_street']);
            $result[0]['shipping_street'] = clear_textarea_breaks($result[0]['shipping_street']);
        }

        return $result;
    }

    /**
     * Get customer files uploaded in the customer profile
     * @param  mixed $id    customer id
     * @param  array  $where perform where
     * @return array
     */
    public function get_customer_files($id, $where = [])
    {
        $this->db->where($where);
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'customer');
        $this->db->order_by('dateadded', 'desc');

        return $this->db->get(db_prefix() . 'files')->result_array();
    }

    /**
     * Delete customer attachment uploaded from the customer profile
     * @param  mixed $id attachment id
     * @return boolean
     */
    public function delete_attachment($id)
    {
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'files')->row();
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                $relPath  = get_upload_path_by_type('customer') . $attachment->rel_id . '/';
                $fullPath = $relPath . $attachment->file_name;
                unlink($fullPath);
                $fname     = pathinfo($fullPath, PATHINFO_FILENAME);
                $fext      = pathinfo($fullPath, PATHINFO_EXTENSION);
                $thumbPath = $relPath . $fname . '_thumb.' . $fext;
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }

            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                $this->db->where('file_id', $id);
                $this->db->delete(db_prefix() . 'shared_customer_files');
                log_activity('Customer Attachment Deleted [ID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('customer') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('customer') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    delete_dir(get_upload_path_by_type('customer') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update contact status Active/Inactive
     */
    public function change_contact_status($id, $status)
    {
        $status = hooks()->apply_filters('change_contact_status', $status, $id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contacts', [
            'active' => $status,
        ]);
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('contact_status_changed', [
                'id'     => $id,
                'status' => $status,
            ]);

            log_activity('Contact Status Changed [ContactID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update client status Active/Inactive
     */
    public function change_client_status($id, $status)
    {
        $this->db->where('userid', $id);
        $this->db->update('clients', [
            'active' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('client_status_changed', [
                'id'     => $id,
                'status' => $status,
            ]);

            log_activity('Customer Status Changed [ID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    /**
     * Change contact password, used from client area
     * @param  mixed $id          contact id to change password
     * @param  string $oldPassword old password to verify
     * @param  string $newPassword new password
     * @return boolean
     */
    public function change_contact_password($id, $oldPassword, $newPassword)
    {
        // Get current password
        $this->db->where('id', $id);
        $client = $this->db->get(db_prefix() . 'contacts')->row();

        if (!app_hasher()->CheckPassword($oldPassword, $client->password)) {
            return [
                'old_password_not_match' => true,
            ];
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contacts', [
            'last_password_change' => date('Y-m-d H:i:s'),
            'password'             => app_hash_password($newPassword),
        ]);

        if ($this->db->affected_rows() > 0) {
            log_activity('Contact Password Changed [ContactID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get customer groups where customer belongs
     * @param  mixed $id customer id
     * @return array
     */
    public function get_customer_groups($id)
    {
        return $this->client_groups_model->get_customer_groups($id);
    }

    /**
     * Get all customer groups
     * @param  string $id
     * @return mixed
     */
    public function get_groups($id = '')
    {
        return $this->client_groups_model->get_groups($id);
    }

    /**
     * Delete customer groups
     * @param  mixed $id group id
     * @return boolean
     */
    public function delete_group($id)
    {
        return $this->client_groups_model->delete($id);
    }

    /**
     * Add new customer groups
     * @param array $data $_POST data
     */
    public function add_group($data)
    {
        return $this->client_groups_model->add($data);
    }

    /**
     * Edit customer group
     * @param  array $data $_POST data
     * @return boolean
     */
    public function edit_group($data)
    {
        return $this->client_groups_model->edit($data);
    }

    /**
    * Create new vault entry
    * @param  array $data        $_POST data
    * @param  mixed $customer_id customer id
    * @return boolean
    */
    public function vault_entry_create($data, $customer_id)
    {
        return $this->client_vault_entries_model->create($data, $customer_id);
    }

    /**
     * Update vault entry
     * @param  mixed $id   vault entry id
     * @param  array $data $_POST data
     * @return boolean
     */
    public function vault_entry_update($id, $data)
    {
        return $this->client_vault_entries_model->update($id, $data);
    }

    /**
     * Delete vault entry
     * @param  mixed $id entry id
     * @return boolean
     */
    public function vault_entry_delete($id)
    {
        return $this->client_vault_entries_model->delete($id);
    }

    /**
     * Get customer vault entries
     * @param  mixed $customer_id
     * @param  array  $where       additional wher
     * @return array
     */
    public function get_vault_entries($customer_id, $where = [])
    {
        return $this->client_vault_entries_model->get_by_customer_id($customer_id, $where);
    }

    /**
     * Get single vault entry
     * @param  mixed $id vault entry id
     * @return object
     */
    public function get_vault_entry($id)
    {
        return $this->client_vault_entries_model->get($id);
    }

    /**
    * Get customer statement formatted
    * @param  mixed $customer_id customer id
    * @param  string $from        date from
    * @param  string $to          date to
    * @return array
    */
    public function get_statement($customer_id, $from, $to)
    {
        return $this->statement_model->get_statement($customer_id, $from, $to);
    }

    /**
    * Send customer statement to email
    * @param  mixed $customer_id customer id
    * @param  array $send_to     array of contact emails to send
    * @param  string $from        date from
    * @param  string $to          date to
    * @param  string $cc          email CC
    * @return boolean
    */
    public function send_statement_to_email($customer_id, $send_to, $from, $to, $cc = '')
    {
        return $this->statement_model->send_statement_to_email($customer_id, $send_to, $from, $to, $cc);
    }

    /**
     * When customer register, mark the contact and the customer as inactive and set the registration_confirmed field to 0
     * @param  mixed $client_id  the customer id
     * @return boolean
     */
    public function require_confirmation($client_id)
    {
        $contact_id = get_primary_contact_user_id($client_id);
        $this->db->where('userid', $client_id);
        $this->db->update(db_prefix() . 'clients', ['active' => 0, 'registration_confirmed' => 0]);

        $this->db->where('id', $contact_id);
        $this->db->update(db_prefix() . 'contacts', ['active' => 0]);

        return true;
    }

    public function confirm_registration($client_id)
    {
        $contact_id = get_primary_contact_user_id($client_id);
        $this->db->where('userid', $client_id);
        $this->db->update(db_prefix() . 'clients', ['active' => 1, 'registration_confirmed' => 1]);

        $this->db->where('id', $contact_id);
        $this->db->update(db_prefix() . 'contacts', ['active' => 1]);

        $contact = $this->get_contact($contact_id);

        if ($contact) {
            send_mail_template('customer_registration_confirmed', $contact);

            return true;
        }

        return false;
    }

    public function send_verification_email($id)
    {
        $contact = $this->get_contact($id);

        if (empty($contact->email)) {
            return false;
        }

        $success = send_mail_template('customer_contact_verification', $contact);

        if ($success) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'contacts', ['email_verification_sent_at' => date('Y-m-d H:i:s')]);
        }

        return $success;
    }

    public function mark_email_as_verified($id)
    {
        $contact = $this->get_contact($id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contacts', [
            'email_verified_at'          => date('Y-m-d H:i:s'),
            'email_verification_key'     => null,
            'email_verification_sent_at' => null,
        ]);

        if ($this->db->affected_rows() > 0) {

            // Check for previous tickets opened by this email/contact and link to the contact
            $this->load->model('tickets_model');
            $this->tickets_model->transfer_email_tickets_to_contact($contact->email, $contact->id);

            return true;
        }

        return false;
    }

    public function get_clients_distinct_countries()
    {
        return $this->db->query('SELECT DISTINCT(country_id), short_name FROM ' . db_prefix() . 'clients JOIN ' . db_prefix() . 'countries ON ' . db_prefix() . 'countries.country_id=' . db_prefix() . 'clients.country')->result_array();
    }

    public function send_notification_customer_profile_file_uploaded_to_responsible_staff($contact_id, $customer_id)
    {
        $staff         = $this->get_staff_members_that_can_access_customer($customer_id);
        $merge_fields  = $this->app_merge_fields->format_feature('client_merge_fields', $customer_id, $contact_id);
        $notifiedUsers = [];


        foreach ($staff as $member) {
            mail_template('customer_profile_uploaded_file_to_staff', $member['email'], $member['staffid'])
            ->set_merge_fields($merge_fields)
            ->send();

            if (add_notification([
                    'touserid' => $member['staffid'],
                    'description' => 'not_customer_uploaded_file',
                    'link' => 'clients/client/' . $customer_id . '?group=attachments',
                ])) {
                array_push($notifiedUsers, $member['staffid']);
            }
        }
        pusher_trigger_notification($notifiedUsers);
    }

    public function get_staff_members_that_can_access_customer($id)
    {
        $id = $this->db->escape_str($id);

        return $this->db->query('SELECT * FROM ' . db_prefix() . 'staff
            WHERE (
                    admin=1
                    OR staffid IN (SELECT staff_id FROM ' . db_prefix() . "customer_admins WHERE customer_id='.$id.')
                    OR staffid IN(SELECT staff_id FROM " . db_prefix() . 'staff_permissions WHERE feature = "customers" AND capability="view")
                )
            AND active=1')->result_array();
    }

    private function check_zero_columns($data)
    {
        if (!isset($data['show_primary_contact'])) {
            $data['show_primary_contact'] = 0;
        }

        if (isset($data['default_currency']) && $data['default_currency'] == '' || !isset($data['default_currency'])) {
            $data['default_currency'] = 0;
        }

        if (isset($data['country']) && $data['country'] == '' || !isset($data['country'])) {
            $data['country'] = 0;
        }

        if (isset($data['billing_country']) && $data['billing_country'] == '' || !isset($data['billing_country'])) {
            $data['billing_country'] = 0;
        }

        if (isset($data['shipping_country']) && $data['shipping_country'] == '' || !isset($data['shipping_country'])) {
            $data['shipping_country'] = 0;
        }

        return $data;
    }

    public function delete_contact_profile_image($id)
    {
        hooks()->do_action('before_remove_contact_profile_image');
        if (file_exists(get_upload_path_by_type('contact_profile_images') . $id)) {
            delete_dir(get_upload_path_by_type('contact_profile_images') . $id);
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contacts', [
            'profile_image' => null,
        ]);
    }

    /**
     * @param $projectId
     * @param  string  $tasks_email
     *
     * @return array[]
     */
    public function get_contacts_for_project_notifications($projectId, $type)
    {
        $this->db->select('clientid,contact_notification,notify_contacts');
        $this->db->from(db_prefix() . 'projects');
        $this->db->where('id', $projectId);
        $project = $this->db->get()->row();

        if (!in_array($project->contact_notification, [1, 2])) {
            return [];
        }

        $this->db
            ->where('userid', $project->clientid)
            ->where('active', 1)
            ->where($type, 1);

        if ($project->contact_notification == 2) {
            $projectContacts = unserialize($project->notify_contacts);
            $this->db->where_in('id', $projectContacts);
        }

        return $this->db->get(db_prefix() . 'contacts')->result_array();
    }

    public function save_client(){
        $this->db->trans_begin();

        $shouldGenerateMr = false;
        $client_id        = null;

        $rawContactNumber       = trim((string) $this->input->post('contact_number'));
        $normalizedContactNumber = preg_replace('/\D+/', '', $rawContactNumber);

        $phoneCandidates = [];
        foreach ([$rawContactNumber, $normalizedContactNumber] as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '' && !in_array($candidate, $phoneCandidates, true)) {
                $phoneCandidates[] = $candidate;
            }
        }

        $normalizedCompany = mb_strtolower(trim((string) $this->input->post('company')));

        $lockKey      = null;
        $lockAcquired = false;
        if (!empty($phoneCandidates)) {
            $lockKey = 'client_' . md5($normalizedCompany . '|' . implode('|', $phoneCandidates));
            $lockRow = $this->db->query('SELECT GET_LOCK(?, 5) AS lck', [$lockKey])->row();
            if ($lockRow && (int) $lockRow->lck === 1) {
                $lockAcquired = true;
            }
        }

        $this->load->model('leads_model');
        $statuses = $this->leads_model->get_status();
        $status_name = '';
        $status_id   = '';
        if (!empty($this->input->post('paying_amount')) && $this->input->post('paying_amount') > 0) {
            foreach ($statuses as $status) {
                if (strcasecmp(trim($status['name']), 'Paid Appointment') === 0) {
                    $status_name = $status['name'];
                    $status_id   = $status['id'];
                    break;
                }
            }
        } elseif (!empty($this->input->post('appointment_date'))) {
            foreach ($statuses as $status) {
                if (strcasecmp(trim($status['name']), 'On appointment') === 0) {
                    $status_name = $status['name'];
                    $status_id   = $status['id'];
                    break;
                }
            }
        }

        //Get Visit id
        $this->db->from(db_prefix() . 'appointment');
        $this->db->like('DATE(appointment_date)', date('Y-m-d'));
        $count = $this->db->count_all_results();
		
		$branch_id = $this->current_branch_id; // or fetch from session/context if not already available

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
		
		
		/* if($count){
			$number = $branch_code.'-'.$branch_short_code.'-'.date('dm').$count + 1;
		}else{
			$number = $branch_code.'-'.$branch_short_code.'-1';
		} */
		
		if($count){
			$number = $branch_code.'-'.date('Ymd').'-'.$count + 1;
		}else{
			$number = $branch_code.'-'.date('Ymd').'-00001';
		}
		$formatted_number = str_pad($number, 4, '0', STR_PAD_LEFT);
		//$visit_id = "V-".$formatted_number;
		$visit_id = "";
		$mr_no = $formatted_number;
		$mr_no = "";
		
		
        $default_language = $this->input->post('default_language');
        $default_language_string = is_array($default_language) ? implode(',', $default_language) : $default_language;
        
        $data = array(
            "company" => $this->input->post('company'),
            "phonenumber" => !empty($normalizedContactNumber) ? $normalizedContactNumber : $rawContactNumber,
            "address" => $this->input->post('area'),
            "default_language" => $default_language_string,
            "city" => $this->input->post('city'),
            "address" => $this->input->post('area'),
            "state" => $this->input->post('area'),
            //"country" => 102,
            "datecreated" => date('Y-m-d H:i:s'),
        );
        $table_suffix = "clients";

        //Check User
        $check_user = null;
        if (!empty($phoneCandidates)) {
            $placeholders = implode(', ', array_fill(0, count($phoneCandidates), '?'));
            $params       = $phoneCandidates;

            $sql = 'SELECT * FROM ' . db_prefix() . $table_suffix . ' WHERE phonenumber IN (' . $placeholders . ')';
            if ($normalizedCompany !== '') {
                $sql    .= ' AND LOWER(TRIM(company)) = ?';
                $params[] = $normalizedCompany;
            }
            $sql .= ' ORDER BY userid ASC LIMIT 1 FOR UPDATE';

            $check_user = $this->db->query($sql, $params)->row();

            if (!$check_user && $normalizedCompany !== '') {
                $sql = 'SELECT * FROM ' . db_prefix() . $table_suffix . ' WHERE phonenumber IN (' . $placeholders . ') ORDER BY userid ASC LIMIT 1 FOR UPDATE';
                $check_user = $this->db->query($sql, $phoneCandidates)->row();
            }
        }

        $return = 0;
        if($check_user){
            $client_id = (int) $check_user->userid;

            $this->db->where('userid', $client_id);
            $this->db->update(db_prefix() . $table_suffix, $data);

            // Prepare patient profile data
            $clients_new_fields_data = array(
                'marital_status'  => $this->input->post('marital_status'),
                'email_id'        => $this->input->post('email_id'),
                'pincode'         => $this->input->post('pincode'),
                'area'            => $this->input->post('area'),
                'salutation'      => $this->input->post('salutation'),
                'age'             => $this->input->post('age'),
                'gender'          => $this->input->post('gender'),
                'whatsapp_number' => !empty($normalizedContactNumber) ? $normalizedContactNumber : $rawContactNumber,
                'alt_number1'     => $this->input->post('alt_number1'),
                'alt_number2'     => $this->input->post('alt_number2'),
                'patient_source_id' => $this->input->post('patient_source_id'),
                'current_status'  => $status_name
            );

            $clients_new_fields_insert = $clients_new_fields_data;
            $clients_new_fields_insert['userid'] = $client_id;
            $clients_new_fields_insert['patient_status'] = 'Active';

            //Get MR NO
            $get_mr_no = $this->db->get_where(db_prefix() . 'clients_new_fields', array("userid"=>$client_id))->row();
            if($get_mr_no){
                $mr_no = $get_mr_no->mr_no;
                if (empty($mr_no)) {
                    $shouldGenerateMr = true;
                }
                $this->db->where('userid', $client_id);
                $this->db->update(db_prefix() . 'clients_new_fields', $clients_new_fields_data);
				
            }else{
                //Inserting patient other fields
                $this->db->insert(db_prefix() . 'clients_new_fields', $clients_new_fields_insert);
                $shouldGenerateMr = true;
            }
			
        }else{
			
			$data['leadid'] = $this->input->post('leadid');
            $this->db->insert(db_prefix() . $table_suffix, $data);
			$client_id = $this->db->insert_id();
			
			$update_lead = array(
			"date_converted" => date('Y-m-d H:i:s')
			);
			$update_lead['status'] = $status_id;
				
			$this->db->where(array("id"=>$this->input->post('leadid')));
			$this->db->update(db_prefix() . 'leads', $update_lead);
			
			
            $return = 1;
            $description = "new_patient_added";
            $this->log_patient_activity($client_id, $description);
				
            
            //Get MR NO
            $get_mr_no = $this->db->get_where(db_prefix() . 'clients_new_fields', array("userid"=>$client_id))->row();
            if($get_mr_no){
                $mr_no = $get_mr_no->mr_no;
                if (empty($mr_no)) {
                    $shouldGenerateMr = true;
                }
                $clients_new_fields_data = array(
					'marital_status'  => $this->input->post('marital_status'),
                    'email_id'  	=> $this->input->post('email_id'),
                    'pincode'   	=> $this->input->post('pincode'),
                    'area'  		=> $this->input->post('area'),
                    'salutation'  	=> $this->input->post('salutation'),
                    'age'         	=> $this->input->post('age'),
                    'gender'        => $this->input->post('gender'),
                    'whatsapp_number'=> !empty($normalizedContactNumber) ? $normalizedContactNumber : $rawContactNumber,
                    'alt_number1'=> $this->input->post('alt_number1'),
                    'alt_number2'=> $this->input->post('alt_number2'),
                    'patient_source_id'=> $this->input->post('patient_source_id'),
					'current_status' => $status_name
                );
                $this->db->where('userid', $client_id);
                $this->db->update(db_prefix() . 'clients_new_fields', $clients_new_fields_data);
            }else{
                //Inserting patient other fields
                $clients_new_fields_data = array(
					'userid'      => $client_id,
                    'marital_status'  => $this->input->post('marital_status'),
                    'email_id'  	=> $this->input->post('email_id'),
                    'pincode'   	=> $this->input->post('pincode'),
                    'area'  		=> $this->input->post('area'),
                    'salutation'  	=> $this->input->post('salutation'),
                    'age'         	=> $this->input->post('age'),
                    'gender'        => $this->input->post('gender'),
                    'patient_status'=> 'Active',
                    'whatsapp_number'=> !empty($normalizedContactNumber) ? $normalizedContactNumber : $rawContactNumber,
                    'alt_number1'=> $this->input->post('alt_number1'),
                    'alt_number2'=> $this->input->post('alt_number2'),
                    'patient_source_id'=> $this->input->post('patient_source_id'),
					'current_status' => $status_name
                );
                $this->db->insert(db_prefix() . 'clients_new_fields', $clients_new_fields_data);
                $shouldGenerateMr = true;
            }
			
           //$this->patient_journey_log_event($client_id, 'patient_created', 'New Patient Created');
        }
        if($client_id AND $this->input->post('groupid')){
            $group_id = $this->input->post('groupid');
            $existing_group = $this->db->get_where(db_prefix() . 'customer_groups', [
                'groupid'     => $group_id,
                'customer_id' => $client_id,
            ])->row();

            if (!$existing_group) {
                $group_data = array(
                    "groupid"      => $group_id,
                    "customer_id"  => $client_id
                );
                $table_suffix = "customer_groups";
                $this->db->insert(db_prefix() . $table_suffix, $group_data);
            }
        }
		
		$attachment_path = null;

		if (!empty($_FILES['attachment']['name'])) {
			$this->load->library('upload');
			$upload_path = 'uploads/appointment_attachments/';

			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0755, true);
			}

			$_FILES['file']['name']     = $_FILES['attachment']['name'];
			$_FILES['file']['type']     = $_FILES['attachment']['type'];
			$_FILES['file']['tmp_name'] = $_FILES['attachment']['tmp_name'];
			$_FILES['file']['error']    = $_FILES['attachment']['error'];
			$_FILES['file']['size']     = $_FILES['attachment']['size'];

			$config['upload_path']   = $upload_path;
			$config['allowed_types'] = '*'; // or set to 'jpg|jpeg|png|pdf|doc|docx'
			$config['file_name']     = uniqid();

			$this->upload->initialize($config);

			if ($this->upload->do_upload('file')) {
				$upload_data = $this->upload->data();
				$attachment_path = $upload_path . $upload_data['file_name'];
			}
		}
		
		// Now assign to DB field
		$attachment = $attachment_path;

        //if ($client_id) {
            //$client_id = $this->input->post('client_id');
			$appointment_date = $this->input->post('appointment_date');
            $appointment_date_formatted = $appointment_date ? date('Y-m-d H:i:s', strtotime($appointment_date)) : null;
			$branch_id = $this->input->post('groupid');
			
			$appointment_data = array(
				'userid'                => $client_id,
				'enquiry_type_id'       => $this->input->post('enquiry_type_id'),
				'appointment_type_id'   => $this->input->post('appointment_type_id'),
				'patient_response_id'   => $this->input->post('patient_response_id'),
				'patient_priority_id'   => $this->input->post('patient_priority_id'),
				'patient_source_id'     => $this->input->post('patient_source_id'),
				'slots_id'              => $this->input->post('slots_id'),
				'branch_id'             => $branch_id,
				'attachment'            => $attachment,
				'treatment_id'          => $this->input->post('treatment_id'),
				'consultation_fee_id'   => $this->input->post('item_select'),
				'enquiry_doctor_id'     => $this->input->post('assign_doctor_id'),
				'unit_doctor_id'        => $this->input->post('assign_doctor_id'),
				'remarks'               => $this->input->post('remarks'),
				'next_calling_date'     => date('Y-m-d', strtotime($this->input->post('next_calling_date'))),
				'appointment_date'      => $appointment_date_formatted,
				'created_by'            => get_staff_user_id(),
				'created_at'            => date('Y-m-d H:i:s')
			);

			// --- Duplicate Restriction ---
            $duplicate = null;

            if ($client_id && $appointment_date_formatted) {
                if ($branch_id) {
                    $sql    = 'SELECT appointment_id FROM ' . db_prefix() . 'appointment WHERE userid = ? AND branch_id = ? AND appointment_date = ? LIMIT 1 FOR UPDATE';
                    $params = [$client_id, $branch_id, $appointment_date_formatted];
                } else {
                    $sql    = 'SELECT appointment_id FROM ' . db_prefix() . 'appointment WHERE userid = ? AND appointment_date = ? LIMIT 1 FOR UPDATE';
                    $params = [$client_id, $appointment_date_formatted];
                }
                $duplicate = $this->db->query($sql, $params)->row();
            }

            if ($duplicate) {
                $this->db->trans_rollback();
                if ($lockAcquired) {
                    $this->db->query('SELECT RELEASE_LOCK(?)', [$lockKey]);
                }
                return 3;
            }

			// --- Insert New Appointment ---
			$this->db->insert(db_prefix() . 'appointment', $appointment_data);
			$appointment_id = $this->db->insert_id();
			
			if ($insert_id) {
				$appointment_id = $insert_id;
				/* $mobile  = $this->input->post('contact_number');
				$company = $this->input->post('company');
				$date    = date('d-m-Y H:i', strtotime($this->input->post('appointment_date')));
				$vars    = [$company, $date, $branch_code];

				// Trigger the hook, passing mobile and params
				hooks()->do_action('appointment_confirmation_triggered', [
					'channel' => 'whatsapp',
					'mobile' => $mobile,
					'params' => $vars,
				]);
				
				// Trigger the hook, passing mobile and params
				hooks()->do_action('appointment_confirmation_triggered', [
					'channel' => 'sms',
					'mobile' => $mobile,
					'params' => $vars,
				]); */
				
			}

			
			
			$this->load->model('invoices_model');
		
			$year = date('Y');

			$this->db->from('tblinvoices');
			$this->db->where('YEAR(date)', $year);
			$count = $this->db->count_all_results();

			$next_number = $count + 1;
			$invoice_number = 'INV-' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
			
			
			if($this->input->post('paying_amount')){
				$paying_amount = $this->input->post('paying_amount');
			}else{
				$paying_amount = 0;
			}

			/* if($this->input->post('due_amount')){
				$due_amount = $this->input->post('due_amount');
			}else{
				$due_amount = 0;
				
			} */
			
			if($paying_amount>0){
				$assigned_doctor = get_staff_full_name($this->input->post('assign_doctor_id'));
				$appointment_date = date('d-m-Y', strtotime($this->input->post('appointment_date')));
				$appointment_time = date('h:i A', strtotime($this->input->post('appointment_date'))); // 12-hour format with AM/PM
				$date_time = date('d-m-Y h:i A', strtotime($this->input->post('appointment_date')));   // Full date-time with AM/PM

				$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"consult_paid" => $paying_amount,
				"assigned_doctor" => $assigned_doctor,
				"appointment_date" => $appointment_date,
				"appointment_time" => $appointment_time,
				"date_time" => $date_time,
				"branch_address" => $branch_address,
				);
				$this->patient_journey_log_event($client_id, 'paid_appointment', 'Paid Appointment Created', $communication_data);
				
				$data = array(
				"current_status" => "Paid Appointment"
				);
				$this->db->where(array("userid"=>$client_id));
				$this->db->update(db_prefix() . 'clients_new_fields', $data);
				
				
				$setting = "Paid Appointment";
				$get_id = $this->db->get_where(db_prefix() . 'leads_status', array("name" => $setting))->row();
				if ($get_id) {
					$update_lead['status'] = $get_id->id;
					$this->db->where(array("id"=>$this->input->post('leadid')));
					$this->db->update(db_prefix() . 'leads', $update_lead);
					
					$data = array(
					"leadid" => $this->input->post('leadid'),
					"userid" => $client_id,
					"status" => $get_id->id,
					"datetime" => date('Y-m-d H:i:s'),
					"remarks" => "Paid Appointment Created from patient module"
					);
					$this->db->insert(db_prefix() . 'lead_patient_journey', $data);
				}
			
			}else{
				$assigned_doctor = get_staff_full_name($this->input->post('assign_doctor_id'));
				$appointment_date = date('d-m-Y', strtotime($this->input->post('appointment_date')));
				$appointment_time = date('h:i A', strtotime($this->input->post('appointment_date'))); // 12-hour format with AM/PM
				$date_time = date('d-m-Y h:i A', strtotime($this->input->post('appointment_date')));   // Full date-time with AM/PM

				$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"assigned_doctor" => $assigned_doctor,
				"appointment_date" => $appointment_date,
				"appointment_time" => $appointment_time,
				"date_time" => $date_time,
				"branch_address" => $branch_address,
				);
				$this->patient_journey_log_event($client_id, 'on_appointment', 'Appointment Created', $communication_data);
				
				$setting = "On Appointment";
				$get_id = $this->db->get_where(db_prefix() . 'leads_status', array("name" => $setting))->row();
				if ($get_id) {
					$update_lead['status'] = $get_id->id;
					$this->db->where(array("id"=>$this->input->post('leadid')));
					$this->db->update(db_prefix() . 'leads', $update_lead);
					
					$data = array(
					"leadid" => $this->input->post('leadid'),
					"userid" => $client_id,
					"status" => $get_id->id,
					"datetime" => date('Y-m-d H:i:s'),
					"remarks" => "On Appointment Created from patient module"
					);
					$this->db->insert(db_prefix() . 'lead_patient_journey', $data);
				}
			}
			$total = $this->input->post('item_select');
			
			$invoice_data['formatted_number'] = $invoice_number;
			$invoice_data['number'] = $next_number;
			$invoice_data['clientid'] = $client_id;
			$invoice_data['show_shipping_on_invoice'] = 1;
			$invoice_data['date'] = date('Y-m-d');
			$invoice_data['duedate'] = date('Y-m-d');
			$invoice_data['currency'] = 1;
			$invoice_data['addedfrom'] = get_staff_user_id();
			$invoice_data['subtotal'] = $total;
			$invoice_data['total'] = $total;
			$invoice_data['prefix'] = "INV-";
			$invoice_data['number_format'] = 1;
			$branch_id = $this->get_client_branch($client_id);
			$invoice_data['branch_id'] = $branch_id;
			
			$due_amount = $total - $paying_amount;
			$invoice_data['allowed_payment_modes'] = 'a:1:{i:0;s:1:"1";}';
			
			$invoice_data['datecreated'] = date('Y-m-d H:i:s');
			
			$id = $this->invoices_model->add($invoice_data); 
			
			
			if($paying_amount>0 || $total == 0){
				if($due_amount == 0){
					$status = 2;
				}else{
					$status = 3;
				}
			}else{
				$status = 1;
			}
			
			$update = array(
			'allowed_payment_modes' => 'a:1:{i:0;s:1:"1";}',
			'status' => $status,
			);
			$this->db->where(array("id"=>$id));
			$this->db->update(db_prefix() . 'invoices', $update);
			
			
			$appointment_update = array(
			'invoice_id' => $id,
			);
			$this->db->where(array("appointment_id"=>$appointment_id));
			$this->db->update(db_prefix() . 'appointment', $appointment_update);
			
			if($paying_amount == 0){
				//$this->confirm_booking($appointment_id);
			}
			
			
		   $itemable= array(
			"rel_id" => $id,
			"rel_type" => "invoice",
			"description" => "Consultation Fee",
			"qty" => 1,
			"item_order" => 1,
			"rate"=>$paying_amount + $due_amount
			);
			
			$this->db->insert(db_prefix() . 'itemable', $itemable);
			
			$branch_id = $this->get_client_branch($client_id);
			
			if($this->input->post('paying_amount')>0){
			   $invoicepaymentrecords= array(
				"invoiceid" => $id,
				"amount" => $this->input->post('paying_amount'),
				"paymentmode" => $this->input->post('paymentmode'),
				"date" => date('Y-m-d'),
				"daterecorded" => date('Y-m-d H:i:s'),
				"received_by" => get_staff_user_id(),
				"branch_id" => $branch_id,
				);
				
				$this->db->insert(db_prefix() . 'invoicepaymentrecords', $invoicepaymentrecords);
				
			}

            $description = "new_appointment_added";
            $this->log_patient_activity($client_id, $description);
            $return = 2;
			
        //}
        
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            if ($lockAcquired) {
                $this->db->query('SELECT RELEASE_LOCK(?)', [$lockKey]);
            }
            return 0;
        }

        $this->db->trans_commit();

        if ($shouldGenerateMr && $client_id) {
            $this->generate_mr_no($client_id);
        }

        if ($lockAcquired) {
            $this->db->query('SELECT RELEASE_LOCK(?)', [$lockKey]);
        }

        return $return;
    }

    public function get_client_due($client_id)
    {
		$this->db->select("
			COALESCE(SUM(inv.total), 0) AS total_amount,
			COALESCE(SUM(inv.total), 0) - COALESCE(SUM(pay.paid_amount), 0) AS due_amount
		", false);
		$this->db->from(db_prefix().'invoices as inv');
		$this->db->join(
			"(SELECT invoiceid, SUM(amount) as paid_amount 
			  FROM ".db_prefix()."invoicepaymentrecords 
			  GROUP BY invoiceid) as pay",
			"pay.invoiceid = inv.id",
			"left"
		);
		$this->db->where("inv.clientid", $client_id);

		$result = $this->db->get()->row();

		// Access values
		//$total_amount = $result->total_amount;
		return $due_amount   = $result->due_amount;
	}
    public function update_client()
    {
        $client_id = $this->input->post('userid');
        $groupid = $this->input->post('groupid');
		
		$due_amount = $this->get_client_due($client_id);
		
		if($due_amount == 0){
			$get_group = $this->db->get_where(db_prefix() . 'customer_groups', array("customer_id"=>$client_id))->row();
		
			if($get_group){
				$customer_groups_id = $get_group->id;
				$customer_groups_data = array(
				"groupid" => $groupid
				);
				$this->db->where(array("id"=>$customer_groups_id));
				$this->db->update(db_prefix() . 'customer_groups', $customer_groups_data);
			}
		}
		
		$existing_client = $this->db->get_where(db_prefix() . 'clients', ['userid' => $client_id])->row_array();
		$existing_client_fields = $this->db->get_where(db_prefix() . 'clients_new_fields', ['userid' => $client_id])->row_array();

        // Build data for main clients table
        $data = array(
			"company"          => $this->input->post('company'),
			"phonenumber"      => $this->input->post('contact_number'),
			"address"          => $this->input->post('address'),
			"billing_street"   => $this->input->post('area'),
			"default_language" => is_array($this->input->post('default_language')) 
									? implode(',', $this->input->post('default_language')) 
									: '',
		);

		// Conditionally update optional fields
		if ($this->input->post('city')) {
			$data["city"]         = $this->input->post('city');
			$data["billing_city"] = $this->input->post('city');
		}

		if ($this->input->post('state')) {
			$data["state"] = $this->input->post('state');
		}

		if ($this->input->post('pincode')) {
			$data["zip"]        = $this->input->post('pincode');
			$data["billing_zip"] = $this->input->post('pincode');
		}

    
        // Update main client table
        $this->db->where('userid', $client_id);
        $this->db->update(db_prefix() . 'clients', $data);
		
		$dob = date("Y-m-d", strtotime($this->input->post('dob')));
			$today = date("Y-m-d");

			// Calculate age
			$dobDate = new DateTime($dob);
			$todayDate = new DateTime($today);
			$age = $dobDate->diff($todayDate)->y;
    
        // Build data for clients_new_fields table
        $clients_new_fields_data = array(
            'userid'        => $client_id,
            'marital_status'=> $this->input->post('marital_status'),
			'email_id'  	=> $this->input->post('email_id'),
			'age'  		=> $this->input->post('age'),
			'area'  		=> $this->input->post('area'),
			'salutation'  	=> $this->input->post('salutation'),
			'dob'         	=> date("Y-m-d", strtotime($this->input->post('dob'))),
			'gender'        => $this->input->post('gender'),
			'patient_status'=> 'Active',
			'whatsapp_number'=> $this->input->post('contact_number'),
			'alt_number1'=> $this->input->post('alt_number1'),
			'alt_number2'=> $this->input->post('alt_number2'),
        );
		
		$pincode = $this->input->post('pincode');
		if (!empty($pincode) || $pincode === '0') { 
			$clients_new_fields_data["pincode"] = $pincode;
		}
		$patient_source_id = $this->input->post('patient_source_id');
		if ($patient_source_id !== null && $patient_source_id !== '') {
			$clients_new_fields_data['patient_source_id'] = $patient_source_id;
		}
    
        // Check if record exists in clients_new_fields
        $exists = $this->db->get_where(db_prefix() . 'clients_new_fields', ['userid' => $client_id])->row();
    
        if ($exists) {
            $this->db->where('userid', $client_id);
            $this->db->update(db_prefix() . 'clients_new_fields', $clients_new_fields_data);
        } else {
            $this->db->insert(db_prefix() . 'clients_new_fields', $clients_new_fields_data);
        }
		$this->db->from(db_prefix() . 'appointment');
        $this->db->like('DATE(appointment_date)', date('Y-m-d'));
        $count = $this->db->count_all_results();
		
		$branch_id = $this->current_branch_id; // or fetch from session/context if not already available

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
			$number = $branch_code.'-'.date('Ymd').'-'.$count + 1;
		}else{
			$number = $branch_code.'-'.date('Ymd').'-00001';
		}
		$formatted_number = str_pad($number, 4, '0', STR_PAD_LEFT);
		//$visit_id = "V-".$formatted_number;
		$visit_id = "";
		
		$attachment_path = null;

		if (!empty($_FILES['attachment']['name'])) {
			$this->load->library('upload');
			$upload_path = 'uploads/appointment_attachments/';

			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0755, true);
			}

			$_FILES['file']['name']     = $_FILES['attachment']['name'];
			$_FILES['file']['type']     = $_FILES['attachment']['type'];
			$_FILES['file']['tmp_name'] = $_FILES['attachment']['tmp_name'];
			$_FILES['file']['error']    = $_FILES['attachment']['error'];
			$_FILES['file']['size']     = $_FILES['attachment']['size'];

			$config['upload_path']   = $upload_path;
			$config['allowed_types'] = '*'; // or set to 'jpg|jpeg|png|pdf|doc|docx'
			$config['file_name']     = uniqid();

			$this->upload->initialize($config);

			if ($this->upload->do_upload('file')) {
				$upload_data = $this->upload->data();
				$attachment_path = $upload_path . $upload_data['file_name'];
			}
		}
		
		// Now assign to DB field
		$attachment = $attachment_path;
		
		
		$appointment_data = array(
			'userid'                => $client_id,
			'enquiry_type_id'       => $this->input->post('enquiry_type_id'),
			'appointment_type_id'   => $this->input->post('appointment_type_id'),
			'patient_response_id'   => $this->input->post('patient_response_id'),
			'patient_priority_id'   => $this->input->post('patient_priority_id'),
			'patient_source_id'     => $this->input->post('patient_source_id'),
			'attachment'            => $attachment,
			'slots_id'              => $this->input->post('slots_id'),
			'branch_id'             => $this->input->post('groupid'),
			'treatment_id'          => $this->input->post('treatment_id'),
			'consultation_fee_id'   => $this->input->post('item_select'),
			'enquiry_doctor_id'     => $this->input->post('assign_doctor_id'),
			'unit_doctor_id'        => $this->input->post('assign_doctor_id'),
			'remarks'               => $this->input->post('remarks'),
			'next_calling_date'     => date('Y-m-d', strtotime($this->input->post('next_calling_date'))),
			'appointment_date'      => date('Y-m-d H:i:s', strtotime($this->input->post('appointment_date'))),
			'created_by'            => get_staff_user_id(),
			'created_at'            => date('Y-m-d H:i:s'),
		);

		if ($this->input->post('assign_doctor_id')) {

			// ✅ Duplicate restriction check
			if (staff_can('multiple_appointments_restriction', 'customers')) {
				$appointment_date_only = date('Y-m-d', strtotime($this->input->post('appointment_date')));

				$this->db->where('userid', $client_id);
				$this->db->where('branch_id', $this->input->post('groupid'));
				$this->db->where('DATE(appointment_date) =', $appointment_date_only);
				
				$duplicate = $this->db->get(db_prefix() . 'appointment')->row();

				if ($duplicate) {
					// Stop insert
					$appointment_id = 0;
					return 3;
				}
			}

			// ✅ Insert only if no duplicate
			$this->db->insert(db_prefix() . 'appointment', $appointment_data);
			$insert_id = $this->db->insert_id();
			$appointment_id = $insert_id;

			if ($insert_id) {
				if ($this->input->post('due_amount') == 0) {
					// $this->confirm_booking($insert_id);
				}
			}

			$description = "new_appointment_added";
			$this->log_patient_activity($insert_id, $description);
		}else{
				$changed_fields = [];

				// Compare fields in clients table
				foreach ($data as $field => $new_value) {
					$old_value = $existing_client[$field] ?? null;
					if ($new_value != $old_value) {
						$changed_fields[$field] = [
							'old' => $old_value,
							'new' => $new_value
						];
					}
				}

				// Compare fields in clients_new_fields table
				foreach ($clients_new_fields_data as $field => $new_value) {
					$old_value = $existing_client_fields[$field] ?? null;
					if ($new_value != $old_value) {
						$changed_fields[$field] = [
							'old' => $old_value,
							'new' => $new_value
						];
					}
				}

				$new_treatment_id = $this->input->post('treatment_id');
				if ($new_treatment_id !== null) {
					$new_treatment_id = $new_treatment_id === '' ? null : $new_treatment_id;
					$appointment_row = null;
					$posted_appointment_id = $this->input->post('appointment_id');

					if (!empty($posted_appointment_id)) {
						$appointment_row = $this->db
							->select('appointment_id, treatment_id')
							->from(db_prefix() . 'appointment')
							->where('appointment_id', $posted_appointment_id)
							->get()
							->row_array();
					}

					if (!$appointment_row) {
						$appointment_row = $this->db
							->select('appointment_id, treatment_id')
							->from(db_prefix() . 'appointment')
							->where('userid', $client_id)
							->order_by('appointment_id', 'DESC')
							->limit(1)
							->get()
							->row_array();
					}

					if ($appointment_row) {
						$existing_treatment_id = $appointment_row['treatment_id'];
						$target_appointment_id = $appointment_row['appointment_id'];

						if ((string)($existing_treatment_id ?? '') !== (string)($new_treatment_id ?? '')) {
							$this->db->where('appointment_id', $target_appointment_id);
							$this->db->update(db_prefix() . 'appointment', ['treatment_id' => $new_treatment_id]);

							$changed_fields['appointment_treatment_id'] = [
								'old' => $existing_treatment_id,
								'new' => $new_treatment_id,
							];
						}
					}
				}

				 $description = "patient_data_updated";
				 $additional_data = json_encode($changed_fields);

				$this->log_patient_activity($client_id, $description, 0, $additional_data);

			}
            
			
           if($this->input->post('assign_doctor_id')){
			   
			   $this->load->model('invoices_model');
		
				$year = date('Y');

				$this->db->from('tblinvoices');
				$this->db->where('YEAR(date)', $year);
				$count = $this->db->count_all_results();

				$next_number = $count + 1;
				$invoice_number = 'INV-' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
				
				if($this->input->post('paying_amount')){
					$paying_amount = $this->input->post('paying_amount');
				}else{
					$paying_amount = 0;
				}

				$total = $this->input->post('item_select');
				
				if($paying_amount>0){
					$assigned_doctor = get_staff_full_name($this->input->post('assign_doctor_id'));
					$appointment_date = date('d-m-Y', strtotime($this->input->post('appointment_date')));
					$appointment_time = date('h:i A', strtotime($this->input->post('appointment_date'))); // 12-hour format with AM/PM
					$date_time = date('d-m-Y h:i A', strtotime($this->input->post('appointment_date')));   // Full date-time with AM/PM

					$branch_address = get_option('invoice_company_address');
					$communication_data = array(
					"assigned_doctor" => $assigned_doctor,
					"appointment_date" => $appointment_date,
					"appointment_time" => $appointment_time,
					"date_time" => $date_time,
					"branch_address" => $branch_address,
					);
					$this->patient_journey_log_event($client_id, 'paid_appointment', 'Paid Appointment Created', $communication_data);
					
					$data = array(
					"current_status" => "Paid Appointment"
					);
					$this->db->where(array("userid"=>$client_id));
					$this->db->update(db_prefix() . 'clients_new_fields', $data);
					
					
					$setting = "Paid Appointment";
					$get_id = $this->db->get_where(db_prefix() . 'leads_status', array("name" => $setting))->row();
					if ($get_id) {
						$update_lead['status'] = $get_id->id;
						$this->db->where(array("id"=>$this->input->post('leadid')));
						$this->db->update(db_prefix() . 'leads', $update_lead);
						
						$data = array(
						"leadid" => $this->input->post('leadid'),
						"userid" => $client_id,
						"status" => $get_id->id,
						"datetime" => date('Y-m-d H:i:s'),
						"remarks" => "Paid Appointment Created from patient module"
						);
						$this->db->insert(db_prefix() . 'lead_patient_journey', $data);
					}
				
				}else{
					$assigned_doctor = get_staff_full_name($this->input->post('assign_doctor_id'));
					$appointment_date = date('d-m-Y', strtotime($this->input->post('appointment_date')));
					$appointment_time = date('h:i A', strtotime($this->input->post('appointment_date'))); // 12-hour format with AM/PM
					$date_time = date('d-m-Y h:i A', strtotime($this->input->post('appointment_date')));   // Full date-time with AM/PM

					$branch_address = get_option('invoice_company_address');
					$communication_data = array(
					"assigned_doctor" => $assigned_doctor,
					"appointment_date" => $appointment_date,
					"appointment_time" => $appointment_time,
					"date_time" => $date_time,
					"branch_address" => $branch_address,
					);
					$this->patient_journey_log_event($client_id, 'on_appointment', 'Appointment Created', $communication_data);
					
					$setting = "On Appointment";
					$get_id = $this->db->get_where(db_prefix() . 'leads_status', array("name" => $setting))->row();
					if ($get_id) {
						$update_lead['status'] = $get_id->id;
						$this->db->where(array("id"=>$this->input->post('leadid')));
						$this->db->update(db_prefix() . 'leads', $update_lead);
						
						$data = array(
						"leadid" => $this->input->post('leadid'),
						"userid" => $client_id,
						"status" => $get_id->id,
						"datetime" => date('Y-m-d H:i:s'),
						"remarks" => "On Appointment Created from patient module"
						);
						$this->db->insert(db_prefix() . 'lead_patient_journey', $data);
					}
					
				}
				
				$invoice_data['formatted_number'] = $invoice_number;
				$invoice_data['number'] = $next_number;
				$invoice_data['clientid'] = $client_id;
				$invoice_data['show_shipping_on_invoice'] = 1;
				$invoice_data['date'] = date('Y-m-d');
				$invoice_data['duedate'] = date('Y-m-d');
				$invoice_data['currency'] = 1;
				$invoice_data['addedfrom'] = get_staff_user_id();
				$invoice_data['subtotal'] = $total;
				$invoice_data['total'] = $total;
				$invoice_data['prefix'] = "INV-";
				$invoice_data['number_format'] = 1;
				$branch_id = $this->get_client_branch($client_id);
				$invoice_data['branch_id'] = $branch_id;
				
				$due_amount = $total - $paying_amount;
				$invoice_data['allowed_payment_modes'] = 'a:1:{i:0;s:1:"1";}';
				
				$invoice_data['datecreated'] = date('Y-m-d H:i:s');
				
				$id = $this->invoices_model->add($invoice_data); 
				
				
				if($paying_amount>0 || $total == 0){
					if($due_amount == 0){
						$status = 2;
					}else{
						$status = 3;
					}
				}else{
					$status = 1;
				}
				
				$update = array(
				'allowed_payment_modes' => 'a:1:{i:0;s:1:"1";}',
				'status' => $status,
				);
				$this->db->where(array("id"=>$id));
				$this->db->update(db_prefix() . 'invoices', $update);
				
				$appointment_update = array(
					'invoice_id' => $id,
					);
					$this->db->where(array("appointment_id"=>$appointment_id));
					$this->db->update(db_prefix() . 'appointment', $appointment_update);
				
			   $itemable= array(
				"rel_id" => $id,
				"rel_type" => "invoice",
				"description" => "Consultation Fee",
				"qty" => 1,
				"item_order" => 1,
				"rate"=>$paying_amount + $due_amount
				);
				
				$this->db->insert(db_prefix() . 'itemable', $itemable);
				
				$branch_id = $this->get_client_branch($client_id);
				if($paying_amount>0){
				   $invoicepaymentrecords= array(
					"invoiceid" => $id,
					"amount" => $paying_amount,
					"paymentmode" => $this->input->post('paymentmode'),
					"date" => date('Y-m-d'),
					"daterecorded" => date('Y-m-d H:i:s'),
					"received_by" => get_staff_user_id(),
					"branch_id" => $branch_id,
					);
					
					$this->db->insert(db_prefix() . 'invoicepaymentrecords', $invoicepaymentrecords);
				}
			   
		   }
			
			
    
	
       return true;
    }
    

    public function get_appointment_data($id = '', $where = [])
    {
        $this->db->select('a.*, i.*, s.*, atype.appointment_type_name'); // Add more fields if needed
        $this->db->from(db_prefix() . 'appointment a');

        // Join master tables
        $this->db->join(db_prefix() . 'items i', 'i.id = a.treatment_id', 'left');
        $this->db->join(db_prefix() . 'appointment_type atype', 'atype.appointment_type_id = a.appointment_type_id', 'left');
        $this->db->join(db_prefix() . 'slots s', 's.slots_id = a.slots_id', 'left'); 

        // Where condition
        $this->db->where('a.userid', $id);

        // Optional additional where filter
        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db->get()->result_array();
    }

    public function get_customer_new_fields($id)
    {
        return $this->db->get_where(db_prefix() . 'clients_new_fields', array("userid"=>$id))->row();
    }

    public function get_patient_activity_log($id)
    {
        //$sorting = hooks()->apply_filters('lead_activity_log_default_sort', 'ASC');

        $this->db->where('patientid', $id);
       // $this->db->order_by('date', $sorting);

        return $this->db->get(db_prefix() . 'patient_activity_log')->result_array();
    }

    public function get_patient_prescription($userid, $casesheet_id = NULL)
	{
		$sorting = hooks()->apply_filters('lead_activity_log_default_sort', 'DESC');

		if ($casesheet_id) {
			$this->db->where('casesheet_id', $casesheet_id);
		}

		$this->db->where('userid', $userid);
		$this->db->order_by('created_datetime', $sorting);


		return $this->db->get(db_prefix() . 'patient_prescription')->result_array();
	}


    public function add_patient_call_log($data)
    {
		$client_id = $data['patientid'];
        $insert = [
            'patientid'         => $data['patientid'],
            'called_by'         => get_staff_user_id(),
            'criteria_id'          => $data['criteria_id'],
            'next_calling_date' => $data['next_calling_date'],
            'pharmacy_medicine_days' => $data['pharmacy_medicine_days'],
            'patient_took_medicine_days' => $data['patient_took_medicine_days'],
            'better_patient' => $data['better_patient'],
            'appointment_type_id'  => $data['appointment_type_id'],
            'appointment_date'  => $data['appointment_date'],
            'created_date'      => !empty($data['created_date']) ? $data['created_date'] : date('Y-m-d H:i:s'),
            'comments'          => $data['comments'],
        ];
		
		$total_amount        = $data['item_select'] ?? null;


        $this->db->insert(db_prefix() . 'patient_call_logs', $insert);
        $insert_id = $this->db->insert_id();
        $description = "new_patient_call_log_added";
        $this->log_patient_activity($data['patientid'], $description);
		
		
		if(!empty($data['next_calling_date'])){
			$this->load->model('misc_model');
			$reminder_data = array(
			"rel_type" => "customer",
			"rel_id" => $data['patientid'],
			"date" => $data['next_calling_date']." 10:00:00",
			"staff" => get_staff_user_id(),
			"notify_by_email" => 1,
			"description" => $data['comments']
			);
			$this->misc_model->add_reminder($reminder_data, $data['leads_id']);
			
		}
		
		$branch_address = get_option('invoice_company_address');
			$communication_data = array(
			"followup_date" => date("d-m-Y", strtotime($data['next_calling_date'])),
			"assigned_doctor"   => !empty($assigned_doctor)   ? $assigned_doctor   : 1,
			"appointment_date"  => !empty($appointment_date)  ? $appointment_date  : date('d-m-Y'),
			"appointment_time"  => !empty($appointment_time)  ? $appointment_time  : date('H:i A'),
			"date_time"         => !empty($date_time)         ? $date_time         : date('d-m-Y H:i A'),
			"branch_address"    => !empty($branch_address)    ? $branch_address    : 'Main Branch',
		);
		
		if($this->input->post('not_registered_patient_week1') == 'not_registered_patient_week1'){
			$check_status = $this->db->get_where(db_prefix() . 'leads_status', array("name"=>'Not Registered'))->row();
			
			
		}else{
			$check_status = $this->db->get_where(db_prefix() . 'leads_status', array("id"=>$data['patient_response_id']))->row();
		}
		
		if($check_status){
			$status_name = strtolower(str_replace(' ', '_', $check_status->name));
			$status_id = $check_status->id;
			
			
			if($data['doctor_id'] != NULL AND $data['appointment_date'] != NULL){
				
			
		$appointment_data = array(
			'userid'              => $client_id,
			'enquiry_type_id'     => $this->input->post('enquiry_type_id'),
			'appointment_type_id' => $this->input->post('appointment_type_id'),
			'patient_response_id' => $this->input->post('patient_response_id'),
			'patient_priority_id' => $this->input->post('patient_priority_id'),
			'patient_source_id'   => $this->input->post('patient_source_id'),
			'slots_id'            => $this->input->post('slots_id'),
			'branch_id'           => $this->input->post('groupid'),
			'treatment_id'        => $this->input->post('treatment_id'),
			'consultation_fee_id' => $this->input->post('consultation_fee_id'),
			'enquiry_doctor_id'   => $this->input->post('doctor_id'),
			'unit_doctor_id'      => $this->input->post('doctor_id'),
			'remarks'             => $this->input->post('remarks'),
			'next_calling_date'   => date('Y-m-d', strtotime($this->input->post('next_calling_date'))),
			'appointment_date'    => date('Y-m-d H:i:s', strtotime($this->input->post('appointment_date'))),
			'created_by'          => get_staff_user_id(),
			'created_at'          => date('Y-m-d H:i:s'),
		);

		if ($this->input->post('doctor_id')) {

			// ✅ Duplicate restriction check
			if (staff_can('multiple_appointments_restriction', 'customers')) {
				$appointment_date_only = date('Y-m-d', strtotime($this->input->post('appointment_date')));

				$this->db->where('userid', $client_id);
				$this->db->where('branch_id', $this->input->post('groupid'));
				$this->db->where('DATE(appointment_date) =', $appointment_date_only);

				$duplicate = $this->db->get(db_prefix() . 'appointment')->row();

				if ($duplicate) {
					$appointment_id = 0;
					return 3;
				}
			}

			// ✅ Insert only if not duplicate
			$this->db->insert(db_prefix() . 'appointment', $appointment_data);
			$insert_id = $this->db->insert_id();
			$appointment_id = $insert_id;

			if ($insert_id) {
				if ($this->input->post('due_amount') == 0) {
					// $this->confirm_booking($insert_id);
				}
			}

			$description = "new_appointment_added";
			$this->log_patient_activity($insert_id, $description);
		}else{
				 $description = "patient_data_updated";
				$this->log_patient_activity($client_id, $description);
			}
            
			
           if($this->input->post('doctor_id')){
			   
			   $this->load->model('invoices_model');
		
				$year = date('Y');

				$this->db->from('tblinvoices');
				$this->db->where('YEAR(date)', $year);
				$count = $this->db->count_all_results();

				$next_number = $count + 1;
				$invoice_number = 'INV-' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
				
				if($this->input->post('payment_amount')){
					$paying_amount = $this->input->post('payment_amount');
				}else{
					$paying_amount = 0;
				}

				$total = $this->input->post('item_select');
				
				if($paying_amount>0){
					$assigned_doctor = get_staff_full_name($this->input->post('assign_doctor_id'));
					$appointment_date = date('d-m-Y', strtotime($this->input->post('appointment_date')));
					$appointment_time = date('h:i A', strtotime($this->input->post('appointment_date'))); // 12-hour format with AM/PM
					$date_time = date('d-m-Y h:i A', strtotime($this->input->post('appointment_date')));   // Full date-time with AM/PM

					$branch_address = get_option('invoice_company_address');
					$communication_data = array(
					"assigned_doctor" => $assigned_doctor,
					"appointment_date" => $appointment_date,
					"appointment_time" => $appointment_time,
					"date_time" => $date_time,
					"branch_address" => $branch_address,
					);
					$this->patient_journey_log_event($client_id, 'paid_appointment', 'Paid Appointment Created', $communication_data);
					
					$data = array(
					"current_status" => "Paid Appointment"
					);
					$this->db->where(array("userid"=>$client_id));
					$this->db->update(db_prefix() . 'clients_new_fields', $data);
					
					
					$setting = "Paid Appointment";
					$get_id = $this->db->get_where(db_prefix() . 'leads_status', array("name" => $setting))->row();
					if ($get_id) {
						$update_lead['status'] = $get_id->id;
						$this->db->where(array("id"=>$this->input->post('leadid')));
						$this->db->update(db_prefix() . 'leads', $update_lead);
						
						$data = array(
						"leadid" => $this->input->post('leadid'),
						"userid" => $client_id,
						"status" => $get_id->id,
						"datetime" => date('Y-m-d H:i:s'),
						"remarks" => "Paid Appointment Created from patient module"
						);
						$this->db->insert(db_prefix() . 'lead_patient_journey', $data);
					}
				
				}else{
					$assigned_doctor = get_staff_full_name($this->input->post('assign_doctor_id'));
					$appointment_date = date('d-m-Y', strtotime($this->input->post('appointment_date')));
					$appointment_time = date('h:i A', strtotime($this->input->post('appointment_date'))); // 12-hour format with AM/PM
					$date_time = date('d-m-Y h:i A', strtotime($this->input->post('appointment_date')));   // Full date-time with AM/PM

					$branch_address = get_option('invoice_company_address');
					$communication_data = array(
					"assigned_doctor" => $assigned_doctor,
					"appointment_date" => $appointment_date,
					"appointment_time" => $appointment_time,
					"date_time" => $date_time,
					"branch_address" => $branch_address,
					);
					$this->patient_journey_log_event($client_id, 'on_appointment', 'Appointment Created', $communication_data);
					
					$setting = "On Appointment";
					$get_id = $this->db->get_where(db_prefix() . 'leads_status', array("name" => $setting))->row();
					if ($get_id) {
						$update_lead['status'] = $get_id->id;
						$this->db->where(array("id"=>$this->input->post('leadid')));
						$this->db->update(db_prefix() . 'leads', $update_lead);
						
						$data = array(
						"leadid" => $this->input->post('leadid'),
						"userid" => $client_id,
						"status" => $get_id->id,
						"datetime" => date('Y-m-d H:i:s'),
						"remarks" => "On Appointment Created from patient module"
						);
						$this->db->insert(db_prefix() . 'lead_patient_journey', $data);
					}
					
				}
				
				$invoice_data['formatted_number'] = $invoice_number;
				$invoice_data['number'] = $next_number;
				$invoice_data['clientid'] = $client_id;
				$invoice_data['show_shipping_on_invoice'] = 1;
				$invoice_data['date'] = date('Y-m-d');
				$invoice_data['duedate'] = date('Y-m-d');
				$invoice_data['currency'] = 1;
				$invoice_data['addedfrom'] = get_staff_user_id();
				$invoice_data['subtotal'] = $total;
				$invoice_data['total'] = $total;
				$invoice_data['prefix'] = "INV-";
				$invoice_data['number_format'] = 1;
				$branch_id = $this->get_client_branch($client_id);
				$invoice_data['branch_id'] = $branch_id;
				
				$due_amount = $total - $paying_amount;
				$invoice_data['allowed_payment_modes'] = 'a:1:{i:0;s:1:"1";}';
				
				$invoice_data['datecreated'] = date('Y-m-d H:i:s');
				
				$id = $this->invoices_model->add($invoice_data); 
				
				
				if($paying_amount>0 || $total == 0){
					if($due_amount == 0){
						$status = 2;
					}else{
						$status = 3;
					}
				}else{
					$status = 1;
				}
				
				$update = array(
				'allowed_payment_modes' => 'a:1:{i:0;s:1:"1";}',
				'status' => $status,
				);
				$this->db->where(array("id"=>$id));
				$this->db->update(db_prefix() . 'invoices', $update);
				
				$appointment_update = array(
					'invoice_id' => $id,
					'consultation_fee_id' => $total
					);
					$this->db->where(array("appointment_id"=>$appointment_id));
					$this->db->update(db_prefix() . 'appointment', $appointment_update);
				
			   $itemable= array(
				"rel_id" => $id,
				"rel_type" => "invoice",
				"description" => "Consultation Fee",
				"qty" => 1,
				"item_order" => 1,
				"rate"=>$paying_amount + $due_amount
				);
				
				$this->db->insert(db_prefix() . 'itemable', $itemable);
				
				$branch_id = $this->get_client_branch($client_id);
				
				if($paying_amount>0){
				   $invoicepaymentrecords= array(
					"invoiceid" => $id,
					"amount" => $paying_amount,
					"paymentmode" => $this->input->post('paymentmode'),
					"date" => date('Y-m-d'),
					"daterecorded" => date('Y-m-d H:i:s'),
					"received_by" => get_staff_user_id(),
					"branch_id"=>$branch_id
					);
					
					$this->db->insert(db_prefix() . 'invoicepaymentrecords', $invoicepaymentrecords);
				}
			   
		   }
				
			}
			
			$insertData = [
				'userid'   => !empty($client_id) ? $client_id : null,
				'status'   => !empty($status_id) ? $status_id : null,
				'datetime' => date('Y-m-d H:i:s'),
				'remarks'  => !empty($data['comments']) ? $data['comments'] : null,
			];

			$this->db->insert(db_prefix() . 'lead_patient_journey', $insertData);
			
		}
		
		$this->patient_journey_log_event($client_id, $status_name, $status_id, $communication_data);
        
		return $insert_id;
    }

    public function get_patient_call_logs($patientid)
    {
        $this->db->select('p.*, type.*, criteria.*'); // Add more fields if needed
        $this->db->from(db_prefix() . 'patient_call_logs p');
        $this->db->join(db_prefix() . 'appointment_type type', 'type.appointment_type_id = p.appointment_type_id', 'left');
       $this->db->join(db_prefix() . 'criteria criteria', 'criteria.criteria_id = p.criteria_id', 'left');
       
        // Optional additional where filter
        if (!empty($where)) {
            $this->db->where($where);
        }
        $this->db->where(array("patientid"=>$patientid));
        //$this->db->order_by('created_date', 'DESC');
        return $this->db->get()->result_array();
    }

    public function save_prescription($data, $patientid)
    {
        // Combine medicine details into a single string (prescription_data)
        $prescription_data = [];
        $i = 1;
        foreach ($data['medicine_name'] as $index => $medicine) {
			if($medicine != NULL){
				$prescription_data[] = $medicine . '; ' . $data['medicine_potency'][$index] . '; ' . $data['medicine_dose'][$index] . '; ' . $data['medicine_timing'][$index] . '; ' . $data['medicine_remarks'][$index];
			}
        }
        $prescription_data = implode('| ', $prescription_data);  // Join all the entries with a semicolon
		
		$this->db->order_by("id", "DESC");
		$get_casesheet = $this->db->get_where(db_prefix() . 'casesheet', array("userid"=>$patientid))->row();
		if($get_casesheet){
			$casesheet_id = $get_casesheet->id;
		}
		
        // Prepare data to save
        $prescription = [
            'userid'              => $patientid,
            'casesheet_id'      => $casesheet_id,
            'prescription_data'      => $prescription_data,
            'created_by'             => get_staff_user_id(),  // Assuming user is logged in
            'created_datetime'       => date('Y-m-d H:i:s'),
            'patient_prescription_status' => 1  // Set default status, change if needed
        ];
		if($prescription_data){
			$this->db->insert(db_prefix() . 'patient_prescription', $prescription);
		}
        $insert_id = $this->db->insert_id();
        $description = "new_prescription_added";
        $this->log_patient_activity($patientid, $description);
		$this->patient_journey_log_event($client_id, 'new_prescription_added', 'New Prescription Added');
        return $insert_id;
    }

    public function log_patient_activity($id, $description, $custom_activity = 0, $additional_data = '')
    {
        $log = [
            'date'            => date('Y-m-d H:i:s'),
            'description'     => $description,
            'patientid'       => $id,
            'staffid'         => get_staff_user_id(),
            'additional_data' => $additional_data,
            'custom_activity' => $custom_activity,
            'full_name'       => get_staff_full_name(get_staff_user_id()),
        ];
        $this->db->insert(db_prefix() . 'patient_activity_log', $log);

        return $this->db->insert_id();
    }

    public function get_invoices($invoice_id)
	{
		$this->db->select('inv.id as invoice_id, inv.*, c.*, pt.*'); // Avoid selecting item.description directly
		$this->db->from(db_prefix() . 'invoices inv');

		// Join master tables
		$this->db->join(db_prefix() . 'estimates e', 'e.invoiceid = inv.id', 'left');
		$this->db->join(db_prefix() . 'patient_treatment pt', 'pt.estimation_id = e.id', 'left');
		$this->db->join(db_prefix() . 'clients c', 'c.userid = inv.clientid', 'left');
		$this->db->join(db_prefix() . 'itemable item', 'item.rel_id = inv.id AND item.rel_type = "invoice"', 'left');

		// Where conditions
		$this->db->where('inv.id', $invoice_id);
		$this->db->where('item.description !=', "Consultation Fee");
		$this->db->where('item.description !=', "Appointment Fee");

		// Group by invoice ID to avoid duplicates
		$this->db->group_by('inv.id');

		// Optional additional where filter
		if (!empty($where)) {
			$this->db->where($where);
		}

		return $this->db->get()->result_array();
	}


    public function get_invoice_payments($invoice_id)
    {
        $this->db->select('inv.*, payment.*, mode.name as payment_mode, staff.firstname, staff.lastname'); // Add more fields if needed
        $this->db->from(db_prefix() . 'invoicepaymentrecords payment');

        // Join master tables
        $this->db->join(db_prefix() . 'invoices inv', 'inv.id = payment.invoiceid', 'left');
        $this->db->join(db_prefix() . 'payment_modes mode', 'mode.id = payment.paymentmode', 'left');
        $this->db->join(db_prefix() . 'staff staff', 'staff.staffid = payment.received_by', 'left');

        // Where condition
        $this->db->where('inv.id', $invoice_id);

        // Optional additional where filter
        if (!empty($where)) {
            $this->db->where($where);
        }
		$this->db->order_by("payment.id", "DESC");
        return $this->db->get()->result_array();
    }

    public function get_patient_package($invoice_id)
    {
        $this->db->select('inv.id, inv.date, inv.recurring, inv.recurring_type, itemable.qty, itemable.rate, itemable.description, inv.total as invoice_total'); // Add more fields if needed
        $this->db->from(db_prefix() . 'invoices inv');

        // Join master tables
        $this->db->join(db_prefix() . 'itemable itemable', 'itemable.rel_id = inv.id', 'left');
        $this->db->join(db_prefix() . 'items items', 'items.description = itemable.description', 'left');
        $this->db->join(db_prefix() . 'items_groups items_groups', 'items_groups.id = items.group_id', 'left');

        // Where condition
        $this->db->where('inv.id', $invoice_id);
        $this->db->where('itemable.rel_type', 'invoice');
        //$this->db->where('items_groups.name', 'Package');
        $this->db->where('itemable.description != ', 'Consultation Fee');

        // Optional additional where filter
        if (!empty($where)) {
            $this->db->where($where);
        }
		$this->db->group_by('inv.id');
        return $this->db->get()->result_array();
    }

    public function get_patient_by_contact($phonenumber, $where = [])
    {
        $this->db->select('c.*, co.*, ct.*, new.*'); // c = clients, co = countries, ct = contacts

        $this->db->from(db_prefix() . 'clients c');

        $this->db->join(db_prefix() . 'countries co', 'co.country_id = c.country', 'left');
        $this->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
        $this->db->join(db_prefix() . 'contacts ct', 'ct.userid = c.userid AND ct.is_primary = 1', 'left');

        if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
            $this->db->where($where);
        }

        if (is_numeric($phonenumber)) {
            $this->db->select('c.*');
            $this->db->where('c.phonenumber', $phonenumber);
            $client = $this->db->get()->row();

            if ($client && get_option('company_requires_vat_number_field') == 0) {
                $client->vat = null;
            }

            $GLOBALS['client'] = $client;

            return $client;
        }

        $this->db->order_by('c.company', 'asc');

        return $this->db->get()->row();
    }

    public function get_appointments()
    {
        $this->db->select('patients.*, appointments.*, new.*'); // Add more fields if needed
        $this->db->from(db_prefix() . 'appointment appointments');

        // Join master tables
        $this->db->join(db_prefix() . 'clients patients', 'patients.userid = appointments.userid', 'left');
        $this->db->join(db_prefix() . 'clients_new_fields new', 'patients.userid = new.userid', 'left');
       
        // Optional additional where filter
        if (!empty($where)) {
            $this->db->where($where);
        }
        $this->db->order_by("appointments.appointment_id", "DESC");
        return $this->db->get()->result_array();
    }

    public function save_casesheet($patientid)
	{
		// Handle file uploads
		$uploaded_files = [];
		if (!empty($_FILES['documents']['name'][0])) {
			$this->load->library('upload');
			$upload_path = 'uploads/clinical_docs/';
			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0755, true);
			}

			$filesCount = count($_FILES['documents']['name']);
			for ($i = 0; $i < $filesCount; $i++) {
				$_FILES['file']['name']     = $_FILES['documents']['name'][$i];
				$_FILES['file']['type']     = $_FILES['documents']['type'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['documents']['tmp_name'][$i];
				$_FILES['file']['error']    = $_FILES['documents']['error'][$i];
				$_FILES['file']['size']     = $_FILES['documents']['size'][$i];

				$config['upload_path']   = $upload_path;
				$config['allowed_types'] = '*';
				$config['file_name']     = uniqid();

				$this->upload->initialize($config);
				if ($this->upload->do_upload('file')) {
					$upload_data = $this->upload->data();
					$uploaded_files[] = $upload_path . $upload_data['file_name'];
				}
			}
		}
		
		
		
		// Unified insert data for tblcasesheet
		$data = [
			'userid'                   => $patientid,
			'presenting_complaints'   => $this->input->post('presenting_complaints'),
			'complaint'   => $this->input->post('complaint'),

			// Personal History
			'appetite'                => $this->input->post('appetite'),
			'thirst'                => $this->input->post('thirst'),
			'desires'                 => $this->input->post('desires'),
			'aversion'                => $this->input->post('aversion'),
			'tongue'                  => $this->input->post('tongue'),
			'urine'                   => $this->input->post('urine'),
			'bowels'                  => $this->input->post('bowels'),
			'sweat'                   => $this->input->post('sweat'),
			'sleep'                   => $this->input->post('sleep'),
			'sun_headache'            => $this->input->post('sun_headache'),
			'thermals'                => $this->input->post('thermals'),
			'habits'                  => $this->input->post('habits'),
			'addiction'               => $this->input->post('addiction'),
			'side'                    => $this->input->post('side'),
			'dreams'                  => $this->input->post('dreams'),
			'diabetes'                => $this->input->post('diabetes'),
			'thyroid'                 => $this->input->post('thyroid'),
			'hypertension'            => $this->input->post('hypertension'),
			'hyperlipidemia'          => $this->input->post('hyperlipidemia'),
			'menstrual_obstetric_history' => $this->input->post('menstrual_obstetric_history'),
			'family_history'          => $this->input->post('family_history'),
			'past_treatment_history'  => $this->input->post('past_treatment_history'),
			'staffid'                 => get_staff_user_id(),

			// General Examination
			'bp'                      => $this->input->post('bp'),
			'pulse'                   => $this->input->post('pulse'),
			'weight'                  => $this->input->post('weight'),
			'height'                  => $this->input->post('height'),
			'temperature'             => $this->input->post('temperature'),
			'bmi'                     => $this->input->post('bmi'),
			'mental_generals'         => $this->input->post('mental_generals'),
			'pg'                      => $this->input->post('pg'),
			'particulars'             => $this->input->post('particulars'),
			'miasmatic_diagnosis'     => $this->input->post('miasmatic_diagnosis'),
			'analysis_evaluation'     => $this->input->post('analysis_evaluation'),
			'reportorial_result'      => $this->input->post('reportorial_result'),
			'management'              => $this->input->post('management'),
			'diet'                    => $this->input->post('diet'),
			'exercise'                => $this->input->post('exercise'),
			'critical'                => $this->input->post('critical'),
			'level_of_assent'         => $this->input->post('level_of_assent'),
			'dos_and_donts'           => $this->input->post('dos_and_donts'),
			'level_of_assurance'      => $this->input->post('level_of_assurance'),
			'criteria_future_plan_rx' => $this->input->post('criteria_future_plan_rx'),
			'nutrition'               => $this->input->post('nutrition'),

			// Clinical Observation
			'progress'                => $this->input->post('progress'),
			'clinical_observation'    => $this->input->post('clinical_observation'),
			'suggested_duration'      => $this->input->post('suggested_duration'),
			'documents'               => !empty($uploaded_files) ? json_encode($uploaded_files) : null,
			'doctor_medicine_days'           => $this->input->post('medicine_days'),
			'medicine_days'           => $this->input->post('medicine_days'),
			'followup_date'           => $this->input->post('followup_date'),
			'patient_status'          => $this->input->post('patient_status'),

			// Mind Section
			'mind'                    => $this->input->post('mind'),
			
			'date'                    => date('Y-m-d'),
			'created_at'              => date('Y-m-d H:i:s'),
		];
		$this->db->insert(db_prefix() . 'casesheet', $data);
		$insert_id = $this->db->insert_id();
		$casesheet_id = $insert_id;
		$this->patient_journey_log_event($client_id, 'casesheet_created', 'CaseSheet ID:'.$casesheet_id);
		$treatment_type  = $this->input->post('treatment_type');
		$treatment_type_id  = $this->input->post('description');
		$duration_value  = $this->input->post('duration_value');
		$suggested_diagnostics_id  = $this->input->post('suggested_diagnostics_id');
		
		$improvement          = $this->input->post('improvement');
		$treatment_status          = $this->input->post('treatment_status');
		
		//$count = count($treatment_type); // Assumes all arrays are the same length
		//print_r($description);
		//exit();
		//for ($i = 0; $i < $count; $i++) {
			
			$check = $this->db->get_where(db_prefix() . 'patient_treatment', array('userid' => $patientid, 'treatment_type_id' => $treatment_type_id))->row();
					
			if($check){
				$id = $check->id;
				
				$treatment_data = array(
					'duration_value' => $duration_value,
					'suggested_diagnostics_id' => $suggested_diagnostics_id,
					'improvement'    => $improvement,
					'userid'         => $patientid,
					'treatment_status'=>$treatment_status,
					'created_at'     => date("Y-m-d H:i:s"),
				);
				$created_at = $check->created_at;
				if (!empty($created_at) && isset($duration_value)) {
					$base_date = new DateTime($created_at);
					$interval = new DateInterval('P' . (int)$duration_value . 'M');
					$base_date->add($interval);
					//$treatment_data['treatment_followup_date'] = $base_date->format('Y-m-d');
					$treatment_data = array(
					'casesheet_id' => $casesheet_id,
					'treatment_followup_date' => $base_date->format('Y-m-d'),
					);
					
				}
				// Insert into DB
				$this->db->where(array("id"=>$id));
				$this->db->update(db_prefix() . 'patient_treatment', $treatment_data);
			}else{
				
				$treatment_followup_date = date('Y-m-d', strtotime("+$duration_value months"));
				$treatment_data = array(
				'casesheet_id' => $casesheet_id,
				'treatment_type_id' => $treatment_type_id,
				'suggested_diagnostics_id' => $suggested_diagnostics_id,
				'duration_value' => $duration_value,
				'improvement'    => $improvement,
				'userid'         => $patientid,
				'treatment_status'=>$treatment_status,
				'created_at'     => date("Y-m-d H:i:s"),
				'treatment_followup_date' => $treatment_followup_date,
				);
				// Insert into DB
				if($treatment_type_id){
					$this->db->insert(db_prefix() . 'patient_treatment', $treatment_data);
				}
				
			}
			
		//}
		
		/* if($count>0){
			//$this->register_patient($patientid, NULL, $treatment_followup_date);
		} */
		// Get POST data
		$data = $this->input->post();

		// Get POST data
		$data = $this->input->post();

		// Combine medicine details into a single string (prescription_data)
		$prescription_data = [];
		$i = 1;

		foreach ($data['prescription_medicine_name'] as $index => $medicine_name) {
			$name     = $medicine_name;
			$potency  = $data['prescription_medicine_potency'][$index] ?? '';
			$dose     = $data['prescription_medicine_dose'][$index] ?? '';
			$timing   = $data['prescription_medicine_timings'][$index] ?? '';
			$remarks  = $data['prescription_medicine_remarks'][$index] ?? '';
			if($medicine_name){
				$prescription_data[] = $name . '; ' . $potency . '; ' . $dose . '; ' . $timing . '; ' . $remarks;
			}
			
		}
		foreach ($data['medicine_name'] as $index => $medicine_name) {
			$name     = $medicine_name;
			$potency  = $data['medicine_potency'][$index] ?? '';
			$dose     = $data['medicine_dose'][$index] ?? '';
			$timing   = $data['medicine_timing'][$index] ?? '';
			$remarks  = $data['medicine_remarks'][$index] ?? '';
			if($medicine_name){
				$prescription_data[] = $name . '; ' . $potency . '; ' . $dose . '; ' . $timing . '; ' . $remarks;
			}
			
		}

		$prescription_data_string = implode('| ', $prescription_data);
		
		if($prescription_data_string){
			// Prepare data for insertion
			$prescription = [
				'userid'                     => $patientid, // or $patientid if set separately
				'casesheet_id'         => $casesheet_id,
				'prescription_data'         => $prescription_data_string,
				'created_by'                => get_staff_user_id(),
				'created_datetime'          => date('Y-m-d H:i:s'),
				'patient_prescription_status' => 1
			];
			
			$check = $this->db->get_where(db_prefix() . 'patient_prescription', array("casesheet_id"=>$casesheet_id, "userid"=>$patientid))->row();
		
			if($check){
				$patient_prescription_id = $check->patient_prescription_id;
				$this->db->where(array("patient_prescription_id"=>$patient_prescription_id));
				$this->db->update(db_prefix() . 'patient_prescription', $prescription);
				//$this->log_patient_activity($patientid, "prescription_updated");
			}else{
				// Insert into database
				$this->db->insert(db_prefix() . 'patient_prescription', $prescription);
				
				$patient_prescription_id = $this->db->insert_id();
				$this->log_patient_activity($patientid, "prescription_added", 0, "Prescription ID:$patient_prescription_id");
			}
			
		}
		
		
		$this->db->where('userid', $patientid);
		$this->db->where('visit_status', 1);
		$this->db->where('DATE(appointment_date) = CURDATE()', null, false); // Only today's date
		$this->db->where('appointment_date IS NOT NULL', null, false); // Ensure date exists
		$appointment = $this->db->get(db_prefix() . 'appointment')->row();
		
		if($appointment){
			$id = $appointment->appointment_id;
			$this->db->where('appointment_id', $id);
			$this->db->update(db_prefix() . 'appointment', ["consulted_date"=>date('Y-m-d H:i:s'), "consultation_duration" => $this->input->post('consultation_duration')]);
			
			$status_name = "Only Consulted";
			$check_status = $this->db->get_where(db_prefix() . 'leads_status', array("name"=>$status_name))->row();
			if($check_status){
				$status_id = $check_status->id;
					
				$add_lead_patient_status = array(
					"userid" => $patientid,
					"status" => $status_id,
					"datetime" => date('Y-m-d H:i:s')
				);
				$this->add_lead_patient_status($add_lead_patient_status);
			}
		}

		return $casesheet_id;
	}
	
	public function update_casesheet()
	{	
		// Handle file uploads
		$uploaded_files = [];
		if (!empty($_FILES['documents']['name'][0])) {
			$this->load->library('upload');
			$upload_path = 'uploads/clinical_docs/';
			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0755, true);
			}
			if($_FILES['documents']['name']){
				$filesCount = count($_FILES['documents']['name']);
			}else{
				$filesCount = 0;
			}
			
			for ($i = 0; $i < $filesCount; $i++) {
				$_FILES['file']['name']     = $_FILES['documents']['name'][$i];
				$_FILES['file']['type']     = $_FILES['documents']['type'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['documents']['tmp_name'][$i];
				$_FILES['file']['error']    = $_FILES['documents']['error'][$i];
				$_FILES['file']['size']     = $_FILES['documents']['size'][$i];

				$config['upload_path']   = $upload_path;
				$config['allowed_types'] = '*';
				$config['file_name']     = uniqid();

				$this->upload->initialize($config);
				if ($this->upload->do_upload('file')) {
					$upload_data = $this->upload->data();
					$uploaded_files[] = $upload_path . $upload_data['file_name'];
				}
			}
		}

		// Get existing files from DB if any
		$casesheet_id = $this->input->post('casesheet_id');
		$patientid = $this->input->post('patientid');
		
		$this->db->where('id', $casesheet_id);
		$existing_row = $this->db->get(db_prefix() . 'casesheet')->row();

		$existing_files = [];
		if (!empty($existing_row->documents)) {
			$existing_files = json_decode($existing_row->documents, true);
		}

		// Merge existing files with newly uploaded ones
		$all_files = array_merge($existing_files, $uploaded_files);

		// Prepare base data array
		$data = [
			'userid'                   => $patientid,
			'presenting_complaints'   => $this->input->post('presenting_complaints'),
			'complaint'   => $this->input->post('complaint'),
			'appetite'                => $this->input->post('appetite'),
			'thirst'                => $this->input->post('thirst'),
			'desires'                 => $this->input->post('desires'),
			'aversion'                => $this->input->post('aversion'),
			'tongue'                  => $this->input->post('tongue'),
			'urine'                   => $this->input->post('urine'),
			'bowels'                  => $this->input->post('bowels'),
			'sweat'                   => $this->input->post('sweat'),
			'sleep'                   => $this->input->post('sleep'),
			'sun_headache'            => $this->input->post('sun_headache'),
			'thermals'                => $this->input->post('thermals'),
			'habits'                  => $this->input->post('habits'),
			'addiction'               => $this->input->post('addiction'),
			'side'                    => $this->input->post('side'),
			'dreams'                  => $this->input->post('dreams'),
			'diabetes'                => $this->input->post('diabetes'),
			'thyroid'                 => $this->input->post('thyroid'),
			'hypertension'            => $this->input->post('hypertension'),
			'hyperlipidemia'          => $this->input->post('hyperlipidemia'),
			'menstrual_obstetric_history' => $this->input->post('menstrual_obstetric_history'),
			'family_history'          => $this->input->post('family_history'),
			'past_treatment_history'  => $this->input->post('past_treatment_history'),
			'bp'                      => $this->input->post('bp'),
			'pulse'                   => $this->input->post('pulse'),
			'weight'                  => $this->input->post('weight'),
			'height'                  => $this->input->post('height'),
			'temperature'             => $this->input->post('temperature'),
			'bmi'                     => $this->input->post('bmi'),
			'mental_generals'         => $this->input->post('mental_generals'),
			'pg'                      => $this->input->post('pg'),
			'particulars'             => $this->input->post('particulars'),
			'miasmatic_diagnosis'     => $this->input->post('miasmatic_diagnosis'),
			'analysis_evaluation'     => $this->input->post('analysis_evaluation'),
			'reportorial_result'      => $this->input->post('reportorial_result'),
			'management'              => $this->input->post('management'),
			'diet'                    => $this->input->post('diet'),
			'exercise'                => $this->input->post('exercise'),
			'critical'                => $this->input->post('critical'),
			'level_of_assent'         => $this->input->post('level_of_assent'),
			'dos_and_donts'           => $this->input->post('dos_and_donts'),
			'level_of_assurance'      => $this->input->post('level_of_assurance'),
			'criteria_future_plan_rx' => $this->input->post('criteria_future_plan_rx'),
			'nutrition'               => $this->input->post('nutrition'),
			'progress'                => $this->input->post('progress'),
			'clinical_observation'    => $this->input->post('clinical_observation'),
			'suggested_duration'      => $this->input->post('suggested_duration'),
			'doctor_medicine_days'    => $this->input->post('medicine_days'),
			'medicine_days'           => $this->input->post('medicine_days'),
			'followup_date'           => $this->input->post('followup_date'),
			'patient_status'          => $this->input->post('patient_status'),
			'mind'                    => $this->input->post('mind'),
		];

		
		// If there's anything to save, update the documents field
		if (!empty($all_files)) {
			$data['documents'] = json_encode($all_files);
		}
		$this->db->where('id', $casesheet_id);
		$this->db->update(db_prefix() . 'casesheet', $data);

		$treatment_type  = $this->input->post('treatment_type');
		$duration_value  = $this->input->post('duration_value');
		$suggested_diagnostics_id  = $this->input->post('suggested_diagnostics_id');
		$improvement     = $this->input->post('improvement');
		if($treatment_type){
			$count = count($treatment_type);
		}else{
			$count = 0;
		}
		
		for ($i = 0; $i < $count; $i++) {
			
			$check = $this->db->get_where(db_prefix() . 'patient_treatment', array('userid' => $patientid, 'treatment_type_id' => $treatment_type[$i]))->row();
				
			if($check){
				$id = $check->id;
				$treatment_data = array(
					'duration_value' => $duration_value[$i],
					'improvement'    => $improvement[$i],
					'suggested_diagnostics_id'    => $suggested_diagnostics_id[$i],
					'userid'         => $patientid,
					'casesheet_id' => $casesheet_id,
					'treatment_status'=>'treatment_started',
					//'created_at'     => date("Y-m-d H:i:s"),
				);
				$created_at = $check->created_at;
				$duration_value = $duration_value[$i];
				if (!empty($created_at) && isset($duration_value[$i])) {
					$base_date = new DateTime($created_at);
					$interval = new DateInterval('P' . (int)$duration_value[$i] . 'M');
					$base_date->add($interval);
					$treatment_data['treatment_followup_date'] = $base_date->format('Y-m-d');
					$treatment_followup_date = $treatment_data['treatment_followup_date'];
				}
				// Insert into DB
				$this->db->where(array("id"=>$id));
				$this->db->update(db_prefix() . 'patient_treatment', $treatment_data);
			}else{
				$treatment_followup_date = date('Y-m-d', strtotime("+$duration_value[$i] months"));
				$treatment_data = array(
				'casesheet_id' => $casesheet_id,
				'treatment_type_id' => $treatment_type[$i],
				'duration_value' => $duration_value[$i],
				'improvement'    => $improvement[$i],
				'suggested_diagnostics_id'    => $suggested_diagnostics_id[$i],
				'userid'         => $patientid,
				'treatment_status'=>'treatment_started',
				'created_at'     => date("Y-m-d H:i:s"),
				'treatment_followup_date' => $treatment_followup_date,
				);
				if($treatment_type[$i]>0){
					$this->db->insert(db_prefix() . 'patient_treatment', $treatment_data);
				}
			}
			
		}
		if($count>0){
			//$this->register_patient($patientid, 1, $treatment_followup_date);
		}
		$patient_treatment_ids = $this->input->post('patient_treatment_id');
		
		foreach($patient_treatment_ids as $patient_treatment_id){
			$data = array(
			"duration_value" => $this->input->post('duration_value_'.$patient_treatment_id),
			"improvement" => $this->input->post('improvement_'.$patient_treatment_id),
			"suggested_diagnostics_id" => $this->input->post('suggested_diagnostics_id_'.$patient_treatment_id),
			"treatment_status" => $this->input->post('treatment_status_'.$patient_treatment_id),
			);
			$check = $this->db->get_where(db_prefix() . 'patient_treatment', array('id' => $patient_treatment_id))->row();
			$created_at = $check->created_at;
			$duration_value = $this->input->post('duration_value_'.$patient_treatment_id);
			$base_date = new DateTime($created_at);
			$interval = new DateInterval('P' . (int)$this->input->post('duration_value_'.$patient_treatment_id) . 'M');
			$base_date->add($interval);
			$data['treatment_followup_date'] = $base_date->format('Y-m-d');
			
			
			$this->db->where('id', $patient_treatment_id);
			$this->db->update(db_prefix() . 'patient_treatment', $data);
		}
		
		
		// Get POST data
		$data = $this->input->post();

		// Combine medicine details into a single string (prescription_data)
		$prescription_data = [];
		$i = 1;

		foreach ($data['prescription_medicine_name'] as $index => $medicine_name) {
			$name     = $medicine_name;
			$potency  = $data['prescription_medicine_potency'][$index] ?? '';
			$dose     = $data['prescription_medicine_dose'][$index] ?? '';
			$timing   = $data['prescription_medicine_timings'][$index] ?? '';
			$remarks  = $data['prescription_medicine_remarks'][$index] ?? '';
			if($medicine_name){
				$prescription_data[] = $name . '; ' . $potency . '; ' . $dose . '; ' . $timing . '; ' . $remarks;
			}
			
		}
		foreach ($data['medicine_name'] as $index => $medicine_name) {
			$name     = $medicine_name;
			$potency  = $data['medicine_potency'][$index] ?? '';
			$dose     = $data['medicine_dose'][$index] ?? '';
			$timing   = $data['medicine_timing'][$index] ?? '';
			$remarks  = $data['medicine_remarks'][$index] ?? '';
			if($medicine_name){
				$prescription_data[] = $name . '; ' . $potency . '; ' . $dose . '; ' . $timing . '; ' . $remarks;
			}
			
		}
		$prescription_data_string = implode('| ', $prescription_data);
		
		if($prescription_data_string){
			// Prepare data for insertion
			$prescription = [
				'userid'                     => $patientid, // or $patientid if set separately
				'casesheet_id'         => $casesheet_id,
				'prescription_data'         => $prescription_data_string,
				'created_by'                => get_staff_user_id(),
				'created_datetime'          => date('Y-m-d H:i:s'),
				'patient_prescription_status' => 1
			];
			
			$check = $this->db->get_where(db_prefix() . 'patient_prescription', array("casesheet_id"=>$casesheet_id, "userid"=>$patientid))->row();
		
			if($check){
				$patient_prescription_id = $check->patient_prescription_id;
				$this->db->where(array("patient_prescription_id"=>$patient_prescription_id));
				$this->db->update(db_prefix() . 'patient_prescription', $prescription);
				//$this->log_patient_activity($patientid, "prescription_updated");
			}else{
				// Insert into database
				$this->db->insert(db_prefix() . 'patient_prescription', $prescription);
				
				$patient_prescription_id = $this->db->insert_id();
				$this->log_patient_activity($patientid, "prescription_added", 0, "Prescription ID:$patient_prescription_id");
			}
			
		}
		
		
		$this->db->where('userid', $patientid);
		$this->db->where('visit_status', 1);
		$this->db->where('DATE(appointment_date) = CURDATE()', null, false); // Only today's date
		$this->db->where('appointment_date IS NOT NULL', null, false); // Ensure date exists
		$appointment = $this->db->get(db_prefix() . 'appointment')->row();
		
		if($appointment){
			$id = $appointment->appointment_id;
			$this->db->where('appointment_id', $id);
			$this->db->update(db_prefix() . 'appointment', ["consulted_date"=>date('Y-m-d H:i:s')]);
			
			$status_name = "Only Consulted";
			$check_status = $this->db->get_where(db_prefix() . 'leads_status', array("name"=>$status_name))->row();
			if($check_status){
				$status_id = $check_status->id;
					
				$add_lead_patient_status = array(
					"userid" => $patientid,
					"status" => $status_id,
					"datetime" => date('Y-m-d H:i:s')
				);
				$this->add_lead_patient_status($add_lead_patient_status);
			}
		}
		
		
		//$this->log_patient_activity($patientid, "casesheet_updated");

		return true;
	}


    public function get_casesheet($id){
        $this->db->select('c.*, t.improvement, t.improvement, t.duration_value, t.treatment_status, t.treatment_status, t.treatment_type_id, treatment.description, t.suggested_diagnostics_id');
        $this->db->from(db_prefix() . 'casesheet c');
        $this->db->join(db_prefix() . 'patient_treatment t', "t.casesheet_id = c.id", 'left');
        
        $this->db->join(db_prefix() . 'items treatment', 'treatment.id = t.treatment_type_id', 'left');
		
        $this->db->where(array("c.userid"=>$id));
		
		$this->db->order_by("c.id", "DESC");
        return $this->db->get()->result_array();
    }
    public function get_casesheet_by_id($id){
        $this->db->select('c.*');
        $this->db->from(db_prefix() . 'casesheet c');
        /* $this->db->join(db_prefix() . 'general_examination ge', 'ge.personal_history_id = history.id', 'left');
        $this->db->join(db_prefix() . 'preliminary_data pd', 'pd.personal_history_id = history.id', 'left');
        $this->db->join(db_prefix() . 'clinical_observation co', 'co.personal_history_id = history.id', 'left'); */
        $this->db->where(array("c.id"=>$id));
        return $this->db->get()->result_array();
    }
    public function prev_treatments($patientid){
        $this->db->select('pt.*, i.description, suggested_diagnostics.suggested_diagnostics_name');
        $this->db->from(db_prefix() . 'patient_treatment pt');
        $this->db->join(db_prefix() . 'items i', 'i.id = pt.treatment_type_id', 'left');
		$this->db->join(db_prefix() . 'suggested_diagnostics suggested_diagnostics', 'suggested_diagnostics.suggested_diagnostics_id = pt.suggested_diagnostics_id', 'left');
        /* $this->db->join(db_prefix() . 'preliminary_data pd', 'pd.personal_history_id = history.id', 'left');
        $this->db->join(db_prefix() . 'clinical_observation co', 'co.personal_history_id = history.id', 'left'); */
        $this->db->where(array("pt.userid"=>$patientid));
        return $this->db->get()->result_array();
    }
    public function prev_documents($patientid){
        $this->db->select('c.documents');
        $this->db->from(db_prefix() . 'casesheet c');
        $this->db->where(array("c.userid"=>$patientid));
        return $this->db->get()->result_array();
    }

    public function search_by_contact_number($contact)
	{
		// Search in clients
		$this->db->select('userid as id, phonenumber, company');
		$this->db->like('phonenumber', $contact);
		//$this->db->limit(10);
		$clients = $this->db->get(db_prefix() . 'clients')->result_array();

		foreach ($clients as &$c) {
			$c['source'] = 'patient';
			$c['type'] = 'Patient';
		}

		// Search in leads
		$this->db->select('id, phonenumber, name as company');
		$this->db->like('phonenumber', $contact);
		$this->db->where('date_converted IS NULL');
		//$this->db->limit(10);
		$leads = $this->db->get(db_prefix() . 'leads')->result_array();

		foreach ($leads as &$l) {
			$l['source'] = 'lead';
			$l['type'] = 'Lead';
		}

		// Merge both and return a max of 10 results
		$results = array_merge($clients, $leads);
		return array_slice($results, 0, 10);
	}
	
	 public function get_lead($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'leads.name, ' . db_prefix() . 'leads.id,' . db_prefix() . 'leads_status.name as status_name,' . db_prefix() . 'leads_sources.name as source_name');
        $this->db->join(db_prefix() . 'leads_status', db_prefix() . 'leads_status.id=' . db_prefix() . 'leads.status', 'left');
		$this->db->join(db_prefix() . 'city city', 'city.city_id = ' . db_prefix() . 'leads.city', 'left');
        $this->db->join(db_prefix() . 'leads_sources', db_prefix() . 'leads_sources.id=' . db_prefix() . 'leads.source', 'left');

        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'leads.id', $id);
            $lead = $this->db->get(db_prefix() . 'leads')->row();
            if ($lead) {
                if ($lead->from_form_id != 0) {
                    $lead->form_data = $this->get_form([
                        'id' => $lead->from_form_id,
                    ]);
                }
                //$lead->attachments = $this->get_lead_attachments($id);
                //$lead->public_url  = leads_public_url($id);
            }

            return $lead;
        }

        return $this->db->get(db_prefix() . 'leads')->result_array();
    }
	
	public function patient_inactive_fields(){
		$res =  $this->db->get_where(db_prefix() . 'master_settings', array("title"=>'patient_inactive_fields'))->row();
		if($res){
			return $res->options;
		}else{
			return "";
		}
	}
	
	public function get_logged_in_staff_branch_id()
    {
        $staff_id = get_staff_user_id();
        if ($staff_id) {
            $this->db->select('branch_id');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where('staffid', $staff_id);
            $row = $this->db->get()->row();
            return $row ? $row->branch_id : null;
        }
        return null;
    }
	
	public function get_patient_package_details($client_id)
	{
		$invoice_package_details = [];

		// Get all invoices for the client
		$invoices = $this->db->get_where(db_prefix() . 'invoices', ['clientid' => $client_id])->result_array();

		foreach ($invoices as $invoice) {
			$invoice_id = $invoice['id'];

			// Select package items under this invoice
			$this->db->select('inv.id as invoice_id, itemable.description, itemable.qty, itemable.rate, inv.total');
			$this->db->from(db_prefix() . 'invoices inv');
			$this->db->join(db_prefix() . 'itemable itemable', 'itemable.rel_id = inv.id', 'left');
			$this->db->join(db_prefix() . 'items items', 'items.description = itemable.description', 'left');
			$this->db->join(db_prefix() . 'items_groups items_groups', 'items_groups.id = items.group_id', 'left');
			$this->db->where('inv.id', $invoice_id);
			$this->db->where('itemable.rel_type', 'invoice');
			$this->db->where('itemable.description !=', 'Consultation Fee');

			$packages = $this->db->get()->result_array();
		
			foreach ($packages as $package) {
				$total = $package['total'];
				$paid = 0;

				// Get total paid against this invoice
				$payments = $this->db->select_sum('amount')
					->get_where(db_prefix() . 'invoicepaymentrecords', [
						'invoiceid' => $package['invoice_id']
					])->row();

				if ($payments && isset($payments->amount)) {
					$paid = $payments->amount;
				}

				$invoice_package_details[] = [
					'invoice_id'   => $package['invoice_id'],
					'description'  => $package['description'],
					'qty'          => $package['qty'],
					'rate'         => $package['rate'],
					'total'        => $total,
					'paid'         => $paid,
					'due'          => $total - $paid,
				];
			}
		}

		return $invoice_package_details;
	}
	
	public function ownership_details($type, $doctor_id = NULL, $from_date = NULL, $to_date = NULL)
	{
		if ($type == "visit") {
			$visit = 1;
			$this->db->where(array("visit_status" => $visit));
		}
		
		$this->db->select('patients.*, appointments.*, new.*, treatment.treatment_name');
		$this->db->from(db_prefix() . 'appointment appointments');

		// Join master tables
		$this->db->join(db_prefix() . 'clients patients', 'patients.userid = appointments.userid', 'left');
		$this->db->join(db_prefix() . 'clients_new_fields new', 'patients.userid = new.userid', 'left');
		$this->db->join(db_prefix() . 'treatment treatment', 'treatment.treatment_id = appointments.treatment_id', 'left');
		
		// Add date filtering for appointments if dates are provided
		if (!empty($from_date)) {
			$this->db->where("DATE(appointments.appointment_date) >=", to_sql_date($from_date));
		}
		if (!empty($to_date)) {
			$this->db->where("DATE(appointments.appointment_date) <=", to_sql_date($to_date));
		}
		
		// Optional additional where filter
		if (!empty($where)) {
			$this->db->where($where);
		}
		
		if ($doctor_id) {
			$this->db->where(array("enquiry_doctor_id" => $doctor_id));
		}
		
		$this->db->order_by("appointments.appointment_id", "DESC");
		return $this->db->get()->result_array();
	}

	public function get_package_html($packages)
	{
	  if (empty($packages)) return 'No packages found.';
	  $html = '<table class="table table-bordered small">';
	  $html .= '<thead><tr>
				  <th>Invoice</th><th>Description</th><th>Qty</th>
				  <th>Rate</th><th>Total</th><th>Paid</th><th>Due</th>
			   </tr></thead><tbody>';

	  foreach ($packages as $p) {
		$html .= '<tr>
					<td>' . $p['invoice_id'] . '</td>
					<td>' . $p['description'] . '</td>
					<td>' . $p['qty'] . '</td>
					<td>' . app_format_money_custom($p['rate'], 1) . '</td>
					<td>' . app_format_money_custom($p['total'], 1) . '</td>
					<td>' . app_format_money_custom($p['paid'], 1) . '</td>
					<td>' . app_format_money_custom($p['due'], 1) . '</td>
				  </tr>';
	  }

	  $html .= '</tbody></table>';
	  return $html;
	}
	public function get_patient_treatment($id)
	{
		$sorting = hooks()->apply_filters('lead_activity_log_default_sort', 'DESC');

		$this->db->select('pt.*, t.description as treatment_name');  // Select fields from both tables
		$this->db->from(db_prefix() . 'patient_treatment pt');
		$this->db->join(db_prefix() . 'items t', 'pt.treatment_type_id = t.id', 'left'); // Adjust field names accordingly
		$this->db->where('pt.userid', $id);
		$this->db->order_by('pt.created_at', $sorting);

		return $this->db->get()->result_array();
	}
	public function get_estimates($client_id)
	{
		$this->db->select('
			tblestimates.*,
			tblprojects.name as project_name,
			clients.company as client_name,
			treatment.treatment_name
		');
		$this->db->from(db_prefix() . 'estimates');
		$this->db->join(db_prefix() . 'clients as clients', 'clients.userid = tblestimates.clientid', 'left');
		$this->db->join(db_prefix() . 'projects', 'tblprojects.id = tblestimates.project_id', 'left');
		$this->db->join(db_prefix() . 'patient_treatment as patient_treatment', 'patient_treatment.estimation_id = tblestimates.id', 'left');
		$this->db->join(db_prefix() . 'treatment as treatment', 'treatment.treatment_id = patient_treatment.treatment_type_id', 'left');
		$this->db->where(db_prefix() . 'estimates.clientid', $client_id);
		$this->db->where(db_prefix() . 'estimates.status !=', 5); // Optional: exclude deleted/archived status
		$this->db->order_by(db_prefix() . 'estimates.date', 'DESC');

		$estimates = $this->db->get()->result_array();


		return $estimates;
	}

	public function get_testimonial()
	{
		// Check if table exists
		if ($this->db->table_exists(db_prefix() . 'flextestimonial')) {
			// Select id and title
			return $this->db->select('id, title, description')
							->from(db_prefix() . 'flextestimonial')
							->where('active', '1') // Optional: Only fetch active testimonials
							->get()
							->result_array();
		}

		// Return empty array if table doesn't exist
		return [];
	}
	
	public function get_shared_requests($id=null, $key=null)
	{
		$check_type = $this->db->get_where(db_prefix() . 'share_request', array("share_key"=>$key))->row();
		if($check_type){
			if($check_type->type == "lead"){
				$this->db->select('
					sr.id as request_id,
					sr.user_id,
					sr.type,
					sr.date_sent,
					sr.status,
					sr.send_email,
					sr.send_sms,
					sr.send_whatsapp,
					sr.share_key,
					l.*,
					t.id AS testimonial_id,
					t.slug AS testimonial_slug,
					t.title AS testimonial_title,
					t.description AS testimonial_description,
				');
				$this->db->from(db_prefix() . 'share_request sr');
				$this->db->join(db_prefix() . 'leads l', 'l.id = sr.user_id', 'left');
				$this->db->join(db_prefix() . 'flextestimonial t', 't.id = sr.feedback_id', 'left'); // If feedback_id is added in tblshare_request
				if($id){
					$this->db->where(array("type"=>'lead', "user_id"=>$id));
				}
				
				if($key){
					$this->db->where(array("type"=>'lead', "share_key"=>$key));
				}
				$query = $this->db->get();
				return $query->result_array();
			}else{
				$this->db->select('
					sr.id as request_id,
					sr.user_id,
					sr.type,
					sr.date_sent,
					sr.status,
					sr.send_email,
					sr.send_sms,
					sr.send_whatsapp,
					sr.share_key,
					c.*,
					t.id AS testimonial_id,
					t.slug AS testimonial_slug,
					t.title AS testimonial_title,
					t.description AS testimonial_description,
				');
				$this->db->from(db_prefix() . 'share_request sr');
				$this->db->join(db_prefix() . 'clients c', 'c.userid = sr.user_id', 'left');
				$this->db->join(db_prefix() . 'flextestimonial t', 't.id = sr.feedback_id', 'left'); // If feedback_id is added in tblshare_request
				if($id){
					$this->db->where(array("type"=>'patient', "user_id"=>$id));
				}
				
				if($key){
					$this->db->where(array("type"=>'patient', "share_key"=>$key));
				}
				$query = $this->db->get();
				return $query->result_array();
				
			}
		}
		
		
	}
	public function get_testimonial_responses($conditions = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'flextestimonialresponses');
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $query = $this->db->get();
        return $query->result_array();
    }

	public function get_appointment_by_id($id)
	{
		return $this->db->get_where(db_prefix() . 'appointment', ['appointment_id' => $id])->row_array();
	}

	public function get_branch()
	{
		return $this->db->get_where(db_prefix() . 'customers_groups')->result_array();
	}

	public function update_appointment($id, $data, $lead_id=NULL)
	{
		$client_id = $data['client_id'];
		unset($data['client_id']);
		$data['updated_by'] = get_staff_user_id();
		$data['updated_at'] = date('Y-m-d H:i:s');

		// 1. Get old appointment data before update
		$this->db->where('appointment_id', $id);
		$existing_data = $this->db->get(db_prefix() . 'appointment')->row_array();

		// 2. Update appointment
		$this->db->where('appointment_id', $id);
		$this->db->update(db_prefix() . 'appointment', $data);

		// 3. Prepare communication data
		$assigned_doctor = get_staff_full_name($data['enquiry_doctor_id'] ?? null);
		$appointment_date = date('d-m-Y', strtotime($data['appointment_date']));
		$appointment_time = date('h:i A', strtotime($data['appointment_date']));
		$date_time = date('d-m-Y h:i A', strtotime($data['appointment_date']));
		$branch_address = get_option('invoice_company_address');

		$communication_data = array(
			"assigned_doctor"   => $assigned_doctor,
			"appointment_date"  => $appointment_date,
			"appointment_time"  => $appointment_time,
			"date_time"         => $date_time,
			"branch_address"    => $branch_address,
		);

		// 4. Trigger patient journey log
		$this->patient_journey_log_event($client_id, 'appointment_created', 'Appointment Updated', $communication_data);

		// 5. Log appointment updates to lead activity
		$this->load->model('leads_model');
		$changed_fields = [];

		foreach ($data as $key => $new_value) {
			if (isset($existing_data[$key]) && $existing_data[$key] != $new_value) {
				// Optional: format datetime or staff name
				$old_value = $existing_data[$key];

				if (in_array($key, ['appointment_date'])) {
					$old_value = date('d-m-Y h:i A', strtotime($old_value));
					$new_value = date('d-m-Y h:i A', strtotime($new_value));
				} elseif ($key === 'enquiry_doctor_id') {
					$old_value = get_staff_full_name($existing_data[$key]);
					$new_value = get_staff_full_name($new_value);
				}

				$changed_fields[] = ucfirst($key) . " changed from '{$old_value}' to '{$new_value}'";
			}
		}

		if (!empty($changed_fields) AND !empty($lead_id)) {
			$this->leads_model->log_lead_activity($lead_id, 'Appointment rescheduled', false, serialize([
				get_staff_full_name(),
				implode("; ", $changed_fields),
			]));
		}
		
		if (!empty($changed_fields) AND !empty($id)) {
			$description = "Appointment Updated";
			$this->log_patient_activity($client_id, $description, 0, implode("; ", $changed_fields));
		}
		return true;
	}

	
	/**
     * Insert a log entry for the patient journey.
     *
     * @param int $userid
     * @param string $status
     * @param string|null $remarks
     * @return bool
     */
    public function patient_journey_log_event($userid, $status, $remarks = null, $communication_data = null, $status_id = NULL)
	{
		if (!$userid || !$status) {
			return false;
		}
		if(!$status_id){
			$status_clean = str_replace('_', ' ', $status);
			$this->db->where('LOWER(name)', strtolower($status_clean));
			$row = $this->db->get(db_prefix() . 'leads_status')->row();
			if($row){
				$status_id = $row->id;
			}
		}
		$data = [
			'userid'     => $userid,
			'status'     => $status,
			'remarks'    => $remarks,
			'created_by' => get_staff_user_id(),
			'created_at' => date('Y-m-d H:i:s'),
		];

		$this->db->insert(db_prefix() . 'patient_journey_log', $data);
		
		$status_lower = strtolower($status);
		$status_lower = strtolower(str_replace('_', ' ', $status_lower));

		$this->db->where('LOWER(title)', $status_lower);

		// Show the compiled query before executing
		$this->db->get_compiled_select(db_prefix() . 'flextestimonial'); // For debugging
		$this->db->where('LOWER(title)', $status_lower);
		
		$get_testimonial = $this->db->get(db_prefix() . 'flextestimonial')->row();
		
		if($get_testimonial){
			$id = $get_testimonial->id;
			$share_key = str_pad(mt_rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT);
			$share_request = array(
			"user_id" => $userid,
			"type" => "patient",
			"date_sent" => date("Y-m-d H:i:s"),
			"created_by" => get_staff_user_id(),
			"status" => 'pending',
			"send_email" => 1,
			"send_sms" => 1,
			"send_whatsapp" => 1,
			"share_key" => $share_key,
			"feedback_id" => $id
			);
			
			$this->db->insert(db_prefix() . 'share_request', $share_request);
			$feedback_link = base_url('flextestimonial/dr/' . $share_key);
		}else{
			$feedback_link = "https://positiveautism.com/1";
		}
		$status = strtolower(str_replace(' ', '_', $status));
		if($status == 'not_registered'){
			$status = "not_registered_patient_week1";
		}
	
		if (is_array($communication_data) && !empty($communication_data)) {
			require_once(FCPATH . 'modules/lead_call_log/helpers/custom_helper.php');
			
			$send_email    = get_option($status . '_template_enabled');
			$send_sms      = get_option($status . '_template_enabled');
			$send_whatsapp = get_option($status . '_template_enabled');
			
			$testimonial_link = "https://youtube.com/shorts/kmTdpLom6WU?si=GUfeARnZJyzRgA3N";
			$link = "https://youtube.com/shorts/kmTdpLom6WU?si=GUfeARnZJyzRgA3N";
			$payment_link = "https://positiveautism.com/";
			

			// Fetch patient details
			$this->db->select('new.email_id, c.phonenumber, c.company');
			$this->db->from('clients as c');
			$this->db->join(db_prefix() . 'clients_new_fields as new', 'new.userid = c.userid');
			$this->db->where('c.userid', $userid);
			$row = $this->db->get()->row();

			$email         = $row ? $row->email_id : null;
			$phonenumber   = $row ? $row->phonenumber : null;
			$patient_name  = $row ? $row->company : null;
			$this->load->config('client/config');
			$vertical_name = config_item('vertical_name');
			$branch_phone = get_option('invoice_company_phonenumber');
			
			// Fields expected in $communication_data
			$fields = [
				'appointment_date', 'appointment_time', 'assigned_doctor', 'date_time', 'branch_address',
				'invoice_link', 'invoice_number', 'reg_date_start', 'reg_date_end', 'follow_up_date',
				'punch_in_time', 'shift_time', 'missed_appoinment', 'package_cost', 'paid_amount',
				'pending_amount', 'treatment_duration', 'renewal_date', 'branch', 'employee_id',
				'treatment', 'last_payment_date', 'paid_date', 'receipt_link', 'staff_name', 'followup_date', 'consult_paid', 'mr_number'
			];
			// Dynamically extract values from $communication_data
			foreach ($fields as $field) {
				${$field} = isset($communication_data[$field]) ? $communication_data[$field] : null;
			}

			// Email sending
			if ($send_email == 1 && $email) {
				$subject  = _l($status);
				$message  = get_option($status . '_template_content');
				

				$replacements = [
				'{patient_name}'       => htmlspecialchars((string) ($patient_name ?? '')),
				'{mr_number}'             => htmlspecialchars((string) ($mr_number ?? '')),
				'{mobile}'             => htmlspecialchars((string) ($phonenumber ?? '')),
				'{staff_name}'   	   => htmlspecialchars((string) ($staff_name ?? '')),
				'{testimonial_link}'   => htmlspecialchars((string) ($testimonial_link ?? '')),
				'{payment_link}'   => htmlspecialchars((string) ($payment_link ?? '')),
				'{branch_phone}'       => htmlspecialchars((string) ($branch_phone ?? '')),
				'{email}'              => htmlspecialchars((string) ($email ?? '')),
				'{feedback_link}'               => htmlspecialchars((string) ($feedback_link ?? '')),
				'{link}'               => htmlspecialchars((string) ($link ?? '')),
				'{appointment_date}'   => htmlspecialchars((string) ($appointment_date ?? '')),
				'{appointment_time}'   => htmlspecialchars((string) ($appointment_time ?? '')),
				'{assigned_doctor}'    => htmlspecialchars((string) ($assigned_doctor ?? '')),
				'{invoice_link}'       => htmlspecialchars((string) ($invoice_link ?? '')),
				'{invoice_number}'     => htmlspecialchars((string) ($invoice_number ?? '')),
				'{reg_date_start}'     => htmlspecialchars((string) ($reg_date_start ?? '')),
				'{reg_date_end}'       => htmlspecialchars((string) ($reg_date_end ?? '')),
				'{follow_up_date}'     => htmlspecialchars((string) ($follow_up_date ?? '')),
				'{punch_in_time}'      => htmlspecialchars((string) ($punch_in_time ?? '')),
				'{shift_time}'         => htmlspecialchars((string) ($shift_time ?? '')),
				'{missed_appoinment}'  => htmlspecialchars((string) ($missed_appoinment ?? '')),
				'{package_cost}'       => htmlspecialchars((string) ($package_cost ?? '')),
				'{paid_amount}'        => htmlspecialchars((string) ($paid_amount ?? '')),
				'{pending_amount}'     => htmlspecialchars((string) ($pending_amount ?? '')),
				'{treatment_duration}' => htmlspecialchars((string) ($treatment_duration ?? '')),
				'{renewal_date}'       => htmlspecialchars((string) ($renewal_date ?? '')),
				'{branch}'             => htmlspecialchars((string) ($branch ?? '')),
				'{branch_address}'     => htmlspecialchars((string) ($branch_address ?? '')),
				'{paid_date}'          => htmlspecialchars((string) ($paid_date ?? '')),
				'{employee_id}'        => htmlspecialchars((string) ($employee_id ?? '')),
				'{treatment}'          => htmlspecialchars((string) ($treatment ?? '')),
				'{last_payment_date}'  => htmlspecialchars((string) ($last_payment_date ?? '')),
				'{vertical_name}'      => htmlspecialchars((string) ($vertical_name ?? '')),
				'{date_time}'          => htmlspecialchars((string) ($date_time ?? '')),
				'{receipt_link}'       => htmlspecialchars((string) ($receipt_link ?? '')),
				'{followup_date}'       => htmlspecialchars((string) ($followup_date ?? '')),
				'{consult_paid}'       => htmlspecialchars((string) ($consult_paid ?? '')),
			];


				$final_message = str_replace(array_keys($replacements), array_values($replacements), $message);
				$result = mailflow_send_email($email, $subject, $final_message, 'system');
				
				log_message_communication([
					'userid'       => $userid,
					'status'       => $status_id,
					'message_type' => 'email',
					'message'      => $final_message,
					'response'      => $result
				]);
			}

			// SMS sending
			if ($send_sms == 1 && $phonenumber) {
				$feedback_sms_template_id      = get_option($status . '_sms_template_id');
				$feedback_sms_template_content = get_option($status . '_sms_template_content');
				
				$replacements = [
					'{patient_name}'       => $patient_name,
					'{mr_number}'   => $mr_number,
					'{testimonial_link}'   => $testimonial_link,
					'{payment_link}'       => $payment_link,
					'{staff_name}'   	   => $staff_name,
					'{mobile}'             => $phonenumber,
					'{branch_phone}'       => $branch_phone,
					'{email}'              => $email,
					'{feedback_link}'      => $feedback_link,
					'{link}'               => $link,
					'{appointment_date}'   => $appointment_date,
					'{appointment_time}'   => $appointment_time,
					'{assigned_doctor}'    => $assigned_doctor,
					'{invoice_link}'       => $invoice_link,
					'{invoice_number}'     => $invoice_number,
					'{reg_date_start}'     => $reg_date_start,
					'{reg_date_end}'       => $reg_date_end,
					'{follow_up_date}'     => $follow_up_date,
					'{punch_in_time}'      => $punch_in_time,
					'{shift_time}'         => $shift_time,
					'{missed_appoinment}'  => $missed_appoinment,
					'{package_cost}'       => $package_cost,
					'{paid_amount}'        => $paid_amount,
					'{pending_amount}'     => $pending_amount,
					'{paid_date}'     => $paid_date,
					'{treatment_duration}' => $treatment_duration,
					'{renewal_date}'       => $renewal_date,
					'{branch}'             => $branch,
					'{branch_address}'     => $branch_address,
					'{employee_id}'        => $employee_id,
					'{treatment}'          => $treatment,
					'{last_payment_date}'  => $last_payment_date,
					'{vertical_name}'      => $vertical_name,
					'{date_time}'      => $date_time,
					'{receipt_link}'      => $receipt_link,
					'{followup_date}'      => $followup_date,
					'{consult_paid}'      => $consult_paid,
				];

				preg_match_all('/\{(.*?)\}/', $feedback_sms_template_content, $matches);

				// Step 1: Extract placeholders like {name}, {phone}
				$found_placeholders = $matches[0]; // Includes curly braces
				$values = [];

				// Copy of original content to modify
				$final_message = $feedback_sms_template_content;

				foreach ($found_placeholders as $placeholder) {
					$key = trim($placeholder, '{}'); // e.g., "name" from "{name}"
					
					$value = isset($replacements[$placeholder]) ? $replacements[$placeholder] : '';

					// Collect for pipe-separated string
					$values[] = $value;

					// Replace placeholder with actual value
					$final_message = str_replace($placeholder, $value, $final_message);
				}

				// Output 1: Pipe-separated raw values (Srinu|9000720819)
				$final_output = implode('|', $values);

				// Output 2: Fully formatted message with replacements
				$final_message_output = $final_message;

				$gateway = $this->app_sms->get_active_gateway();
				if ($gateway !== false) {
					$className = 'sms_' . $gateway['id'];
					$retval = $this->{$className}->send_fastAPI($phonenumber, $feedback_sms_template_id, $final_output);
					
					log_message_communication([
						'userid'       => $userid,
						'status'       => $status_id,
						'message_type' => 'sms',
						'message'      => $final_message_output,
						'response'      => $retval
					]);
				}
			}
			// WhatsApp sending
			if ($send_whatsapp == 1 && $phonenumber) {
				$templateName    = get_option($status . '_whatsapp_template_name');
				$templateContent = get_option($status . '_whatsapp_template_content');
				

				$replacements = [
					'patient_name'       => $patient_name,
					'mr_number'       => $mr_number,
					'testimonial_link'   => $testimonial_link,
					'payment_link'   => $payment_link,
					'staff_name'         => $staff_name,
					'mobile'             => $phonenumber,
					'branch_phone'       => $branch_phone,
					'email'              => $email,
					'paid_date'          => $paid_date,
					'feedback_link'         => $feedback_link,
					'link'               => $link,
					'appointment_date'   => $appointment_date,
					'appointment_time'   => $appointment_time,
					'assigned_doctor'    => $assigned_doctor,
					'invoice_link'       => $invoice_link,
					'invoice_number'     => $invoice_number,
					'reg_date_start'     => $reg_date_start,
					'reg_date_end'       => $reg_date_end,
					'follow_up_date'     => $follow_up_date,
					'punch_in_time'      => $punch_in_time,
					'shift_time'         => $shift_time,
					'missed_appoinment'  => $missed_appoinment,
					'package_cost'       => $package_cost,
					'paid_amount'        => $paid_amount,
					'pending_amount'     => $pending_amount,
					'treatment_duration' => $treatment_duration,
					'renewal_date'       => $renewal_date,
					'branch'             => $branch,
					'branch_address'     => $branch_address,
					'employee_id'        => $employee_id,
					'treatment'          => $treatment,
					'last_payment_date'  => $last_payment_date,
					'vertical_name'      => $vertical_name,
					'date_time'      => $date_time,
					'receipt_link'      => $receipt_link,
					'followup_date'      => $followup_date,
					'consult_paid'      => $consult_paid,
				];
				
				preg_match_all('/\{{1,2}(\w+)\}{1,2}/', $templateContent, $matches);
				$found_keys = $matches[1]; // Only the keys (without braces)
				$raw_placeholders = $matches[0]; // With braces, e.g. {name}, {{name}}

				$parameterArray = [];
				$final_message_output = $templateContent;
				

				foreach ($found_keys as $index => $key) {
					$placeholder = $raw_placeholders[$index]; // e.g., {name} or {{name}}

					if (isset($replacements[$key])) {
						$value = $replacements[$key];
						$parameterArray[$key] = $value;

						// Replace in message
						$final_message_output = str_replace($placeholder, $value, $final_message_output);
					}
				}
				$retval = send_message_via_api($phonenumber, $templateName, $parameterArray);
				
				log_message_communication([
					'status'       => $status_id,
					'userid'       => $userid,
					'message_type' => 'whatsapp',
					'message'      => $final_message_output,
					'response'      => $retval
				]);
			}
		}
		
	}

    public function generate_mr_no($userid)
    {
        $userid = (int) $userid;
        if ($userid <= 0) {
            return false;
        }

        $currentRecord = $this->db->get_where(db_prefix() . 'clients_new_fields', ['userid' => $userid])->row();
        if (!$currentRecord) {
            return false;
        }

        if (!empty($currentRecord->mr_no)) {
            return $currentRecord->mr_no;
        }

        $branch_id = $this->current_branch_id;
        if (!$branch_id) {
            $branchRow = $this->db->select('groupid')
                ->from(db_prefix() . 'customer_groups')
                ->where('customer_id', $userid)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()
                ->row();
            if ($branchRow) {
                $branch_id = (int) $branchRow->groupid;
            }
        }

        $isGlobalSequence = !$branch_id;

        $branch_code = '';
        $branch_short_code = '';

        if (!$isGlobalSequence) {
            $get_branch_code = $this->db->get_where(db_prefix() . 'master_settings', [
                'title'     => 'branch_code',
                'branch_id' => $branch_id,
            ])->row();
            if ($get_branch_code && $get_branch_code->options !== '') {
                $parts = array_map('trim', explode(',', $get_branch_code->options));
                $branch_code = $parts[0] ?? '';
            }

            $get_branch_short_code = $this->db->get_where(db_prefix() . 'master_settings', [
                'title'     => 'branch_short_code',
                'branch_id' => $branch_id,
            ])->row();
            if ($get_branch_short_code && $get_branch_short_code->options !== '') {
                $parts = array_map('trim', explode(',', $get_branch_short_code->options));
                $branch_short_code = $parts[0] ?? '';
            }
        }

        $prefix = $branch_short_code !== '' ? $branch_short_code : $branch_code;
        if ($prefix === '') {
            $prefix = 'MR';
        }

        $startPosition = strlen($prefix) + 1;

        $manageTransaction = false;
        if ($this->db->trans_depth() === 0) {
            $this->db->trans_begin();
            $manageTransaction = true;
        }

        if ($isGlobalSequence) {
            $sql = 'SELECT MAX(CAST(SUBSTRING(cnf.mr_no, ' . $startPosition . ') AS UNSIGNED)) AS max_seq
                    FROM ' . db_prefix() . 'clients_new_fields cnf
                    WHERE cnf.mr_no LIKE ?
                    FOR UPDATE';
            $params = [$prefix . '%'];
        } else {
            $sql = 'SELECT MAX(CAST(SUBSTRING(cnf.mr_no, ' . $startPosition . ') AS UNSIGNED)) AS max_seq
                    FROM ' . db_prefix() . 'clients_new_fields cnf
                    JOIN ' . db_prefix() . 'customer_groups cg ON cg.customer_id = cnf.userid
                    WHERE cg.groupid = ?
                      AND cnf.mr_no LIKE ?
                    FOR UPDATE';
            $params = [$branch_id, $prefix . '%'];
        }

        $row = $this->db->query($sql, $params)->row();
        $nextSequence = ($row && $row->max_seq) ? (int) $row->max_seq : 0;
        $nextSequence++;

        $mr_no = $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);

        $this->db->where('userid', $userid);
        $this->db->group_start();
        $this->db->where('mr_no', '');
        $this->db->or_where('mr_no IS NULL', null, false);
        $this->db->group_end();
        $this->db->set('mr_no', $mr_no);
        $this->db->update(db_prefix() . 'clients_new_fields');

        if ($manageTransaction) {
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }
            $this->db->trans_commit();
        }

        // Double-check in case another process populated the value simultaneously.
        $latestRecord = $this->db->select('mr_no')
            ->from(db_prefix() . 'clients_new_fields')
            ->where('userid', $userid)
            ->get()
            ->row();

        return $latestRecord && !empty($latestRecord->mr_no) ? $latestRecord->mr_no : $mr_no;
    }
	
    public function register_patient($userid, $invoiceId = NULL, $treatment_followup_date = NULL)
    {
		if($invoiceId){
			$check = $this->db->get_where(db_prefix() . 'itemable', array("rel_type"=>'invoice', "rel_id"=>$invoiceId, "description !="=>'Consultation Fee'))->row();
			if($check){
				$data = array(
				"current_status" =>"Registered",
				"registration_start_date" =>date('Y-m-d H:i:s')
				);
				$this->db->where('userid', $userid);
				$this->db->update(db_prefix() . 'clients_new_fields', $data);
				$this->generate_mr_no($userid);
				
				

				$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"branch_address" => $branch_address,
				);
				$this->patient_journey_log_event($client_id, 'patient_registered', 'Patient Registered', $communication_data);
				
			}
		}
		if($treatment_followup_date){
			$data = array(
				"registration_start_date" =>date('Y-m-d'),
				"registration_end_date" =>$treatment_followup_date,
				);
				$this->db->where('userid', $userid);
				$this->db->update(db_prefix() . 'clients_new_fields', $data);
		}
		
		return true;
	}
	
	public function confirm_booking($id = NULL)
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

			$get_branch_short_code = $this->db->get_where(db_prefix() . 'master_settings', [
				'title'     => 'branch_short_code',
				'branch_id' => $branch_id
			])->row();
			$branch_short_code = $get_branch_short_code ? $get_branch_short_code->options : '';
			
			if($count){
				$number = $branch_code.'-'.$branch_short_code.'-'.$count + 1;
			}else{
				$number = $branch_code.'-'.$branch_short_code.'-1';
			}
			
			$formatted_number = str_pad($number, 4, '0', STR_PAD_LEFT);
			$visit_id = "V-".$formatted_number;
			
			
			$this->db->where('appointment_id', $id);
			$this->db->update(db_prefix() . 'appointment', ['visit_status' => '1', "visit_id"=>$visit_id, "visited_date"=>date('Y-m-d H:i:s')]);
			echo "Here";
			exit();
			//echo json_encode(['success' => true, 'message' => _l('visit_successfully_confirmed')]);
			return true;
		} else {
			return false;
			//echo json_encode(['success' => false, 'message' => _l('something_went_wrong')]);
		}
	}
	
	public function add_estimation($data)
    {
		if (isset($data['patient_treatment_id'])) {
            $patient_treatment_id = $data['patient_treatment_id'];
            unset($data['patient_treatment_id']);
        }
        $data['datecreated'] = date('Y-m-d H:i:s');

        $data['addedfrom'] = get_staff_user_id();

        $data['prefix'] = get_option('estimate_prefix');

        $data['number_format'] = get_option('estimate_number_format');

        $save_and_send = isset($data['save_and_send']);

        $estimateRequestID = false;
        if (isset($data['estimate_request_id'])) {
            $estimateRequestID = $data['estimate_request_id'];
            unset($data['estimate_request_id']);
        }
		 unset($data['invoice_treatment_name']);
		 unset($data['dr_duration']);
		 unset($data['invoice_period']);

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data['hash'] = app_generate_hash();
        $tags         = isset($data['tags']) ? $data['tags'] : '';

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }
        if (isset($data['paying_amount'])) {
            $paying_amount = $data['paying_amount'];
            unset($data['paying_amount']);
        }
        if (isset($data['paymentmode'])) {
            $paymentmode = $data['paymentmode'];
            unset($data['paymentmode']);
        }
        if (isset($data['invoice_acknowledge'])) {
            $invoice_acknowledge = $data['invoice_acknowledge'];
            unset($data['invoice_acknowledge']);
        }

        $data = $this->map_shipping_columns($data);

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }

        $hook = hooks()->apply_filters('before_estimate_added', [
            'data'  => $data,
            'items' => $items,
        ]);

        $data  = $hook['data'];
        $items = $hook['items'];
		
		// Get the latest estimation expirydate for this client
		$this->db->select('expirydate')
				 ->from(db_prefix() . 'estimates')
				 ->where('clientid', $data['clientid'])
				 ->order_by('id', 'DESC')
				 ->limit(1);
		$latest_estimate = $this->db->get()->row();

		if ($latest_estimate && !empty($latest_estimate->expirydate)) {
			$latest_expiry = new DateTime($latest_estimate->expirydate);
			$today         = new DateTime();

			// If latest expiry date is in the future
			if ($latest_expiry > $today) {
				// Difference in days from today to latest expiry
				$days_diff = $today->diff($latest_expiry)->days;

				// Add the same number of days to current $data['expirydate']
				$current_expiry = new DateTime($data['expirydate']);
				$current_expiry->add(new DateInterval('P' . $days_diff . 'D'));
				$data['expirydate'] = $current_expiry->format('Y-m-d');
			}
		}
		$this->db->insert(db_prefix() . 'estimates', $data);
       
		$insert_id = $this->db->insert_id();
		$estimation_id = $insert_id;
        if ($insert_id) {
			$client_id = $data['clientid'];
			$expirydate = $data['expirydate'];
			if($paying_amount>0){
				$data = array(
				"registration_end_date" => $expirydate
				);
				$this->db->where(array("userid"=>$client_id));
				$this->db->update(db_prefix() . 'clients_new_fields', $data);
			}
			
				
				
            $this->save_formatted_number($insert_id);
            
            // Update next estimate number in settings
            $this->db->where('name', 'next_estimate_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');

            if ($estimateRequestID !== false && $estimateRequestID != '') {
                $this->load->model('estimate_request_model');
                $completedStatus = $this->estimate_request_model->get_status_by_flag('completed');
                $this->estimate_request_model->update_request_status([
                    'requestid' => $estimateRequestID,
                    'status'    => $completedStatus->id,
                ]);
            }

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            handle_tags_save($tags, $insert_id, 'estimate');

            foreach ($items as $key => $item) {
                if ($itemid = add_new_sales_item_post($item, $insert_id, 'estimate')) {
                    _maybe_insert_post_item_tax($itemid, $item, $insert_id, 'estimate');
                }
            }

            update_sales_total_tax_column($insert_id, 'estimate', db_prefix() . 'estimates');
            $this->log_estimate_activity($insert_id, 'estimate_activity_created');

            hooks()->do_action('after_estimate_added', $insert_id);

            if ($save_and_send === true) {
                $this->send_estimate_to_client($insert_id, '', true, '', true);
            }
			
			$this->load->model('estimates_model');
			$this->load->model('payments_model');
			
			
			$invoice_id = $this->estimates_model->convert_to_invoice($insert_id);
			if($paying_amount>0){
				$clientid = $data['clientid'];
				
				$expirydate = $data['expirydate'];
				$data = array(
					"registration_end_date" => $expirydate
					);
				$this->db->where(array("userid"=>$client_id));
				$this->db->update(db_prefix() . 'clients_new_fields', $data);
				//$this->register_patient($clientid, $invoice_id);
				$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"branch_address" => $branch_address,
				);
				$this->patient_journey_log_event($clientid, 'patient_registered', 'Patient Registered', $communication_data);
			}
			
			// Step 1: Get existing estimation_id
			$this->db->select('estimation_id');
			$this->db->from(db_prefix() . 'patient_treatment');
			$this->db->where('id', $patient_treatment_id);
			$existing = $this->db->get()->row('estimation_id');

			// Step 2: Convert existing to array
			$existing_ids = [];
			if (!empty($existing)) {
				if (is_numeric($existing)) {
					$existing_ids[] = (int)$existing;
				} else {
					$decoded = json_decode($existing, true);
					if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
						$existing_ids = array_map('intval', $decoded);
					}
				}
			}

			// Step 3: Append new ID if not already present
			if (!in_array((int)$estimation_id, $existing_ids)) {
				$existing_ids[] = (int)$estimation_id;
			}

			// Step 4: Update with JSON-encoded array
			$estimation_data = array(
				"estimation_id" => json_encode($existing_ids)
			);
			$this->db->where(array("id" => $patient_treatment_id));
			$this->db->update(db_prefix() . 'patient_treatment', $estimation_data);

			
            return $invoice_id;
        }

        return false;
    }
	private function map_shipping_columns($data)
    {
        if (!isset($data['include_shipping'])) {
            foreach ($this->shipping_fields as $_s_field) {
                if (isset($data[$_s_field])) {
                    $data[$_s_field] = null;
                }
            }
            $data['show_shipping_on_estimate'] = 1;
            $data['include_shipping']          = 0;
        } else {
            $data['include_shipping'] = 1;
            // set by default for the next time to be checked
            if (isset($data['show_shipping_on_estimate']) && ($data['show_shipping_on_estimate'] == 1 || $data['show_shipping_on_estimate'] == 'on')) {
                $data['show_shipping_on_estimate'] = 1;
            } else {
                $data['show_shipping_on_estimate'] = 0;
            }
        }

        return $data;
    }
	public function save_formatted_number($id) 
    {
        $formattedNumber = format_estimate_number($id);

        $this->db->where('id', $id);
        $this->db->update('estimates', ['formatted_number' => $formattedNumber]);
    }
	public function log_estimate_activity($id, $description = '', $client = false, $additional_data = '')
    {
        $staffid   = get_staff_user_id();
        $full_name = get_staff_full_name(get_staff_user_id());
        if (DEFINED('CRON')) {
            $staffid   = '[CRON]';
            $full_name = '[CRON]';
        } elseif ($client == true) {
            $staffid   = null;
            $full_name = '';
        }

        $this->db->insert(db_prefix() . 'sales_activity', [
            'description'     => $description,
            'date'            => date('Y-m-d H:i:s'),
            'rel_id'          => $id,
            'rel_type'        => 'estimate',
            'staffid'         => $staffid,
            'full_name'       => $full_name,
            'additional_data' => $additional_data,
        ]);
    }
	
	public function update_treatment_post_estimation($treatment_id, $estimation_id, $expirydate)
	{
		$data = [
			'treatment_start_date'   => date('Y-m-d'),
			'treatment_end_date'     => $expirydate,
			'estimation_id'          => $estimation_id,
			'estimation_created_by'  => get_staff_user_id(),
			'estimation_added_date'  => date('Y-m-d H:i:s'),
		];

		$this->db->where('id', $treatment_id);
		$this->db->update(db_prefix() . 'patient_treatment', $data);

		return $this->db->affected_rows() > 0;
	}
	
	public function add_lead_patient_status($data = [])
	{
		// Ensure either leadid or userid is provided
		if (empty($data['leadid']) && empty($data['userid'])) {
			return false; // Nothing to insert
		}

		$insertData = [
			'leadid'   => !empty($data['leadid']) ? $data['leadid'] : null,
			'userid'   => !empty($data['userid']) ? $data['userid'] : null,
			'status'   => !empty($data['status']) ? $data['status'] : null,
			'datetime' => date('Y-m-d H:i:s'),
			'remarks'  => !empty($data['remarks']) ? $data['remarks'] : null,
		];

		$this->db->insert(db_prefix() . 'lead_patient_journey', $insertData);
		return $this->db->insert_id();
	}

	public function get_latest_casesheet($id){
		$this->db->select('pt.*, pt.id as patient_treatment_id, i.description as treatment_name, i.rate, case.*, staff.firstname, staff.lastname');
		$this->db->from(db_prefix() . 'patient_treatment pt');
		$this->db->join(db_prefix() . 'items i', 'i.id = pt.treatment_type_id', 'left');
		$this->db->join(db_prefix() . 'casesheet case', 'case.id = pt.casesheet_id', 'left');
		$this->db->join(db_prefix() . 'staff staff', 'staff.staffid = case.staffid', 'left');
		
		$this->db->order_by('pt.id', 'desc');
		$this->db->order_by('pt.casesheet_id', 'desc');
		$this->db->where('pt.userid', $id);
		$this->db->where('case.userid', $id);

		return $this->db->get()->row();
	}
	
	public function get_latest_casesheet_package($id){
		$this->db->select('pt.*, pt.id as patient_treatment_id, i.description as treatment_name, i.rate, case.*, staff.firstname, staff.lastname');
		$this->db->from(db_prefix() . 'patient_treatment pt');
		$this->db->join(db_prefix() . 'items i', 'i.id = pt.treatment_type_id', 'left');
		$this->db->join(db_prefix() . 'casesheet case', 'case.id = pt.casesheet_id', 'left');
		$this->db->join(db_prefix() . 'staff staff', 'staff.staffid = case.staffid', 'left');
		
		$this->db->order_by('pt.id', 'desc');
		$this->db->order_by('pt.casesheet_id', 'desc');
		$this->db->where('pt.userid', $id);
		//$this->db->where('pt.estimation_id IS NULL');
		$this->db->where('case.userid', $id);

		return $this->db->get()->row();
	}
	
	public function get_payment_modes(){
		return $this->db->get(db_prefix() . 'payment_modes')->result_array();
	}

	public function get_pincodes(){
		return $this->db->get(db_prefix() . 'pincode')->result_array();
	}
	
	
	public function get_today_appointment_data($id)
	{
		$today = date('Y-m-d');
		$current_staff_id = get_staff_user_id();

		// ✅ Get current staff's role name
		$this->db->select('r.name as role_name');
		$this->db->from(db_prefix() . 'staff s');
		$this->db->join(db_prefix() . 'roles r', 'r.roleid = s.role', 'left');
		$this->db->where('s.staffid', $current_staff_id);
		$staff = $this->db->get()->row();

		$is_doctor = ($staff && in_array(strtolower($staff->role_name), ['doctor', 'service doctor']));

		// ✅ Use subquery to get latest token per patient+doctor+date
		$token_subquery = "
			SELECT *
			FROM " . db_prefix() . "tokens t1
			WHERE t1.date = '$today'
			  AND t1.token_status IN ('Pending', 'Recall')
			  AND t1.patient_id != " . (int) $id . "
			  AND t1.token_id = (
				  SELECT MAX(t2.token_id)
				  FROM " . db_prefix() . "tokens t2
				  WHERE t2.patient_id = t1.patient_id
					AND t2.doctor_id = t1.doctor_id
					AND t2.date = t1.date
					AND t2.token_status IN ('Pending', 'Recall')
			  )
		";

		// ✅ Main appointment query with JOIN on subquery as tokens
		$this->db->select('
			a.appointment_id,
			a.enquiry_doctor_id,
			a.userid as patient_id,
			c.company as patient_name,
			t.token_number,
			t.token_status
		');
		$this->db->from(db_prefix() . 'appointment a');
		$this->db->join(db_prefix() . 'clients c', 'c.userid = a.userid', 'left');
		$this->db->join("($token_subquery) t", 't.patient_id = a.userid AND t.doctor_id = a.enquiry_doctor_id', 'inner');
		$this->db->where('a.visit_status', 1);
		$this->db->where('DATE(a.appointment_date)', $today);

		if ($is_doctor) {
			$this->db->where('a.enquiry_doctor_id', $current_staff_id);
		}

		return $this->db->get()->result_array();
	}




	public function start_token($patient_id, $doctor_id)
	{
		$today = date('Y-m-d');

		// 1. Complete any active "Serving" token for today for this doctor
		$this->db->where('doctor_id', $doctor_id);
		$this->db->where('date', $today);
		$this->db->where('token_status', 'Serving');
		$this->db->update(db_prefix() . 'tokens', ['token_status' => 'Completed']);

		// 2. Check if token already exists for patient/doctor/today
		$this->db->where('patient_id', $patient_id);
		$this->db->where('doctor_id', $doctor_id);
		$this->db->where('date', $today);
		$query = $this->db->get(db_prefix() . 'tokens');
		$existing_token = $query->row();

		if ($existing_token) {
			// 3a. Update the existing token to "Serving"
			$this->db->where('token_id', $existing_token->token_id);
			$this->db->update(db_prefix() . 'tokens', ['token_status' => 'Serving']);
		}

		return true;
	}
	
	public function get_leads_sources($id = null)
    {
        if ($id !== null) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'leads_sources')->row_array();
        }

        $this->db->order_by('name', 'ASC');
        return $this->db->get(db_prefix() . 'leads_sources')->result_array();
    }
	public function get_client_branch($client_id){
		$get_branch_id = $this->db->get_where(db_prefix() . 'customer_groups', array("customer_id"=>$client_id))->row();
		return $get_branch_id ? $get_branch_id->groupid : 0;
	}
	
	public function get_first_appointment($userid){
		$this->db->select("inv.total");
		$this->db->from(db_prefix() . 'appointment as a');
		$this->db->join(db_prefix() . 'invoices as inv', "a.invoice_id = inv.id");
		$this->db->where(array("a.userid"=>$userid));
		$this->db->order_by("a.appointment_id", "DESC");
		$this->db->limit(1);
		return $this->db->get()->row();
	}
	
	public function get_roles(){
		return $this->db->get(db_prefix() . 'roles')->result_array();
	}
}
