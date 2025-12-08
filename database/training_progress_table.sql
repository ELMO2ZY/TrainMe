-- Training Progress Table
-- This table stores training completion data for all users

USE trainme_db;

-- Drop table if it exists (optional - remove this line if you want to keep existing data)
-- DROP TABLE IF EXISTS training_progress;

-- Create table with UNSIGNED INT to match users.id (which is int unsigned)
CREATE TABLE IF NOT EXISTS training_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    module_key VARCHAR(50) NOT NULL,
    score INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_module (user_id, module_key),
    INDEX idx_user_id (user_id),
    INDEX idx_module_key (module_key),
    INDEX idx_completed_at (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraint (only if it doesn't already exist)
-- If you get an error that the constraint already exists, you can skip this part
ALTER TABLE training_progress 
DROP FOREIGN KEY IF EXISTS fk_training_progress_user;

ALTER TABLE training_progress 
ADD CONSTRAINT fk_training_progress_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

