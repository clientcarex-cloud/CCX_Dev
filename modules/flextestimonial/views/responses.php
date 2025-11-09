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
                        <?php echo $title; ?>
                    </h4>
                </div>
                <div class="panel_s flex-testimonial-responses-table">
                    <div class="panel-body">
                        <table class="table dt-table table-striped table-bordered" data-order-col="6" data-order-type="desc">
                            <thead>
                                <tr>
                                    <th><?php echo _flextestimonial_lang('name-and-email'); ?></th>
                                    <th><?php echo _flextestimonial_lang('rating'); ?></th>
                                    <th><?php echo _flextestimonial_lang('text_response'); ?></th>
                                    <th><?php echo _flextestimonial_lang('images'); ?></th>
                                    <th><?php echo _flextestimonial_lang('show_in_wall_of_love'); ?></th>
                                    <th><?php echo _flextestimonial_lang('video_response'); ?></th>
                                    <th><?php echo _flextestimonial_lang('created_at'); ?></th>
                                    <th><?php echo _flextestimonial_lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($responses as $response) { ?>
                                    <tr>
                                        <td>
                                            <?php if ($response['user_photo']) { ?>
                                                <img src="<?php echo flextestimonial_media_url($response['user_photo']); ?>" alt="User Photo" class="flex-testimonial-user-photo" /><br/>
                                            <?php } ?>
                                        <p><?php echo $response['name'] . ' <br> ' . $response['email']; ?></p>
                                        <?php if ($response['website_url']) { ?>
                                            <b/><a href="<?php echo $response['website_url']; ?>" target="_blank"><?php echo $response['website_url']; ?></a>
                                        <?php } ?>
                                    </td>
                                        <td>
                                            <?php if ($response['rating']) { ?>
                                                <div class="flex-testimonial-rating">
                                                    <?php for ($i = 0; $i < $response['rating']; $i++) { ?>
                                                        <span class="flex-testimonial-rating-star active">
                                                            <i class="fa-solid fa-star"></i>
                                                        </span>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </td>
                                        <td><?php echo nl2br($response['text_response']); ?></td>
                                        <td>
                                            <?php if ($response['images']) { ?>
                                                <?php $images = flextestimonial_perfect_unserialize($response['images']); ?>
                                                <?php if (count($images) > 0) { ?>  
                                                    <?php foreach ($images as $image) {
                                                        if(!$image) continue;
                                                        ?>
                                                        <a href="<?php echo flextestimonial_media_url($image); ?>" target="_blank" class="flex-testimonial-img-link">
                                                            <img src="<?php echo flextestimonial_media_url($image); ?>" alt="Image" class="flex-testimonial-img"/>
                                                        </a>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <label for="featured_<?php echo $response['id']; ?>" class="flex-testimonial-featured-label" data-id="<?php echo $response['id']; ?>">
                                                <!-- checkbox -->
                                                <input onclick="flextestimonial_update_response_featured(<?php echo $response['id']; ?>)" type="checkbox" name="featured_<?php echo $response['id']; ?>" value="1" <?php echo $response['featured'] ? 'checked' : ''; ?> />
                                            </label>
                                        </td>
                                        <td>
                                            <?php echo $this->load->view('partials/response/video', ['response' => $response]); ?>
                                        </td>
                                        <td><?php echo $response['created_at']; ?></td>
                                        <td>
                                            <?php if (has_permission(FLEXTESTIMONIAL_MODULE_NAME, '', 'delete')) { ?>
                                                <a href="<?php echo admin_url('flextestimonial/delete_response/' . $response['id']); ?>" class="btn text-danger btn-flextestimonial-delete"><i class="fa fa-trash"></i></a>
                                            <?php } ?>
                                            <!-- view response -->
                                            <a href="<?php echo base_url('flextestimonial/r/' . $response['slug']); ?>" target="_blank" class="btn text-primary"><i class="fa fa-eye"></i></a>
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
</div>
<input type="hidden" id="flextestimonial-ajax-url" value="<?php echo admin_url('flextestimonial/ajax'); ?>">
<?php init_tail(); ?>
</body>

</html>