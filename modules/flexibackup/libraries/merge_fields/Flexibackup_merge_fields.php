<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexibackup_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
                [
                    'name'      => 'Backup Name',
                    'key'       => '{backup_name}',
                    'available' => [
                    ],
                    'templates' => [
                        'flexibackup-new-backup-to-staff',
                    ],
                ],
                [
                    'name'      => 'Backup Type',
                    'key'       => '{backup_type}',
                    'available' => [
                    ],
                    'templates' => [
                        'flexibackup-new-backup-to-staff',
                    ],
                ],
                [
                    'name'      => 'Backup Date',
                    'key'       => '{backup_date}',
                    'available' => [
                    ],
                    'templates' => [
                        'flexibackup-new-backup-to-staff',
                    ],
                ],
            ];
    }

    /**
     * Flexibackup event merge fields
     */
    public function format($backup)
    {
        $fields['{backup_name}']       = $backup->backup_name;
        $fields['{backup_type}']       = $backup->backup_type;
        $fields['{backup_date}']       = $backup->datecreated;
        return hooks()->apply_filters('flexibackup_merge_fields', $fields, [
            'event' => $backup,
        ]);
    }
}