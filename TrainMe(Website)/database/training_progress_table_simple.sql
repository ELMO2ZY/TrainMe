-- Training Progress Table (Simple Version - No Foreign Key)
-- Use this if you're getting foreign key compatibility errors
-- The table will work fine without the foreign key constraint

USE trainme_db;

CREATE TABLE IF NOT EXISTS training_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    module_key VARCHAR(50) NOT NULL,
    score INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_module (user_id, module_key),
    INDEX idx_user_id (user_id),
    INDEX idx_module_key (module_key),
    INDEX idx_completed_at (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: Foreign key constraint is omitted to avoid compatibility issues
-- The table will still work perfectly - it just won't enforce referential integrity at the database level
-- Your application code will handle the relationship between users and training_progress

