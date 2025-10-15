-- Database User Initialization Script

USE camagru;

-- Users Table Creation
CREATE DATABASE IF NOT EXISTS camagru;
USE camagru;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    verification_token VARCHAR(64),
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Images Table Creation
CREATE TABLE if NOT EXISTS images (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	image_path VARCHAR(255) NOT NULL,
	is_public TINYINT(1) DEFAULT 0,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Comments Table Creation
CREATE TABLE if NOT EXISTS comments (
	id INT AUTO_INCREMENT PRIMARY KEY,
	image_id INT NOT NULL,
	user_id INT NOT NULL,
	comment_text TEXT NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Likes Table Creation
CREATE TABLE if NOT EXISTS likes (
	id INT AUTO_INCREMENT PRIMARY KEY,
	image_id INT NOT NULL,
	user_id INT NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	UNIQUE (image_id, user_id)
);

