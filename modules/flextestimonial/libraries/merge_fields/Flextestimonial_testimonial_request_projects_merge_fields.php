<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial_testimonial_request_projects_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Testimonial Name',
                'key'       => '{testimonial_name}',
                'available' => [],
                'templates' => [
                    'flextestimonial-testimonial-request-projects',
                ],
            ],
            [
                'name'      => 'Testimonial URL',
                'key'       => '{testimonial_url}',
                'available' => [],
                'templates' => [
                    'flextestimonial-testimonial-request-projects',
                ],
            ],
            [
                'name'      => 'Project Name',
                'key'       => '{project_name}',
                'available' => [],
                'templates' => [
                    'flextestimonial-testimonial-request-projects',
                ],
            ],
        ];
    }

    /**
     * Flextestimonial event merge fields
     */
    public function format($testimonial, $project)
    {
        $fields['{testimonial_name}']       = $testimonial['title'];
        $fields['{testimonial_url}']       = flextestimonial_display_url($testimonial['slug']);
        $fields['{project_name}']       = $project->name;
        return hooks()->apply_filters('flextestimonial_testimonial_request_projects_merge_fields', $fields, [
            'event' => $testimonial,
        ]);
    }
}
