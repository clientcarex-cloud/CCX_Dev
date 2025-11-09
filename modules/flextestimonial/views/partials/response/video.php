<?php if (isset($response['video_url']) && !empty($response['video_url'])) { 
            $video_id = 'video_' . $response['id'];
        ?>
            <div class="tw-flex tw-items-center tw-justify-center tw-mt-4" id="<?php echo $video_id; ?>_container">
                <video id="<?php echo $video_id; ?>_hidden" style="display:none;" crossorigin="anonymous">
                    <source src="<?php echo flextestimonial_media_url($response['video_url']); ?>" type="video/mp4">
                </video>
                <canvas id="<?php echo $video_id; ?>_canvas" style="display:none;"></canvas>
                <video controls
                    id="<?php echo $video_id; ?>_final"
                    class="tw-w-full tw-max-w-[300px] tw-rounded-lg tw-bg-gray-100"
                    style="--plyr-color-main: #3b82f6;">
                    <source src="<?php echo flextestimonial_media_url($response['video_url']); ?>" type="video/mp4">
                </video>
                <script>
                    (function() {
                        const container = document.getElementById('<?php echo $video_id; ?>_container');
                        const video = document.getElementById('<?php echo $video_id; ?>_hidden');
                        const canvas = document.getElementById('<?php echo $video_id; ?>_canvas');
                        const finalVideo = document.getElementById('<?php echo $video_id; ?>_final');
                        
                        if (!video || !canvas || !finalVideo) return;

                        const ctx = canvas.getContext('2d');

                        video.onloadeddata = function() {
                            video.currentTime = 1;
                        };

                        video.onseeked = function() {
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                            const imgData = canvas.toDataURL('image/jpeg');
                            finalVideo.setAttribute('poster', imgData);
                        };
                    })();
                </script>
            </div>
        <?php } ?>