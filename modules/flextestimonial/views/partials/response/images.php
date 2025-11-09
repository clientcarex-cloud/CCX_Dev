<?php if (isset($response['images']) && !empty($response['images'])) : ?>
            <?php $images = flextestimonial_perfect_unserialize($response['images']);
            $validImages = array_filter($images); // Remove empty values
            if (!empty($validImages)) : 
            $slider_id = 'flex-testimonial-' . $response['id']; ?>

                <div class="flex-testimonial-slider" id="<?php echo $slider_id; ?>">
                    <div class="flex-testimonial-container">
                        <?php foreach ($validImages as $index => $image) : ?>
                            <img src="<?php echo flextestimonial_media_url($image); ?>"
                                alt="Testimonial Image"
                                class="flex-testimonial-image <?php echo $index === 0 ? 'active' : ''; ?>"
                                data-index="<?php echo $index; ?>">
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($validImages) > 1) : ?>
                        <button class="flex-testimonial-nav-btn prev" onclick="moveSlide('<?php echo $slider_id; ?>', -1)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <button class="flex-testimonial-nav-btn next" onclick="moveSlide('<?php echo $slider_id; ?>', 1)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>

                        <div class="flex-testimonial-dots">
                            <?php foreach ($validImages as $index => $image) : ?>
                                <button class="flex-testimonial-dot <?php echo $index === 0 ? 'active' : ''; ?>"
                                    onclick="goToSlide('<?php echo $slider_id; ?>', <?php echo $index; ?>)"
                                    data-index="<?php echo $index; ?>">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <script>
                    (function() {
                        const sliderId = '<?php echo $slider_id; ?>';
                        const slider = document.getElementById(sliderId);
                        const sliderState = {
                            currentSlide: 0,
                            images: slider.querySelectorAll('.flex-testimonial-image'),
                            dots: slider.querySelectorAll('.flex-testimonial-dot'),
                            totalSlides: <?php echo count($validImages); ?>
                        };

                        // Store slider state globally
                        window.flexTestimonialSliders = window.flexTestimonialSliders || {};
                        window.flexTestimonialSliders[sliderId] = sliderState;
                    })();

                    function showSlide(sliderId, index) {
                        const state = window.flexTestimonialSliders[sliderId];
                        // Hide all images
                        state.images.forEach(img => img.classList.remove('active'));
                        state.dots?.forEach(dot => dot.classList.remove('active'));

                        // Show current image
                        state.images[index].classList.add('active');
                        state.dots[index]?.classList.add('active');
                    }

                    function moveSlide(sliderId, direction) {
                        const state = window.flexTestimonialSliders[sliderId];
                        state.currentSlide = (state.currentSlide + direction + state.totalSlides) % state.totalSlides;
                        showSlide(sliderId, state.currentSlide);
                    }

                    function goToSlide(sliderId, index) {
                        const state = window.flexTestimonialSliders[sliderId];
                        state.currentSlide = index;
                        showSlide(sliderId, state.currentSlide);
                    }
                </script>
            <?php endif; ?>
        <?php endif; ?>