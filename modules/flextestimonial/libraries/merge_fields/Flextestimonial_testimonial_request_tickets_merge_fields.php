<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial_testimonial_request_tickets_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Testimonial Name',
                'key'       => '{testimonial_name}',
                'available' => [],
                'templates' => [
                    'flextestimonial-testimonial-request-tickets',
                ],
            ],
            [
                'name'      => 'Testimonial URL',
                'key'       => '{testimonial_url}',
                'available' => [],
                'templates' => [
                    'flextestimonial-testimonial-request-tickets',
                ],
            ],
            [
                'name'      => 'Ticket Subject',
                'key'       => '{ticket_subject}',
                'available' => [],
                'templates' => [
                    'flextestimonial-testimonial-request-tickets',
                ],
            ],
        ];
    }

    /**
     * Flextestimonial event merge fields
     */
    public function format($testimonial, $ticket)
    {
        $fields['{testimonial_name}']       = $testimonial['title'];
        $fields['{testimonial_url}']       = flextestimonial_display_url($testimonial['slug']);
        $fields['{ticket_subject}']       = $ticket->subject;
        return hooks()->apply_filters('flextestimonial_testimonial_request_tickets_merge_fields', $fields, [
            'event' => $testimonial,
        ]);
    }
}
