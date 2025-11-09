<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$form_data = flextestimonial_get_testimonial_side_panel_items();
$i = 1;
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <a href="<?php echo admin_url('flextestimonial'); ?>" class="">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                        <?php echo $title; ?>
                    </h4>
                    <div>
                        <a class="btn btn-secondary mright5" data-toggle="modal" data-target="#flextestimonial_share_modal">
                            <i class="fa-solid fa-share"></i>
                            <?php echo _flextestimonial_lang('share'); ?>
                        </a>
                        <a href="<?php echo admin_url('flextestimonial/responses/' . $testimonial['slug']); ?>" class="btn btn-secondary mright5">
                            <i class="fa-solid fa-comments"></i>
                            <?php echo _flextestimonial_lang('responses'); ?>
                        </a>
                        <a href="<?php echo flextestimonial_display_url($testimonial['slug']); ?>"
                            target="_blank"
                           class="btn btn-secondary mright5">
                            <i class="fa-solid fa-eye"></i>
                            <?php echo _flextestimonial_lang('preview'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel_s flex-testimonial-manage-wrapper">
                    <div class="panel-body">
                        <!-- split the panel into two columns with preview on the right and accordion with the form on the left -->
                        <div class="row">
                            <div class="col-md-4 flex-testimonial-left-column">
                                <?php echo form_open(admin_url('flextestimonial/update/' . $testimonial['slug']), ['class' => 'tw-flex tw-flex-col tw-gap-4']); ?>
                                <!-- accordion -->
                                <div class="panel-group" id="accordion">
                                    <?php foreach ($form_data as $key => $data): ?>
                                        <?php $data['count'] = $i; ?>
                                        <?php echo $this->load->view('partials/manage/accordion-base', $data); ?>
                                        <?php $i++; ?>
                                    <?php endforeach; ?>
                                </div>
                                <div class="tw-flex tw-justify-end">
                                    <button type="submit" class="btn btn-primary" id="flextestimonial-save-changes"><?php echo _flextestimonial_lang('save_changes'); ?></button>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                            <div class="col-md-8 text-center">
                                <div class="testimonial-preview-section">
                                    <?php echo $this->load->view('display', ['testimonial' => $testimonial]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="flextestimonial-slug" value="<?php echo $testimonial['slug']; ?>">
<input type="hidden" id="flextestimonial-ajax-url" value="<?php echo admin_url('flextestimonial/ajax'); ?>">
<?php echo $this->load->view('partials/modals/share', ['testimonial' => $testimonial]); ?>
<?php init_tail(); ?>
</body>

</html>