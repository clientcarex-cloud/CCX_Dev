<h2 class="tw-text-2xl tw-font-bold tw-mb-4 tw-text-left"><?php echo _flextestimonial_lang('almost_done'); ?></h2>
<div class="tw-mb-2 tw-text-left">
    <label for="name" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700"><?php echo _flextestimonial_lang('your-name'); ?> <span class="tw-text-red-500 text-danger">*</span></label>
    <?php echo render_input('name', '', $testimonial['patient_name'], 'text', ['readonly' => true]); ?>
	
	<input type="hidden" name="userid" value="<?php echo $testimonial['userid'];?>">

</div>
<!-- email address check if enable_email and require_email are 1 -->
<?php if ($testimonial['enable_email'] == 1): ?>
    <div class="tw-mb-2 tw-text-left">
        <label for="email" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700"><?php echo _flextestimonial_lang('email-address'); ?>
            <?php if ($testimonial['require_email'] == 1): ?>
                <span class="tw-text-red-500 text-danger">*</span>
            <?php endif; ?>
        </label>
        <?php echo render_input('email', '', '', 'email',); ?>
    </div>
<?php endif; ?>

<!--user photo check if enable_user_photo and require_user_photo are 1 -->
<!-- show preview on the left side of the input field -->
<?php if ($testimonial['enable_user_photo'] == 1): ?>
    <div class="tw-mb-6 tw-text-left">
        <label for="user_photo" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700"><?php echo _flextestimonial_lang('user-photo'); ?>
            <?php if ($testimonial['require_user_photo'] == 1): ?>
                <span class="tw-text-red-500 text-danger">*</span>
            <?php endif; ?>
        </label>
        <div class="tw-flex tw-items-center">
            <div class="tw-w-1/2 tw-mr-2">
                <?php $user_photo = base_url('modules/flextestimonial/assets/images/userphoto.jpg'); ?>
                <img src="<?php echo $user_photo; ?>" alt="User Photo" class="tw-w-full tw-h-full flex-testimonial-user-photo">
            </div>
            <div class="tw-w-1/2 tw-mt-4">
                <?php echo render_input('user_photo', '', '', 'file'); ?>
            </div>
        </div><br/>
    <?php endif; ?>
    <!-- job title check if enable_job_title and require_job_title are 1 -->
    <?php if ($testimonial['enable_job_title'] == 1): ?>
        <div class="tw-mb-2 tw-text-left">
            <label for="job_title" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700"><?php echo _flextestimonial_lang('job-title'); ?>
                <?php if ($testimonial['require_job_title'] == 1): ?>
                    <span class="tw-text-red-500 text-danger">*</span>
                <?php endif; ?>
            </label>
            <?php echo render_input('job_title', '', '', 'text'); ?>
        </div>
    <?php endif; ?>

    <!-- company name check if enable_company_name and require_company_name are 1 -->
    <?php if ($testimonial['enable_company_name'] == 1): ?>
        <div class="tw-mb-2 tw-text-left">
            <label for="company_name" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700"><?php echo _flextestimonial_lang('company-name'); ?>
                <?php if ($testimonial['require_company_name'] == 1): ?>
                    <span class="tw-text-red-500 text-danger">*</span>
                <?php endif; ?>
            </label>
            <?php echo render_input('company_name', '', '', 'text'); ?>
        </div>
    <?php endif; ?>

    <!-- website url check if enable_website_url and require_website_url are 1 -->
    <?php if ($testimonial['enable_website_url'] == 1): ?>
        <div class="tw-mb-2 tw-text-left">
            <label for="website_url" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700"><?php echo _flextestimonial_lang('website-url'); ?>
                <?php if ($testimonial['require_website_url'] == 1): ?>
                    <span class="tw-text-red-500 text-danger">*</span>
                <?php endif; ?>
            </label>
            <?php echo render_input('website_url', '', '', 'url'); ?>
        </div>
    <?php endif; ?>

    <!-- submit button -->
    <div class="tw-flex tw-w-full">
        <button type="button" id="flextestimonial-submit-customer-info" class="tw-bg-blue-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-md btn-block" style="background-color: <?php echo $testimonial['primary_color']; ?>; border-color: <?php echo $testimonial['primary_color']; ?>;"><?php echo _flextestimonial_lang('submit'); ?></button>
    </div>
    <p class="tw-text-center tw-text-gray-500 tw-mt-2"><small><?php echo $testimonial['marketing_consent_label']; ?></small></p>
	
	
