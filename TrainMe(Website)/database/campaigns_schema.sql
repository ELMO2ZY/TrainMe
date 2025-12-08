-- Campaigns Database Schema for TrainMe
-- Run this SQL script in DBeaver to create the campaigns tables

USE trainme_db;

-- Campaigns table
CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    template VARCHAR(50) NOT NULL, -- 'microsoft', 'paypal', 'amazon', 'google', 'custom'
    subject VARCHAR(255) NOT NULL,
    email_content TEXT NOT NULL,
    sender_name VARCHAR(100) DEFAULT 'Security Team',
    sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com',
    landing_page_url VARCHAR(500),
    status ENUM('draft', 'scheduled', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    total_recipients INT DEFAULT 0,
    total_sent INT DEFAULT 0,
    total_clicks INT DEFAULT 0,
    total_reports INT DEFAULT 0,
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campaign recipients table
CREATE TABLE IF NOT EXISTS campaign_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    user_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    name VARCHAR(100),
    status ENUM('pending', 'sent', 'delivered', 'failed', 'clicked', 'reported') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    reported_at TIMESTAMP NULL,
    click_count INT DEFAULT 0,
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_email (email),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_campaign_user (campaign_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campaign tracking table (for detailed click tracking)
CREATE TABLE IF NOT EXISTS campaign_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    recipient_id INT NOT NULL,
    user_id INT NOT NULL,
    action_type ENUM('email_sent', 'email_opened', 'link_clicked', 'form_submitted', 'reported') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer VARCHAR(500),
    clicked_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES campaign_recipients(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify tables were created
SELECT 'Campaigns tables created successfully!' AS Status;
SELECT COUNT(*) AS 'Campaigns Count' FROM campaigns;
SELECT COUNT(*) AS 'Recipients Count' FROM campaign_recipients;
SELECT COUNT(*) AS 'Tracking Count' FROM campaign_tracking;

