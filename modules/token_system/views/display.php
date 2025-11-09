<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
		  <div class="row">
		  <div class="col-md-10">
			<h4 class="no-margin"><?= _l($title); ?></h4>
		  </div>
		  <div class="col-md-2">
		  <div class="mb-2">
              <a href="<?= admin_url('token_system/add'); ?>" class="btn btn-primary">
                <i class="fa fa-plus"></i> <?= _l('new_display'); ?>
              </a>
            </div>
		  </div>
	  </div>
            
			
            <hr class="hr-panel-heading" />
            <div class="clearfix"></div>

            

            <?= render_datatable([
              _l('s_no'),
              _l('display_name'),
              _l('queue_type'),
              _l('doctor_info'),
              _l('media_type'),
              _l('patient_info'),
              _l('created_at'),
              _l('action')
            ], 'display-config'); ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>

<script>
  $(function () {
    initDataTable('.table-display-config', admin_url + 'token_system/displays_table', [], [], 'undefined', [0, 'desc']);
  });
</script>
