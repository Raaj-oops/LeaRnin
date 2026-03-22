-- TechLearn Ecosystem Platform - Complete Database Schema
-- Run this in phpMyAdmin or MySQL to create the database

CREATE DATABASE IF NOT EXISTS techlearn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techlearn;

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    skill_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    bio TEXT,
    profile_picture VARCHAR(255) DEFAULT 'default-avatar.png',
    join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_admin TINYINT(1) DEFAULT 0,
    last_active DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- COURSES TABLE
-- ============================================
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    thumbnail VARCHAR(255) DEFAULT 'default-course.jpg',
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    estimated_hours INT DEFAULT 10,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    is_published TINYINT(1) DEFAULT 1,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
-- MODULES TABLE
-- ============================================
CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- ============================================
-- LESSONS TABLE
-- ============================================
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    lecture_content TEXT,
    lecture_video_url VARCHAR(500),
    tutorial_content TEXT,
    tutorial_code TEXT,
    tutorial_video_url VARCHAR(500),
    workshop_problem TEXT,
    workshop_hints TEXT,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

-- ============================================
-- RESOURCES TABLE (Lesson-specific resources)
-- ============================================
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    resource_type ENUM('video', 'article', 'documentation', 'github') NOT NULL,
    url VARCHAR(500) NOT NULL,
    description TEXT,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- ============================================
-- EXTRA RESOURCES TABLE (Course-wide resources)
-- ============================================
CREATE TABLE extra_resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    resource_type ENUM('video', 'article', 'documentation', 'github', 'course', 'book', 'podcast') NOT NULL,
    url VARCHAR(500) NOT NULL,
    description TEXT,
    tags VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- ============================================
-- ENROLLMENTS TABLE
-- ============================================
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    progress_percent INT DEFAULT 0,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- ============================================
-- LESSON PROGRESS TABLE
-- ============================================
CREATE TABLE lesson_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    is_lecture_completed TINYINT(1) DEFAULT 0,
    is_tutorial_completed TINYINT(1) DEFAULT 0,
    is_workshop_completed TINYINT(1) DEFAULT 0,
    completed_at DATETIME,
    UNIQUE KEY unique_progress (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- ============================================
-- WORKSHOP SUBMISSIONS TABLE
-- ============================================
CREATE TABLE workshop_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    github_link VARCHAR(500),
    description TEXT,
    screenshots VARCHAR(255),
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'reviewing', 'approved', 'rejected') DEFAULT 'pending',
    grade INT,
    feedback TEXT,
    reviewed_by INT,
    reviewed_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
-- PROJECTS TABLE (Portfolio)
-- ============================================
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    screenshot VARCHAR(255),
    github_link VARCHAR(500),
    live_demo VARCHAR(500),
    technologies VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    likes_count INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- PROJECT LIKES TABLE
-- ============================================
CREATE TABLE project_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    liked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- PROJECT COMMENTS TABLE
-- ============================================
CREATE TABLE project_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- COMMUNITY POSTS TABLE
-- ============================================
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'general',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    likes_count INT DEFAULT 0,
    replies_count INT DEFAULT 0,
    is_pinned TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- POST REPLIES TABLE
-- ============================================
CREATE TABLE post_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- POST LIKES TABLE
-- ============================================
CREATE TABLE post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    liked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_post_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- JOBS TABLE
-- ============================================
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    company_logo VARCHAR(255),
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    skills_required VARCHAR(500),
    location VARCHAR(200),
    job_type ENUM('full-time', 'part-time', 'internship', 'contract') DEFAULT 'full-time',
    salary_range VARCHAR(100),
    posted_by INT,
    posted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deadline DATE,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
-- JOB APPLICATIONS TABLE
-- ============================================
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    cover_letter TEXT,
    resume VARCHAR(255),
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'reviewing', 'shortlisted', 'rejected', 'hired') DEFAULT 'pending',
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- NOTICES/ANNOUNCEMENTS TABLE
-- ============================================
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    is_pinned TINYINT(1) DEFAULT 0,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
-- NOTICE REPLIES TABLE
-- ============================================
CREATE TABLE notice_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notice_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notice_id) REFERENCES notices(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- CHATBOT CONVERSATIONS TABLE
-- ============================================
CREATE TABLE chatbot_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(64) NOT NULL,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
-- CURRICULUM PROGRESS TRACKER TABLE
-- ============================================
CREATE TABLE curriculum_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    current_module INT DEFAULT 1,
    current_lesson INT DEFAULT 1,
    weekly_goal_hours INT DEFAULT 10,
    start_date DATE,
    target_end_date DATE,
    notes TEXT,
    UNIQUE KEY unique_progress (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- ============================================
-- ACHIEVEMENTS TABLE
-- ============================================
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    criteria_type VARCHAR(100),
    criteria_value INT
);

-- ============================================
-- USER ACHIEVEMENTS TABLE
-- ============================================
CREATE TABLE user_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
);

-- ============================================
-- NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50),
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- INSERT SAMPLE DATA
-- ============================================

-- Sample Users (password: 'password123' hashed with bcrypt)
INSERT INTO users (name, email, password, skill_level, bio, is_admin) VALUES
('Admin User', 'admin@techlearn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'advanced', 'Platform administrator and instructor.', 1),
('John Developer', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'intermediate', 'Full-stack developer passionate about learning.', 0),
('Sarah Student', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'beginner', 'Just starting my tech journey!', 0);

-- Sample Notices
INSERT INTO notices (title, content, priority, is_pinned, created_by) VALUES
('Welcome to TechLearn Platform!', 
'Welcome to our new learning platform! We\'re excited to have you here. Explore our courses, join the community, and start your learning journey. All courses are completely FREE!\n\nIf you have any questions, feel free to ask in the community forum or use our chatbot.', 
'high', 1, 1);

INSERT INTO notices (title, content, priority, created_by) VALUES
('New Course: Agentic AI & LLM Development', 
'We\'re thrilled to announce our newest course on Agentic AI! Learn to build autonomous AI agents using LangChain, OpenAI API, and vector databases.\n\nThis course is perfect for developers looking to leverage AI in their applications. Start learning today!', 
'medium', 1);

INSERT INTO notices (title, content, priority, created_by) VALUES
('Community Challenge: Build a Portfolio Project', 
'Join our monthly challenge! Build a portfolio project using what you\'ve learned and share it with the community.\n\nThe best projects will be featured on our homepage. Deadline: End of this month.\n\nPrizes:\n🥇 1st Place: Featured on homepage + Certificate\n🥈 2nd Place: Certificate + Shoutout\n🥉 3rd Place: Certificate', 
'medium', 1);

-- Sample Achievements
INSERT INTO achievements (title, description, icon, criteria_type, criteria_value) VALUES
('First Steps', 'Complete your first lesson', 'award', 'lessons_completed', 1),
('Course Finisher', 'Complete your first course', 'trophy', 'courses_completed', 1),
('Project Showcase', 'Upload your first project', 'folder', 'projects_uploaded', 1),
('Community Member', 'Create your first forum post', 'message-circle', 'posts_created', 1),
('Workshop Master', 'Complete 5 workshops', 'code', 'workshops_completed', 5),
('Helping Hand', 'Reply to 10 forum posts', 'heart', 'replies_count', 10),
('Job Seeker', 'Apply to your first job', 'briefcase', 'applications_count', 1);

-- Create indexes for better performance
CREATE INDEX idx_courses_category ON courses(category);
CREATE INDEX idx_courses_difficulty ON courses(difficulty_level);
CREATE INDEX idx_enrollments_user ON enrollments(user_id);
CREATE INDEX idx_enrollments_course ON enrollments(course_id);
CREATE INDEX idx_lessons_module ON lessons(module_id);
CREATE INDEX idx_posts_category ON posts(category);
CREATE INDEX idx_jobs_type ON jobs(job_type);
CREATE INDEX idx_jobs_active ON jobs(is_active);
CREATE INDEX idx_notices_pinned ON notices(is_pinned);
CREATE INDEX idx_notices_expires ON notices(expires_at);
CREATE INDEX idx_chatbot_session ON chatbot_conversations(session_id);