<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require __DIR__.'/REST_Controller.php';

class Mobile_search extends REST_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Api_model');
    }

    /**
     * @api {get} api/mobile_search/:mobile Search Customers (by mobile)
     * @apiName SearchCustomerByMobile
     * @apiGroup Customer
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} mobile Mobile number to search in customer records.
     *
     * @apiSuccess {Object[]} customers List of matching customers.
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *         {
     *             "userid": "12",
     *             "name": "John Doe",
     *             "mobile": "9876543210",
     *             "alternate_mobile": "9123456780",
     *             "branch": "Hyderabad"
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
    public function index_get()
    {
        // Get mobile number from URI segment 3
        $mobile = $this->uri->rsegment(3);

        if (empty($mobile)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Mobile number is required'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Build search query
        $this->db->select('
            c.userid,
            c.company as name,
            c.phonenumber as mobile,
            new.alt_number1 as alternate_mobile,
            b.name as branch
        ');
        $this->db->from(db_prefix() . 'clients c');
        $this->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
        $this->db->join(db_prefix() . 'customer_groups a', 'a.customer_id = c.userid', 'left');
        $this->db->join(db_prefix() . 'customers_groups b', 'b.id = a.groupid', 'left');
        $this->db->group_start()
                 ->where('c.phonenumber', $mobile)
                 ->or_where('new.alt_number1', $mobile)
                 ->group_end();

        $data = $this->db->get()->result_array();

        if ($data) {
            $this->response($data, REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No data were found'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
