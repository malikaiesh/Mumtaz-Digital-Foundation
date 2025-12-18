-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255) DEFAULT 'default.png',
    role VARCHAR(20) DEFAULT 'student',
    bio TEXT,
    skills TEXT,
    portfolio_url VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses Table
CREATE TABLE IF NOT EXISTS courses (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    thumbnail VARCHAR(255),
    category_id INTEGER REFERENCES categories(id),
    instructor_id INTEGER REFERENCES users(id),
    price DECIMAL(10,2) DEFAULT 0,
    discount_price DECIMAL(10,2),
    level VARCHAR(20) DEFAULT 'beginner',
    duration VARCHAR(50),
    language VARCHAR(50) DEFAULT 'English',
    requirements TEXT,
    outcomes TEXT,
    skills TEXT,
    is_featured BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT FALSE,
    total_lessons INTEGER DEFAULT 0,
    total_students INTEGER DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Modules Table
CREATE TABLE IF NOT EXISTS modules (
    id SERIAL PRIMARY KEY,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Lessons Table
CREATE TABLE IF NOT EXISTS lessons (
    id SERIAL PRIMARY KEY,
    module_id INTEGER REFERENCES modules(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    video_url VARCHAR(500),
    video_type VARCHAR(20) DEFAULT 'youtube',
    duration INTEGER DEFAULT 0,
    attachments TEXT,
    is_free BOOLEAN DEFAULT FALSE,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Enrollments Table
CREATE TABLE IF NOT EXISTS enrollments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    payment_status VARCHAR(20) DEFAULT 'pending',
    payment_method VARCHAR(50),
    amount_paid DECIMAL(10,2) DEFAULT 0,
    transaction_id VARCHAR(100),
    progress INTEGER DEFAULT 0,
    completed_lessons TEXT DEFAULT '[]',
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP,
    UNIQUE(user_id, course_id)
);

-- Lesson Progress Table
CREATE TABLE IF NOT EXISTS lesson_progress (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    lesson_id INTEGER REFERENCES lessons(id) ON DELETE CASCADE,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP,
    UNIQUE(user_id, lesson_id)
);

-- Quizzes Table
CREATE TABLE IF NOT EXISTS quizzes (
    id SERIAL PRIMARY KEY,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    time_limit INTEGER DEFAULT 30,
    passing_score INTEGER DEFAULT 60,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quiz Questions Table
CREATE TABLE IF NOT EXISTS quiz_questions (
    id SERIAL PRIMARY KEY,
    quiz_id INTEGER REFERENCES quizzes(id) ON DELETE CASCADE,
    question TEXT NOT NULL,
    option_a VARCHAR(500),
    option_b VARCHAR(500),
    option_c VARCHAR(500),
    option_d VARCHAR(500),
    correct_answer CHAR(1) NOT NULL,
    points INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0
);

-- Quiz Attempts Table
CREATE TABLE IF NOT EXISTS quiz_attempts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    quiz_id INTEGER REFERENCES quizzes(id) ON DELETE CASCADE,
    score INTEGER DEFAULT 0,
    total_questions INTEGER DEFAULT 0,
    passed BOOLEAN DEFAULT FALSE,
    answers TEXT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP
);

-- Certificates Table
CREATE TABLE IF NOT EXISTS certificates (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    certificate_id VARCHAR(50) UNIQUE NOT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, course_id)
);

-- Assignments Table
CREATE TABLE IF NOT EXISTS assignments (
    id SERIAL PRIMARY KEY,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date TIMESTAMP,
    max_score INTEGER DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Assignment Submissions Table
CREATE TABLE IF NOT EXISTS assignment_submissions (
    id SERIAL PRIMARY KEY,
    assignment_id INTEGER REFERENCES assignments(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    submission_text TEXT,
    file_path VARCHAR(255),
    link_url VARCHAR(500),
    score INTEGER,
    feedback TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, course_id)
);

-- Blog Posts Table
CREATE TABLE IF NOT EXISTS blog_posts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT,
    excerpt TEXT,
    thumbnail VARCHAR(255),
    author_id INTEGER REFERENCES users(id),
    category VARCHAR(100),
    tags TEXT,
    meta_title VARCHAR(255),
    meta_description TEXT,
    is_published BOOLEAN DEFAULT FALSE,
    views INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Testimonials Table
CREATE TABLE IF NOT EXISTS testimonials (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    company VARCHAR(100),
    content TEXT NOT NULL,
    avatar VARCHAR(255),
    rating INTEGER DEFAULT 5,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Coupons Table
CREATE TABLE IF NOT EXISTS coupons (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type VARCHAR(20) DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    min_purchase DECIMAL(10,2) DEFAULT 0,
    max_uses INTEGER DEFAULT NULL,
    used_count INTEGER DEFAULT 0,
    valid_from TIMESTAMP,
    valid_until TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    enrollment_id INTEGER REFERENCES enrollments(id) ON DELETE CASCADE,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    status VARCHAR(20) DEFAULT 'pending',
    payment_proof VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP
);

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type VARCHAR(50) DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Activity Logs Table
CREATE TABLE IF NOT EXISTS activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT INTO categories (name, slug, description, icon) VALUES
('Website Development', 'website-development', 'Learn to build modern websites from scratch', 'fa-code'),
('WordPress Development', 'wordpress-development', 'Master WordPress theme and plugin development', 'fa-wordpress'),
('SEO', 'seo', 'Complete search engine optimization course', 'fa-search'),
('Digital Marketing', 'digital-marketing', 'Learn digital marketing strategies', 'fa-bullhorn'),
('Graphic Design', 'graphic-design', 'Master graphic design tools and techniques', 'fa-paint-brush')
ON CONFLICT (slug) DO NOTHING;

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role, is_verified, is_active) VALUES
('Admin', 'admin@mumtazdigital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', TRUE, TRUE)
ON CONFLICT (email) DO NOTHING;

-- Insert sample instructor
INSERT INTO users (name, email, password, role, bio, is_verified, is_active) VALUES
('Muhammad Ali', 'instructor@mumtazdigital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor', 'Senior Web Developer with 10+ years of experience in teaching and development.', TRUE, TRUE)
ON CONFLICT (email) DO NOTHING;

-- Insert sample courses
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, price, level, duration, is_featured, is_published, total_lessons, outcomes, skills) VALUES
('Complete Website Development Bootcamp', 'complete-website-development-bootcamp', 
'Master web development from scratch. Learn HTML, CSS, JavaScript, and modern frameworks to build professional websites.',
'Learn to build modern, responsive websites from scratch with HTML, CSS, JavaScript and more.',
1, 2, 15000, 'beginner', '12 weeks', TRUE, TRUE, 45,
'Build professional websites from scratch|Master HTML5, CSS3, and JavaScript|Create responsive mobile-first designs|Deploy websites to production',
'HTML5|CSS3|JavaScript|Bootstrap|Git'),

('WordPress Mastery Course', 'wordpress-mastery-course',
'Learn WordPress from beginner to advanced. Create themes, plugins, and e-commerce sites.',
'Become a WordPress expert and build any type of website without coding.',
2, 2, 12000, 'intermediate', '8 weeks', TRUE, TRUE, 35,
'Create custom WordPress themes|Develop WordPress plugins|Build e-commerce with WooCommerce|Master WordPress SEO',
'WordPress|PHP|Theme Development|WooCommerce'),

('Complete SEO Training', 'complete-seo-training',
'Master search engine optimization. Learn on-page, off-page, and technical SEO to rank websites.',
'Rank any website on Google with proven SEO strategies and techniques.',
3, 2, 10000, 'beginner', '6 weeks', TRUE, TRUE, 28,
'Rank websites on first page of Google|Master keyword research|Build quality backlinks|Technical SEO audits',
'On-Page SEO|Off-Page SEO|Technical SEO|Keyword Research'),

('Digital Marketing Professional', 'digital-marketing-professional',
'Complete digital marketing course covering social media, content marketing, email marketing, and paid advertising.',
'Master all aspects of digital marketing to grow any business online.',
4, 2, 18000, 'beginner', '10 weeks', TRUE, TRUE, 42,
'Run successful ad campaigns|Build social media presence|Email marketing automation|Content marketing strategies',
'Facebook Ads|Google Ads|Social Media|Email Marketing|Content Marketing')
ON CONFLICT (slug) DO NOTHING;

-- Insert sample modules and lessons for first course
INSERT INTO modules (course_id, title, description, sort_order) VALUES
(1, 'Introduction to Web Development', 'Get started with web development basics', 1),
(1, 'HTML Fundamentals', 'Learn HTML structure and elements', 2),
(1, 'CSS Styling', 'Master CSS for beautiful designs', 3),
(1, 'JavaScript Basics', 'Add interactivity with JavaScript', 4)
ON CONFLICT DO NOTHING;

INSERT INTO lessons (module_id, title, content, video_url, duration, is_free, sort_order) VALUES
(1, 'Welcome to the Course', 'Welcome to the Complete Website Development Bootcamp!', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 10, TRUE, 1),
(1, 'Setting Up Your Environment', 'Learn to set up VS Code and essential tools', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 15, TRUE, 2),
(2, 'HTML Document Structure', 'Understanding the basic structure of HTML documents', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 20, FALSE, 1),
(2, 'HTML Elements and Tags', 'Learn about common HTML elements', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 25, FALSE, 2),
(3, 'Introduction to CSS', 'Getting started with CSS styling', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 20, FALSE, 1),
(3, 'CSS Selectors', 'Master different types of CSS selectors', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 25, FALSE, 2),
(4, 'JavaScript Fundamentals', 'Learn JavaScript basics', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 30, FALSE, 1)
ON CONFLICT DO NOTHING;

-- Insert sample testimonials
INSERT INTO testimonials (name, role, company, content, rating, is_featured) VALUES
('Ahmed Khan', 'Web Developer', 'Freelancer', 'This platform transformed my career. The courses are well-structured and practical. I landed my first freelance client within 2 months of completing the web development course!', 5, TRUE),
('Sara Malik', 'Digital Marketer', 'Tech Solutions', 'The Digital Marketing course was exactly what I needed. Now I manage campaigns for multiple clients. Highly recommend Mumtaz Digital Foundation!', 5, TRUE),
('Usman Ali', 'WordPress Developer', 'Self-Employed', 'As someone from a non-technical background, I was able to learn WordPress development easily. The step-by-step approach made everything clear.', 5, TRUE)
ON CONFLICT DO NOTHING;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Mumtaz Digital Foundation'),
('site_tagline', 'Empowering Digital Skills for the Future'),
('contact_email', 'info@mumtazdigital.com'),
('contact_phone', '+92 300 1234567'),
('address', 'Lahore, Pakistan'),
('facebook_url', 'https://facebook.com/mumtazdigital'),
('twitter_url', 'https://twitter.com/mumtazdigital'),
('youtube_url', 'https://youtube.com/mumtazdigital'),
('linkedin_url', 'https://linkedin.com/company/mumtazdigital')
ON CONFLICT (setting_key) DO NOTHING;
