-- CRM app database schema generated from the current PHP models/controllers.
-- Import this file into your MySQL/MariaDB server to provision the required tables.
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS deal_files;
DROP TABLE IF EXISTS client_files;
DROP TABLE IF EXISTS client_notes;
DROP TABLE IF EXISTS client_activities;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS deals;
DROP TABLE IF EXISTS leads;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS auth_tokens;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auth_tokens (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_auth_tokens_user_id (user_id),
    UNIQUE KEY uniq_auth_tokens_token (token),
    CONSTRAINT fk_auth_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    token_hash CHAR(64) NOT NULL,
    code_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX idx_password_resets_token (token_hash),
    INDEX idx_password_resets_code (code_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clients (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    company VARCHAR(150),
    position VARCHAR(150),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_clients_user_id (user_id),
    INDEX idx_clients_created_at (user_id, created_at),
    INDEX idx_clients_search (user_id, full_name, email),
    UNIQUE KEY uniq_clients_user_email (user_id, email),
    CONSTRAINT fk_clients_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE leads (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    owner_id INT UNSIGNED,
    property_for VARCHAR(40),
    payment_option VARCHAR(20),
    interested_property VARCHAR(60),
    area VARCHAR(100),
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    status VARCHAR(30) NOT NULL DEFAULT 'new',
    source VARCHAR(60),
    budget DECIMAL(14,2),
    currency CHAR(3),
    notes TEXT,
    last_contact_at DATE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    archived_at DATETIME,
    INDEX idx_leads_user_id (user_id),
    INDEX idx_leads_user_status (user_id, status),
    INDEX idx_leads_user_archived (user_id, archived_at),
    INDEX idx_leads_created_at (user_id, created_at),
    INDEX idx_leads_status (status),
    CONSTRAINT fk_leads_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_leads_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE deals (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    lead_id INT UNSIGNED,
    client_id INT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    stage VARCHAR(30) NOT NULL DEFAULT 'ongoing',
    amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    currency CHAR(3),
    location VARCHAR(255),
    property_detail TEXT,
    close_date DATE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_deals_user_id (user_id),
    INDEX idx_deals_user_stage (user_id, stage),
    INDEX idx_deals_created_at (user_id, created_at),
    INDEX idx_deals_stage (stage),
    INDEX idx_deals_lead_id (lead_id),
    INDEX idx_deals_client_id (client_id),
    CONSTRAINT fk_deals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_deals_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    CONSTRAINT fk_deals_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tasks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    lead_id INT UNSIGNED,
    client_id INT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tasks_user_id (user_id),
    INDEX idx_tasks_user_status (user_id, status),
    INDEX idx_tasks_due_date (user_id, due_date),
    INDEX idx_tasks_status (status),
    CONSTRAINT fk_tasks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_tasks_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE client_notes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_client_notes_client_id (client_id),
    INDEX idx_client_notes_user_id (user_id),
    CONSTRAINT fk_client_notes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_client_notes_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE client_activities (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    type VARCHAR(20) NOT NULL,
    detail TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_client_activities_client_id (client_id),
    INDEX idx_client_activities_user_id (user_id),
    CONSTRAINT fk_client_activities_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_client_activities_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE client_files (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(1024),
    size_label VARCHAR(50),
    disk_path VARCHAR(1024),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_client_files_client_id (client_id),
    INDEX idx_client_files_user_id (user_id),
    CONSTRAINT fk_client_files_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_client_files_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE deal_files (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    deal_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(1024),
    size_label VARCHAR(50),
    disk_path VARCHAR(1024),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_deal_files_deal_id (deal_id),
    INDEX idx_deal_files_user_id (user_id),
    CONSTRAINT fk_deal_files_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_deal_files_deal FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
