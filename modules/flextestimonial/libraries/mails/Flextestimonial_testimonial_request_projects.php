<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial_testimonial_request_projects extends App_mail_template
{
    protected $for = 'flextestimonial';

    protected $email;
    protected $testimonial;
    protected $project;
    public $slug = 'flextestimonial-testimonial-request-projects';
    public $rel_type = 'flextestimonial';
    /**
     * @var mixed
     */

    public function __construct($email, $testimonial, $project)
    {
        parent::__construct();

        $this->email = $email;
        $this->testimonial = $testimonial;
        $this->project = $project;
    }

    public function build()
    {
        $this->set_merge_fields('flextestimonial_testimonial_request_projects_merge_fields', $this->testimonial, $this->project);
        $this->to($this->email)->set_rel_id($this->testimonial['id']);
    }
}