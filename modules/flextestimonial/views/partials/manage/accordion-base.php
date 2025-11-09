<?php
$label = $label ?? '';
$icon = $icon ?? '';
$key = $key ?? '';
$count = $count ?? 1;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $key; ?>">
                <span class="icon-styles">
                    <i class="fa <?php echo $icon; ?> icon-color-<?php echo $count; ?>"></i> &nbsp;&nbsp;
                </span>
                <span class="icon-text font-size-18"><?php echo $label; ?></span>
                <span class="pull-right">
                    <i class="fa fa-chevron-down accordion-arrow"></i>
                </span>
            </a>
        </h4>
    </div>
    <div id="collapse<?php echo $key; ?>" class="panel-collapse collapse">
        <div class="panel-body">
            <?php if ($key == 'general'): ?>
                <?php echo render_input('title', _flextestimonial_lang('title'), $testimonial['title']); ?>
                <?php echo render_textarea('description', _flextestimonial_lang('description'), $testimonial['description']); ?>
                <?php echo render_input('notification_emails', _flextestimonial_lang('notification_emails'), $testimonial['notification_emails']); ?>
                <?php echo $this->load->view('partials/manage/yes-no', ['label' => _flextestimonial_lang('active'), 'name' => 'active', 'value' => $testimonial['active']]); ?>
            <?php elseif ($key == 'design'): ?>
                <?php echo render_color_picker('primary_color', _flextestimonial_lang('primary_color'), $testimonial['primary_color']); ?>
                <?php echo render_color_picker('background_color', _flextestimonial_lang('background_color'), $testimonial['background_color']); ?>
                <?php echo $this->load->view('partials/manage/yes-no', ['label' => _flextestimonial_lang('enable_gradient'), 'name' => 'enable_gradient', 'value' => $testimonial['enable_gradient']]); ?>
                <?php echo $this->load->view('partials/manage/yes-no', ['label' => _flextestimonial_lang('enable_logo'), 'name' => 'enable_logo', 'value' => $testimonial['enable_logo']]); ?>
            <?php elseif ($key == 'welcome_page'): ?>
                <?php echo render_input('welcome_title', _flextestimonial_lang('welcome_title'), $testimonial['welcome_title']); ?>
                <?php echo render_textarea('welcome_message', _flextestimonial_lang('welcome_message'), $testimonial['welcome_message']); ?>
                <?php echo $this->load->view('partials/manage/yes-no', ['label' => _flextestimonial_lang('enable_video_testimonial'), 'name' => 'enable_video_testimonial', 'value' => $testimonial['enable_video_testimonial']]); ?>
                <?php echo $this->load->view('partials/manage/yes-no', ['label' => _flextestimonial_lang('enable_text_testimonial'), 'name' => 'enable_text_testimonial', 'value' => $testimonial['enable_text_testimonial']]); ?>
            <?php elseif ($key == 'response_prompt'): ?>
                <?php echo render_textarea('response_prompt', _flextestimonial_lang('response_prompt'), $testimonial['response_prompt']); ?>
                <?php echo $this->load->view('partials/manage/yes-no', ['label' => _flextestimonial_lang('enable_rating'), 'name' => 'enable_rating', 'value' => $testimonial['enable_rating']]); ?>
                <?php echo $this->load->view('partials/manage/yes-no', ['label' => _flextestimonial_lang('enable_image'), 'name' => 'enable_image', 'value' => $testimonial['enable_image']]); ?>
            <?php elseif ($key == 'customer_details'): ?>
                <?php
                $customer_media = [
                    'email' => [
                        'title' => _flextestimonial_lang('collect_email'),
                        'description' => _flextestimonial_lang('collect_email_description'),
                        'icon' => 'fa-at',
                        'enabled_name' => 'enable_email',
                        'require_name' => 'require_email',
                        'enabled_value' => $testimonial['enable_email'],
                        'required_value' => $testimonial['require_email'],
                    ],
                    'job_title' => [
                        'title' => _flextestimonial_lang('collect_job_title'),
                        'description' => _flextestimonial_lang('collect_job_title_description'),
                        'icon' => 'fa-briefcase',
                        'enabled_name' => 'enable_job_title',
                        'require_name' => 'require_job_title',
                        'enabled_value' => $testimonial['enable_job_title'],
                        'required_value' => $testimonial['require_job_title'],
                    ],
                    'user_photo' => [
                        'title' => _flextestimonial_lang('collect_user_photo'),
                        'description' => _flextestimonial_lang('collect_user_photo_description'),
                        'icon' => 'fa-user',
                        'enabled_name' => 'enable_user_photo',
                        'require_name' => 'require_user_photo',
                        'enabled_value' => $testimonial['enable_user_photo'],
                        'required_value' => $testimonial['require_user_photo'],
                    ],
                    'website_url' => [
                        'title' => _flextestimonial_lang('collect_website_url'),
                        'description' => _flextestimonial_lang('collect_website_url_description'),
                        'icon' => 'fa-globe',
                        'enabled_name' => 'enable_website_url',
                        'require_name' => 'require_website_url',
                        'enabled_value' => $testimonial['enable_website_url'],
                        'required_value' => $testimonial['require_website_url'],
                    ],
                    'company_name' => [
                        'title' => _flextestimonial_lang('collect_company_name'),
                        'description' => _flextestimonial_lang('collect_company_name_description'),
                        'icon' => 'fa-building',
                        'enabled_name' => 'enable_company_name',
                        'require_name' => 'require_company_name',
                        'enabled_value' => $testimonial['enable_company_name'],
                        'required_value' => $testimonial['require_company_name'],
                    ]
                ];
                ?>
                <?php foreach ($customer_media as $media): ?>
                    <?php echo $this->load->view('partials/manage/customer-media', $media); ?>
                <?php endforeach; ?>

            <?php elseif ($key == 'thankyou_page'): ?>
                <?php echo render_input('thankyou_title', _flextestimonial_lang('thankyou_title'), $testimonial['thankyou_title']); ?>
                <?php echo render_textarea('thankyou_message', _flextestimonial_lang('thankyou_message'), $testimonial['thankyou_message']); ?>
                <?php //echo render_input('thankyou_video', _flextestimonial_lang('upload_video_file'), '', 'file'); ?>
                <?php echo render_input('thankyou_button_text', _flextestimonial_lang('thankyou_button_text'), $testimonial['thankyou_button_text']); ?>
                <?php echo render_input('thankyou_button_url', _flextestimonial_lang('thankyou_button_url'), $testimonial['thankyou_button_url']); ?>
            <?php elseif ($key == 'word_of_mouth'): ?>
                <?php echo $this->load->view('partials/manage/yes-no', ['label' => _flextestimonial_lang('enable_social_share'), 'name' => 'enable_social_share', 'value' => $testimonial['enable_social_share']]); ?>
            <?php elseif ($key == 'custom_labels'): ?>
                <?php echo render_input('record_a_video_button_label', _flextestimonial_lang('record_a_video_button_label'), $testimonial['record_a_video_button_label']); ?>
                <?php echo render_input('write_a_testimonial_button_label', _flextestimonial_lang('write_a_testimonial_button_label'), $testimonial['write_a_testimonial_button_label']); ?>
                <?php echo render_input('upload_image_button_label', _flextestimonial_lang('upload_image_button_label'), $testimonial['upload_image_button_label']); ?>
                <?php echo render_input('marketing_consent_label', _flextestimonial_lang('marketing_consent_label'), $testimonial['marketing_consent_label']); ?>
            <?php endif; ?>
        </div>
    </div>
</div>