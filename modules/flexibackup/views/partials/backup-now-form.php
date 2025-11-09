<div class="modal fade" id="auto_backup_config" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('flexibackup/backup_now')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('flexibackup_perform_a_backup'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mbot25">
                    <?php echo _l('flexibackup_back_up_now_note'); ?><br/>
                </div>
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="flexibackup_include_database_in_the_backup" id="flexibackup_include_database_in_the_backup" value="1">
                    <label for="flexibackup_include_database_in_the_backup"><?php echo _l('flexibackup_include_database_in_the_backup'); ?></label>
                </div>
                <hr class="-tw-mx-3 tw-mt-3 tw-mb-6">
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="flexibackup_include_file_in_the_backup" id="flexibackup_include_file_in_the_backup" value="1"/>
                    <label for="flexibackup_include_file_in_the_backup"><?php echo _l('flexibackup_include_file_in_the_backup'); ?></label>
                </div>
                <hr class="-tw-mx-3 tw-mt-3 tw-mb-6">
                <div class="p-2 tw-ml-5 tw-hidden bk-now-options-wrapper">
                    <?php  echo flexi_backup_file_options(); ?>
                </div>
                <div>
                    <?php echo render_textarea('note', 'flexibackup_note') ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('flexibackup_now'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->