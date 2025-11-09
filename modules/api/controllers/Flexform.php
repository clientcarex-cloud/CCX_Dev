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
class Flexform extends REST_Controller {
    function __construct()
    {
        parent::__construct();
        // Load your model if not already loaded
        $this->load->model('Api_model');
    }

    /**
     * @api {get} api/flexform/:id Request flexform information
     * @apiName GetFlexform
     * @apiGroup Flexform
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Flexform unique ID.
     *
     * @apiSuccess {Object} flexform Flexform information.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "id": "1",
     *         "name": "Sample Form",
     *         "slug": "sample-form",
     *         "type": "contact",
     *         "type_id": 3,
     *         "staffid": 5,
     *         "published": "1",
     *         "allow_duplicate_leads": "0",
     *         "require_terms_and_conditions": "1",
     *         "enable_captcha": "0",
     *         "enable_single_page": "1",
     *         "lead_name_prefix": "Lead-",
     *         "data_submission_notification_emails": "email@example.com",
     *         "lead_source": 2,
     *         "lead_status": 4,
     *         "notify_form_submission": "1",
     *         "notify_type": "email",
     *         "notify_ids": "1,3",
     *         "responsible": 7,
     *         "submit_btn_name": "Submit",
     *         "submit_btn_text_color": "#ffffff",
     *         "submit_btn_bg_color": "#007bff",
     *         "description": "This is a sample flexform.",
     *         "date_added": "2025-06-01 12:00:00",
     *         "date_updated": "2025-06-02 12:00:00",
     *         "end_date": "2025-12-31 23:59:59",
     *         "privacy": "public",
     *         "customerids": "",
     *         "staffids": ""
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
        $data = $this->Api_model->get_table('flexform', $id);

        // Check if the data store contains
        if ($data)
        {
            $data = $this->Api_model->get_api_custom_data($data, "flexform", $id);

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
	 * @api {get} api/flexform/data_search/:key Search Flexforms (by keyword)
	 * @apiName SearchFlexformData
	 * @apiGroup Flexform
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {String} key Keyword to search in form fields (e.g. name, slug, description).
	 *
	 * @apiSuccess {Object[]} flexforms List of matching Flexforms.
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     [
	 *         {
	 *             "id": "1",
	 *             "name": "Contact Us",
	 *             "slug": "contact-us",
	 *             "type": "lead",
	 *             ...
	 *         }
	 *     ]
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
		// Perform search using common model method
		$data = $this->Api_model->search('flexform', $key);

		// Check if results exist
		if ($data)
		{
			// Optional: sort by ID
			usort($data, function($a, $b) {
				return $a['id'] - $b['id'];
			});

			// Format using custom API transformation
			$data = $this->Api_model->get_api_custom_data($data, "flexform");

			// Send response
			$this->response($data, REST_Controller::HTTP_OK);
		}
		else
		{
			// Send empty response
			$this->response([
				'status' => FALSE,
				'message' => 'No data were found'
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}

}
