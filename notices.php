<?php
require_once 'config/database.php';

// Get all active notices
$notices = $pdo->query("SELECT n.*, u.name as author_name,
                        (SELECT COUNT(*) FROM notice_replies WHERE notice_id = n.id) as reply_count
                        FROM notices n
                        JOIN users u ON n.created_by = u.id
                        WHERE n.expires_at IS NULL OR n.expires_at > NOW()
                        ORDER BY n.is_pinned DESC, n.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices - TechLearn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .page-header {
            background: var(--gradient-dark);
            color: white;
            padding: 6rem 0 3rem;
            margin-top: -72px;
            padding-top: calc(6rem + 72px);
        }
        
        .page-header h1 {
            color: white;
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .page-header p {
            color: rgba(255,255,255,0.8);
            font-size: 1.25rem;
        }
        
        .notices-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 0;
        }
        
        .notice-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all var(--transition-base);
        }
        
        .notice-card:hover {
            box-shadow: var(--shadow-lg);
        }
        
        .notice-card.pinned {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.03);
        }
        
        .notice-card.urgent {
            border-color: var(--error);
            background: rgba(239, 68, 68, 0.03);
        }
        
        .notice-card.high {
            border-color: var(--accent);
            background: rgba(245, 158, 11, 0.03);
        }
        
        .notice-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        
        .notice-title {
            font-size: 1.375rem;
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
        
        .pinned-badge {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.375rem 0.75rem;
            background: var(--primary);
            color: white;
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .notice-content {
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
        }
        
        .notice-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-color);
        }
        
        .notice-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        .notice-meta span {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem;
            color: var(--text-muted);
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
            
            <ul class="nav-links" id="nav-links">
                <li><a href="courses/courses.php">Courses</a></li>
                <li><a href="projects/portfolio.php">Portfolio</a></li>
                <li><a href="community/forum.php">Community</a></li>
                <li><a href="jobs/jobs.php">Jobs</a></li>
                <li><a href="notices.php" class="active">Notices</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                    <i data-lucide="moon"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i data-lucide="layout-dashboard"></i>
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-ghost">Sign In</a>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1>Announcements & Notices</h1>
            <p>Stay updated with the latest news from TechLearn</p>
        </div>
    </header>

    <!-- Notices List -->
    <section class="section" style="padding-top: 2rem;">
        <div class="container">
            <div class="notices-container">
                <?php if (empty($notices)): ?>
                <div class="empty-state">
                    <i data-lucide="bell-off" style="width: 80px; height: 80px; margin-bottom: 1.5rem; opacity: 0.5;"></i>
                    <h3>No notices at the moment</h3>
                    <p>Check back later for updates!</p>
                </div>
                <?php else: ?>
                    <?php foreach ($notices as $notice): ?>
                    <div class="notice-card <?php echo $notice['is_pinned'] ? 'pinned' : ''; ?> <?php echo $notice['priority']; ?>">
                        <div class="notice-header">
                            <h2 class="notice-title"><?php echo e($notice['title']); ?></h2>
                            <span class="priority-badge priority-<?php echo $notice['priority']; ?>">
                                <?php echo ucfirst($notice['priority']); ?>
                            </span>
                            <?php if ($notice['is_pinned']): ?>
                            <span class="pinned-badge">
                                <i data-lucide="pin" style="width: 14px; height: 14px;"></i>
                                Pinned
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="notice-content">
                            <?php echo nl2br(e($notice['content'])); ?>
                        </div>
                        
                        <div class="notice-footer">
                            <div class="notice-meta">
                                <span>
                                    <i data-lucide="user" style="width: 16px; height: 16px;"></i>
                                    <?php echo e($notice['author_name']); ?>
                                </span>
                                <span>
                                    <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                                    <?php echo date('M j, Y', strtotime($notice['created_at'])); ?>
                                </span>
                                <span>
                                    <i data-lucide="message-circle" style="width: 16px; height: 16px;"></i>
                                    <?php echo $notice['reply_count']; ?> replies
                                </span>
                            </div>
                            <a href="notice_detail.php?id=<?php echo $notice['id']; ?>" class="btn btn-primary">
                                View & Reply
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="assets/js/script.js"></script>
</body>
</html>