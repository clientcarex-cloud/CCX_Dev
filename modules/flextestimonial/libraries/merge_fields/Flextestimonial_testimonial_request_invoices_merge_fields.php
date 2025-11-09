<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial_testimonial_request_invoices_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Testimonial Name',
                'key'       => '{testimonial_name}',
                'available' => [],
                'templates' => [
                    'flextestimonial-testimonial-request-invoices',
                ],
            ],
            [
                'name'      => 'Testimonial URL',
                'key'       => '{testimonial_url}',
                'available' => [],
                'templates' => [
                    'flextestimonial-testimonial-request-invoices',
                ],
            ],
            [
                'name'      => 'Invoice Number',
                'key'       => '{invoice_number}',
                'available' => [],
                'templates' => [
                    'flextestimonial-testimonial-request-invoices',
                ],
            ],
        ];
    }

    /**
     * Flextestimonial event merge fields
     */
    public function format($testimonial, $invoice)
    {
        $fields['{testimonial_name}']       = $testimonial['title'];
        $fields['{testimonial_url}']       = flextestimonial_display_url($testimonial['slug']);
        $fields['{invoice_number}']       = format_invoice_number($invoice->id);
        return hooks()->apply_filters('flextestimonial_testimonial_request_invoices_merge_fields', $fields, [
            'event' => $testimonial,
        ]);
    }
}
