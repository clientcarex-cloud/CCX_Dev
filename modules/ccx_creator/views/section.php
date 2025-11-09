<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo html_escape($section_label ?? 'CCX Creator'); ?>
                        </h4>
                        <p class="text-muted mtop10">
                            This placeholder confirms the <strong><?php echo html_escape($section_label ?? ''); ?></strong> page is ready for future content.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
