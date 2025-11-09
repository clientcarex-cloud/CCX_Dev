<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function override_staff_select_branch_field($html, $field)
{
	log_message('debug', 'Custom field hook executed for field slug: ' . $field['slug']);

    if ($field['slug'] == 'staff_select_branch' && $field['fieldto'] == 'staff') {
        $field_id = 'custom_fields[' . $field['id'] . ']';

        $html = '<select id="' . $field_id . '" name="' . $field_id . '" class="form-control" data-custom-field-value="' . html_escape($field['value']) . '"></select>';
        $html .= '<script>
            $(function() {
                var select = $("#' . $field_id . '");
                $.ajax({
                    url: "' . admin_url('client/get_dynamic_options') . '",
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        select.empty();
                        $.each(data, function(i, item) {
                            select.append($("<option>").val(item.value).text(item.label));
                        });

                        var selected = select.attr("data-custom-field-value");
                        if (selected) {
                            select.val(selected);
                        }
                    },
                    error: function() {
                        console.error("Could not load dynamic options.");
                    }
                });
            });
        </script>';
    }

    return $html;
}


if (!function_exists('mailflow_send_email')) {
    function mailflow_send_email($email, $subject, $message, $smtp_integration = 'system')
    {
       
        if (defined('DEMO') && DEMO) {
            return true;
        }
		
		$CI = &get_instance();

		$cnf = [
			'from_email' => get_option('smtp_email'),
			'from_name' => get_option('companyname'),
			'email' => $email,
			'subject' => $subject,
			'message' => $message,
		];

		$template = new StdClass();
		$template->message = get_option('email_header') . $cnf['message'] . get_option('email_footer');
		$template->fromname = $cnf['from_name'];
		$template->subject = $cnf['subject'];

		$template = parse_email_template($template);

		$cnf['message'] = $template->message;
		$cnf['from_name'] = $template->fromname;
		$cnf['subject'] = $template->subject;

		$cnf['message'] = check_for_links($cnf['message']);

		$cnf = hooks()->apply_filters('before_send_simple_email', $cnf);

		if (isset($cnf['prevent_sending']) && $cnf['prevent_sending'] == true) {
			return false;
		}

		$CI->load->config('email');

		if (!empty($smtp_integration) && $smtp_integration !== 'system') {

			$CI->load->model('mailflow/mailflow_model');
			$integrationData = $CI->mailflow_model->getIntegration($smtp_integration);

			$emailConfig = $CI->config->item('email');

			$emailConfig['useragent'] = 'phpmailer';
			$emailConfig['protocol'] = 'smtp';
			$emailConfig['smtp_host'] = trim($integrationData->smtp_host);

			if ($integrationData->smtp_username == '') {
				$emailConfig['smtp_user'] = trim($integrationData->email);
			} else {
				$emailConfig['smtp_user'] = trim($integrationData->smtp_username);
			}

			$emailConfig['smtp_pass'] = get_instance()->encryption->decrypt($integrationData->smtp_password);
			$emailConfig['smtp_port'] = trim($integrationData->smtp_port);
			$emailConfig['smtp_crypto'] = $integrationData->email_encryption;

			$charset = strtoupper($integrationData->email_charset);
			$charset = trim($charset);
			if ($charset == '' || strcasecmp($charset, 'utf8') == 'utf8') {
				$charset = 'utf-8';
			}

			$emailConfig['charset'] = $charset;

			$CI->email->initialize($emailConfig);
		}

		$CI->email->clear(true);
		$CI->email->set_newline(config_item('newline'));
		$CI->email->from($cnf['from_email'], $cnf['from_name']);
		$CI->email->to($cnf['email']);

		$bcc = '';
		// Used for action hooks
		if (isset($cnf['bcc'])) {
			$bcc = $cnf['bcc'];
			if (is_array($bcc)) {
				$bcc = implode(', ', $bcc);
			}
		}

		$systemBCC = get_option('bcc_emails');
		if ($systemBCC != '') {
			if ($bcc != '') {
				$bcc .= ', ' . $systemBCC;
			} else {
				$bcc .= $systemBCC;
			}
		}
		if ($bcc != '') {
			$CI->email->bcc($bcc);
		}

		if (isset($cnf['cc'])) {
			$CI->email->cc($cnf['cc']);
		}

		if (isset($cnf['reply_to'])) {
			$CI->email->reply_to($cnf['reply_to']);
		}

		$CI->email->subject($cnf['subject']);
		$CI->email->message($cnf['message']);

		$CI->email->set_alt_message(strip_html_tags($cnf['message'], '<br/>, <br>, <br />'));

		if ($CI->email->send()) {
			log_activity('Email sent to: ' . $cnf['email'] . ' Subject: ' . $cnf['subject']);

			return true;
		}

		return false;
    }
}

