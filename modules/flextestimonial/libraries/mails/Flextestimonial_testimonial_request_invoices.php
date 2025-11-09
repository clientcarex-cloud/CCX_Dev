<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial_testimonial_request_invoices extends App_mail_template
{
    protected $for = 'flextestimonial';

    protected $email;
    protected $testimonial;
    protected $invoice;
    public $slug = 'flextestimonial-testimonial-request-invoices';
    public $rel_type = 'flextestimonial';
    /**
     * @var mixed
     */

    public function __construct($email, $testimonial, $invoice)
    {
        parent::__construct();

        $this->email = $email;
        $this->testimonial = $testimonial;
        $this->invoice = $invoice;
    }

    public function build()
    {
        $this->set_merge_fields('flextestimonial_testimonial_request_invoices_merge_fields', $this->testimonial, $this->invoice);
        $this->to($this->email)->set_rel_id($this->testimonial['id']);
    }
}