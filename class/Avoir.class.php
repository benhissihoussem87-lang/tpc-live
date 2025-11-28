<?php
include_once 'connexion.db.php';

class Avoir {
    private $cnx;
    public function __construct() {
        $this->cnx = connexion();
        $this->ensureSchemaUpToDate();
    }

    public function getAll() {
        try {
            $sql = "SELECT a.*, c.nom_client \n                    FROM facture_avoir a \n                    JOIN clients c ON a.id_client = c.id \n                    ORDER BY a.date_avoir DESC, a.id DESC";
            $st = $this->cnx->query($sql);
            $rows = $st->fetchAll();
            // Backward-compatible alias: expose num_facture_nouveux in addition to num_fact_new
            return array_map(function(array $row) {
                if (isset($row['num_fact_new']) && !isset($row['num_facture_nouveux'])) {
                    $row['num_facture_nouveux'] = $row['num_fact_new'];
                }
                return $row;
            }, $rows);
        } catch (\PDOException $e) {
            // If table doesn't exist yet, return empty list gracefully
            $msg = $e->getMessage();
            if (strpos($msg, '42S02') !== false || stripos($msg, 'Base table or view not found') !== false) {
                return [];
            }
            throw $e;
        }
    }

    public function getById($id) {
        $sql = "SELECT a.*, c.nom_client, c.adresse, a.matriculeFiscale AS mf_avoir, c.matriculeFiscale AS mf_client \n                FROM facture_avoir a \n                JOIN clients c ON a.id_client = c.id \n                WHERE a.id = :id";
        $st = $this->cnx->prepare($sql);
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        if ($row && isset($row['num_fact_new']) && !isset($row['num_facture_nouveux'])) {
            $row['num_facture_nouveux'] = $row['num_fact_new'];
        }
        return $row;
    }

    public function create(array $data) {
        $numFactNew = $data['num_facture_nouveux'] ?? ($data['num_fact_new'] ?? null);
        $params = [
            ':num_avoir' => $data['num_avoir'] ?? null,
            ':num_fact'  => $data['num_fact']  ?? null,
            ':num_fact_new' => $numFactNew,
            ':id_client' => $data['id_client'],
            ':date_avoir'=> $data['date_avoir'],
            ':total_ht'  => $data['total_ht']  ?? 0,
            ':total_tva' => $data['total_tva'] ?? 0,
            ':total_ttc' => $data['total_ttc'] ?? 0,
            ':type_avoir' => $data['type_avoir'] ?? null,
            ':pourcentage' => isset($data['pourcentage']) && $data['pourcentage'] !== '' ? (float)$data['pourcentage'] : null,
            ':matriculeFiscale' => ($data['matriculeFiscale'] ?? null),
        ];
        $sql = "INSERT INTO facture_avoir
                (num_avoir, num_fact, num_fact_new, id_client, date_avoir, total_ht, total_tva, total_ttc, type_avoir, pourcentage, matriculeFiscale)
                VALUES (:num_avoir, :num_fact, :num_fact_new, :id_client, :date_avoir, :total_ht, :total_tva, :total_ttc, :type_avoir, :pourcentage, :matriculeFiscale)";
        try {
            $st = $this->cnx->prepare($sql);
            $result = $st->execute($params);
            if ($result) {
                $avoirId = (int)$this->cnx->lastInsertId();
                if (!empty($data['lines']) && is_array($data['lines'])) {
                    $this->saveLines($avoirId, $data['lines']);
                }
                return $avoirId ?: true;
            }
            return false;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '42S02') !== false || stripos($msg, 'Base table or view not found') !== false) {
                $this->bootstrapSchema();
                $st = $this->cnx->prepare($sql);
                $result = $st->execute($params);
                if ($result) {
                    $avoirId = (int)$this->cnx->lastInsertId();
                    if (!empty($data['lines']) && is_array($data['lines'])) {
                        $this->saveLines($avoirId, $data['lines']);
                    }
                    return $avoirId ?: true;
                }
                return false;
            }
            throw $e;
        }
    }

    private function bootstrapSchema(): void {
        $ddl = <<<SQL
CREATE TABLE IF NOT EXISTS `facture_avoir` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `num_avoir` VARCHAR(32) NOT NULL,
  `num_fact` VARCHAR(32) NULL,
  `num_fact_new` VARCHAR(32) NULL,
  `id_client` INT NOT NULL,
  `date_avoir` DATE NOT NULL,
  `total_ht` DECIMAL(12,3) NOT NULL DEFAULT 0,
  `total_tva` DECIMAL(12,3) NOT NULL DEFAULT 0,
  `total_ttc` DECIMAL(12,3) NOT NULL DEFAULT 0,
  `type_avoir` VARCHAR(16) DEFAULT NULL,
  `pourcentage` DECIMAL(6,3) DEFAULT NULL,
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
SQL;
        $this->cnx->exec($ddl);
    }

    private function bootstrapLinesSchema(): void {
        $ddl = <<<SQL
CREATE TABLE IF NOT EXISTS `facture_avoir_lignes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `avoir_id` INT NOT NULL,
  `projet_id` INT DEFAULT NULL,
  `libelle` VARCHAR(1000) DEFAULT NULL,
  `prix_unit_htv` DECIMAL(12,3) NOT NULL DEFAULT 0,
  `qte` DECIMAL(12,3) NOT NULL DEFAULT 0,
  `tva` DECIMAL(6,3) NOT NULL DEFAULT 19,
  `prixForfitaire` DECIMAL(12,3) DEFAULT NULL,
  `prixTTC` DECIMAL(12,3) DEFAULT NULL,
  `adresseClient` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `avoir_idx` (`avoir_id`),
  CONSTRAINT `fk_facture_avoir_lignes` FOREIGN KEY (`avoir_id`) REFERENCES `facture_avoir`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;
        $this->cnx->exec($ddl);
    }

    /**
     * Ensure existing installations get new columns without manual SQL.
     * - Adds `num_fact_new`, `type_avoir`, and `pourcentage` to `facture_avoir` if they do not exist.
     */
    private function ensureSchemaUpToDate(): void {
        // Ensure base table exists
        try {
            $this->cnx->query("SELECT 1 FROM facture_avoir LIMIT 1");
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '42S02') !== false || stripos($msg, 'Base table or view not found') !== false) {
                $this->bootstrapSchema();
            } else {
                throw $e;
            }
        }

        // Add num_fact_new column if missing
        try {
            $this->cnx->query("SELECT `num_fact_new` FROM facture_avoir LIMIT 1");
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '1054') !== false || stripos($msg, 'Unknown column') !== false) {
                try {
                    $this->cnx->exec("ALTER TABLE facture_avoir ADD COLUMN `num_fact_new` VARCHAR(32) NULL AFTER `num_fact`");
                } catch (\PDOException $e2) {
                    // Ignore if column already added or ALTER not permitted
                }
            } else {
                throw $e;
            }
        }

        // Add type_avoir column if missing
        try {
            $this->cnx->query("SELECT `type_avoir` FROM facture_avoir LIMIT 1");
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '1054') !== false || stripos($msg, 'Unknown column') !== false) {
                try {
                    $this->cnx->exec("ALTER TABLE facture_avoir ADD COLUMN `type_avoir` VARCHAR(16) DEFAULT NULL AFTER `total_ttc`");
                } catch (\PDOException $e2) {
                    // Ignore if column already added or ALTER not permitted
                }
            } else {
                throw $e;
            }
        }

        // Add pourcentage column if missing
        try {
            $this->cnx->query("SELECT `pourcentage` FROM facture_avoir LIMIT 1");
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '1054') !== false || stripos($msg, 'Unknown column') !== false) {
                try {
                    $this->cnx->exec("ALTER TABLE facture_avoir ADD COLUMN `pourcentage` DECIMAL(6,3) DEFAULT NULL AFTER `type_avoir`");
                } catch (\PDOException $e2) {
                    // Ignore if column already added or ALTER not permitted
                }
            } else {
                throw $e;
            }
        }
    }

    private function convertToUtf8($value) {
        if (!is_string($value) || $value === '') {
            return $value;
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        }
        if (function_exists('iconv')) {
            $converted = @iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $value);
            if ($converted !== false) {
                return $converted;
            }
        }
        return $value;
    }

    private function saveLines(int $avoirId, array $lines): void {
        $this->bootstrapSchema();
        $this->bootstrapLinesSchema();
        $del = $this->cnx->prepare("DELETE FROM facture_avoir_lignes WHERE avoir_id = :id");
        $del->execute([':id' => $avoirId]);

        $insert = $this->cnx->prepare(
            "INSERT INTO facture_avoir_lignes (avoir_id, projet_id, libelle, prix_unit_htv, qte, tva, prixForfitaire, prixTTC, adresseClient)
             VALUES (:avoir_id, :projet_id, :libelle, :pu, :qte, :tva, :prixForfitaire, :prixTTC, :adresse)"
        );

        foreach ($lines as $line) {
            $insert->execute([
                ':avoir_id' => $avoirId,
                ':projet_id' => isset($line['projet']) && $line['projet'] !== '' ? (int)$line['projet'] : null,
                ':libelle' => $this->convertToUtf8($line['projet_label'] ?? ''),
                ':pu' => isset($line['prix_unit_htv']) && is_numeric($line['prix_unit_htv']) ? (float)$line['prix_unit_htv'] : 0,
                ':qte' => isset($line['qte']) && is_numeric($line['qte']) ? (float)$line['qte'] : 0,
                ':tva' => isset($line['tva']) && is_numeric($line['tva']) ? (float)$line['tva'] : 0,
                ':prixForfitaire' => ($line['prixForfitaire'] === '' || $line['prixForfitaire'] === null) ? null : (float)$line['prixForfitaire'],
                ':prixTTC' => ($line['prixTTC'] === '' || $line['prixTTC'] === null) ? null : (float)$line['prixTTC'],
                ':adresse' => $this->convertToUtf8($line['adresseClient'] ?? ''),
            ]);
        }
    }

    public function getLines(int $avoirId): array {
        try {
            $this->bootstrapLinesSchema();
            $st = $this->cnx->prepare("SELECT * FROM facture_avoir_lignes WHERE avoir_id = :id ORDER BY id ASC");
            $st->execute([':id' => $avoirId]);
            $rows = $st->fetchAll();

            // Normalize column names so legacy templates can use the in-memory buffer directly.
            return array_map(function(array $row) {
                return [
                    'id'             => isset($row['id']) ? (int)$row['id'] : null,
                    'avoir_id'       => isset($row['avoir_id']) ? (int)$row['avoir_id'] : null,
                    'projet'         => $row['projet_id'] !== null ? (int)$row['projet_id'] : null,
                    'projet_label'   => $this->convertToUtf8($row['libelle'] ?? ''),
                    'prix_unit_htv'  => isset($row['prix_unit_htv']) ? (float)$row['prix_unit_htv'] : 0,
                    'qte'            => isset($row['qte']) ? (float)$row['qte'] : 0,
                    'tva'            => isset($row['tva']) ? (float)$row['tva'] : 0,
                    'prixForfitaire' => $row['prixForfitaire'],
                    'prixTTC'        => $row['prixTTC'],
                    'adresseClient'  => $this->convertToUtf8($row['adresseClient'] ?? ''),
                ];
            }, $rows);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '42S02') !== false || stripos($msg, 'Base table or view not found') !== false) {
                return [];
            }
            throw $e;
        }
    }

    public function delete($id) {
        $st = $this->cnx->prepare("DELETE FROM facture_avoir WHERE id = :id");
        return $st->execute([':id' => $id]);
    }

    public function update(int $id, array $data) {
        $numFactNew = $data['num_facture_nouveux'] ?? ($data['num_fact_new'] ?? null);
        $sql = "UPDATE facture_avoir SET
                  num_avoir = :num_avoir,
                  num_fact = :num_fact,
                  num_fact_new = :num_fact_new,
                  id_client = :id_client,
                  date_avoir = :date_avoir,
                  total_ht = :total_ht,
                  total_tva = :total_tva,
                  total_ttc = :total_ttc,
                  type_avoir = :type_avoir,
                  pourcentage = :pourcentage,
                  matriculeFiscale = :matriculeFiscale
                WHERE id = :id";
        $st = $this->cnx->prepare($sql);
        $result = $st->execute([
            ':num_avoir' => $data['num_avoir'],
            ':num_fact'  => ($data['num_fact'] ?? null),
            ':num_fact_new' => $numFactNew,
            ':id_client' => $data['id_client'],
            ':date_avoir'=> $data['date_avoir'],
            ':total_ht'  => $data['total_ht'],
            ':total_tva' => $data['total_tva'],
            ':total_ttc' => $data['total_ttc'],
            ':type_avoir' => $data['type_avoir'] ?? null,
            ':pourcentage' => isset($data['pourcentage']) && $data['pourcentage'] !== '' ? (float)$data['pourcentage'] : null,
            ':matriculeFiscale' => ($data['matriculeFiscale'] ?? null),
            ':id' => $id,
        ]);

        if ($result && !empty($data['lines']) && is_array($data['lines'])) {
            $this->saveLines($id, $data['lines']);
        }
        return $result;
    }

    public function nextNumber(?int $year = null): string {
        $year4 = (string)($year ?? (int)date('Y'));
        $year2 = substr($year4, -2);
        try {
            $st = $this->cnx->prepare(
                "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(num_avoir,'/',1) AS UNSIGNED)),0)
                 FROM facture_avoir WHERE num_avoir LIKE :sfx2 OR num_avoir LIKE :sfx4"
            );
            $st->execute([':sfx2' => '%/'.$year2, ':sfx4' => '%/'.$year4]);
            $seq = ((int)$st->fetchColumn()) + 1;
        } catch (\PDOException $e) {
            // If table is missing, default to first sequence for current year
            $msg = $e->getMessage();
            if (strpos($msg, '42S02') !== false || stripos($msg, 'Base table or view not found') !== false) {
                $seq = 1;
            } else {
                throw $e;
            }
        }
        return sprintf('%02d/%s', $seq, $year2);
    }
}

$avoir = new Avoir();

?>
