<?php
require_once '../config/database.php';

$postId = $_GET['id'] ?? 0;

// View existing post
if ($postId) {
    // Get post details
    $stmt = $pdo->prepare("SELECT p.*, u.name as author_name, u.profile_picture 
                           FROM posts p 
                           JOIN users u ON p.user_id = u.id 
                           WHERE p.id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        redirect('forum.php');
    }
    
    // Get replies
    $stmt = $pdo->prepare("SELECT r.*, u.name as author_name, u.profile_picture 
                           FROM post_replies r 
                           JOIN users u ON r.user_id = u.id 
                           WHERE r.post_id = ? 
                           ORDER BY r.created_at ASC");
    $stmt->execute([$postId]);
    $replies = $stmt->fetchAll();
    
    // Handle reply submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply']) && isLoggedIn()) {
        $content = trim($_POST['content'] ?? '');
        
        if (!empty($content)) {
            $stmt = $pdo->prepare("INSERT INTO post_replies (post_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$postId, $_SESSION['user_id'], $content]);
            
            // Update reply count
            $pdo->prepare("UPDATE posts SET replies_count = replies_count + 1 WHERE id = ?")->execute([$postId]);
            
            redirect("post.php?id=$postId");
        }
    }
} 
// Create new post
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'general';
    
    if (empty($title) || empty($content)) {
        $error = 'Please fill in both title and content.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, category) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $content, $category]);
        
        $newPostId = $pdo->lastInsertId();
        redirect("post.php?id=$newPostId");
    }
}

$categories = ['general', 'showcase', 'discussion', 'help', 'jobs'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $postId ? e($post['title']) : 'New Post'; ?> - TechLearn Community</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .page-container {
            padding-top: 100px;
            min-height: 100vh;
            background: var(--bg-secondary);
        }
        
        .post-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .post-detail {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .post-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .post-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .post-meta {
            flex: 1;
        }
        
        .post-author {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .post-time {
            font-size: 0.875rem;
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
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .post-content {
            color: var(--text-secondary);
            line-height: 1.8;
            font-size: 1.05rem;
        }
        
        .post-actions-bar {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .replies-section {
            margin-top: 2rem;
        }
        
        .replies-section h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .reply-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .reply-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .reply-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .reply-content {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .reply-form {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .reply-form h3 {
            font-size: 1.125rem;
            margin-bottom: 1rem;
        }
        
        .new-post-form {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2rem;
        }
        
        .new-post-form h1 {
            font-size: 1.875rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--error);
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
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
                <a href="forum.php" class="btn btn-ghost">
                    <i data-lucide="arrow-left"></i>
                    Back to Forum
                </a>
            </div>
        </div>
    </nav>

    <div class="page-container">
        <div class="container">
            <div class="post-container">
                <?php if ($postId): ?>
                <!-- View Post -->
                <div class="post-detail">
                    <div class="post-header">
                        <img src="../assets/uploads/avatars/<?php echo e($post['profile_picture']); ?>" alt="<?php echo e($post['author_name']); ?>" class="post-avatar" onerror="this.src='../assets/images/default-avatar.png'">
                        <div class="post-meta">
                            <div class="post-author"><?php echo e($post['author_name']); ?></div>
                            <div class="post-time"><?php echo date('M j, Y \a\t g:i A', strtotime($post['created_at'])); ?></div>
                        </div>
                        <span class="post-category"><?php echo e($post['category']); ?></span>
                    </div>
                    
                    <h1 class="post-title"><?php echo e($post['title']); ?></h1>
                    
                    <div class="post-content">
                        <?php echo nl2br(e($post['content'])); ?>
                    </div>
                    
                    <div class="post-actions-bar">
                        <button class="post-action">
                            <i data-lucide="heart"></i>
                            <span><?php echo $post['likes_count']; ?> likes</span>
                        </button>
                        <button class="post-action">
                            <i data-lucide="message-circle"></i>
                            <span><?php echo $post['replies_count']; ?> replies</span>
                        </button>
                        <button class="post-action">
                            <i data-lucide="share-2"></i>
                            Share
                        </button>
                    </div>
                </div>
                
                <!-- Replies -->
                <div class="replies-section">
                    <h3>Replies (<?php echo count($replies); ?>)</h3>
                    
                    <?php foreach ($replies as $reply): ?>
                    <div class="reply-card">
                        <div class="reply-header">
                            <img src="../assets/uploads/avatars/<?php echo e($reply['profile_picture']); ?>" alt="<?php echo e($reply['author_name']); ?>" class="reply-avatar" onerror="this.src='../assets/images/default-avatar.png'">
                            <div>
                                <div style="font-weight: 600;"><?php echo e($reply['author_name']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?php echo date('M j, Y \a\t g:i A', strtotime($reply['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="reply-content">
                            <?php echo nl2br(e($reply['content'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Reply Form -->
                <?php if (isLoggedIn()): ?>
                <div class="reply-form">
                    <h3>Add a Reply</h3>
                    <form method="POST">
                        <div class="form-group">
                            <textarea name="content" class="form-textarea" rows="4" placeholder="Write your reply..." required></textarea>
                        </div>
                        <button type="submit" name="reply" class="btn btn-primary">
                            <i data-lucide="send"></i>
                            Post Reply
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="reply-form" style="text-align: center;">
                    <p style="color: var(--text-muted); margin-bottom: 1rem;">Sign in to join the discussion</p>
                    <a href="../login.php" class="btn btn-primary">Sign In</a>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <!-- New Post Form -->
                <div class="new-post-form">
                    <h1>Create New Post</h1>
                    
                    <?php if (isset($error)): ?>
                    <div class="alert-error">
                        <i data-lucide="alert-circle"></i>
                        <?php echo e($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn()): ?>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label" for="title">Title</label>
                            <input type="text" id="title" name="title" class="form-input" placeholder="What's on your mind?" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="category">Category</label>
                            <select id="category" name="category" class="form-input">
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="content">Content</label>
                            <textarea id="content" name="content" class="form-textarea" rows="8" placeholder="Share your thoughts, ask a question, or showcase your work..." required></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                                <i data-lucide="plus"></i>
                                Create Post
                            </button>
                            <a href="forum.php" class="btn btn-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                    <?php else: ?>
                    <div style="text-align: center; padding: 3rem;">
                        <p style="color: var(--text-muted); margin-bottom: 1rem;">Sign in to create a post</p>
                        <a href="../login.php" class="btn btn-primary btn-lg">Sign In</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>