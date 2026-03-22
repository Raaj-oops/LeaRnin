<?php
require_once '../config/database.php';

// Get filter parameters
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT j.*, 
          (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as application_count
          FROM jobs j 
          WHERE j.is_active = 1";
$params = [];

if ($type) {
    $query .= " AND j.job_type = ?";
    $params[] = $type;
}

if ($search) {
    $query .= " AND (j.title LIKE ? OR j.company_name LIKE ? OR j.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY j.posted_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Get user's applications if logged in
$userApplications = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT job_id FROM applications WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userApplications = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs & Internships - TechLearn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
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
        
        .filters-bar {
            background: var(--bg-primary);
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem 0;
        }
        
        .filters-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-input {
            padding: 0.625rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            background: var(--bg-primary);
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        
        .search-input {
            flex: 1;
            min-width: 250px;
        }
        
        .jobs-list {
            padding: 3rem 0;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .job-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all var(--transition-base);
        }
        
        .job-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        
        .job-header {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }
        
        .job-logo {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-lg);
            object-fit: cover;
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .job-info {
            flex: 1;
        }
        
        .job-title {
            font-size: 1.375rem;
            font-weight: 700;
            margin-bottom: 0.375rem;
        }
        
        .job-company {
            color: var(--text-secondary);
            font-size: 1rem;
        }
        
        .job-badges {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }
        
        .job-badge {
            padding: 0.375rem 0.875rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .job-badge.type {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }
        
        .job-badge.salary {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .job-description {
            color: var(--text-secondary);
            margin-bottom: 1.25rem;
            line-height: 1.6;
        }
        
        .job-skills {
            margin-bottom: 1.25rem;
        }
        
        .job-skills h4 {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
        }
        
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .skill-tag {
            padding: 0.25rem 0.625rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        
        .job-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-color);
        }
        
        .job-meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .job-meta span {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .applied-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-radius: var(--radius-lg);
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .no-jobs {
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
            <a href="../index.php" class="logo">
                <div class="logo-icon">
                    <i data-lucide="code-2"></i>
                </div>
                TechLearn
            </a>
            
            <ul class="nav-links" id="nav-links">
                <li><a href="../courses/courses.php">Courses</a></li>
                <li><a href="../projects/portfolio.php">Portfolio</a></li>
                <li><a href="../community/forum.php">Community</a></li>
                <li><a href="jobs.php" class="active">Jobs</a></li>
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

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1>Jobs & Internships</h1>
            <p>Find your next opportunity in tech</p>
        </div>
    </header>

    <!-- Filters -->
    <div class="filters-bar">
        <div class="container">
            <form method="GET" class="filters-form">
                <input type="text" name="search" class="filter-input search-input" placeholder="Search jobs..." 
                       value="<?php echo e($search); ?>">
                
                <select name="type" class="filter-input">
                    <option value="">All Types</option>
                    <option value="full-time" <?php echo $type === 'full-time' ? 'selected' : ''; ?>>Full-time</option>
                    <option value="part-time" <?php echo $type === 'part-time' ? 'selected' : ''; ?>>Part-time</option>
                    <option value="internship" <?php echo $type === 'internship' ? 'selected' : ''; ?>>Internship</option>
                    <option value="contract" <?php echo $type === 'contract' ? 'selected' : ''; ?>>Contract</option>
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="search"></i>
                    Filter
                </button>
                
                <?php if ($type || $search): ?>
                <a href="jobs.php" class="btn btn-ghost">
                    <i data-lucide="x"></i>
                    Clear
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Jobs List -->
    <section class="section" style="padding-top: 2rem;">
        <div class="container">
            <div class="jobs-list">
                <?php if (empty($jobs)): ?>
                <div class="no-jobs">
                    <i data-lucide="briefcase-x" style="width: 80px; height: 80px; margin-bottom: 1.5rem; opacity: 0.5;"></i>
                    <h3>No jobs found</h3>
                    <p>Try adjusting your search criteria</p>
                </div>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <div class="job-logo">
                                <?php echo strtoupper(substr(e($job['company_name']), 0, 1)); ?>
                            </div>
                            <div class="job-info">
                                <h3 class="job-title"><?php echo e($job['title']); ?></h3>
                                <p class="job-company"><?php echo e($job['company_name']); ?></p>
                            </div>
                        </div>
                        
                        <div class="job-badges">
                            <span class="job-badge type">
                                <i data-lucide="briefcase" style="width: 14px; height: 14px;"></i>
                                <?php echo ucfirst(str_replace('-', ' ', e($job['job_type']))); ?>
                            </span>
                            <?php if ($job['salary_range']): ?>
                            <span class="job-badge salary">
                                <i data-lucide="dollar-sign" style="width: 14px; height: 14px;"></i>
                                <?php echo e($job['salary_range']); ?>
                            </span>
                            <?php endif; ?>
                            <span class="job-badge">
                                <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i>
                                <?php echo e($job['location'] ?? 'Remote'); ?>
                            </span>
                        </div>
                        
                        <p class="job-description"><?php echo substr(e($job['description']), 0, 200) . '...'; ?></p>
                        
                        <?php if ($job['skills_required']): ?>
                        <div class="job-skills">
                            <h4>Required Skills</h4>
                            <div class="skills-list">
                                <?php foreach (array_slice(explode(',', $job['skills_required']), 0, 6) as $skill): ?>
                                <span class="skill-tag"><?php echo trim(e($skill)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="job-footer">
                            <div class="job-meta">
                                <span><i data-lucide="calendar"></i> Posted <?php echo date('M j', strtotime($job['posted_at'])); ?></span>
                                <span><i data-lucide="users"></i> <?php echo $job['application_count']; ?> applicants</span>
                            </div>
                            
                            <?php if (in_array($job['id'], $userApplications)): ?>
                            <span class="applied-badge">
                                <i data-lucide="check"></i>
                                Applied
                            </span>
                            <?php else: ?>
                            <a href="apply.php?job_id=<?php echo $job['id']; ?>" class="btn btn-primary">
                                Apply Now
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="../assets/js/script.js"></script>
</body>
</html>