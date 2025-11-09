<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Message_model extends App_Model {
	private $sms_api_key;
	private $sms_sender_id;
	private $tenantId;
	private $whatsapp_apiKey;
    public function __construct() {
        parent::__construct();
		
		$this->tenantId = '318198' ;
        $this->whatsapp_apiKey =  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiJhYTc1OTVhZC00YTMxLTRkYzgtODcxMC04YTNhNmU3NmYwNGQiLCJ1bmlxdWVfbmFtZSI6ImppdmEuYW1yQGdtYWlsLmNvbSIsIm5hbWVpZCI6ImppdmEuYW1yQGdtYWlsLmNvbSIsImVtYWlsIjoiaml2YS5hbXJAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMDUvMTIvMjAyNSAxMDo0MTo1OCIsInRlbmFudF9pZCI6IjMxODE5OCIsImRiX25hbWUiOiJtdC1wcm9kLVRlbmFudHMiLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOiJBRE1JTklTVFJBVE9SIiwiZXhwIjoyNTM0MDIzMDA4MDAsImlzcyI6IkNsYXJlX0FJIiwiYXVkIjoiQ2xhcmVfQUkifQ.W3XTC6rETxQtwIZhMWkOQ49QyULXKvEdg7xea1MGCr8';
		
		$this->sms_sender_id = 'OBEX' ;
		$this->sms_api_key =  'mdvy0nEJ7fOHAcoS5ZjLaMgl8krhpQ6CsWbwtGxeUF94uRIXN2BzwPfN5QKidDmYjRUG3oycu2l6L7hS';
	
    }

   public function get_active_template($trigger_key, $channel) {
		$this->db->where('trigger_key', $trigger_key)
				 ->where('template_channel', $channel)
				 ->where('status', 1);
				 
		$query = $this->db->get(db_prefix().'message_config');
		log_message('debug', "get_active_template SQL: ".$this->db->last_query());
		$result = $query->row_array();

		if (!$result) {
			log_message('error', "No active template found for trigger_key: {$trigger_key}, channel: {$channel}");
		}
		
		return $result;
	}


    public function dispatch_message($data)
    {
        $template_key = $data['template_key'] ?? '';
        $mobile       = $data['mobile'] ?? '';
        $params       = $data['params'] ?? [];
        if (!$template_key || !$mobile) {
            log_message('error', "[dispatch_message] Missing template_key or mobile");
            return false;
        }
        // 1. Fetch the active template
        $template = $this->get_active_template($template_key, $data['channel'] ?? 'sms');
        if (!$template) {
            log_message('error', "[dispatch_message] No active template found for key: {$template_key}");
            return false;
        }

        // 2. Replace {#var#} placeholders with provided parameters
        $message = $template['template_body'];
        $template_id = $template['template_id'];
        $required_params = explode(',', $template['params_required'] ?? '');
		
		
        foreach ($required_params as $index => $key) {
            $value = $params[$index] ?? '';
            $message = preg_replace('/\{#var#\}/', $value, $message, 1); // Replaces one at a time
        }
		//print_r($params);
        // 3. Send via appropriate channel
        if ($template['template_channel'] === 'sms') {
            return $this->send_sms($mobile, $params, $template_id);
        } elseif ($template['template_channel'] === 'whatsapp') {
            return $this->send_whatsapp($mobile, $template['template_name'], $params);
        }

        log_message('error', "[dispatch_message] Unknown channel: {$template['template_channel']}");
        return false;
    }

   public function send_sms($phone, $params, $template_id) 
   {
		$api_key    = $this->sms_api_key;  // Must be set in the class
		$sender_id  = $this->sms_sender_id;
		$msg = implode('|', $params);
		
		$numbers = explode(",", $phone); // support multiple
		$requests = [];
		foreach ($numbers as $num) {
			$requests[] = [
				"sender_id"         => $sender_id,
				"message"           => $template_id,
				"variables_values"  => $msg,
				"flash"             => 0,
				"numbers"           => $num
			];
		}
		$fields = json_encode([
			"route"    => "dlt",
			"requests" => $requests
		]);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL            => "https://www.fast2sms.com/dev/custom",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_POSTFIELDS     => $fields,
			CURLOPT_HTTPHEADER     => array(
				"authorization: $api_key",
				"cache-control: no-cache",
				"Content-Type: application/json"
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);

		$arr = json_decode($response);
		$status = isset($arr->return) && $arr->return == 1 ? 'Sent' : 'Failed';

		// Optional logging
		$this->db->insert(db_prefix() . '_smslogs', [
			'ltemplate_body'     => $message,
			'lnumbers'           => implode(',', $numbers),
			'ltemplate_status'   => $status,
			'lcredit_minus'      => ceil(strlen($message) / 160),
			'created_at'         => date('Y-m-d H:i:s'),
		]);

		// Optional credit deduction
		if ($status == 'Sent') {
			$this->db->set('ccredit', 'ccredit - ' . ceil(strlen($message) / 160), false);
			$this->db->update(db_prefix() . '_smscredits');
		}

		return $status == 'Sent';
	}


    public function send_whatsapp($phone, $template_name, $params) {
		log_message('debug', "[send_whatsapp] Sending WhatsApp template $template_name to $phone with params: " . json_encode($params));
		
		// Config values - ensure these are set as class variables or load from config
		$tenantId = $this->tenantId;
		$apiKey = $this->whatsapp_apiKey;
		$broadcastName = 'System Trigger'; // You can customize this label if needed

		// Format parameters: WhatsApp may expect named parameters (e.g., "name", "date", etc.)
		$parameters = [];
		foreach ($params as $index => $value) {
			$parameters[] = [
				'name'  => (string)($index + 1),  // Or change this to specific names like "name", "time" if required
				'value' => $value
			];
		}

		// Optional mapping of variable positions to meaningful names (WATI may expect 'name', 'date', etc.)
		foreach ($parameters as &$param) {
			if ($param['name'] == '1') {
				$param['name'] = 'name'; // Change as needed based on template variables
			}
			if ($param['name'] == '2') {
				$param['name'] = 'time'; // Example: second param is time
			}
			// Add more mappings if needed
		}

		// Prepare the data to send in the cURL request
		$payload = json_encode([
			"template_name"   => $template_name,
			"broadcast_name"  => $broadcastName,
			"parameters"      => $parameters,
		]);

		// Prepare URL
		$url = 'https://live-mt-server.wati.io/' . $tenantId . '/api/v1/sendTemplateMessage?whatsappNumber=' . urlencode($phone);

		// cURL setup
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => '',
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_POSTFIELDS     => $payload,
			CURLOPT_HTTPHEADER     => array(
				'accept: */*',
				'Authorization: Bearer ' . $apiKey,
				'Content-Type: application/json-patch+json'
			),
		));

		// Execute cURL
		$response = curl_exec($curl);

		// Error Handling
		if (curl_errno($curl)) {
			$error = curl_error($curl);
			log_message('error', "[send_whatsapp] cURL Error: $error");
			curl_close($curl);
			return false;
		}

		curl_close($curl);
		log_message('debug', "[send_whatsapp] API Response: $response");

		return json_decode($response, true);
	}

}
