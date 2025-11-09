<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <a href="<?php echo admin_url('flextestimonial'); ?>" class="">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                        <?php echo _flextestimonial_lang('testimonials'); ?>
                    </h4>
                </div>
                <div class="panel_s flex-testimonial-manage-wrapper">
                    <div class="panel-heading">
                        <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                            <?php echo _flextestimonial_lang('testimonial_automation_settings'); ?>
                        </h4>
                    </div>
                    <div class="panel-body flex-min-form-wrapper">
                        <div class="alert alert-info">
                            <p><i class="fa fa-info-circle tw-font-bold"></i> <?php echo _flextestimonial_lang('testimonial_automation_settings'); ?></p>
                            <p><?php echo _flextestimonial_lang('automation_info'); ?></p>
                        </div>
                        <?php echo form_open_multipart(admin_url('flextestimonial/settings'), ['class' => 'tw-flex tw-flex-col tw-gap-4']); ?>
                        <!--pick a testimonial form-->
                        <div class="form-group tw-mb-4">
                            <label for="flextestimonial_for_projects"><?php echo _flextestimonial_lang('flextestimonial_for_projects'); ?></label>
                            <select name="flextestimonial_for_projects" id="flextestimonial_for_projects" class="form-control">
                                <option value=""><?php echo _flextestimonial_lang('select_testimonial_form'); ?></option>
                                <?php foreach ($forms as $form) { ?>
                                    <option value="<?php echo $form['id']; ?>" <?php echo get_option('flextestimonial_for_projects') == $form['id'] ? 'selected' : ''; ?>><?php echo $form['title']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <hr/>
                        <div class="form-group tw-mb-4">
                            <label for="flextestimonial_for_invoices"><?php echo _flextestimonial_lang('flextestimonial_for_invoices'); ?></label>
                            <select name="flextestimonial_for_invoices" id="flextestimonial_for_invoices" class="form-control">
                                <option value=""><?php echo _flextestimonial_lang('select_testimonial_form'); ?></option>
                                <?php foreach ($forms as $form) { ?>
                                    <option value="<?php echo $form['id']; ?>" <?php echo get_option('flextestimonial_for_invoices') == $form['id'] ? 'selected' : ''; ?>><?php echo $form['title']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <hr/>
                        <div class="form-group tw-mb-4">
                            <label for="flextestimonial_for_tickets"><?php echo _flextestimonial_lang('flextestimonial_for_tickets'); ?></label>
                            <select name="flextestimonial_for_tickets" id="flextestimonial_for_tickets" class="form-control">
                                <option value=""><?php echo _flextestimonial_lang('select_testimonial_form'); ?></option>
                                <?php foreach ($forms as $form) { ?>
                                    <option value="<?php echo $form['id']; ?>" <?php echo get_option('flextestimonial_for_tickets') == $form['id'] ? 'selected' : ''; ?>><?php echo $form['title']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <br/>
                        <div class="tw-flex tw-justify-end">
                            <button type="submit" class="btn btn-primary" id="flextestimonial-save-changes"><?php echo _flextestimonial_lang('save_changes'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>