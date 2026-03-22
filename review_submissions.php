<?php
require_once '../config/database.php';

// Require admin
if (!isAdmin()) {
    redirect('../dashboard.php');
}

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $submissionId = $_POST['submission_id'] ?? 0;
    $grade = $_POST['grade'] ?? 0;
    $feedback = trim($_POST['feedback'] ?? '');
    $status = $_POST['status'] ?? 'pending';
    
    $stmt = $pdo->prepare("UPDATE workshop_submissions 
                           SET grade = ?, feedback = ?, status = ?, reviewed_by = ?, reviewed_at = NOW() 
                           WHERE id = ?");
    $stmt->execute([$grade, $feedback, $status, $_SESSION['user_id'], $submissionId]);
    
    // Update lesson progress if approved
    if ($status === 'approved') {
        $stmt = $pdo->prepare("SELECT lesson_id, user_id FROM workshop_submissions WHERE id = ?");
        $stmt->execute([$submissionId]);
        $sub = $stmt->fetch();
        
        if ($sub) {
            $stmt = $pdo->prepare("INSERT INTO lesson_progress (user_id, lesson_id, is_workshop_completed) 
                                   VALUES (?, ?, 1) 
                                   ON DUPLICATE KEY UPDATE is_workshop_completed = 1");
            $stmt->execute([$sub['user_id'], $sub['lesson_id']]);
        }
    }
    
    setFlash('success', 'Submission reviewed successfully!');
    redirect('review_submissions.php');
}

// Get all submissions with details
$submissions = $pdo->query("SELECT ws.*, u.name as student_name, u.email as student_email,
                            l.title as lesson_title, c.title as course_title,
                            reviewer.name as reviewer_name
                            FROM workshop_submissions ws
                            JOIN users u ON ws.user_id = u.id
                            JOIN lessons l ON ws.lesson_id = l.id
                            JOIN modules m ON l.module_id = m.id
                            JOIN courses c ON m.course_id = c.id
                            LEFT JOIN users reviewer ON ws.reviewed_by = reviewer.id
                            ORDER BY ws.submitted_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submissions - TechLearn Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
            padding-top: 72px;
        }
        
        .admin-sidebar {
            background: var(--gradient-dark);
            color: white;
            position: fixed;
            left: 0;
            top: 72px;
            bottom: 0;
            width: 260px;
            overflow-y: auto;
        }
        
        .admin-sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .admin-sidebar-header h2 {
            color: white;
            font-size: 1.25rem;
        }
        
        .admin-nav {
            list-style: none;
            padding: 1rem 0;
        }
        
        .admin-nav li {
            margin-bottom: 0.25rem;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            color: rgba(255,255,255,0.7);
            font-weight: 500;
            transition: all var(--transition-fast);
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .admin-nav a.active {
            border-left: 3px solid var(--primary);
        }
        
        .admin-main {
            margin-left: 260px;
            padding: 2rem;
            background: var(--bg-secondary);
            min-height: calc(100vh - 72px);
        }
        
        .admin-header {
            margin-bottom: 2rem;
        }
        
        .admin-header h1 {
            font-size: 1.875rem;
        }
        
        .submission-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .submission-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .submission-info h3 {
            font-size: 1.125rem;
            margin-bottom: 0.25rem;
        }
        
        .submission-info p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending { background: rgba(245, 158, 11, 0.1); color: var(--accent); }
        .status-approved { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .status-rejected { background: rgba(239, 68, 68, 0.1); color: var(--error); }
        
        .submission-content {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .submission-content h4 {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }
        
        .grade-form {
            display: grid;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        
        .grade-inputs {
            display: grid;
            grid-template-columns: 120px 1fr 150px;
            gap: 1rem;
        }
        
        @media (max-width: 1024px) {
            .admin-sidebar {
                display: none;
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .grade-inputs {
                grid-template-columns: 1fr;
            }
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
                <a href="../dashboard.php" class="btn btn-ghost">User Dashboard</a>
                <a href="../logout.php" class="btn btn-primary">Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <ul class="admin-nav">
                <li><a href="admin_dashboard.php">
                    <i data-lucide="layout-dashboard"></i>
                    Dashboard
                </a></li>
                <li><a href="manage_courses.php">
                    <i data-lucide="book-open"></i>
                    Courses
                </a></li>
                <li><a href="manage_users.php">
                    <i data-lucide="users"></i>
                    Users
                </a></li>
                <li><a href="review_submissions.php" class="active">
                    <i data-lucide="code"></i>
                    Submissions
                </a></li>
                <li><a href="manage_jobs.php">
                    <i data-lucide="briefcase"></i>
                    Jobs
                </a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Review Workshop Submissions</h1>
            </div>
            
            <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: var(--radius-lg); background: rgba(16, 185, 129, 0.1); color: var(--success);">
                <?php echo e($flash['message']); ?>
            </div>
            <?php endif; ?>
            
            <?php foreach ($submissions as $sub): ?>
            <div class="submission-card">
                <div class="submission-header">
                    <div class="submission-info">
                        <h3><?php echo e($sub['student_name']); ?></h3>
                        <p><?php echo e($sub['course_title']); ?> • <?php echo e($sub['lesson_title']); ?></p>
                    </div>
                    <span class="status-badge status-<?php echo e($sub['status']); ?>">
                        <?php echo ucfirst(e($sub['status'])); ?>
                    </span>
                </div>
                
                <?php if ($sub['github_link']): ?>
                <div class="submission-content">
                    <h4>GitHub Repository</h4>
                    <a href="<?php echo e($sub['github_link']); ?>" target="_blank" style="display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="github"></i>
                        <?php echo e($sub['github_link']); ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if ($sub['description']): ?>
                <div class="submission-content">
                    <h4>Description</h4>
                    <p><?php echo nl2br(e($sub['description'])); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($sub['screenshots']): ?>
                <div class="submission-content">
                    <h4>Screenshot</h4>
                    <img src="../assets/uploads/submissions/<?php echo e($sub['screenshots']); ?>" alt="Submission screenshot" style="max-width: 100%; max-height: 300px; border-radius: var(--radius-lg);">
                </div>
                <?php endif; ?>
                
                <?php if ($sub['status'] !== 'pending'): ?>
                <div class="submission-content">
                    <h4>Review</h4>
                    <p><strong>Grade:</strong> <?php echo $sub['grade']; ?>/100</p>
                    <p><strong>Feedback:</strong> <?php echo e($sub['feedback']); ?></p>
                    <p><strong>Reviewed by:</strong> <?php echo e($sub['reviewer_name']); ?> on <?php echo date('M j, Y', strtotime($sub['reviewed_at'])); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($sub['status'] === 'pending'): ?>
                <form method="POST" class="grade-form">
                    <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                    
                    <div class="grade-inputs">
                        <div>
                            <label class="form-label">Grade (0-100)</label>
                            <input type="number" name="grade" class="form-input" min="0" max="100" required>
                        </div>
                        <div>
                            <label class="form-label">Feedback</label>
                            <input type="text" name="feedback" class="form-input" placeholder="Provide feedback..." required>
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input" required>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" name="grade_submission" class="btn btn-primary">
                        <i data-lucide="check"></i>
                        Submit Review
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>