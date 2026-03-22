<?php
require_once '../config/database.php';

// Require login
if (!isLoggedIn()) {
    redirect('../login.php');
}

$lessonId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// Get lesson details with course and module info
$stmt = $pdo->prepare("SELECT l.*, m.title as module_title, m.course_id, c.title as course_title
                       FROM lessons l 
                       JOIN modules m ON l.module_id = m.id 
                       JOIN courses c ON m.course_id = c.id
                       WHERE l.id = ?");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    redirect('courses.php');
}

// Check enrollment
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$userId, $lesson['course_id']]);
if (!$stmt->fetch()) {
    redirect('course_details.php?id=' . $lesson['course_id']);
}

// Get lesson progress
$stmt = $pdo->prepare("SELECT * FROM lesson_progress WHERE user_id = ? AND lesson_id = ?");
$stmt->execute([$userId, $lessonId]);
$progress = $stmt->fetch();

if (!$progress) {
    // Create progress record
    $stmt = $pdo->prepare("INSERT INTO lesson_progress (user_id, lesson_id) VALUES (?, ?)");
    $stmt->execute([$userId, $lessonId]);
    $progress = ['is_lecture_completed' => 0, 'is_tutorial_completed' => 0, 'is_workshop_completed' => 0];
}

// Get resources
$stmt = $pdo->prepare("SELECT * FROM resources WHERE lesson_id = ? ORDER BY resource_type");
$stmt->execute([$lessonId]);
$resources = $stmt->fetchAll();

// Get extra resources for the course
$stmt = $pdo->prepare("SELECT * FROM extra_resources WHERE course_id = ? ORDER BY resource_type, created_at DESC LIMIT 10");
$stmt->execute([$lesson['course_id']]);
$extraResources = $stmt->fetchAll();

// Get workshop submission if exists
$stmt = $pdo->prepare("SELECT * FROM workshop_submissions WHERE user_id = ? AND lesson_id = ?");
$stmt->execute([$userId, $lessonId]);
$submission = $stmt->fetch();

// Handle progress updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_lecture_complete'])) {
        $stmt = $pdo->prepare("UPDATE lesson_progress SET is_lecture_completed = 1 WHERE user_id = ? AND lesson_id = ?");
        $stmt->execute([$userId, $lessonId]);
        $progress['is_lecture_completed'] = 1;
    }
    
    if (isset($_POST['mark_tutorial_complete'])) {
        $stmt = $pdo->prepare("UPDATE lesson_progress SET is_tutorial_completed = 1 WHERE user_id = ? AND lesson_id = ?");
        $stmt->execute([$userId, $lessonId]);
        $progress['is_tutorial_completed'] = 1;
    }
}

// Get all lessons in this module for navigation
$stmt = $pdo->prepare("SELECT id, title FROM lessons WHERE module_id = ? ORDER BY sort_order");
$stmt->execute([$lesson['module_id']]);
$moduleLessons = $stmt->fetchAll();

// Find current lesson index
$currentIndex = 0;
foreach ($moduleLessons as $index => $l) {
    if ($l['id'] == $lessonId) {
        $currentIndex = $index;
        break;
    }
}

$prevLesson = $currentIndex > 0 ? $moduleLessons[$currentIndex - 1] : null;
$nextLesson = $currentIndex < count($moduleLessons) - 1 ? $moduleLessons[$currentIndex + 1] : null;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($lesson['title']); ?> - TechLearn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .lesson-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: calc(100vh - 72px);
            padding-top: 72px;
        }
        
        .lesson-sidebar {
            background: var(--bg-primary);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            max-height: calc(100vh - 72px);
            position: sticky;
            top: 72px;
        }
        
        .lesson-sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .lesson-sidebar-header h3 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .lesson-sidebar-header p {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .lesson-nav-list {
            list-style: none;
        }
        
        .lesson-nav-item a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 0.9rem;
            transition: all var(--transition-fast);
        }
        
        .lesson-nav-item a:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        
        .lesson-nav-item.active a {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            border-left: 3px solid var(--primary);
        }
        
        .lesson-nav-item.completed a::before {
            content: '';
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            flex-shrink: 0;
        }
        
        .lesson-main {
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .lesson-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        
        .lesson-breadcrumb a {
            color: var(--text-muted);
        }
        
        .lesson-breadcrumb a:hover {
            color: var(--primary);
        }
        
        .lesson-title {
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .lesson-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
        }
        
        .lesson-tab {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            color: var(--text-muted);
            border: none;
            background: none;
            cursor: pointer;
            position: relative;
            transition: color var(--transition-fast);
            border-radius: var(--radius-md);
        }
        
        .lesson-tab:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        
        .lesson-tab.active {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }
        
        .lesson-tab.completed {
            color: var(--success);
        }
        
        .lesson-section {
            display: none;
        }
        
        .lesson-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: var(--radius-xl);
            margin-bottom: 2rem;
            background: var(--bg-secondary);
        }
        
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .content-block {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .content-block h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .code-block {
            background: #1e293b;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            overflow-x: auto;
            margin: 1rem 0;
            position: relative;
        }
        
        .code-block pre {
            margin: 0;
            color: #e2e8f0;
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .code-copy-btn {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            padding: 0.5rem;
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: var(--radius-md);
            color: #94a3b8;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .code-copy-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .workshop-box {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(249, 115, 22, 0.1) 100%);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .workshop-box h3 {
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .hints-box {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .hints-box h4 {
            font-size: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .hints-box ul {
            margin-left: 1.5rem;
            color: var(--text-secondary);
        }
        
        .hints-box li {
            margin-bottom: 0.5rem;
        }
        
        .resources-list {
            display: grid;
            gap: 1rem;
        }
        
        .resource-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            transition: all var(--transition-fast);
        }
        
        .resource-item:hover {
            background: var(--bg-tertiary);
            transform: translateX(4px);
        }
        
        .resource-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .resource-icon.video { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .resource-icon.article { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .resource-icon.documentation { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .resource-icon.github { background: rgba(15, 23, 42, 0.1); color: #0f172a; }
        
        [data-theme="dark"] .resource-icon.github {
            background: rgba(255, 255, 255, 0.1);
            color: #f8fafc;
        }
        
        .resource-info {
            flex: 1;
        }
        
        .resource-info h4 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        
        .resource-info p {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .lesson-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        
        .nav-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            color: var(--text-primary);
            font-weight: 500;
            transition: all var(--transition-fast);
        }
        
        .nav-btn:hover {
            background: var(--bg-secondary);
            border-color: var(--primary);
        }
        
        .nav-btn.next {
            background: var(--gradient-primary);
            color: white;
            border-color: transparent;
        }
        
        .nav-btn.next:hover {
            box-shadow: var(--shadow-lg);
        }
        
        .nav-btn.disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        
        .complete-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--success);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .complete-btn:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        
        .complete-btn:disabled {
            background: var(--bg-tertiary);
            color: var(--text-muted);
            cursor: not-allowed;
            transform: none;
        }
        
        @media (max-width: 1024px) {
            .lesson-layout {
                grid-template-columns: 1fr;
            }
            
            .lesson-sidebar {
                display: none;
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
                <a href="course_details.php?id=<?php echo $lesson['course_id']; ?>" class="btn btn-ghost">
                    <i data-lucide="arrow-left"></i>
                    Back to Course
                </a>
                <a href="../dashboard.php" class="btn btn-primary">
                    <i data-lucide="layout-dashboard"></i>
                    Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="lesson-layout">
        <!-- Sidebar -->
        <aside class="lesson-sidebar">
            <div class="lesson-sidebar-header">
                <h3><?php echo e($lesson['course_title']); ?></h3>
                <p><?php echo e($lesson['module_title']); ?></p>
            </div>
            <ul class="lesson-nav-list">
                <?php foreach ($moduleLessons as $index => $l): ?>
                <li class="lesson-nav-item <?php echo $l['id'] == $lessonId ? 'active' : ''; ?>">
                    <a href="lesson.php?id=<?php echo $l['id']; ?>">
                        <span><?php echo $index + 1; ?>.</span>
                        <?php echo e($l['title']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="lesson-main">
            <!-- Breadcrumb -->
            <nav class="lesson-breadcrumb">
                <a href="../dashboard.php">Dashboard</a>
                <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                <a href="course_details.php?id=<?php echo $lesson['course_id']; ?>"><?php echo e($lesson['course_title']); ?></a>
                <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                <span><?php echo e($lesson['title']); ?></span>
            </nav>
            
            <h1 class="lesson-title"><?php echo e($lesson['title']); ?></h1>
            
            <!-- Tabs -->
            <div class="lesson-tabs">
                <button class="lesson-tab active <?php echo $progress['is_lecture_completed'] ? 'completed' : ''; ?>" data-tab="lecture">
                    <i data-lucide="play-circle"></i>
                    Lecture
                    <?php if ($progress['is_lecture_completed']): ?>
                    <i data-lucide="check-circle" style="color: var(--success);"></i>
                    <?php endif; ?>
                </button>
                <button class="lesson-tab <?php echo $progress['is_tutorial_completed'] ? 'completed' : ''; ?>" data-tab="tutorial">
                    <i data-lucide="code"></i>
                    Tutorial
                    <?php if ($progress['is_tutorial_completed']): ?>
                    <i data-lucide="check-circle" style="color: var(--success);"></i>
                    <?php endif; ?>
                </button>
                <button class="lesson-tab <?php echo $progress['is_workshop_completed'] ? 'completed' : ''; ?>" data-tab="workshop">
                    <i data-lucide="wrench"></i>
                    Workshop
                    <?php if ($progress['is_workshop_completed']): ?>
                    <i data-lucide="check-circle" style="color: var(--success);"></i>
                    <?php endif; ?>
                </button>
                <button class="lesson-tab" data-tab="resources">
                    <i data-lucide="external-link"></i>
                    Resources
                </button>
            </div>
            
            <!-- Lecture Section -->
            <div id="lecture" class="lesson-section active">
                <?php if ($lesson['lecture_video_url']): ?>
                <div class="video-container">
                    <iframe src="<?php echo e($lesson['lecture_video_url']); ?>" 
                            title="Lecture Video" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen></iframe>
                </div>
                <?php endif; ?>
                
                <div class="content-block">
                    <h3><i data-lucide="book-open"></i> Lecture Notes</h3>
                    <div class="lecture-content">
                        <?php echo $lesson['lecture_content']; ?>
                    </div>
                </div>
                
                <?php if (!$progress['is_lecture_completed']): ?>
                <form method="POST" style="text-align: center;">
                    <button type="submit" name="mark_lecture_complete" class="complete-btn">
                        <i data-lucide="check"></i>
                        Mark Lecture as Complete
                    </button>
                </form>
                <?php else: ?>
                <div style="text-align: center; padding: 1rem; background: rgba(16, 185, 129, 0.1); border-radius: var(--radius-lg); color: var(--success);">
                    <i data-lucide="check-circle"></i>
                    Lecture Completed!
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Tutorial Section -->
            <div id="tutorial" class="lesson-section">
                <?php if ($lesson['tutorial_video_url']): ?>
                <div class="video-container">
                    <iframe src="<?php echo e($lesson['tutorial_video_url']); ?>" 
                            title="Tutorial Video" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen></iframe>
                </div>
                <?php endif; ?>
                
                <div class="content-block">
                    <h3><i data-lucide="code"></i> Step-by-Step Tutorial</h3>
                    <div class="tutorial-content">
                        <?php echo $lesson['tutorial_content']; ?>
                    </div>
                </div>
                
                <?php if ($lesson['tutorial_code']): ?>
                <div class="content-block">
                    <h3><i data-lucide="terminal"></i> Code Example</h3>
                    <div class="code-block">
                        <button class="code-copy-btn" onclick="copyCode(this)">
                            <i data-lucide="copy" style="width: 16px; height: 16px;"></i>
                        </button>
                        <pre><code><?php echo e($lesson['tutorial_code']); ?></code></pre>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!$progress['is_tutorial_completed']): ?>
                <form method="POST" style="text-align: center;">
                    <button type="submit" name="mark_tutorial_complete" class="complete-btn">
                        <i data-lucide="check"></i>
                        Mark Tutorial as Complete
                    </button>
                </form>
                <?php else: ?>
                <div style="text-align: center; padding: 1rem; background: rgba(16, 185, 129, 0.1); border-radius: var(--radius-lg); color: var(--success);">
                    <i data-lucide="check-circle"></i>
                    Tutorial Completed!
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Workshop Section -->
            <div id="workshop" class="lesson-section">
                <div class="workshop-box">
                    <h3><i data-lucide="wrench"></i> Workshop Challenge</h3>
                    <p><?php echo nl2br(e($lesson['workshop_problem'])); ?></p>
                </div>
                
                <?php if ($lesson['workshop_hints']): ?>
                <div class="hints-box">
                    <h4><i data-lucide="lightbulb"></i> Hints</h4>
                    <ul>
                        <?php foreach (explode("\n", $lesson['workshop_hints']) as $hint): ?>
                        <?php if (trim($hint)): ?>
                        <li><?php echo e(trim($hint)); ?></li>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="content-block">
                    <h3><i data-lucide="upload"></i> Submit Your Solution</h3>
                    
                    <?php if ($submission): ?>
                    <div style="padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius-lg); margin-bottom: 1rem;">
                        <p><strong>Status:</strong> 
                            <span style="padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.875rem; font-weight: 600;
                                <?php echo $submission['status'] === 'approved' ? 'background: rgba(16, 185, 129, 0.1); color: var(--success);' : 
                                    ($submission['status'] === 'rejected' ? 'background: rgba(239, 68, 68, 0.1); color: var(--error);' : 
                                    'background: rgba(245, 158, 11, 0.1); color: var(--accent);'); ?>">
                                <?php echo ucfirst(e($submission['status'])); ?>
                            </span>
                        </p>
                        <?php if ($submission['grade']): ?>
                        <p style="margin-top: 0.5rem;"><strong>Grade:</strong> <?php echo $submission['grade']; ?>/100</p>
                        <?php endif; ?>
                        <?php if ($submission['feedback']): ?>
                        <p style="margin-top: 0.5rem;"><strong>Feedback:</strong> <?php echo e($submission['feedback']); ?></p>
                        <?php endif; ?>
                        <p style="margin-top: 0.5rem;"><strong>Submitted:</strong> <?php echo date('M j, Y', strtotime($submission['submitted_at'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <a href="../workshops/submit_workshop.php?lesson_id=<?php echo $lessonId; ?>" class="btn btn-primary btn-full">
                        <i data-lucide="upload"></i>
                        <?php echo $submission ? 'Update Submission' : 'Submit Solution'; ?>
                    </a>
                </div>
            </div>
            
            <!-- Resources Section -->
            <div id="resources" class="lesson-section">
                <!-- Lesson-specific resources -->
                <div class="content-block">
                    <h3><i data-lucide="book-open"></i> Lesson Resources</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                        Resources specifically curated for this lesson.
                    </p>
                    
                    <?php if (empty($resources)): ?>
                    <p style="color: var(--text-muted);">No additional resources available for this lesson.</p>
                    <?php else: ?>
                    <div class="resources-list">
                        <?php foreach ($resources as $resource): ?>
                        <a href="<?php echo e($resource['url']); ?>" target="_blank" class="resource-item">
                            <div class="resource-icon <?php echo e($resource['resource_type']); ?>">
                                <?php if ($resource['resource_type'] === 'video'): ?>
                                <i data-lucide="play"></i>
                                <?php elseif ($resource['resource_type'] === 'article'): ?>
                                <i data-lucide="file-text"></i>
                                <?php elseif ($resource['resource_type'] === 'documentation'): ?>
                                <i data-lucide="book"></i>
                                <?php else: ?>
                                <i data-lucide="github"></i>
                                <?php endif; ?>
                            </div>
                            <div class="resource-info">
                                <h4><?php echo e($resource['title']); ?></h4>
                                <p><?php echo e($resource['description']); ?> • <?php echo ucfirst(e($resource['resource_type'])); ?></p>
                            </div>
                            <i data-lucide="external-link" style="color: var(--text-muted);"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Course-wide extra resources -->
                <div class="content-block">
                    <h3><i data-lucide="external-link"></i> Extra Knowledge & Deep Dive Resources</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                        Explore these curated resources to deepen your understanding and stay updated with the latest in <?php echo e($lesson['course_title']); ?>.
                    </p>
                    
                    <?php if (empty($extraResources)): ?>
                    <p style="color: var(--text-muted);">No extra resources available yet.</p>
                    <?php else: ?>
                    <div class="resources-list">
                        <?php foreach ($extraResources as $resource): ?>
                        <a href="<?php echo e($resource['url']); ?>" target="_blank" class="resource-item">
                            <div class="resource-icon <?php echo e($resource['resource_type']); ?>">
                                <?php if ($resource['resource_type'] === 'video'): ?>
                                <i data-lucide="play"></i>
                                <?php elseif ($resource['resource_type'] === 'article'): ?>
                                <i data-lucide="file-text"></i>
                                <?php elseif ($resource['resource_type'] === 'documentation'): ?>
                                <i data-lucide="book"></i>
                                <?php elseif ($resource['resource_type'] === 'course'): ?>
                                <i data-lucide="graduation-cap"></i>
                                <?php elseif ($resource['resource_type'] === 'book'): ?>
                                <i data-lucide="book-open"></i>
                                <?php elseif ($resource['resource_type'] === 'podcast'): ?>
                                <i data-lucide="headphones"></i>
                                <?php else: ?>
                                <i data-lucide="github"></i>
                                <?php endif; ?>
                            </div>
                            <div class="resource-info">
                                <h4><?php echo e($resource['title']); ?></h4>
                                <p><?php echo e($resource['description']); ?> • <?php echo ucfirst(e($resource['resource_type'])); ?></p>
                                <?php if ($resource['tags']): ?>
                                <div style="margin-top: 0.5rem;">
                                    <?php foreach (explode(',', $resource['tags']) as $tag): ?>
                                    <span style="display: inline-block; padding: 0.125rem 0.5rem; background: var(--bg-tertiary); border-radius: var(--radius-sm); font-size: 0.75rem; color: var(--text-muted); margin-right: 0.25rem;"><?php echo trim(e($tag)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <i data-lucide="external-link" style="color: var(--text-muted);"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Lesson Navigation -->
            <div class="lesson-navigation">
                <?php if ($prevLesson): ?>
                <a href="lesson.php?id=<?php echo $prevLesson['id']; ?>" class="nav-btn">
                    <i data-lucide="arrow-left"></i>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Previous</div>
                        <div><?php echo e($prevLesson['title']); ?></div>
                    </div>
                </a>
                <?php else: ?>
                <span></span>
                <?php endif; ?>
                
                <?php if ($nextLesson): ?>
                <a href="lesson.php?id=<?php echo $nextLesson['id']; ?>" class="nav-btn next">
                    <div style="text-align: right;">
                        <div style="font-size: 0.75rem; opacity: 0.8;">Next</div>
                        <div><?php echo e($nextLesson['title']); ?></div>
                    </div>
                    <i data-lucide="arrow-right"></i>
                </a>
                <?php else: ?>
                <span></span>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        // Tab switching
        document.querySelectorAll('.lesson-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;
                
                // Update active tab
                document.querySelectorAll('.lesson-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Show target section
                document.querySelectorAll('.lesson-section').forEach(section => {
                    section.classList.remove('active');
                    if (section.id === target) {
                        section.classList.add('active');
                    }
                });
            });
        });
        
        // Copy code function
        function copyCode(btn) {
            const code = btn.nextElementSibling.textContent;
            navigator.clipboard.writeText(code).then(() => {
                btn.innerHTML = '<i data-lucide="check" style="width: 16px; height: 16px;"></i>';
                lucide.createIcons();
                setTimeout(() => {
                    btn.innerHTML = '<i data-lucide="copy" style="width: 16px; height: 16px;"></i>';
                    lucide.createIcons();
                }, 2000);
            });
        }
    </script>
</body>
</html>