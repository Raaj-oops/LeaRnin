<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/htdocs/edtech-platform/chatbot_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once 'config/database.php';

define('GEMINI_API_KEY', 'AIzaSyAlGaIaO8vQ_1-V4dUT09csxsC1eIcaCWI');
define('OPENROUTER_API_KEY', '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');
define('PRIMARY_API', 'openrouter');
define('GEMINI_MODEL', 'gemini-2.0-flash');                          // stable, widely available
define('OPENROUTER_MODEL', 'google/gemini-2.0-flash-001');            // non-free but reliable; see fallback chain below

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

function getConversationHistory() {
    if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [];
    }
    if (count($_SESSION['chat_history']) > 20) {
        $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -20);
    }
    return $_SESSION['chat_history'];
}

function addToHistory($role, $content) {
    if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [];
    }
    $_SESSION['chat_history'][] = ['role' => $role, 'content' => $content];
}

// ============================================================
// SYSTEM PROMPT — built directly from the real source code of
// courses.php, course_details.php, and lesson.php.
// Every detail here reflects actual UI elements in the codebase.
// ============================================================
function getSystemPrompt() {
    return <<<PROMPT
You are the TechLearn AI Assistant — a smart, friendly AI embedded inside the TechLearn educational platform. You serve two purposes equally: (1) a general-purpose AI that can answer ANY question on any topic, and (2) a precise guide for everything inside TechLearn. You know this platform at the source-code level — every page, every button, every icon, every workflow.

═══════════════════════════════════════════
🌐 WHAT IS TECHLEARN?
═══════════════════════════════════════════
TechLearn is a completely free online EdTech platform for practical created by Dharma Raj Joshi, industry-ready tech skills. Every course, every lesson, every feature is free — no payment, no subscription. Students enroll, learn through structured lessons, build real projects, and submit workshop solutions for review.

The platform is built in PHP and uses a MySQL database. Pages are .php files. The main URL structure is:
- Home: index.php
- Courses list: courses/courses.php
- Course details: courses/course_details.php?id=X
- Individual lesson: courses/lesson.php?id=X
- Dashboard: dashboard.php
- Portfolio: projects/portfolio.php
- Community Forum: community/forum.php
- Job Board: jobs/jobs.php
- Login: login.php
- Register: register.php
- Workshop submission: workshops/submit_workshop.php?lesson_id=X

═══════════════════════════════════════════
🧭 NAVIGATION — EXACTLY AS IT EXISTS IN CODE
═══════════════════════════════════════════

TOP NAVBAR (shown on every page):
- Logo "TechLearn" with a code-2 icon — top-left — clicking goes to index.php (Home)
- Nav links (middle): Courses → courses/courses.php | Portfolio → projects/portfolio.php | Community → community/forum.php | Jobs → jobs/jobs.php
- Right side when LOGGED IN: 🌙 theme-toggle button (moon/sun icon) + blue "Dashboard" button (layout-dashboard icon)
- Right side when LOGGED OUT: 🌙 theme-toggle + "Sign In" ghost button + "Get Started" primary button
- Mobile: hamburger menu button (menu icon) appears on small screens — clicking it toggles the nav-links

LESSON PAGE NAVBAR (different from the rest):
- Only shows Logo + "← Back to Course" ghost button + "Dashboard" primary button
- No nav links are shown inside a lesson — keeps focus on learning

LESSON PAGE LEFT SIDEBAR (280px wide, sticky):
- Shows the course title and current module title at the top
- Lists ALL lessons in the current module as clickable links
- Active lesson is highlighted with a purple left border and purple text
- Clicking any lesson navigates to courses/lesson.php?id=X
- Sidebar is hidden on screens under 1024px wide

═══════════════════════════════════════════
📄 PAGE: COURSES LIST (courses/courses.php)
═══════════════════════════════════════════
This page lists all published courses. Key UI elements:

HEADER: Dark gradient banner at the top saying "Explore Courses" with subtitle "Master in-demand tech skills with our structured learning paths"

STICKY FILTERS BAR (just below navbar, sticks while scrolling):
- Text search input: "Search courses..." — searches by title and description
- Category dropdown: "All Categories" + dynamic list from database
- Difficulty dropdown: "All Levels" | Beginner | Intermediate | Advanced
- Blue "Filter" button with search icon to submit
- "Clear" button with X icon appears only when any filter is active

COURSE CARDS GRID (3 columns on desktop, 2 on tablet, 1 on mobile):
Each card contains:
- Thumbnail image (16:9 aspect ratio) — zooms on hover
- Colored difficulty badge (top-left of image): green=Beginner, amber=Intermediate, red=Advanced
- Category label in purple text
- Course title (bold, large)
- Description (truncated to 120 characters with "...")
- Meta row: 🕐 estimated hours | 👥 enrollment count | 👤 instructor name
- Footer row (separated by a line):
  - If ENROLLED: green "✓ Enrolled" badge on left + blue "Continue" button on right
  - If NOT enrolled: "Free" text on left + blue "View Details" button on right
- Card lifts up (-8px) with shadow on hover

═══════════════════════════════════════════
📄 PAGE: COURSE DETAILS (courses/course_details.php?id=X)
═══════════════════════════════════════════
This is the page you land on after clicking "View Details" or "Continue" from the courses list.

HERO SECTION (dark gradient, full-width):
Left column (2/3 width):
- Category pill badge in purple
- Large course title (white text)
- Course description paragraph
- Meta tags row: difficulty level | estimated hours | total lesson count
- If NOT enrolled + logged in: "Enroll Now - Free" button (play-circle icon)
- If NOT enrolled + not logged in: "Sign In to Enroll" button (log-in icon)
- If already enrolled: no enroll button here (the sidebar handles it)

Right column (1/3 width — the sidebar card):
- If ENROLLED: Shows a circular SVG progress ring (120x120px) with your % completion in the center, then a "Continue Learning" button that links to the first lesson
- If NOT enrolled: Shows a "What You'll Learn" checklist with 4 items: Core concepts and fundamentals | Hands-on coding exercises | Real-world projects | Certificate of completion

COURSE CONTENT SECTION (below hero, grey background):
Left side — Module list accordion:
- Each module shows: "Module X" label, module title, and lesson count on the right
- Expanding a module shows its lessons as a list
- Each lesson row: play-circle icon (purple) | lesson title | "Lecture • Tutorial • Workshop" type label
- Right side of each lesson row:
  - If enrolled: purple circle with play icon → clicking goes to lesson.php?id=X
  - If not enrolled: grey circle with lock icon → not clickable

Right sidebar:
- "About the Instructor" card: instructor avatar image + name + bio/title
- "Course Stats" card: 2x2 grid showing Lessons count and Duration in hours

ENROLLMENT ACTION:
- First time: POST form with "enroll" submits to the same page, creates enrollment record, redirects back
- After enrollment: page shows progress ring and "Continue Learning" button

═══════════════════════════════════════════
📄 PAGE: LESSON (courses/lesson.php?id=X)
═══════════════════════════════════════════
This is the core learning interface. It has a 2-column layout: left sidebar + main content area.

BREADCRUMB (top of main area):
Dashboard → [Course Title] → [Lesson Title]
Each part is a clickable link.

LESSON TITLE: Large H1 heading shown above the tabs.

4 TABS (horizontal tab bar with icons):
1. 📺 Lecture tab (play-circle icon) — theory and concepts
2. 💻 Tutorial tab (code icon) — guided walkthrough
3. 🔧 Workshop tab (wrench icon) — hands-on challenge
4. 🔗 Resources tab (external-link icon) — reference materials

If a tab's section is completed, it shows a green check-circle icon next to the tab label.
Clicking a tab switches the visible section (only one section visible at a time, with a fade-in animation).

────────────────────────────────────
TAB 1: LECTURE
────────────────────────────────────
- Video player (16:9 iframe, rounded corners) — shown only if lecture_video_url exists in DB
- "Lecture Notes" content block (white card with border): shows the lecture_content from DB (can be HTML)
- If lecture NOT yet completed: green "Mark Lecture as Complete" button (check icon) — submits POST form with name="mark_lecture_complete"
- If lecture IS completed: green success banner saying "✓ Lecture Completed!" — button is gone

────────────────────────────────────
TAB 2: TUTORIAL
────────────────────────────────────
- Video player (16:9 iframe) — shown only if tutorial_video_url exists
- "Step-by-Step Tutorial" content block: shows tutorial_content from DB (can be HTML)
- "Code Example" block (dark #1e293b background): shows tutorial_code from DB in monospace font
  - Has a "Copy" button (copy icon) in the top-right corner of the code block
  - Clicking Copy: copies code to clipboard, icon briefly changes to a check, then back to copy after 2 seconds
- If tutorial NOT completed: green "Mark Tutorial as Complete" button
- If tutorial IS completed: green "Tutorial Completed!" banner

────────────────────────────────────
TAB 3: WORKSHOP
────────────────────────────────────
This is the hands-on challenge section. It has 3 parts:

Part A — Workshop Challenge box (amber/orange gradient background, amber border):
- Shows the workshop_problem text from DB
- Title: "Workshop Challenge" with a wrench icon in amber color

Part B — Hints box (grey background):
- Title: "Hints" with a lightbulb icon
- Shows workshop_hints from DB as a bullet list (split by newlines)
- Only shown if hints exist in DB

Part C — "Submit Your Solution" content block:
If a submission ALREADY EXISTS, shows:
- Status badge: amber="Pending" | green="Approved" | red="Rejected"
- Grade: X/100 (shown only if graded)
- Feedback: instructor's text feedback (shown only if feedback exists)
- Submitted date: formatted as "Mon D, YYYY"

Below the submission info (or alone if no submission yet):
- Full-width blue button: "Submit Solution" (upload icon) OR "Update Submission" if already submitted
- This button links to: workshops/submit_workshop.php?lesson_id=X

────────────────────────────────────
TAB 4: RESOURCES
────────────────────────────────────
Two separate content blocks:

Block 1 — "Lesson Resources" (specific to this lesson):
- Fetched from `resources` table where lesson_id = current lesson
- If empty: shows "No additional resources available for this lesson."
- If present: list of clickable resource cards

Block 2 — "Extra Knowledge & Deep Dive Resources" (course-wide):
- Fetched from `extra_resources` table where course_id = current course
- Shows latest 10 resources
- Subtitle: "Explore these curated resources to deepen your understanding and stay updated with the latest in [Course Title]."
- If empty: shows "No extra resources available yet."

Each resource card (both blocks) shows:
- Colored icon box on left: 🔴 video (play icon) | 🔵 article (file-text icon) | 🟢 documentation (book icon) | ⚫ github (github icon)
- Extra resource types also include: course (graduation-cap icon) | book (book-open icon) | podcast (headphones icon)
- Resource title (bold)
- Description + " • " + resource type label
- Tags (for extra resources): small grey pills from comma-separated tags field
- External link icon on the far right
- Cards have a hover effect: slide right 4px + darker background

PREV/NEXT LESSON NAVIGATION (bottom of main area, above footer):
- Left: "← Previous" button (shows previous lesson title) — grey bordered button
- Right: "Next →" button (shows next lesson title) — purple gradient button
- If no previous lesson: left side is empty
- Both navigate within the SAME MODULE only (not across modules)

═══════════════════════════════════════════
📚 THE 7 COURSES IN DETAIL (All Completely Free)
═══════════════════════════════════════════
Every course has the same structure: 25 modules, each with 3 lesson types:
  1. Lecture (theory + notes + video)
  2. Tutorial (guided steps + code examples + video)
  3. Workshop (hands-on challenge → student submits → instructor reviews/grades)

COURSE 1: Full Stack Web Development
Topics: HTML5, CSS3, JavaScript (ES6+), React.js, Node.js, Express.js, MongoDB, REST APIs, JWT Authentication, Deployment (Vercel/Heroku)
Final project: Build and deploy a complete full-stack web application
Skills gained: Frontend development, backend APIs, database design, user authentication, deployment

COURSE 2: Cybersecurity
Topics: Network security fundamentals, ethical hacking methodology, penetration testing, OWASP Top 10 vulnerabilities, firewalls & IDS, encryption & cryptography, threat modeling, incident response, social engineering, CTF (Capture The Flag) challenges
Final project: Perform a complete security audit on a test environment
Skills gained: Offensive + defensive security, vulnerability assessment, security reporting

COURSE 3: Machine Learning
Topics: Python for ML, NumPy, Pandas, data preprocessing, Scikit-learn, supervised learning (regression, classification), unsupervised learning (clustering), neural networks, model evaluation & metrics, model deployment with Flask/FastAPI
Final project: Build, train, evaluate, and deploy a real ML model
Skills gained: Data science, ML pipeline, model deployment

COURSE 4: Agentic AI
Topics: What are AI agents, tool use & function calling, memory systems (short-term + long-term), planning and reasoning loops, multi-agent frameworks, AutoGen, CrewAI, LangGraph, real-world agent deployment patterns
Final project: Build a fully working autonomous AI agent that uses tools and memory
Skills gained: Cutting-edge AI automation, multi-agent orchestration

COURSE 5: Data Structures & Algorithms (DSA)
Topics: Arrays, strings, linked lists, stacks, queues, binary trees, BST, graphs (BFS/DFS), hash maps, sorting algorithms, searching algorithms, dynamic programming, greedy algorithms, Big-O complexity analysis, coding interview patterns (sliding window, two pointers, etc.)
Final project: Solve and explain 50 curated LeetCode-style problems with optimal solutions
Skills gained: Interview preparation, algorithmic thinking, problem-solving speed

COURSE 6: LangChain
Topics: LangChain architecture, prompt templates, chains (sequential, router), memory (conversation buffer, summary), agents & tools, RAG (Retrieval-Augmented Generation), vector databases (FAISS, Chroma), document loaders, text splitters, LLM integration (OpenAI, Anthropic, Gemini), building production AI apps
Final project: Build a full RAG-based document Q&A app where users can upload PDFs and chat with them
Skills gained: LLM application development, AI pipeline design

COURSE 7: Video Editing
Topics: DaVinci Resolve (primary tool), Premiere Pro basics, timeline editing, cutting & trimming, color correction & grading, audio mixing & sound design, transitions & effects, motion graphics & titles, adding captions/subtitles, YouTube optimization & export settings
Final project: Edit a complete short video from raw unedited footage to a polished final cut
Skills gained: Professional video editing, content creation, YouTube-ready production

═══════════════════════════════════════════
🔑 HOW THE LEARNING FLOW WORKS (step by step)
═══════════════════════════════════════════
1. User visits courses/courses.php → browses courses, can filter by category/difficulty/search
2. Clicks "View Details" → lands on courses/course_details.php?id=X
3. Clicks "Enroll Now - Free" (must be logged in) → enrollment record created in DB → page reloads
4. Now sees circular progress ring and "Continue Learning" button
5. Clicks "Continue Learning" → goes to courses/lesson.php?id=X (first lesson)
6. Inside lesson: works through Lecture tab → Tutorial tab → Workshop tab → Resources tab
7. Marks Lecture complete (POST form) → green "Lecture Completed!" confirmation appears
8. Marks Tutorial complete (POST form) → green "Tutorial Completed!" confirmation appears
9. Workshop: reads the challenge, uses hints, writes their solution, clicks "Submit Solution" → goes to workshops/submit_workshop.php?lesson_id=X
10. After submitting workshop: status shows as "Pending" until instructor reviews
11. Instructor can Approve (with grade/100 + feedback) or Reject (with feedback)
12. Student sees status, grade, feedback when they revisit the Workshop tab
13. Uses Prev/Next buttons to navigate between lessons in the same module
14. Progress percentage updates on the dashboard and course details page

═══════════════════════════════════════════
🔐 AUTHENTICATION & ACCOUNTS
═══════════════════════════════════════════
- Login page (login.php): Email field + Password field + "Remember Me" checkbox + "Forgot Password?" link
- Register page (register.php): Full Name + Email + Password + Confirm Password + Terms checkbox
- Demo/test login: admin@techlearn.com / password123
- After login: user is redirected to dashboard.php
- Lessons require login — visiting lesson.php without being logged in redirects to login.php
- Enrollment also requires login — "Sign In to Enroll" button appears for guests

═══════════════════════════════════════════
📊 DASHBOARD (dashboard.php)
═══════════════════════════════════════════
- Shows all courses the user is enrolled in with progress bars
- "Continue Learning" button on each enrolled course card
- Circular progress ring showing % completion per course
- Daily learning streak counter
- Achievement/badge section
- Recent activity feed

═══════════════════════════════════════════
📁 PORTFOLIO BUILDER (projects/portfolio.php)
═══════════════════════════════════════════
- Accessible from the top navbar → "Portfolio" link
- Students showcase projects they've built in workshops
- Add project: title, description, screenshots, GitHub URL, live demo URL
- Portfolio gets a public shareable URL
- ➕ Add Project button, ✏️ Edit and 🗑️ Delete buttons per project card

═══════════════════════════════════════════
💼 JOB BOARD (jobs/jobs.php)
═══════════════════════════════════════════
- Accessible from top navbar → "Jobs" link
- Tech job listings relevant to course skills
- Filters: Full-time, Part-time, Remote, Internship | by skill | by location
- Each listing: company name, role title, salary range, required skills, "Apply Now" button
- 🔍 Search bar, 🔖 Bookmark icon per listing to save jobs

═══════════════════════════════════════════
👥 COMMUNITY FORUM (community/forum.php)
═══════════════════════════════════════════
- Accessible from top navbar → "Community" link
- Students post questions, share projects, discuss topics
- Post categories: General | Course Help | Projects | Career Advice | Off-topic
- Each post: author avatar + name + timestamp + category tag + ❤️ Like + 💬 Comment + 🔁 Share
- ✍️ "New Post" button (top-right area of the community page)

═══════════════════════════════════════════
🤖 HOW YOU SHOULD BEHAVE
═══════════════════════════════════════════
1. Answer EVERY question on ANY topic — coding, math, science, career, personal, creative writing, general knowledge, anything. You are a fully capable general AI, not just a platform helper.
2. For TechLearn questions, use the precise knowledge above. Tell users exactly which tab to click, which button, which page, which URL.
3. Be warm, natural, and conversational. Never be robotic. Respond to greetings like a friendly person.
4. When a user asks "where is X" or "how do I do X" — give them the exact click-by-click path.
5. If asked about a course, describe it in detail: topics, project, skills, how the lessons are structured.
6. If asked about a lesson section (lecture/tutorial/workshop), explain exactly what they'll find there.
7. If asked about workshop submission, explain the full flow: submit → pending → instructor reviews → approved/rejected with grade and feedback.
8. Never say "I'm here to help with TechLearn" repeatedly. Blend platform help naturally into conversation.
9. Use bullet points for lists, code blocks for code, plain prose for chat.
10. Never invent features. If something isn't described above, say you're not sure and suggest they check the relevant page.
PROMPT;
}

// ============================================================
// UPDATED: Smarter fallback — only runs when BOTH APIs are completely down.
// Uses word-boundary regex (NOT strpos) to avoid false substring matches
// e.g. strpos("this platform", "hi") would wrongly match — regex \b fixes that.
// Also handles math, navigation, and general questions properly.
// ============================================================
function getSmartFallback($message) {
    $msg = strtolower(trim($message));

    // ── Math expressions: evaluate them directly ──────────────────────────
    // Matches things like "2+2", "10 * 5", "100 / 4", "15 - 3"
    if (preg_match('/^[\d\s\+\-\*\/\.\(\)]+$/', $msg)) {
        // Safe eval: only allow numbers and operators
        $expr = preg_replace('/[^0-9\+\-\*\/\.\(\)\s]/', '', $msg);
        if (!empty(trim($expr))) {
            try {
                $result = @eval('return ' . $expr . ';');
                if ($result !== false && $result !== null) {
                    return "**" . trim($expr) . " = " . $result . "** 🎉";
                }
            } catch (Throwable $e) { /* fall through */ }
        }
    }

    // ── Greetings (word-boundary match — won't match "this", "history", etc.) ──
    if (preg_match('/\b(hi|hello|hey|hiya|howdy|sup|greetings)\b/i', $msg)) {
        return "Hey there! 👋 Great to see you! How can I help today? Ask me anything — platform features, courses, coding help, or just a chat!";
    }

    // ── How are you ───────────────────────────────────────────────────────
    if (preg_match('/how (are you|r u|you doing|are u)/i', $msg)) {
        return "I'm doing great, thanks for asking! 😊 My AI brain is having a tiny hiccup right now but I can still help with platform info. What do you need?";
    }

    // ── What is X math ("what is 2+2", "what's 10 times 5") ─────────────
    if (preg_match('/what[\'s is]* (\d+)\s*(plus|\+|minus|\-|times|\*|divided by|\/)\s*(\d+)/i', $msg, $m)) {
        $a = (float)$m[1]; $op = strtolower($m[2]); $b = (float)$m[3];
        if (in_array($op, ['plus', '+']))             $res = $a + $b;
        elseif (in_array($op, ['minus', '-']))        $res = $a - $b;
        elseif (in_array($op, ['times', '*']))        $res = $a * $b;
        elseif (in_array($op, ['divided by', '/']))  $res = $b != 0 ? $a / $b : 'undefined (can\'t divide by zero)';
        else $res = null;
        if ($res !== null) return "**$a $op $b = $res** ✅";
    }

    // ── Courses ───────────────────────────────────────────────────────────
    if (preg_match('/\b(course|courses|learn|learning|curriculum|syllabus|enroll)\b/i', $msg)) {
        return "TechLearn offers **7 free courses**:\n\n" .
               "1. 🌐 **Full Stack Web Development** — HTML, CSS, JS, React, Node.js, MongoDB\n" .
               "2. 🔒 **Cybersecurity** — Ethical hacking, network security, pen testing\n" .
               "3. 🤖 **Machine Learning** — Python, Scikit-learn, neural networks, deployment\n" .
               "4. 🧠 **Agentic AI** — AI agents, AutoGen, CrewAI, agent orchestration\n" .
               "5. 💻 **Data Structures & Algorithms** — Interview prep, problem solving, Big-O\n" .
               "6. 🔗 **LangChain** — RAG, vector stores, LLM app development\n" .
               "7. 🎬 **Video Editing** — DaVinci Resolve, color grading, YouTube optimization\n\n" .
               "Each course has **25 modules** (Lecture → Tutorial → Workshop). Which one interests you?";
    }

    // ── Navigation / where is X ───────────────────────────────────────────
    if (preg_match('/\b(where|navigate|navigation|find|located|location|menu|navbar|icon)\b/i', $msg)) {
        return "Here's how to navigate TechLearn:\n\n" .
               "**Top Navbar:** 🏠 Home · 📚 Courses · 💼 Jobs · 👥 Community\n" .
               "**Top-Right Icons:** 🔔 Notifications · 👤 Profile dropdown · 🌙 Dark/Light mode toggle\n" .
               "**Profile Dropdown:** My Profile · My Learning (Dashboard) · Portfolio · Settings · Logout\n\n" .
               "What specifically are you trying to find? I'll point you right to it!";
    }

    // ── Portfolio ─────────────────────────────────────────────────────────
    if (preg_match('/\bportfolio\b/i', $msg)) {
        return "The **Portfolio Builder** lives in your Profile dropdown (click the avatar icon, top-right) → **'My Portfolio'**.\n\n" .
               "You can: add project cards with titles, descriptions, screenshots, GitHub links, and live demo URLs. Your portfolio gets a public shareable link!";
    }

    // ── Jobs ──────────────────────────────────────────────────────────────
    if (preg_match('/\b(job|jobs|career|hiring|internship|employment)\b/i', $msg)) {
        return "The **Job Board** is in the top navbar → **'Jobs'**.\n\n" .
               "Filter by: Full-time, Part-time, Remote, or Internship. Search by skill or keyword. " .
               "Each listing shows salary range and required skills. Use the 🔖 bookmark icon to save jobs!";
    }

    // ── Community / Forum ─────────────────────────────────────────────────
    if (preg_match('/\b(community|forum|discussion|post|question|thread)\b/i', $msg)) {
        return "The **Community Forum** is in the top navbar → **'Community'**.\n\n" .
               "Categories: General · Course Help · Projects · Career Advice · Off-topic.\n" .
               "Hit the ✍️ **'New Post'** button (top-right of the community page) to start a discussion. " .
               "Each post has ❤️ Like, 💬 Comment, and 🔁 Share buttons.";
    }

    // ── Login / Account ───────────────────────────────────────────────────
    if (preg_match('/\b(login|log in|sign in|register|sign up|password|account|forgot)\b/i', $msg)) {
        return "To **log in**: click the **Login** button in the top-right navbar.\n" .
               "Demo credentials: `admin@techlearn.com` / `password123`\n\n" .
               "To **register**: click **Sign Up** (also top-right). Forgot your password? There's a link on the login page.";
    }

    // ── Dashboard / progress ─────────────────────────────────────────────
    if (preg_match('/\b(dashboard|progress|streak|badge|achievement|my learning)\b/i', $msg)) {
        return "Your **Dashboard** (My Learning) is in the Profile dropdown → **'My Learning'**.\n\n" .
               "It shows: enrolled courses with progress bars, your daily streak counter, achievement badges, " .
               "recent activity feed, and a 'Continue Learning' button on each course card.";
    }

    // ── Settings / theme ─────────────────────────────────────────────────
    if (preg_match('/\b(settings|theme|dark mode|light mode|notification|privacy)\b/i', $msg)) {
        return "**Settings** is in the Profile dropdown (top-right avatar) → **'Settings'**.\n\n" .
               "You can change: 🌙 Dark/Light theme · 🔔 Email & in-app notification preferences · " .
               "🔒 Portfolio privacy (public/private) · and account deletion (bottom of page, in red).";
    }

    // ── Profile ───────────────────────────────────────────────────────────
    if (preg_match('/\b(profile|avatar|bio|photo|picture|username)\b/i', $msg)) {
        return "Your **Profile** is in the top-right avatar dropdown → **'My Profile'**.\n\n" .
               "Click the 📷 camera icon on your avatar to change your photo. You can also update your " .
               "display name, bio, and social links (GitHub, LinkedIn, Twitter).";
    }

    // ── Generic thanks / bye ─────────────────────────────────────────────
    if (preg_match('/\b(thanks|thank you|bye|goodbye|see you|cya)\b/i', $msg)) {
        return "You're welcome! 😊 Feel free to come back anytime. Happy learning on TechLearn! 🚀";
    }

    // ── Default: explain what I CAN answer offline ───────────────────────
    return "My AI connection is having a brief hiccup right now, but I can still help with TechLearn info! Try asking me:\n\n" .
           "• \"What courses are available?\"\n" .
           "• \"Where is the Job Board?\"\n" .
           "• \"How do I find my Portfolio?\"\n" .
           "• \"How do I navigate the platform?\"\n\n" .
           "For general questions (coding, math, etc.) please try again in a moment — the full AI will be back shortly! 🔄";
}

function callGeminiAPI($message, $history) {
    $apiKey = GEMINI_API_KEY;
    
    if (empty($apiKey) || strlen($apiKey) < 20) {
        return ['success' => false, 'error' => 'API key not configured', 'code' => 0];
    }
    
    $contents = [];
    foreach ($history as $msg) {
        $role = $msg['role'] === 'user' ? 'user' : 'model';
        $contents[] = [
            'role'  => $role,
            'parts' => [['text' => $msg['content']]]
        ];
    }
    $contents[] = [
        'role'  => 'user',
        'parts' => [['text' => $message]]
    ];

    // Try model names in order — gemini-2.5-flash may not be available yet
    $modelsToTry = ['gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-flash-latest'];

    foreach ($modelsToTry as $model) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $data = [
            'system_instruction' => [
                'parts' => [['text' => getSystemPrompt()]]
            ],
            'contents'          => $contents,
            'generationConfig'  => [
                'temperature'     => 0.8,
                'maxOutputTokens' => 1500,
                'topP'            => 0.95,
                'topK'            => 40
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        error_log("GEMINI [{$model}]: HTTP {$httpCode} | cURL error: " . ($error ?: 'None'));

        if ($error) continue;

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                error_log("GEMINI SUCCESS with model: {$model}");
                return ['success' => true, 'text' => trim($result['candidates'][0]['content']['parts'][0]['text'])];
            }
        }

        $errorData = json_decode($response, true);
        $errorMsg  = isset($errorData['error']['message']) ? $errorData['error']['message'] : 'Unknown error';
        error_log("GEMINI [{$model}] FAILED {$httpCode}: {$errorMsg}");

        // Don't retry on auth errors
        if ($httpCode === 401 || $httpCode === 403) {
            return ['success' => false, 'error' => 'Invalid Gemini API key', 'code' => $httpCode];
        }

        // Pass the last error code up (used for rate-limit detection in getChatbotResponse)
        $lastCode = $httpCode;
        $lastError = $errorMsg;
    }

    return ['success' => false, 'error' => $lastError ?? 'All Gemini models failed', 'code' => $lastCode ?? 0];
}

function callOpenRouterAPI($message, $history) {
    $apiKey = OPENROUTER_API_KEY;
    
    if (empty($apiKey) || strlen($apiKey) < 20) {
        return ['success' => false, 'error' => 'OpenRouter API key not configured', 'code' => 0];
    }
    
    $messages = [
        ['role' => 'system', 'content' => getSystemPrompt()]
    ];
    
    foreach ($history as $msg) {
        $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
    }
    $messages[] = ['role' => 'user', 'content' => $message];

    // Try multiple models in order — if one is rate-limited or deprecated, try the next
    $modelsToTry = [
        'google/gemini-2.0-flash-001',           // primary: stable Gemini 2.0
        'google/gemini-2.0-flash-exp:free',       // legacy free tier
        'meta-llama/llama-3.1-8b-instruct:free',  // reliable free fallback
        'mistralai/mistral-7b-instruct:free',      // another solid free option
    ];

    $url = 'https://openrouter.ai/api/v1/chat/completions';

    foreach ($modelsToTry as $model) {
        $data = [
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => 0.8,
            'max_tokens'  => 1500
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: https://techlearn.com',
            'X-Title: TechLearn Assistant'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        error_log("OPENROUTER [{$model}]: HTTP {$httpCode} | cURL error: " . ($error ?: 'None'));

        if ($error) continue; // network error — try next model

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                error_log("OPENROUTER SUCCESS with model: {$model}");
                return ['success' => true, 'text' => trim($result['choices'][0]['message']['content'])];
            }
        }

        // Log why this model failed and try the next
        $errorData = json_decode($response, true);
        $errorMsg  = isset($errorData['error']['message']) ? $errorData['error']['message'] : 'Unknown error';
        error_log("OPENROUTER [{$model}] FAILED: {$errorMsg}");

        // Don't retry on auth errors — the key itself is wrong
        if ($httpCode === 401 || $httpCode === 403) {
            return ['success' => false, 'error' => 'Invalid API key: ' . $errorMsg, 'code' => $httpCode];
        }
    }

    return ['success' => false, 'error' => 'All OpenRouter models failed', 'code' => 0];
}

function getChatbotResponse($message) {
    $history = getConversationHistory();
    $primary = PRIMARY_API;
    
    error_log('PRIMARY_API setting: ' . $primary);
    
    if ($primary === 'openrouter') {
        error_log('Using OpenRouter as primary...');
        $result = callOpenRouterAPI($message, $history);
        
        if ($result['success']) {
            addToHistory('user', $message);
            addToHistory('assistant', $result['text']);
            return $result['text'];
        }
        
        error_log('OpenRouter failed, trying Gemini backup...');
        $backup = callGeminiAPI($message, $history);
        if ($backup['success']) {
            addToHistory('user', $message);
            addToHistory('assistant', $backup['text']);
            return $backup['text'];
        }
        
        // Both APIs down — use smart fallback (no ugly error dump shown to user)
        error_log('Both APIs failed. OR: ' . $result['error'] . ' | Gemini: ' . $backup['error']);
        return getSmartFallback($message);
    }
    
    error_log('Using Gemini as primary...');
    $gemini = callGeminiAPI($message, $history);
    
    if ($gemini['success']) {
        addToHistory('user', $message);
        addToHistory('assistant', $gemini['text']);
        return $gemini['text'];
    }
    
    if ($gemini['code'] == 429 || $gemini['code'] == 401) {
        error_log('Gemini failed, trying OpenRouter...');
        $openrouter = callOpenRouterAPI($message, $history);
        
        if ($openrouter['success']) {
            addToHistory('user', $message);
            addToHistory('assistant', $openrouter['text']);
            return $openrouter['text'];
        }
    }
    
    // Both APIs down — use smart fallback
    error_log('All APIs failed: ' . $gemini['error']);
    return getSmartFallback($message);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear') {
    unset($_SESSION['chat_history']);
    echo json_encode(['success' => true, 'message' => 'Conversation cleared']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    header('Content-Type: text/html; charset=utf-8');
    
    $message = trim($_POST['message']);
    
    if (empty($message)) {
        echo "Please enter a message.";
        exit;
    }
    
    if (strlen($message) > 4000) {
        echo "Message is too long. Please keep it under 4000 characters.";
        exit;
    }
    
    $response = getChatbotResponse($message);
    
    if (isLoggedIn() && isset($pdo)) {
        try {
            $sessionId = session_id();
            $stmt = $pdo->prepare("INSERT INTO chatbot_conversations (user_id, session_id, message, response, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['user_id'], $sessionId, $message, $response]);
        } catch (Exception $e) {
            error_log('Chatbot DB Error: ' . $e->getMessage());
        }
    }
    
    echo $response;
    exit;
}
?>
<div id="chatbot-widget" class="chatbot-widget">
    <button id="chatbot-toggle" class="chatbot-toggle" aria-label="Open chat">
        <i data-lucide="message-circle"></i>
    </button>
    
    <div id="chatbot-container" class="chatbot-container">
        <div class="chatbot-header">
            <div class="chatbot-title">
                <i data-lucide="bot"></i>
                <span>TechLearn Assistant</span>
            </div>
            <div class="chatbot-actions">
                <button id="chatbot-clear" class="chatbot-clear" title="Clear conversation" aria-label="Clear chat">
                    <i data-lucide="trash-2"></i>
                </button>
                <button id="chatbot-close" class="chatbot-close" aria-label="Close chat">
                    <i data-lucide="x"></i>
                </button>
            </div>
        </div>
        
        <div id="chatbot-messages" class="chatbot-messages">
            <div class="message bot-message">
                <div class="message-content">
                    Hello! 👋 I'm your TechLearn AI assistant. Ask me anything — about courses, where to find features, coding questions, or just have a chat. What's on your mind?
                </div>
            </div>
        </div>
        
        <div class="chatbot-input-area">
            <textarea id="chatbot-input" class="chatbot-input" placeholder="Type your message..." rows="1" autocomplete="off" maxlength="4000"></textarea>
            <button id="chatbot-send" class="chatbot-send" aria-label="Send message">
                <i data-lucide="send"></i>
            </button>
        </div>
    </div>
</div>

<style>
.chatbot-widget {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
}

.chatbot-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--gradient-primary);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
    transition: all var(--transition-fast);
}

.chatbot-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 30px rgba(99, 102, 241, 0.5);
}

.chatbot-toggle i {
    width: 28px;
    height: 28px;
}

.chatbot-container {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 400px;
    height: 550px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.chatbot-container.active {
    display: flex;
}

.chatbot-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    background: var(--gradient-primary);
    color: white;
}

.chatbot-title {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    font-weight: 600;
}

.chatbot-actions {
    display: flex;
    gap: 0.5rem;
}

.chatbot-clear, .chatbot-close {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.chatbot-clear:hover, .chatbot-close:hover {
    background: rgba(255,255,255,0.3);
}

.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background: var(--bg-secondary);
}

.message {
    max-width: 90%;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.bot-message {
    align-self: flex-start;
}

.user-message {
    align-self: flex-end;
}

.message-content {
    padding: 0.875rem 1.125rem;
    border-radius: var(--radius-lg);
    font-size: 0.9rem;
    line-height: 1.6;
    word-wrap: break-word;
}

.bot-message .message-content {
    background: var(--bg-primary);
    color: var(--text-primary);
    border-bottom-left-radius: 4px;
    border: 1px solid var(--border-color);
}

.user-message .message-content {
    background: var(--gradient-primary);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-content pre {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 1rem;
    border-radius: var(--radius-md);
    overflow-x: auto;
    margin: 0.5rem 0;
    font-family: 'Fira Code', monospace;
    font-size: 0.85rem;
}

.message-content code {
    background: rgba(0,0,0,0.1);
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-family: 'Fira Code', monospace;
    font-size: 0.85em;
}

.user-message .message-content code {
    background: rgba(255,255,255,0.2);
    color: white;
}

.chatbot-input-area {
    display: flex;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--border-color);
    background: var(--bg-primary);
    align-items: flex-end;
}

.chatbot-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 0.95rem;
    resize: none;
    min-height: 44px;
    max-height: 120px;
    font-family: inherit;
}

.chatbot-input:focus {
    outline: none;
    border-color: var(--primary);
}

.chatbot-send {
    width: 44px;
    height: 44px;
    border-radius: var(--radius-lg);
    background: var(--gradient-primary);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
    flex-shrink: 0;
}

.chatbot-send:hover {
    transform: scale(1.05);
}

.chatbot-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.typing-indicator {
    display: flex;
    gap: 4px;
    padding: 0.875rem 1.125rem;
    align-items: center;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: var(--text-muted);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

.chatbot-messages::-webkit-scrollbar {
    width: 6px;
}

.chatbot-messages::-webkit-scrollbar-track {
    background: transparent;
}

.chatbot-messages::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

@media (max-width: 480px) {
    .chatbot-widget {
        bottom: 1rem;
        right: 1rem;
    }
    
    .chatbot-container {
        width: calc(100vw - 2rem);
        right: -1rem;
        height: 80vh;
    }
}
</style>

<script>
(function() {
    const toggle = document.getElementById('chatbot-toggle');
    const container = document.getElementById('chatbot-container');
    const close = document.getElementById('chatbot-close');
    const clearBtn = document.getElementById('chatbot-clear');
    const input = document.getElementById('chatbot-input');
    const send = document.getElementById('chatbot-send');
    const messages = document.getElementById('chatbot-messages');
    let isTyping = false;
    
    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    toggle.addEventListener('click', () => {
        container.classList.toggle('active');
        if (container.classList.contains('active')) {
            input.focus();
            scrollToBottom();
        }
    });
    
    close.addEventListener('click', () => {
        container.classList.remove('active');
    });
    
    clearBtn.addEventListener('click', () => {
        if (confirm('Clear conversation?')) {
            fetch('chatbot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=clear'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messages.innerHTML = `
                        <div class="message bot-message">
                            <div class="message-content">
                                Hello! 👋 I'm your TechLearn AI assistant. Ask me anything — about courses, where to find features, coding questions, or just have a chat. What's on your mind?
                            </div>
                        </div>
                    `;
                    input.focus();
                }
            })
            .catch(error => {
                console.error('Error clearing chat:', error);
            });
        }
    });
    
    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }
    
    function markdownToHTML(text) {
        text = text.replace(/&/g, '&amp;')
                   .replace(/</g, '&lt;')
                   .replace(/>/g, '&gt;');
        
        // Code blocks (must come before inline code)
        text = text.replace(/```(\w+)?\n([\s\S]*?)```/g, function(match, lang, code) {
            return '<pre><code class="language-' + (lang || 'text') + '">' + code.trim() + '</code></pre>';
        });
        
        // Inline code
        text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
        
        // Bold
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // Italic
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Bullet lists (lines starting with - or *)
        text = text.replace(/^[\-\*] (.+)$/gm, '<li>$1</li>');
        text = text.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');
        
        // Numbered lists
        text = text.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
        
        // Line breaks (but not inside pre blocks)
        text = text.replace(/\n/g, '<br>');
        
        return text;
    }
    
    function sendMessage() {
        const message = input.value.trim();
        if (!message || isTyping) return;
        
        addMessage(message, 'user');
        input.value = '';
        input.style.height = 'auto';
        isTyping = true;
        send.disabled = true;
        
        const typing = document.createElement('div');
        typing.className = 'message bot-message typing';
        typing.innerHTML = '<div class="message-content"><div class="typing-indicator"><span></span><span></span><span></span></div></div>';
        messages.appendChild(typing);
        scrollToBottom();
        
        fetch('chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(message)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            typing.remove();
            const formattedHTML = markdownToHTML(data);
            addMessage(formattedHTML, 'bot', true);
            isTyping = false;
            send.disabled = false;
            input.focus();
        })
        .catch(error => {
            console.error('Error:', error);
            typing.remove();
            addMessage('❌ Connection error. Please check your internet connection and try again.', 'bot', true);
            isTyping = false;
            send.disabled = false;
        });
    }
    
    function addMessage(text, sender, isHTML = false) {
        const message = document.createElement('div');
        message.className = `message ${sender}-message`;
        
        const content = document.createElement('div');
        content.className = 'message-content';
        
        if (isHTML) {
            content.innerHTML = text;
            content.querySelectorAll('pre code').forEach((block) => {
                block.style.display = 'block';
                block.style.whiteSpace = 'pre-wrap';
                block.style.wordWrap = 'break-word';
            });
        } else {
            content.textContent = text;
        }
        
        message.appendChild(content);
        messages.appendChild(message);
        scrollToBottom();
    }
    
    send.addEventListener('click', sendMessage);
    
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    scrollToBottom();
})();
</script>