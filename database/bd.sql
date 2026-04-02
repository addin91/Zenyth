CREATE DATABASE IF NOT EXISTS zenyth;

-- clients
CREATE TABLE IF NOT EXISTS `clients` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) UNIQUE,
    `mot_de_passe` VARCHAR(255) NOT NULL,
    `statut_compte` ENUM('invite','actif','inactif') NOT NULL DEFAULT 'invite',
    `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    CHECK (`email` LIKE '%@%')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- clients
CREATE TABLE IF NOT EXISTS `admin` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) UNIQUE,
    `mot_de_passe` VARCHAR(255) NOT NULL,
    `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    CHECK (`email` LIKE '%@%')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- animateurs
CREATE TABLE IF NOT EXISTS `animateurs` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `specialite` VARCHAR(150) DEFAULT NULL,
    `actif` BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- reservations
CREATE TABLE IF NOT EXISTS `reservations` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `id_client` INT UNSIGNED DEFAULT NULL,
    `date_debut` DATE NOT NULL,
    `date_fin` DATE NOT NULL,
    `nombre_personnes` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `statut` ENUM('en_attente','validee','refusee') NOT NULL DEFAULT 'en_attente',
    `commentaire` TEXT DEFAULT NULL,
    `date_demande` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_client` (`id_client`),
    INDEX `idx_statut` (`statut`),
    INDEX `idx_dates` (`date_debut`, `date_fin`),
    CONSTRAINT `fk_res_client`
        FOREIGN KEY (`id_client`) REFERENCES `clients` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CHECK (`date_fin` >= `date_debut`),
    CHECK (`nombre_personnes` > 0 AND `nombre_personnes` < 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- chambres
CREATE TABLE IF NOT EXISTS `chambres` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `nom_chambre` VARCHAR(100) NOT NULL,
    `type_chambre` VARCHAR(80) NOT NULL,
    `capacite` TINYINT UNSIGNED NOT NULL DEFAULT 2,
    `prix_nuit` DECIMAL(8,2) NOT NULL,
    `statut` ENUM('libre','occupe') NOT NULL DEFAULT 'libre',
    INDEX `idx_type` (`type_chambre`),
    INDEX `idx_statut` (`statut`),
    CHECK (`capacite` > 0 AND `capacite` < 10),
    CHECK (`prix_nuit` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- reservation_chambres
CREATE TABLE IF NOT EXISTS `reservation_chambres` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `id_reservation` INT UNSIGNED NOT NULL,
    `id_chambre` INT UNSIGNED NOT NULL,
    UNIQUE KEY `uq_res_chambre` (`id_reservation`, `id_chambre`),
    INDEX `idx_chambre` (`id_chambre`),
    CONSTRAINT `fk_rc_reservation`
        FOREIGN KEY (`id_reservation`) REFERENCES `reservations` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_rc_chambre`
        FOREIGN KEY (`id_chambre`) REFERENCES `chambres` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- prestations
CREATE TABLE IF NOT EXISTS `prestations` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `nom` VARCHAR(150) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `prix_unitaire` DECIMAL(8,2) NOT NULL,
    `actif` BOOLEAN NOT NULL DEFAULT FALSE,
    CHECK (`prix_unitaire` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- reservation_prestations
CREATE TABLE IF NOT EXISTS `reservation_prestations` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `id_reservation` INT UNSIGNED NOT NULL,
    `id_prestation` INT UNSIGNED NOT NULL,
    `quantite` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `reduction` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    INDEX `idx_reservation` (`id_reservation`),
    INDEX `idx_prestation` (`id_prestation`),
    CONSTRAINT `fk_rp_reservation`
        FOREIGN KEY (`id_reservation`) REFERENCES `reservations` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_rp_prestation`
        FOREIGN KEY (`id_prestation`) REFERENCES `prestations` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CHECK (`quantite` > 0 AND `quantite` < 10),
    CHECK (`reduction` BETWEEN 0 AND 100),
    CHECK (`total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- activites
CREATE TABLE IF NOT EXISTS `activites` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `nom` VARCHAR(150) NOT NULL,
    `type` VARCHAR(80) DEFAULT NULL,
    `duree` SMALLINT UNSIGNED DEFAULT NULL,
    `capacite_min` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `capacite_max` TINYINT UNSIGNED NOT NULL DEFAULT 20,
    `prix` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `actif` BOOLEAN NOT NULL DEFAULT FALSE,
    INDEX `idx_type` (`type`),
    CHECK (`capacite_min` > 0),
    CHECK (`capacite_max` >= `capacite_min`),
    CHECK (`prix` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- demandes_activites
CREATE TABLE IF NOT EXISTS `demandes_activites` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `id_reservation` INT UNSIGNED NOT NULL,
    `id_activite` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `creneau` TIME DEFAULT NULL,
    `nombre_personnes_concernees` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `message` TEXT DEFAULT NULL,
    INDEX `idx_reservation` (`id_reservation`),
    INDEX `idx_activite` (`id_activite`),
    INDEX `idx_date` (`date`),
    CONSTRAINT `fk_da_reservation`
        FOREIGN KEY (`id_reservation`) REFERENCES `reservations` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_da_activite`
        FOREIGN KEY (`id_activite`) REFERENCES `activites` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CHECK (`nombre_personnes_concernees` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- activites_prevues
CREATE TABLE IF NOT EXISTS `activites_prevues` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `id_activite` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `creneau` TIME NOT NULL,
    `id_animateur` INT UNSIGNED DEFAULT NULL,
    `message` TEXT DEFAULT NULL,
    `capacite_restante` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_activite` (`id_activite`),
    INDEX `idx_animateur` (`id_animateur`),
    INDEX `idx_date` (`date`),
    CONSTRAINT `fk_ap_activite`
        FOREIGN KEY (`id_activite`) REFERENCES `activites` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_ap_animateur`
        FOREIGN KEY (`id_animateur`) REFERENCES `animateurs` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CHECK (`capacite_restante` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- factures
CREATE TABLE IF NOT EXISTS `factures` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `id_reservation` INT UNSIGNED NOT NULL UNIQUE,
    `montant_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `avoirs` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `reductions` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `montant_final` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `statut` ENUM('brouillon','emise','payee','annulee') NOT NULL DEFAULT 'brouillon',
    `date_emission` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_fac_reservation`
        FOREIGN KEY (`id_reservation`) REFERENCES `reservations` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CHECK (`montant_total` >= 0),
    CHECK (`montant_final` >= 0),
    CHECK (`avoirs` >= 0),
    CHECK (`reductions` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
