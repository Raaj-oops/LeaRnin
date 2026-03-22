<?php
require_once '../config/database.php';

$courseId = $_GET['id'] ?? 0;

// Get course details
$stmt = $pdo->prepare("SELECT c.*, u.name as instructor_name, u.bio as instructor_bio
                       FROM courses c 
                       LEFT JOIN users u ON c.created_by = u.id 
                       WHERE c.id = ? AND c.is_published = 1");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    redirect('courses.php');
}

// Get course modules with lessons
$stmt = $pdo->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY sort_order");
$stmt->execute([$courseId]);
$modules = $stmt->fetchAll();

foreach ($modules as &$module) {
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order");
    $stmt->execute([$module['id']]);
    $module['lessons'] = $stmt->fetchAll();
}

// Check if user is enrolled
$isEnrolled = false;
$progress = 0;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$_SESSION['user_id'], $courseId]);
    $enrollment = $stmt->fetch();
    $isEnrolled = !!$enrollment;
    $progress = $enrollment['progress_percent'] ?? 0;
}

// Get total lessons count
$totalLessons = 0;
foreach ($modules as $module) {
    $totalLessons += count($module['lessons']);
}

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll']) && isLoggedIn()) {
    $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $courseId]);
    redirect("course_details.php?id=$courseId");
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($course['title']); ?> - TechLearn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .course-hero {
            background: var(--gradient-dark);
            color: white;
            padding: 6rem 0 3rem;
            margin-top: -72px;
            padding-top: calc(6rem + 72px);
        }
        
        .course-hero-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            align-items: start;
        }
        
        .course-hero h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .course-hero p {
            color: rgba(255,255,255,0.8);
            font-size: 1.125rem;
            margin-bottom: 1.5rem;
        }
        
        .course-meta-tags {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        
        .meta-tag {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: var(--radius-full);
            font-size: 0.875rem;
        }
        
        .course-card-sidebar {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: var(--radius-xl);
            padding: 2rem;
        }
        
        .course-progress-ring {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            position: relative;
        }
        
        .progress-ring-circle {
            transform: rotate(-90deg);
        }
        
        .progress-ring-bg {
            fill: none;
            stroke: rgba(255,255,255,0.2);
            stroke-width: 8;
        }
        
        .progress-ring-fill {
            fill: none;
            stroke: url(#progressGradient);
            stroke-width: 8;
            stroke-linecap: round;
            transition: stroke-dashoffset var(--transition-slow);
        }
        
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .course-content-area {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            padding: 3rem 0;
        }
        
        .module-list {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            overflow: hidden;
        }
        
        .module-item {
            border-bottom: 1px solid var(--border-color);
        }
        
        .module-item:last-child {
            border-bottom: none;
        }
        
        .module-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            background: var(--bg-secondary);
            cursor: pointer;
        }
        
        .module-title {
            font-weight: 600;
        }
        
        .module-lessons {
            list-style: none;
        }
        
        .lesson-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            transition: background var(--transition-fast);
        }
        
        .lesson-item:last-child {
            border-bottom: none;
        }
        
        .lesson-item:hover {
            background: var(--bg-secondary);
        }
        
        .lesson-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .lesson-icon.lecture { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
        .lesson-icon.tutorial { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .lesson-icon.workshop { background: rgba(245, 158, 11, 0.1); color: var(--accent); }
        
        .lesson-info {
            flex: 1;
        }
        
        .lesson-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .lesson-type {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: capitalize;
        }
        
        .lesson-status {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .lesson-status.locked {
            background: var(--bg-tertiary);
            color: var(--text-muted);
        }
        
        .lesson-status.completed {
            background: var(--success);
            color: white;
        }
        
        .lesson-status.pending {
            background: var(--primary);
            color: white;
        }
        
        .sidebar-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .sidebar-card h3 {
            font-size: 1.125rem;
            margin-bottom: 1rem;
        }
        
        .instructor-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .instructor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .instructor-details h4 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        
        .instructor-details p {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .course-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .course-stat {
            text-align: center;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
        }
        
        .course-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .course-stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        @media (max-width: 1024px) {
            .course-hero-content,
            .course-content-area {
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
            
            <ul class="nav-links" id="nav-links">
                <li><a href="courses.php">Courses</a></li>
                <li><a href="../projects/portfolio.php">Portfolio</a></li>
                <li><a href="../community/forum.php">Community</a></li>
                <li><a href="../jobs/jobs.php">Jobs</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                    <i data-lucide="moon"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                    <a href="../dashboard.php" class="btn btn-primary">
                        <i data-lucide="layout-dashboard"></i>
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="../login.php" class="btn btn-ghost">Sign In</a>
                    <a href="../register.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </nav>

    <!-- Course Hero -->
    <section class="course-hero">
        <div class="container">
            <div class="course-hero-content">
                <div>
                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: var(--primary); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; margin-bottom: 1rem;">
                        <?php echo e($course['category']); ?>
                    </span>
                    <h1><?php echo e($course['title']); ?></h1>
                    <p><?php echo e($course['description']); ?></p>
                    
                    <div class="course-meta-tags">
                        <span class="meta-tag">
                            <i data-lucide="bar-chart"></i>
                            <?php echo ucfirst(e($course['difficulty_level'])); ?>
                        </span>
                        <span class="meta-tag">
                            <i data-lucide="clock"></i>
                            <?php echo e($course['estimated_hours']); ?> hours
                        </span>
                        <span class="meta-tag">
                            <i data-lucide="book-open"></i>
                            <?php echo $totalLessons; ?> lessons
                        </span>
                    </div>
                    
                    <?php if (!$isEnrolled): ?>
                        <?php if (isLoggedIn()): ?>
                        <form method="POST">
                            <button type="submit" name="enroll" class="btn btn-primary btn-lg">
                                <i data-lucide="play-circle"></i>
                                Enroll Now - Free
                            </button>
                        </form>
                        <?php else: ?>
                        <a href="../login.php" class="btn btn-primary btn-lg">
                            <i data-lucide="log-in"></i>
                            Sign In to Enroll
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <?php if ($isEnrolled): ?>
                <div class="course-card-sidebar">
                    <svg width="0" height="0" style="position: absolute;">
                        <defs>
                            <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#6366f1"/>
                                <stop offset="100%" stop-color="#a855f7"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="course-progress-ring">
                        <svg class="progress-ring-circle" width="120" height="120">
                            <circle class="progress-ring-bg" cx="60" cy="60" r="52"/>
                            <circle class="progress-ring-fill" cx="60" cy="60" r="52"
                                    stroke-dasharray="326.73"
                                    stroke-dashoffset="<?php echo 326.73 - (326.73 * $progress / 100); ?>"/>
                        </svg>
                        <div class="progress-text"><?php echo $progress; ?>%</div>
                    </div>
                    <p style="text-align: center; margin-bottom: 1.5rem;">Course Progress</p>
                    
                    <?php 
                    // Find first incomplete lesson
                    $firstLessonId = null;
                    foreach ($modules as $module) {
                        foreach ($module['lessons'] as $lesson) {
                            $firstLessonId = $lesson['id'];
                            break 2;
                        }
                    }
                    ?>
                    <a href="lesson.php?id=<?php echo $firstLessonId; ?>" class="btn btn-primary btn-full">
                        <i data-lucide="play"></i>
                        Continue Learning
                    </a>
                </div>
                <?php else: ?>
                <div class="course-card-sidebar">
                    <h3 style="margin-bottom: 1.5rem;">What You'll Learn</h3>
                    <ul style="list-style: none;">
                        <li style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <i data-lucide="check" style="color: var(--success);"></i>
                            <span>Core concepts and fundamentals</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <i data-lucide="check" style="color: var(--success);"></i>
                            <span>Hands-on coding exercises</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <i data-lucide="check" style="color: var(--success);"></i>
                            <span>Real-world projects</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.75rem;">
                            <i data-lucide="check" style="color: var(--success);"></i>
                            <span>Certificate of completion</span>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Course Content -->
    <section class="section" style="background: var(--bg-secondary);">
        <div class="container">
            <div class="course-content-area">
                <div>
                    <h2 style="margin-bottom: 1.5rem;">Course Content</h2>
                    
                    <div class="module-list">
                        <?php foreach ($modules as $index => $module): ?>
                        <div class="module-item">
                            <div class="module-header">
                                <div>
                                    <span style="color: var(--text-muted); font-size: 0.875rem;">Module <?php echo $index + 1; ?></span>
                                    <div class="module-title"><?php echo e($module['title']); ?></div>
                                </div>
                                <span style="color: var(--text-muted); font-size: 0.875rem;">
                                    <?php echo count($module['lessons']); ?> lessons
                                </span>
                            </div>
                            <ul class="module-lessons">
                                <?php foreach ($module['lessons'] as $lesson): ?>
                                <li class="lesson-item">
                                    <div class="lesson-icon lecture">
                                        <i data-lucide="play-circle" style="width: 16px; height: 16px;"></i>
                                    </div>
                                    <div class="lesson-info">
                                        <div class="lesson-title"><?php echo e($lesson['title']); ?></div>
                                        <div class="lesson-type">Lecture • Tutorial • Workshop</div>
                                    </div>
                                    <?php if ($isEnrolled): ?>
                                    <a href="lesson.php?id=<?php echo $lesson['id']; ?>" class="lesson-status pending">
                                        <i data-lucide="play" style="width: 12px; height: 12px;"></i>
                                    </a>
                                    <?php else: ?>
                                    <div class="lesson-status locked">
                                        <i data-lucide="lock" style="width: 12px; height: 12px;"></i>
                                    </div>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <aside>
                    <div class="sidebar-card">
                        <h3>About the Instructor</h3>
                        <div class="instructor-info">
                            <img src="../assets/uploads/avatars/default-avatar.png" alt="<?php echo e($course['instructor_name']); ?>" class="instructor-avatar">
                            <div class="instructor-details">
                                <h4><?php echo e($course['instructor_name'] ?? 'TechLearn Team'); ?></h4>
                                <p><?php echo e($course['instructor_bio'] ?? 'Expert Instructor'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sidebar-card">
                        <h3>Course Stats</h3>
                        <div class="course-stats">
                            <div class="course-stat">
                                <div class="course-stat-value"><?php echo $totalLessons; ?></div>
                                <div class="course-stat-label">Lessons</div>
                            </div>
                            <div class="course-stat">
                                <div class="course-stat-value"><?php echo e($course['estimated_hours']); ?>h</div>
                                <div class="course-stat-label">Duration</div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <script src="../assets/js/script.js"></script>
</body>
</html>