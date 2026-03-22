-- TechLearn Platform - Comprehensive Course Data
-- This file contains complete curricula for all courses

USE techlearn;

-- ============================================
-- COURSE 1: FULL STACK WEB DEVELOPMENT
-- Duration: 30 weeks | 25 Modules | 75 Lessons
-- ============================================

INSERT INTO courses (title, description, category, thumbnail, difficulty_level, estimated_hours, created_by, is_published) VALUES
('Full Stack Web Development Bootcamp', 
'Complete journey from HTML basics to deploying production-ready applications. Master frontend (HTML, CSS, JavaScript, React), backend (PHP, Node.js), databases (MySQL, MongoDB), and DevOps. By the end, you\'ll build 15+ projects and be job-ready.', 
'Web Development', 'fullstack-course.jpg', 'beginner', 180, 1, 1);

SET @course1 = LAST_INSERT_ID();

-- Module 1: Web Fundamentals (Week 1)
INSERT INTO modules (course_id, title, description, sort_order) VALUES
(@course1, 'Module 1: Web Fundamentals & HTML5', 'Understanding how the web works and building your first web pages with HTML5', 1);
SET @mod1 = LAST_INSERT_ID();

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@mod1, 'Chapter 1.1: How the Web Works', 
'Understanding HTTP, DNS, browsers, and the client-server model',
'<h3>How the Internet Works</h3>
<p>Before writing code, understand the infrastructure:</p>
<ul>
<li><strong>HTTP/HTTPS:</strong> The protocol that powers the web</li>
<li><strong>DNS:</strong> How domain names translate to IP addresses</li>
<li><strong>Client-Server Model:</strong> Request-response cycle</li>
<li><strong>Browser Rendering:</strong> How browsers parse HTML/CSS/JS</li>
</ul>
<h3>Key Concepts</h3>
<p>When you type google.com, your browser:</p>
<ol>
<li>Queries DNS servers to find Google\'s IP</li>
<li>Sends HTTP GET request to that IP</li>
<li>Server responds with HTML/CSS/JS</li>
<li>Browser renders the page</li>
</ol>',
'https://www.youtube.com/embed/7_LPdttKXPc',
'<h3>Inspecting Network Requests</h3>
<p>Open Chrome DevTools (F12) → Network tab:</p>
<ol>
<li>Visit any website</li>
<li>Watch requests being made</li>
<li>Examine request/response headers</li>
<li>See timing breakdown</li>
</ol>',
NULL,
'Build a simple HTML page that displays your browser\'s user agent and screen resolution using JavaScript',
'1. Use navigator.userAgent\n2. Use window.screen.width/height\n3. Display in a styled div',
1);

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@mod1, 'Chapter 1.2: HTML5 Semantic Structure', 
'Building well-structured, accessible web pages',
'<h3>HTML5 Semantic Elements</h3>
<p>Semantic HTML provides meaning to your content:</p>
<ul>
<li><code>&lt;header&gt;</code> - Introductory content</li>
<li><code>&lt;nav&gt;</code> - Navigation links</li>
<li><code>&lt;main&gt;</code> - Primary content</li>
<li><code>&lt;article&gt;</code> - Self-contained content</li>
<li><code>&lt;section&gt;</code> - Thematic grouping</li>
<li><code>&lt;aside&gt;</code> - Sidebar content</li>
<li><code>&lt;footer&gt;</code> - Footer content</li>
</ul>
<h3>Accessibility Matters</h3>
<p>Use proper heading hierarchy (h1→h6), alt text for images, and ARIA labels when needed.',
'https://www.youtube.com/embed/kGW8Al_cga4',
'<h3>Building a Blog Post Structure</h3>',
'&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;My Blog Post&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;header&gt;
        &lt;h1&gt;My Tech Blog&lt;/h1&gt;
        &lt;nav&gt;
            &lt;a href="/"&gt;Home&lt;/a&gt;
            &lt;a href="/about"&gt;About&lt;/a&gt;
        &lt;/nav&gt;
    &lt;/header&gt;
    
    &lt;main&gt;
        &lt;article&gt;
            &lt;header&gt;
                &lt;h2&gt;Learning Web Development&lt;/h2&gt;
                &lt;time datetime="2024-01-15"&gt;Jan 15, 2024&lt;/time&gt;
            &lt;/header&gt;
            &lt;p&gt;Content here...&lt;/p&gt;
        &lt;/article&gt;
    &lt;/main&gt;
    
    &lt;aside&gt;
        &lt;h3&gt;Related Posts&lt;/h3&gt;
    &lt;/aside&gt;
    
    &lt;footer&gt;
        &lt;p&gt;&copy; 2024 My Blog&lt;/p&gt;
    &lt;/footer&gt;
&lt;/body&gt;
&lt;/html&gt;',
'Create a complete personal portfolio homepage with semantic HTML5 including: header with navigation, hero section with your photo, about section, skills section with progress bars, projects grid, contact form, and footer with social links',
'1. Use semantic elements throughout\n2. Include proper meta tags for SEO\n3. Add Open Graph tags\n4. Ensure accessibility with alt texts\n5. Use proper form labels',
2);

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@mod1, 'Chapter 1.3: Forms & User Input', 
'Creating interactive forms with validation',
'<h3>HTML Forms Deep Dive</h3>
<p>Forms are the primary way users interact with websites:</p>
<ul>
<li><strong>Input Types:</strong> text, email, password, number, date, tel, url</li>
<li><strong>Validation:</strong> required, pattern, min, max, minlength, maxlength</li>
<li><strong>Accessibility:</strong> label associations, fieldset/legend</li>
</ul>
<h3>Modern Form Features</h3>
<p>HTML5 brought powerful form features without JavaScript:</p>
<ul>
<li>Placeholder text</li>
<li>Autocomplete attributes</li>
<li>Datalist for suggestions</li>
<li>Built-in validation</li>
</ul>',
'https://www.youtube.com/embed/fNcJuPIZ2WE',
'<h3>Building a Registration Form</h3>',
'&lt;form action="/register" method="POST" novalidate&gt;
    &lt;fieldset&gt;
        &lt;legend&gt;Personal Information&lt;/legend&gt;
        
        &lt;label for="fullname"&gt;Full Name *&lt;/label&gt;
        &lt;input type="text" id="fullname" name="fullname" 
               required minlength="2" 
               placeholder="John Doe"&gt;
        
        &lt;label for="email"&gt;Email *&lt;/label&gt;
        &lt;input type="email" id="email" name="email" 
               required placeholder="john@example.com"&gt;
        
        &lt;label for="password"&gt;Password *&lt;/label&gt;
        &lt;input type="password" id="password" name="password" 
               required minlength="8" 
               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"&gt;
        &lt;small&gt;Must contain: 8+ chars, 1 uppercase, 1 lowercase, 1 number&lt;/small&gt;
        
        &lt;label for="birthdate"&gt;Birth Date&lt;/label&gt;
        &lt;input type="date" id="birthdate" name="birthdate" 
               max="2006-01-01"&gt;
    &lt;/fieldset&gt;
    
    &lt;button type="submit"&gt;Register&lt;/button&gt;
&lt;/form&gt;',
'Build a complete multi-step job application form with: personal info, education history (dynamic add/remove), work experience, skills (checkboxes), cover letter, and file upload for resume',
'1. Use fieldset to group sections\n2. Add client-side validation\n3. Show progress indicator\n4. Make it accessible\n5. Style with CSS (next module)',
3);

-- Resources for Module 1
INSERT INTO resources (lesson_id, title, resource_type, url, description) VALUES
(LAST_INSERT_ID() - 2, 'MDN: How the Web Works', 'documentation', 'https://developer.mozilla.org/en-US/docs/Learn/Getting_started_with_the_web/How_the_Web_works', 'Mozilla\'s comprehensive guide to web fundamentals'),
(LAST_INSERT_ID() - 2, 'HTTP Status Codes Explained', 'article', 'https://httpstatuses.com/', 'Complete reference for HTTP status codes'),
(LAST_INSERT_ID() - 1, 'HTML5 Semantic Elements', 'documentation', 'https://developer.mozilla.org/en-US/docs/Glossary/Semantics', 'MDN guide to semantic HTML'),
(LAST_INSERT_ID() - 1, 'Web Accessibility Guidelines', 'documentation', 'https://www.w3.org/WAI/WCAG21/quickref/', 'Official WCAG 2.1 guidelines'),
(LAST_INSERT_ID(), 'HTML Forms Guide', 'documentation', 'https://developer.mozilla.org/en-US/docs/Learn/Forms', 'Complete forms tutorial from MDN'),
(LAST_INSERT_ID(), 'Form Validation Tutorial', 'video', 'https://www.youtube.com/embed/rsd4FNGTRBw', 'Learn form validation techniques');

-- Module 2: CSS Fundamentals (Week 2)
INSERT INTO modules (course_id, title, description, sort_order) VALUES
(@course1, 'Module 2: CSS3 Styling & Layout', 'Master CSS selectors, box model, flexbox, and grid for beautiful layouts', 2);
SET @mod2 = LAST_INSERT_ID();

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@mod2, 'Chapter 2.1: CSS Selectors & Specificity', 
'Understanding how CSS selects and styles elements',
'<h3>CSS Selectors</h3>
<p>Selectors target HTML elements to style:</p>
<ul>
<li><strong>Basic:</strong> element, .class, #id</li>
<li><strong>Combinators:</strong> descendant ( ), child (>), adjacent (+), general (~)</li>
<li><strong>Attribute:</strong> [attr], [attr=value], [attr^=value], [attr$=value], [attr*=value]</li>
<li><strong>Pseudo-classes:</strong> :hover, :focus, :nth-child(), :not()</li>
<li><strong>Pseudo-elements:</strong> ::before, ::after, ::first-line</li>
</ul>
<h3>Specificity Hierarchy</h3>
<p>When styles conflict, specificity determines winner:</p>
<ol>
<li>Inline styles (1000)</li>
<li>IDs (100)</li>
<li>Classes, attributes, pseudo-classes (10)</li>
<li>Elements, pseudo-elements (1)</li>
</ol>',
'https://www.youtube.com/embed/1h5StQJ8hww',
'<h3>Building a Styled Navigation</h3>',
'/* Reset and base styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Navigation styling */
.navbar {
    background: #1a1a2e;
    padding: 1rem 2rem;
}

.navbar ul {
    display: flex;
    list-style: none;
    gap: 2rem;
}

.navbar a {
    color: #fff;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: background 0.3s;
}

.navbar a:hover,
.navbar a:focus {
    background: #16213e;
}

/* Active page indicator */
.navbar a[aria-current="page"] {
    background: #e94560;
}

/* Dropdown using :hover */
.dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    background: #fff;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.dropdown:hover .dropdown-menu {
    display: block;
}',
'Style your portfolio homepage from Module 1: Create a responsive navigation, style the hero section with a gradient background, add card styles for projects, and create a beautiful contact form',
'1. Use CSS variables for colors\n2. Implement hover effects\n3. Add transitions\n4. Use pseudo-elements for decorative elements\n5. Ensure good contrast ratios',
1);

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@mod2, 'Chapter 2.2: Flexbox Layout Mastery', 
'One-dimensional layout system for alignment and distribution',
'<h3>Flexbox Fundamentals</h3>
<p>Flexbox is perfect for one-dimensional layouts (row OR column):</p>
<ul>
<li><strong>flex-direction:</strong> row, row-reverse, column, column-reverse</li>
<li><strong>justify-content:</strong> flex-start, center, flex-end, space-between, space-around, space-evenly</li>
<li><strong>align-items:</strong> stretch, flex-start, center, flex-end, baseline</li>
<li><strong>flex-wrap:</strong> nowrap, wrap, wrap-reverse</li>
<li><strong>align-content:</strong> For multi-line flex containers</li>
</ul>
<h3>Flex Item Properties</h3>
<ul>
<li><strong>flex-grow:</strong> How much item grows relative to others</li>
<li><strong>flex-shrink:</strong> How much item shrinks</li>
<li><strong>flex-basis:</strong> Default size before growing/shrinking</li>
<li><strong>flex:</strong> Shorthand (grow shrink basis)</li>
<li><strong>align-self:</strong> Override align-items for individual item</li>
</ul>',
'https://www.youtube.com/embed/K74l26pE4YA',
'<h3>Common Flexbox Patterns</h3>',
'/* Center anything */
.center-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* Card layout */
.card-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    padding: 2rem;
}

.card {
    flex: 1 1 300px; /* Grow, shrink, basis */
    max-width: 400px;
}

/* Sidebar layout */
.page-layout {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    flex: 0 0 250px; /* Fixed width */
}

.main-content {
    flex: 1; /* Takes remaining space */
}

/* Navigation */
.nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
}

.nav-links {
    display: flex;
    gap: 2rem;
}',
'Build a complete dashboard layout with: fixed sidebar navigation, header with search and user menu, main content area with stats cards in a responsive grid, recent activity list, and a footer',
'1. Use flexbox for the overall layout\n2. Sidebar should be fixed width\n3. Main content should scroll\n4. Stats cards should wrap on smaller screens\n5. Header should stay at top',
2);

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@mod2, 'Chapter 2.3: CSS Grid Advanced Layouts', 
'Two-dimensional layout system for complex designs',
'<h3>CSS Grid Fundamentals</h3>
<p>Grid is for two-dimensional layouts (rows AND columns):</p>
<ul>
<li><strong>grid-template-columns:</strong> Define column sizes (px, %, fr, auto, repeat())</li>
<li><strong>grid-template-rows:</strong> Define row sizes</li>
<li><strong>grid-gap/gap:</strong> Spacing between grid items</li>
<li><strong>grid-template-areas:</strong> Named grid areas for visual layout</li>
</ul>
<h3>Grid Item Placement</h3>
<ul>
<li><strong>grid-column:</strong> Start/end column lines (e.g., 1 / 3)</li>
<li><strong>grid-row:</strong> Start/end row lines</li>
<li><strong>grid-area:</strong> Place in named area</li>
</ul>
<h3>Auto-placement</h3>
<p>grid-auto-flow controls how items fill: row, column, dense</p>',
'https://www.youtube.com/embed/9zBsdzdS4wM',
'<h3>Building a Photo Gallery Grid</h3>',
'/* Responsive photo gallery */
.gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
    padding: 1rem;
}

/* Featured image spans 2x2 */
.gallery-item.featured {
    grid-column: span 2;
    grid-row: span 2;
}

/* Holy Grail Layout */
.holy-grail {
    display: grid;
    grid-template-columns: 200px 1fr 200px;
    grid-template-rows: auto 1fr auto;
    grid-template-areas:
        "header header header"
        "nav main aside"
        "footer footer footer";
    min-height: 100vh;
}

.holy-grail header { grid-area: header; }
.holy-grail nav { grid-area: nav; }
.holy-grail main { grid-area: main; }
.holy-grail aside { grid-area: aside; }
.holy-grail footer { grid-area: footer; }

/* Dashboard grid */
.dashboard {
    display: grid;
    grid-template-columns: 250px 1fr;
    grid-template-rows: 60px 1fr;
    gap: 0;
    height: 100vh;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}',
'Create a Pinterest-style masonry layout with: variable height images, responsive columns (1 on mobile, 2 on tablet, 3 on desktop), hover overlay with image info, and smooth transitions',
'1. Use grid-auto-rows for masonry effect\n2. Implement responsive breakpoints\n3. Add hover overlays\n4. Use object-fit for images\n5. Add loading placeholders',
3);

-- Resources for Module 2
INSERT INTO resources (lesson_id, title, resource_type, url, description) VALUES
(LAST_INSERT_ID() - 2, 'CSS-Tricks: Complete Guide to Flexbox', 'documentation', 'https://css-tricks.com/snippets/css/a-guide-to-flexbox/', 'The ultimate flexbox reference'),
(LAST_INSERT_ID() - 2, 'Flexbox Froggy Game', 'article', 'https://flexboxfroggy.com/', 'Learn flexbox by playing a game'),
(LAST_INSERT_ID() - 1, 'CSS-Tricks: Complete Guide to Grid', 'documentation', 'https://css-tricks.com/snippets/css/complete-guide-grid/', 'The ultimate grid reference'),
(LAST_INSERT_ID() - 1, 'Grid Garden Game', 'article', 'https://cssgridgarden.com/', 'Learn grid by playing a game'),
(LAST_INSERT_ID(), 'Modern CSS Layouts', 'video', 'https://www.youtube.com/embed/qm0IfG1GyZU', 'Modern layout techniques'),
(LAST_INSERT_ID(), 'CSS Specificity Calculator', 'article', 'https://specificity.keegan.st/', 'Calculate selector specificity');

-- ============================================
-- COURSE 2: CYBERSECURITY FUNDAMENTALS
-- Duration: 28 weeks | 25 Modules
-- ============================================

INSERT INTO courses (title, description, category, thumbnail, difficulty_level, estimated_hours, created_by, is_published) VALUES
('Cybersecurity Fundamentals', 
'From networking basics to ethical hacking. Learn to protect systems, identify vulnerabilities, and respond to security incidents. Covers network security, cryptography, penetration testing, and security operations. Prepare for CompTIA Security+.', 
'Cybersecurity', 'cyber-course.jpg', 'beginner', 160, 1, 1);

SET @course2 = LAST_INSERT_ID();

INSERT INTO modules (course_id, title, description, sort_order) VALUES
(@course2, 'Module 1: Introduction to Cybersecurity', 'Understanding the threat landscape and security fundamentals', 1);
SET @cyber_mod1 = LAST_INSERT_ID();

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@cyber_mod1, 'Chapter 1.1: The Cyber Threat Landscape', 
'Understanding types of threats and attackers',
'<h3>Types of Cyber Threats</h3>
<ul>
<li><strong>Malware:</strong> Viruses, worms, trojans, ransomware, spyware</li>
<li><strong>Phishing:</strong> Social engineering via email/websites</li>
<li><strong>DDoS:</strong> Distributed Denial of Service attacks</li>
<li><strong>Man-in-the-Middle:</strong> Intercepting communications</li>
<li><strong>Zero-Day:</strong> Exploiting unknown vulnerabilities</li>
<li><strong>Insider Threats:</strong> Malicious or negligent employees</li>
</ul>
<h3>Threat Actors</h3>
<ul>
<li><strong>Script Kiddies:</strong> Unskilled attackers using tools</li>
<li><strong>Hacktivists:</strong> Politically motivated</li>
<li><strong>Criminal Organizations:</strong> Financially motivated</li>
<li><strong>Nation States:</strong> APTs (Advanced Persistent Threats)</li>
</ul>',
'https://www.youtube.com/embed/aO858HyFbKI',
'<h3>Analyzing a Phishing Email</h3>
<p>Learn to identify phishing attempts:</p>
<ol>
<li>Check sender email address carefully</li>
<li>Hover over links before clicking</li>
<li>Look for urgency tactics</li>
<li>Check for spelling/grammar errors</li>
<li>Verify through official channels</li>
</ol>',
NULL,
'Analyze 5 real phishing emails (provided) and create a report identifying: attack vectors, red flags, targeted information, and recommendations for users',
'1. Document sender analysis\n2. Identify social engineering tactics\n3. Check URL destinations\n4. Analyze email headers\n5. Create user awareness guide',
1);

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@cyber_mod1, 'Chapter 1.2: Networking Fundamentals for Security', 
'TCP/IP, OSI model, and network protocols',
'<h3>OSI Model Layers</h3>
<ol>
<li><strong>Physical:</strong> Cables, signals, bits</li>
<li><strong>Data Link:</strong> MAC addresses, frames, switches</li>
<li><strong>Network:</strong> IP addresses, routing, packets</li>
<li><strong>Transport:</strong> TCP/UDP, ports, segments</li>
<li><strong>Session:</strong> Session management</li>
<li><strong>Presentation:</strong> Encryption, compression</li>
<li><strong>Application:</strong> HTTP, FTP, SMTP, DNS</li>
</ol>
<h3>Key Protocols</h3>
<ul>
<li><strong>TCP:</strong> Connection-oriented, reliable</li>
<li><strong>UDP:</strong> Connectionless, faster</li>
<li><strong>HTTP/HTTPS:</strong> Web traffic (80/443)</li>
<li><strong>DNS:</strong> Domain resolution (53)</li>
<li><strong>DHCP:</strong> IP assignment</li>
</ul>',
'https://www.youtube.com/embed/vv4y_uOneC0',
'<h3>Using Wireshark for Packet Analysis</h3>',
'-- Common Wireshark display filters
ip.addr == 192.168.1.1          -- Filter by IP
tcp.port == 80                  -- Filter by port
http.request.method == "GET"    -- HTTP GET requests
dns.qry.name contains "google"  -- DNS queries
ssl.handshake.type == 1         -- SSL client hello

-- Capture only HTTP traffic
tcp port 80 or tcp port 443',
'Use Wireshark to capture and analyze your home network traffic for 30 minutes. Identify: devices on network, protocols used, any suspicious traffic, and create a network map',
'1. Install Wireshark\n2. Set up capture filters\n3. Identify all devices by MAC\n4. Map protocol usage\n5. Look for anomalies',
2);

-- ============================================
-- COURSE 3: MACHINE LEARNING
-- Duration: 30 weeks | 25 Modules
-- ============================================

INSERT INTO courses (title, description, category, thumbnail, difficulty_level, estimated_hours, created_by, is_published) VALUES
('Machine Learning Fundamentals', 
'Master ML from scratch. Learn Python, data preprocessing, supervised/unsupervised learning, neural networks, and model deployment. Build real projects: spam detector, image classifier, recommendation system, and price predictor.', 
'Artificial Intelligence', 'ml-course.jpg', 'beginner', 180, 1, 1);

SET @course3 = LAST_INSERT_ID();

INSERT INTO modules (course_id, title, description, sort_order) VALUES
(@course3, 'Module 1: Python for Data Science', 'Python fundamentals with focus on data manipulation', 1);
SET @ml_mod1 = LAST_INSERT_ID();

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@ml_mod1, 'Chapter 1.1: Python Fundamentals & NumPy', 
'Python basics and numerical computing',
'<h3>Why Python for ML?</h3>
<ul>
<li>Simple, readable syntax</li>
<li>Rich ecosystem: NumPy, Pandas, Scikit-learn, TensorFlow</li>
<li>Strong community support</li>
<li>Integration with C/C++ for performance</li>
</ul>
<h3>NumPy Essentials</h3>
<p>NumPy provides efficient array operations:</p>
<ul>
<li><strong>ndarray:</strong> N-dimensional array object</li>
<li><strong>Vectorization:</strong> Operations on entire arrays</li>
<li><strong>Broadcasting:</strong> Operations on different shaped arrays</li>
<li><strong>Universal functions:</strong> Element-wise operations</li>
</ul>',
'https://www.youtube.com/embed/QUT1VHiLmmI',
'<h3>NumPy Array Operations</h3>',
'import numpy as np

# Create arrays
arr = np.array([1, 2, 3, 4, 5])
matrix = np.array([[1, 2, 3], [4, 5, 6], [7, 8, 9]])

# Array properties
print(arr.shape)      # (5,)
print(matrix.shape)   # (3, 3)
print(arr.dtype)      # int64

# Vectorized operations
arr * 2              # [2, 4, 6, 8, 10]
arr + 10             # [11, 12, 13, 14, 15]

# Matrix operations
matrix.T             # Transpose
np.dot(matrix, matrix)  # Matrix multiplication
np.linalg.inv(matrix)   # Inverse (if exists)

# Indexing and slicing
arr[1:4]             # [2, 3, 4]
matrix[0, :]         # First row
matrix[:, 1]         # Second column

# Statistical operations
np.mean(arr)
np.std(arr)
np.max(arr)
np.argmax(arr)       # Index of max value',
'Build a Python script that analyzes a CSV dataset of house prices: calculate statistics, find correlations, identify outliers, and create visualizations using matplotlib',
'1. Load data with pandas\n2. Calculate mean, median, std\n3. Find correlation matrix\n4. Detect outliers using IQR\n5. Create histograms and scatter plots',
1);

-- ============================================
-- COURSE 4: AGENTIC AI & LLMs
-- Duration: 25 weeks | 20 Modules
-- ============================================

INSERT INTO courses (title, description, category, thumbnail, difficulty_level, estimated_hours, created_by, is_published) VALUES
('Agentic AI & LLM Development', 
'Build autonomous AI agents using Large Language Models. Learn prompt engineering, RAG systems, tool use, multi-agent orchestration, and LangChain. Create agents that can research, code, and solve complex tasks autonomously.', 
'Artificial Intelligence', 'agent-course.jpg', 'intermediate', 150, 1, 1);

SET @course4 = LAST_INSERT_ID();

INSERT INTO modules (course_id, title, description, sort_order) VALUES
(@course4, 'Module 1: LLM Fundamentals & Prompt Engineering', 'Understanding how LLMs work and crafting effective prompts', 1);
SET @agent_mod1 = LAST_INSERT_ID();

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@agent_mod1, 'Chapter 1.1: How Large Language Models Work', 
'Transformers, tokenization, and model architecture',
'<h3>What are LLMs?</h3>
<p>Large Language Models are neural networks trained on vast text corpora to predict the next token:</p>
<ul>
<li><strong>Transformer Architecture:</strong> Self-attention mechanism</li>
<li><strong>Tokenization:</strong> Breaking text into tokens (words/subwords)</li>
<li><strong>Context Window:</strong> How much text the model can process</li>
<li><strong>Parameters:</strong> Billions of learned weights</li>
</ul>
<h3>Popular Models</h3>
<ul>
<li><strong>GPT-4/3.5:</strong> OpenAI\'s models (closed source)</li>
<li><strong>Claude:</strong> Anthropic\'s assistant-focused models</li>
<li><strong>Llama 2/3:</strong> Meta\'s open source models</li>
<li><strong>Mistral:</strong> Efficient open source alternative</li>
</ul>
<h3>Key Concepts</h3>
<ul>
<li><strong>Temperature:</strong> Controls randomness (0=deterministic, 1=creative)</li>
<li><strong>Top-p (nucleus sampling):</strong> Controls diversity</li>
<li><strong>Max tokens:</strong> Limits response length</li>
</ul>',
'https://www.youtube.com/embed/LPZh9BOjkQs',
'<h3>Setting Up OpenAI API</h3>',
'import openai

# Set API key
openai.api_key = "your-api-key"

# Basic completion
response = openai.ChatCompletion.create(
    model="gpt-3.5-turbo",
    messages=[
        {"role": "system", "content": "You are a helpful assistant."},
        {"role": "user", "content": "Explain quantum computing in simple terms."}
    ],
    temperature=0.7,
    max_tokens=500
)

print(response.choices[0].message.content)

# Streaming response
for chunk in openai.ChatCompletion.create(
    model="gpt-3.5-turbo",
    messages=[{"role": "user", "content": "Write a poem about AI."}],
    stream=True
):
    if chunk.choices[0].delta.get("content"):
        print(chunk.choices[0].delta.content, end="")',
'Build a Python CLI tool that uses GPT-3.5 to: summarize long articles, answer questions about documents, translate text between languages, and generate code snippets based on descriptions',
'1. Use argparse for CLI\n2. Implement file reading\n3. Create different prompt templates\n4. Handle API errors\n5. Add streaming output',
1);

-- ============================================
-- COURSE 5: DATA STRUCTURES & ALGORITHMS
-- Duration: 30 weeks | 25 Modules
-- ============================================

INSERT INTO courses (title, description, category, thumbnail, difficulty_level, estimated_hours, created_by, is_published) VALUES
('Data Structures & Algorithms in Python', 
'Master DSA for coding interviews. Learn arrays, linked lists, stacks, queues, trees, graphs, sorting, searching, and dynamic programming. Solve 200+ problems with detailed explanations. Prepare for FAANG interviews.', 
'Computer Science', 'dsa-course.jpg', 'beginner', 180, 1, 1);

SET @course5 = LAST_INSERT_ID();

INSERT INTO modules (course_id, title, description, sort_order) VALUES
(@course5, 'Module 1: Arrays & Strings', 'Foundation data structures and manipulation techniques', 1);
SET @dsa_mod1 = LAST_INSERT_ID();

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@dsa_mod1, 'Chapter 1.1: Array Fundamentals', 
'Dynamic arrays, time complexity, and basic operations',
'<h3>Array Characteristics</h3>
<ul>
<li><strong>Contiguous Memory:</strong> Elements stored sequentially</li>
<li><strong>O(1) Access:</strong> Direct index access</li>
<li><strong>O(n) Insert/Delete:</strong> May need to shift elements</li>
</ul>
<h3>Time Complexity</h3>
<table>
<tr><th>Operation</th><th>Static Array</th><th>Dynamic Array</th></tr>
<tr><td>Access</td><td>O(1)</td><td>O(1)</td></tr>
<tr><td>Search</td><td>O(n)</td><td>O(n)</td></tr>
<tr><td>Insert</td><td>O(n)</td><td>O(1) amortized</td></tr>
<tr><td>Delete</td><td>O(n)</td><td>O(n)</td></tr>
</table>
<h3>Python Lists</h3>
<p>Python lists are dynamic arrays with automatic resizing.</p>',
'https://www.youtube.com/embed/B31LgI4Y4Aw',
'<h3>Two Pointer Technique</h3>',
'def two_sum_sorted(arr, target):
    """Find two numbers that add up to target."""
    left, right = 0, len(arr) - 1
    
    while left < right:
        current_sum = arr[left] + arr[right]
        if current_sum == target:
            return [left, right]
        elif current_sum < target:
            left += 1
        else:
            right -= 1
    return None

def max_area(height):
    """Container With Most Water problem."""
    left, right = 0, len(height) - 1
    max_water = 0
    
    while left < right:
        width = right - left
        h = min(height[left], height[right])
        max_water = max(max_water, width * h)
        
        if height[left] < height[right]:
            left += 1
        else:
            right -= 1
    
    return max_water',
'Solve the "3Sum" problem: Given an integer array nums, return all unique triplets [nums[i], nums[j], nums[k]] such that i != j, i != k, j != k, and nums[i] + nums[j] + nums[k] == 0',
'1. Sort the array first\n2. Fix one number, use two pointers for the other two\n3. Skip duplicates to ensure unique triplets\n4. Time: O(n²), Space: O(1) excluding output',
1);

-- ============================================
-- COURSE 6: LANGCHAIN & RAG SYSTEMS
-- Duration: 20 weeks | 18 Modules
-- ============================================

INSERT INTO courses (title, description, category, thumbnail, difficulty_level, estimated_hours, created_by, is_published) VALUES
('LangChain & RAG Systems', 
'Build production-ready AI applications with LangChain. Learn chains, agents, memory, document loaders, vector databases, and Retrieval-Augmented Generation. Create chatbots that understand your documents.', 
'Artificial Intelligence', 'langchain-course.jpg', 'intermediate', 120, 1, 1);

SET @course6 = LAST_INSERT_ID();

INSERT INTO modules (course_id, title, description, sort_order) VALUES
(@course6, 'Module 1: LangChain Fundamentals', 'Core concepts and building blocks of LangChain', 1);
SET @lc_mod1 = LAST_INSERT_ID();

INSERT INTO lessons (module_id, title, description, lecture_content, lecture_video_url, tutorial_content, tutorial_code, workshop_problem, workshop_hints, sort_order) VALUES
(@lc_mod1, 'Chapter 1.1: Introduction to LangChain', 
'What is LangChain and why use it?',
'<h3>LangChain Core Components</h3>
<ul>
<li><strong>Models:</strong> LLMs and Chat Models interfaces</li>
<li><strong>Prompts:</strong> Template and manage prompts</li>
<li><strong>Chains:</strong> Sequence of calls (LLM, tool, data)</li>
<li><strong>Agents:</strong> LLMs that decide actions</li>
<li><strong>Memory:</strong> Persist state between calls</li>
<li><strong>Document Loaders:</strong> Load data from various sources</li>
<li><strong>Vector Stores:</strong> Store and search embeddings</li>
</ul>
<h3>Why LangChain?</h3>
<ul>
<li>Standardized interface for different LLMs</li>
<li>Easy chaining of operations</li>
<li>Built-in memory management</li>
<li>Rich ecosystem of integrations</li>
</ul>',
'https://www.youtube.com/embed/lG7Uxts9SX4',
'<h3>Your First LangChain App</h3>',
'from langchain import OpenAI, PromptTemplate, LLMChain

# Initialize LLM
llm = OpenAI(temperature=0.7)

# Create a prompt template
template = """
You are a helpful coding assistant.
Language: {language}
Task: {task}

Provide clean, well-commented code:
"""

prompt = PromptTemplate(
    input_variables=["language", "task"],
    template=template
)

# Create chain
chain = LLMChain(llm=llm, prompt=prompt)

# Run chain
result = chain.predict(language="Python", task="Read a CSV file and calculate average")
print(result)',
'Build a LangChain application that: loads a PDF document, splits it into chunks, creates embeddings, stores in ChromaDB, and answers questions about the document content',
'1. Use PyPDFLoader for PDF\n2. Use RecursiveCharacterTextSplitter\n3. Use OpenAIEmbeddings\n4. Store in Chroma vector DB\n5. Create retrieval QA chain',
1);

-- ============================================
-- COURSE 7: VIDEO EDITING & MOTION GRAPHICS
-- Duration: 20 weeks | 18 Modules
-- ============================================

INSERT INTO courses (title, description, category, thumbnail, difficulty_level, estimated_hours, created_by, is_published) VALUES
('Video Editing & Motion Graphics', 
'Create professional videos with DaVinci Resolve and After Effects. Learn editing techniques, color grading, motion graphics, visual effects, and audio mixing. Perfect for content creators and aspiring filmmakers.', 
'Digital Creation', 'video-course.jpg', 'beginner', 120, 1, 1);

SET @course7 = LAST_INSERT_ID();

-- ============================================
-- EXTRA RESOURCES TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS extra_resources (
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
-- EXTRA RESOURCES: FULL STACK WEB DEVELOPMENT
-- ============================================

INSERT INTO extra_resources (course_id, title, resource_type, url, description, tags) VALUES
(@course1, 'freeCodeCamp Full Stack Developer Curriculum', 'course', 'https://www.freecodecamp.org/learn/full-stack-developer-v9', 'The most comprehensive free curriculum online: 64 workshops, 513 video lectures, 83 labs, and a free proctored certification exam. Covers HTML, CSS, JavaScript, React, Next.js, Node.js, SQL, Python, and TypeScript.', 'freecodecamp,certification,comprehensive,beginner'),
(@course1, 'The Odin Project', 'course', 'https://www.theodinproject.com', 'Free open-source full stack curriculum. Project-based learning covering HTML, CSS, JavaScript, Git, React, Node.js, and databases. 100% free, community-maintained, and trusted by tens of thousands of self-taught developers.', 'odin,open-source,project-based,free'),
(@course1, 'JavaScript.info — The Modern JavaScript Tutorial', 'documentation', 'https://javascript.info', 'Widely considered the single best JavaScript reference online. Covers the language from fundamentals to advanced topics including closures, async/await, modules, and the DOM. Free, beautifully structured, with exercises throughout.', 'javascript,modern,comprehensive,reference'),
(@course1, 'CSS-Tricks', 'article', 'https://css-tricks.com', 'Daily articles about CSS, HTML, and JavaScript. Home of the definitive Flexbox and CSS Grid guides. Essential reading and bookmarking for any frontend developer at any level.', 'css,frontend,daily,reference'),
(@course1, 'Web.dev by Google', 'documentation', 'https://web.dev', 'Google\'s official guidance for building modern, performant web experiences. Covers Core Web Vitals, accessibility, PWAs, and best practices. Authoritative and constantly updated by the Chrome team.', 'google,performance,best-practices,accessibility'),
(@course1, 'Frontend Masters', 'course', 'https://frontendmasters.com', 'In-depth frontend courses by industry experts including Kyle Simpson (JavaScript), Brian Holt (React), and Scott Moss (Node.js). The highest quality advanced frontend courses available online. Some free content available.', 'advanced,expert,javascript,react'),
(@course1, 'Eloquent JavaScript — Free Book', 'book', 'https://eloquentjavascript.net', 'One of the best programming books ever written, available completely free online. Dives deep into JavaScript — functions, objects, the DOM, async programming, and more. Essential reading for serious JS developers.', 'book,free,javascript,deep-dive'),
(@course1, 'MDN Web Docs', 'documentation', 'https://developer.mozilla.org', 'The bible of web development. Authoritative reference for HTML, CSS, and JavaScript from Mozilla. Every professional web developer has this bookmarked and uses it daily. The single most important resource in web dev.', 'mdn,reference,html,css,javascript');

-- ============================================
-- EXTRA RESOURCES: CYBERSECURITY
-- ============================================

INSERT INTO extra_resources (course_id, title, resource_type, url, description, tags) VALUES
(@course2, 'TryHackMe', 'course', 'https://tryhackme.com', 'Hands-on cybersecurity training with browser-based virtual labs — no setup required. Learn by legally attacking vulnerable machines. Free tier covers Linux fundamentals, network security, web hacking, and privilege escalation. Best beginner platform available.', 'hands-on,ctf,beginner-friendly,labs'),
(@course2, 'Hack The Box', 'course', 'https://www.hackthebox.com', 'Industry-respected penetration testing labs and challenges. Machines range from beginner to insane difficulty. Used by professionals and CTF competitors worldwide. Builds real offensive security skills that employers value.', 'pentesting,ctf,advanced,professional'),
(@course2, 'Cybrary — Free Security Courses', 'course', 'https://www.cybrary.it', 'Free cybersecurity courses and certifications covering Security+, SOC operations, and MITRE ATT&CK framework. Well-structured career learning paths for aspiring blue teamers and security analysts.', 'free,certifications,career,soc'),
(@course2, 'OWASP Top 10', 'documentation', 'https://owasp.org/www-project-top-ten', 'The authoritative reference for the top 10 web application security risks. Required reading for every cybersecurity professional working with web systems. Updated regularly by the Open Web Application Security Project.', 'owasp,web-security,essential,reference'),
(@course2, 'SANS Cyber Aces — Free Training', 'course', 'https://www.sans.org/cyberaces', 'Free introductory cybersecurity course from SANS — the most respected name in security training. Covers OS internals, networking, and systems security. The gold standard institution offering beginner content at zero cost.', 'sans,free,introductory,foundations'),
(@course2, 'PortSwigger Web Security Academy', 'course', 'https://portswigger.net/web-security', 'Free web security training from the makers of Burp Suite. Covers SQLi, XSS, CSRF, SSRF, XXE, IDOR, OAuth flaws, and more with interactive labs. Arguably the single best free web security resource on the internet.', 'web-security,free,practical,burpsuite'),
(@course2, 'Blue Team Labs Online', 'course', 'https://blueteamlabs.online', 'Defensive security training platform focused on SOC operations, incident response, log analysis, and threat hunting. Builds skills for the defensive side of cybersecurity — the side most companies actually hire for.', 'blue-team,defensive,soc,incident-response'),
(@course2, 'PentesterLab', 'course', 'https://pentesterlab.com', 'Learn web penetration testing through structured exercises covering specific vulnerability classes. Progressive challenges from beginner to expert with guided exploitation walkthroughs and detailed writeups.', 'web-pentesting,beginner,exercises,structured');

-- ============================================
-- EXTRA RESOURCES: MACHINE LEARNING
-- ============================================

INSERT INTO extra_resources (course_id, title, resource_type, url, description, tags) VALUES
(@course3, 'fast.ai — Practical Deep Learning for Coders', 'course', 'https://course.fast.ai', 'Jeremy Howard\'s radical top-down approach to deep learning — start with working AI systems, then understand the theory. Uses PyTorch. Created by the former Kaggle #1 ranked competitor. Completely free, no sign-up required.', 'fastai,practical,top-down,pytorch'),
(@course3, 'Andrew Ng — Machine Learning Specialization', 'course', 'https://www.coursera.org/specializations/machine-learning-introduction', 'The classic ML course taken by over 7 million people. Stanford\'s 3-course foundational curriculum covering supervised learning, unsupervised learning, and reinforcement learning using Python, NumPy, and scikit-learn. Free to audit.', 'andrew-ng,coursera,stanford,classic'),
(@course3, 'Kaggle Learn — ML Micro-Courses', 'course', 'https://www.kaggle.com/learn', 'Free bite-sized micro-courses with live Jupyter notebooks: Intro to ML, Intermediate ML, Feature Engineering, Computer Vision, and NLP. Earn free verified certificates. Real competition datasets to practice on from day one.', 'kaggle,practical,competitions,certificates'),
(@course3, '3Blue1Brown — Neural Networks from Scratch', 'video', 'https://www.youtube.com/playlist?list=PLZHQObOWTQDNU6R1_67000Dx_ZCJB-3pi', 'Grant Sanderson\'s stunning visual explanations of neural networks. Makes backpropagation, gradient descent, and transformer architecture feel genuinely intuitive. The best mathematical intuition-building resource in ML. Essential viewing.', 'visual,math,intuitive,3blue1brown'),
(@course3, 'Papers With Code', 'documentation', 'https://paperswithcode.com', 'ML papers with code implementations and state-of-the-art leaderboards. The best place to track what is actually working in machine learning research right now, with reproducible code you can run immediately.', 'research,papers,code,state-of-the-art'),
(@course3, 'Distill.pub', 'article', 'https://distill.pub', 'Clear, interactive visual explanations of ML research. Distill articles are the gold standard for making complex ideas genuinely understandable. A masterclass in both ML content and technical communication.', 'research,visual,interactive,explanations'),
(@course3, 'Scikit-learn Official Documentation', 'documentation', 'https://scikit-learn.org/stable', 'The official docs, user guides, and API reference for the most widely used ML library in Python. Learn the tools professionals actually use in production. Includes tutorials, examples, and mathematical explanations of every algorithm.', 'scikit-learn,official,python,reference'),
(@course3, 'ML From Scratch — GitHub', 'github', 'https://github.com/eriklindernoren/MLFromScratch', 'Pure Python implementations of ML algorithms from scratch — linear regression, neural networks, decision trees, SVMs, k-means, and more. Nothing builds genuine understanding like building algorithms with no libraries.', 'from-scratch,github,python,algorithms');

-- ============================================
-- EXTRA RESOURCES: AGENTIC AI & LLM DEVELOPMENT
-- ============================================

INSERT INTO extra_resources (course_id, title, resource_type, url, description, tags) VALUES
(@course4, 'OpenAI Cookbook', 'documentation', 'https://cookbook.openai.com', 'Official OpenAI guide with examples for function calling, embeddings, fine-tuning, vision, assistants, and production best practices. The primary reference from the team that built GPT-4. Updated constantly with new patterns.', 'openai,examples,best-practices,official'),
(@course4, 'LangChain Documentation', 'documentation', 'https://python.langchain.com', 'Official LangChain docs for building LLM-powered applications — chains, agents, memory, document loaders, and vector store integrations. The definitive reference for the most widely used LLM application framework.', 'langchain,official,agents,chains'),
(@course4, 'Prompt Engineering Guide', 'documentation', 'https://www.promptingguide.ai', 'The most comprehensive prompt engineering reference online. Covers zero-shot, few-shot, chain-of-thought, ReAct, self-consistency, and advanced techniques. Essential study material before building any serious LLM application.', 'prompts,engineering,zero-shot,chain-of-thought'),
(@course4, 'LlamaIndex Documentation', 'documentation', 'https://docs.llamaindex.ai', 'Data framework for LLM applications — connect LLMs to your data with advanced indexing, retrieval, and agentic tools. Essential for building production-grade RAG and multi-step reasoning systems over large document collections.', 'llamaindex,rag,indexing,data'),
(@course4, 'AutoGPT — GitHub', 'github', 'https://github.com/Significant-Gravitas/AutoGPT', 'One of the first demonstrations of autonomous AI agents completing multi-step tasks without human input. Study the pioneering architecture that launched the agentic AI movement. Invaluable for understanding how agents are structured.', 'autogpt,autonomous,agents,architecture'),
(@course4, 'Hugging Face Transformers', 'documentation', 'https://huggingface.co/docs/transformers', 'State-of-the-art ML models for PyTorch, TensorFlow, and JAX. Access thousands of pretrained models for NLP, vision, audio, and multimodal tasks. The gateway to open-source AI — use Llama, Mistral, and Phi without API costs.', 'huggingface,open-source,models,transformers'),
(@course4, 'Anthropic Claude Prompt Engineering Guide', 'documentation', 'https://docs.anthropic.com/claude/docs/prompt-engineering', 'Official Anthropic prompt engineering guide for Claude. Covers role prompting, XML structuring, chain-of-thought, and techniques specific to frontier models. Invaluable for anyone building applications on top of Claude or similar models.', 'anthropic,claude,prompts,official'),
(@course4, 'Latent Space — AI Engineering Newsletter', 'article', 'https://www.latent.space', 'The definitive weekly newsletter for AI engineers covering the latest in LLMs, agentic systems, and production AI. Deep technical interviews with the engineers actually building frontier AI. Essential for staying at the cutting edge.', 'newsletter,weekly,engineering,llms');

-- ============================================
-- EXTRA RESOURCES: DATA STRUCTURES & ALGORITHMS
-- ============================================

INSERT INTO extra_resources (course_id, title, resource_type, url, description, tags) VALUES
(@course5, 'LeetCode', 'course', 'https://leetcode.com', 'The #1 platform for coding interview preparation — 2,000+ problems across all topics and difficulty levels, with editorial solutions and discussion boards. Every FAANG interview involves problems sourced from here. Free tier: 1,500+ problems.', 'practice,interview,problems,faang'),
(@course5, 'NeetCode Roadmap', 'article', 'https://neetcode.io/roadmap', 'The most efficient structured DSA study plan available online. Free roadmap covering every essential topic with curated LeetCode problems and YouTube video explanations for each one. The fastest path from beginner to FAANG-ready.', 'roadmap,structured,free,interview'),
(@course5, 'Visualgo — Algorithm Visualizations', 'documentation', 'https://visualgo.net', 'Step-by-step animations of data structures and algorithms — sorting, graph traversals, tree operations, heap, and more. The best tool for building intuitive understanding of how algorithms actually work at a mechanical level.', 'visual,interactive,animations,intuition'),
(@course5, 'MIT 6.006 — Introduction to Algorithms', 'video', 'https://www.youtube.com/playlist?list=PLUl4u3cNGP61Oq3tWYp6V_F-5jb5L2iHb', 'MIT\'s full algorithms lecture series on YouTube — one of the most rigorous free algorithm courses in existence. Covers the mathematical foundations behind every major algorithm and data structure. University-quality content at zero cost.', 'mit,academic,rigorous,theory'),
(@course5, 'Grokking Algorithms — Illustrated Book', 'book', 'https://www.manning.com/books/grokking-algorithms', 'The most accessible DSA book ever written. Every concept is explained with diagrams and illustrations. The perfect first book before tackling more rigorous material. Covers arrays, linked lists, recursion, dynamic programming, and graphs.', 'book,illustrated,beginner,accessible'),
(@course5, 'AlgoExpert', 'course', 'https://www.algoexpert.io', 'Video explanations of coding problems with clear walkthroughs for every solution. Structured by topic with data structure crash courses included. The highest quality interview prep platform — the premium alternative to LeetCode.', 'video,explanations,interview,structured'),
(@course5, 'Big-O Cheat Sheet', 'article', 'https://www.bigocheatsheet.com', 'Time and space complexity reference for every major data structure and sorting algorithm. Bookmark this immediately. You will consult it constantly during DSA studies and every technical interview you ever take.', 'reference,complexity,big-o,cheat-sheet'),
(@course5, 'Princeton Algorithms — Robert Sedgewick', 'course', 'https://www.coursera.org/learn/algorithms-part1', 'Algorithms by Robert Sedgewick, author of the classic Algorithms textbook used in CS programs worldwide. Covers the same content as a Princeton CS degree. One of the most academically respected algorithm courses available online.', 'princeton,coursera,academic,sedgewick');

-- ============================================
-- EXTRA RESOURCES: LANGCHAIN & RAG SYSTEMS
-- ============================================

INSERT INTO extra_resources (course_id, title, resource_type, url, description, tags) VALUES
(@course6, 'LangChain Academy', 'course', 'https://academy.langchain.com', 'Official LangChain courses covering LangGraph fundamentals, building stateful agents, and advanced retrieval patterns. Taught by the team that builds and maintains LangChain — the most authoritative and up-to-date source possible.', 'langchain,official,langgraph,agents'),
(@course6, 'Pinecone Learning Center', 'documentation', 'https://www.pinecone.io/learn', 'Vector search and RAG guides from the makers of Pinecone. Covers embeddings, semantic search, hybrid search, chunking strategies, and building complete RAG pipelines from scratch. The best practical RAG reference available online.', 'pinecone,vectors,semantic-search,rag'),
(@course6, 'Chroma Documentation', 'documentation', 'https://docs.trychroma.com', 'Official docs for Chroma — the AI-native open-source embedding database. Runs locally with no cloud account needed. The easiest way to add vector search to Python projects. Perfect for learning RAG without incurring cloud storage costs.', 'chroma,embeddings,open-source,local'),
(@course6, 'LangGraph Documentation', 'documentation', 'https://langchain-ai.github.io/langgraph', 'Official documentation for LangGraph — the framework for building stateful agent workflows with cycles, branching, and persistent memory. The essential next step beyond basic LangChain for production-grade agentic RAG systems.', 'langgraph,agents,stateful,workflows'),
(@course6, 'Building Retrieval Agents with LangChain', 'article', 'https://blog.langchain.dev/retrieval-agents', 'Official LangChain blog tutorial on building retrieval agents — combining tool use with RAG for dynamic multi-step document analysis. A practical, code-first guide straight from the team that built the framework.', 'rag,agents,tutorial,retrieval'),
(@course6, 'Vector Databases Comparison Guide', 'article', 'https://thedataquarry.com/posts/vector-db-1', 'In-depth comparison of vector database options: Pinecone, Weaviate, Qdrant, Chroma, Milvus, and pgvector. Performance benchmarks and feature analysis to help you select the right vector store for your specific RAG use case.', 'comparison,vector-db,benchmarks,guide'),
(@course6, 'OpenAI Embeddings Guide', 'documentation', 'https://platform.openai.com/docs/guides/embeddings', 'Official OpenAI guide to working with text embeddings — how they work, chunking best practices, batch processing, and practical examples for semantic search, classification, and clustering. The primary embeddings reference.', 'openai,embeddings,semantic-search,official'),
(@course6, 'Hugging Face × LangChain Integration', 'documentation', 'https://python.langchain.com/docs/integrations/platforms/huggingface', 'Official guide to using open-source Hugging Face models with LangChain. Run Llama 3, Mistral, Phi-3, and thousands of community models locally or via Inference API. Build complete RAG systems without paying for proprietary model APIs.', 'huggingface,open-source,integration,local');

-- ============================================
-- EXTRA RESOURCES: VIDEO EDITING & MOTION GRAPHICS
-- ============================================

INSERT INTO extra_resources (course_id, title, resource_type, url, description, tags) VALUES
(@course7, 'DaVinci Resolve — Official Free Training', 'course', 'https://www.blackmagicdesign.com/products/davinciresolve/training', 'Official free training from Blackmagic Design for DaVinci Resolve — the industry\'s most powerful free professional editor. Covers editing, color grading, Fusion VFX, Fairlight audio, and collaboration. Both the software AND training are 100% free.', 'davinci,official,free,color-grading'),
(@course7, 'Film Riot — YouTube Channel', 'video', 'https://www.youtube.com/user/filmriot', 'Filmmaking and VFX tutorials covering practical effects, editing tricks, color grading, and storytelling for indie filmmakers. Hundreds of free tutorials from one of YouTube\'s most entertaining and educational filmmaking channels.', 'filmriot,youtube,vfx,filmmaking'),
(@course7, 'Peter McKinnon — YouTube Channel', 'video', 'https://www.youtube.com/user/petermckinnon24', 'Photography and filmmaking tips from one of YouTube\'s most creative educators. Covers camera techniques, LUT creation, editing workflows, and the full creative process behind compelling, professional-looking video content.', 'peter-mckinnon,creative,filmmaking,editing'),
(@course7, 'Video Copilot — After Effects Tutorials', 'course', 'https://www.videocopilot.net/tutorials', 'After Effects tutorials from Andrew Kramer — the engineer behind many Hollywood VFX tools including Element 3D. Covers motion graphics, 3D compositing, particle effects, and cinematic title sequences. All tutorials are free.', 'after-effects,vfx,motion-graphics,hollywood'),
(@course7, 'Mixkit — Free Video Assets & Templates', 'article', 'https://mixkit.co', 'Free stock footage, sound effects, music tracks, and After Effects templates — all royalty-free for commercial use. A goldmine of professional-quality assets for video editors and motion graphic designers. No subscription required.', 'free,assets,stock-footage,templates'),
(@course7, 'Pexels — Free Stock Video Footage', 'article', 'https://www.pexels.com/videos', 'Free HD and 4K stock video footage with no attribution required for most content. Use for B-roll, backgrounds, and supplemental footage. One of the largest free stock video libraries online, updated daily with new content.', 'stock-footage,free,4k,b-roll'),
(@course7, 'Color Grading Central', 'course', 'https://www.colorgradingcentral.com', 'Professional color grading tutorials and LUTs for DaVinci Resolve, Premiere Pro, and Final Cut Pro. Learn to give your footage a cinematic, broadcast-quality look using professional color science and grading workflows.', 'color-grading,luts,cinematic,davinci'),
(@course7, 'Adobe Premiere Pro — Official Tutorials', 'documentation', 'https://helpx.adobe.com/premiere-pro/tutorials.html', 'Official Adobe Premiere Pro tutorials covering basic cuts, transitions, color correction, audio mixing, multicam editing, and export settings. Direct from the team that builds the software — always current with the latest version.', 'premiere,adobe,official,editing');

-- ============================================
-- NOTICES/ANNOUNCEMENTS TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS notices (
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

CREATE TABLE IF NOT EXISTS notice_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notice_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notice_id) REFERENCES notices(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample notices
INSERT INTO notices (title, content, priority, is_pinned, created_by) VALUES
('Welcome to TechLearn Platform!', 
'Welcome to our new learning platform! We\'re excited to have you here. Explore our courses, join the community, and start your learning journey. If you have any questions, feel free to ask in the community forum.', 
'high', 1, 1);

INSERT INTO notices (title, content, priority, created_by) VALUES
('New Course: Agentic AI & LLM Development', 
'We\'re thrilled to announce our newest course on Agentic AI! Learn to build autonomous AI agents using LangChain, OpenAI API, and vector databases. This course is perfect for developers looking to leverage AI in their applications.', 
'medium', 1);

INSERT INTO notices (title, content, priority, created_by) VALUES
('Community Challenge: Build a Portfolio Project', 
'Join our monthly challenge! Build a portfolio project using what you\'ve learned and share it with the community. The best projects will be featured on our homepage. Deadline: End of this month.', 
'medium', 1);

-- ============================================
-- CHATBOT CONVERSATIONS TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS chatbot_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(64) NOT NULL,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
-- CURRICULUM TRACKER TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS curriculum_progress (
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