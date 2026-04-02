CREATE DATABASE IF NOT EXISTS service_pro;
USE service_pro;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    tracking_id INT UNIQUE,
    description TEXT,
    model VARCHAR(100),
    category VARCHAR(50),
    service_type ENUM('Walk-in', 'Home-based', 'On-shop') NOT NULL DEFAULT 'On-shop',
    qr_code_path VARCHAR(255),
    status ENUM('Pending', 'In Progress', 'Ready', 'Claimed') DEFAULT 'Pending',
    total_price DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;