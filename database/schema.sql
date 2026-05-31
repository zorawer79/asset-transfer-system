-- Asset Transfer Management System Database Schema

-- Create Database
CREATE DATABASE IF NOT EXISTS asset_transfer_db;
USE asset_transfer_db;

-- Asset Groups Table
CREATE TABLE IF NOT EXISTS asset_groups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assets Table
CREATE TABLE IF NOT EXISTS assets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_name VARCHAR(255) NOT NULL,
    asset_id VARCHAR(100) NOT NULL UNIQUE,
    group_id INT NOT NULL,
    rfid_code VARCHAR(100) UNIQUE,
    status ENUM('New', 'In Store', 'Used', 'Scrap') DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES asset_groups(id) ON DELETE CASCADE,
    INDEX idx_asset_id (asset_id),
    INDEX idx_rfid_code (rfid_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Locations Table
CREATE TABLE IF NOT EXISTS locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    location_name VARCHAR(255) NOT NULL UNIQUE,
    location_type VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Asset Transfers Table
CREATE TABLE IF NOT EXISTS transfers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT NOT NULL,
    transfer_from VARCHAR(255),
    stored_location VARCHAR(255),
    transfer_to VARCHAR(255) NOT NULL,
    date_received DATE,
    date_of_transfer DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    INDEX idx_asset_id (asset_id),
    INDEX idx_transfer_date (date_of_transfer)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Asset Photos Table
CREATE TABLE IF NOT EXISTS asset_photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT NOT NULL,
    photo_path VARCHAR(500) NOT NULL,
    photo_name VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    INDEX idx_asset_id (asset_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Asset Notes Table
CREATE TABLE IF NOT EXISTS asset_notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT NOT NULL,
    note_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    INDEX idx_asset_id (asset_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create indexes for better query performance
CREATE INDEX idx_asset_group ON assets(group_id);
CREATE INDEX idx_transfer_asset ON transfers(asset_id);