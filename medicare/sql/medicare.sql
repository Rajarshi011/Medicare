
-- medicare_xampp SQL schema
-- Import this in phpMyAdmin. Database name recommended: medicare_db

CREATE DATABASE IF NOT EXISTS medicare_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medicare_db;

-- Users: patients, doctors, admin
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('patient','doctor','admin') NOT NULL DEFAULT 'patient',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctors profile
DROP TABLE IF EXISTS doctors;
CREATE TABLE doctors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  specialization VARCHAR(100) NOT NULL,
  license_no VARCHAR(100) NOT NULL,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Subscription plans
DROP TABLE IF EXISTS subscriptions;
CREATE TABLE subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  duration_days INT NOT NULL,
  max_consultations INT DEFAULT NULL, -- NULL means unlimited
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User subscriptions
DROP TABLE IF EXISTS user_subscriptions;
CREATE TABLE user_subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  subscription_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  remaining_consultations INT DEFAULT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
);

-- Appointments
DROP TABLE IF EXISTS appointments;
CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  doctor_id INT NOT NULL,
  date DATE NOT NULL,
  time TIME NOT NULL,
  status ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Prescriptions
DROP TABLE IF EXISTS prescriptions;
CREATE TABLE prescriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NOT NULL,
  doctor_id INT NOT NULL,
  patient_id INT NOT NULL,
  prescription_text TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed data
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@medicare.test', '$2y$10$n8t3R5p3vW2p5WgHqf0f9.YG6Q6KrsGm9mK3Q0Xq2xZhQq7wZMI1C', 'admin'); 
-- password for admin: Admin@123

INSERT INTO users (name, email, password, role) VALUES
('Dr. A. Sharma', 'doctor1@medicare.test', '$2y$10$wH.S6wqT8i3D7k4rQ5uYIuV8xWg9N4w1m3sYqZ0nYb2k3l4m5n6Oa', 'doctor'),
('Riya Sen', 'riya@medicare.test', '$2y$10$wH.S6wqT8i3D7k4rQ5uYIuV8xWg9N4w1m3sYqZ0nYb2k3l4m5n6Oa', 'patient');

-- The hashed password above is for: Test@123 (for doctor1 and riya)

INSERT INTO doctors (user_id, specialization, license_no, verified) VALUES
((SELECT id FROM users WHERE email='doctor1@medicare.test'), 'General Physician', 'LIC-IND-12345', 1);

INSERT INTO subscriptions (plan_name, price, duration_days, max_consultations) VALUES
('Basic', 0.00, 30, 2),
('Standard', 299.00, 30, 5),
('Premium', 599.00, 30, NULL);
