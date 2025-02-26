-- Create Database
CREATE DATABASE IF NOT EXISTS bbpc;
USE bbpc;

-- Create users table
CREATE TABLE users (
    user_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    bank_account VARCHAR(255)
);

-- Create notifications table
CREATE TABLE notifications (
    notify_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11),
    message VARCHAR(255),
    status ENUM('Unread', 'Read') DEFAULT 'Unread',
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create feedback table
CREATE TABLE feedback (
    feedback_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11),
    message TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create bonds table
CREATE TABLE bonds (
    bond_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    bond_num VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('Available', 'Sold', 'Won') DEFAULT 'Available',
    owner_id INT(11),
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(user_id)
);

-- Create draws table
CREATE TABLE draws (
    draw_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    draw_date DATE NOT NULL,
    draw_round INT(11) NOT NULL
);

-- Create prize_category table
CREATE TABLE prize_category (
    cat_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    category ENUM('First', 'Second', 'Third', 'Other') NOT NULL,
    prize_amount DECIMAL(12,2) NOT NULL
);

-- Create draw_results table
CREATE TABLE draw_results (
    result_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    draw_id INT(11),
    bond_num VARCHAR(50),
    cat_id INT(11),
    FOREIGN KEY (draw_id) REFERENCES draws(draw_id),
    FOREIGN KEY (cat_id) REFERENCES prize_category(cat_id)
);

-- Create check_prize table
CREATE TABLE check_prize (
    check_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    result_id INT(11),
    bond_id INT(11),
    user_id INT(11),
    check_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (result_id) REFERENCES draw_results(result_id),
    FOREIGN KEY (bond_id) REFERENCES bonds(bond_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
