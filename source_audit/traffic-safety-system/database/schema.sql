-- Team 6: Intelligent Traffic Violation Reporting and Road Safety Management System
-- Import this file in phpMyAdmin. The database is created if your MySQL user has permission.
CREATE DATABASE IF NOT EXISTS traffic_safety_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE traffic_safety_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','officer','citizen') NOT NULL DEFAULT 'citizen',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference_no VARCHAR(24) NOT NULL UNIQUE,
    reporter_name VARCHAR(100) NOT NULL,
    reporter_email VARCHAR(190) NOT NULL,
    vehicle_number VARCHAR(15) NOT NULL,
    violation_type VARCHAR(80) NOT NULL,
    location_text VARCHAR(255) NOT NULL,
    incident_at DATETIME NULL,
    description TEXT NOT NULL,
    evidence_path VARCHAR(255) NULL,
    status ENUM('Submitted','Under Review','Verified','Rejected','Closed') NOT NULL DEFAULT 'Submitted',
    officer_notes TEXT NULL,
    penalty_amount DECIMAL(10,2) NULL COMMENT 'Illustrative prototype estimate only; not an official fine',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    citizen_id INT UNSIGNED NULL,
    INDEX idx_reports_status_created (status, created_at),
    INDEX idx_reports_email_ref (reporter_email, reference_no),
    INDEX idx_reports_vehicle (vehicle_number),
    INDEX idx_reports_violation (violation_type),
    INDEX idx_reports_location (location_text),
    INDEX idx_reports_coordinates (latitude, longitude),
    CONSTRAINT fk_reports_citizen FOREIGN KEY (citizen_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS report_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    old_status ENUM('Submitted','Under Review','Verified','Rejected','Closed') NULL,
    new_status ENUM('Submitted','Under Review','Verified','Rejected','Closed') NOT NULL,
    notes TEXT NULL,
    changed_by INT UNSIGNED NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    CONSTRAINT fk_history_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_history_report_time (report_id, changed_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS offence_rules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    category ENUM('Minor','Major','Severe') NOT NULL DEFAULT 'Minor',
    default_penalty DECIMAL(10,2) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS challans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    challan_no VARCHAR(30) NOT NULL UNIQUE,
    report_id BIGINT UNSIGNED NOT NULL,
    rule_id INT UNSIGNED NULL,
    citizen_id INT UNSIGNED NULL,
    vehicle_number VARCHAR(15) NOT NULL,
    offence_name VARCHAR(120) NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('Draft','Issued','Paid','Cancelled') NOT NULL DEFAULT 'Draft',
    issued_at DATETIME NULL,
    due_at DATETIME NULL,
    notice_text TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_challan_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    CONSTRAINT fk_challan_rule FOREIGN KEY (rule_id) REFERENCES offence_rules(id) ON DELETE SET NULL,
    CONSTRAINT fk_challan_citizen FOREIGN KEY (citizen_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_challan_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    report_id BIGINT UNSIGNED NULL,
    challan_id BIGINT UNSIGNED NULL,
    title VARCHAR(150) NOT NULL,
    message VARCHAR(500) NOT NULL,
    link VARCHAR(255) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_challan FOREIGN KEY (challan_id) REFERENCES challans(id) ON DELETE CASCADE,
    INDEX idx_notifications_user_read (user_id, is_read, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    actor_role VARCHAR(20) NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(100) NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_action_time (action, created_at)
) ENGINE=InnoDB;

INSERT IGNORE INTO offence_rules (name, description, category, default_penalty, active) VALUES
('Speeding','Exceeds the applicable road speed limit','Major',1500.00,1),
('Red-light violation','Fails to stop at a red traffic signal','Major',2000.00,1),
('Wrong-side driving','Drives against the designated traffic flow','Severe',2500.00,1),
('Illegal parking','Parked in a prohibited or unsafe location','Minor',500.00,1),
('Helmet / seat-belt violation','Required safety equipment or restraint not used','Major',1000.00,1),
('Using a mobile phone while driving','Uses a handheld device while controlling a vehicle','Major',1500.00,1),
('Blocking a pedestrian crossing','Obstructs a designated pedestrian crossing','Minor',700.00,1),
('Dangerous / reckless driving','Creates avoidable danger to other road users','Severe',3000.00,1),
('Other','Other traffic-safety concern requiring review','Minor',0.00,1);
