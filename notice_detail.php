<?php
require_once 'config/database.php';

$noticeId = $_GET['id'] ?? 0;

// Get notice details
$stmt = $pdo->prepare("SELECT n.*, u.name as author_name FROM notices n JOIN users u ON n.created_by = u.id WHERE n.id = ?");
$stmt->execute([$noticeId]);
$notice = $stmt->fetch();

if (!$notice) {
    redirect('notices.php');
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply']) && isLoggedIn()) {
    $content = trim($_POST['content'] ?? '');
    
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO notice_replies (notice_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$noticeId, $_SESSION['user_id'], $content]);
        redirect("notice_detail.php?id=$noticeId");
    }
}

// Get replies
$stmt = $pdo->prepare("SELECT r.*, u.name as author_name, u.profile_picture 
                       FROM notice_replies r 
                       JOIN users u ON r.user_id = u.id 
                       WHERE r.notice_id = ? 
                       ORDER BY r.created_at ASC");
$stmt->execute([$noticeId]);
$replies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($notice['title']); ?> - TechLearn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .page-container {
            padding-top: 100px;
            min-height: 100vh;
            background: var(--bg-secondary);
        }
        
        .notice-detail {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        
        .notice-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        
        .notice-card.urgent {
            border-color: var(--error);
        }
        
        .notice-card.high {
            border-color: var(--accent);
        }
        
        .notice-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .notice-title {
            font-size: 1.75rem;
            font-weight: 700;
            flex: 1;
        }
        
        .priority-badge {
            padding: 0.375rem 0.875rem;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .priority-urgent { background: rgba(239, 68, 68, 0.1); color: var(--error); }
        .priority-high { background: rgba(245, 158, 11, 0.1); color: var(--accent); }
        .priority-medium { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .priority-low { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        
        .notice-content {
            color: var(--text-secondary);
            line-height: 1.8;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .notice-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .replies-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .replies-section h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .reply-card {
            display: flex;
            gap: 1rem;
            padding: 1.25rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
        }
        
        .reply-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }
        
        .reply-content {
            flex: 1;
        }
        
        .reply-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .reply-author {
            font-weight: 600;
        }
        
        .reply-time {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .reply-text {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .reply-form {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2rem;
        }
        
        .reply-form h3 {
            font-size: 1.125rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i data-lucide="code-2"></i>
                </div>
                TechLearn
            </a>
            
            <div class="nav-actions">
                <a href="notices.php" class="btn btn-ghost">
                    <i data-lucide="arrow-left"></i>
                    Back to Notices
                </a>
            </div>
        </div>
    </nav>

    <div class="page-container">
        <div class="container">
            <div class="notice-detail">
                <!-- Notice Content -->
                <div class="notice-card <?php echo $notice['priority']; ?>">
                    <div class="notice-header">
                        <h1 class="notice-title"><?php echo e($notice['title']); ?></h1>
                        <span class="priority-badge priority-<?php echo $notice['priority']; ?>">
                            <?php echo ucfirst($notice['priority']); ?>
                        </span>
                    </div>
                    
                    <div class="notice-content">
                        <?php echo nl2br(e($notice['content'])); ?>
                    </div>
                    
                    <div class="notice-meta">
                        <span>
                            <i data-lucide="user" style="width: 16px; height: 16px;"></i>
                            <?php echo e($notice['author_name']); ?>
                        </span>
                        <span>
                            <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($notice['created_at'])); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Replies -->
                <div class="replies-section">
                    <h3>Replies (<?php echo count($replies); ?>)</h3>
                    
                    <?php foreach ($replies as $reply): ?>
                    <div class="reply-card">
                        <img src="assets/uploads/avatars/<?php echo e($reply['profile_picture']); ?>" alt="<?php echo e($reply['author_name']); ?>" class="reply-avatar" onerror="this.src='assets/images/default-avatar.png'">
                        <div class="reply-content">
                            <div class="reply-header">
                                <span class="reply-author"><?php echo e($reply['author_name']); ?></span>
                                <span class="reply-time"><?php echo date('M j, Y \a\t g:i A', strtotime($reply['created_at'])); ?></span>
                            </div>
                            <p class="reply-text"><?php echo nl2br(e($reply['content'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Reply Form -->
                <?php if (isLoggedIn()): ?>
                <div class="reply-form">
                    <h3>Add Your Reply</h3>
                    <form method="POST">
                        <div class="form-group">
                            <textarea name="content" class="form-textarea" rows="4" placeholder="Write your response..." required></textarea>
                        </div>
                        <button type="submit" name="reply" class="btn btn-primary">
                            <i data-lucide="send"></i>
                            Post Reply
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="reply-form" style="text-align: center;">
                    <p style="color: var(--text-muted); margin-bottom: 1rem;">Sign in to reply to this notice</p>
                    <a href="login.php" class="btn btn-primary">Sign In</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>