-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 29 fév. 2024 à 08:34
-- Version du serveur : 8.2.0
-- Version de PHP : 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `todolist`
--
CREATE DATABASE IF NOT EXISTS `todolist` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `todolist`;

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id_task` int NOT NULL,
  `id_theme` int NOT NULL,
  PRIMARY KEY (`id_task`,`id_theme`),
  KEY `id_theme` (`id_theme`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `color`
--

DROP TABLE IF EXISTS `color`;
CREATE TABLE IF NOT EXISTS `color` (
  `id_color` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `hex_value` varchar(10) NOT NULL,
  PRIMARY KEY (`id_color`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `color`
--

INSERT INTO `color` (`id_color`, `name`, `hex_value`) VALUES
(1, 'green', '#216e4e'),
(2, 'yellow', '#7f5f01'),
(3, 'orange', '#a54800'),
(4, 'red', '#ae2e24'),
(5, 'purple', '#5e4db2'),
(6, 'blue', '#0055cc'),
(7, 'blue-light', '#206a83'),
(8, 'green-light', '#4c6b1f'),
(9, 'pink', '#943d73'),
(10, 'grey', '#596773');

-- --------------------------------------------------------

--
-- Structure de la table `msg`
--

DROP TABLE IF EXISTS `msg`;
CREATE TABLE IF NOT EXISTS `msg` (
  `id_msg` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id_msg`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `msg`
--

INSERT INTO `msg` (`id_msg`, `name`) VALUES
(1, 'Tâche effectuée &#x1F60E'),
(2, 'Tâche supprimée &#x1F6AE'),
(3, 'Tâche modifiée &#x1F609'),
(4, 'Tâche ajoutée &#x1F605'),
(5, 'La tâche ne peut pas être déplacée &#x1F937'),
(6, 'Alerte ajoutée &#x23F0'),
(7, 'Thème ajouté à la tâche &#x1F4AB'),
(8, 'Thème supprimé de la tâche &#x1F6AE'),
(9, 'Alerte supprimée &#x1F6AE'),
(10, 'Couleur ajoutée à la tâche &#x1F57A'),
(11, 'Couleur retirée de la tâche &#x1F926'),
(12, 'Utilisateur créé avec succès &#x1F440'),
(13, 'L\'ajout d\'un utilisateur a échoué &#x1F643'),
(14, 'Thème supprimé &#x1F4A2'),
(15, 'Thème ajouté &#x1F3AD'),
(16, 'Nom du thème modifié &#x1F91D'),
(17, 'error_referer &#x1F608'),
(18, 'error_token &#x1F47D'),
(19, 'Aucune action demandée &#x1F933'),
(20, 'Couleur du thème modifiée &#x1F64C'),
(21, 'Couleur supprimée &#x1F4A5'),
(22, 'Couleur modifiée &#x1F388'),
(23, 'Couleur ajoutée &#x1F31E');

-- --------------------------------------------------------

--
-- Structure de la table `task`
--

DROP TABLE IF EXISTS `task`;
CREATE TABLE IF NOT EXISTS `task` (
  `id_task` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `creation_date` datetime NOT NULL,
  `alarm_date` datetime DEFAULT NULL,
  `done_date` datetime DEFAULT NULL,
  `state` tinyint(1) NOT NULL,
  `priority` smallint NOT NULL,
  `id_color` int DEFAULT NULL,
  PRIMARY KEY (`id_task`),
  KEY `id_color` (`id_color`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `task`
--

INSERT INTO `task` (`id_task`, `name`, `creation_date`, `alarm_date`, `done_date`, `state`, `priority`, `id_color`) VALUES
(1, 'Passer l\'aspirateur', '2023-11-10 23:26:15', NULL, NULL, 0, 1, NULL),
(2, 'Faire la vaisselle', '2023-11-06 12:05:23', NULL, NULL, 0, 2, NULL),
(3, 'Prendre rdv chez le dentiste', '2023-11-01 18:01:02', NULL, NULL, 0, 3, NULL),
(4, 'Prendre rdv chez le médecin', '2023-10-25 05:12:56', NULL, '2023-11-02 12:05:12', 1, 0, NULL),
(5, 'Faire la vidange', '2023-10-10 17:32:50', NULL, '2023-10-12 22:17:23', 1, 0, NULL),
(6, 'Regarder pour les vacances', '2023-10-06 07:12:42', NULL, NULL, 0, 4, NULL),
(7, 'Tester l\'appli', '2023-11-01 23:12:25', NULL, NULL, 0, 5, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `theme`
--

DROP TABLE IF EXISTS `theme`;
CREATE TABLE IF NOT EXISTS `theme` (
  `id_theme` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id_theme`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `theme`
--

INSERT INTO `theme` (`id_theme`, `name`) VALUES
(1, 'Travail'),
(2, 'Projet web'),
(3, 'Maison'),
(4, 'Recherche de stage'),
(5, 'Vacances'),
(6, 'Travaux'),
(7, 'Important');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- User creation
CREATE USER 'test-user'@'localhost' IDENTIFIED BY 'test-password';
GRANT ALL ON todolist.* TO 'test-user'@'localhost';
FLUSH PRIVILEGES;