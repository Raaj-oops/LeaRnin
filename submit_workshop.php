<?php
require_once '../config/database.php';

// Require login
if (!isLoggedIn()) {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];
$lessonId = $_GET['lesson_id'] ?? 0;

// Get lesson details
$stmt = $pdo->prepare("SELECT l.*, m.course_id, c.title as course_title 
                       FROM lessons l 
                       JOIN modules m ON l.module_id = m.id 
                       JOIN courses c ON m.course_id = c.id
                       WHERE l.id = ?");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    redirect('../courses/courses.php');
}

// Check enrollment
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$userId, $lesson['course_id']]);
if (!$stmt->fetch()) {
    redirect('../courses/course_details.php?id=' . $lesson['course_id']);
}

// Get existing submission
$stmt = $pdo->prepare("SELECT * FROM workshop_submissions WHERE user_id = ? AND lesson_id = ?");
$stmt->execute([$userId, $lessonId]);
$submission = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $githubLink = trim($_POST['github_link'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($githubLink) && empty($description)) {
        $error = 'Please provide either a GitHub link or a description of your solution.';
    } else {
        $screenshotPath = $submission['screenshots'] ?? null;
        
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
                $filename = 'submission_' . $userId . '_' . $lessonId . '_' . time() . '.' . $ext;
                $uploadPath = SUBMISSION_UPLOADS . $filename;
                
                if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $uploadPath)) {
                    // Delete old screenshot if exists
                    if ($screenshotPath && file_exists(SUBMISSION_UPLOADS . $screenshotPath)) {
                        unlink(SUBMISSION_UPLOADS . $screenshotPath);
                    }
                    $screenshotPath = $filename;
                } else {
                    $error = 'Failed to upload screenshot.';
                }
            }
        }
        
        if (empty($error)) {
            if ($submission) {
                // Update existing submission
                $stmt = $pdo->prepare("UPDATE workshop_submissions 
                                       SET github_link = ?, description = ?, screenshots = ?, status = 'pending', submitted_at = NOW() 
                                       WHERE id = ?");
                $stmt->execute([$githubLink, $description, $screenshotPath, $submission['id']]);
            } else {
                // Create new submission
                $stmt = $pdo->prepare("INSERT INTO workshop_submissions (user_id, lesson_id, github_link, description, screenshots) 
                                       VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $lessonId, $githubLink, $description, $screenshotPath]);
            }
            
            $success = 'Your solution has been submitted successfully!';
            
            // Refresh submission data
            $stmt = $pdo->prepare("SELECT * FROM workshop_submissions WHERE user_id = ? AND lesson_id = ?");
            $stmt->execute([$userId, $lessonId]);
            $submission = $stmt->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Workshop - TechLearn</title>
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
        
        .workshop-info {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(249, 115, 22, 0.1) 100%);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .workshop-info h3 {
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
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
        
        .file-upload-text {
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .file-upload-hint {
            font-size: 0.875rem;
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
                <a href="../courses/lesson.php?id=<?php echo $lessonId; ?>" class="btn btn-ghost">
                    <i data-lucide="arrow-left"></i>
                    Back to Lesson
                </a>
            </div>
        </div>
    </nav>

    <div class="page-container">
        <div class="container">
            <div class="form-card">
                <div class="form-header">
                    <h1>Submit Workshop Solution</h1>
                    <p><?php echo e($lesson['course_title']); ?> • <?php echo e($lesson['title']); ?></p>
                </div>
                
                <div class="workshop-info">
                    <h3><i data-lucide="wrench"></i> Challenge</h3>
                    <p><?php echo nl2br(e($lesson['workshop_problem'])); ?></p>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i data-lucide="check-circle"></i>
                    <?php echo e($success); ?>
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
                        <label class="form-label" for="github_link">GitHub Repository Link</label>
                        <input type="url" id="github_link" name="github_link" class="form-input" 
                               placeholder="https://github.com/username/project" 
                               value="<?php echo e($submission['github_link'] ?? ''); ?>">
                        <p class="form-hint">Share the link to your GitHub repository with the solution</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="description">Solution Description</label>
                        <textarea id="description" name="description" class="form-textarea" rows="5" 
                                  placeholder="Describe your approach, challenges faced, and how you solved them..."><?php echo e($submission['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Screenshot (Optional)</label>
                        <label class="file-upload" id="file-upload-label">
                            <input type="file" name="screenshot" id="screenshot" accept="image/*">
                            <i data-lucide="upload-cloud" class="file-upload-icon"></i>
                            <p class="file-upload-text">Click to upload or drag and drop</p>
                            <p class="file-upload-hint">PNG, JPG or GIF (max 5MB)</p>
                            <?php if ($submission && $submission['screenshots']): ?>
                            <img src="../assets/uploads/submissions/<?php echo e($submission['screenshots']); ?>" alt="Current screenshot" class="preview-image" id="preview-image">
                            <?php else: ?>
                            <img src="" alt="Preview" class="preview-image" id="preview-image" style="display: none;">
                            <?php endif; ?>
                        </label>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                            <i data-lucide="upload"></i>
                            <?php echo $submission ? 'Update Submission' : 'Submit Solution'; ?>
                        </button>
                        <a href="../courses/lesson.php?id=<?php echo $lessonId; ?>" class="btn btn-secondary btn-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        // File upload preview
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