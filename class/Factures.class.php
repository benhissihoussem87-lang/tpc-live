<?php
include_once 'connexion.db.php';

class Factures {
    private $cnx;
    public function __construct() { $this->cnx = connexion(); }

    // ---------------- Core CRUD ----------------
    public function Ajout($num, $client, $numboncommande, $date, $reglement) {
        // Be explicit about columns to avoid schema/strict-mode mismatches
        $sql = "INSERT INTO facture (`num_fact`,`client`,`numboncommande`,`date`,`reglement`) 
                VALUES (:num, :client, :numbc, :datev, :reglement)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':num' => $num,
            ':client' => $client,
            ':numbc' => $numboncommande,
            ':datev' => $date,
            ':reglement' => $reglement
        ]);
    }

    // Update facture header fields
    public function Modifier($num, $client, $numboncommande, $date, $reglement) {
        $sql = "UPDATE facture
                SET client = :client,
                    numboncommande = :numbc,
                    date = :datev,
                    reglement = :reglement
                WHERE num_fact = :num";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':client' => $client,
            ':numbc' => $numboncommande,
            ':datev' => $date,
            ':reglement' => $reglement,
            ':num' => $num,
        ]);
    }

    public function AjoutProjets_Facture($facture, $prix_unit_htv, $qte, $tva, $remise, $prixForfitaire, $prixTTC, $projet, $adresseClient) {
        // Normalize project and address to satisfy FK/nullability
        $projVal = (isset($projet) && $projet !== '' && is_numeric($projet) && (int)$projet > 0) ? (int)$projet : null;
        $adrVal = isset($adresseClient) ? trim((string)$adresseClient) : null;
        if ($adrVal === '') { $adrVal = null; }

        $sql = "INSERT INTO facture_projets (`facture`,`prix_unit_htv`,`qte`,`tva`,`remise`,`prixForfitaire`,`prixTTC`,`projet`,`adresseClient`)
                VALUES (:facture, :pu, :qte, :tva, :remise, :pf, :pttc, :projet, :adr)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':facture' => $facture,
            ':pu' => $prix_unit_htv,
            ':qte' => $qte,
            ':tva' => $tva,
            ':remise' => $remise,
            ':pf' => $prixForfitaire,
            ':pttc' => $prixTTC,
            ':projet' => $projVal,
            ':adr' => $adrVal
        ]);
    }

    public function deleteFacture($num) {
        // Ensure we remove any linked Bon de Commande when a facture is deleted.
        // Do it in a transaction so we don't leave partial state.
        try {
            $this->cnx->beginTransaction();

            // Remove payment records before dropping the facture itself so
            // orphaned entries don't linger in the `reglement` table.
            $stReg = $this->cnx->prepare("DELETE FROM reglement WHERE num_fact = :num");
            $stReg->execute([':num' => $num]);

            // Remove Bon de Commande tied to this facture number
            // (Many pages use bon_commande.num_bon_commande == facture.num_fact)
            $stBc = $this->cnx->prepare("DELETE FROM bon_commande WHERE num_bon_commande = :num");
            $stBc->execute([':num' => $num]);

            // Also clean detail lines if no FK is present
            $stLines = $this->cnx->prepare("DELETE FROM facture_projets WHERE facture = :num");
            $stLines->execute([':num' => $num]);

            // Finally delete the facture itself
            $st = $this->cnx->prepare("DELETE FROM facture WHERE num_fact = :num");
            $st->execute([':num' => $num]);

            $this->cnx->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->cnx->inTransaction()) {
                $this->cnx->rollBack();
            }
            return false;
        }
    }

    public function deleteFactureByAdresse($num, $adresse, $unused = null) {
        $st = $this->cnx->prepare("DELETE FROM facture_projets WHERE facture = :num AND adresseClient = :adr");
        return $st->execute([':num' => $num, ':adr' => $adresse]);
    }

    public function ModifierAdresseFacture($adresseExiste, $adresseUpdate, $numFacture, $lineId = null, $scope = 'address') {
        $facture = (string)$numFacture;
        $lineId = $lineId !== null ? (int)$lineId : null;
        $scope = ($scope === 'line') ? 'line' : 'address';

        $newValue = trim((string)$adresseUpdate);
        $newValue = ($newValue === '') ? null : $newValue;

        if ($scope === 'line' && $lineId) {
            $st = $this->cnx->prepare("UPDATE facture_projets SET adresseClient = :new WHERE id_Projets_Facture = :id");
            return $st->execute([':new' => $newValue, ':id' => $lineId]);
        }

        $oldValue = trim((string)$adresseExiste);
        if ($oldValue === '') {
            $sql = "UPDATE facture_projets
                    SET adresseClient = :new
                    WHERE facture = :facture AND (adresseClient IS NULL OR adresseClient = '')";
            $params = [':new' => $newValue, ':facture' => $facture];
        } else {
            $sql = "UPDATE facture_projets
                    SET adresseClient = :new
                    WHERE facture = :facture AND adresseClient = :old";
            $params = [':new' => $newValue, ':facture' => $facture, ':old' => $oldValue];
        }
        $st = $this->cnx->prepare($sql);
        return $st->execute($params);
    }

    // Check if a facture already has project lines (optionally scoped to an address)
    public function VerifFacturedansFacturesProjet($facture, $adresseClient = null) {
        $facture = (string)$facture;
        $adr = isset($adresseClient) ? trim((string)$adresseClient) : '';
        if ($adr === '') {
            $sql = "SELECT 1 FROM facture_projets WHERE facture = :f AND (adresseClient IS NULL OR adresseClient = '') LIMIT 1";
            $st = $this->cnx->prepare($sql);
            $st->execute([':f' => $facture]);
        } else {
            $sql = "SELECT 1 FROM facture_projets WHERE facture = :f AND adresseClient = :adr LIMIT 1";
            $st = $this->cnx->prepare($sql);
            $st->execute([':f' => $facture, ':adr' => $adr]);
        }
        return (bool)$st->fetchColumn();
    }

    // Delete all project lines for a facture, optionally restricted to one address
    public function delete_All_Projets_By_FactureAndMultiAdress($facture, $adresseClient = null) {
        $facture = (string)$facture;
        $adr = isset($adresseClient) ? trim((string)$adresseClient) : '';
        if ($adr === '') {
            $st = $this->cnx->prepare("DELETE FROM facture_projets WHERE facture = :f AND (adresseClient IS NULL OR adresseClient = '')");
            return $st->execute([':f' => $facture]);
        } else {
            $st = $this->cnx->prepare("DELETE FROM facture_projets WHERE facture = :f AND adresseClient = :adr");
            return $st->execute([':f' => $facture, ':adr' => $adr]);
        }
    }

    // Fully purge all project lines tied to a facture (any address)
    public function delete_All_Projets_By_Facture($facture) {
        $facture = (string)$facture;
        $st = $this->cnx->prepare("DELETE FROM facture_projets WHERE facture = :f");
        return $st->execute([':f' => $facture]);
    }

    // Insert (used by legacy code named 'ModifierProjets_Facture' after it deletes old lines)
    public function ModifierProjets_Facture($facture, $prix_unit_htv, $qte, $tva, $remise, $prixForfitaire, $prixTTC, $projet, $adresseClient) {
        return $this->AjoutProjets_Facture($facture, $prix_unit_htv, $qte, $tva, $remise, $prixForfitaire, $prixTTC, $projet, $adresseClient);
    }

    // ---------------- Queries ----------------
    public function AfficherFactures() {
        // Order by normalized date (supports both 'YYYY-MM-DD' and 'DD/MM/YYYY')
        // then by numeric part of num_fact to keep the newest numbers at the end.
        $sql = "
            SELECT f.*, clt.nom_client, clt.adresse, clt.numexonoration
            FROM facture AS f
            JOIN clients AS clt ON f.client = clt.id
            ORDER BY
                CASE
                    WHEN f.date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
                        THEN STR_TO_DATE(f.date, '%Y-%m-%d')
                    WHEN f.date REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$'
                        THEN STR_TO_DATE(f.date, '%d/%m/%Y')
                    ELSE NULL
                END ASC,
                CAST(SUBSTRING_INDEX(f.num_fact,'/',1) AS UNSIGNED) ASC
        ";
        $req = $this->cnx->query($sql);
        return $req->fetchAll();
    }

    public function AfficherFacturesNonRegle() {
        // Only show invoices that are NOT paid according to the `reglement` table.
        // Logic: include factures with no reglement row OR etat_reglement not equal to 'oui'/'Oui'.
        $sql = "
            SELECT f.*, clt.nom_client, clt.adresse, clt.numexonoration
            FROM facture AS f
            JOIN clients AS clt ON f.client = clt.id
            LEFT JOIN reglement AS r ON r.num_fact = f.num_fact
            WHERE LOWER(TRIM(COALESCE(r.etat_reglement, 'non'))) NOT IN ('oui','avoir')
            ORDER BY
                CASE
                    WHEN f.date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
                        THEN STR_TO_DATE(f.date, '%Y-%m-%d')
                    WHEN f.date REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$'
                        THEN STR_TO_DATE(f.date, '%d/%m/%Y')
                    ELSE NULL
                END ASC,
                CAST(SUBSTRING_INDEX(f.num_fact,'/',1) AS UNSIGNED) ASC
        ";
        $req = $this->cnx->query($sql);
        return $req->fetchAll();
    }

    // Some pages expect a method named AfficherAllFactures()
    public function AfficherAllFactures() {
        $sql = "
            SELECT f.*, clt.nom_client
            FROM facture AS f
            JOIN clients AS clt ON f.client = clt.id
            ORDER BY
                CASE
                    WHEN f.date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
                        THEN STR_TO_DATE(f.date, '%Y-%m-%d')
                    WHEN f.date REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$'
                        THEN STR_TO_DATE(f.date, '%d/%m/%Y')
                    ELSE NULL
                END ASC,
                CAST(SUBSTRING_INDEX(f.num_fact,'/',1) AS UNSIGNED) ASC
        ";
        $req = $this->cnx->query($sql);
        return $req->fetchAll();
    }

    public function GetReglementByFacture($facture) {
        // Mirror logic of Reglements::getReglementByFacture but avoid the
        // id OR num comparison that can pick the wrong row for values like
        // "217/2025". Numeric-only -> search by id; otherwise by num_fact.
        if (is_scalar($facture) && preg_match('/^\d+$/', (string)$facture)) {
            $st = $this->cnx->prepare("SELECT * FROM reglement WHERE id_reglement = :id LIMIT 1");
            $st->execute([':id' => $facture]);
        } else {
            $st = $this->cnx->prepare("SELECT * FROM reglement WHERE num_fact = :num LIMIT 1");
            $st->execute([':num' => $facture]);
        }
        return $st->fetch();
    }

    public function GetBonCommandeByFacture($facture) {
        $facture = (string)$facture;
        $result = null;

        // Try dedicated bon_commande table first so we can expose client number + date
        $st = $this->cnx->prepare("
            SELECT num_bon_commande,
                   num_bon_commandeClient,
                   date_bon_commande,
                   piecejointe
            FROM bon_commande
            WHERE num_bon_commande = :num
            LIMIT 1
        ");
        $st->execute([':num' => $facture]);
        $row = $st->fetch();
        if ($row) {
            $display = trim((string)($row['num_bon_commandeClient'] ?? ''));
            if ($display === '') {
                $display = trim((string)($row['num_bon_commande'] ?? ''));
            }
            $result = [
                'numboncommande' => $display,
                'num_bon_commande' => $row['num_bon_commande'] ?? null,
                'num_bon_commandeClient' => $row['num_bon_commandeClient'] ?? null,
                'date_bon_commande' => $row['date_bon_commande'] ?? null,
                'piecejointe' => $row['piecejointe'] ?? null,
            ];
        }

        // Fallback to facture table column if nothing in bon_commande
        if (!$result) {
            $st = $this->cnx->prepare("SELECT numboncommande FROM facture WHERE num_fact = :f LIMIT 1");
            $st->execute([':f' => $facture]);
            $row = $st->fetch();
            if ($row && isset($row['numboncommande'])) {
                $display = trim((string)$row['numboncommande']);
                if ($display !== '') {
                    $result = [
                        'numboncommande' => $display,
                        'num_bon_commande' => $facture,
                        'num_bon_commandeClient' => $row['numboncommande'],
                    ];
                }
            }
        }

        // If still empty, respect the fact it is blank (do not auto-fill with num_fact)
        if (!$result) {
            $result = [
                'numboncommande' => '',
                'num_bon_commande' => null,
                'num_bon_commandeClient' => null,
            ];
        }

        return $result;
    }

    // Detail of a single facture joined with client info
    public function detailFacture($num) {
        $st = $this->cnx->prepare("SELECT f.*, clt.* FROM facture AS f JOIN clients AS clt ON f.client = clt.id WHERE f.num_fact = :n LIMIT 1");
        $st->execute([':n' => $num]);
        return $st->fetch();
    }

    public function getTypeFacture($facture) {
        $st = $this->cnx->prepare("SELECT prixForfitaire FROM facture_projets WHERE facture = :f");
        $st->execute([':f' => $facture]);
        return $st->fetchAll();
    }

    public function getAdresseFactureByNumFacture($facture) {
        $st = $this->cnx->prepare("SELECT DISTINCT(adresseClient) AS adresseClient FROM facture_projets WHERE facture = :f");
        $st->execute([':f' => $facture]);
        return $st->fetchAll();
    }

    public function getAdresseFactureWithProjects($facture) {
        $sql = "
            SELECT
                COALESCE(NULLIF(fp.adresseClient, ''), '') AS adresseClient,
                COUNT(*) AS nb_lignes,
                GROUP_CONCAT(DISTINCT COALESCE(p.classement, CONCAT('Projet #', fp.projet)) ORDER BY p.classement SEPARATOR ', ') AS projets
            FROM facture_projets AS fp
            LEFT JOIN projet AS p ON fp.projet = p.id
            WHERE fp.facture = :f
            GROUP BY COALESCE(NULLIF(fp.adresseClient, ''), '')
            ORDER BY adresseClient
        ";
        $st = $this->cnx->prepare($sql);
        $st->execute([':f' => $facture]);
        return $st->fetchAll();
    }

    // Expose list of project lines with project names for a given facture
    public function get_AllProjets_ByFacture($facture) {
        // Use LEFT JOIN so lines still appear even if the projet was deleted/missing
        // Alias projet fields to avoid name collisions and make template fallbacks clear
        $sql = "
            SELECT fp.*,
                   p.classement   AS projet_classement
            FROM facture_projets AS fp
            LEFT JOIN projet AS p ON fp.projet = p.id
            WHERE fp.facture = :f
        ";
        $st = $this->cnx->prepare($sql);
        $st->execute([':f' => $facture]);
        return $st->fetchAll();
    }

    public function get_All_AdressesClient_ProjetsFacture($facture) {
        $st = $this->cnx->prepare("SELECT DISTINCT(adresseClient) AS adresseClient FROM facture_projets WHERE facture = :f AND adresseClient IS NOT NULL AND adresseClient <> ''");
        $st->execute([':f' => $facture]);
        return $st->fetchAll();
    }

    public function getLastFactureClient($client) {
        $st = $this->cnx->prepare("SELECT * FROM facture WHERE client = :c ORDER BY id DESC LIMIT 1");
        $st->execute([':c' => $client]);
        return $st->fetch();
    }

    // ---------------- Archive (archivefacture + joins) ----------------
    public function AjoutArchive($num_fact, $client, $date, $joindre, $reglement, $typeReglement, $numcheque, $datecheque, $retenu, $projets) {
        // Guard: if an archive row for this num_fact already exists, update it instead of inserting a duplicate.
        $check = $this->cnx->prepare("SELECT 1 FROM archivefacture WHERE num_fact = :num LIMIT 1");
        $check->execute([':num' => $num_fact]);
        if ($check->fetchColumn()) {
            $upd = $this->cnx->prepare(
                "UPDATE archivefacture
                 SET client = :client,
                     date = :datev,
                     joindre = :joindre,
                     reglement = :reglement,
                     typeReglement = :type,
                     numcheque = :numch,
                     datecheque = :datech,
                     retenu = :retenu,
                     Projets = :projets
                 WHERE num_fact = :num"
            );
            return $upd->execute([
                ':num' => $num_fact,
                ':client' => $client,
                ':datev' => $date,
                ':joindre' => $joindre,
                ':reglement' => $reglement,
                ':type' => $typeReglement,
                ':numch' => $numcheque,
                ':datech' => $datecheque,
                ':retenu' => $retenu,
                ':projets' => $projets,
            ]);
        }

        $sql = "INSERT INTO archivefacture (`num_fact`,`client`,`date`,`joindre`,`reglement`,`typeReglement`,`numcheque`,`datecheque`,`retenu`,`Projets`)
                VALUES (:num, :client, :datev, :joindre, :reglement, :type, :numch, :datech, :retenu, :projets)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':num' => $num_fact,
            ':client' => $client,
            ':datev' => $date,
            ':joindre' => $joindre,
            ':reglement' => $reglement,
            ':type' => $typeReglement,
            ':numch' => $numcheque,
            ':datech' => $datecheque,
            ':retenu' => $retenu,
            ':projets' => $projets,
        ]);
    }

    public function AfficherFacturesArchives() {
        // Join archivefacture with clients; optionally left join archive_reglement to have reglement fields
        $sql = "
            SELECT af.*, clt.nom_client
            FROM archivefacture AS af
            JOIN clients AS clt ON af.client = clt.id
            ORDER BY af.date DESC
        ";
        $req = $this->cnx->query($sql);
        return $req->fetchAll();
    }

    public function deleteArchiveFacture($num) {
        $st = $this->cnx->prepare("DELETE FROM archivefacture WHERE num_fact = :num");
        return $st->execute([':num' => $num]);
    }
}

// Ensure global instance for legacy includes
if (!isset($facture) || !($facture instanceof Factures)) {
    $facture = new Factures();
}
