let mediaRecorder;
let recordedChunks = [];
let stream;

$(document).ready(function() {
    const videoPreview = document.getElementById('video-preview');
    const recordButton = document.getElementById('flextestimonial-video-record-button');
    const stopButton = document.getElementById('flextestimonial-video-stop-button');
    const retryButton = document.getElementById('flextestimonial-video-retry-button');
    const videoPlaceholder = document.querySelector('.flex-testimonial-video-placeholder');

    // Function to reset the recording state
    function resetRecordingState() {
        // Clear any existing video data
        window.flextestimonialVideoData = null;
        
        // Reset video element
        videoPreview.srcObject = null;
        videoPreview.src = '';
        videoPreview.pause();
        videoPreview.currentTime = 0;
        videoPreview.style.display = 'none';
        
        // Show placeholder
        videoPlaceholder.style.display = 'block';
        
        // Reset buttons
        recordButton.style.display = 'block';
        stopButton.style.display = 'none';
        retryButton.style.display = 'none';
    }

    // Request camera access and start preview
    recordButton.addEventListener('click', async () => {
        try {
            // Request camera with specific constraints
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'user',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }, 
                audio: true 
            });
            
            // Set up video element
            videoPreview.srcObject = stream;
            videoPreview.muted = true; // Mute to prevent feedback
            videoPreview.play(); // Start playing the video
            
            // Show video and hide placeholder
            videoPreview.style.display = 'block';
            videoPlaceholder.style.display = 'none';
            
            // Check supported MIME types
            const mimeTypes = [
                'video/mp4;codecs=h264',
                'video/mp4;codecs=avc1',
                'video/webm;codecs=vp9',
                'video/webm;codecs=vp8'
            ];
            
            let selectedMimeType = null;
            for (const mimeType of mimeTypes) {
                if (MediaRecorder.isTypeSupported(mimeType)) {
                    selectedMimeType = mimeType;
                    console.log('Using MIME type:', mimeType);
                    break;
                }
            }
            
            if (!selectedMimeType) {
                throw new Error('No supported MIME type found');
            }
            
            // Initialize MediaRecorder with the selected format
            mediaRecorder = new MediaRecorder(stream, {
                mimeType: selectedMimeType
            });
            recordedChunks = [];

            mediaRecorder.ondataavailable = (e) => {
                if (e.data.size > 0) {
                    recordedChunks.push(e.data);
                }
            };

            mediaRecorder.onstop = () => {
                const mimeType = mediaRecorder.mimeType;
                console.log('Recording stopped with MIME type:', mimeType);
                
                const blob = new Blob(recordedChunks, { type: mimeType });
                const videoURL = URL.createObjectURL(blob);
                
                // Check file size before proceeding
                if (blob.size > 10 * 1024 * 1024) { // 10MB limit
                    alert('Video size is too large. Please record a shorter video or use a lower resolution.');
                    resetRecordingState();
                    return;
                }
                
                // Stop all tracks
                stream.getTracks().forEach(track => track.stop());
                
                // Update video element for playback
                videoPreview.srcObject = null;
                videoPreview.src = videoURL;
                videoPreview.muted = false;
                videoPreview.loop = true;
                videoPreview.play().catch(err => console.error('Error playing video:', err));
                
                // Create a file input and attach the video
                const formData = new FormData();
                const fileExtension = mimeType.includes('mp4') ? 'mp4' : 'webm';
                console.log('Saving file with extension:', fileExtension);
                formData.append('video_response', blob, `testimonial.${fileExtension}`);
                
                // Store the form data for later submission
                window.flextestimonialVideoData = formData;

                // Show retry button
                retryButton.style.display = 'block';
            };

            // Start recording
            mediaRecorder.start();
            recordButton.style.display = 'none';
            stopButton.style.display = 'block';
        } catch (err) {
            console.error('Error accessing camera:', err);
            alert('Error accessing camera. Please make sure you have granted camera permissions.');
        }
    });

    // Stop recording
    stopButton.addEventListener('click', () => {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            stopButton.style.display = 'none';
        }
    });

    // Retry recording
    retryButton.addEventListener('click', () => {
        resetRecordingState();
    });
}); 