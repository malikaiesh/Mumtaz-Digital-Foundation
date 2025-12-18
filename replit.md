# Mumtaz Digital Foundation - LMS Platform

## Overview
A comprehensive Learning Management System (LMS) built with PHP and PostgreSQL. This platform provides digital skills training with features for course management, student enrollment, payment processing, certifications, and admin controls.

## Project Structure
```
/
├── admin/                 # Admin panel pages
│   ├── index.php         # Admin dashboard
│   ├── courses.php       # Course management
│   ├── students.php      # Student management
│   ├── payments.php      # Payment management
│   ├── enrollments.php   # Enrollment tracking
│   └── categories.php    # Category management
├── assets/
│   ├── css/style.css     # Main stylesheet
│   └── js/main.js        # Frontend JavaScript
├── config/
│   ├── database.php      # Database connection
│   ├── init.php          # Application initialization
│   └── schema.sql        # Database schema
├── includes/
│   ├── header.php        # Site header
│   └── footer.php        # Site footer
├── pages/
│   ├── api/              # API endpoints
│   ├── login.php         # User login
│   ├── register.php      # User registration
│   ├── courses.php       # Course listing
│   ├── course-detail.php # Course details
│   ├── dashboard.php     # Student dashboard
│   ├── my-courses.php    # Enrolled courses
│   ├── learn.php         # Learning interface
│   ├── enroll.php        # Course enrollment
│   ├── certificates.php  # User certificates
│   └── profile.php       # User profile
├── uploads/              # User uploads
├── index.php             # Homepage
└── setup.php             # Database initialization
```

## Key Features

### Frontend (Student Side)
- Professional homepage with hero section
- Course browsing with filters (category, level, price)
- Course detail pages with curriculum
- Student registration and login
- Student dashboard with progress tracking
- Video lessons (YouTube/Vimeo embedded)
- Certificate generation upon course completion
- Profile management

### Admin Panel
- Dashboard with statistics
- Course management (add, edit, delete, publish)
- Student management (view, block, approve)
- Enrollment tracking
- Payment approval system
- Category management

### Payment System
- Free and paid courses
- Manual payment methods (JazzCash, EasyPaisa, Bank Transfer)
- Payment verification by admin

## Database
PostgreSQL database with tables for:
- users, categories, courses, modules, lessons
- enrollments, lesson_progress, certificates
- payments, quizzes, quiz_questions, quiz_attempts
- testimonials, blog_posts, notifications

## Default Credentials
- **Admin Login:**
  - Email: admin@mumtazdigital.com
  - Password: admin123

## Technologies
- Backend: PHP 8.3
- Database: PostgreSQL
- Frontend: HTML5, CSS3, JavaScript, Bootstrap 5
- Icons: Font Awesome 6

## Running the Project
The server runs on port 5000 using PHP's built-in server.

## Recent Changes
- Initial setup of LMS platform
- Created database schema with all required tables
- Built homepage, course pages, and student dashboard
- Implemented admin panel with management features
- Added payment approval system
- Set up certificate generation

## User Preferences
- Currency: PKR (Pakistani Rupee)
- Language: English (with Urdu support planned)
