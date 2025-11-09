<div class="flextestimonial-display tw-flex tw-items-center tw-justify-center"
    style="<?php
            if (isset($testimonial['enable_gradient']) && $testimonial['enable_gradient'] == 1) {
                $darker_shade = _flextestimonial_darken_color($testimonial['primary_color'], 30);
                echo 'background: linear-gradient(135deg, ' . $testimonial['primary_color'] . ', ' . $darker_shade . ');';
            } else {
                echo 'background-color: ' . $testimonial['background_color'] . ';';
            }
            ?> ">
    <div class="bubble bubble-1"></div>
    <div class="bubble bubble-2"></div>
    <div class="bubble bubble-3"></div>
    <div class="bubble bubble-4"></div>

    <!-- White section -->
    <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 70%;<?php echo $testimonial['enable_gradient'] ? ' background: #fafafa' : ' background-color: ' . $testimonial['background_color'] . ';' ?>; clip-path: polygon(0 30%, 100% 0, 100% 100%, 0% 100%);"></div>
   <?php if(!isset($is_manage)): ?>
    <?php echo form_open('flextestimonial/submit/' . $testimonial['slug']); ?>
	<?php
	if(isset($testimonial['request_id'])){
		?>
		<input type="hidden" name="request_id" value="<?php echo $testimonial['request_id'];?>"> 
		<?php
	}
	?>
	
    <?php endif; ?>
    <div class="flextestimonial-display-content tw-container tw-mx-auto tw-px-4 tw-relative">
        <div class="tw-bg-white tw-rounded-lg tw-shadow-lg tw-p-6 tw-max-w-md tw-mx-auto">
            <div class="tw-flex tw-flex-col">
                <?php if ($testimonial['enable_logo'] == 1): ?>
                    <?php get_dark_company_logo('', 'navbar-brand logo'); ?>
                <?php endif; ?>
                <div class="flextestimonial-display-content-section">
                    <div class="flextestimonial-display-content-section-welcome">
                        <?php echo $this->load->view('partials/display/welcome', ['testimonial' => $testimonial]); ?>
                    </div>
                    <?php if ($testimonial['enable_text_testimonial'] == 1): ?>
                        <div class="flextestimonial-display-content-section-text-response">
                            <?php echo $this->load->view('partials/display/text-response', ['testimonial' => $testimonial]); 
							?>
                        </div>
                    <?php endif; ?>
                    <?php if ($testimonial['enable_video_testimonial'] == 1): ?>
                        <div class="flextestimonial-display-content-section-video-response">
                            <?php echo $this->load->view('partials/display/video-response', ['testimonial' => $testimonial]); ?>
                        </div>
                    <?php endif; ?>
                    <div class="flextestimonial-display-content-section-customer-info">
                        <?php echo $this->load->view('partials/display/customer-info', ['testimonial' => $testimonial]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if(!isset($is_manage)): ?>
        <?php form_close(); ?>
    <?php endif; ?>
</div>