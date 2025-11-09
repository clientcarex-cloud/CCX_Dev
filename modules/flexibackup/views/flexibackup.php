<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo $title; ?>
                    </h4>
                    <div>
                        <?php flexibackup_init_menu(); ?>
                    </div>
                </div>
                <div id="file-actions-wrapper" class="relative">

                </div>
                <div class="panel_s">
                    <input type="hidden" id="base_url" value="<?php echo admin_url('flexibackup/ajax'); ?>" />
                    <div class="panel-body panel-table-full">
                        <table class="table dt-table" data-order-col="0" data-order-type="desc">
                            <thead>
                            <th><?php echo _l('flexibackup_date'); ?></th>
                            <th><?php echo _l('flexibackup_backup_data_click'); ?></th>
                            <th><?php echo _l('flexibackup_uploaded_to_remote_storage'); ?></th>
                            <th><?php echo _l('flexibackup_note'); ?></th>
                            <th><?php echo _l('options'); ?></th>
                            </thead>
                            <tbody>
                                <?php foreach($backups as $backup): ?>
                                <tr>
                                    <td data-order="<?php echo $backup['datecreated']; ?>">
                                        <span class="bold"><?php echo date('M jS, Y, g:i a', strtotime($backup['datecreated'])); ?></span>
                                    </td>
                                    <td>
                                        <?php if($backup['backup_type'] == 'file'): ?>
                                        <?php foreach (explode(',',$backup['backup_data']) as $item):
                                                if(!$item) continue;
                                                ?>
                                           <button type="button" class="btn btn-default flexibackup-show-action-btn"
                                                   data-id="<?php echo $backup['id'] ?>"
                                                   data-type="<?php echo $item ?>"
                                           ><?php echo ucfirst($item); ?></button>
                                        <?php endforeach;?>
                                        <?php else: ?>
                                            <button type="button"
                                                    class="btn btn-default flexibackup-show-action-btn"
                                                    data-id="<?php echo $backup['id'] ?>"
                                                    data-type="<?php echo $backup['backup_type'] ?>"
                                            ><?php echo ucfirst($backup['backup_type']) ?></button>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($backup['uploaded_to_remote'] == 1): ?>
                                            <span class="label label-info"><?php echo ucfirst(_l('yes')); ?></span>
                                        <?php else: ?>
                                            <span class="label label-warning"><?php echo ucfirst(_l('no')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $backup['note'] ? $backup['note'] : '' ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo admin_url('misc/delete_note/' . $backup['id']); ?>"
                                           class="btn btn-primary flexibackup-restore-backup" data-id="<?php echo $backup['id'] ?>">
                                            <?php echo _l("flexibackup_restore"); ?>
                                        </a>
                                        <a href="<?php echo admin_url('flexibackup/delete_backup/'.$backup['id'].'/all') ?>"
                                           class="btn btn-danger _delete">
                                            <i class="fa fa-trash"></i>
                                            <?php echo _l("delete"); ?>
                                        </a>
                                        <?php if($backup['uploaded_to_remote'] == 0): ?>
                                        <a href="<?php echo admin_url('flexibackup/upload_to_remote/'.$backup['id']) ?>"
                                           class="btn btn-warning _delete">
                                            <i class="fa fa-cloud-upload"></i>
                                           <?php echo _l("flexibackup_upload_to_remote") ?>
                                        </a>
                                        <?php endif; ?>
                                        <a href=""
                                           class="btn btn-link btn-outline-info flexibackup-view-log-file"
                                           data-id="<?php echo $backup['id'] ?>"
                                        >
                                            <?php echo _l("flexibackup_view_log"); ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="flexibackup-log-file-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l("flexibackup_log_file");  ?></h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="flexibackup-log-file-modal-url"><?php echo _l('flexibackup_download_log_file'); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="flexibackup-restore-backup-modal" tabindex="-1" role="dialog">

</div>
<?php echo init_backup_now_form() ?>
<?php init_tail(); ?>
</body>

</html>