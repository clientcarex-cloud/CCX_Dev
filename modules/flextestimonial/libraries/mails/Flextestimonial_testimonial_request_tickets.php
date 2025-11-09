<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial_testimonial_request_tickets extends App_mail_template
{
    protected $for = 'flextestimonial';

    protected $email;
    protected $testimonial;
    protected $ticket;
    public $slug = 'flextestimonial-testimonial-request-tickets';
    public $rel_type = 'flextestimonial';
    /**
     * @var mixed
     */

    public function __construct($email, $testimonial, $ticket)
    {
        parent::__construct();

        $this->email = $email;
        $this->testimonial = $testimonial;
        $this->ticket = $ticket;
    }

    public function build()
    {
        $this->set_merge_fields('flextestimonial_testimonial_request_tickets_merge_fields', $this->testimonial, $this->ticket);
        $this->to($this->email)->set_rel_id($this->testimonial['id']);
    }
}