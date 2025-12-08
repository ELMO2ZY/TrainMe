-- ============================================
-- DIRECT TABLE CREATION - Run this NOW
-- ============================================

USE trainme_db;

-- Drop if exists
DROP TABLE IF EXISTS campaign_tracking;

-- Create the table - SIMPLE VERSION
CREATE TABLE campaign_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    recipient_id INT NOT NULL,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer VARCHAR(500),
    clicked_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Immediately verify
SELECT 'Table creation attempted' AS Status;
SHOW TABLES LIKE 'campaign_tracking';
DESCRIBE campaign_tracking;

