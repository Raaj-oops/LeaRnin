<?php
require_once '../config/database.php';

// Get filter parameters
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT p.*, u.name as author_name, u.profile_picture 
          FROM posts p 
          JOIN users u ON p.user_id = u.id 
          WHERE 1=1";
$params = [];

if ($category) {
    $query .= " AND p.category = ?";
    $params[] = $category;
}

if ($search) {
    $query .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.is_pinned DESC, p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get categories
$categories = ['general', 'showcase', 'discussion', 'help', 'jobs'];

// Check liked posts if logged in
$likedPosts = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT post_id FROM post_likes WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $likedPosts = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community - TechLearn</title>
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
        
        .forum-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            padding: 3rem 0;
        }
        
        .forum-sidebar {
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .sidebar-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .sidebar-card h3 {
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .category-list {
            list-style: none;
        }
        
        .category-list li {
            margin-bottom: 0.5rem;
        }
        
        .category-list a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            border-radius: var(--radius-lg);
            color: var(--text-secondary);
            font-size: 0.95rem;
            transition: all var(--transition-fast);
        }
        
        .category-list a:hover,
        .category-list a.active {
            background: var(--bg-secondary);
            color: var(--primary);
        }
        
        .category-list a.active {
            background: rgba(99, 102, 241, 0.1);
        }
        
        .forum-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            background: var(--bg-primary);
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .post-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all var(--transition-fast);
        }
        
        .post-card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .post-card.pinned {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.02);
        }
        
        .post-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .post-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .post-meta {
            flex: 1;
        }
        
        .post-author {
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .post-time {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .post-category {
            padding: 0.25rem 0.75rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary);
            text-transform: capitalize;
        }
        
        .post-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        
        .post-title a {
            color: var(--text-primary);
        }
        
        .post-title a:hover {
            color: var(--primary);
        }
        
        .post-content {
            color: var(--text-secondary);
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .post-footer {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .post-action {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 0.875rem;
            cursor: pointer;
            transition: color var(--transition-fast);
        }
        
        .post-action:hover {
            color: var(--primary);
        }
        
        .post-action.liked {
            color: var(--error);
        }
        
        .pinned-badge {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: var(--primary);
            color: white;
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .no-posts {
            text-align: center;
            padding: 4rem;
            color: var(--text-muted);
        }
        
        @media (max-width: 768px) {
            .forum-layout {
                grid-template-columns: 1fr;
            }
            
            .forum-sidebar {
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
            
            <ul class="nav-links" id="nav-links">
                <li><a href="../courses/courses.php">Courses</a></li>
                <li><a href="../projects/portfolio.php">Portfolio</a></li>
                <li><a href="forum.php" class="active">Community</a></li>
                <li><a href="../jobs/jobs.php">Jobs</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                    <i data-lucide="moon"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                    <a href="post.php" class="btn btn-primary">
                        <i data-lucide="plus"></i>
                        New Post
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
            <h1>Community Forum</h1>
            <p>Connect, share, and learn with fellow developers</p>
        </div>
    </header>

    <!-- Forum Content -->
    <section class="section" style="padding-top: 2rem;">
        <div class="container">
            <div class="forum-layout">
                <!-- Sidebar -->
                <aside class="forum-sidebar">
                    <div class="sidebar-card">
                        <h3>Categories</h3>
                        <ul class="category-list">
                            <li><a href="forum.php" class="<?php echo !$category ? 'active' : ''; ?>">
                                <i data-lucide="layout-grid"></i>
                                All Posts
                            </a></li>
                            <?php foreach ($categories as $cat): ?>
                            <li><a href="forum.php?category=<?php echo $cat; ?>" class="<?php echo $category === $cat ? 'active' : ''; ?>">
                                <i data-lucide="hash"></i>
                                <?php echo ucfirst($cat); ?>
                            </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="sidebar-card">
                        <h3>Community Stats</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                            <div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                    <?php echo $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn(); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Posts</div>
                            </div>
                            <div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">
                                    <?php echo $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Members</div>
                            </div>
                        </div>
                    </div>
                </aside>
                
                <!-- Main Content -->
                <div>
                    <!-- Actions Bar -->
                    <div class="forum-actions">
                        <form method="GET" style="flex: 1; display: flex; gap: 1rem;">
                            <input type="text" name="search" class="search-input" placeholder="Search posts..." 
                                   value="<?php echo e($search); ?>">
                            <?php if ($category): ?>
                            <input type="hidden" name="category" value="<?php echo e($category); ?>">
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="search"></i>
                            </button>
                            <?php if ($search): ?>
                            <a href="forum.php<?php echo $category ? '?category=' . $category : ''; ?>" class="btn btn-ghost">
                                <i data-lucide="x"></i>
                            </a>
                            <?php endif; ?>
                        </form>
                        
                        <?php if (isLoggedIn()): ?>
                        <a href="post.php" class="btn btn-primary">
                            <i data-lucide="plus"></i>
                            New Post
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Posts List -->
                    <?php if (empty($posts)): ?>
                    <div class="no-posts">
                        <i data-lucide="message-square-x" style="width: 80px; height: 80px; margin-bottom: 1.5rem; opacity: 0.5;"></i>
                        <h3>No posts found</h3>
                        <p>Be the first to start a discussion!</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                        <div class="post-card <?php echo $post['is_pinned'] ? 'pinned' : ''; ?>">
                            <div class="post-header">
                                <img src="../assets/uploads/avatars/<?php echo e($post['profile_picture']); ?>" alt="<?php echo e($post['author_name']); ?>" class="post-avatar" onerror="this.src='../assets/images/default-avatar.png'">
                                <div class="post-meta">
                                    <div class="post-author"><?php echo e($post['author_name']); ?></div>
                                    <div class="post-time"><?php echo date('M j, Y \a\t g:i A', strtotime($post['created_at'])); ?></div>
                                </div>
                                <span class="post-category"><?php echo e($post['category']); ?></span>
                                <?php if ($post['is_pinned']): ?>
                                <span class="pinned-badge">
                                    <i data-lucide="pin" style="width: 12px; height: 12px;"></i>
                                    Pinned
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <h3 class="post-title">
                                <a href="post.php?id=<?php echo $post['id']; ?>"><?php echo e($post['title']); ?></a>
                            </h3>
                            
                            <p class="post-content"><?php echo substr(e($post['content']), 0, 200) . '...'; ?></p>
                            
                            <div class="post-footer">
                                <?php if (isLoggedIn()): ?>
                                <button class="post-action like-btn <?php echo in_array($post['id'], $likedPosts) ? 'liked' : ''; ?>" data-id="<?php echo $post['id']; ?>">
                                    <i data-lucide="heart"></i>
                                    <span><?php echo $post['likes_count']; ?></span>
                                </button>
                                <?php else: ?>
                                <span class="post-action">
                                    <i data-lucide="heart"></i>
                                    <span><?php echo $post['likes_count']; ?></span>
                                </span>
                                <?php endif; ?>
                                
                                <a href="post.php?id=<?php echo $post['id']; ?>" class="post-action">
                                    <i data-lucide="message-circle"></i>
                                    <span><?php echo $post['replies_count']; ?> replies</span>
                                </a>
                                
                                <button class="post-action">
                                    <i data-lucide="share-2"></i>
                                    Share
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script src="../assets/js/script.js"></script>
</body>
</html>