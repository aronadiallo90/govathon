-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 19 mai 2025 à 11:38
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `govathon`
--

-- --------------------------------------------------------

--
-- Structure de la table `criteres`
--

CREATE TABLE `criteres` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `coefficient` float NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `criteres`
--

INSERT INTO `criteres` (`id`, `nom`, `description`, `coefficient`, `created_at`, `updated_at`) VALUES
(1, 'Innovation', 'Niveau d\'innovation du projet dans l\'administration', 4.5, '2025-05-05 12:50:47', '2025-05-15 09:18:26'),
(2, 'Faisabilité', 'Faisabilité technique et financière', 2, '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(3, 'Impact', 'Impact social et économique', 3, '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(4, 'Durabilité', 'Durabilité et respect de l\'environnement', 2, '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(5, 'Présentation', 'Qualité de la présentation', 1, '2025-05-05 12:50:47', '2025-05-05 12:50:47');

-- --------------------------------------------------------

--
-- Structure de la table `dynamic_field_definitions`
--

CREATE TABLE `dynamic_field_definitions` (
  `id` int(11) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_type` enum('text','number','date','email','tel') NOT NULL DEFAULT 'text',
  `is_required` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `dynamic_field_definitions`
--

INSERT INTO `dynamic_field_definitions` (`id`, `field_name`, `field_type`, `is_required`, `created_at`, `updated_at`) VALUES
(2, 'Email', 'email', 0, '2025-05-05 16:48:10', '2025-05-05 16:48:10'),
(3, 'Téléphone', 'text', 0, '2025-05-05 16:49:24', '2025-05-05 16:49:24');

-- --------------------------------------------------------

--
-- Structure de la table `etapes`
--

CREATE TABLE `etapes` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ordre` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `statut` enum('a_venir','en_cours','terminee') NOT NULL DEFAULT 'a_venir'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `etapes`
--

INSERT INTO `etapes` (`id`, `nom`, `description`, `ordre`, `date_debut`, `date_fin`, `created_at`, `updated_at`, `statut`) VALUES
(1, 'Présélection', 'Première phase de sélection des projets', 9, '2024-05-01', '2024-05-15', '2025-05-05 12:50:47', '2025-05-13 08:57:45', 'terminee'),
(2, 'Qualification', 'Phase de qualification des projets retenus', 1, '2024-05-16', '2024-05-31', '2025-05-05 12:50:47', '2025-05-13 09:23:08', 'a_venir'),
(4, 'Finale', 'Phase finale du concours', 3, '2024-06-16', '2024-06-30', '2025-05-05 12:50:47', '2025-05-13 09:23:14', 'a_venir'),
(8, 'Inscription', 'les equipes s\'inscrivent', 1, '2025-05-16', '2025-05-16', '2025-05-15 09:19:33', '2025-05-15 09:19:41', 'en_cours');

-- --------------------------------------------------------

--
-- Structure de la table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `jury_id` int(11) NOT NULL,
  `note` decimal(4,2) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('draft','submitted','under_review','approved','rejected') DEFAULT 'draft',
  `secteur_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `projects`
--

INSERT INTO `projects` (`id`, `nom`, `description`, `status`, `secteur_id`, `created_by`, `created_at`, `updated_at`) VALUES
(8, 'JHE FDHV', 'JHD FBJVDFVHEV', 'draft', 6, 3, '2025-05-05 17:30:23', '2025-05-05 17:30:23'),
(10, 'jhbhbuhu', 'jknerfjkngjerkngit ekrjtntrenr', 'approved', 9, 3, '2025-05-06 12:59:09', '2025-05-06 12:59:09'),
(11, 'TestNouveausqjn', 'skjbnz zjbkzer  zekjbnf', 'submitted', 4, 3, '2025-05-06 15:59:30', '2025-05-06 15:59:30'),
(12, 'NNNNNNNN', 'bbbbbbbb', 'draft', 11, 3, '2025-05-06 17:30:58', '2025-05-06 17:30:58'),
(13, 'test test', 'K?LNFSKJBGNFDNGJLEFDJN', 'rejected', 10, 3, '2025-05-08 08:30:50', '2025-05-08 08:30:50'),
(14, 'NNNNNNNN', 'ihbohkl oihbih hjb', 'submitted', 8, 3, '2025-05-08 10:27:22', '2025-05-08 10:27:22');

-- --------------------------------------------------------

--
-- Structure de la table `project_dynamic_values`
--

CREATE TABLE `project_dynamic_values` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `field_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `project_dynamic_values`
--

INSERT INTO `project_dynamic_values` (`id`, `project_id`, `field_id`, `field_value`, `created_at`, `updated_at`) VALUES
(2, 8, 2, 'aronadiallo90@gmail.com', '2025-05-05 17:30:23', '2025-05-05 17:30:23'),
(3, 8, 3, '7797897878767', '2025-05-05 17:30:23', '2025-05-05 17:30:23'),
(6, 10, 2, 'aronadiallo@gmail.com', '2025-05-06 12:59:09', '2025-05-06 12:59:09'),
(7, 10, 3, '88888888', '2025-05-06 12:59:09', '2025-05-06 12:59:09'),
(8, 11, 2, 'aronadiallo@gmail.com', '2025-05-06 15:59:30', '2025-05-06 15:59:30'),
(9, 11, 3, '135674678', '2025-05-06 15:59:30', '2025-05-06 15:59:30'),
(10, 12, 2, 'aronadiallo@gmail.com', '2025-05-06 17:30:58', '2025-05-06 17:30:58'),
(11, 12, 3, '888888', '2025-05-06 17:30:58', '2025-05-06 17:30:58'),
(12, 13, 2, 'aronadiallo90@gmail.com', '2025-05-08 08:30:50', '2025-05-08 08:30:50'),
(13, 13, 3, '776791039', '2025-05-08 08:30:50', '2025-05-08 08:30:50'),
(14, 14, 2, 'aronadialliio@gmail.com', '2025-05-08 10:27:22', '2025-05-08 10:27:22'),
(15, 14, 3, '88888888', '2025-05-08 10:27:22', '2025-05-08 10:27:22');

-- --------------------------------------------------------

--
-- Structure de la table `secteurs`
--

CREATE TABLE `secteurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fa-building',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `secteurs`
--

INSERT INTO `secteurs` (`id`, `nom`, `icon`, `description`, `created_at`, `updated_at`) VALUES
(4, 'Santé', 'fa-heartbeat', 'Projets dans le domaine de la santé et du bien-être', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(5, 'Education', 'fa-graduation-cap', 'Projets éducatifs et formation', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(6, 'Agriculture', 'fa-leaf', 'Projets agricoles et développement rural', '2025-05-05 12:50:47', '2025-05-15 09:20:11'),
(8, 'Environnement', 'fa-tree', 'Projets écologiques et développement durable', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(9, 'Finance', 'fa-coins', 'Projets financiers et économiques', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(10, 'Infrastructure', 'fa-building', 'Projets d\'infrastructure et construction', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(11, 'Industrie', 'fa-industry', 'Projets industriels et manufacturiers', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(12, 'Innovation', 'fa-flask', 'Projets innovants et recherche', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(16, 'Test', 'fa-hammer', 'kjbj jhb', '2025-05-12 11:20:07', '2025-05-12 11:20:07');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','jury','admin','superadmin') NOT NULL DEFAULT 'user',
  `secteur_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_global_jury` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `secteur_id`, `is_active`, `is_global_jury`, `created_at`, `updated_at`, `reset_token`, `reset_token_expires`) VALUES
(2, 'Super Admin', 'superadmin@example.com', '$2y$10$jUsESY5l874AGuUAyPbUC.WMBTPoRFgA/b.yUE3vazEWsCX6cnD4S', 'superadmin', NULL, 1, 0, '2025-05-05 11:12:55', '2025-05-13 11:31:55', NULL, NULL),
(3, 'Super Admin', 'aronadiallo90@gmail.com', '$2y$10$YANAJ0XoKZt9KnGhVqCBBOpDI7XQ2oHBWUMoImA4oEDt6HdQ5v8WW', 'superadmin', NULL, 1, 0, '2025-05-05 11:12:55', '2025-05-05 16:15:27', '0b1272ac9b632c788345661e6e4e8575e231702f1f5c71396a19a5e53e9110d8', '2025-05-05 19:15:27'),
(16, 'Marc Expert', 'marc.expert@jury.com', '$2y$10$YANAJ0XoKZt9KnGhVqCBBOpDI7XQ2oHBWUMoImA4oEDt6HdQ5v8WW', 'admin', NULL, 1, 1, '2025-05-05 15:51:55', '2025-05-13 10:44:38', NULL, NULL),
(17, 'Lucie', 'lucie.test@jury.com', '$2y$10$YANAJ0XoKZt9KnGhVqCBBOpDI7XQ2oHBWUMoImA4oEDt6HdQ5v8WW', 'jury', 12, 1, 0, '2025-05-05 15:51:55', '2025-05-13 10:53:18', NULL, NULL),
(21, 'Test', 'superadminTest@example.com', '$2y$10$LWp2vHgVwtlx2dQ924IpLOvi1IhmahkCuix0pG3Ub5urfR/B0V5Z.', 'jury', 4, 1, 1, '2025-05-13 10:45:57', '2025-05-15 09:21:04', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `critere_id` int(11) NOT NULL,
  `etape_id` int(11) NOT NULL,
  `note` float NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `project_etapes`
--
CREATE TABLE `project_etapes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `etape_id` int(11) NOT NULL,
  `status` enum('en_cours','valide','elimine') DEFAULT 'en_cours',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_project_etape` (`project_id`),
  KEY `etape_id` (`etape_id`),
  CONSTRAINT `project_etapes_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_etapes_ibfk_2` FOREIGN KEY (`etape_id`) REFERENCES `etapes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `criteres`
--
ALTER TABLE `criteres`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `dynamic_field_definitions`
--
ALTER TABLE `dynamic_field_definitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `field_name` (`field_name`);

--
-- Index pour la table `etapes`
--
ALTER TABLE `etapes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `jury_id` (`jury_id`);

--
-- Index pour la table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `secteur_id` (`secteur_id`),
  ADD KEY `idx_projects_status` (`status`),
  ADD KEY `projects_user_fk` (`created_by`);

--
-- Index pour la table `project_dynamic_values`
--
ALTER TABLE `project_dynamic_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Index pour la table `secteurs`
--
ALTER TABLE `secteurs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `secteur_id` (`secteur_id`);

--
-- Index pour la table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`user_id`,`project_id`,`critere_id`,`etape_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `critere_id` (`critere_id`),
  ADD KEY `etape_id` (`etape_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `criteres`
--
ALTER TABLE `criteres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `dynamic_field_definitions`
--
ALTER TABLE `dynamic_field_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `etapes`
--
ALTER TABLE `etapes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `project_dynamic_values`
--
ALTER TABLE `project_dynamic_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT pour la table `secteurs`
--
ALTER TABLE `secteurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `evaluations`
--
ALTER TABLE `

-- Modification de la table project_etapes pour garantir l'unicité des projets
ALTER TABLE `project_etapes` 
DROP INDEX IF EXISTS `project_id`,
DROP INDEX IF EXISTS `unique_project_etape`,
ADD UNIQUE KEY `unique_project_etape` (`project_id`);

-- Mise à jour des données existantes pour éviter les doublons
DELETE t1 FROM project_etapes t1
INNER JOIN project_etapes t2
WHERE t1.id > t2.id 
AND t1.project_id = t2.project_id;