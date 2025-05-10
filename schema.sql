CREATE DATABASE IF NOT EXISTS vulnerable_bank;
USE vulnerable_bank;

-- A1:2017 - Injection vulnerability - No prepared statements
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL, -- A2:2017 - Broken Authentication - Storing plain text passwords
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user'
);

CREATE TABLE accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    account_number VARCHAR(20) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_account VARCHAR(20),
    to_account VARCHAR(20),
    amount DECIMAL(10,2),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE complaints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    file_name VARCHAR(255),
    file_path VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    username VARCHAR(50),
    action VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert initial users (A2:2017 - Broken Authentication - Weak passwords)
INSERT INTO users (username, password, email, role) VALUES
('admin', 'admin123', 'admin@bank.com', 'admin'),
('user1', 'password1', 'user1@bank.com', 'user'),
('user2', 'password2', 'user2@bank.com', 'user'),
('user3', 'password3', 'user3@bank.com', 'user');

-- Insert accounts for users
INSERT INTO accounts (user_id, account_number, balance) VALUES
(1, 'ACC001', 10000.00),
(1, 'ACC002', 5000.00),
(2, 'ACC003', 7500.00),
(2, 'ACC004', 3000.00),
(3, 'ACC005', 12000.00),
(3, 'ACC006', 8000.00),
(4, 'ACC007', 6000.00),
(4, 'ACC008', 4000.00); 