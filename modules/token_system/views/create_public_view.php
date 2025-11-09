<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Public Token Display</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
            background-color: #f8f9fa;
        }
        .header {
            background: #fff;
            padding: 10px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container-fluid {
            height: calc(100% - 60px);
        }
        .left-panel {
            background: #000;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .video-frame, .image-slider {
			width: 100%;
			height: 100%;
			display: none;
			display: flex;
			justify-content: center; /* Center horizontally */
			align-items: center; /* Center vertically */
		}

        .control-buttons {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 10;
        }
        .carousel-item img {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }
        .right-panel {
            background: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .section-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
			border-top: 1px solid #ddd;
			border-bottom: 1px solid #ddd;
        }
		
		.doctor-card {
			display: flex;
			align-items: center;
			background: #f1f1f1;
			border-radius: 10px;
			padding: 10px;
			height: 120px; /* Adjust the height as needed */
		}

		.doctor-card img {
			margin-right: 15px; /* Space between image and details */
		}

		.doctor-details {
			display: flex;
			flex-direction: column;
			justify-content: center; /* Center the text vertically within the available space */
		}

		.patient-card {
			display: flex;
			align-items: center;
			background: #f1f1f1;
			border-radius: 10px;
			padding: 10px;
			margin-bottom: 10px;
		}

		.patient-card img {
			margin-right: 15px; /* Space between image and details */
		}

		.patient-details {
			display: flex;
			flex-direction: column;
		}

        .right-top, .right-middle, .right-bottom, .right-footer {
            padding: 10px;
        }
        .right-bottom {
            height: 70%;
            overflow-y: auto;
        }
        .right-footer {
            height: 10%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .emergency-alert {
			position: fixed;
			top: 60px;
			right: 0;
			width: 33.333%;
			height: calc(100% - 60px);
			background: rgba(255,0,0,0.4);
			color: white;
			display: none;
			justify-content: center;
			align-items: center;
			font-size: 2rem;
			z-index: 1000;
		}

        .luchbreak-alert {
			position: fixed;
			top: 60px;
			right: 0;
			width: 33.333%;
			height: calc(100% - 60px);
			background: rgba(0, 0, 0, 0.4);
			color: white;
			display: none;
			justify-content: center;
			align-items: center;
			font-size: 2rem;
			z-index: 1000;
		}

        .blur {
            filter: blur(5px);
            pointer-events: none;
        }
		.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.datetime-display {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.5rem;
    color: #000;
    font-weight: bold;
}
.right-top, .right-middle {
    padding: 0; /* Remove extra padding */
    margin: 0; /* Remove extra margin */
}

.doctor-card{
	margin-top: 10px;
   ! margin-bottom: 10px; /* Reduce the space between cards */
}


.doctor-card img {
    margin-bottom: 5px; /* Adjust space between image and text */
}





.patient-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
    background: #f9f9f9; /* Background color */
    border-radius: 10px; /* Rounded corners */
}

.patient-card img {
    margin-right: 15px; /* Space between image and details */
}

.patient-details {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    width: 100%; /* Ensure it spans the full width */
}

.patient-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* Keep the name and age left aligned */
}

.patient-name {
    font-size: 1.2rem; /* Increase the size of the name */
    font-weight: bold;
}

.token-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end; /* Align token number and status to the right */
}

.token-label {
    font-size: 0.9rem; /* Smaller size for the label */
    color: #333; /* Default color for the label */
}

.token-number {
    font-size: 1.2rem; /* Bigger size for token number */
    color: #8787dd; /* Blue color for token number */
    font-weight: bold;
}

button {
    margin-top: 5px;
}


    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <?php $logo = get_admin_header_logo_url(); ?>
    <img src="<?= e($logo); ?>" class="img-responsive" alt="<?= e(get_option('companyname')); ?>" style="width: 150px"/>
   <i class="fas fa-expand" onclick="toggleFullScreen()"></i>
    

    <!-- Centered DateTime Display -->
    <div id="datetime" class="datetime-display"></div>
</div>


<div class="container-fluid">
    <div class="row h-90" id="right-content">
        
        <!-- Left Panel -->
        <div class="col-md-8 p-0 left-panel">
            <!--<div class="control-buttons">
                <button class="btn btn-primary btn-sm" onclick="showVideo()">Play Video</button>
                <button class="btn btn-secondary btn-sm" onclick="showSlider()">Show Images</button>
            </div>-->
			<?php
			//print_r($counter);
			
			//print_r($display_images);
			if($counter->youtube_link){
			?>
            <div class="video-frame">
                <iframe width="100%" height="100%"
                    src="<?php echo $counter->youtube_link;?>"
                    frameborder="0" allow="autoplay; fullscreen" allowfullscreen>
                </iframe>
            </div>
			<?php
			}else{
				$display_images = get_display_images($counter->display_id);
			?>
            <div class="image-slider">
                <div id="carouselExampleIndicators" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="4000">
                    <div class="carousel-inner h-100">
					
						<?php
						foreach($display_images as $images){
						?>
                        <div class="carousel-item active">
                            <img src="<?= base_url($images['image_path']); ?>" alt="Hospital">
                        </div>
                        <?php
						}
						?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>
			<?php
			}
			?>
        </div>

        <!-- Right Panel -->
        <div class="col-md-4 right-panel">
    
			<div class="right-top" style="height: 30%">
				<div class="doctor-card">
					<img src="https://randomuser.me/api/portraits/men/32.jpg" class="rounded-circle" width="80" height="80" alt="Doctor">
					<div class="doctor-details">
						<h6 class="mt-2"><?php echo $counter->salutation.' '.$counter->doctor_name;?></h6>
						<small><?php echo _l('experience'); ?>: <?php echo $counter->experience_years;?> <?php echo _l('years'); ?></small>
						<small><?php echo _l('specialization'); ?>: <?php echo $counter->specialization_name;?></small>
						<small><?php echo _l('languages'); ?>: English, Hindi, Telugu</small>
						<br>
					</div>
				</div>
			</div>
			
			<div class="right-middle">
				<div class="section-title" style="margin-top: 5px;"><?php echo _l('serving_patient'); ?></div>
				<?php
				if($current_patient && !empty($current_patient)){
					$current_patient = $current_patient[0];
				?>
					<div class="patient-card">
						<img src="https://images.vexels.com/media/users/3/134789/isolated/lists/aa4c5ca0e2a83abbf167e49c8a50e834-happy-smile-emoji-emoticon-icon.png" class="rounded-circle" width="60" height="60" alt="Patient">
						<div class="patient-details">
							<div class="patient-info">
								<h6 class="patient-name"><?php echo $current_patient['salutation'].' '.$current_patient['company'];?></h6>
								<small><?php echo _l('date'); ?>: <?php echo _d($current_patient['date']);?></small> <!-- Added new parameter below name -->
							</div>
							<div class="token-info">
								<span class="token-label"><?php echo _l('token'); ?>:<span class="token-number"> #<?php echo $current_patient['token_number'];?></span></span> 
								<button class="btn btn-success btn-sm mt-2"><?php echo _l('serving'); ?></button>
							</div>
						</div>
					</div>
				<?php
				} else {
					// If no patients
					echo '<div class="no-patient">'. _l('no_patients') .'</div>';
				}
				?>
			</div>

			<div class="right-bottom">
				<div class="section-title"><?php echo _l('queued_patients'); ?></div>
				
				<?php
				if(!empty($queued_patients)) {
					foreach($queued_patients as $queue) {
						$token_status = "Serving";
				?>
					<!-- Example Next Patients -->
					<div class="patient-card">
						<img src="https://images.vexels.com/media/users/3/134789/isolated/lists/aa4c5ca0e2a83abbf167e49c8a50e834-happy-smile-emoji-emoticon-icon.png" class="rounded-circle" width="60" height="60" alt="Smiley Emoji">

						<div class="patient-details">
							<div class="patient-info">
								<h6 class="patient-name"><?php echo $queue['salutation'].' '.$queue['company'];?></h6>
								<small><?php echo _l('date'); ?>: <?php echo _d($queue['date']);?></small> <!-- Added new parameter below name -->
							</div>
							<div class="token-info">
								<span class="token-label"><?php echo _l('token'); ?>: <span class="token-number">#<?php echo $queue['token_number'];?></span> </span>
								<a href="<?= admin_url('token_system/next_call_public/'.$queue['token_id'].'/'.$token_status.'/'.$counter->counter_id); ?>"><button class="btn btn-warning btn-sm mt-2"><?php echo _l('call'); ?></button></a>
							</div>
						</div>
					</div>
				<?php
					}
				} else {
					// If no queued patients
					echo '<div class="no-patient">'. _l('no_queued_patients') .'</div>';
				}
				?>
			</div>
			
			<div class="right-footer">
				<button onclick="toggleEmergency()" class="btn btn-danger w-100">
					<i class="fas fa-triangle-exclamation"></i> <?php echo _l('emergency'); ?>
				</button>
			</div>
		</div>

    </div>
</div>

<!-- Emergency Alert -->
<div id="emergency-alert" class="emergency-alert d-none">
    <i class="fas fa-bell fa-shake"></i>&nbsp; EMERGENCY
</div>

<!-- Emergency Alert -->
<div id="luchbreak-alert" class="luchbreak-alert d-none">
    <i class="fas fa-pause"></i>&nbsp; Paused
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($counter->counter_status == "Emergency") { ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            toggleEmergency(); // Call the function when the page loads
        });
    </script>
<?php } ?>

<?php 
// Automatically toggle the lunch break if the counter is on a lunch break
if ($counter->counter_status == "Lunch Break") { ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            toggleLunchBreak(); // Call the function when the page loads
        });
    </script>
<?php } ?>
<script>
function toggleLunchBreak() {
    const rightPanel = document.querySelector('.right-panel');
    const lunchAlert = document.getElementById('luchbreak-alert');

    if (lunchAlert.classList.contains('d-none')) {
        rightPanel.classList.add('blur');
        lunchAlert.classList.remove('d-none');
        lunchAlert.classList.add('d-flex');
    } else {
        rightPanel.classList.remove('blur');
        lunchAlert.classList.add('d-none');
        lunchAlert.classList.remove('d-flex');
    }
}



function showVideo() {
    document.getElementById('videoFrame').style.display = 'block';
    document.getElementById('imageSlider').style.display = 'none';
}
function showSlider() {
    document.getElementById('videoFrame').style.display = 'none';
    document.getElementById('imageSlider').style.display = 'block';
}
function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}
function toggleEmergency() {
    const rightPanel = document.querySelector('.right-panel');
    const emergencyAlert = document.getElementById('emergency-alert');

    if (emergencyAlert.classList.contains('d-none')) {
        rightPanel.classList.add('blur');
        emergencyAlert.classList.remove('d-none');
        emergencyAlert.classList.add('d-flex');
    } else {
        rightPanel.classList.remove('blur');
        emergencyAlert.classList.add('d-none');
        emergencyAlert.classList.remove('d-flex');
    }
}

function updateTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const date = now.toLocaleDateString();
    
    document.getElementById("datetime").textContent = `${date} ${hours}:${minutes}:${seconds}`;
}

// Update the time every second
setInterval(updateTime, 1000);

// Initialize the time immediately
updateTime();


</script>

</body>
</html>
