<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Link Expired</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(to bottom right, #f8f9fa, #e9ecef);
            min-height: 100vh;
        }
        .link-expired-container {
            max-width: 500px;
            margin: auto;
            padding: 2rem;
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .back-icon {
            position: fixed;
            top: 1rem;
            left: 1rem;
            background-color: white;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            color: #333;
        }
        .back-icon:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

    <!-- Floating Back Icon -->
    

    <!-- Main Container -->
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="link-expired-container">
            <div class="text-danger mb-3">
                <i class="fas fa-link-slash fa-4x"></i>
            </div>
            <h2 class="mb-3">Link Expired</h2>
            <p class="text-muted mb-4">
                The testimonial link you are trying to access has expired or is no longer available.
            </p>
            <!--<a href="<?php echo site_url(); ?>" class="btn btn-primary">
                <i class="fas fa-home me-2"></i> Go to Homepage
            </a>--
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
