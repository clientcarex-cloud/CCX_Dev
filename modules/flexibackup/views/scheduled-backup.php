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
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 animated fadeIn">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <?php echo _l("flexibackup_files"); ?>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <p><?php echo $files_backup_schedule ? "<span class='text-success'>".date('D, F j, Y H:i',$files_backup_schedule)."</span>" :   _l("flexibackup_nothing_currently_scheduled") ?></p>
                        <br/>
                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <button type="button" class="btn btn-primary"><?php echo _l('flexibackup_time_now') ?></button>
                            <button type="button" class="btn btn-secondary"><?php echo date('D, F j, Y H:i', $time_now); ?></button>
                        </div>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <?php echo _l("flexibackup_database"); ?>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <p><?php echo $database_backup_schedule ? "<span class='text-success'>".date('D, F j, Y H:i',$database_backup_schedule)."</span>" :  _l("flexibackup_nothing_currently_scheduled") ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_backup_now_form(); ?>
<?php init_tail(); ?>
</body>

</html>