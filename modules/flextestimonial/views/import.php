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
                    <div class="">
                        <a href="<?php echo base_url('modules/flextestimonial/assets/images/example.csv') ?>" class="btn btn-primary btn-sm" download><i class="fa fa-download"></i> <?php echo _flextestimonial_lang('download_template'); ?></a>
                    </div>
                </div>
                <div class="panel_s flex-testimonial-manage-wrapper">
                    <div class="panel-heading">
                        <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                            <?php echo _flextestimonial_lang('import_testimonials'); ?>
                        </h4>
                    </div>
                    <div class="panel-body flex-min-form-wrapper">
                        <?php if (isset($message) && $message) { ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> <?php echo $message; ?>
                            </div>
                        <?php } ?>
                        <?php echo form_open_multipart(admin_url('flextestimonial/import'), ['class' => 'tw-flex tw-flex-col tw-gap-4']); ?>
                        <!--pick a testimonial form-->
                        <div class="form-group tw-mb-4">
                            <label for="testimonial_form_id"><?php echo _flextestimonial_lang('import_form'); ?></label>
                            <select name="testimonial_form_id" id="testimonial_form_id" class="form-control">
                                <option value=""><?php echo _flextestimonial_lang('select_testimonial_form'); ?></option>
                                <?php foreach ($forms as $form) { ?>
                                    <option value="<?php echo $form['id']; ?>"><?php echo $form['title']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php echo render_input('import_file', _flextestimonial_lang('file'), '', 'file'); ?>
                        <br/>
                        <div class="tw-flex tw-justify-end">
                            <button type="submit" class="btn btn-primary" id="flextestimonial-save-changes"><?php echo _flextestimonial_lang('import_testimonials'); ?></button>
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