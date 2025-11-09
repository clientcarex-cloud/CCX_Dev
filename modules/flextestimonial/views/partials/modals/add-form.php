<div class="modal fade" id="flextestimonial_new_form" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('flextestimonial/create')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _flextestimonial_lang('new_testimonial_form'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo render_input('title', _flextestimonial_lang('title'), '', 'text'); ?>
                <?php echo render_textarea('description', _flextestimonial_lang('description'), '', ['rows' => 3]); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _flextestimonial_lang('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _flextestimonial_lang('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->