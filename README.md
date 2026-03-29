# TechLearn Platform - Complete Learning Ecosystem

A comprehensive tech learning ecosystem platform with 7 complete courses, curated free resources, community features, job board, and AI chatbot.

## 🎓 Available Courses (All FREE!)

### 1. Full Stack Web Development (30 weeks)
Complete journey from HTML basics to deploying production-ready applications.
- **Modules**: 25 modules, 75 lessons
- **Hours**: 180 hours
- **Topics**: HTML5, CSS3, JavaScript, React, PHP, Node.js, MySQL, MongoDB, REST APIs, GraphQL, DevOps
- **Projects**: 15+ real-world projects
- **Outcome**: Job-ready full stack developer

### 2. Cybersecurity Fundamentals (28 weeks)
From networking basics to ethical hacking and security operations.
- **Modules**: 25 modules, 75 lessons
- **Hours**: 160 hours
- **Topics**: Network security, cryptography, ethical hacking, penetration testing, SOC operations, incident response
- **Certification**: Prepares for CompTIA Security+
- **Outcome**: Entry-level security analyst

### 3. Machine Learning Fundamentals (30 weeks)
Master ML from scratch with Python and real-world projects.
- **Modules**: 25 modules, 75 lessons
- **Hours**: 180 hours
- **Topics**: Python, NumPy, Pandas, supervised/unsupervised learning, neural networks, deep learning, model deployment
- **Projects**: Spam detector, image classifier, recommendation system, price predictor
- **Outcome**: ML engineer ready

### 4. Agentic AI & LLM Development (25 weeks)
Build autonomous AI agents using Large Language Models.
- **Modules**: 20 modules, 60 lessons
- **Hours**: 150 hours
- **Topics**: GPT, Claude, Llama, prompt engineering, LangChain, RAG, vector databases, multi-agent systems
- **Projects**: AI research agent, code assistant, document analyzer
- **Outcome**: AI application developer

### 5. Data Structures & Algorithms (30 weeks)
Master DSA for coding interviews at top tech companies.
- **Modules**: 25 modules, 75 lessons
- **Hours**: 180 hours
- **Topics**: Arrays, linked lists, trees, graphs, sorting, searching, dynamic programming, greedy algorithms
- **Problems**: 200+ LeetCode-style problems
- **Outcome**: FAANG interview ready

### 6. LangChain & RAG Systems (20 weeks)
Build production-ready AI applications with LangChain.
- **Modules**: 18 modules, 54 lessons
- **Hours**: 120 hours
- **Topics**: Chains, agents, memory, document loaders, vector DBs, Retrieval-Augmented Generation
- **Projects**: Document Q&A bot, knowledge base assistant
- **Outcome**: AI integration specialist

### 7. Video Editing & Motion Graphics (20 weeks)
Create professional videos with industry-standard tools.
- **Modules**: 18 modules, 54 lessons
- **Hours**: 120 hours
- **Topics**: DaVinci Resolve, After Effects, color grading, motion graphics, VFX, audio mixing
- **Projects**: Short film, motion graphics reel, commercial
- **Outcome**: Professional video editor

## ✨ Platform Features

### Learning System
- **Lecture**: Video lessons + comprehensive notes
- **Tutorial**: Step-by-step coding walkthroughs
- **Workshop**: Real-world problem solving with submission & grading
- **Progress Tracking**: Visual progress indicators
- **Extra Resources**: Curated free resources for deep dives

### Community Features
- **Forum**: Discussion boards by category
- **Portfolio**: Showcase projects with GitHub/live demo links
- **Likes & Comments**: Engage with community content
- **Notices**: Admin announcements with user replies

### Job Board
- Browse internships and full-time positions
- Apply directly through platform
- Track application status
- Filter by job type, location, skills

### AI Chatbot
- 24/7 assistance for platform navigation
- Course recommendations
- Technical support
- Learning path guidance

### Admin Panel
- Create and manage courses
- Review workshop submissions with grading
- Post notices and announcements
- Manage users and job listings
- View platform analytics

## 🚀 Installation

### Prerequisites
- XAMPP, WAMP, or any PHP/MySQL server
- PHP 7.4+
- MySQL 5.7+

### Setup Steps

1. **Copy files to web server**:
   ```bash
   # XAMPP
   C:\xampp\htdocs\edtech-platform\
   
   # WAMP
   C:\wamp\www\edtech-platform\
   ```

2. **Create database**:
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `techlearn`
   - Import: `database/schema.sql`
   - Import: `database/courses_data.sql`

3. **Configure database** (if needed):
   ```php
   // config/database.php
   define('DB_HOST', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', '');
   define('DB_NAME', 'techlearn');
   ```

4. **Set permissions**:
   ```bash
   chmod 755 assets/uploads/projects/
   chmod 755 assets/uploads/avatars/
   chmod 755 assets/uploads/submissions/
   ```

5. **Access platform**:
   ```
   http://localhost/edtech-platform
   ```

## 👤 Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@techlearn.com | password123 |
| Student | john@example.com | password123 |
| Student | sarah@example.com | password123 |

## 📁 Project Structure

```
edtech-platform/
├── index.php                    # Landing page
├── login.php / register.php     # Authentication
├── dashboard.php                # User dashboard
├── notices.php                  # Announcements
├── chatbot.php                  # AI assistant widget
├── config/
│   └── database.php             # DB config & helpers
├── database/
│   ├── schema.sql               # Database schema
│   └── courses_data.sql         # Course content
├── courses/                     # Course browsing & lessons
├── workshops/                   # Workshop submissions
├── projects/                    # Portfolio system
├── community/                   # Forum
├── jobs/                        # Job board
├── admin/                       # Admin panel
│   ├── admin_dashboard.php
│   ├── manage_courses.php
│   ├── manage_users.php
│   ├── review_submissions.php
│   ├── manage_jobs.php
│   └── notices.php
└── assets/
    ├── css/style.css
    ├── js/script.js
    └── uploads/
```

## 🎓 Learning Path

### Week 1-4: Foundations
- Web fundamentals / Python basics
- Core concepts and theory
- First hands-on tutorials

### Week 5-12: Building Skills
- Intermediate concepts
- Complex tutorials
- Weekly workshops
- Start portfolio projects

### Week 13-20: Advanced Topics
- Advanced concepts
- Real-world projects
- Community engagement
- Interview preparation

### Week 21-30: Mastery & Job Prep
- Capstone projects
- Portfolio completion
- Mock interviews
- Job applications

## 🔒 Security Features

- Password hashing (bcrypt)
- CSRF protection
- Prepared statements (SQL injection prevention)
- XSS protection (output encoding)
- Session management
- Input validation

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Icons**: Lucide
- **Fonts**: Inter (Google Fonts)

## 📚 Curated Resources

Each course includes:
- **Free video tutorials** (YouTube)
- **Official documentation**
- **Blog articles**
- **GitHub repositories**
- **Free courses**
- **Books (free/PDF)**
- **Podcasts**

### 🔗 Notable External Resources

- **freeCodeCamp** — [github.com/freeCodeCamp/freeCodeCamp](https://github.com/freeCodeCamp/freeCodeCamp)
  Free, open-source coding curriculum with thousands of exercises and projects covering web development, data science, and more.

## 🎯 Outcomes

After completing any course:
- ✅ Job-ready portfolio
- ✅ Industry-relevant skills
- ✅ Certificate of completion
- ✅ Community connections
- ✅ Interview preparation

## 🤝 Contributing

This is an educational platform. Feel free to:
- Add new courses
- Improve existing content
- Fix bugs
- Enhance features

## 📄 License

Free for educational use.

---

**Start your learning journey today!** 🚀