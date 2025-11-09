<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp_Model extends App_Model
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
		
	}

}