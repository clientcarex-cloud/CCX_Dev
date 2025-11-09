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
					<h4 class="no-margin"><?php echo _l($title); ?></h4>
				</div> 
				<div class="col-md-2">
					<a href="<?= admin_url('client/doctor/add_doctor'); ?>" class="btn btn-primary me-auto">
						<i class="fa fa-plus"></i> <?= _l('new_doctor'); ?>
					 </a>
				</div>
			</div>
			
            <hr class="hr-panel-heading" />
            <div class="clearfix"></div>
            

            <?php
              $table_data = [
                _l('id'),
                _l('doctor_name'),
                _l('email'),
                _l('role'),
                _l('phonenumber'),
                _l('actions'),
              ];
              render_datatable($table_data, 'doctor-list-table');
            ?>

          </div>
        </div>
      </div>
    </div>
  </div>
<?php init_tail(); ?>
<script>
  $(function () {
    initDataTable('.table-doctor-list-table', admin_url + 'client/doctor', [], [], 'undefined', [0, 'desc']);
  });
</script>
</body>
</html>
