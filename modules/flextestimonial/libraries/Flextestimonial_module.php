<?php

class Flextestimonial_module{

    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('flextestimonial/flextestimonial_model');
        $this->ci->load->model('flextestimonial/flextestimonialresponses_model');
    }

    public function send_thank_you_email($email, $testimonial){
        //we are not attaching pdf
        $template_name = 'flextestimonial_thank_you';
        $template = mail_template($template_name, "flextestimonial", $email, $testimonial);
        $template->send();
    }

    public function send_submission_notification_email($emails, $testimonial){
        $template_name = 'flextestimonial_submission_notification';
        foreach($emails as $email){
            $template = mail_template($template_name, "flextestimonial", $email, $testimonial);
            $template->send();
        }
    }

    public function create_thank_you_email_template(){
        $template_name = 'flextestimonial-thank-you';
        $template_message = 'Hello, <br/><br/> Thank you for submitting a testimonial. <br/><br/> Regards';
        create_email_template('Thank You for Submitting a Testimonial', $template_message,'staff', 'Thank You Email Template for Testimonial', $template_name);
    }

    public function create_submission_notification_email_template(){
        $template_name = 'flextestimonial-submission-notification';
        $template_message = 'Hello, <br/><br/> A new testimonial has been submitted for <a href="{testimonial_url}">{testimonial_name}</a>. <br/><br/> Regards';
        create_email_template('New Testimonial Submission', $template_message,'staff', 'Submission Notification Email Template for Testimonial', $template_name);
    }

    public function send_testimonial_request_email_tickets($email, $testimonial,$ticket){
        $template_name = 'flextestimonial_testimonial_request_tickets';
        $template = mail_template($template_name, "flextestimonial", $email, $testimonial,$ticket);
        $template->send();
    }

    public function create_testimonial_request_email_template_tickets(){
        $template_name = 'flextestimonial-testimonial-request-tickets';
        $template_message = 'Hello, <br/><br/> Your ticket has now been closed. <br/><br/> We would love to hear from you, please submit a testimonial for your experience. <br/><br/> 
        <a href="{testimonial_url}">Submit a Testimonial</a> <br/><br/> Regards';
        create_email_template('Your Ticket has been Closed, we would love to hear from you', $template_message,'staff', 'Testimonial Request Email Template for Tickets', $template_name);
    }

    public function send_testimonial_request_email_projects($email, $testimonial,$project){
        $template_name = 'flextestimonial_testimonial_request_projects';
        $template = mail_template($template_name, "flextestimonial", $email, $testimonial,$project);
        $template->send();
    }

    public function create_testimonial_request_email_template_projects(){
        $template_name = 'flextestimonial-testimonial-request-projects';
        $template_message = 'Hello, <br/><br/> Your project has now been completed. <br/><br/> We would love to hear from you, please submit a testimonial for your experience. <br/><br/> 
        <a href="{testimonial_url}">Submit a Testimonial</a> <br/><br/> Regards';
        create_email_template('Your Project has been Completed, we would love to hear from you', $template_message,'staff', 'Testimonial Request Email Template for Projects', $template_name);
    }

    public function send_testimonial_request_email_invoices($email, $testimonial,$invoice){
        $template_name = 'flextestimonial_testimonial_request_invoices';
        $template = mail_template($template_name, "flextestimonial", $email, $testimonial,$invoice);
        $template->send();
    }

    public function create_testimonial_request_email_template_invoices(){
        $template_name = 'flextestimonial-testimonial-request-invoices';
        $template_message = 'Hello, <br/><br/> Your invoice payment has now been received. <br/><br/> We would love to hear from you, please submit a testimonial for your experience. <br/><br/> 
        <a href="{testimonial_url}">Submit a Testimonial</a> <br/><br/> Regards';
        create_email_template('Your Invoice has been Paid, we would love to hear from you', $template_message,'staff', 'Testimonial Request Email Template for Invoices', $template_name);
    }


}


