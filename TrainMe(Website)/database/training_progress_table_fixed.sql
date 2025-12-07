-- Training Progress Table (Fixed Version)
-- This version handles foreign key compatibility issues

USE trainme_db;

-- Option 1: Create table without foreign key first, then add it
-- This allows the table to be created even if there's a foreign key issue

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

-- Try to add foreign key constraint
-- If this fails, you can skip it - the table will still work
-- The foreign key just ensures data integrity

-- First, drop the constraint if it exists (in case of re-running)
ALTER TABLE training_progress DROP FOREIGN KEY IF EXISTS fk_training_progress_user;

-- Now add the foreign key
-- If your users.id is UNSIGNED, change INT to INT UNSIGNED below
ALTER TABLE training_progress 
ADD CONSTRAINT fk_training_progress_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

