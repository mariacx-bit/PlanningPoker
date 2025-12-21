-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : dim. 21 déc. 2025 à 21:19
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `PlanningPoker`
--

-- --------------------------------------------------------

--
-- Structure de la table `joueur_partie`
--

CREATE TABLE `joueur_partie` (
  `id` int(11) NOT NULL,
  `partie_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pseudonyme` varchar(100) NOT NULL,
  `est_maitre` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `partie`
--

CREATE TABLE `partie` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `lien` varchar(20) NOT NULL,
  `nb_joueur` int(11) NOT NULL,
  `mode` varchar(15) NOT NULL,
  `minuteur` time NOT NULL,
  `createur` varchar(255) NOT NULL,
  `id_createur` int(11) NOT NULL,
  `etat` varchar(20) DEFAULT 'en_cours',
  `backlog_json` longtext DEFAULT NULL,
  `resultats` longtext DEFAULT NULL,
  `tache_index` int(11) DEFAULT 0,
  `tour` int(11) DEFAULT 1,
  `created_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `partie`
--

INSERT INTO `partie` (`id`, `nom`, `lien`, `nb_joueur`, `mode`, `minuteur`, `createur`, `id_createur`, `etat`, `backlog_json`, `resultats`, `tache_index`, `tour`, `created_at`) VALUES
(1, 'test', 'e504ca8c', 10, 'fibonacci', '00:02:00', '', 0, 'en_cours', NULL, NULL, 0, 1, '0000-00-00'),
(2, 'test', '8872C4', 10, 'moyenne', '00:02:00', 'noun', 2, 'en_cours', '[{\"id\":1,\"title\":\"ohhhhh\",\"description\":\"\",\"status\":\"validated\",\"estimate\":21},{\"id\":2,\"title\":\"test\",\"description\":\"\",\"status\":\"validated\",\"estimate\":21},{\"id\":3,\"title\":\"tddt\",\"description\":\"\",\"status\":\"validated\",\"estimate\":5},{\"id\":4,\"title\":\"fcjy\",\"description\":\"\",\"status\":\"validated\",\"estimate\":2},{\"id\":5,\"title\":\"j\",\"description\":\"\",\"status\":\"validated\",\"estimate\":44.5},{\"id\":6,\"title\":\"gc\",\"description\":\"\",\"status\":\"pending\",\"estimate\":null},{\"id\":7,\"title\":\"khi\",\"description\":\"gjv\",\"status\":\"pending\",\"estimate\":null}]', NULL, 5, 1, '0000-00-00'),
(3, 'projet planning poker', '9719DC', 10, 'strict', '00:02:00', 'noun', 2, 'running', '[{&quot;id&quot;:1,&quot;title&quot;:&quot;test&quot;,&quot;description&quot;:&quot;&quot;}]', '[]', 0, 1, '2025-12-16'),
(4, 'bonjour', '2011A5', 2, 'strict', '00:01:00', 'noun', 2, 'en_cours', '[{\"id\":1,\"title\":\"bla\",\"description\":\"\",\"status\":\"validated\",\"estimate\":8}]', NULL, 0, 2, '2025-12-21');

-- --------------------------------------------------------

--
-- Structure de la table `tache`
--

CREATE TABLE `tache` (
  `id` int(11) NOT NULL,
  `description` text NOT NULL,
  `resultat` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pseudonyme` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `password`, `pseudonyme`) VALUES
(2, 'clairmanon@hotmail.com', '$2y$10$v55UC5KahTC3lFP4dckmkuT4EM0DoZtmzmTdJXgS5nu8v.uuCRRVq', 'nounette');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `joueur_partie`
--
ALTER TABLE `joueur_partie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partie_id` (`partie_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `partie`
--
ALTER TABLE `partie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tache`
--
ALTER TABLE `tache`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `joueur_partie`
--
ALTER TABLE `joueur_partie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `partie`
--
ALTER TABLE `partie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `tache`
--
ALTER TABLE `tache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `joueur_partie`
--
ALTER TABLE `joueur_partie`
  ADD CONSTRAINT `joueur_partie_ibfk_1` FOREIGN KEY (`partie_id`) REFERENCES `partie` (`id`),
  ADD CONSTRAINT `joueur_partie_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
