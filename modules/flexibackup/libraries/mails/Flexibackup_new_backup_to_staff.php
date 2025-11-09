<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Flexibackup_new_backup_to_staff extends App_mail_template
{
    protected $for = 'staff';

    protected $staff_email;

    protected $staffid;
    protected $file;
    protected $backup;
    public $slug = 'flexibackup-new-backup-to-staff';

    public $rel_type = 'flexibackup';

    public function __construct($staff_email, $staffid, $backup, $file)
    {
        parent::__construct();

        $this->staff_email = $staff_email;
        $this->staffid = $staffid;
        $this->backup = $backup;
        $this->file = $file;
    }

    public function build()
    {
        $this->add_attachment($this->file);
        $this->set_merge_fields('flexibackup_merge_fields', $this->backup);
        $this->to($this->staff_email)
            ->set_rel_id($this->backup->id)
            ->set_staff_id($this->staffid)
            ->set_merge_fields('staff_merge_fields', $this->staffid);
    }
}