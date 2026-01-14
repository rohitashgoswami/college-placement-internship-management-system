-- Create database
CREATE DATABASE college_placement_system;

-- Use database
USE college_placement_system;

-- =========================
-- Admin Table
-- =========================
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- =========================
-- Student Table
-- =========================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    roll_number VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
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
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (internship_id) REFERENCES internships(id)
);
