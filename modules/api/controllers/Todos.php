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
class Todos extends REST_Controller {
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
        $data = $this->Api_model->get_table('todos', $id);

        // Check if the data store contains
        if ($data)
        {
            $data = $this->Api_model->get_api_custom_data($data, "todos", $id);

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
        $data = $this->Api_model->search('todos', $key);
        // Check if the data store contains
        if ($data)
        {
			usort($data, function($a, $b) {
				return $a['todoid'] - $b['todoid'];
			});
            $data = $this->Api_model->get_api_custom_data($data,"todos");

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
        // form validation
        $this->form_validation->set_rules('description', 'Description', 'required');
		$this->form_validation->set_rules('staffid', 'Staff ID', 'required');

		

        if ($this->form_validation->run() == FALSE)
        {
            // form validation error
            $message = array(
                'status' => FALSE,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors() 
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        else
        {
           $insert_data = [
				'description' => $this->input->post('description', TRUE),
				'staffid' => $this->input->post('staffid', TRUE)
				
			];

               
            if (!empty($this->input->post('custom_fields', TRUE))) {
                $insert_data['custom_fields'] = $this->Api_model->value($this->input->post('custom_fields', TRUE));
            }
            // insert data
            $output = $this->Api_model->add_todos($insert_data);
            if ($output > 0 && !empty($output)) {
                // success
                $this->handle_task_attachments_array($output);
                $message = array(
                    'status' => TRUE,
                    'message' => 'Todo add successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Todo add failed.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {delete} api/delete/tasks/:id Delete a Task
     * @apiName DeleteTask
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Task unique ID.
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {String} message Task Delete Successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Task Delete Successful."
     *     }
     *
     * @apiError {String} status Request status.
     * @apiError {String} message Task Delete Fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Task Delete Fail."
     *     }
     */
    public function data_delete($id = '')
    {
        $id = $this->security->xss_clean($id);
        if (empty($id) && !is_numeric($id)) {
            $message = array(
                'status' => FALSE,
                'message' => 'Invalid Todo ID'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            // delete data
            $output = $this->Api_model->delete_todos_item($id);
            if ($output === TRUE) {
                // success
                $message = array(
                    'status' => TRUE,
                    'message' => 'Todo Delete Successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Todo Delete Fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {put} api/tasks/:id Update a task
     * @apiName PutTask
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
     * @apiParamExample {json} Request-Example:
     *  {
     *      "billable": "1", 
     *      "is_public": "1",
     *      "name": "Task 1",
     *      "hourly_rate": "0.00",
     *      "milestone": "0",
     *      "startdate": "27/08/2019",
     *      "duedate": null,
     *      "priority": "0",
     *      "repeat_every": "",
     *      "repeat_every_custom": "1",
     *      "repeat_type_custom": "day",
     *      "cycles": "0",
     *      "rel_type": "lead",
     *      "rel_id": "11",
     *      "tags": "",
     *      "description": ""
     *   }
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {String} message Task Update Successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Task Update Successful."
     *     }
     *
     * @apiError {String} status Request status.
     * @apiError {String} message Task Update Fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Task Update Fail."
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
            $message = array('status' => FALSE, 'message' => 'Invalid Todo ID');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $update_data = $this->input->post();
           
            $output = $this->Api_model->update_todo($update_data, $id);
            if (!empty($update_file) && count($update_file)) {
                if ($output <= 0 || empty($output)) {
                    $output = $id;
                }
            }

            if ($output > 0 && !empty($output)) {
                $message = array(
                    'status' => TRUE,
                    'message' => 'Todo Update Successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Todo Update Fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    function handle_task_attachments_array($task_id, $index_name = 'file') {
        $path = get_upload_path_by_type('task') . $task_id . '/';
        $CI = & get_instance();
        if (isset($_FILES[$index_name]['name']) && ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
            if (!is_array($_FILES[$index_name]['name'])) {
                $_FILES[$index_name]['name'] = [$_FILES[$index_name]['name']];
                $_FILES[$index_name]['type'] = [$_FILES[$index_name]['type']];
                $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
                $_FILES[$index_name]['error'] = [$_FILES[$index_name]['error']];
                $_FILES[$index_name]['size'] = [$_FILES[$index_name]['size']];
            }
            _file_attachments_index_fix($index_name);
            for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
                // Get the temp file path
                $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    if (_perfex_upload_error($_FILES[$index_name]['error'][$i]) || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                        continue;
                    }
                    _maybe_create_upload_path($path);
                    $filename = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                    $newFilePath = $path . $filename;
                    // Upload the file into the temp dir
                    if (copy($tmpFilePath, $newFilePath)) {
                        unlink($tmpFilePath);
                        $CI = & get_instance();
                        $CI->load->model('tasks_model');
                        $data = [];
                        $data[] = ['file_name' => $filename, 'filetype' => $_FILES[$index_name]['type'][$i], ];
                        $CI->tasks_model->add_attachment_to_database($task_id, $data, false);
                    }
                }
            }
        }
        return true;
    }
}