<a href="<?php echo admin_url('flexibackup'); ?>" class="btn btn-default">
    <?php echo _l('flexibackup_existing_backups'); ?>
</a>
<a href="<?php echo admin_url('flexibackup/schedule_backup'); ?>" class="btn btn-default">
    <?php echo _l('flexibackup_next_scheduled_backup'); ?>
</a>
<a href="<?php echo admin_url('flexibackup/settings'); ?>" class="btn btn-default">
    <?php echo _l('flexibackup_settings'); ?>
</a>
<a href="#" data-toggle="modal" data-target="#auto_backup_config"
   class="btn btn-primary mright5">
    <?php echo _l('flexibackup_now'); ?>
</a>