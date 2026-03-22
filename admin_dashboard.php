<?php
require_once '../config/database.php';

// Require admin
if (!isAdmin()) {
    redirect('../dashboard.php');
}

// Get stats
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'courses' => $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
    'enrollments' => $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn(),
    'projects' => $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
    'posts' => $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
    'jobs' => $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn(),
    'applications' => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn(),
    'submissions' => $pdo->query("SELECT COUNT(*) FROM workshop_submissions WHERE status = 'pending'")->fetchColumn(),
];

// Get recent users
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY join_date DESC LIMIT 5")->fetchAll();

// Get pending submissions
$pendingSubmissions = $pdo->query("SELECT ws.*, u.name as student_name, l.title as lesson_title, c.title as course_title
                                   FROM workshop_submissions ws
                                   JOIN users u ON ws.user_id = u.id
                                   JOIN lessons l ON ws.lesson_id = l.id
                                   JOIN modules m ON l.module_id = m.id
                                   JOIN courses c ON m.course_id = c.id
                                   WHERE ws.status = 'pending'
                                   ORDER BY ws.submitted_at DESC
                                   LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TechLearn</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
        }
        
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .stat-card-icon.blue { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
        .stat-card-icon.green { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-card-icon.orange { background: rgba(245, 158, 11, 0.1); color: var(--accent); }
        .stat-card-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
        
        .stat-card-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
        }
        
        .stat-card-label {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .admin-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .admin-section h2 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .data-table th {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.875rem;
            text-transform: uppercase;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-pending { background: rgba(245, 158, 11, 0.1); color: var(--accent); }
        .badge-approved { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge-rejected { background: rgba(239, 68, 68, 0.1); color: var(--error); }
        
        @media (max-width: 1024px) {
            .admin-sidebar {
                display: none;
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
                <a href="../logout.php" class="btn btn-primary">
                    <i data-lucide="log-out"></i>
                    Sign Out
                </a>
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
                <li><a href="admin_dashboard.php" class="active">
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
                <li><a href="review_submissions.php">
                    <i data-lucide="code"></i>
                    Submissions
                    <?php if ($stats['submissions'] > 0): ?>
                    <span style="margin-left: auto; padding: 0.125rem 0.5rem; background: var(--error); border-radius: var(--radius-full); font-size: 0.75rem;">
                        <?php echo $stats['submissions']; ?>
                    </span>
                    <?php endif; ?>
                </a></li>
                <li><a href="manage_jobs.php">
                    <i data-lucide="briefcase"></i>
                    Jobs
                </a></li>
                <li><a href="notices.php">
                    <i data-lucide="megaphone"></i>
                    Notices
                </a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Dashboard Overview</h1>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon blue">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($stats['users']); ?></div>
                    <div class="stat-card-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon green">
                        <i data-lucide="book-open"></i>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($stats['courses']); ?></div>
                    <div class="stat-card-label">Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon orange">
                        <i data-lucide="graduation-cap"></i>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($stats['enrollments']); ?></div>
                    <div class="stat-card-label">Enrollments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon purple">
                        <i data-lucide="folder"></i>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($stats['projects']); ?></div>
                    <div class="stat-card-label">Projects</div>
                </div>
            </div>
            
            <!-- Pending Submissions -->
            <div class="admin-section">
                <h2>Pending Workshop Submissions</h2>
                
                <?php if (empty($pendingSubmissions)): ?>
                <p style="color: var(--text-muted);">No pending submissions.</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Lesson</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingSubmissions as $sub): ?>
                        <tr>
                            <td><?php echo e($sub['student_name']); ?></td>
                            <td><?php echo e($sub['course_title']); ?></td>
                            <td><?php echo e($sub['lesson_title']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($sub['submitted_at'])); ?></td>
                            <td>
                                <a href="review_submission.php?id=<?php echo $sub['id']; ?>" class="btn btn-primary btn-sm">Review</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <!-- Recent Users -->
            <div class="admin-section">
                <h2>Recent Users</h2>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Skill Level</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo e($user['name']); ?></td>
                            <td><?php echo e($user['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo e($user['skill_level']); ?>">
                                    <?php echo ucfirst(e($user['skill_level'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['join_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>