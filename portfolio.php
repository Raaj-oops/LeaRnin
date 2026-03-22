<?php
require_once '../config/database.php';

// Get filter parameters
$search = $_GET['search'] ?? '';
$technology = $_GET['tech'] ?? '';

// Build query
$query = "SELECT p.*, u.name as author_name, u.profile_picture 
          FROM projects p 
          JOIN users u ON p.user_id = u.id 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.technologies LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($technology) {
    $query .= " AND p.technologies LIKE ?";
    $params[] = "%$technology%";
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Get unique technologies for filter
$allTechs = [];
foreach ($projects as $project) {
    $techs = explode(',', $project['technologies']);
    foreach ($techs as $tech) {
        $tech = trim($tech);
        if ($tech && !in_array($tech, $allTechs)) {
            $allTechs[] = $tech;
        }
    }
}
sort($allTechs);

// Check if user has liked projects (if logged in)
$likedProjects = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT project_id FROM project_likes WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $likedProjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - TechLearn</title>
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
        
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            padding: 3rem 0;
        }
        
        .portfolio-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            overflow: hidden;
            transition: all var(--transition-base);
        }
        
        .portfolio-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }
        
        .portfolio-image {
            aspect-ratio: 16/10;
            overflow: hidden;
            position: relative;
        }
        
        .portfolio-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }
        
        .portfolio-card:hover .portfolio-image img {
            transform: scale(1.05);
        }
        
        .portfolio-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            opacity: 0;
            transition: opacity var(--transition-fast);
        }
        
        .portfolio-card:hover .portfolio-overlay {
            opacity: 1;
        }
        
        .portfolio-content {
            padding: 1.5rem;
        }
        
        .portfolio-author {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .author-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .author-name {
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .portfolio-title {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        
        .portfolio-tech {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .tech-tag {
            padding: 0.25rem 0.5rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .portfolio-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        
        .portfolio-actions {
            display: flex;
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            transition: color var(--transition-fast);
        }
        
        .action-btn:hover {
            color: var(--primary);
        }
        
        .action-btn.liked {
            color: var(--error);
        }
        
        .no-results {
            text-align: center;
            padding: 4rem;
            color: var(--text-muted);
        }
        
        @media (max-width: 1024px) {
            .portfolio-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 640px) {
            .portfolio-grid {
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
                <li><a href="../courses/courses.php">Courses</a></li>
                <li><a href="portfolio.php" class="active">Portfolio</a></li>
                <li><a href="../community/forum.php">Community</a></li>
                <li><a href="../jobs/jobs.php">Jobs</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                    <i data-lucide="moon"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                    <a href="upload_project.php" class="btn btn-primary">
                        <i data-lucide="plus"></i>
                        Upload
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
            <h1>Student Portfolio</h1>
            <p>Discover amazing projects built by our community members</p>
        </div>
    </header>

    <!-- Filters -->
    <div class="filters-bar">
        <div class="container">
            <form method="GET" class="filters-form">
                <input type="text" name="search" class="filter-input search-input" placeholder="Search projects..." 
                       value="<?php echo e($search); ?>">
                
                <select name="tech" class="filter-input">
                    <option value="">All Technologies</option>
                    <?php foreach ($allTechs as $tech): ?>
                    <option value="<?php echo e($tech); ?>" <?php echo $technology === $tech ? 'selected' : ''; ?>>
                        <?php echo e($tech); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="search"></i>
                    Filter
                </button>
                
                <?php if ($search || $technology): ?>
                <a href="portfolio.php" class="btn btn-ghost">
                    <i data-lucide="x"></i>
                    Clear
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Portfolio Grid -->
    <section class="section" style="padding-top: 2rem;">
        <div class="container">
            <?php if (empty($projects)): ?>
            <div class="no-results">
                <i data-lucide="folder-x" style="width: 80px; height: 80px; margin-bottom: 1.5rem; opacity: 0.5;"></i>
                <h3>No projects found</h3>
                <p>Try adjusting your search or be the first to upload a project!</p>
            </div>
            <?php else: ?>
            <div class="portfolio-grid">
                <?php foreach ($projects as $project): ?>
                <div class="portfolio-card">
                    <div class="portfolio-image">
                        <img src="../assets/uploads/projects/<?php echo e($project['screenshot']); ?>" alt="<?php echo e($project['title']); ?>" onerror="this.src='../assets/images/default-project.jpg'">
                        <div class="portfolio-overlay">
                            <a href="<?php echo e($project['github_link']); ?>" target="_blank" class="btn btn-primary">
                                <i data-lucide="github"></i>
                                View Code
                            </a>
                            <?php if ($project['live_demo']): ?>
                            <a href="<?php echo e($project['live_demo']); ?>" target="_blank" class="btn btn-secondary">
                                <i data-lucide="external-link"></i>
                                Live Demo
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="portfolio-content">
                        <div class="portfolio-author">
                            <img src="../assets/uploads/avatars/<?php echo e($project['profile_picture']); ?>" alt="<?php echo e($project['author_name']); ?>" class="author-avatar" onerror="this.src='../assets/images/default-avatar.png'">
                            <span class="author-name"><?php echo e($project['author_name']); ?></span>
                        </div>
                        <h3 class="portfolio-title"><?php echo e($project['title']); ?></h3>
                        <div class="portfolio-tech">
                            <?php 
                            $techs = explode(',', $project['technologies']);
                            foreach (array_slice($techs, 0, 4) as $tech): 
                            ?>
                            <span class="tech-tag"><?php echo trim(e($tech)); ?></span>
                            <?php endforeach; ?>
                            <?php if (count($techs) > 4): ?>
                            <span class="tech-tag">+<?php echo count($techs) - 4; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="portfolio-footer">
                            <div class="portfolio-actions">
                                <?php if (isLoggedIn()): ?>
                                <button class="action-btn like-btn <?php echo in_array($project['id'], $likedProjects) ? 'liked' : ''; ?>" 
                                        data-id="<?php echo $project['id']; ?>">
                                    <i data-lucide="heart"></i>
                                    <span><?php echo $project['likes_count']; ?></span>
                                </button>
                                <?php else: ?>
                                <span class="action-btn">
                                    <i data-lucide="heart"></i>
                                    <span><?php echo $project['likes_count']; ?></span>
                                </span>
                                <?php endif; ?>
                            </div>
                            <span style="font-size: 0.875rem; color: var(--text-muted);">
                                <?php echo date('M j, Y', strtotime($project['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="../assets/js/script.js"></script>
</body>
</html>