<?php
require_once '../config/database.php';

// Require login
if (!isLoggedIn()) {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];
$jobId = $_GET['job_id'] ?? 0;

// Get job details
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND is_active = 1");
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    redirect('jobs.php');
}

// Check if already applied
$stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? AND job_id = ?");
$stmt->execute([$userId, $jobId]);
if ($stmt->fetch()) {
    redirect('jobs.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coverLetter = trim($_POST['cover_letter'] ?? '');
    
    if (empty($coverLetter)) {
        $error = 'Please write a cover letter.';
    } else {
        $resumePath = null;
        
        // Handle resume upload
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['resume']['type'], $allowedTypes)) {
                $error = 'Only PDF and Word documents are allowed.';
            } elseif ($_FILES['resume']['size'] > $maxSize) {
                $error = 'File size must be less than 5MB.';
            } else {
                $ext = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
                $filename = 'resume_' . $userId . '_' . $jobId . '_' . time() . '.' . $ext;
                $uploadPath = '../assets/uploads/' . $filename;
                
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $uploadPath)) {
                    $resumePath = $filename;
                } else {
                    $error = 'Failed to upload resume.';
                }
            }
        }
        
        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO applications (job_id, user_id, cover_letter, resume) VALUES (?, ?, ?, ?)");
            $stmt->execute([$jobId, $userId, $coverLetter, $resumePath]);
            
            $success = 'Your application has been submitted successfully!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply - <?php echo e($job['title']); ?> - TechLearn</title>
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
        
        .job-summary {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .job-summary h3 {
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
        }
        
        .job-summary p {
            color: var(--text-secondary);
            font-size: 0.95rem;
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
                <a href="jobs.php" class="btn btn-ghost">
                    <i data-lucide="arrow-left"></i>
                    Back to Jobs
                </a>
            </div>
        </div>
    </nav>

    <div class="page-container">
        <div class="container">
            <div class="form-card">
                <div class="form-header">
                    <h1>Apply for Position</h1>
                </div>
                
                <div class="job-summary">
                    <h3><?php echo e($job['title']); ?></h3>
                    <p><?php echo e($job['company_name']); ?> • <?php echo ucfirst(str_replace('-', ' ', e($job['job_type']))); ?></p>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i data-lucide="check-circle"></i>
                    <?php echo e($success); ?>
                    <a href="jobs.php" class="btn btn-primary btn-sm" style="margin-left: auto;">Browse More Jobs</a>
                </div>
                <?php else: ?>
                    <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i data-lucide="alert-circle"></i>
                        <?php echo e($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label" for="cover_letter">Cover Letter *</label>
                            <textarea id="cover_letter" name="cover_letter" class="form-textarea" rows="8" 
                                      placeholder="Tell us why you're a great fit for this position..." required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Resume (Optional)</label>
                            <label class="file-upload" id="file-upload-label">
                                <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx">
                                <i data-lucide="upload-cloud" style="width: 48px; height: 48px; margin-bottom: 1rem; color: var(--text-muted);"></i>
                                <p>Click to upload or drag and drop</p>
                                <p style="font-size: 0.875rem; color: var(--text-muted);">PDF, DOC or DOCX (max 5MB)</p>
                            </label>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                                <i data-lucide="send"></i>
                                Submit Application
                            </button>
                            <a href="jobs.php" class="btn btn-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>