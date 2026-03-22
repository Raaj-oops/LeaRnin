<?php
require_once '../config/database.php';

// Require admin
if (!isAdmin()) {
    redirect('../dashboard.php');
}

// Handle course creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $difficulty = $_POST['difficulty'] ?? 'beginner';
    $hours = intval($_POST['hours'] ?? 10);
    
    if ($title && $description && $category) {
        $stmt = $pdo->prepare("INSERT INTO courses (title, description, category, difficulty_level, estimated_hours, created_by) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $category, $difficulty, $hours, $_SESSION['user_id']]);
        
        $courseId = $pdo->lastInsertId();
        
        // Create default module
        $stmt = $pdo->prepare("INSERT INTO modules (course_id, title, description, sort_order) VALUES (?, 'Module 1: Introduction', 'Getting started with the course', 1)");
        $stmt->execute([$courseId]);
        
        setFlash('success', 'Course created successfully!');
        redirect('manage_courses.php');
    }
}

// Get all courses
$courses = $pdo->query("SELECT c.*, u.name as instructor_name,
                        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
                        (SELECT COUNT(*) FROM modules WHERE course_id = c.id) as module_count
                        FROM courses c 
                        LEFT JOIN users u ON c.created_by = u.id 
                        ORDER BY c.created_at DESC")->fetchAll();

$categories = ['Artificial Intelligence', 'Web Development', 'Cybersecurity', 'Data Science', 'Mobile Development', 'DevOps'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - TechLearn Admin</title>
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
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 2rem;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        .modal-header h2 {
            font-size: 1.5rem;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
        }
        
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
                <li><a href="manage_courses.php" class="active">
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
                <h1>Manage Courses</h1>
                <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('active')">
                    <i data-lucide="plus"></i>
                    Create Course
                </button>
            </div>
            
            <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: var(--radius-lg); background: rgba(16, 185, 129, 0.1); color: var(--success);">
                <?php echo e($flash['message']); ?>
            </div>
            <?php endif; ?>
            
            <div class="admin-section">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Category</th>
                            <th>Level</th>
                            <th>Enrollments</th>
                            <th>Modules</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td>
                                <strong><?php echo e($course['title']); ?></strong>
                                <br><small style="color: var(--text-muted);"><?php echo e($course['instructor_name']); ?></small>
                            </td>
                            <td><?php echo e($course['category']); ?></td>
                            <td><?php echo ucfirst(e($course['difficulty_level'])); ?></td>
                            <td><?php echo number_format($course['enrollment_count']); ?></td>
                            <td><?php echo $course['module_count']; ?></td>
                            <td>
                                <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Create Course Modal -->
    <div class="modal" id="createModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Course</h2>
                <button class="modal-close" onclick="document.getElementById('createModal').classList.remove('active')">&times;</button>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Course Title</label>
                    <input type="text" name="title" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-input" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Difficulty Level</label>
                    <select name="difficulty" class="form-input">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Estimated Hours</label>
                    <input type="number" name="hours" class="form-input" value="10" min="1">
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" name="create_course" class="btn btn-primary" style="flex: 1;">Create Course</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('createModal').classList.remove('active')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>