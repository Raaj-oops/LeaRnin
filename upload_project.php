<?php
require_once '../config/database.php';

// Require login
if (!isLoggedIn()) {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $githubLink = trim($_POST['github_link'] ?? '');
    $liveDemo = trim($_POST['live_demo'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    
    if (empty($title) || empty($description) || empty($githubLink)) {
        $error = 'Please fill in all required fields.';
    } else {
        $screenshotPath = null;
        
        // Handle screenshot upload
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['screenshot']['type'], $allowedTypes)) {
                $error = 'Only JPG, PNG, and GIF images are allowed.';
            } elseif ($_FILES['screenshot']['size'] > $maxSize) {
                $error = 'Image size must be less than 5MB.';
            } else {
                $ext = pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION);
                $filename = 'project_' . $userId . '_' . time() . '.' . $ext;
                $uploadPath = PROJECT_UPLOADS . $filename;
                
                if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $uploadPath)) {
                    $screenshotPath = $filename;
                } else {
                    $error = 'Failed to upload screenshot.';
                }
            }
        }
        
        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO projects (user_id, title, description, screenshot, github_link, live_demo, technologies) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $title, $description, $screenshotPath, $githubLink, $liveDemo, $technologies]);
            
            $success = 'Project uploaded successfully!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Project - TechLearn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .page-container {
            padding-top: 100px;
            min-height: 100vh;
            background: var(--bg-secondary);
        }
        
        .form-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-header {
            margin-bottom: 2rem;
        }
        
        .form-header h1 {
            font-size: 1.875rem;
            margin-bottom: 0.5rem;
        }
        
        .form-header p {
            color: var(--text-secondary);
        }
        
        .file-upload {
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-lg);
            padding: 2rem;
            text-align: center;
            transition: all var(--transition-fast);
            cursor: pointer;
        }
        
        .file-upload:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }
        
        .file-upload input {
            display: none;
        }
        
        .file-upload-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            color: var(--text-muted);
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: var(--radius-lg);
            margin-top: 1rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--success);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--error);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="../index.php" class="logo">
                <div class="logo-icon">
                    <i data-lucide="code-2"></i>
                </div>
                TechLearn
            </a>
            
            <div class="nav-actions">
                <a href="portfolio.php" class="btn btn-ghost">
                    <i data-lucide="arrow-left"></i>
                    Back to Portfolio
                </a>
            </div>
        </div>
    </nav>

    <div class="page-container">
        <div class="container">
            <div class="form-card">
                <div class="form-header">
                    <h1>Upload Project</h1>
                    <p>Showcase your work to the community</p>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i data-lucide="check-circle"></i>
                    <?php echo e($success); ?>
                    <a href="portfolio.php" class="btn btn-primary btn-sm" style="margin-left: auto;">View Portfolio</a>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <i data-lucide="alert-circle"></i>
                    <?php echo e($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="title">Project Title *</label>
                        <input type="text" id="title" name="title" class="form-input" placeholder="My Awesome Project" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="description">Description *</label>
                        <textarea id="description" name="description" class="form-textarea" rows="4" 
                                  placeholder="Describe your project, what it does, and what you learned..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="technologies">Technologies Used</label>
                        <input type="text" id="technologies" name="technologies" class="form-input" 
                               placeholder="PHP, JavaScript, MySQL, React (comma separated)">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="github_link">GitHub Repository *</label>
                        <input type="url" id="github_link" name="github_link" class="form-input" 
                               placeholder="https://github.com/username/project" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="live_demo">Live Demo URL</label>
                        <input type="url" id="live_demo" name="live_demo" class="form-input" 
                               placeholder="https://myproject.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Project Screenshot</label>
                        <label class="file-upload" id="file-upload-label">
                            <input type="file" name="screenshot" id="screenshot" accept="image/*">
                            <i data-lucide="upload-cloud" class="file-upload-icon"></i>
                            <p>Click to upload or drag and drop</p>
                            <p style="font-size: 0.875rem; color: var(--text-muted);">PNG, JPG or GIF (max 5MB)</p>
                            <img src="" alt="Preview" class="preview-image" id="preview-image" style="display: none;">
                        </label>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                            <i data-lucide="upload"></i>
                            Upload Project
                        </button>
                        <a href="portfolio.php" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        const fileInput = document.getElementById('screenshot');
        const previewImage = document.getElementById('preview-image');
        
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>