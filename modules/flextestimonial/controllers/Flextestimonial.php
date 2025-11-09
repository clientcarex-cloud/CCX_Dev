<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('flextestimonial_model');
    }

    public function index()
    {
        $this->load->model('flextestimonialresponses_model');
        $data['title'] = _flextestimonial_lang('testimonials_forms');
        //get all testimonials from testimonails model
        $forms = $this->flextestimonial_model->get();
        $all_forms = [];
        foreach ($forms as $form) {
            $form['testimonials_count'] = count($this->flextestimonialresponses_model->get(['testimonial_id' => $form['id']]));
            $all_forms[] = $form;
        }
        $data['forms'] = $all_forms;
        //load js
        $this->app_scripts->add('flextestimonial-js', module_dir_url('flextestimonial', 'assets/js/flextestimonial.js'), 'admin', ['app-js']);
        $this->load->view('index', $data);
    }

    //create testimonial
    public function create()
    {
        //if the request is post  
        //post title is required
        if ($this->input->post()) {
            if (!$this->input->post('title')) {
                set_alert('error', _flextestimonial_lang('title_required'));
                redirect(admin_url('flextestimonial'));
            }
            $slug = slug_it($this->input->post('title'));
            //check if the slug is already in use
            if ($this->flextestimonial_model->get(['slug' => $slug])) {
                $slug = $slug . '-' . uniqid();
            }
            $data = [
                'title' => $this->input->post('title'),
                'slug' => $slug,
                'description' => $this->input->post('description'),
                //set default
                'primary_color' => "#1c5be8",
                'background_color' => "#fff",
                'enable_gradient' => "1",
                'welcome_title' => _flextestimonial_lang('share_a_testimonial'),
                'welcome_message' => _flextestimonial_lang('share_a_testimonial_message'),
                'enable_video_testimonial' => "1",
                'enable_text_testimonial' => "1",
                'welcome_video_url' => "",
                'response_prompt' => _flextestimonial_lang('what_did_you_think'),
                'enable_logo' => "1",
                'enable_rating' => "1",
                'enable_image' => "1",
                'enable_email' => "1",
                'require_email' => "1",
                'enable_job_title' => "1",
                'require_job_title' => "1",
                'enable_user_photo' => "1",
                'require_user_photo' => "1",
                'enable_website_url' => "1",
                'require_website_url' => "1",
                'enable_company_name' => "1",
                'require_company_name' => "1",
                'thankyou_title' => _flextestimonial_lang('thank_you_title'),
                'thankyou_message' => _flextestimonial_lang('thank_you_message'),
                'thankyou_button_text' => "",
                'thankyou_button_url' => "",
                'enable_social_share' => "0",
                'record_a_video_button_label' => _flextestimonial_lang('record_a_video_button_label_default'),
                'write_a_testimonial_button_label' => _flextestimonial_lang('write_a_testimonial_button_label_default'),
                'upload_image_button_label' => _flextestimonial_lang('upload_image_button_label_default'),
                'marketing_consent_label' => _flextestimonial_lang('marketing_consent_label_default'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            //check if the slug is already in use
            if ($this->flextestimonial_model->get(['slug' => $data['slug']])) {
                //if it exists, add a random number to the slug
                $data['slug'] = $data['slug'] . '-' . uniqid();
            }
            $this->flextestimonial_model->add($data);
            set_alert('success', _flextestimonial_lang('testimonial_created'));
            redirect(admin_url('flextestimonial/manage/' . $data['slug']));
        }
        //redirect to the index page
        redirect(admin_url('flextestimonial'));
    }

    //update testimonial
    public function update($id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect(admin_url('flextestimonial/manage/' . $id));
        }
        //check if the testimonial exists
        $testimonial = $this->flextestimonial_model->get(['slug' => $id]);
        if (!$testimonial) {
            //return json error
            echo json_encode(['success' => false, 'message' => _flextestimonial_lang('testimonial_not_found')]);
            return;
        }
        $id = $testimonial[0]['id'];
        $data = [
            'title' => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'primary_color' => $this->input->post('primary_color'),
            'background_color' => $this->input->post('background_color'),
            'enable_gradient' => $this->input->post('enable_gradient'),
            'active' => $this->input->post('active'),
            'welcome_title' => $this->input->post('welcome_title'),
            'welcome_message' => $this->input->post('welcome_message'),
            'response_prompt' => $this->input->post('response_prompt'),
            'enable_rating' => $this->input->post('enable_rating'),
            'enable_image' => $this->input->post('enable_image'),
            'enable_email' => $this->input->post('enable_email'),
            'require_email' => $this->input->post('require_email'),
            'enable_job_title' => $this->input->post('enable_job_title'),
            'require_job_title' => $this->input->post('require_job_title'),
            'enable_user_photo' => $this->input->post('enable_user_photo'),
            'require_user_photo' => $this->input->post('require_user_photo'),
            'enable_website_url' => $this->input->post('enable_website_url'),
            'require_website_url' => $this->input->post('require_website_url'),
            'enable_company_name' => $this->input->post('enable_company_name'),
            'require_company_name' => $this->input->post('require_company_name'),
            'thankyou_title' => $this->input->post('thankyou_title'),
            'thankyou_message' => $this->input->post('thankyou_message'),
            'thankyou_button_text' => $this->input->post('thankyou_button_text'),
            'thankyou_button_url' => $this->input->post('thankyou_button_url'),
            'enable_social_share' => $this->input->post('enable_social_share'),
            'record_a_video_button_label' => $this->input->post('record_a_video_button_label'),
            'write_a_testimonial_button_label' => $this->input->post('write_a_testimonial_button_label'),
            'upload_image_button_label' => $this->input->post('upload_image_button_label'),
            'marketing_consent_label' => $this->input->post('marketing_consent_label'),
            'enable_video_testimonial' => $this->input->post('enable_video_testimonial'),
            'enable_text_testimonial' => $this->input->post('enable_text_testimonial'),
            'notification_emails' => $this->input->post('notification_emails'),
            'enable_logo' => $this->input->post('enable_logo'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->flextestimonial_model->update($id, $data);
        //return json success
        //get the latest testimonial
        $testimonial = $this->flextestimonial_model->get(['id' => $id]);
        $html = $this->load->view('display', ['testimonial' => $testimonial[0], 'active_panel' => $this->input->post('active_panel')], true);
        echo json_encode(['success' => true, 'message' => _flextestimonial_lang('testimonial_updated'), 'html' => $html]);
    }

    //manage testimonial
    public function manage($slug)
    {
        $testimonial = $this->flextestimonial_model->get(['slug' => $slug]);
        //check if the testimonial exists
        if (!$testimonial) {
            set_alert('error', _flextestimonial_lang('testimonial_not_found'));
            redirect(admin_url('flextestimonial'));
        }
        $data['is_manage'] = true;
        $data['testimonial'] = $testimonial[0];
        $data['title'] = _flextestimonial_lang('manage') . ' ' . $data['testimonial']['title'];
        //load js and css for the manage page
        $this->app_css->add('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flextestimonial-js', module_dir_url('flextestimonial', 'assets/js/flextestimonial.js'), 'admin', ['app-js']);
        $this->load->view('manage', $data);
    }

    public function responses($slug)
    {
        $testimonial = $this->flextestimonial_model->get(['slug' => $slug]);
        if (!$testimonial) {
            set_alert('error', _flextestimonial_lang('testimonial_not_found'));
            redirect(admin_url('flextestimonial'));
        }
        $this->load->model('flextestimonialresponses_model');
        $data['title'] = _flextestimonial_lang('responses') . ' ' . $testimonial[0]['title'];
        $data['testimonial'] = $testimonial[0];
        $data['responses'] = $this->flextestimonialresponses_model->get(['testimonial_id' => $testimonial[0]['id']]);
        $this->app_css->add('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flextestimonial-js', module_dir_url('flextestimonial', 'assets/js/flextestimonial.js'), 'admin', ['app-js']);
        $this->load->view('responses', $data);
    }

    public function delete_response($id)
    {
        //check permission
        if (!has_permission(FLEXTESTIMONIAL_MODULE_NAME, '', 'delete')) {
            set_alert('error', _flextestimonial_lang('permission_denied'));
            redirect(admin_url('flextestimonial'));
        }
        $this->load->model('flextestimonialresponses_model');
        $response = $this->flextestimonialresponses_model->get(['id' => $id]);
        if (!$response) {
            set_alert('error', _flextestimonial_lang('response_not_found'));
            redirect(admin_url('flextestimonial'));
        }
        $response = $response[0];
        //testimonial id
        $testimonial_id = $response['testimonial_id'];
        $testimonial = $this->flextestimonial_model->get(['id' => $testimonial_id]);
        if (!$testimonial) {
            set_alert('error', _flextestimonial_lang('testimonial_not_found'));
            redirect(admin_url('flextestimonial'));
        }
        $testimonial = $testimonial[0];
        //if the reponse has have video, delete the video
        if ($response['video_url']) {
            //unlink the video
            $video_path = flextestimonial_media_path($response['video_url']);
            if (file_exists($video_path)) {
                unlink($video_path);
            }
        }
        //if the reponse has have images, delete the images
        if ($response['images']) {
            $images = flextestimonial_perfect_unserialize($response['images']);
            foreach ($images as $image) {
                if ($image && file_exists(flextestimonial_media_path($image))) {
                    unlink(flextestimonial_media_path($image));
                }
            }
        }
        $this->flextestimonialresponses_model->delete(['id' => $id]);
        set_alert('success', _flextestimonial_lang('response_deleted'));
        redirect(admin_url('flextestimonial/responses/' . $testimonial['slug']));
    }

    public function ajax()
    {
        $action = $this->input->post('action');
        $panel_id = $this->input->post('panel_id');
        $testimonial_slug = $this->input->post('slug');
        $testimonial = $this->flextestimonial_model->get(['slug' => $testimonial_slug]);
        switch ($action) {
            case 'get_preview':
                $partial = 'partials/display/welcome';
                if ($panel_id == 'collapseresponse_prompt') {
                    $partial = 'partials/display/text-response';
                } elseif ($panel_id == 'collapsevideo_testimonial') {
                    $partial = 'partials/display/video-response';
                } elseif ($panel_id == 'collapsecustomer_details') {
                    $partial = 'partials/display/customer-info';
                } elseif ($panel_id == 'collapsethankyou_page' || $panel_id == 'collapseword_of_mouth') {
                    $partial = 'partials/display/thankyou';
                }
                $html = $this->load->view($partial, ['testimonial' => $testimonial[0]], true);
                echo json_encode(['success' => true, 'html' => $html]);
                break;

            case 'update_response_featured':
                $this->load->model('flextestimonialresponses_model');
                $this->flextestimonialresponses_model->update($this->input->post('id'), ['featured' => $this->input->post('featured')]);
                echo json_encode(['success' => true, 'message' => _flextestimonial_lang('response_featured_updated')]);
                break;
        }
    }

    //delete testimonial
    public function delete($slug)
    {
        //check permission
        if (!has_permission('flextestimonial', '', 'delete')) {
            set_alert('error', _flextestimonial_lang('permission_denied'));
            redirect(admin_url('flextestimonial'));
        }
        //check if the testimonial exists
        if (!$this->flextestimonial_model->get(['slug' => $slug])) {
            set_alert('error', _flextestimonial_lang('testimonial_not_found'));
            redirect(admin_url('flextestimonial'));
        }
        $this->flextestimonial_model->delete(['slug' => $slug]);
        set_alert('success', _flextestimonial_lang('testimonial_deleted'));
        redirect(admin_url('flextestimonial'));
    }

    public function import()
    {
        if ($this->input->post()) {
            $testimonial_form_id = $this->input->post('testimonial_form_id');
            $testimonial_form = $this->flextestimonial_model->get(['id' => $testimonial_form_id]);
            $message = '';
            if (!$testimonial_form) {
                $message = _flextestimonial_lang('testimonial_form_not_found');
                $data['title'] = _flextestimonial_lang('import_testimonials');
                $data['forms'] = $this->flextestimonial_model->get();
                $data['message'] = $message;
                $this->app_css->add('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'), 'admin', ['app-css']);
                $this->load->view('import', $data);
                return;
            }

            $testimonial_form = $testimonial_form[0];
            $testimonial_form_slug = $testimonial_form['slug'];
            $import_file = $_FILES['import_file'];
            if (!$import_file || $import_file['size'] == 0) {
                $message = _flextestimonial_lang('import_file_not_found');
                $data['title'] = _flextestimonial_lang('import_testimonials');
                $data['forms'] = $this->flextestimonial_model->get();
                $data['message'] = $message;
                $this->app_css->add('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'), 'admin', ['app-css']);
                $this->load->view('import', $data);
                return;
            }

            //Check if the file is a csv file
            if ($import_file['type'] != 'text/csv') {
                $message = _flextestimonial_lang('invalid_file_type');
                $data['title'] = _flextestimonial_lang('import_testimonials');
                $data['forms'] = $this->flextestimonial_model->get();
                $data['message'] = $message;
                $this->app_css->add('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'), 'admin', ['app-css']);
                $this->load->view('import', $data);
                return;
            }
            $this->load->model('flextestimonialresponses_model');
            //let insert into the responses table based on the each row in the csv file
            $csv_data = array_map('str_getcsv', file($import_file['tmp_name']));
            $i = 0;
            foreach ($csv_data as $row) {
                //skip the first row
                if ($i == 0) {
                    $i++;
                    continue;
                }
                $slug = slug_it(md5(time().$row[0]));
                $data = [
                    'testimonial_id' => $testimonial_form_id,
                    'name' => $row[0],
                    'slug' => $slug,
                    'email' => $row[1],
                    'user_photo' => $row[2],
                    'text_response' => $row[3],
                    'rating' => $row[4],
                    'job_title' => $row[5],
                    'source' => $row[6],
                    'video_url' => !empty($row[8]) ? $row[8] : '',
                    'website_url' => $row[9],
                    'created_at' => date('Y-m-d H:i:s',strtotime($row[7])),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->flextestimonialresponses_model->add($data);
            }
            set_alert('success', _flextestimonial_lang('import_success'));
            redirect(admin_url('flextestimonial/responses/' . $testimonial_form_slug));
        }else{
            $data['title'] = _flextestimonial_lang('import_testimonials');
            $data['forms'] = $this->flextestimonial_model->get();
            $this->app_css->add('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'), 'admin', ['app-css']);
            $this->load->view('import', $data);
        }
    }

    //settings
    public function settings()
    {
        $data['title'] = _flextestimonial_lang('testimonial_automation_settings');
        if($post = $this->input->post()){
            update_option('flextestimonial_for_projects', $post['flextestimonial_for_projects']);
            update_option('flextestimonial_for_invoices', $post['flextestimonial_for_invoices']);
            update_option('flextestimonial_for_tickets', $post['flextestimonial_for_tickets']);
            
            set_alert('success', _flextestimonial_lang('settings_updated'));
            redirect(admin_url('flextestimonial/settings'));
        }
        $data['forms'] = $this->flextestimonial_model->get();
        $this->app_css->add('flextestimonial-css', module_dir_url('flextestimonial', 'assets/css/flextestimonial.css'), 'admin', ['app-css']);
        $this->load->view('settings', $data);
    }
}
