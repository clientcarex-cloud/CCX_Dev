<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading">
Prescription
</h4>
<div class="row">
    <div class="col-md-12">
        <a href="#" class="btn btn-primary mbot15" onclick="slideToggle('.usernote'); return false;">
            <i class="fa-regular fa-plus tw-mr-1"></i>
            <?= _l('new_prescription'); ?>
        </a>
        <div class="usernote hide">
            <?= form_open(admin_url('misc/add_prescription/' . $client->userid . '/prescription')); ?>
            <?= render_textarea('description', 'note_description', '', ['rows' => 5]); ?>
            <button class="btn btn-primary pull-right mbot15">
                <?= _l('submit'); ?>
            </button>
            <?= form_close(); ?>
        </div>
        <table class="table dt-table" data-order-col="2" data-order-type="desc">
            <thead>
                <tr>
                    <th width="50%">
                    Prescription
                    </th>
                    
                    <th>
                        <?= _l('options'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                //print_r($user_prescription);
                foreach ($user_prescription as $note) { ?>
                <tr>
                    <td width="50%">
                        <div
                            data-note-description="<?= e($note['id']); ?>">
                            <?= process_text_content_for_display($note['prescription']); ?>
                        </div>
                        <div data-note-edit-textarea="<?= e($note['id']); ?>"
                            class="hide">
                            <textarea name="prescription" class="form-control"
                                rows="4"><?= clear_textarea_breaks($note['prescription']); ?></textarea>
                            <div class="text-right mtop15">
                                <button type="button" class="btn btn-default"
                                    onclick="toggle_edit_note(<?= e($note['id']); ?>);return false;">
                                    <?= _l('cancel'); ?>
                                </button>
                                <button type="button" class="btn btn-primary"
                                    onclick="edit_note(<?= e($note['id']); ?>);">
                                    <?= _l('update_note'); ?>
                                </button>
                            </div>
                        </div>
                    </td>
                    
                    
                    <td>
                        <div class="tw-flex tw-items-center tw-space-x-2">
                            <?php //if ($note['addedfrom'] == get_staff_user_id() || is_admin()) { ?>
                            <a href="#"
                                onclick="toggle_edit_note(<?= e($note['id']); ?>);return false;"
                                class="text-muted">
                                <i class="fa-regular fa-pen-to-square fa-lg"></i>
                            </a>
                            <a href="<?= admin_url('misc/delete_prescription/' . $note['id']); ?>"
                                class="text-muted _delete">
                                <i class="fa-regular fa-trash-can fa-lg"></i>
                            </a>
                            <?php// } ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </div>