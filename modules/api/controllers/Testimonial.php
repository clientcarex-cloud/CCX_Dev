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
class Testimonial extends REST_Controller {
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

    /**
	 * @api {get} api/testimonial/:id Request testimonial information
	 * @apiName GetTestimonial
	 * @apiGroup Testimonials
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {Number} id Testimonial unique ID.
	 *
	 * @apiSuccess {Object} testimonial Testimonial information.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     {
	 *         "testimonialid": "10",
	 *         "client_name": "John Doe",
	 *         "message": "Great service!",
	 *         "dateadded": "2024-11-15 14:30:00",
	 *         "status": "approved",
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
        $data = $this->Api_model->get_table('testimonial', $id);

        // Check if the data store contains
        if ($data)
        {
            //$data = $this->Api_model->get_api_custom_data($data, "testimonial", $id);

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
	 * @api {get} api/testimonials/search/:keysearch Search Testimonials
	 * @apiName GetTestimonialSearch
	 * @apiGroup Testimonials
	 *
	 * @apiHeader {String} authtoken Authentication token, generated from admin area
	 *
	 * @apiParam {String} keysearch Search keywords (used in title, slug, or description).
	 *
	 * @apiSuccess {Object[]} testimonials List of matching testimonials.
	 * @apiSuccess {Number} testimonials.id Testimonial ID.
	 * @apiSuccess {String} testimonials.title Testimonial title.
	 * @apiSuccess {String} testimonials.slug URL-friendly slug.
	 * @apiSuccess {String} testimonials.description Testimonial description.
	 * @apiSuccess {String} testimonials.created_at Created date (formatted).
	 * @apiSuccess {String} testimonials.updated_at Updated date (formatted).
	 * @apiSuccess {Object[]} testimonials.responses Associated testimonial responses.
	 * @apiSuccess {Number} testimonials.responses.id Response ID.
	 * @apiSuccess {String} testimonials.responses.name Responder's name.
	 * @apiSuccess {String} testimonials.responses.text_response Text feedback.
	 * @apiSuccess {String} testimonials.responses.video_url Optional video URL.
	 * @apiSuccess {String} testimonials.responses.created_at Date when response was submitted.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     [
	 *         {
	 *             "id": "1",
	 *             "title": "Customer Satisfaction Survey",
	 *             "slug": "customer-satisfaction",
	 *             "description": "Collect client testimonials for marketing",
	 *             "created_at": "04-06-2025 14:15",
	 *             "updated_at": "04-06-2025 14:25",
	 *             "responses": [
	 *                 {
	 *                     "id": "7",
	 *                     "name": "John Doe",
	 *                     "text_response": "Excellent service!",
	 *                     "video_url": "https://example.com/video.mp4",
	 *                     "created_at": "04-06-2025 15:00"
	 *                 }
	 *             ]
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
        $data = $this->Api_model->search('testimonial', $key);
        // Check if the data store contains
        if ($data)
        {
			usort($data, function($a, $b) {
				return $a['id'] - $b['id'];
			});
            $data = $this->Api_model->get_api_custom_data($data,"testimonial");

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

    
    
}