<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Eod extends AdminController
{
	private $current_branch_id;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('eod_model');
        $this->load->model('client/Client_model'); // correct for modules

        $this->current_branch_id = $this->Client_model->get_logged_in_staff_branch_id();
    }


    private function check_customer_permissions()
    {
        if (staff_cant('view', 'eod')) {
            if (!have_assigned_customers() && staff_cant('create', 'eod')) {
                access_denied('eod');
            }
        }
    }

	
   public function my_eod()
    {
       if (staff_cant('view_own', 'eod')) {
            access_denied('eod');
        } 
        $data['title'] = _l("eod_list");
		
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('eod', 'tables/my_eod_table'));
        }
       $this->load->view('my_eod', $data);
    }
	
   public function all_eod()
    {
		if (staff_cant('view_all', 'eod')) {
            access_denied('all_eod');
        } 
        $data['title'] = _l("eod_list");
		
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('eod', 'tables/all_eod_table'));
        }
       $this->load->view('all_eod', $data);
    }

   public function create()
	{
		if (staff_cant('create', 'eod')) {
			access_denied('eod');
		}
		if ($this->input->post()) {
			$data = array(
				"staffid"     => get_staff_user_id(),
				"eod_id"      => date('Ymd') . get_staff_user_id(),
				'date'        => date('Y-m-d H:i:s'),
				'branch_id'   => $this->current_branch_id,
				'eod_status'  => 'Pending',
				'activity'    => $this->input->post('activity'),
				'subject'     => $this->input->post('subject'),
				'today_report'=> $this->input->post('today_report'),
			);
			$inserted = $this->eod_model->insert($data);
			
			if ($inserted) {
				set_alert('success', _l('eod_created_successfully'));
			} else {
				set_alert('danger', _l('eod_creation_failed'));
			}
			redirect(admin_url('eod/my_eod'));
		}
		$this->load->view('my_eod_form');
	}

	public function edit($id)
	{
		if (staff_cant('edit', 'eod')) {
			access_denied('eod');
		}
		$data['eod'] = $this->eod_model->get($id);
		if ($this->input->post()) {
			$updateData = $this->input->post();
			$updated = $this->eod_model->update($id, $updateData);
			if ($updated) {
				set_alert('success', _l('eod_updated_successfully'));
			} else {
				set_alert('warning', _l('no_changes_made'));
			}
			redirect(admin_url('eod/my_eod'));
		}
		$this->load->view('my_eod_edit', $data);
	}

	public function delete($id)
	{
		if (staff_cant('delete', 'eod')) {
			access_denied('eod');
		}
		$deleted = $this->eod_model->delete($id);
		if ($deleted) {
			set_alert('success', _l('eod_deleted_successfully'));
		} else {
			set_alert('danger', _l('eod_deletion_failed'));
		}
		redirect(admin_url('eod/my_eod'));
	}

	public function update_status()
	{
		$id     = $this->input->post('id');
		$status = $this->input->post('status');

		if (!$id || !$status) {
			echo json_encode(['success' => false, 'message' => _l('invalid_data')]);
			return;
		}

		$this->db->where('id', $id);
		$updated = $this->db->update(db_prefix() . 'eod', ['eod_status' => $status]);

		if ($updated) {
			// Optional: Set a flash alert for AJAX calls if needed
			echo json_encode(['success' => true, 'message' => _l('eod_status_updated')]);
		} else {
			echo json_encode(['success' => false, 'message' => _l('eod_status_update_failed')]);
		}
	}


}
