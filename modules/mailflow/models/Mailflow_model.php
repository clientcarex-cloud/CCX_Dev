<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mailflow_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function searchLeadsEmails($leadSource=[], $assignedStaff=[], $leadCountry=[],$leadGroups=[])
    {
        $this->db->from(db_prefix() . 'leads');

        if (!empty($leadSource)) {
            $this->db->where_in('source', $leadSource);
        }

        if (!empty($assignedStaff)) {
            $this->db->where_in('assigned', $assignedStaff);
        }

        if (!empty($leadCountry)) {
            $this->db->where_in('country', $leadCountry);
        }

        if (!empty($leadGroups)) {
            $this->db->where_in('status', $leadGroups);
        }

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $emails = $query->result_array();
            $emails = array_column($emails, 'email');
            $emails = array_filter($emails, 'strlen');
            $emails = array_unique($emails);

            return array_values($emails);
        }

        return [];
    }

    public function searchLeadsPhoneNumbers($leadSource=[], $assignedStaff=[], $leadCountry=[],$leadGroups=[])
    {

        $this->db->from(db_prefix() . 'leads');

        if (!empty($leadSource)) {
            $this->db->where_in('source', $leadSource);
        }

        if (!empty($assignedStaff)) {
            $this->db->where_in('assigned', $assignedStaff);
        }

        if (!empty($leadCountry)) {
            $this->db->where_in('country', $leadCountry);
        }

        if (!empty($leadGroups)) {
            $this->db->where_in('status', $leadGroups);
        }

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $emails = $query->result_array();
            //$emails = array_column($emails, 'phonenumber');
            //$emails = array_filter($emails, 'strlen');
            //$emails = array_unique($emails);

            return array_values($emails);
        }

        return [];
    }

    public function searchCustomersEmails($customerStatus = 'active', $customerGroups=[], $customerCountries=[])
    {
        $status = '1';

        if ($customerStatus == 'inactive') {
            $status = '0';
        }

        $searchQuery = $this->db->select('ct.email')
            ->from(db_prefix() . 'clients c')
            ->join(db_prefix() . 'contacts ct', 'c.userid = ct.userid');

        if (!empty($customerStatus)) {
            $this->db->where('c.active', $status);
        }

        if (!empty($customerGroups)) {
            $searchQuery->join(db_prefix() . 'customer_groups cg', 'c.userid = cg.customer_id');
            $this->db->where_in('cg.groupid', $customerGroups);
        }

        if (!empty($customerCountries)) {
            $this->db->where_in('c.country', $customerCountries);
        }

        $query = $this->db->get();

        $emails = array_column($query->result_array(), 'email');
        $emails = array_filter($emails, 'strlen');

        return array_unique($emails);
    }

    public function searchCustomersPhoneNumbers($customerStatus = 'active', $customerGroups=[], $customerCountries=[])
    {
        $status = '1';

        if ($customerStatus == 'inactive') {
            $status = '0';
        }

        $searchQuery = $this->db->select('c.*, cn.*')
            ->from(db_prefix() . 'clients c')
            ->join(db_prefix() . 'contacts ct', 'c.userid = ct.userid')
            ->join(db_prefix() . 'clients_new_fields cn', 'c.userid = cn.userid');

        if (!empty($customerStatus)) {
            $this->db->where('c.active', $status);
        }

        if (!empty($customerGroups)) {
            $searchQuery->join(db_prefix() . 'customer_groups cg', 'c.userid = cg.customer_id');
            $this->db->where_in('cg.groupid', $customerGroups);
        }

        if (!empty($customerCountries)) {
            $this->db->where_in('c.country', $customerCountries);
        }

        $query = $this->db->get();
		return $query->result_array();
        //$emails = array_column($query->result_array(), 'phonenumber');
        //$emails = array_filter($emails, 'strlen');

        //return array_unique($emails);
    }
    
    public function add($data)
    {
        $this->db->insert(db_prefix() . 'mailflow_newsletter_history', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'mailflow_newsletter_history')->row();
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'mailflow_newsletter_history');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function addUnsubscribedEmail($data)
    {
        $this->db->insert(db_prefix() . 'mailflow_unsubscribed_emails', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getUnsubscribedEmails()
    {
        $emailList = $this->db->get(db_prefix() . 'mailflow_unsubscribed_emails')->result_array();

        $emails = array_column($emailList, 'email');
        $emails = array_filter($emails, 'strlen');

        return array_unique($emails);
    }

    public function getUnsubscribedEmail($id)
    {
        $this->db->where('email', $id);
        return $this->db->get(db_prefix() . 'mailflow_unsubscribed_emails')->row();
    }

    public function deleteUnsubscribedEmail($id)
    {

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'mailflow_unsubscribed_emails');

        return $this->db->affected_rows() > 0;
    }

    public function addTemplate($data)
    {
        $this->db->insert(db_prefix() . 'mailflow_email_templates', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getTemplate($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'mailflow_email_templates')->row();
    }

    public function getTemplates()
    {
        return $this->db->get(db_prefix() . 'mailflow_email_templates')->result_array();
    }

    public function updateTemplate($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'mailflow_email_templates', $data);

        return $this->db->affected_rows() > 0;
    }

    public function deleteTemplate($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'mailflow_email_templates');

        return $this->db->affected_rows() > 0;
    }

    public function addIntegration($data)
    {

        if (isset($data['smtp_password'])) {
            $data['smtp_password'] = $this->encryption->encrypt($data['smtp_password']);
        }

        $this->db->insert(db_prefix() . 'mailflow_smtp_integrations', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getIntegration($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'mailflow_smtp_integrations')->row();
    }

    public function getIntegrations()
    {
        return $this->db->get(db_prefix() . 'mailflow_smtp_integrations')->result_array();
    }

    public function updateIntegration($id, $data)
    {
        if (isset($data['smtp_password'])) {
            $data['smtp_password'] = $this->encryption->encrypt($data['smtp_password']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'mailflow_smtp_integrations', $data);

        return $this->db->affected_rows() > 0;
    }

    public function deleteIntegration($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'mailflow_smtp_integrations');

        return $this->db->affected_rows() > 0;
    }

    public function addScheduledCampaign($data)
    {
        $this->db->insert(db_prefix() . 'mailflow_scheduled_campaigns', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getScheduledCampaign($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'mailflow_scheduled_campaigns')->row();
    }

    public function getScheduledCampaigns($campaign_status = '')
    {
        if ($campaign_status !== '') {
            $this->db->where('campaign_status', $campaign_status);
        }

        return $this->db->get(db_prefix() . 'mailflow_scheduled_campaigns')->result_array();
    }

    public function updateScheduledCampaign($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'mailflow_scheduled_campaigns', $data);

        return $this->db->affected_rows() > 0;
    }

    public function deleteScheduledCampaign($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'mailflow_scheduled_campaigns');

        return $this->db->affected_rows() > 0;
    }
	
	public function get_roles()
    {

        return $this->db->get(db_prefix() . 'roles')->result_array();
    }
	
	public function searchStaffEmails($roles = [])
	{
		$this->db->from(db_prefix() . 'staff');

		if (!empty($roles)) {
			$this->db->where_in('role', $roles); // Assuming 'role' is the column for role ID or name
		}

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$emails = $query->result_array();
			$emails = array_column($emails, 'email');
			$emails = array_filter($emails, 'strlen');
			$emails = array_unique($emails);

			return array_values($emails);
		}

		return [];
	}
	public function searchStaffPhoneNumbers($roles = [])
	{
		$this->db->from(db_prefix() . 'staff');

		if (!empty($roles)) {
			$this->db->where_in('role', $roles); // Or use 'role_id' if applicable
		}

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result_array();
			//$phones = array_column($staffData, 'phonenumber');
			//$phones = array_filter($phones, 'strlen');
			//$phones = array_unique($phones);

			//return array_values($phones);
		}

		return [];
	}


}
