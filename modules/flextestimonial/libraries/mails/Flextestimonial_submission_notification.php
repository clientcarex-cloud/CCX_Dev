<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial_submission_notification extends App_mail_template
{
    protected $for = 'flextestimonial';

    protected $email;
    protected $testimonial;
    public $slug = 'flextestimonial-submission-notification';
    public $rel_type = 'flextestimonial';
    /**
     * @var mixed
     */

    public function __construct($email, $testimonial)
    {
        parent::__construct();

        $this->email = $email;
        $this->testimonial = $testimonial;
    }

    public function build()
    {
        $this->set_merge_fields('flextestimonial_submission_notification_merge_fields', $this->testimonial);
        $this->to($this->email)->set_rel_id($this->testimonial['id']);
    }
}