<div class="flextestimonial-wall-of-love">
    <div class="flextestimonial-wall-of-love-header text-center">
        <h1 class="tw-text-2xl tw-font-bold"><?php echo _flextestimonial_lang('wall_of_love_title'); ?></h1>
        <h2 class="flextestimonial-wall-of-love-description tw-mb-6 tw-font-normal"><?php echo _flextestimonial_lang('wall_of_love_description'); ?></h2>
    </div>
    <div class="flextestimonial-wall-of-love-responses">
        <div class="flextestimonial-display-content tw-container tw-mx-auto tw-px-4 tw-relative">
            <div class="">
                <div class="tw-grid-masonry">
                    <?php foreach ($responses as $response) : ?>
                        <div class="tw-grid-item tw-bg-white tw-rounded-lg tw-shadow-lg tw-p-6 tw-w-full tw-mb-6">
                            <?php echo $this->load->view('partials/response-card', ['response' => $response]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="tw-flex tw-justify-center tw-mt-6">
                    <button 
                    data-url="<?php echo base_url('flextestimonial/more_responses'); ?>"
                    data-limit="<?php echo 6; ?>"
                    data-offset="<?php echo 6; ?>"
                    data-loading="<?php echo _flextestimonial_lang('loading'); ?>"
                    data-loadmore="<?php echo _flextestimonial_lang('load_more'); ?>"
                    id="flextestimonial-wall-of-love-load-more-button"
                    class="flextestimonial-wall-of-love-load-more-button btn btn-primary">
                        <?php echo _flextestimonial_lang('load_more'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>