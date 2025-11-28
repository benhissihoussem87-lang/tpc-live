-- TPC DB Patch – Invoice/Offer Keys and FKs
-- You can run this section by section. If an index/FK already exists,
-- MySQL may warn about duplicates—skip those lines and continue.

-- 0) Use the correct DB (change if different)
USE `tpc`;

-- Optional sanity check
SHOW TABLES;

-- 1) Column widths (unify to VARCHAR(32) for num_fact-like fields)
ALTER TABLE `archivefacture`
  MODIFY COLUMN `num_fact` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE `bordereaux`
  MODIFY COLUMN `num_fact` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE `reglement`
  MODIFY COLUMN `num_fact` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE `offre_prix`
  MODIFY COLUMN `num_offre` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE `bon_commande`
  MODIFY COLUMN `num_bon_commande` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE `facture_projets`
  MODIFY COLUMN `facture` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;

-- 2) Helpful indexes (ignore duplicate warnings)
ALTER TABLE `bordereaux`      ADD INDEX `idx_bord_num_fact` (`num_fact`);
ALTER TABLE `reglement`       ADD INDEX `idx_regl_num_fact` (`num_fact`);
ALTER TABLE `bon_commande`    ADD INDEX `idx_bon_cmd_num`   (`num_bon_commande`);
ALTER TABLE `facture_projets` ADD INDEX `idx_fp_facture`    (`facture`);

-- 3) Data cleanup (safe, idempotent)
UPDATE `bordereaux`      SET `num_fact` = TRIM(`num_fact`);
UPDATE `reglement`       SET `num_fact` = TRIM(`num_fact`);
UPDATE `bon_commande`    SET `num_bon_commande` = TRIM(`num_bon_commande`);
UPDATE `facture_projets` SET `facture` = TRIM(`facture`);

-- Orphan checks (should ideally return 0 rows)
SELECT bc.id, bc.num_bon_commande
FROM bon_commande AS bc LEFT JOIN facture AS f ON bc.num_bon_commande = f.num_fact
WHERE f.num_fact IS NULL;

SELECT b.id, b.num_fact
FROM bordereaux AS b LEFT JOIN facture AS f ON b.num_fact = f.num_fact
WHERE f.num_fact IS NULL;

SELECT fp.id_Projets_Facture, fp.facture
FROM facture_projets AS fp LEFT JOIN facture AS f ON fp.facture = f.num_fact
WHERE f.num_fact IS NULL;

-- 4) Foreign keys (drop if exists; names may differ per DB)
-- bordereaux -> facture (CASCADE)
SET @__fk := (SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bordereaux'
                AND REFERENCED_TABLE_NAME = 'facture' LIMIT 1);
SET @__sql := IF(@__fk IS NOT NULL, CONCAT('ALTER TABLE `bordereaux` DROP FOREIGN KEY `', @__fk, '`'), NULL);
PREPARE __stmt FROM @__sql; EXECUTE __stmt; DEALLOCATE PREPARE __stmt;
ALTER TABLE `bordereaux`
  ADD CONSTRAINT `fk_bordereaux_facture_num2`
  FOREIGN KEY (`num_fact`) REFERENCES `facture` (`num_fact`)
  ON UPDATE CASCADE ON DELETE CASCADE;

-- reglement -> facture (CASCADE)
SET @__fk := (SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reglement'
                AND REFERENCED_TABLE_NAME = 'facture' LIMIT 1);
SET @__sql := IF(@__fk IS NOT NULL, CONCAT('ALTER TABLE `reglement` DROP FOREIGN KEY `', @__fk, '`'), NULL);
PREPARE __stmt FROM @__sql; EXECUTE __stmt; DEALLOCATE PREPARE __stmt;
ALTER TABLE `reglement`
  ADD CONSTRAINT `reglement_ibfk_1`
  FOREIGN KEY (`num_fact`) REFERENCES `facture` (`num_fact`)
  ON UPDATE CASCADE ON DELETE CASCADE;

-- bon_commande -> facture (CASCADE)
SET @__fk := (SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bon_commande'
                AND REFERENCED_TABLE_NAME = 'facture' LIMIT 1);
SET @__sql := IF(@__fk IS NOT NULL, CONCAT('ALTER TABLE `bon_commande` DROP FOREIGN KEY `', @__fk, '`'), NULL);
PREPARE __stmt FROM @__sql; EXECUTE __stmt; DEALLOCATE PREPARE __stmt;
ALTER TABLE `bon_commande`
  ADD CONSTRAINT `bon_commande_ibfk_1`
  FOREIGN KEY (`num_bon_commande`) REFERENCES `facture` (`num_fact`)
  ON UPDATE CASCADE ON DELETE CASCADE;

-- facture_projets -> facture (SET NULL)
SET @__fk := (SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'facture_projets'
                AND REFERENCED_TABLE_NAME = 'facture' LIMIT 1);
SET @__sql := IF(@__fk IS NOT NULL, CONCAT('ALTER TABLE `facture_projets` DROP FOREIGN KEY `', @__fk, '`'), NULL);
PREPARE __stmt FROM @__sql; EXECUTE __stmt; DEALLOCATE PREPARE __stmt;
ALTER TABLE `facture_projets`
  ADD CONSTRAINT `fk_facture_projets_facture_num3`
  FOREIGN KEY (`facture`) REFERENCES `facture` (`num_fact`)
  ON UPDATE CASCADE ON DELETE SET NULL;

-- (Optional) quick verification (adjust IDs)
-- START TRANSACTION;
-- INSERT INTO facture (num_fact, client, numboncommande, date, reglement)
-- VALUES ('1000/2025', 4, '', CURDATE(), 'non');
-- INSERT INTO bordereaux (num_bordereaux, date, num_fact, pieces_jointe, type, adresse_bordereaux)
-- VALUES ('BR-999', DATE_FORMAT(CURDATE(), '%Y-%m-%d'), '1000/2025', '', 'X', 'Test');
-- INSERT INTO facture_projets (facture, prix_unit_htv, qte, tva, remise, prixForfitaire, prixTTC, projet, adresseClient)
-- VALUES ('1000/2025', '10', '2', '19', '0', '', '23.8', 1, 'Test Addr');
-- SELECT * FROM bordereaux WHERE num_fact = '1000/2025';
-- DELETE FROM facture WHERE num_fact = '1000/2025';
-- SELECT COUNT(*) AS bordereaux_left FROM bordereaux WHERE num_fact = '1000/2025';
-- SELECT id_Projets_Facture, facture FROM facture_projets ORDER BY id_Projets_Facture DESC LIMIT 3;
-- ROLLBACK;

