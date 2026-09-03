-- MySQL schema for Agnes personal website
-- Create database first, then run this script.

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    cv_file VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lecturers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    caption TEXT,
    date DATE DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    authors VARCHAR(1000),
    journal VARCHAR(500),
    year SMALLINT,
    abstract TEXT,
    doi VARCHAR(255),
    publication_url VARCHAR(1000),
    pdf_file VARCHAR(255),
    image VARCHAR(255),
    caption TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    description TEXT,
    publication_date DATE DEFAULT NULL,
    pdf_file VARCHAR(255),
    pdf_file_name VARCHAR(255),
    cover_image VARCHAR(255),
    caption TEXT,
    allow_download BOOLEAN NOT NULL DEFAULT TRUE,
    allow_print BOOLEAN NOT NULL DEFAULT TRUE,
    viewer_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS consultancy_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    youtube_url VARCHAR(1000),
    thumbnail VARCHAR(255),
    caption TEXT,
    date DATE DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO admins (username, password_hash)
VALUES ('agnes', '$2y$12$.VZL3hR1dLN.4tARUbYN/Ofzez/a3DZBJtHCs0URZYu3ng/TJqNWW')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);

