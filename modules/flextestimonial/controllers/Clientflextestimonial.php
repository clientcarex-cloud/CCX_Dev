<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Clientflextestimonial extends ClientsController
{
    public function vt($slug)
    {
        $this->load->model('flextestimonial/flextestimonial_model');
        $testimonial = $this->flextestimonial_model->get(['slug' => $slug]);
        if (!$testimonial) {
            show_404();
        }
        $testimonial = $testimonial[0];
        $data['testimonial'] = $testimonial;
        if ($testimonial['active'] == 0) {
            show_404();
        }
        $this->data($data);
        //add body class
        //if the privacy is customers, we don't need to disable navigation
        $this->disableNavigation()
            ->disableSubMenu();
        $this->disableFooter();
        $this->title($testimonial['title']);
        no_index_customers_area();
        $this->app_css->theme('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'));
        $this->app_scripts->theme('flextestimonial-js', module_dir_url('flextestimonial', 'assets/js/client-flextestimonial.js'));
        $this->app_scripts->theme('flextestimonial-video-js', module_dir_url('flextestimonial', 'assets/js/video-recording.js'));
        $this->view('client/index');
        $this->layout();
    }
	
    public function dr($key)
	{
		$this->load->model('client/client_model');
		$patient_data = $this->client_model->get_shared_requests($id = null, $key);

		if ($patient_data) {
			$record = $patient_data[0];

			// Check if link has expired
			$date_sent = $record['date_sent'];
			$today = date('Y-m-d');
			if (!empty($date_sent) && $date_sent < $today) {
				$this->load->view('flextestimonial/link_expired');
				return;
			}

			$patient_name = ($record['type'] == "lead") ? $record['name'] : $record['company'];
			$userid = ($record['type'] == "lead") ? $record['id'] : $record['userid'];
			$request_id = $record['request_id'];
			$testimonial_slug = $record['testimonial_slug'];

		} else {
			$this->load->view('flextestimonial/link_expired');
			return;
		}

		$this->load->model('flextestimonial/flextestimonial_model');
		$testimonial = $this->flextestimonial_model->get(['slug' => $testimonial_slug]);

		if (!$testimonial || $testimonial[0]['active'] == 0) {
			$this->load->view('flextestimonial/link_expired');
			return;
		}

		$testimonial = $testimonial[0];
		$testimonial['userid'] = $userid;
		$testimonial['patient_name'] = $patient_name;
		$testimonial['request_id'] = $request_id;
		$data['testimonial'] = $testimonial;

		$this->data($data);

		$this->disableNavigation()
			->disableSubMenu();
		$this->disableFooter();
		$this->title($testimonial['title']);

		no_index_customers_area();

		$this->app_css->theme('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'));
		$this->app_scripts->theme('flextestimonial-js', module_dir_url('flextestimonial', 'assets/js/client-flextestimonial.js'));
		$this->app_scripts->theme('flextestimonial-video-js', module_dir_url('flextestimonial', 'assets/js/video-recording.js'));

		$this->view('client/index');
		$this->layout();
	}


    public function submit($slug)
    {
        if (!$this->input->is_ajax_request()) {
            redirect(site_url('flextestimonial/vt/' . $slug));
        }
        $this->load->model('flextestimonial/flextestimonialresponses_model');
        $this->load->model('flextestimonial/flextestimonial_model');
        $testimonial = $this->flextestimonial_model->get(['slug' => $slug]);
        $user_photo = "";
        $video_response = "";
        $images = [];
        $text_response = "";
        $email = "";
        $job_title = "";
        $company_name = "";
        $website_url = "";

        if (!$testimonial) {
            echo json_encode(['status' => 'error', 'message' => _l('flextestimonial_testimonial_not_found')]);
            exit();
        }
        $testimonial = $testimonial[0];

        //name is required
        $name = $this->input->post('name');
        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => _l('flextestimonial_name_required')]);
            exit();
        }

        //if enable_email is 1, then we need to check if the email is not empty
        if ($testimonial['enable_email'] == 1) {
            $email = $this->input->post('email');
            if (empty($email) && $testimonial['require_email'] == 1) {
                echo json_encode(['status' => 'error', 'message' => _l('flextestimonial_email_required')]);
                exit();
            }
        }

        //if enable_job_title is 1, then we need to check if the job_title is not empty
        if ($testimonial['enable_job_title'] == 1) {
            $job_title = $this->input->post('job_title');
            if (empty($job_title) && $testimonial['require_job_title'] == 1) {
                echo json_encode(['status' => 'error', 'message' => _l('flextestimonial_job_title_required')]);
                exit();
            }
        }

        //if enable_company_name is 1, then we need to check if the company_name is not empty
        if ($testimonial['enable_company_name'] == 1) {
            $company_name = $this->input->post('company_name');
            if (empty($company_name) && $testimonial['require_company_name'] == 1) {
                echo json_encode(['status' => 'error', 'message' => _l('flextestimonial_company_name_required')]);
                exit();
            }
        }

        //if enable_website_url is 1, then we need to check if the website_url is not empty
        if ($testimonial['enable_website_url'] == 1) {
            $website_url = $this->input->post('website_url');
            if (empty($website_url) && $testimonial['require_website_url'] == 1) {
                echo json_encode(['status' => 'error', 'message' => _l('flextestimonial_website_url_required')]);
                exit();
            }
        }
        //if enable_user_photo is 1, then we need to check if the user_photo is uploaded
        if ($testimonial['enable_user_photo'] == 1) {
            if (!isset($_FILES['user_photo']) && empty($_FILES['user_photo']) && $testimonial['require_user_photo'] == 1) {
                echo json_encode(['status' => 'error', 'message' => _l('flextestimonial_user_photo_required')]);
                exit();
            }
            //let us upload the user photo
            $user_photo = "";
            if (isset($_FILES['user_photo']) && $_FILES['user_photo']['size'] > 0) {
                $user_photo = $this->upload_image($_FILES['user_photo']);
            }
            if (empty($user_photo) && $testimonial['require_user_photo'] == 1) {
                echo json_encode(['status' => 'error', 'message' => _l('flextestimonial_user_photo_required')]);
                exit();
            }
        }
		
        //if enable_text_testimonial is 1, then we need to check if the text_response is not empty
        if ($testimonial['enable_text_testimonial'] == 1) {
            $text_response = $this->input->post('text_response');
            //it is possible that the text_response is empty, but the user has uploaded images or video
            if (empty($text_response) && empty($_FILES['images']) && empty($_FILES['video_response']) && empty($_FILES['video_file'])) {
                echo json_encode(['status' => 'error',
                'restart' => true,
                'message' => _l('flextestimonial_text_response_required')]);
                exit();
            }
        }
        //if enable_video_testimonial is 1,
        if ($testimonial['enable_video_testimonial'] == 1) {
            //let us upload the video response
            $video_response = "";
            if (isset($_FILES['video_response']) && $_FILES['video_response']['size'] > 0) {
                $video_response = $this->upload_video_response($_FILES['video_response']);
                if ($video_response == "") {
                    // Check if it's a file size error
                    echo json_encode(['status' => 'error',
                    'restart' => true,
                    'message' => _l('flextestimonial_video_response_required')]);
                    exit();
                }
            }
            //if the video file was uploaded but it is too large
            if (isset($_FILES['video_response']['error']) && $_FILES['video_response']['error'] == 1) {
                echo json_encode(['status' => 'error',
                'restart' => true,
                'message' => _l('flextestimonial_video_size_too_large')]);
                exit();
            }
            if (isset($_FILES['video_file']) && $_FILES['video_file']['size'] > 0) {
                $video_response = $this->upload_video_response($_FILES['video_file']);
                if ($video_response == "") {
                    // Check if it's a file size error
                    echo json_encode(['status' => 'error',
                    'restart' => true,
                    'message' => _l('flextestimonial_video_response_required')]);
                    exit();
                }
            }
            //if the video file was uploaded but it is too large
            if (isset($_FILES['video_file']['error']) && $_FILES['video_file']['error'] == 1) {
                echo json_encode(['status' => 'error',
                'restart' => true,
                'message' => _l('flextestimonial_video_size_too_large')]);
                exit();
            }
        }
        //if enable_image is 1, then we need to check if the images are uploaded
        if ($testimonial['enable_image'] == 1) {
            if (!isset($_FILES['images']) && empty($_FILES['images'])) {
                echo json_encode(['status' => 'error',
                'restart' => true,
                'message' => _l('flextestimonial_image_required')]);
                exit();
            }
            //let us upload the images
            $images = [];
            if (isset($_FILES['images'])) {
                $images = $this->upload_images($_FILES['images']);
            }
			
        }
        //if no images, video or text response, then we need to show an error
        if (!$images && !$video_response && !$text_response) {
            echo json_encode(['status' => 'error',
            'restart' => true,
            'message' => _flextestimonial_lang('no_content_provided_message')]);
            exit();
        }


        //if enable_rating is 1, then we need to check if the rating is not empty
        $rating = ($video_response) ? $this->input->post('video_rating') : $this->input->post('text_rating');
        $slug = md5(time() . $this->input->post('name'));
		
		$userid = $this->input->post('userid');
		
		$request_id = $this->input->post('request_id');
        //let us save the data
        $data = [
            'testimonial_id' => $testimonial['id'],
            'text_response' => $text_response,
            'request_id' => $request_id,
            'slug' => $slug,
            'video_url' => $video_response,
            'images' => flextestimonial_perfect_serialize($images),
            'name' => $this->input->post('name'),
            'email' => $email,
            'job_title' => $job_title,
            'company_name' => $company_name,
            'website_url' => $website_url,
            'user_photo' => $user_photo,
            'rating' => (int)$rating,
            'source' => 'website',
            'staff_id' => get_staff_user_id(),
            'client_id' => get_client_user_id(),
            'contact_id' => get_contact_user_id(),
            'status' => 'pending',
            'featured' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $response_id = $this->flextestimonialresponses_model->add($data);
        if ($response_id) {
            $response = $this->flextestimonialresponses_model->get(['id' => $response_id]);
            $html = $this->load->view('partials/display/thankyou', ['response' => $response[0], 'testimonial' => $testimonial], true);
            $CI = &get_instance();
            $CI->load->library(FLEXTESTIMONIAL_MODULE_NAME . '/Flextestimonial_module');
            //send submission notification email
            $emails_to_notify = $testimonial['notification_emails'];
            if ($emails_to_notify) {
                $emails_to_notification_arr = explode(',', $emails_to_notify);
                try {
                    $CI->flextestimonial_module->send_submission_notification_email($emails_to_notification_arr, $testimonial);
                } catch (Exception $e) {
                }
            }

            //send thank you email
            if ($email) {
                try {
                    $CI->flextestimonial_module->send_thank_you_email($email, $testimonial);
                } catch (Exception $e) {
                }
            }
			
			//Sending Custom Email, WhatsApp & SMS
			
			if($userid){
				$share_request = $this->db->get_where(db_prefix() . 'share_request', array("feedback_id"=>$testimonial['id'], "user_id"=>$userid, "type"=>'patient'))->row();
				if($share_request){
					$id = $share_request->id;
					$data = array(
					"status" => 'Completed'
					); 
					$this->db->where(array("id"=>$id));
					$this->db->update(db_prefix() . 'share_request', $data);
				}
				$this->load->model('client/client_model');
				$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"branch_address" => $branch_address,
				);
				$this->client_model->patient_journey_log_event($userid, 'feedback_auto_reply', 'Feedback auto reply', $communication_data);
			}
			 
			
			
            echo json_encode(['status' => 'success', 'message' => _l('flextestimonial_testimonial_submitted_successfully'), 'html' => $html]);
        } else {
            echo json_encode(['status' => 'error', 'message' => _l('flextestimonial_testimonial_submission_failed')]);
        }
        exit();
    }

    private function upload_image($file)
    {
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); 
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp','JPG', 'JPEG', 'PNG', 'GIF', 'SVG', 'WEBP'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            return '';
        }
        $outputFileSaveableName = md5(time() . $file['name']) . '.' . $fileExtension;
        $outputFileName = FLEXTESTIMONIAL_FOLDER . $outputFileSaveableName;
        if (move_uploaded_file($file['tmp_name'], $outputFileName)) {
            return $outputFileSaveableName;
        }
        return '';
    }

    private function upload_video_response($file)
    {
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['mp4', 'mov', 'avi', 'wmv', 'flv', 'webm'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            return '';
        }
        
        $outputFileSaveableName = md5(time() . $file['name']) . '.' . $fileExtension;
        $outputFileName = FLEXTESTIMONIAL_FOLDER . $outputFileSaveableName;
        if (move_uploaded_file($file['tmp_name'], $outputFileName)) {
            return $outputFileSaveableName;
        }
        return '';
    }

    private function upload_images($files)
    {	
        $fileArray = $this->normalizeFilesArray($files);
        $outputFiles = [];
        foreach ($fileArray as $file) {
            $outputFiles[] = $this->upload_image($file);
        }
        return $outputFiles;
    }

    private function normalizeFilesArray($files): array
    {
        $normalized = [];

        if (is_array($files['name'])) {
            // Multiple files
            for ($i = 0; $i < count($files['name']); $i++) {
                $normalized[] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
            }
        } else {
            // Single file
            $normalized[] = $files;
        }

        return $normalized;
    }

    /*
    *Each response page
    *@param string $slug
    */

    public function r($slug)
    {
        $this->load->model('flextestimonial/flextestimonialresponses_model');
        $response = $this->flextestimonialresponses_model->get(['slug' => $slug]);
        if (!$response) {
            show_404();
        }
        $response = $response[0];
        //get the testimonial
        $this->load->model('flextestimonial/flextestimonial_model');
        $testimonial = $this->flextestimonial_model->get(['id' => $response['testimonial_id']]);
        $testimonial = $testimonial[0];
        $this->data(['response' => $response, 'testimonial' => $testimonial]);
        $this->view('client/response');
        //if the privacy is customers, we don't need to disable navigation
        $this->disableNavigation()
            ->disableSubMenu();
        $this->disableFooter();
        $this->title(_flextestimonial_lang('testimonial'));
        no_index_customers_area();
        $this->app_css->theme('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'));
        $this->app_scripts->theme('flextestimonial-js', module_dir_url('flextestimonial', 'assets/js/client-flextestimonial.js'));
        $this->layout();
    }

    public function wall_of_love()
    {
        $this->load->model('flextestimonial/flextestimonialresponses_model');
        $responses = $this->flextestimonialresponses_model->get_featured();
        $this->data(['responses' => $responses]);
        $this->view('client/wall_of_love');
        // $this->disableNavigation()
        //     ->disableSubMenu();
        // $this->disableFooter();
        // no_index_customers_area();
        $this->title(_flextestimonial_lang('wall_of_love'));
        $this->app_css->theme('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'));
        $this->app_scripts->theme('masonry-js', module_dir_url('flextestimonial', 'assets/js/masonry.pkgd.min.js'));
        $this->app_scripts->theme('imagesloaded-js', module_dir_url('flextestimonial', 'assets/js/imagesloaded.pkgd.min.js'));
        $this->app_scripts->theme('flextestimonial-js', module_dir_url('flextestimonial', 'assets/js/client-flextestimonial.js'));
        $this->layout();
    }

    public function more_responses()
    {
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $this->load->model('flextestimonial/flextestimonialresponses_model');
        $responses = $this->flextestimonialresponses_model->get_featured($limit, $offset);
        $html = '';
        if ($responses) {
            foreach ($responses as $response) {
                $html .= "<div class='tw-grid-item tw-bg-white tw-rounded-lg tw-shadow-lg tw-p-6 tw-w-full tw-mb-6'>";
                $html .= $this->load->view('partials/response-card', ['response' => $response], true);
                $html .= "</div>";
            }
        }
        echo json_encode(['status' => 'success', 'html' => $html]);
    }
}
