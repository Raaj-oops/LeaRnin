<?php
require_once '../config/database.php';

// Require admin
if (!isAdmin()) {
    redirect('../dashboard.php');
}

// Get all users
$users = $pdo->query("SELECT u.*, 
                      (SELECT COUNT(*) FROM enrollments WHERE user_id = u.id) as course_count,
                      (SELECT COUNT(*) FROM projects WHERE user_id = u.id) as project_count
                      FROM users u 
                      ORDER BY u.join_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - TechLearn Admin</title>
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
        
        .admin-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
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
        
        .skill-badge {
            padding: 0.25rem 0.625rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .skill-beginner { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .skill-intermediate { background: rgba(245, 158, 11, 0.1); color: var(--accent); }
        .skill-advanced { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
        
        @media (max-width: 1024px) {
            .admin-sidebar {
                display: none;
            }
            
            .admin-main {
                margin-left: 0;
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
                <li><a href="manage_users.php" class="active">
                    <i data-lucide="users"></i>
                    Users
                </a></li>
                <li><a href="review_submissions.php">
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
                <h1>Manage Users</h1>
            </div>
            
            <div class="admin-section">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Skill Level</th>
                            <th>Courses</th>
                            <th>Projects</th>
                            <th>Joined</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?php echo e($user['name']); ?></strong>
                                <br><small style="color: var(--text-muted);"><?php echo e($user['email']); ?></small>
                            </td>
                            <td>
                                <span class="skill-badge skill-<?php echo e($user['skill_level']); ?>">
                                    <?php echo ucfirst(e($user['skill_level'])); ?>
                                </span>
                            </td>
                            <td><?php echo $user['course_count']; ?></td>
                            <td><?php echo $user['project_count']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($user['join_date'])); ?></td>
                            <td>
                                <?php if ($user['is_admin']): ?>
                                <span style="padding: 0.25rem 0.625rem; background: var(--primary); color: white; border-radius: var(--radius-full); font-size: 0.75rem;">Admin</span>
                                <?php else: ?>
                                <span style="color: var(--text-muted);">User</span>
                                <?php endif; ?>
                            </td>
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