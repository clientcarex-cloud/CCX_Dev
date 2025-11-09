<div class="tw-flex tw-items-start tw-p-2 tw-mb-2 tw-bg-white tw-rounded-lg tw-shadow">
    <div class="tw-flex-shrink-0 tw-mr-4">
        <i class="fas <?php echo $icon; ?> tw-text-lg tw-bg-gray-100 tw-p-1 tw-rounded-full tw-text-blue-500"></i>
    </div>

    <div class="tw-flex tw-flex-col tw-flex-grow tw-space-y-2">
        <div class="tw-flex">
            <h6 class="tw-text-sm tw-m-0 tw-font-semibold"><?php echo $title; ?></h6>
        </div>
        <div class="tw-flex">
            <p class="tw-m-0 tw-text-sm"><?php echo $description; ?></p>
        </div>
        <!-- Enable/Disable -->
        <div class="tw-flex">
           <!--load yes no view here-->
           <?php echo $this->load->view('partials/manage/yes-no', ['label' => _flextestimonial_lang('enabled'), 'name' => $enabled_name, 'value' => $enabled_value]); ?>
        </div>
        <!-- Required -->
        <div class="tw-flex">
            <!--load yes no view here-->
            <?php echo $this->load->view('partials/manage/yes-no', ['label' => _flextestimonial_lang('required'), 'name' => $require_name, 'value' => $required_value]); ?>
        </div>
    </div>

</div>