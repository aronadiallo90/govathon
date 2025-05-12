-- Script de création de la base de données GOVATHON
-- Version: 1.0
-- Date: 2025-05-05

-- Suppression de la base si elle existe déjà
DROP DATABASE IF EXISTS govathon;

-- Création de la base de données
CREATE DATABASE govathon
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE govathon;

-- Désactiver les contraintes de clés étrangères pendant la création
SET FOREIGN_KEY_CHECKS = 0;

-- Table des secteurs
DROP TABLE IF EXISTS secteurs;
CREATE TABLE secteurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des étapes
DROP TABLE IF EXISTS etapes;
CREATE TABLE etapes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    ordre INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    etat ENUM('pending', 'active', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des utilisateurs
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des administrateurs
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    secteur_id INT,
    is_superadmin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (secteur_id) REFERENCES secteurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des définitions de champs dynamiques
DROP TABLE IF EXISTS dynamic_field_definitions;
CREATE TABLE dynamic_field_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_name VARCHAR(255) NOT NULL UNIQUE,
    field_type ENUM('text', 'number', 'date', 'email', 'tel') NOT NULL DEFAULT 'text',
    is_required BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des projets
DROP TABLE IF EXISTS projects;
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    secteur_id INT NOT NULL,
    created_by INT NOT NULL,
    status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (secteur_id) REFERENCES secteurs(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des valeurs des champs dynamiques
DROP TABLE IF EXISTS project_dynamic_values;
CREATE TABLE project_dynamic_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    field_id INT NOT NULL,
    field_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES dynamic_field_definitions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des jurys
DROP TABLE IF EXISTS jurys;
CREATE TABLE jurys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    secteur_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (secteur_id) REFERENCES secteurs(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des critères
DROP TABLE IF EXISTS criteres;
CREATE TABLE criteres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    coefficient FLOAT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des votes
DROP TABLE IF EXISTS votes;
CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jury_id INT NOT NULL,
    project_id INT NOT NULL,
    critere_id INT NOT NULL,
    etape_id INT NOT NULL,
    note FLOAT NOT NULL,
    commentaire TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (jury_id) REFERENCES jurys(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (critere_id) REFERENCES criteres(id) ON DELETE CASCADE,
    FOREIGN KEY (etape_id) REFERENCES etapes(id) ON DELETE CASCADE,
    UNIQUE (jury_id, project_id, critere_id, etape_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Réactiver les contraintes de clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

-- Données initiales pour l'administrateur
INSERT INTO admins (name, email, password, is_superadmin) VALUES 
('Super Admin', 'superadmin@example.com', '$2y$10$jUsESY5l874AGuUAyPbUC.WMBTPoRFgA/b.yUE3vazEWsCX6cnD4S', 1);

-- Données initiales pour les étapes
INSERT INTO etapes (nom, description, ordre, date_debut, date_fin, etat) VALUES 
('Présélection', 'Première phase de sélection des projets', 1, '2024-05-01', '2024-05-15', 'pending'),
('Qualification', 'Phase de qualification des projets retenus', 2, '2024-05-16', '2024-05-31', 'pending'),
('Demi-finale', 'Phase de demi-finale', 3, '2024-06-01', '2024-06-15', 'pending'),
('Finale', 'Phase finale du concours', 4, '2024-06-16', '2024-06-30', 'pending');

-- Index pour améliorer les performances
CREATE INDEX idx_projects_secteur ON projects(secteur_id);
CREATE INDEX idx_projects_status ON projects(status);
CREATE INDEX idx_dynamic_values_project ON project_dynamic_values(project_id);
CREATE INDEX idx_votes_project ON votes(project_id);
