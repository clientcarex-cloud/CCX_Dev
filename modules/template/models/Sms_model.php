    <?php
    
    defined('BASEPATH') or exit('No direct script access allowed');
    
    class Sms_Model extends App_Model
    {
		private $api_key;
		private $sender_id;
		public function __construct()
		{
			parent::__construct();
			$this->sender_id = 'OBEX' ;
			$this->api_key =  'mdvy0nEJ7fOHAcoS5ZjLaMgl8krhpQ6CsWbwtGxeUF94uRIXN2BzwPfN5QKidDmYjRUG3oycu2l6L7hS';
		}
    
		public function send_sms_template($template_constant, $mobile, $vars)
		{
			
			$api_key = $this->api_key;
			$sender_id = $this->sender_id;
			$check_template = $this->db->get_where(db_prefix() . '_templates', array("constant"=>$template_constant, "template_status"=>"Active"))->row();
			if($check_template){
				$template_id = $check_template->template_id;
				// Parse variables from message template
				$msg = implode('|', $vars);

				$requests = [];
				$total_credits = 0;

				$numbers = explode(",",$mobile);
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
			}
			
			

			redirect($_SERVER['HTTP_REFERER']);
		}
   
    }