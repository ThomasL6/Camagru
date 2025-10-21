-- Migration script to add password reset fields to existing users table
-- Run this if you already have a users table created

USE camagru;

-- Add reset_token column (will fail silently if column already exists)
SET @query = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN reset_token VARCHAR(64) NULL AFTER is_verified;',
        'SELECT "Column reset_token already exists" AS message;'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'camagru'
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'reset_token'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add reset_token_expiry column (will fail silently if column already exists)
SET @query = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN reset_token_expiry TIMESTAMP NULL AFTER reset_token;',
        'SELECT "Column reset_token_expiry already exists" AS message;'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'camagru'
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'reset_token_expiry'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
