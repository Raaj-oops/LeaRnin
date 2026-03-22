<?php
require_once '../config/database.php';

// Get filter parameters
$category = $_GET['category'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT c.*, u.name as instructor_name,
          (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
          FROM courses c 
          LEFT JOIN users u ON c.created_by = u.id 
          WHERE c.is_published = 1";
$params = [];

if ($category) {
    $query .= " AND c.category = ?";
    $params[] = $category;
}

if ($difficulty) {
    $query .= " AND c.difficulty_level = ?";
    $params[] = $difficulty;
}

if ($search) {
    $query .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Get unique categories for filter
$stmt = $pdo->query("SELECT DISTINCT category FROM courses WHERE is_published = 1 ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Check enrollment status for logged in users
$enrolledCourses = [];
if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT course_id FROM enrollments WHERE user_id = ?");
    $stmt->execute([$userId]);
    $enrolledCourses = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - TechLearn</title>
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
            max-width: 600px;
        }
        
        .filters-bar {
            background: var(--bg-primary);
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem 0;
            position: sticky;
            top: 72px;
            z-index: 100;
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
            min-width: 180px;
        }
        
        .filter-input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .search-input {
            flex: 1;
            min-width: 250px;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            padding: 3rem 0;
        }
        
        .course-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            overflow: hidden;
            transition: all var(--transition-base);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }
        
        .course-thumbnail {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
        }
        
        .course-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }
        
        .course-card:hover .course-thumbnail img {
            transform: scale(1.1);
        }
        
        .course-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-beginner { background: var(--success); color: white; }
        .badge-intermediate { background: var(--accent); color: white; }
        .badge-advanced { background: var(--error); color: white; }
        
        .course-content {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .course-category {
            font-size: 0.875rem;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .course-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }
        
        .course-description {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 1rem;
            flex: 1;
        }
        
        .course-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        
        .course-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .course-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        
        .enrolled-badge {
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
        
        .no-results {
            text-align: center;
            padding: 4rem;
            color: var(--text-muted);
        }
        
        .no-results i {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }
        
        @media (max-width: 1024px) {
            .courses-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 640px) {
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .filters-form {
                flex-direction: column;
            }
            
            .filter-input, .search-input {
                width: 100%;
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
                <li><a href="courses.php" class="active">Courses</a></li>
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

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1>Explore Courses</h1>
            <p>Master in-demand tech skills with our structured learning paths</p>
        </div>
    </header>

    <!-- Filters Bar -->
    <div class="filters-bar">
        <div class="container">
            <form method="GET" class="filters-form">
                <input type="text" name="search" class="filter-input search-input" placeholder="Search courses..." 
                       value="<?php echo e($search); ?>">
                
                <select name="category" class="filter-input">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo e($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                        <?php echo e($cat); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="difficulty" class="filter-input">
                    <option value="">All Levels</option>
                    <option value="beginner" <?php echo $difficulty === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                    <option value="intermediate" <?php echo $difficulty === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                    <option value="advanced" <?php echo $difficulty === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="search"></i>
                    Filter
                </button>
                
                <?php if ($category || $difficulty || $search): ?>
                <a href="courses.php" class="btn btn-ghost">
                    <i data-lucide="x"></i>
                    Clear
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Courses Grid -->
    <section class="section" style="padding-top: 2rem;">
        <div class="container">
            <?php if (empty($courses)): ?>
            <div class="no-results">
                <i data-lucide="search-x"></i>
                <h3>No courses found</h3>
                <p>Try adjusting your filters or search query</p>
            </div>
            <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <div class="course-thumbnail">
                        <img src="../assets/images/<?php echo e($course['thumbnail']); ?>" alt="<?php echo e($course['title']); ?>" onerror="this.src='../assets/images/default-course.jpg'">
                        <span class="course-badge badge-<?php echo e($course['difficulty_level']); ?>">
                            <?php echo ucfirst(e($course['difficulty_level'])); ?>
                        </span>
                    </div>
                    <div class="course-content">
                        <div class="course-category"><?php echo e($course['category']); ?></div>
                        <h3 class="course-title"><?php echo e($course['title']); ?></h3>
                        <p class="course-description"><?php echo substr(e($course['description']), 0, 120) . '...'; ?></p>
                        <div class="course-meta">
                            <span><i data-lucide="clock"></i> <?php echo e($course['estimated_hours']); ?>h</span>
                            <span><i data-lucide="users"></i> <?php echo number_format($course['enrollment_count']); ?></span>
                            <span><i data-lucide="user"></i> <?php echo e($course['instructor_name'] ?? 'TechLearn'); ?></span>
                        </div>
                        <div class="course-footer">
                            <?php if (in_array($course['id'], $enrolledCourses)): ?>
                            <span class="enrolled-badge">
                                <i data-lucide="check-circle"></i>
                                Enrolled
                            </span>
                            <a href="course_details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">Continue</a>
                            <?php else: ?>
                            <span style="color: var(--text-muted); font-size: 0.875rem;">Free</span>
                            <a href="course_details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom" style="border-top: none; padding-top: 0;">
                <p>&copy; <?php echo date('Y'); ?> TechLearn. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>