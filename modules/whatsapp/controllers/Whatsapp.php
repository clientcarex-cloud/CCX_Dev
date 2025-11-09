<?php

defined('BASEPATH') or exit('No direct script access allowed');
   
class Whatsapp extends AdminController
{
	private $tenantId;
	private $apiKey;
	

    public function __construct()
    {
        parent::__construct();
		$this->tenantId = '318198' ;
        $this->apiKey =  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiJhYTc1OTVhZC00YTMxLTRkYzgtODcxMC04YTNhNmU3NmYwNGQiLCJ1bmlxdWVfbmFtZSI6ImppdmEuYW1yQGdtYWlsLmNvbSIsIm5hbWVpZCI6ImppdmEuYW1yQGdtYWlsLmNvbSIsImVtYWlsIjoiaml2YS5hbXJAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMDUvMTIvMjAyNSAxMDo0MTo1OCIsInRlbmFudF9pZCI6IjMxODE5OCIsImRiX25hbWUiOiJtdC1wcm9kLVRlbmFudHMiLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOiJBRE1JTklTVFJBVE9SIiwiZXhwIjoyNTM0MDIzMDA4MDAsImlzcyI6IkNsYXJlX0FJIiwiYXVkIjoiQ2xhcmVfQUkifQ.W3XTC6rETxQtwIZhMWkOQ49QyULXKvEdg7xea1MGCr8';
		$this->load->model('Whatsapp_model');
    }
    
    public function index(){
    
		$data['title'] = 'WhatsApp';
		$response = $this->get_whatsapp_templates();
		$data['whatsapp_templates'] =  $response['messageTemplates'] ?? [];
		$this->load->view('send_whatsapp', $data);
    
    }
	
    public function get_whatsapp_templates(){
		$tenantId = $this->tenantId;
		$apiKey = $this->apiKey;
		
        $pageSize = 10; // You can modify this as needed

        $url = "https://live-mt-server.wati.io/$tenantId/api/v1/getMessageTemplates?pageSize=$pageSize";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false, 
            CURLOPT_HTTPHEADER => [
                'Accept: */*',
                'Authorization: Bearer ' . $apiKey
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
           return [];
        } else {
            return json_decode($response, true);
			 
        }
    
	}
	
    public function send_template_message() {
		// Multiple WhatsApp numbers separated by comma
		$input_numbers = (string) $this->input->post('mobile_numbers');
		$whatsappNumbers = array_filter(array_map('trim', explode(',', $input_numbers)));

		$templateName = $this->input->post('template_id');
		$broadcastName = "Open WhatsApp";

		// Get the template variables (name-value pairs)
		$template_variables = $this->input->post('template_variables');
		$parameters = [];

		// Format parameters for API
		if (!empty($template_variables) && is_array($template_variables)) {
			foreach ($template_variables as $key => $value) {
				$parameters[] = [
					'name' => (string)$key,  // Use the variable names like 'name', 'date', etc.
					'value' => $value        // The corresponding value like 'Srinivas', etc.
				];
			}
		}

		// Results to store responses from the API
		$results = [];

		foreach ($whatsappNumbers as $number) {
			$number = trim($number);
			if (empty($number)) continue;

			// Prepare data for the API call
			$data = [
				'template_name' => $templateName,
				'broadcast_name' => $broadcastName,
				'parameters' => $parameters
			];

			// Call the API to send the message
			$response = $this->send_message_via_api($number, $templateName, $broadcastName, $parameters);
			$results[] = [
				'number' => $number,
				'response' => json_decode($response, true)
			];
		}

		// Output result
		set_alert('success', _l('sent_successfully'));
		redirect('whatsapp/index');
	}

    private function send_message_via_api($whatsappNumber, $templateName, $broadcastName, $parameters) {
		$tenantId = $this->tenantId;
		$apiKey = $this->apiKey;
		// Modify the parameter names if needed
		foreach ($parameters as &$param) {
			if ($param['name'] == '1') {
				$param['name'] = 'name'; // Change this based on the actual logic needed
			}
		}

		// Prepare the data to send in the cURL request
		$data = [
			"template_name" => $templateName,
			"broadcast_name" => $broadcastName,
			"parameters" => $parameters, // No need to json_encode here
		];

		// cURL setup
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://live-mt-server.wati.io/'.$tenantId.'/api/v1/sendTemplateMessage?whatsappNumber=' . urlencode($whatsappNumber),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => array(
				'accept: */*',
				'Authorization: Bearer ' . $apiKey,
				'Content-Type: application/json-patch+json'
			),
			CURLOPT_POSTFIELDS => json_encode($data), // Send data as JSON
		));

		// Execute cURL
		$response = curl_exec($curl);

		// Close cURL
		curl_close($curl);
		return $response;
	}
	public function update_template_status() {
		$id     = $this->input->post('id');
		$status = $this->input->post('status');
		if($status == "Active"){
			$status = 1;
		}else{
			$status = 0;
		}
		
		$this->db->where('id', $id);
		$updated = $this->db->update(db_prefix() . 'message_config', ['status' => $status]);

		echo json_encode([
			'success' => $updated,
			'csrfHash' => $this->security->get_csrf_hash()
		]);
	}
        
}
