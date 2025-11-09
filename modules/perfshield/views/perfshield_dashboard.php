<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
        <div class="panel_s">
	        <div class="panel-body">
		        <div class="alert alert-danger" font-medium="">
		        	<h4><?= _l('notice') ?> !</h4>
		        	<p><?= _l('clear_log_notice') ?></p>
		        </div>
		        <?php if (has_permission('perfshield', '', 'delete')) { ?>
				<span class="pull-right">
					<a href="javascript:void(0)" class="btn btn-danger clear_log"><?php echo _l('clear_log') ?></a>
				</span>
				<?php } ?>
				<h4 class="tw-mt-0 tw-mb-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
					<i class="fa-solid fa-chart-simple tw-mr-2"></i>
					<?php echo _l('failed_login_attempts_logs') ?>
				</h4>
				<div class="clearfix"></div>
				<hr class="hr-panel-heading"/>
				<div>
					<?php
						render_datatable([
							_l('#'),
							_l('email_address'),
							_l('last_failed_attempt'),
							_l('view_ip'),
							_l('failed_attempts_count'),
							_l('lockouts_count'),
							_l('country'),
							_l('country_code'),
							_l('isp'),
							_l('is_mobile') . ' ?'
						], 'perfshield_logs');
					?>
				</div>
	        </div>
        </div>
	</div>
</div>
<?php init_tail(); ?>

<script>
	"use strict";
	$(document).ready(function() {
	    initDataTable('.table-perfshield_logs', `${admin_url}perfshield/get_table_data/perfshield_logs`);
	});
</script>