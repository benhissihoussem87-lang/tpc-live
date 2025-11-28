-- Create table facture_avoir if missing
CREATE TABLE IF NOT EXISTS `facture_avoir` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `num_avoir` VARCHAR(32) NOT NULL,
  `num_fact` VARCHAR(32) NULL,
  `id_client` INT NOT NULL,
  `date_avoir` DATE NOT NULL,
  `total_ht` DECIMAL(12,3) NOT NULL DEFAULT 0,
  `total_tva` DECIMAL(12,3) NOT NULL DEFAULT 0,
  `total_ttc` DECIMAL(12,3) NOT NULL DEFAULT 0,
  `matriculeFiscale` VARCHAR(64) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_num_avoir` (`num_avoir`),
  KEY `idx_num_fact` (`num_fact`),
  KEY `idx_id_client` (`id_client`),
  CONSTRAINT `fk_facture_avoir_client`
    FOREIGN KEY (`id_client`) REFERENCES `clients`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_facture_avoir_facture`
    FOREIGN KEY (`num_fact`) REFERENCES `facture`(`num_fact`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
