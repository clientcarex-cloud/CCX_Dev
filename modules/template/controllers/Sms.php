<?php

defined('BASEPATH') or exit('No direct script access allowed');
   
class Sms extends AdminController
{
	private $api_key;
	private $sender_id;

    public function __construct()
    {
        parent::__construct();
		$this->sender_id = 'OBEX' ;
        $this->api_key =  'mdvy0nEJ7fOHAcoS5ZjLaMgl8krhpQ6CsWbwtGxeUF94uRIXN2BzwPfN5QKidDmYjRUG3oycu2l6L7hS';
		$this->load->model('Office_models');
    }
    
    public function index(){
    
		$smsbalance = $this->get_sms_balance(); // Call a private helper method (see below)

		$data['title'] = 'SMS Spark';
		$data['smsbalance'] = $smsbalance;

		$this->load->view('index', $data);
    
    }
    
    public function ajax_data(){
     $id = $this->input->post('id'); 
     $data=$this->Office_models->get_body($id);
     echo json_encode($data);
     //print_r($data);die('===');
    
    }
    
    
        
    public function ajax_data_edit(){
     $id = $this->input->post('id'); 
     $data=$this->Office_models->get_body_edit($id);
     echo json_encode($data);
     //print_r($data);die('===');
    
    }
    
    public function num_count() {
       $input = $this->input->post('phonenumber');
       $values = explode(",", $number);
       $count = count($values);
       echo $count;
   }
    
  
	public function old_send_sms(){
            
        $number = $this->input->post('phonenumber');
		$sender_id = $this->sender_id;
		$api_key = $this->api_key;
         
         $phone = explode(",",$number);
         
         
         for($i=0;$i<count($phone);$i++){
        
        $template_id = $this->input->post('template_id');
        
       $phonenumber = $phone[$i];
        
         $message = $this->input->post('message');
   
      preg_match_all('/{(.*?)}/', $message, $matches);
      
      $text='';
       foreach ($matches[1] as $a ){
      $text .=   $a."|";
       }
   
       $msg=  rtrim($text,"|");
       //die('===');
     
        $fields = array(
        "sender_id" => "$sender_id",
        "message" => "$template_id",
        "variables_values" => "$msg",
        "route" => "dlt",
        "numbers" => "$number",
        );
        $curl = curl_init();     
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($fields),
        CURLOPT_HTTPHEADER => array(
        "authorization: $api_key",
        "accept: */*",
        "cache-control: no-cache",
        "content-type: application/json"
        ),
        ));
        
        
        $response = curl_exec($curl);
		curl_close($curl);
		$arr = json_decode($response);

		$status = 'Sent';
		if($arr->return != 1) {
			$status = 'Failed';
			$this->session->set_userdata('smsnotsent', '1');
		} else {
			$this->session->set_userdata('smssent', '1');
			$text = $this->input->post('message');
		//
		$number = $this->input->post('phonenumber');   
		$phone = explode(",",$number);
		$total_numbers = count($phone);

		for($i=0;$i<$total_numbers;$i++){
			// your existing code here
			// ...
			$text = $this->input->post('message');
			$characters_count = strlen($text);
			$credits = ceil($characters_count / 160);
			$total_credits = $credits * $total_numbers;

			// update the credit
			$this->db->set('ccredit', 'ccredit-'.$total_credits, FALSE);
			$this->db->update(db_prefix().'_smscredits'); }
		//
		}

		$data = array(
			'ltemplate_id' => $this->input->post('template_id'),
			'ltemplate_body' => $this->input->post('message'),
			'lnumbers' => $this->input->post('phonenumber'),
			'ltemplate_status' => $status,
			'lcredit_minus' => $total_credits,
			'created_at' => date('Y-m-d H:i:s'),
		);
		$this->db->insert(db_prefix().'_smslogs', $data);
		redirect($_SERVER['HTTP_REFERER']);
        
        }
        }
        
        
        
	private function get_sms_balance()
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://www.fast2sms.com/dev/wallet",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_HTTPHEADER => array(
				"authorization: {$this->api_key}"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			return '0.00'; // Default fallback
		}

		$decoded = json_decode($response, true);
		return isset($decoded['wallet']) ? $decoded['wallet'] : '0.00';
	}
	
	public function send_sms()
	{
		$api_key = $this->api_key;
		$sender_id = $this->sender_id;
		$template_id = $this->input->post('template_id');
		$message = $this->input->post('message');
		
		// Parse variables from message template
		preg_match_all('/{(.*?)}/', $message, $matches);
		$text = '';
		foreach ($matches[1] as $match) {
			$text .= $match . '|';
		}
		$msg = rtrim($text, '|');

		$requests = [];
		$total_credits = 0;

		// Assume form posts arrays: numbers[], values1[], values2[], ...
		$numbers = $this->input->post('phonenumber'); // Array of phone numbers
		$numbers = explode(",",$numbers);
		foreach ($numbers as $i => $num) {
			$requests[] = [
				"sender_id" => $sender_id,
				"message" => $template_id,
				"variables_values" => $msg,
				"flash" => 0,
				"numbers" => $num
			];

			$total_credits += ceil(strlen($message) / 160); // Assume 1 credit per SMS length
		}

		$fields = json_encode([
			"route" => "dlt",
			"requests" => $requests
		]);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://www.fast2sms.com/dev/custom",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $fields,
			CURLOPT_HTTPHEADER => array(
				"authorization: $api_key",
				"cache-control: no-cache",
				"Content-Type: application/json"
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		$arr = json_decode($response);
		
		$status = ($arr->return == 1) ? 'Sent' : 'Failed';
		$this->session->set_userdata($status == 'Sent' ? 'smssent' : 'smsnotsent', '1');

		// Deduct credit
		if ($status == 'Sent') {
			$this->db->set('ccredit', 'ccredit-' . $total_credits, FALSE);
			$this->db->update(db_prefix() . '_smscredits');
		}

		// Log
		$data = array(
			'ltemplate_id' => $template_id,
			'ltemplate_body' => $message,
			'lnumbers' => implode(',', $numbers),
			'ltemplate_status' => $status,
			'lcredit_minus' => $total_credits,
			'created_at' => date('Y-m-d H:i:s'),
		);
		$this->db->insert(db_prefix() . '_smslogs', $data);

		redirect($_SERVER['HTTP_REFERER']);
	}
	public function send_sms_template($template_constant, $mobile, $vars)
	{
		$api_key = $this->api_key;
		$sender_id = $this->sender_id;
		$template_id = $this->input->post('template_id');
		$message = $this->input->post('message');
		
		exit();
		
		// Parse variables from message template
		preg_match_all('/{(.*?)}/', $message, $matches);
		$text = '';
		foreach ($matches[1] as $match) {
			$text .= $match . '|';
		}
		$msg = rtrim($text, '|');

		$requests = [];
		$total_credits = 0;

		// Assume form posts arrays: numbers[], values1[], values2[], ...
		$numbers = $this->input->post('phonenumber'); // Array of phone numbers
		$numbers = explode(",",$numbers);
		foreach ($numbers as $i => $num) {
			$requests[] = [
				"sender_id" => $sender_id,
				"message" => $template_id,
				"variables_values" => $msg,
				"flash" => 0,
				"numbers" => $num
			];

			$total_credits += ceil(strlen($message) / 160); // Assume 1 credit per SMS length
		}

		$fields = json_encode([
			"route" => "dlt",
			"requests" => $requests
		]);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://www.fast2sms.com/dev/custom",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $fields,
			CURLOPT_HTTPHEADER => array(
				"authorization: $api_key",
				"cache-control: no-cache",
				"Content-Type: application/json"
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		$arr = json_decode($response);
		
		$status = ($arr->return == 1) ? 'Sent' : 'Failed';
		$this->session->set_userdata($status == 'Sent' ? 'smssent' : 'smsnotsent', '1');

		// Deduct credit
		if ($status == 'Sent') {
			$this->db->set('ccredit', 'ccredit-' . $total_credits, FALSE);
			$this->db->update(db_prefix() . '_smscredits');
		}

		// Log
		$data = array(
			'ltemplate_id' => $template_id,
			'ltemplate_body' => $message,
			'lnumbers' => implode(',', $numbers),
			'ltemplate_status' => $status,
			'lcredit_minus' => $total_credits,
			'created_at' => date('Y-m-d H:i:s'),
		);
		$this->db->insert(db_prefix() . '_smslogs', $data);

		redirect($_SERVER['HTTP_REFERER']);
	}


        
}
