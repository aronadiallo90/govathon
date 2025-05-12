-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 06 mai 2025 à 19:51
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
(1, 'Innovation', 'Niveau d\'innovation du projet', 3, '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
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
  `etat` enum('pending','active','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `etapes`
--

INSERT INTO `etapes` (`id`, `nom`, `description`, `ordre`, `date_debut`, `date_fin`, `etat`, `created_at`, `updated_at`) VALUES
(1, 'Présélection', 'Première phase de sélection des projets', 1, '2024-05-01', '2024-05-15', 'pending', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(2, 'Qualification', 'Phase de qualification des projets retenus', 2, '2024-05-16', '2024-05-31', 'pending', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(3, 'Demi-finale', 'Phase de demi-finale', 3, '2024-06-01', '2024-06-15', 'pending', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(4, 'Finale', 'Phase finale du concours', 4, '2024-06-16', '2024-06-30', 'pending', '2025-05-05 12:50:47', '2025-05-05 12:50:47');

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
(9, 'tesg2', 'kjldbegn rfg jktr', 'under_review', 8, 3, '2025-05-05 17:37:25', '2025-05-05 17:37:25'),
(10, 'jhbhbuhu', 'jknerfjkngjerkngit ekrjtntrenr', 'approved', 9, 3, '2025-05-06 12:59:09', '2025-05-06 12:59:09'),
(11, 'TestNouveausqjn', 'skjbnz zjbkzer  zekjbnf', 'submitted', 4, 3, '2025-05-06 15:59:30', '2025-05-06 15:59:30'),
(12, 'NNNNNNNN', 'bbbbbbbb', 'draft', 11, 3, '2025-05-06 17:30:58', '2025-05-06 17:30:58');

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
(4, 9, 2, 'fmamad1@jh.com', '2025-05-05 17:37:25', '2025-05-05 17:37:25'),
(5, 9, 3, '886677777', '2025-05-05 17:37:25', '2025-05-05 17:37:25'),
(6, 10, 2, 'aronadiallo@gmail.com', '2025-05-06 12:59:09', '2025-05-06 12:59:09'),
(7, 10, 3, '88888888', '2025-05-06 12:59:09', '2025-05-06 12:59:09'),
(8, 11, 2, 'aronadiallo@gmail.com', '2025-05-06 15:59:30', '2025-05-06 15:59:30'),
(9, 11, 3, '135674678', '2025-05-06 15:59:30', '2025-05-06 15:59:30'),
(10, 12, 2, 'aronadiallo@gmail.com', '2025-05-06 17:30:58', '2025-05-06 17:30:58'),
(11, 12, 3, '888888', '2025-05-06 17:30:58', '2025-05-06 17:30:58');

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
(6, 'Agriculture', 'fa-leaf', 'Projets agricoles et développement rurale', '2025-05-05 12:50:47', '2025-05-05 15:25:42'),
(8, 'Environnement', 'fa-tree', 'Projets écologiques et développement durable', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(9, 'Finance', 'fa-coins', 'Projets financiers et économiques', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(10, 'Infrastructure', 'fa-building', 'Projets d\'infrastructure et construction', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(11, 'Industrie', 'fa-industry', 'Projets industriels et manufacturiers', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(12, 'Innovation', 'fa-flask', 'Projets innovants et recherche', '2025-05-05 12:50:47', '2025-05-05 12:50:47'),
(14, 'test', 'fa-hammer', 'dfjskgnjngf', '2025-05-06 13:05:17', '2025-05-06 13:05:17');

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
(1, 'mamadou', 'fmamad12345@gmail.com', '$2y$10$VEAdiYxaYGRuiAzK5VD0XOT8iaZlSHfcnt86ChYcPKB7K6ZAsCCB.', 'user', NULL, 1, 0, '2025-05-05 11:12:55', '2025-05-05 11:12:55', NULL, NULL),
(2, 'Super Admin', 'superadmin@example.com', '$2y$10$jUsESY5l874AGuUAyPbUC.WMBTPoRFgA/b.yUE3vazEWsCX6cnD4S', 'superadmin', NULL, 1, 0, '2025-05-05 11:12:55', '2025-05-05 11:22:24', NULL, NULL),
(3, 'Super Admin', 'aronadiallo90@gmail.com', '$2y$10$YANAJ0XoKZt9KnGhVqCBBOpDI7XQ2oHBWUMoImA4oEDt6HdQ5v8WW', 'superadmin', NULL, 1, 0, '2025-05-05 11:12:55', '2025-05-05 16:15:27', '0b1272ac9b632c788345661e6e4e8575e231702f1f5c71396a19a5e53e9110d8', '2025-05-05 19:15:27'),
(5, 'test', 'test@gmail.com', '$2y$10$opwb.rEkQ8ZYUmL7KBkMnua3lpx7vtNhLqKHOHpANpHUXp3.Ya8..', 'user', NULL, 1, 0, '2025-05-05 15:30:10', '2025-05-05 15:30:10', NULL, NULL),
(12, 'Jean Dupont', 'jean.dupont@jury.com', '$2y$10$YANAJ0XoKZt9KnGhVqCBBOpDI7XQ2oHBWUMoImA4oEDt6HdQ5v8WW', 'jury', 4, 1, 0, '2025-05-05 15:51:55', '2025-05-05 15:51:55', NULL, NULL),
(13, 'Marie Martin', 'marie.martin@jury.com', '$2y$10$YANAJ0XoKZt9KnGhVqCBBOpDI7XQ2oHBWUMoImA4oEDt6HdQ5v8WW', 'jury', 5, 1, 0, '2025-05-05 15:51:55', '2025-05-05 15:51:55', NULL, NULL),
(14, 'Pierre Paul', 'pierre.paul@jury.com', '$2y$10$YANAJ0XoKZt9KnGhVqCBBOpDI7XQ2oHBWUMoImA4oEDt6HdQ5v8WW', 'jury', 6, 1, 0, '2025-05-05 15:51:55', '2025-05-05 15:51:55', NULL, NULL),
(15, 'Sarah Global', 'sarah.global@jury.com', '$2y$10$YANAJ0XoKZt9KnGhVqCBBOpDI7XQ2oHBWUMoImA4oEDt6HdQ5v8WW', 'jury', NULL, 1, 1, '2025-05-05 15:51:55', '2025-05-05 15:51:55', NULL, NULL),
(16, 'Marc Expert', 'marc.expert@jury.com', '$2y$10$YANAJ0XoKZt9KnGhVqCBBOpDI7XQ2oHBWUMoImA4oEDt6HdQ5v8WW', 'jury', 8, 1, 0, '2025-05-05 15:51:55', '2025-05-05 15:51:55', NULL, NULL),
(17, 'Lucie Test', 'lucie.test@jury.com', '$2y$10$YANAJ0XoKZt9KnGhVqCBBOpDI7XQ2oHBWUMoImA4oEDt6HdQ5v8WW', 'jury', 12, 0, 0, '2025-05-05 15:51:55', '2025-05-05 15:51:55', NULL, NULL),
(18, 'test2', 'test2@gmail.com', '$2y$10$mCUboO4UB9iY9XtEABHZwuqdWsOPRQPPASEbUi8js5Al5.yKtckSC', 'user', NULL, 1, 0, '2025-05-05 16:12:09', '2025-05-05 16:12:09', NULL, NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `dynamic_field_definitions`
--
ALTER TABLE `dynamic_field_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `etapes`
--
ALTER TABLE `etapes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `project_dynamic_values`
--
ALTER TABLE `project_dynamic_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `secteurs`
--
ALTER TABLE `secteurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`jury_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`secteur_id`) REFERENCES `secteurs` (`id`),
  ADD CONSTRAINT `projects_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `project_dynamic_values`
--
ALTER TABLE `project_dynamic_values`
  ADD CONSTRAINT `project_dynamic_values_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_dynamic_values_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `dynamic_field_definitions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`secteur_id`) REFERENCES `secteurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`critere_id`) REFERENCES `criteres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_4` FOREIGN KEY (`etape_id`) REFERENCES `etapes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
