-- LearnHub LMS Database Schema
-- Run this file to create the database and tables

CREATE DATABASE IF NOT EXISTS learnhub;
USE learnhub;

-- Users Table (Students and Teachers)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
    student_id VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses Table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    course_code VARCHAR(20) NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Course Enrollments
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);

-- Assignments Table
CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    due_date DATETIME NOT NULL,
    total_points INT DEFAULT 100,
    file_name VARCHAR(255) NULL,
    file_path VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Assignment Submissions
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    file_name VARCHAR(255),
    file_path VARCHAR(500),
    comment TEXT,
    grade INT NULL,
    feedback TEXT NULL,
    graded_at DATETIME NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Quizzes Table
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    time_limit INT DEFAULT 30,
    total_points INT DEFAULT 100,
    due_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Quiz Questions
CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_answer CHAR(1) NOT NULL,
    points INT DEFAULT 10,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Quiz Attempts
CREATE TABLE quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    score INT DEFAULT 0,
    total_points INT DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Quiz Answers
CREATE TABLE quiz_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_answer CHAR(1),
    is_correct BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

-- Course Materials/Files
CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Announcements
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Insert Demo Data
-- Password is 'password' hashed with password_hash()
INSERT INTO users (username, email, password, full_name, role, student_id) VALUES
('admin', 'admin@learnhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin', NULL),
('teacher1', 'teacher@learnhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Sarah Johnson', 'teacher', NULL),
('student1', 'student@learnhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Student', 'student', 'STU001'),
('student2', 'maria@learnhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Lopez', 'student', 'STU002');

-- Demo Courses
INSERT INTO courses (teacher_id, course_code, course_name, description) VALUES
(2, 'CS301', 'Database Systems', 'Learn about database design, SQL, and database management systems.'),
(2, 'WD201', 'Web Development', 'Full stack web development with HTML, CSS, JavaScript, and PHP.');

-- Enroll students in courses
INSERT INTO enrollments (student_id, course_id) VALUES
(3, 1), (3, 2), (4, 1), (4, 2);

-- Demo Assignment
INSERT INTO assignments (course_id, title, description, due_date, total_points) VALUES
(1, 'Database Design Project', 'Design a complete database schema for a library management system. Include ER diagram, normalized tables, and SQL statements.', DATE_ADD(NOW(), INTERVAL 7 DAY), 100),
(1, 'SQL Practice', 'Complete the SQL exercises on the provided sample database.', DATE_ADD(NOW(), INTERVAL 14 DAY), 50);

-- Demo Quiz
INSERT INTO quizzes (course_id, title, description, time_limit, total_points, due_date) VALUES
(1, 'SQL Basics Quiz', 'Test your knowledge of basic SQL commands and syntax.', 20, 50, DATE_ADD(NOW(), INTERVAL 7 DAY));

-- Demo Quiz Questions
INSERT INTO quiz_questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer, points) VALUES
(1, 'Which SQL statement is used to retrieve data from a database?', 'GET', 'SELECT', 'RETRIEVE', 'FETCH', 'B', 10),
(1, 'Which SQL clause is used to filter records?', 'WHERE', 'FILTER', 'HAVING', 'LIMIT', 'A', 10),
(1, 'Which SQL statement is used to insert new data?', 'ADD', 'INSERT INTO', 'UPDATE', 'CREATE', 'B', 10),
(1, 'What does SQL stand for?', 'Strong Question Language', 'Structured Query Language', 'Simple Query Language', 'Standard Query Language', 'B', 10),
(1, 'Which operator is used to search for a pattern?', 'SEARCH', 'FIND', 'LIKE', 'MATCH', 'C', 10);

-- Demo Announcement
INSERT INTO announcements (course_id, title, content) VALUES
(1, 'Welcome to Database Systems', 'Welcome to CS301! Please review the syllabus and complete the first assignment by the due date.'),
(1, 'Office Hours', 'Office hours are Tuesday and Thursday 2-4 PM in Room 305.');