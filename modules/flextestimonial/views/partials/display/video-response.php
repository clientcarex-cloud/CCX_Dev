<h2 class="tw-text-2xl tw-font-bold tw-mb-4"><?php echo _flextestimonial_lang('record_a_video_testimonial'); ?></h2>
<div class="tw-text-left tw-mb-4 tw-w-full">
    <p><?php echo nl2br($testimonial['response_prompt']); ?></p>
</div>
<!-- star rating -->
<?php if ($testimonial['enable_rating'] == '1'): ?>
    <div class="tw-mb-4">
    <div class="flex-testimonial-rating">
        <?php for($i = 1; $i <= 5; $i++): ?>
        <a href="#" class="flex-testimonial-rating-star" data-rating="<?php echo $i; ?>">
            <i class="fas fa-star"></i>
            <input type="radio" name="video_rating" value="<?php echo $i; ?>" class="flex-testimonial-rating-star-input">
        </a>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>


<!-- video recording -->
<div class="tw-mb-4 flex-testimonial-video-recording-container">
    <video id="video-preview" class="flex-testimonial-video-preview" style="display: none;"></video>
    <img src="<?php echo base_url('modules/flextestimonial/assets/images/video-placeholder.png'); ?>" alt="Video Placeholder" class="flex-testimonial-video-placeholder">
    <!-- Video Controls -->
    <div class="video-controls">
        <button type="button" class="record-button" id="flextestimonial-video-record-button">
            <i class="fas fa-video"></i>
        </button>
        <button type="button" class="stop-button" id="flextestimonial-video-stop-button" style="display: none;">
            <i class="fas fa-stop"></i>
        </button>
        <button type="button" class="retry-button" id="flextestimonial-video-retry-button" style="display: none;">
            <i class="fas fa-redo"></i>
        </button>
    </div>
</div>
<p class="tw-text-sm tw-text-gray-500 tw-mb-2 tw-text-center">
    <?php echo _flextestimonial_lang('or'); ?>
</p>
<!--upload a file button -->
<div class="tw-mb-4 flex-testimonial-upload-container">
    <input type="file" id="video-file" class="flex-testimonial-upload-input" name="video_file" accept="video/*"/>
    <label for="video-file" class="flex-testimonial-upload-label">
        <i class="fas fa-upload tw-mr-2"></i> <?php echo _flextestimonial_lang('upload_video'); ?>
        <span id="flex-testimonial-file-upload-count"></span>
    </label>
</div>
<!-- video recording not file upload -->
<!--<button type="button"
data-msg="<?php echo _flextestimonial_lang('video_required'); ?>"
id="flextestimonial-submit-video-response" class="tw-border-0 tw-w-full hover:tw-bg-gray-200  tw-rounded-lg tw-py-3 tw-mb-3 tw-flex tw-items-center tw-justify-center tw-text-white" style="background-color: <?php echo $testimonial['primary_color']; ?>;">
    <i class="fas fa-video tw-mr-2"></i> <?php echo _flextestimonial_lang('submit'); ?>
</button>-->
<button type="button" id="flextestimonial-submit-customer-info" class="tw-bg-blue-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-md btn-block" style="background-color: <?php echo $testimonial['primary_color']; ?>; border-color: <?php echo $testimonial['primary_color']; ?>;"><?php echo _flextestimonial_lang('submit'); ?></button>
<!-- floating back icon -->
<a href="#" class="flex-testimonial-back-icon"><i class="fas fa-arrow-left"></i></a>