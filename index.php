<?php
require_once 'config/database.php';

// Get featured courses
$stmt = $pdo->query("SELECT c.*, u.name as instructor_name 
                     FROM courses c 
                     LEFT JOIN users u ON c.created_by = u.id 
                     WHERE c.is_published = 1 
                     ORDER BY c.created_at DESC 
                     LIMIT 6");
$featuredCourses = $stmt->fetchAll();

// Get featured projects
$stmt = $pdo->query("SELECT p.*, u.name as author_name, u.profile_picture 
                     FROM projects p 
                     JOIN users u ON p.user_id = u.id 
                     ORDER BY p.likes_count DESC 
                     LIMIT 4");
$featuredProjects = $stmt->fetchAll();

// Get stats
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'courses' => $pdo->query("SELECT COUNT(*) FROM courses WHERE is_published = 1")->fetchColumn(),
    'projects' => $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
    'jobs' => $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = 1")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechLearn - Master Tech Skills, Build Your Future</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
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
                <li><a href="notices.php">Notices</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                    <i data-lucide="moon"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i data-lucide="layout-dashboard"></i>
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-ghost">Sign In</a>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="container hero-content">
            <div class="hero-text">
                <h1>Master Tech Skills.<br><span>Build Your Future.</span></h1>
                <p>Learn → Practice → Build → Showcase → Get Hired. The complete ecosystem for tech professionals to grow their careers.</p>
                <div class="hero-actions">
                    <a href="courses/courses.php" class="btn btn-primary btn-lg">
                        <i data-lucide="play-circle"></i>
                        Start Learning
                    </a>
                    <a href="projects/portfolio.php" class="btn btn-secondary btn-lg">
                        <i data-lucide="folder"></i>
                        Explore Projects
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo number_format($stats['users']); ?>+</div>
                        <div class="stat-label">Learners</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stats['courses']; ?>+</div>
                        <div class="stat-label">Courses</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stats['projects']; ?>+</div>
                        <div class="stat-label">Projects</div>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-card">
                    <div class="glass-card" style="margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="width: 48px; height: 48px; background: var(--gradient-primary); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: white;">
                                <i data-lucide="trophy"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700;">Achievement Unlocked!</div>
                                <div style="font-size: 0.875rem; color: var(--text-secondary);">Course Master</div>
                            </div>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 85%;"></div>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="glass-card" style="text-align: center;">
                            <i data-lucide="book-open" style="color: var(--primary); margin-bottom: 0.5rem;"></i>
                            <div style="font-weight: 700;">12</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Courses</div>
                        </div>
                        <div class="glass-card" style="text-align: center;">
                            <i data-lucide="code" style="color: var(--success); margin-bottom: 0.5rem;"></i>
                            <div style="font-weight: 700;">8</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Projects</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="section" style="background: var(--bg-secondary);">
        <div class="container">
            <div class="section-header">
                <h2>Your Learning Journey</h2>
                <p>A structured path from beginner to professional</p>
            </div>
            
            <div class="learning-path">
                <div class="path-step">
                    <div class="path-step-number">1</div>
                    <span>Learn</span>
                </div>
                <i data-lucide="arrow-right" class="path-arrow"></i>
                <div class="path-step">
                    <div class="path-step-number">2</div>
                    <span>Practice</span>
                </div>
                <i data-lucide="arrow-right" class="path-arrow"></i>
                <div class="path-step">
                    <div class="path-step-number">3</div>
                    <span>Build</span>
                </div>
                <i data-lucide="arrow-right" class="path-arrow"></i>
                <div class="path-step">
                    <div class="path-step-number">4</div>
                    <span>Showcase</span>
                </div>
                <i data-lucide="arrow-right" class="path-arrow"></i>
                <div class="path-step">
                    <div class="path-step-number">5</div>
                    <span>Get Hired</span>
                </div>
            </div>
            
            <div class="grid grid-4" style="margin-top: 4rem;">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="book-open"></i>
                    </div>
                    <h3>Structured Learning</h3>
                    <p>Curated courses with lectures, tutorials, and hands-on workshops</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="code"></i>
                    </div>
                    <h3>Real Projects</h3>
                    <p>Build portfolio-worthy projects that demonstrate your skills</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="users"></i>
                    </div>
                    <h3>Community</h3>
                    <p>Connect with peers, get feedback, and grow together</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="briefcase"></i>
                    </div>
                    <h3>Career Support</h3>
                    <p>Access internships and job opportunities from top companies</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>In-Demand Skills</h2>
                <p>Master the technologies shaping the future</p>
            </div>
            
            <div class="grid grid-3">
                <div class="glass-card" style="text-align: center;">
                    <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white;">
                        <i data-lucide="brain" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">AI Fundamentals</h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">Machine learning, neural networks, and AI applications</p>
                </div>
                
                <div class="glass-card" style="text-align: center;">
                    <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white;">
                        <i data-lucide="bot" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">Agentic AI</h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">Build autonomous AI agents and LLM applications</p>
                </div>
                
                <div class="glass-card" style="text-align: center;">
                    <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white;">
                        <i data-lucide="shield" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">Cybersecurity</h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">Protect systems and networks from threats</p>
                </div>
                
                <div class="glass-card" style="text-align: center;">
                    <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white;">
                        <i data-lucide="globe" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">Full Stack Web</h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">Frontend, backend, and everything in between</p>
                </div>
                
                <div class="glass-card" style="text-align: center;">
                    <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white;">
                        <i data-lucide="server" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">Backend Development</h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">Server-side programming and database design</p>
                </div>
                
                <div class="glass-card" style="text-align: center;">
                    <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white;">
                        <i data-lucide="sparkles" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">AI Tools</h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">Master Claude, GPT, and modern AI workflows</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Courses Section -->
    <section class="section" style="background: var(--bg-secondary);">
        <div class="container">
            <div class="section-header">
                <h2>Featured Courses</h2>
                <p>Start your learning journey with our most popular courses</p>
            </div>
            
            <div class="grid grid-3">
                <?php foreach ($featuredCourses as $course): ?>
                <div class="course-card">
                    <div class="course-thumbnail">
                        <img src="assets/images/<?php echo e($course['thumbnail']); ?>" alt="<?php echo e($course['title']); ?>" onerror="this.src='assets/images/default-course.jpg'">
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
                            <span><i data-lucide="user"></i> <?php echo e($course['instructor_name'] ?? 'TechLearn Team'); ?></span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="courses/course_details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">
                            View Course
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 3rem;">
                <a href="courses/courses.php" class="btn btn-secondary btn-lg">
                    View All Courses
                    <i data-lucide="arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Projects Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Student Showcase</h2>
                <p>Amazing projects built by our community members</p>
            </div>
            
            <div class="portfolio-grid">
                <?php foreach ($featuredProjects as $project): ?>
                <div class="portfolio-card">
                    <div class="portfolio-image">
                        <img src="assets/uploads/projects/<?php echo e($project['screenshot']); ?>" alt="<?php echo e($project['title']); ?>" onerror="this.src='assets/images/default-project.jpg'">
                    </div>
                    <div class="portfolio-content">
                        <h3 class="portfolio-title"><?php echo e($project['title']); ?></h3>
                        <div class="portfolio-tech">
                            <?php 
                            $techs = explode(',', $project['technologies']);
                            foreach (array_slice($techs, 0, 3) as $tech): 
                            ?>
                            <span class="tech-tag"><?php echo trim(e($tech)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="portfolio-links">
                            <a href="<?php echo e($project['github_link']); ?>" target="_blank">
                                <i data-lucide="github"></i> Code
                            </a>
                            <?php if ($project['live_demo']): ?>
                            <a href="<?php echo e($project['live_demo']); ?>" target="_blank">
                                <i data-lucide="external-link"></i> Demo
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 3rem;">
                <a href="projects/portfolio.php" class="btn btn-secondary btn-lg">
                    Explore Portfolio
                    <i data-lucide="arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section" style="background: var(--gradient-dark); color: white;">
        <div class="container" style="text-align: center;">
            <h2 style="color: white; font-size: 3rem; margin-bottom: 1rem;">Ready to Start Your Journey?</h2>
            <p style="color: rgba(255,255,255,0.8); font-size: 1.25rem; max-width: 600px; margin: 0 auto 2rem;">
                Join thousands of learners building their tech careers. Start learning for free today.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="register.php" class="btn btn-primary btn-lg" style="background: white; color: var(--primary);">
                    Get Started Free
                </a>
                <a href="courses/courses.php" class="btn btn-outline btn-lg" style="border-color: white; color: white;">
                    Browse Courses
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="logo">
                        <div class="logo-icon">
                            <i data-lucide="code-2"></i>
                        </div>
                        TechLearn
                    </a>
                    <p>The complete ecosystem for tech professionals to learn, build, and grow their careers.</p>
                </div>
                <div class="footer-links">
                    <h4>Platform</h4>
                    <ul>
                        <li><a href="courses/courses.php">Courses</a></li>
                        <li><a href="projects/portfolio.php">Portfolio</a></li>
                        <li><a href="community/forum.php">Community</a></li>
                        <li><a href="jobs/jobs.php">Jobs</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Tutorials</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Help Center</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> TechLearn. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <?php include 'chatbot.php'; ?>
</body>
</html>