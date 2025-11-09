<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l("flexibackup_restore_files_from");  ?> <?php echo date('M jS, Y, g:i a', strtotime($backup->datecreated)); ?></h4>
        </div>
        <?php echo form_open(admin_url('flexibackup/restore_backup'), ['id' => 'restore-backup-form']); ?>
        <div class="modal-body">
            <label for="restore_files"><?php echo _l("flexibackup_choose_componet_to_restore");  ?></label -->
            <input type="hidden" name="backup_id" value="<?php echo $backup->id; ?>">
            <?php foreach ($file_types as $file_type): ?>
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="restore_files[]" id="restore_files_<?php echo $file_type ?>"
                           value="<?php echo $file_type ?>">
                    <label for="restore_files_<?php echo $file_type ?>">
                        <?php echo ucfirst($file_type) ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <br/>
            <p class="text-info">
                <?php echo _l("flexibackup_restore_warning");  ?>
            </p>
            <?php if($backup->backup_type == "database"): ?>
            <p class="bold text-warning"><?php echo _l("flexibackup_restore_db_warning") ?></p>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary"><?php echo _l('flexibackup_restore'); ?></button>
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>