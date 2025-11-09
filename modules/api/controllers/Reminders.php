<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

/** @noinspection PhpIncludeInspection */
require __DIR__.'/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Reminders extends REST_Controller {
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

    /**
	 * @api {get} api/reminder/:id Request reminder information
	 * @apiName GetReminder
	 * @apiGroup Reminders
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {Number} id Reminder unique ID.
	 *
	 * @apiSuccess {Object} reminder Information about the reminder.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     {
	 *         "id": "10",
	 *         "staff": "3",
	 *         "description": "Follow up with client",
	 *         "date": "2025-06-03 10:30:00",
	 *         "rel_id": "15",
	 *         "rel_type": "project",
	 *         "creator": "1",
	 *         "isnotified": "0",
	 *         "notify_by_email": "1"
	 *         ...
	 *     }
	 *
	 * @apiError {Boolean} status Request status.
	 * @apiError {String} message No data were found.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "status": false,
	 *       "message": "No data were found"
	 *     }
	 */

    public function data_get($id = '')
    {
        // If the id parameter doesn't exist return all the
        $data = $this->Api_model->get_table('reminders', $id);

        // Check if the data store contains
        if ($data)
        {
            $data = $this->Api_model->get_api_custom_data($data, "reminders", $id);

            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No data were found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    /**
	 * @api {get} api/reminders/search/:keysearch Search Reminders
	 * @apiName GetReminderSearch
	 * @apiGroup Reminders
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {String} keysearch Search keywords (used in description).
	 *
	 * @apiSuccess {Object[]} reminders List of matching Reminders.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     [
	 *         {
	 *             "id": "12",
	 *             "description": "Follow up with client",
	 *             "staff": "3",
	 *             "date": "2025-06-03 10:30:00",
	 *             "rel_id": "15",
	 *             "rel_type": "project",
	 *             "creator": "1",
	 *             "isnotified": "0",
	 *             "notify_by_email": "1"
	 *         },
	 *         {
	 *             "id": "14",
	 *             "description": "Send invoice reminder",
	 *             "staff": "3",
	 *             "date": "2025-06-02 09:00:00",
	 *             "rel_id": "7",
	 *             "rel_type": "client",
	 *             "creator": "1",
	 *             "isnotified": "1",
	 *             "notify_by_email": "0"
	 *         }
	 *     ]
	 *
	 * @apiError {Boolean} status Request status.
	 * @apiError {String} message No data were found.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "status": false,
	 *       "message": "No data were found"
	 *     }
	 */


    public function data_search_get($key = '')
    {
        // If the id parameter doesn't exist return all the
        $data = $this->Api_model->search('reminders', $key);
        // Check if the data store contains
        if ($data)
        {
			usort($data, function($a, $b) {
				return $a['id'] - $b['id'];
			});
            $data = $this->Api_model->get_api_custom_data($data,"reminders");

            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No data were found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    /**
	 * @api {post} api/reminders Add New Reminder
	 * @apiName PostReminder
	 * @apiGroup Reminders
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {String} description        Mandatory Reminder Description.
	 * @apiParam {DateTime} date             Mandatory Reminder Date and Time (YYYY-MM-DD HH:mm:ss).
	 * @apiParam {Number} staff              Mandatory Staff ID assigned to the reminder.
	 * @apiParam {Number} rel_id             Mandatory Related ID (e.g. client, project, etc.).
	 * @apiParam {String} rel_type           Mandatory Related Type (e.g. client, project).
	 * @apiParam {Number} [isnotified=0]     Optional Notification status (0 = not notified, 1 = notified).
	 * @apiParam {Number} [notify_by_email=1] Optional Notify by email (0 = no, 1 = yes).
	 * @apiParam {Number} creator            Mandatory Creator staff ID.
	 *
	 * @apiParamExample {json} Request-Example:
	 * {
	 *    "description": "Follow up with client about project status.",
	 *    "date": "2025-06-03 10:30:00",
	 *    "staff": 3,
	 *    "rel_id": 15,
	 *    "rel_type": "project",
	 *    "isnotified": 0,
	 *    "notify_by_email": 1,
	 *    "creator": 1
	 * }
	 *
	 * @apiSuccess {Boolean} status Request status.
	 * @apiSuccess {String} message Reminder added successfully.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     {
	 *       "status": true,
	 *       "message": "Reminder added successfully."
	 *     }
	 *
	 * @apiError {Boolean} status Request status.
	 * @apiError {String} message Reminder add failed.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "status": false,
	 *       "message": "Reminder add failed."
	 *     }
	 */

    public function data_post()
	{ 
		\modules\api\core\Apiinit::the_da_vinci_code('api');

		// Form validation rules
		$this->form_validation->set_rules('description', 'Description', 'required');
		$this->form_validation->set_rules('date', 'Date', 'required');
		$this->form_validation->set_rules('rel_id', 'Related ID', 'required|integer');
		$this->form_validation->set_rules('staff', 'Staff ID', 'required|integer');
		$this->form_validation->set_rules('rel_type', 'Related Type', 'required');
		$this->form_validation->set_rules('creator', 'Creator ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$message = [
				'status'  => FALSE,
				'error'   => $this->form_validation->error_array(),
				'message' => validation_errors(),
			];
			$this->response($message, REST_Controller::HTTP_BAD_REQUEST);
			return;
		}

		// Prepare insert data from POST
		$insert_data = [
			'description'     => $this->input->post('description', TRUE),
			'date'            => $this->input->post('date', TRUE),
			'rel_id'          => $this->input->post('rel_id', TRUE),
			'staff'           => $this->input->post('staff', TRUE),
			'rel_type'        => $this->input->post('rel_type', TRUE),
			'creator'         => $this->input->post('creator', TRUE),
			'notify_by_email' => $this->input->post('notify_by_email', TRUE) ?: 1,
			'isnotified'      => 0,
		];

		// Call model to insert reminder
		$insert_id = $this->Api_model->add_reminder($insert_data);

		if ($insert_id > 0) {
			$message = [
				'status'  => TRUE,
				'message' => 'Reminder added successfully.',
				'id'      => $insert_id,
			];
			$this->response($message, REST_Controller::HTTP_OK);
		} else {
			$message = [
				'status'  => FALSE,
				'message' => 'Failed to add reminder.',
			];
			$this->response($message, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
	}


   /**
	 * @api {delete} api/delete/reminders/:id Delete a Reminder
	 * @apiName DeleteReminder
	 * @apiGroup Reminders
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {Number} id Reminder unique ID.
	 *
	 * @apiSuccess {Boolean} status Request status.
	 * @apiSuccess {String} message Reminder delete successful.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     {
	 *       "status": true,
	 *       "message": "Reminder delete successful."
	 *     }
	 *
	 * @apiError {Boolean} status Request status.
	 * @apiError {String} message Reminder delete failed.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "status": false,
	 *       "message": "Reminder delete failed."
	 *     }
	 */


    public function data_delete($id = '')
    {
        $id = $this->security->xss_clean($id);
        if (empty($id) && !is_numeric($id)) {
            $message = array(
                'status' => FALSE,
                'message' => 'Invalid reminders ID'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            // delete data
            $output = $this->Api_model->delete_reminder($id);
            if ($output === TRUE) {
                // success
                $message = array(
                    'status' => TRUE,
                    'message' => 'Reminders Delete Successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'RemindersDelete Fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
	/**
	 * @api {put} api/reminders/:id Update a Reminder
	 * @apiName UpdateReminder
	 * @apiGroup Reminders
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {String} description       Mandatory Reminder description.
	 * @apiParam {String} date              Mandatory Reminder date in datetime format (e.g., "2025-06-03 10:00:00").
	 * @apiParam {Number} staff             Mandatory Staff ID associated with reminder.
	 * @apiParam {Number} rel_id            Mandatory Related entity ID.
	 * @apiParam {String} rel_type          Mandatory Related entity type (e.g., "client", "project").
	 * @apiParam {Number} creator           Mandatory Creator staff ID.
	 * @apiParam {Number} [isnotified=0]   Optional Notification status flag (0 = not notified, 1 = notified).
	 * @apiParam {Number} [notify_by_email=1] Optional Whether to notify by email (0 = no, 1 = yes).
	 *
	 * @apiParamExample {json} Request-Example:
	 * {
	 *     "description": "Step-by-step guide to flossing.",
	 *     "date": "2025-06-03 10:00:00",
	 *     "staff": 1,
	 *     "rel_id": 1,
	 *     "rel_type": "Test",
	 *     "creator": 1,
	 *     "isnotified": 0,
	 *     "notify_by_email": 1
	 * }
	 *
	 * @apiSuccess {Boolean} status Request status.
	 * @apiSuccess {String} message Reminder update successful.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     {
	 *       "status": true,
	 *       "message": "Reminder update successful."
	 *     }
	 *
	 * @apiError {Boolean} status Request status.
	 * @apiError {String} message Reminder update failed.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "status": false,
	 *       "message": "Reminder update failed."
	 *     }
	 */


    public function data_put($id = '')
    {
		
		 // Get raw input and decode JSON, clean for XSS
		$_POST = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);

		// If empty, try to parse input stream (fallback)
		if (empty($_POST) || !isset($_POST)) {
			$this->load->library('parse_input_stream');
			$_POST = $this->parse_input_stream->parse_parameters();
			$_FILES = $this->parse_input_stream->parse_files();
			if (empty($_POST) || !isset($_POST)) {
				$message = ['status' => FALSE, 'message' => 'Data Not Acceptable OR Not Provided'];
				$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
				return;
			}
		}

		// Validate ID parameter
		if (empty($id) || !is_numeric($id)) {
			$message = ['status' => FALSE, 'message' => 'Invalid reminder ID'];
			$this->response($message, REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		// Set POST data for form_validation
		$this->form_validation->set_data($_POST);


		// Prepare data for update (sanitize input as needed)
		$update_data = $this->input->post();
		
		// Call model to update reminder by ID
		$output = $this->Api_model->update_reminder($update_data, $id);

		if ($output) {
			$message = [
				'status'  => TRUE,
				'message' => 'Reminder update successful.'
			];
			$this->response($message, REST_Controller::HTTP_OK);
		} else {
			$message = [
				'status'  => FALSE,
				'message' => 'Reminder update failed.'
			];
			$this->response($message, REST_Controller::HTTP_NOT_FOUND);
		}
    }

    
}