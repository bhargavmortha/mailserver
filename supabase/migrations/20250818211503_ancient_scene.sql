-- MailFlow Database Schema

CREATE DATABASE IF NOT EXISTS mailflow;
USE mailflow;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('Administrator', 'User', 'Manager') DEFAULT 'User',
    is_active BOOLEAN DEFAULT TRUE,
    failed_attempts INT DEFAULT 0,
    last_attempt TIMESTAMP NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Emails table
CREATE TABLE emails (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    subject VARCHAR(500) NOT NULL,
    body TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    is_starred BOOLEAN DEFAULT FALSE,
    is_draft BOOLEAN DEFAULT FALSE,
    is_spam BOOLEAN DEFAULT FALSE,
    is_deleted BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_recipient_created (recipient_id, created_at),
    INDEX idx_sender_created (sender_id, created_at),
    INDEX idx_is_read (is_read),
    INDEX idx_is_starred (is_starred)
);

-- Email attachments table
CREATE TABLE email_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    size INT NOT NULL,
    mime_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email_id) REFERENCES emails(id) ON DELETE CASCADE
);

-- Email labels table
CREATE TABLE email_labels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Email label assignments
CREATE TABLE email_label_assignments (
    email_id INT NOT NULL,
    label_id INT NOT NULL,
    PRIMARY KEY (email_id, label_id),
    FOREIGN KEY (email_id) REFERENCES emails(id) ON DELETE CASCADE,
    FOREIGN KEY (label_id) REFERENCES email_labels(id) ON DELETE CASCADE
);

-- Email queue for processing
CREATE TABLE email_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email_id INT NOT NULL,
    status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (email_id) REFERENCES emails(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
);

-- Audit logs table
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);

-- System settings table
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (email, password, name, role) VALUES 
('admin@mailflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'Administrator');
-- Password is: admin123

-- Insert sample emails
INSERT INTO emails (sender_id, recipient_id, subject, body, created_at) VALUES
(1, 1, 'Welcome to the Admin Mail System', 'This is your new email administration system with full control over user accounts, permissions, and email flow.', NOW()),
(1, 1, 'New user account request', 'A new user has requested access to the email system. Please review and approve or deny access.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 1, 'Unusual login activity detected', 'We''ve detected an unusual login attempt from a new device. Please verify if this was you or take action.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 1, 'Scheduled maintenance this weekend', 'We''ll be performing system updates this weekend. The mail system will be down for approximately 2 hours.', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 1, 'Storage quota update', 'Your department''s storage quota has been increased to 100GB. You can now adjust individual user quotas.', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Insert default labels
INSERT INTO email_labels (name, color, user_id) VALUES
('Work', '#10b981', 1),
('Personal', '#3b82f6', 1),
('Important', '#f59e0b', 1),
('Projects', '#8b5cf6', 1);

-- Insert sample audit logs
INSERT INTO audit_logs (user_id, action, details, ip_address, created_at) VALUES
(1, 'login', 'Admin login from 192.168.1.145', '192.168.1.145', DATE_SUB(NOW(), INTERVAL 2 MINUTE)),
(1, 'user_created', 'New user account created', '192.168.1.145', DATE_SUB(NOW(), INTERVAL 35 MINUTE)),
(1, 'security_alert', 'Storage usage warning threshold reached', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 'config_update', 'System configuration updated', '192.168.1.145', DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('smtp_host', 'localhost', 'SMTP server hostname'),
('smtp_port', '587', 'SMTP server port'),
('max_attachment_size', '10485760', 'Maximum attachment size in bytes'),
('session_timeout', '3600', 'Session timeout in seconds'),
('maintenance_mode', '0', 'Enable maintenance mode');