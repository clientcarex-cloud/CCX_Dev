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
class Knowledge_base extends REST_Controller {
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

    /**
     * @api {get} api/todo/:id Request todo information
     * @apiName GetTask
     * @apiGroup todo
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id todo unique ID.
     *
     * @apiSuccess {Object} todo information.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "todoid": "10",
     *         "staff_name": "Srinu",
     *         "description": "Test",
     *         "dateadded": "2019-02-25 12:26:37",
     *         "finished": "0",
     *         "item_order": "0",
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
        $data = $this->Api_model->get_table('knowledge_base', $id);

        // Check if the data store contains
        if ($data)
        {
            $data = $this->Api_model->get_api_custom_data($data, "knowledge_base", $id);

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
	 * @api {get} api/todos/search/:keysearch Search TODOs
	 * @apiName GetTodoSearch
	 * @apiGroup Todos
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {String} keysearch Search keywords (used in description).
	 *
	 * @apiSuccess {Object[]} todos List of matching TODOs.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     [
	 *         {
	 *             "todoid": "5",
	 *             "description": "Call patient for feedback",
	 *             "staffid": "3",
	 *             "dateadded": "2025-06-02 10:30:00",
	 *             "finished": "0",
	 *             "datefinished": null,
	 *             "item_order": "1"
	 *         },
	 *         {
	 *             "todoid": "6",
	 *             "description": "Submit invoice to admin",
	 *             "staffid": "3",
	 *             "dateadded": "2025-06-01 12:45:00",
	 *             "finished": "1",
	 *             "datefinished": "2025-06-01 17:00:00",
	 *             "item_order": "2"
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
        $data = $this->Api_model->search('knowledge_base', $key);
        // Check if the data store contains
        if ($data)
        {
			usort($data, function($a, $b) {
				return $a['articleid'] - $b['articleid'];
			});
            $data = $this->Api_model->get_api_custom_data($data,"knowledge_base");

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
     * @api {post} api/tasks Add New Task
     * @apiName PostTask
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} name              Mandatory Task Name.
     * @apiParam {Date} startdate           Mandatory Task Start Date.
     * @apiParam {String} [is_public]       Optional Task public.
     * @apiParam {String} [billable]        Optional Task billable.
     * @apiParam {String} [hourly_rate]     Optional Task hourly rate.
     * @apiParam {String} [milestone]       Optional Task milestone.
     * @apiParam {Date} [duedate]           Optional Task deadline.
     * @apiParam {String} [priority]        Optional Task priority.
     * @apiParam {String} [repeat_every]    Optional Task repeat every.
     * @apiParam {Number} [repeat_every_custom]     Optional Task repeat every custom.
     * @apiParam {String} [repeat_type_custom]      Optional Task repeat type custom.
     * @apiParam {Number} [cycles]                  Optional cycles.
     * @apiParam {string="lead","customer","invoice", "project", "quotation", "contract", "annex", "ticket", "expense", "proposal"} rel_type Mandatory Task Related.
     * @apiParam {Number} rel_id            Optional Related ID.
     * @apiParam {String} [tags]            Optional Task tags.
     * @apiParam {String} [description]     Optional Task description.
     *
     *
     * @apiParamExample {Multipart Form} Request-Example:
     *     array (size=15)
     *     'is_public' => string 'on' (length=2)
     *     'billable' => string 'on' (length=2)
     *     'name' => string 'Task 12' (length=7)
     *     'hourly_rate' => string '0' (length=1)
     *     'milestone' => string '' (length=0)
     *     'startdate' => string '17/07/2019' (length=10)
     *     'duedate' => string '31/07/2019 11:07' (length=16)
     *     'priority' => string '2' (length=1)
     *     'repeat_every' => string '' (length=0)
     *     'repeat_every_custom' => string '1' (length=1)
     *     'repeat_type_custom' => string 'day' (length=3)
     *     'rel_type' => string 'customer' (length=8)
     *     'rel_id' => string '9' (length=1)
     *     'tags' => string '' (length=0)
     *     'description' => string '<span>Task Description</span>' (length=29)
     *
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {String} message Task add successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Task add successful."
     *     }
     *
     * @apiError {String} status Request status.
     * @apiError {String} message Task add fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Task add fail."
     *     }
     * 
     */
    public function data_post()
	{
		\modules\api\core\Apiinit::the_da_vinci_code('api');

		// Form validation
		$this->form_validation->set_rules('articlegroup', 'Article Group Name', 'required');
		$this->form_validation->set_rules('subject', 'Subject', 'required');
		$this->form_validation->set_rules('description', 'Description', 'required');
		$this->form_validation->set_rules('staffid', 'Staff ID', 'required');

		if ($this->form_validation->run() == FALSE) {
			// Validation error
			$message = array(
				'status'  => FALSE,
				'error'   => $this->form_validation->error_array(),
				'message' => validation_errors()
			);
			$this->response($message, REST_Controller::HTTP_NOT_FOUND);
		} else {
			// Prepare data
			$insert_data = [
				'articlegroup' => $this->input->post('articlegroup', TRUE),
				'subject'      => $this->input->post('subject', TRUE),
				'description'  => $this->input->post('description', TRUE),
				'staffid'      => $this->input->post('staffid', TRUE),
			];

			if (!empty($this->input->post('custom_fields', TRUE))) {
				$insert_data['custom_fields'] = $this->Api_model->value($this->input->post('custom_fields', TRUE));
			}

			// Call model to insert article
			$output = $this->Api_model->add_knowledge_article($insert_data);

			if ($output > 0 && !empty($output)) {
				$message = array(
					'status'  => TRUE,
					'message' => 'Knowledge article added successfully.'
				);
				$this->response($message, REST_Controller::HTTP_OK);
			} else {
				$message = array(
					'status'  => FALSE,
					'message' => 'Failed to add article.'
				);
				$this->response($message, REST_Controller::HTTP_NOT_FOUND);
			}
		}
	}


   /**
	 * @api {delete} api/delete/knowledge_base/:id Delete a Knowledge Base Article
	 * @apiName DeleteKnowledgeBase
	 * @apiGroup KnowledgeBase
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {Number} id Knowledge Base Article unique ID.
	 *
	 * @apiSuccess {Boolean} status Request status.
	 * @apiSuccess {String} message Knowledge Base Article Delete Successful.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     {
	 *       "status": true,
	 *       "message": "Knowledge Base Article Delete Successful."
	 *     }
	 *
	 * @apiError {Boolean} status Request status.
	 * @apiError {String} message Knowledge Base Article Delete Fail.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "status": false,
	 *       "message": "Knowledge Base Article Delete Fail."
	 *     }
	 */

    public function data_delete($id = '')
    {
        $id = $this->security->xss_clean($id);
        if (empty($id) && !is_numeric($id)) {
            $message = array(
                'status' => FALSE,
                'message' => 'Invalid knowledge_base ID'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            // delete data
            $output = $this->Api_model->delete_knowledge_base($id);
            if ($output === TRUE) {
                // success
                $message = array(
                    'status' => TRUE,
                    'message' => 'knowledge Base Delete Successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'knowledge Base Delete Fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
	 * @api {put} api/knowledge_base/:id Update a Knowledge Base Article
	 * @apiName UpdateKnowledgeBase
	 * @apiGroup KnowledgeBase
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {String} subject         Mandatory Article Subject.
	 * @apiParam {String} description     Mandatory Article Description.
	 * @apiParam {String} articlegroup    Mandatory Group Name. If not found, a new group will be created.
	 * @apiParam {Object[]} [custom_fields] Optional array of custom fields.
	 *
	 * @apiParamExample {json} Request-Example:
	 * {
	 *     "subject": "How to use the CRM",
	 *     "description": "This article explains the CRM usage.",
	 *     "articlegroup": "Getting Started",
	 *     "custom_fields": [
	 *         {"fieldid": 5, "value": "Internal"},
	 *         {"fieldid": 6, "value": "2025"}
	 *     ]
	 * }
	 *
	 * @apiSuccess {Boolean} status Request status.
	 * @apiSuccess {String} message Knowledge Base Update Successful.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     {
	 *       "status": true,
	 *       "message": "Knowledge Base Update Successful."
	 *     }
	 *
	 * @apiError {Boolean} status Request status.
	 * @apiError {String} message Knowledge Base Update Failed.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "status": false,
	 *       "message": "Knowledge Base Update Failed."
	 *     }
	 */

    public function data_put($id = '')
    {
        $_POST = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);
        if (empty($_POST) || !isset($_POST)) {
            $this->load->library('parse_input_stream');
            $_POST = $this->parse_input_stream->parse_parameters();
            $_FILES = $this->parse_input_stream->parse_files();
            if (empty($_POST) || !isset($_POST)) {
                $message = array('status' => FALSE, 'message' => 'Data Not Acceptable OR Not Provided');
                $this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
        }
        $this->form_validation->set_data($_POST);
        if (empty($id) && !is_numeric($id)) {
            $message = array('status' => FALSE, 'message' => 'Invalid knowledge_base ID');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $update_data = $this->input->post();
            $output = $this->Api_model->update_knowledge_base($update_data, $id);
          
            if ($output > 0 && !empty($output)) {
                // success
                $message = array(
                    'status' => TRUE,
                    'message' => 'knowledge_base Update Successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'knowledge_base Update Fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    
}