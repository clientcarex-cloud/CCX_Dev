<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo $title; ?>
                    </h4>
                    <div>
                        <!--wall of love -->
                        <a href="<?php echo base_url('flextestimonial/wall_of_love'); ?>"
                           class=" mright5">
                            <i class="fa fa-heart"></i> <?php echo _flextestimonial_lang('wall_of_love'); ?>
                        </a>
                        <!--autommations -->
                        <a href="<?php echo admin_url('flextestimonial/settings'); ?>"
                           class="btn btn-secondary mright5">
                            <i class="fa fa-wand-magic-sparkles"></i> <?php echo _flextestimonial_lang('automation'); ?>
                        </a>
                        <!-- import button -->
                        <a href="<?php echo admin_url('flextestimonial/import'); ?>"
                           class="btn btn-secondary mright5">
                            <i class="fa fa-upload"></i> <?php echo _flextestimonial_lang('import_testimonials'); ?>
                        </a>
                        <!-- new form button -->
                        <a href="#" data-toggle="modal" data-target="#flextestimonial_new_form"
                           class="btn btn-primary mright5">
                            <i class="fa fa-plus"></i> <?php echo _flextestimonial_lang('new_testimonial_form'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _flextestimonial_lang('title'); ?></th>
                                        <th><?php echo _flextestimonial_lang('description'); ?></th>
                                        <th><?php echo _flextestimonial_lang('testimonials'); ?></th>
                                        <th><?php echo _flextestimonial_lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- loop through the testimonials -->
                                    <?php foreach ($forms as $form) { ?>
                                        <tr>
                                            <td><?php echo $form['title']; ?>
                                            <div class="row-options">
                                                <a href="<?php echo admin_url('flextestimonial/responses/' . $form['slug']); ?>" class="text-success"> <?php echo _flextestimonial_lang('responses') ?></a>
                                            </div>
                                        </td>
                                            <td><?php echo $form['description']; ?></td>
                                            <td><?php echo $form['testimonials_count']; ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('flextestimonial/manage/' . $form['slug']); ?>" class="text-primary"><?php echo _flextestimonial_lang('manage'); ?></a>
                                                <a href="<?php echo admin_url('flextestimonial/delete/' . $form['slug']); ?>" class="btn text-danger btn-flextestimonial-delete"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                    </div>
                </div>
        </div>
    </div>
</div>

<!-- create form modal -->
<?php echo $this->load->view('partials/modals/add-form'); ?>

<?php init_tail(); ?>
</body>

</html>