<?php
require_once '../config/database.php';

// Require admin
if (!isAdmin()) {
    redirect('../dashboard.php');
}

// Handle job creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_job'])) {
    $companyName = trim($_POST['company_name'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $jobType = $_POST['job_type'] ?? 'full-time';
    $salary = trim($_POST['salary'] ?? '');
    $deadline = $_POST['deadline'] ?? null;
    
    if ($companyName && $title && $description) {
        $stmt = $pdo->prepare("INSERT INTO jobs (company_name, title, description, requirements, skills_required, location, job_type, salary_range, deadline, posted_by) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$companyName, $title, $description, $requirements, $skills, $location, $jobType, $salary, $deadline, $_SESSION['user_id']]);
        
        setFlash('success', 'Job posted successfully!');
        redirect('manage_jobs.php');
    }
}

// Get all jobs
$jobs = $pdo->query("SELECT j.*, 
                    (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as application_count
                    FROM jobs j 
                    ORDER BY j.posted_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - TechLearn Admin</title>
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
            max-width: 700px;
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
                </a></li>
                <li><a href="manage_jobs.php" class="active">
                    <i data-lucide="briefcase"></i>
                    Jobs
                </a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Manage Jobs</h1>
                <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('active')">
                    <i data-lucide="plus"></i>
                    Post Job
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
                            <th>Job</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Applications</th>
                            <th>Status</th>
                            <th>Posted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td>
                                <strong><?php echo e($job['title']); ?></strong>
                                <br><small style="color: var(--text-muted);"><?php echo e($job['company_name']); ?></small>
                            </td>
                            <td><?php echo ucfirst(str_replace('-', ' ', e($job['job_type']))); ?></td>
                            <td><?php echo e($job['location'] ?? 'Remote'); ?></td>
                            <td><?php echo $job['application_count']; ?></td>
                            <td>
                                <?php if ($job['is_active']): ?>
                                <span style="padding: 0.25rem 0.625rem; background: rgba(16, 185, 129, 0.1); color: var(--success); border-radius: var(--radius-full); font-size: 0.75rem;">Active</span>
                                <?php else: ?>
                                <span style="padding: 0.25rem 0.625rem; background: var(--bg-tertiary); color: var(--text-muted); border-radius: var(--radius-full); font-size: 0.75rem;">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($job['posted_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Create Job Modal -->
    <div class="modal" id="createModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Post New Job</h2>
                <button class="modal-close" onclick="document.getElementById('createModal').classList.remove('active')">&times;</button>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Job Title</label>
                    <input type="text" name="title" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Requirements</label>
                    <textarea name="requirements" class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Required Skills (comma separated)</label>
                    <input type="text" name="skills" class="form-input" placeholder="PHP, JavaScript, MySQL">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-input" placeholder="Remote or City">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Job Type</label>
                        <select name="job_type" class="form-input">
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="internship">Internship</option>
                            <option value="contract">Contract</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Salary Range</label>
                        <input type="text" name="salary" class="form-input" placeholder="$70k - $90k">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Application Deadline</label>
                        <input type="date" name="deadline" class="form-input">
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" name="create_job" class="btn btn-primary" style="flex: 1;">Post Job</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('createModal').classList.remove('active')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>