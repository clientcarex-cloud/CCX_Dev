<?php

$job_title = isset($response['job_title']) && !empty($response['job_title']) ? $response['job_title'] : '';
$company_name = isset($response['company_name']) && !empty($response['company_name']) ? $response['company_name'] : '';
$website_url = isset($response['website_url']) && !empty($response['website_url']) ? $response['website_url'] : '';
$sub_title = "";
if ($job_title && $company_name) {
    $sub_title = $job_title . ' ' . _flextestimonial_lang('at') . ' ' . $company_name;
} else if ($job_title) {
    $sub_title = $job_title;
} else if ($company_name) {
    $sub_title = $company_name;
}
if($website_url){
    $sub_title = '<a class="tw-text-gray-600" href="'.$website_url.'" target="_blank">'.$sub_title.'</a>';
}
?>

<!-- Profile Section -->
<div class="tw-flex tw-items-center tw-mb-2">
    <div class="tw-w-12 tw-h-12 tw-rounded-full tw-overflow-hidden tw-mr-4">
        <?php if (isset($response['user_photo']) && !empty($response['user_photo'])) : ?>
            <img src="<?php echo flextestimonial_media_url($response['user_photo']); ?>" alt="Profile" class="flex-testimonial-user-photo tw-w-full tw-h-full tw-object-cover">
        <?php else : ?>
            <div class="tw-w-full tw-h-full tw-bg-gray-600 tw-flex tw-items-center tw-justify-center">
                <span class="tw-text-gray-500 tw-text-2xl"><?php echo substr($response['name'] ?? 'A', 0, 1); ?></span>
            </div>
        <?php endif; ?>
    </div>
    <div>
        <h3 class="tw-font-semibold tw-text-gray-800 tw-mt-3 tw-mb-1"><?php echo $response['name'] ?? ''; ?></h3>
        <p class="tw-text-gray-600">
            <?php echo $sub_title; ?>
        </p>
    </div>
</div>

<!-- Star Rating and Text Response -->
<div class="tw-flex tw-flex-col">
    <?php
    $rating = isset($response['rating']) ? (int)$response['rating'] : 0;
    $max_rating = 5;
    if ($rating) {
    ?>
        <div class="flex-testimonial-rating tw-w-full tw-mb-3">
            <?php for ($i = 0; $i < $max_rating; $i++) { ?>
                <span class="flex-testimonial-rating-star <?php echo $i < $rating ? 'active' : ''; ?>">
                    <i class="fa-solid fa-star"></i>
                </span>
            <?php } ?>
        </div>
    <?php } ?>
    <!-- Review Text -->
    <div class="tw-text-gray-700">
        <?php if (isset($response['text_response']) && !empty($response['text_response'])) : ?>
            <?php echo nl2br($response['text_response']); ?>
        <?php endif; ?>

        <?php echo $this->load->view('partials/response/video', ['response' => $response]); ?>
        <!--images-->
        <?php echo $this->load->view('partials/response/images', ['response' => $response]); ?>
        <p class="tw-text-gray-600 tw-mt-2">
            <?php echo date('d M, Y', strtotime($response['created_at'])); ?>
        </p>
    </div>
</div>