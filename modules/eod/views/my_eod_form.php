<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('create_eod'); ?></h4>
                        <hr class="hr-panel-heading" />

                        <?php echo form_open(admin_url('eod/create')); ?>
                       <div class="row">

						<div class="col-md-12">
							<?php
							echo render_select(
								'activity',
								[
									['id' => 'Daily Task', 'name' => 'Daily Task'],
									['id' => 'Weekly Task', 'name' => 'Weekly Task']
								],
								['id', 'name'],
								'activity',
								'',
								[],
								[],
								'required="required"'
							);
							?>
						</div>




						<div class="col-md-6">
							<?php echo render_textarea('subject', 'subject', '', ['required' => 'required']); ?>
						</div>

						<div class="col-md-6">
							<?php echo render_textarea('today_report', 'today_report', '', ['required' => 'required']); ?>
						</div>

					</div>

                        <div class="text-right mtop15">
                            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
