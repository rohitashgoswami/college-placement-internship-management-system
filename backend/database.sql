-- Create database
CREATE DATABASE college_placement_system;

-- Use database
USE college_placement_system;

-- =========================
-- Admin Table
-- =========================
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Default admin account for fresh local setups
-- Username: admin
-- Password: admin123
INSERT INTO admin (username, password)
VALUES ('admin', 'admin123');

-- =========================
-- Student Table
-- =========================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    roll_number VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    location VARCHAR(100) NULL,
    cgpa DECIMAL(4,2) NULL,
    skills TEXT NULL,
    bio TEXT NULL,
    department VARCHAR(100) NULL,
    graduation_year VARCHAR(10) NULL,
    resume VARCHAR(255) NULL,
    linkedin_url VARCHAR(255) NULL,
    github_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================
-- Internship / Job Table
-- =========================
CREATE TABLE internships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    title VARCHAR(100) NOT NULL,
    type VARCHAR(20) NOT NULL, -- Internship / Placement
    location VARCHAR(100),
    eligibility VARCHAR(200),
    description TEXT,
    last_date DATE
);

-- =========================
-- Applications Table
-- =========================
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    internship_id INT,
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (internship_id) REFERENCES internships(id)
);

-- =========================
-- Saved Jobs Table
-- =========================
CREATE TABLE saved_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    internship_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_saved_job (student_id, internship_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
);

-- =========================
-- Interview Rounds Table
-- =========================
CREATE TABLE interview_rounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    round_number INT NOT NULL,
    round_title VARCHAR(100) NOT NULL,
    round_status VARCHAR(30) DEFAULT 'Scheduled',
    scheduled_at DATETIME NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_application_round (application_id, round_number),
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);
