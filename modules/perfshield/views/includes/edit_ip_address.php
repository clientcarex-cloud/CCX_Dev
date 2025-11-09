<!-- Customer counter Modal -->
<div class="modal fade" id="edit_ip_address" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo _l('edit_ip_address'); ?></h4>
            </div>
            <?php 
                $attributes = ['id' => 'edit_ip_address_form'];
                $hidden     = ['id' => ''];
            ?>
            <?php echo form_open('', $attributes, $hidden) ?>
            <div class="modal-body">
                <?php echo render_input('ip_address', '', '', 'text') ?>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><?php echo _l('update') ?></button>
            </div>
            <?php echo form_close() ?>
        </div>
    </div>
</div>
<!-- Over -->