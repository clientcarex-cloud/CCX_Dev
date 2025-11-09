<h2 class="tw-text-2xl tw-font-bold tw-mb-4"><?php echo _flextestimonial_lang('write_a_text_testimonial'); ?></h2>
<div class="tw-text-left tw-mb-4 tw-w-full">
    <p><?php echo nl2br($testimonial['response_prompt']); ?></p>
</div>
<!-- star rating -->
 <?php  if($testimonial['enable_rating'] == '1'): ?>
<div class="tw-mb-4">
    <div class="flex-testimonial-rating">
        <?php for($i = 1; $i <= 5; $i++): ?>
        <a href="#" class="flex-testimonial-rating-star" data-rating="<?php echo $i; ?>">
            <i class="fas fa-star"></i>
            <input type="radio" name="text_rating" value="<?php echo $i; ?>" class="flex-testimonial-rating-star-input">
        </a>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>
<!-- textarea for text response -->
 <?php echo form_textarea(['name' => 'text_response', 'id' => 'text_response', 'class' => 'tw-w-full tw-p-2 tw-border tw-rounded-md tw-mb-4', 'placeholder' => _flextestimonial_lang('share_your_experience')]); ?>
<?php if($testimonial['enable_image'] == '1'): ?>
    <div class="tw-text-left tw-mb-4 border-2 border-gray-300 tw-rounded-md tw-p-2">
        <label for="image" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 flex-testimonial-upload-label"><i class="fas fa-image tw-mr-2"></i><?php echo $testimonial['upload_image_button_label']; ?> <span id="flex-testimonial-file-upload-count"></span></label>
        <input type="file" accept="image/*" name="images[]" id="image" class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-border-indigo-500 focus:tw-ring-indigo-500 focus:tw-ring-opacity-50 flex-testimonial-upload-input" max="5" multiple>
    </div>
<?php endif; ?>
<!--<button id="flextestimonial-submit-text-response" 
    type="button"
    data-msg="<?php echo _flextestimonial_lang('text_and_images_required'); ?>"
    class="tw-border-0 tw-w-full hover:tw-bg-gray-200  tw-rounded-lg tw-py-3 tw-mb-3 tw-flex tw-items-center tw-justify-center tw-text-white" style="background-color: <?php echo $testimonial['primary_color']; ?>;">
    <i class="fas fa-pencil-alt tw-mr-2"></i> <?php echo _flextestimonial_lang('submit'); ?>
</button>-->
<button type="button" id="flextestimonial-submit-customer-info" class="tw-bg-blue-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-md btn-block" style="background-color: <?php echo $testimonial['primary_color']; ?>; border-color: <?php echo $testimonial['primary_color']; ?>;"><?php echo _flextestimonial_lang('submit'); ?></button>
<!-- floating back icon -->
<a href="#" class="flex-testimonial-back-icon"><i class="fas fa-arrow-left"></i></a>