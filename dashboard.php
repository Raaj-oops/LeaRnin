<?php
require_once 'config/database.php';

// Require login
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();

// CRITICAL FIX: Validate user data is actually an array with required keys
if (!$user || !is_array($user) || !isset($user['id'])) {
    // Invalid user data - clear session and redirect to login
    session_destroy();
    redirect('login.php');
}

// Get user's enrolled courses with progress
$stmt = $pdo->prepare("SELECT c.*, e.progress_percent, e.enrolled_at 
                       FROM enrollments e 
                       JOIN courses c ON e.course_id = c.id 
                       WHERE e.user_id = ? 
                       ORDER BY e.enrolled_at DESC 
                       LIMIT 4");
$stmt->execute([$user['id']]);
$enrolledCourses = $stmt->fetchAll();

// Get recommended courses (not enrolled)
$stmt = $pdo->prepare("SELECT c.*, u.name as instructor_name 
                       FROM courses c 
                       LEFT JOIN users u ON c.created_by = u.id 
                       WHERE c.is_published = 1 
                       AND c.id NOT IN (SELECT course_id FROM enrollments WHERE user_id = ?)
                       ORDER BY c.created_at DESC 
                       LIMIT 3");
$stmt->execute([$user['id']]);
$recommendedCourses = $stmt->fetchAll();

// Get user's projects
$stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$user['id']]);
$userProjects = $stmt->fetchAll();

// Get recent forum posts
$stmt = $pdo->query("SELECT p.*, u.name as author_name, u.profile_picture 
                     FROM posts p 
                     JOIN users u ON p.user_id = u.id 
                     ORDER BY p.created_at DESC 
                     LIMIT 5");
$recentPosts = $stmt->fetchAll();

// Get user stats - FIXED VERSION
$stats = [
    'courses' => 0,
    'projects' => 0,
    'completed_lessons' => 0,
    'certificates' => 0 // Placeholder for future feature
];

// Courses count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?");
if ($stmt->execute([$user['id']])) {
    $stats['courses'] = $stmt->fetchColumn();
}

// Projects count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ?");
if ($stmt->execute([$user['id']])) {
    $stats['projects'] = $stmt->fetchColumn();
}

// Completed lessons count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND is_lecture_completed = 1");
if ($stmt->execute([$user['id']])) {
    $stats['completed_lessons'] = $stmt->fetchColumn();
}

// Get user achievements
$stmt = $pdo->prepare("SELECT a.* FROM achievements a 
                       JOIN user_achievements ua ON a.id = ua.achievement_id 
                       WHERE ua.user_id = ?");
$stmt->execute([$user['id']]);
$achievements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TechLearn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .user-welcome {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
        }
        
        .user-info h1 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        
        .user-info p {
            color: var(--text-secondary);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
        }
        
        .sidebar-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .sidebar-nav {
            list-style: none;
        }
        
        .sidebar-nav li {
            margin-bottom: 0.25rem;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-lg);
            color: var(--text-secondary);
            font-weight: 500;
            transition: all var(--transition-fast);
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: var(--bg-secondary);
            color: var(--primary);
        }
        
        .sidebar-nav a.active {
            background: rgba(99, 102, 241, 0.1);
        }
        
        .main-content {
            min-height: calc(100vh - 200px);
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            text-align: center;
        }
        
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
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
        
        .content-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .section-header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        .section-header-flex h2 {
            font-size: 1.25rem;
        }
        
        .course-progress-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
        }
        
        .course-progress-thumb {
            width: 80px;
            height: 60px;
            border-radius: var(--radius-md);
            object-fit: cover;
        }
        
        .course-progress-info {
            flex: 1;
        }
        
        .course-progress-info h4 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .progress-bar-small {
            height: 6px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-full);
            overflow: hidden;
        }
        
        .progress-bar-small .progress-fill {
            height: 100%;
            background: var(--gradient-primary);
            border-radius: var(--radius-full);
            transition: width var(--transition-slow);
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-content p {
            margin-bottom: 0.25rem;
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }
        
        .empty-state i {
            width: 64px;
            height: 64px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
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
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                    <i data-lucide="moon"></i>
                </button>
                
                <a href="logout.php" class="btn btn-ghost">
                    <i data-lucide="log-out"></i>
                    Sign Out
                </a>
            </div>
            
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </nav>

    <div class="dashboard" style="padding-top: 100px;">
        <div class="container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="user-welcome">
                    <img src="assets/uploads/avatars/<?php echo e($user['profile_picture'] ?? 'default-avatar.png'); ?>" alt="<?php echo e($user['name'] ?? 'User'); ?>" class="user-avatar" onerror="this.src='assets/images/default-avatar.png'">
                    <div class="user-info">
                        <h1>Welcome back, <?php echo e(isset($user['name']) ? explode(' ', $user['name'])[0] : 'User'); ?>!</h1>
                        <p>Continue your learning journey</p>
                    </div>
                </div>
                <a href="projects/upload_project.php" class="btn btn-primary">
                    <i data-lucide="plus"></i>
                    Upload Project
                </a>
            </div>
            
            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-card-icon blue">
                        <i data-lucide="book-open"></i>
                    </div>
                    <div class="stat-card-value"><?php echo $stats['courses']; ?></div>
                    <div class="stat-card-label">Courses Enrolled</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon green">
                        <i data-lucide="folder"></i>
                    </div>
                    <div class="stat-card-value"><?php echo $stats['projects']; ?></div>
                    <div class="stat-card-label">Projects</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon orange">
                        <i data-lucide="check-circle"></i>
                    </div>
                    <div class="stat-card-value"><?php echo $stats['completed_lessons']; ?></div>
                    <div class="stat-card-label">Lessons Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon purple">
                        <i data-lucide="award"></i>
                    </div>
                    <div class="stat-card-value"><?php echo count($achievements); ?></div>
                    <div class="stat-card-label">Achievements</div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <!-- Sidebar -->
                <aside>
                    <div class="sidebar-card">
                        <ul class="sidebar-nav">
                            <li><a href="dashboard.php" class="active">
                                <i data-lucide="layout-dashboard"></i>
                                Dashboard
                            </a></li>
                            <li><a href="courses/courses.php">
                                <i data-lucide="book-open"></i>
                                My Courses
                            </a></li>
                            <li><a href="projects/portfolio.php">
                                <i data-lucide="folder"></i>
                                My Portfolio
                            </a></li>
                            <li><a href="workshops/submissions.php">
                                <i data-lucide="code"></i>
                                My Submissions
                            </a></li>
                            <li><a href="community/forum.php">
                                <i data-lucide="message-square"></i>
                                Community
                            </a></li>
                            <li><a href="jobs/jobs.php">
                                <i data-lucide="briefcase"></i>
                                Jobs
                            </a></li>
                            <li><a href="#">
                                <i data-lucide="settings"></i>
                                Settings
                            </a></li>
                        </ul>
                    </div>
                    
                    <?php if (!empty($achievements)): ?>
                    <div class="sidebar-card">
                        <h3 style="font-size: 1rem; margin-bottom: 1rem;">Recent Achievements</h3>
                        <?php foreach (array_slice($achievements, 0, 3) as $achievement): ?>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div style="width: 36px; height: 36px; background: var(--gradient-primary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white;">
                                <i data-lucide="<?php echo e($achievement['icon'] ?? 'award'); ?>"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;"><?php echo e($achievement['title'] ?? 'Achievement'); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo e($achievement['description'] ?? ''); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </aside>
                
                <!-- Main Content -->
                <div class="main-content">
                    <!-- Enrolled Courses -->
                    <div class="content-section">
                        <div class="section-header-flex">
                            <h2><i data-lucide="book-open"></i> Continue Learning</h2>
                            <a href="courses/courses.php" class="btn btn-sm btn-secondary">View All</a>
                        </div>
                        
                        <?php if (empty($enrolledCourses)): ?>
                        <div class="empty-state">
                            <i data-lucide="book-open"></i>
                            <p>You haven't enrolled in any courses yet.</p>
                            <a href="courses/courses.php" class="btn btn-primary" style="margin-top: 1rem;">Browse Courses</a>
                        </div>
                        <?php else: ?>
                            <?php foreach ($enrolledCourses as $course): ?>
                            <div class="course-progress-item">
                                <img src="assets/images/<?php echo e($course['thumbnail'] ?? 'default-course.jpg'); ?>" alt="<?php echo e($course['title'] ?? 'Course'); ?>" class="course-progress-thumb" onerror="this.src='assets/images/default-course.jpg'">
                                <div class="course-progress-info">
                                    <h4><?php echo e($course['title'] ?? 'Untitled Course'); ?></h4>
                                    <div class="progress-bar-small">
                                        <div class="progress-fill" style="width: <?php echo $course['progress_percent'] ?? 0; ?>%"></div>
                                    </div>
                                </div>
                                <span style="font-weight: 600; color: var(--primary);"><?php echo $course['progress_percent'] ?? 0; ?>%</span>
                                <a href="courses/course_details.php?id=<?php echo $course['id'] ?? 0; ?>" class="btn btn-primary btn-sm">Continue</a>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Two Column Layout -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <!-- Recommended Courses -->
                        <div class="content-section">
                            <div class="section-header-flex">
                                <h2><i data-lucide="sparkles"></i> Recommended</h2>
                            </div>
                            
                            <?php if (empty($recommendedCourses)): ?>
                            <p style="color: var(--text-muted);">No recommendations at the moment.</p>
                            <?php else: ?>
                                <?php foreach ($recommendedCourses as $course): ?>
                                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: var(--radius-lg); margin-bottom: 0.75rem;">
                                    <img src="assets/images/<?php echo e($course['thumbnail'] ?? 'default-course.jpg'); ?>" alt="<?php echo e($course['title'] ?? 'Course'); ?>" style="width: 60px; height: 45px; border-radius: var(--radius-md); object-fit: cover;" onerror="this.src='assets/images/default-course.jpg'">
                                    <div style="flex: 1;">
                                        <h4 style="font-size: 0.9rem; margin-bottom: 0.25rem;"><?php echo e($course['title'] ?? 'Untitled'); ?></h4>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo e($course['category'] ?? 'General'); ?></span>
                                    </div>
                                    <a href="courses/course_details.php?id=<?php echo $course['id'] ?? 0; ?>" class="btn btn-sm btn-primary">Enroll</a>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Recent Activity -->
                        <div class="content-section">
                            <div class="section-header-flex">
                                <h2><i data-lucide="activity"></i> Community Activity</h2>
                            </div>
                            
                            <?php foreach ($recentPosts as $post): ?>
                            <div class="activity-item">
                                <img src="assets/uploads/avatars/<?php echo e($post['profile_picture'] ?? 'default-avatar.png'); ?>" alt="<?php echo e($post['author_name'] ?? 'User'); ?>" class="activity-avatar" onerror="this.src='assets/images/default-avatar.png'">
                                <div class="activity-content">
                                    <p><strong><?php echo e($post['author_name'] ?? 'Unknown'); ?></strong> posted <strong><?php echo e($post['title'] ?? 'Untitled'); ?></strong></p>
                                    <span class="activity-time"><?php echo isset($post['created_at']) ? date('M j, g:i A', strtotime($post['created_at'])) : 'Recently'; ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- My Projects -->
                    <div class="content-section">
                        <div class="section-header-flex">
                            <h2><i data-lucide="folder"></i> My Projects</h2>
                            <a href="projects/portfolio.php" class="btn btn-sm btn-secondary">View All</a>
                        </div>
                        
                        <?php if (empty($userProjects)): ?>
                        <div class="empty-state">
                            <i data-lucide="folder"></i>
                            <p>You haven't uploaded any projects yet.</p>
                            <a href="projects/upload_project.php" class="btn btn-primary" style="margin-top: 1rem;">Upload Project</a>
                        </div>
                        <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                            <?php foreach ($userProjects as $project): ?>
                            <div style="border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden;">
                                <img src="assets/uploads/projects/<?php echo e($project['screenshot'] ?? 'default-project.jpg'); ?>" alt="<?php echo e($project['title'] ?? 'Project'); ?>" style="width: 100%; height: 120px; object-fit: cover;" onerror="this.src='assets/images/default-project.jpg'">
                                <div style="padding: 1rem;">
                                    <h4 style="font-size: 0.95rem; margin-bottom: 0.5rem;"><?php echo e($project['title'] ?? 'Untitled Project'); ?></h4>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <?php if (!empty($project['github_link'])): ?>
                                        <a href="<?php echo e($project['github_link']); ?>" target="_blank" class="btn btn-sm btn-secondary">Code</a>
                                        <?php endif; ?>
                                        <?php if (!empty($project['live_demo'])): ?>
                                        <a href="<?php echo e($project['live_demo']); ?>" target="_blank" class="btn btn-sm btn-primary">Demo</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <?php include 'chatbot.php'; ?>
</body>
</html>