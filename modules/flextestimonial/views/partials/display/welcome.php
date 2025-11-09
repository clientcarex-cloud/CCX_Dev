<h2 class="tw-text-2xl tw-font-bold tw-mb-4" style="margin-top: -15px;">
<?php
	if(isset($testimonial['request_id'])){
		 echo 'Hi '. $testimonial['patient_name']; 
	}
		 ?>
<br>
<?php echo $testimonial['welcome_title'];?></h2>
<div class="tw-text-left tw-mb-6 tw-w-full">
    <p><?php echo nl2br($testimonial['welcome_message']) ?></p>
</div>
<?php if($testimonial['enable_video_testimonial'] == 1): ?>
<button type="button" id="flextestimonial-video-cta" class="tw-border-0 tw-w-full hover:tw-bg-gray-200  tw-rounded-lg tw-py-3 tw-mb-3 tw-flex tw-items-center tw-justify-center tw-text-white" style="background-color: <?php echo $testimonial['primary_color']; ?>;">
    <i class="fas fa-video tw-mr-2"></i> <?php echo $testimonial['record_a_video_button_label']; ?>
</button>
<?php endif; ?>
<?php if($testimonial['enable_text_testimonial'] == 1): ?>
<button type="button" id="flextestimonial-text-cta" class="tw-border-0 tw-w-full hover:tw-bg-gray-200 tw-rounded-lg tw-py-3 tw-flex tw-items-center tw-justify-center tw-text-gray-700">
    <i class="fas fa-pen tw-mr-2"></i> <?php echo $testimonial['write_a_testimonial_button_label']; ?>
</button>
<?php endif; ?>