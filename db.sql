-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : jeu. 20 nov. 2025 à 16:39
-- Version du serveur : 10.3.39-MariaDB
-- Version de PHP : 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `e40250u_sae301`
--

-- --------------------------------------------------------

--
-- Structure de la table `Favori`
--

CREATE TABLE `Favori` (
  `id_favori` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `ref_parking_api` varchar(50) NOT NULL COMMENT 'Identifiant JSON (ex: pub_tsp_sta.159)',
  `nom_custom` varchar(100) DEFAULT NULL COMMENT 'Surnom donné par l utilisateur'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Favori`
--

INSERT INTO `Favori` (`id_favori`, `id_utilisateur`, `ref_parking_api`, `nom_custom`) VALUES
(1, 1, 'pub_tsp_sta.159', 'P+R Woippy (Accessible)'),
(2, 2, 'pub_tsp_sta.93', 'Parking Sport Gratuit'),
(3, 3, 'pub_tsp_sta.114', 'Maud Huy Centre');

-- --------------------------------------------------------

--
-- Structure de la table `Historique`
--

CREATE TABLE `Historique` (
  `id_historique` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `ref_parking_api` varchar(50) NOT NULL,
  `date_recherche` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Historique`
--

INSERT INTO `Historique` (`id_historique`, `id_utilisateur`, `ref_parking_api`, `date_recherche`) VALUES
(1, 1, 'pub_tsp_sta.159', '2023-10-25 08:30:00'),
(2, 2, 'pub_tsp_sta.93', '2023-10-26 18:15:00');

-- --------------------------------------------------------

--
-- Structure de la table `Ref_Motorisation`
--

CREATE TABLE `Ref_Motorisation` (
  `id_motorisation` int(11) NOT NULL,
  `libelle_moto` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Ref_Motorisation`
--

INSERT INTO `Ref_Motorisation` (`id_motorisation`, `libelle_moto`) VALUES
(2, 'Electrique'),
(3, 'Hybride'),
(4, 'Sans moteur'),
(1, 'Thermique (Essence/Diesel)');

-- --------------------------------------------------------

--
-- Structure de la table `Ref_Type_Vehicule`
--

CREATE TABLE `Ref_Type_Vehicule` (
  `id_type_veh` int(11) NOT NULL,
  `libelle_type` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Ref_Type_Vehicule`
--

INSERT INTO `Ref_Type_Vehicule` (`id_type_veh`, `libelle_type`) VALUES
(2, 'Moto'),
(3, 'Velo'),
(1, 'Voiture');

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateur`
--

CREATE TABLE `Utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `est_pmr` tinyint(1) DEFAULT 0 COMMENT '0: Non, 1: Oui (Carte Mobilité Inclusion)',
  `preference_cout` enum('GRATUIT','PAYANT','INDIFFERENT') DEFAULT 'INDIFFERENT'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Utilisateur`
--

INSERT INTO `Utilisateur` (`id_utilisateur`, `email`, `mot_de_passe`, `pseudo`, `est_pmr`, `preference_cout`) VALUES
(1, 'alice.pmr@test.fr', 'hash_secret_1', 'Alice57', 1, 'INDIFFERENT'),
(2, 'bob.elec@test.fr', 'hash_secret_2', 'BobTesla', 0, 'GRATUIT'),
(3, 'charlie.etudiant@test.fr', 'hash_secret_3', 'CharlyMoto', 0, 'PAYANT');

-- --------------------------------------------------------

--
-- Structure de la table `Vehicule`
--

CREATE TABLE `Vehicule` (
  `id_vehicule` int(11) NOT NULL,
  `nom_vehicule` varchar(50) DEFAULT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_type_veh` int(11) NOT NULL,
  `id_motorisation` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Vehicule`
--

INSERT INTO `Vehicule` (`id_vehicule`, `nom_vehicule`, `id_utilisateur`, `id_type_veh`, `id_motorisation`) VALUES
(1, 'Clio Adaptée', 1, 1, 1),
(2, 'Model 3', 2, 1, 2),
(3, 'Yamaha MT07', 3, 2, 1),
(4, 'VTT Rockrider', 2, 3, 4);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Favori`
--
ALTER TABLE `Favori`
  ADD PRIMARY KEY (`id_favori`),
  ADD KEY `fk_fav_user` (`id_utilisateur`);

--
-- Index pour la table `Historique`
--
ALTER TABLE `Historique`
  ADD PRIMARY KEY (`id_historique`),
  ADD KEY `fk_hist_user` (`id_utilisateur`);

--
-- Index pour la table `Ref_Motorisation`
--
ALTER TABLE `Ref_Motorisation`
  ADD PRIMARY KEY (`id_motorisation`),
  ADD UNIQUE KEY `libelle_moto` (`libelle_moto`);

--
-- Index pour la table `Ref_Type_Vehicule`
--
ALTER TABLE `Ref_Type_Vehicule`
  ADD PRIMARY KEY (`id_type_veh`),
  ADD UNIQUE KEY `libelle_type` (`libelle_type`);

--
-- Index pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `Vehicule`
--
ALTER TABLE `Vehicule`
  ADD PRIMARY KEY (`id_vehicule`),
  ADD KEY `fk_veh_user` (`id_utilisateur`),
  ADD KEY `fk_veh_type` (`id_type_veh`),
  ADD KEY `fk_veh_moto` (`id_motorisation`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Favori`
--
ALTER TABLE `Favori`
  MODIFY `id_favori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `Historique`
--
ALTER TABLE `Historique`
  MODIFY `id_historique` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `Ref_Motorisation`
--
ALTER TABLE `Ref_Motorisation`
  MODIFY `id_motorisation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `Ref_Type_Vehicule`
--
ALTER TABLE `Ref_Type_Vehicule`
  MODIFY `id_type_veh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `Vehicule`
--
ALTER TABLE `Vehicule`
  MODIFY `id_vehicule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Favori`
--
ALTER TABLE `Favori`
  ADD CONSTRAINT `fk_fav_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Historique`
--
ALTER TABLE `Historique`
  ADD CONSTRAINT `fk_hist_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Vehicule`
--
ALTER TABLE `Vehicule`
  ADD CONSTRAINT `fk_veh_moto` FOREIGN KEY (`id_motorisation`) REFERENCES `Ref_Motorisation` (`id_motorisation`),
  ADD CONSTRAINT `fk_veh_type` FOREIGN KEY (`id_type_veh`) REFERENCES `Ref_Type_Vehicule` (`id_type_veh`),
  ADD CONSTRAINT `fk_veh_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
