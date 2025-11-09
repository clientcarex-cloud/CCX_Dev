<div class="panel_s">
    <div class="panel-body">
        <div class="">
            <div class="">
                <a href="" class="pull-right flexibackup-remove-file-action-preview-btn"><i class="fa fa-remove text-info font-medium valign"></i></a>
                <p> <?php echo _l("download") ?> <span class="bold"> <?php echo ucfirst($type) ?> </span> <?php echo date('M jS, Y, g:i a', strtotime($backup->datecreated)) ?> </p>
                <?php echo _l("flexibackup_file_ready_actions") ?>
                <a href="<?php echo admin_url('flexibackup/download_backup/'.$id.'/'.$type) ?>" type="button" class="btn btn-default"><?php echo _l("flexibackup_download_to_your_computer") ?> </a>
                <a href="<?php echo admin_url('flexibackup/delete_backup/'.$id.'/'.$type) ?>" type="button" class="btn btn-default _delete"><?php echo _l("flexibackup_delete_from_your_webserver") ?></a>
                <?php if($type != 'database'): ?>
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#browse-content-<?php echo $id; ?>-<?php echo $type ?>">
                    <?php echo _l("flexibackup_browse_contents") ?>
                </button>
                    <div class="modal fade" id="browse-content-<?php echo $id; ?>-<?php echo $type ?>" tabindex="-1" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title"><?php echo "Browsing ZIP File : ".$type;  ?> (<?php echo date('M dS, Y, g:i a', strtotime($backup->datecreated)) ?>)</h4>
                                </div>
                                <div class="modal-body">
                                    <div id="file-tree-<?php echo $id; ?>-<?php echo $type ?>">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>