
<div class="flextestimonial-display flextestimonial-display-response  flextestimonial-full-page-display tw-flex tw-items-center tw-justify-center"
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
    <div class="flextestimonial-display-content tw-container tw-mx-auto tw-px-4 tw-relative">
        <div class="tw-bg-white tw-rounded-lg tw-shadow-lg tw-p-4 tw-max-w-md tw-mx-auto">
            <div class="tw-flex tw-flex-col">
                <?php get_dark_company_logo('', 'navbar-brand logo'); ?>
                <?php echo $this->load->view('partials/response-card', ['response' => $response, 'testimonial' => $testimonial]); ?>
            </div>
        </div>
    </div>
</div>