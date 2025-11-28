<?php
include_once 'connexion.db.php';
class OffresPrix{
	private $cnx;
	public function __construct(){$this->cnx=connexion();}
	
	// Ajout Offre Prix (idempotent on num_offre)
	public function Ajout($num,$date,$client){
		// Use an upsert to avoid UNIQUE(num_offre) violations when re-adding
		// existing numbers after manual deletions in related tables.
		$sql = "INSERT INTO offre_prix (`num_offre`,`date`,`client`)
				VALUES (:num,:datev,:client)
				ON DUPLICATE KEY UPDATE `date` = VALUES(`date`), `client` = VALUES(`client`)";
		$st = $this->cnx->prepare($sql);
		return $st->execute([
			':num' => $num,
			':datev' => $date,
			':client' => $client,
		]);
	}
	
	// Modifier Offre Prix
	public function Modifier($num,$date,$client,$idOffre){
		$sql="update `offre_prix` SET  `num_offre`='$num',`date`='$date',`client`='$client' where id_offre='$idOffre' ";
		
		 $result=$this->cnx->exec($sql);
		 if($result) return true;
		 else return false;
		
	}
	// Ajout Projets Offres
	public function AjoutProjets_Offre($offre,$prix_unitaire,$qte,$tva,$remise,$prixForfitaire,$prixTTC,$projet,$adresse){
		// Explicit columns; let id auto-increment
		$projVal = (isset($projet) && $projet !== '' && is_numeric($projet) && (int)$projet > 0) ? (int)$projet : null;
		$adrVal = isset($adresse) ? trim((string)$adresse) : null;
		if ($adrVal === '') { $adrVal = null; }
		$sql = "INSERT INTO offres_projets (`offre`,`prix_unit_htv`,`qte`,`tva`,`remise`,`prixForfitaire`,`prixTTC`,`projet`,`adresseClient`)
				VALUES (:offre,:pu,:qte,:tva,:remise,:pf,:pttc,:projet,:adr)";
		$st = $this->cnx->prepare($sql);
		return $st->execute([
			':offre'=>$offre,
			':pu'=>$prix_unitaire,
			':qte'=>$qte,
			':tva'=>$tva,
			':remise'=>$remise,
			':pf'=>$prixForfitaire,
			':pttc'=>$prixTTC,
			':projet'=>$projVal,
			':adr'=>$adrVal,
		]);
	}
	
	
	// Modifier Projets Offres
	public function ModifierProjets_Offre($offre,$prix_unitaire,$qte,$tva,$remise,$prixForfitaire,$prixTTC,$projet,$adresse){
		// Original code re-inserts; preserve behavior but with explicit columns
		$projVal = (isset($projet) && $projet !== '' && is_numeric($projet) && (int)$projet > 0) ? (int)$projet : null;
		$adrVal = isset($adresse) ? trim((string)$adresse) : null;
		if ($adrVal === '') { $adrVal = null; }
		$sql = "INSERT INTO offres_projets (`offre`,`prix_unit_htv`,`qte`,`tva`,`remise`,`prixForfitaire`,`prixTTC`,`projet`,`adresseClient`)
				VALUES (:offre,:pu,:qte,:tva,:remise,:pf,:pttc,:projet,:adr)";
		$st = $this->cnx->prepare($sql);
		return $st->execute([
			':offre'=>$offre,
			':pu'=>$prix_unitaire,
			':qte'=>$qte,
			':tva'=>$tva,
			':remise'=>$remise,
			':pf'=>$prixForfitaire,
			':pttc'=>$prixTTC,
			':projet'=>$projVal,
			':adr'=>$adrVal,
		]);
	}
	
	public function AfficherProjets_By_Offre($offre){
		 $sql="SELECT * FROM offres_projets as fO,projet as p where fO.projet=p.id and fO.offre='$offre'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	// Afficher Offres 
	public function getAllOffres(){
		$sql="SELECT * FROM offre_prix as Of,clients as clt where Of.client=clt.id";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	public function AfficherOffres(){
		$sql="SELECT * FROM offre_prix as Of,clients as clt where Of.client=clt.id";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	// delete Offre 
	public function deleteOffre($id){
		$sql="delete FROM offre_prix where id_offre='$id'";
		 $result=$this->cnx->exec($sql);
		 if($result) return true;
		 else return false;
	}

    // Offre By Id
	public function getOffre_Prix($id){
		$sql="SELECT * FROM offre_prix where id='$id' ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
	
    public function VerifExistenceOffre($offre){
		  $sql="SELECT * FROM offre_prix where num_offre='$offre' ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
/************************  Archive Offre  ******************************************/
	// Ajout Archive Offre Prix
	public function AjoutArchive($num,$date,$client,$facture,$joindre){
        $sql = "INSERT INTO archiveoffre (num_offre, factureArchive, client, date_archive, piece_jointe)
                VALUES (:num,:facture,:client,:datev,:joindre)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':num' => $num,
            ':facture' => $facture,
            ':client' => $client,
            ':datev' => $date,
            ':joindre' => $joindre,
        ]);
	}
  // Afficher les Offres de Prix Archive 
  public function getOffres_PrixArchive(){
		 $sql="SELECT * FROM archiveoffre as OfA,clients as clt,archivefacture as FA where OfA.client=clt.id and OfA.factureArchive =FA.num_fact ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
// Afficher les Offres de Prix Archive By Client
  public function getOffres_PrixArchiveByClient($client){
		 $sql="SELECT * FROM archiveoffre as OfA,clients as clt,archivefacture as FA where OfA.client=clt.id and OfA.factureArchive =FA.num_fact and OfA.client='$client'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
//detail Offre 
public function detailOffre($idOffre){
		 $sql="SELECT * FROM offre_prix where  id_offre ='$idOffre' ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
	
public function detailOffreByNumOffre($idOffre){
		 $sql="SELECT * FROM offre_prix as ofP,clients as clt where  ofP.num_offre ='$idOffre' and ofP.client=clt.id ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}

	
	public function get_All_AdressesClient_ProjetsOffre($offre){
		$sql="SELECT distinct(adresseClient) FROM offres_projets as fP where  fP.offre='$offre' and fP.adresseClient!='' ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		// Fallback if offres_projets is empty: look in facture_projets mirror.
		if (empty($resultat)) {
			$sql2 = "SELECT DISTINCT(adresseClient) FROM facture_projets WHERE facture = '$offre' AND adresseClient IS NOT NULL";
			$req2 = $this->cnx->query($sql2);
			$resultat = $req2->fetchAll();
		}
		return $resultat;
	}
	
public function get_All_AdressesClient_ProjetsByOffre($offre){
		 $sql="SELECT distinct(adresseClient) FROM offres_projets where  offre='$offre'  ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		if (empty($resultat)) {
			$sql2 = "SELECT DISTINCT(adresseClient) FROM facture_projets WHERE facture = '$offre'";
			$req2 = $this->cnx->query($sql2);
			$resultat = $req2->fetchAll();
		}
		return $resultat;
	}
/******** Afficher les projets de l'offre selectionner***********/
	public function get_AllProjets_ByOffre($offre, $offreId = null){
		$offre = (string)$offre;
		$offreNorm    = preg_replace('/\s+/', '', $offre);
		$offreNoSlash = str_replace('/', '', $offreNorm);
		$offreIdInt   = (is_numeric($offreId)) ? (int)$offreId : null;
		$offreLike    = '%'.$offre.'%';

		$conds = [];
		$params = [];

		// Base variants on the raw num_offre
		$conds[] = "fP.offre = ?";
		$params[] = $offre;

		$conds[] = "TRIM(fP.offre) = TRIM(?)";
		$params[] = $offre;

		$conds[] = "REPLACE(TRIM(fP.offre), ' ', '') = ?";
		$params[] = $offreNorm;

		$conds[] = "REPLACE(REPLACE(TRIM(fP.offre), ' ', ''), '/', '') = ?";
		$params[] = $offreNoSlash;

		$conds[] = "fP.offre LIKE ?";
		$params[] = $offreLike;

		// Variants based on id_offre if provided
		if ($offreIdInt !== null) {
			$conds[] = "fP.offre = ?";
			$params[] = (string)$offreIdInt;

			$conds[] = "op.id_offre = ?";
			$params[] = $offreIdInt;

			$conds[] = "op.num_offre = ?";
			$params[] = $offre;

			$conds[] = "TRIM(op.num_offre) = TRIM(?)";
			$params[] = $offre;

			$conds[] = "REPLACE(TRIM(op.num_offre), ' ', '') = ?";
			$params[] = $offreNorm;

			$conds[] = "REPLACE(REPLACE(TRIM(op.num_offre), ' ', ''), '/', '') = ?";
			$params[] = $offreNoSlash;

			$conds[] = "op.num_offre LIKE ?";
			$params[] = $offreLike;
		}

		$sql = "
			SELECT fP.*,
			       p.id   AS projet_id,
			       p.classement
			FROM offres_projets AS fP
			LEFT JOIN projet AS p ON fP.projet = p.id
			LEFT JOIN offre_prix AS op ON fP.offre = op.num_offre
			WHERE ".implode(' OR ', $conds)."
		";
		$st = $this->cnx->prepare($sql);
		$st->execute($params);
		$rows = $st->fetchAll();

		// Fallback: if nothing matched (unusual formatting), try a broad LIKE scan without joins.
		if (empty($rows)) {
			$likeNum    = '%'.$offre.'%';
			$likeNorm   = '%'.$offreNorm.'%';
			$likeNoSlash= '%'.$offreNoSlash.'%';
			$likeIdStr  = ($offreIdInt !== null) ? '%'.$offreIdInt.'%' : null;

			$fallbackConds = [
				"offre LIKE ?",
				"REPLACE(offre, ' ', '') LIKE ?",
				"REPLACE(REPLACE(offre, ' ', ''), '/', '') LIKE ?"
			];
			$fallbackParams = [$likeNum, $likeNorm, $likeNoSlash];

			if ($likeIdStr !== null) {
				$fallbackConds[] = "offre LIKE ?";
				$fallbackParams[] = $likeIdStr;
			}

			$fallbackSql = "
				SELECT *, projet AS projet_id
				FROM offres_projets
				WHERE ".implode(' OR ', $fallbackConds)."
			";
			$fallbackStmt = $this->cnx->prepare($fallbackSql);
			$fallbackStmt->execute($fallbackParams);
			$rows = $fallbackStmt->fetchAll();
		}

		// Final fallback: many legacy offers only have lines in facture_projets (mirrored during creation).
		if (empty($rows)) {
			$fallback = $this->cnx->prepare("
				SELECT fp.*, p.classement, p.id AS projet_id
				FROM facture_projets AS fp
				LEFT JOIN projet AS p ON fp.projet = p.id
				WHERE fp.facture = :f OR TRIM(fp.facture) = TRIM(:f2)
			");
			$fallback->execute([':f' => $offre, ':f2' => $offre]);
			$rows = $fallback->fetchAll();
		}

		return $rows;
	}
// Delete All Projet By Offre 

	public function delete_All_Projets_By_Offre($offre){
		// delete Offres_Projet by numFacture
		 $sql="delete from offres_projets where offre='$offre'";
		
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
	}	
/************ delete Offre By Num Offre et Adresse ***********/
public function delete_All_Projets_By_OffreAndAdresse($offre,$adresse){
		$offre = (string)$offre;
		$adr   = isset($adresse) ? trim((string)$adresse) : '';

		if ($adr === '') {
			$sql = "DELETE FROM offres_projets WHERE offre = :offre AND (adresseClient IS NULL OR adresseClient = '')";
			$params = [':offre' => $offre];
		} else {
			$sql = "DELETE FROM offres_projets WHERE offre = :offre AND adresseClient = :adr";
			$params = [':offre' => $offre, ':adr' => $adr];
		}

		$st = $this->cnx->prepare($sql);
		return $st->execute($params);
	}
public function getAdresseOffreProjetByOffre($offre){
		$offre = (string)$offre;
		$sql="SELECT distinct(adresseClient) FROM offres_projets  where offre='$offre'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		// Legacy rows may exist only in facture_projets; surface those addresses too
		if (empty($resultat)) {
			$sql2 = "SELECT DISTINCT(adresseClient) FROM facture_projets WHERE facture = :offre";
			$st2  = $this->cnx->prepare($sql2);
			$st2->execute([':offre' => $offre]);
			$resultat = $st2->fetchAll();
		}
		return $resultat;
	}

/************ Modifier Adresse Offre ***************/
public function ModifierAdresseOffre($adresse,$nouveauAdresse,$offre){
		$offre = (string)$offre;
		$old   = isset($adresse) ? trim((string)$adresse) : '';
		$new   = isset($nouveauAdresse) ? trim((string)$nouveauAdresse) : '';
		$new   = ($new === '') ? null : $new;

		if ($old === '') {
			$sql = "UPDATE offres_projets
					SET adresseClient = :new
					WHERE offre = :offre AND (adresseClient IS NULL OR adresseClient = '')";
			$sqlFact = "UPDATE facture_projets
					SET adresseClient = :new
					WHERE facture = :offre AND (adresseClient IS NULL OR adresseClient = '')";
			$params = [':new' => $new, ':offre' => $offre];
		} else {
			$sql = "UPDATE offres_projets
					SET adresseClient = :new
					WHERE offre = :offre AND adresseClient = :old";
			$sqlFact = "UPDATE facture_projets
					SET adresseClient = :new
					WHERE facture = :offre AND adresseClient = :old";
			$params = [':new' => $new, ':offre' => $offre, ':old' => $old];
		}

		$st = $this->cnx->prepare($sql);
		$ok1 = $st->execute($params);

		// Mirror update in facture_projets to keep address consistent when offres_projets is empty.
		$st2 = $this->cnx->prepare($sqlFact);
		$ok2 = $st2->execute($params);

		return $ok1 || $ok2;
	}
// Delete Offre By Adresse 

	public function deleteOffreByAdresse($offre,$adresse){
		$offre = (string)$offre;
		$adr   = isset($adresse) ? trim((string)$adresse) : '';

		if ($adr === '') {
			$sql = "DELETE FROM offres_projets WHERE offre = :offre AND (adresseClient IS NULL OR adresseClient = '')";
			$sqlFact = "DELETE FROM facture_projets WHERE facture = :offre AND (adresseClient IS NULL OR adresseClient = '')";
			$params = [':offre' => $offre];
		} else {
			$sql = "DELETE FROM offres_projets WHERE offre = :offre AND adresseClient = :adr";
			$sqlFact = "DELETE FROM facture_projets WHERE facture = :offre AND adresseClient = :adr";
			$params = [':offre' => $offre, ':adr' => $adr];
		}

		$st = $this->cnx->prepare($sql);
		$ok1 = $st->execute($params);

		$st2 = $this->cnx->prepare($sqlFact);
		$ok2 = $st2->execute($params);

		return $ok1 || $ok2;
	}	
/************ verification existance offre & adresse  ********************/	
public function VerifOffredansFacturesProjet($offre,$adresse){
		$offre = (string)$offre;
		$adr   = isset($adresse) ? trim((string)$adresse) : '';

		if ($adr === '') {
			$sql = "SELECT 1 FROM offres_projets WHERE offre = :offre AND (adresseClient IS NULL OR adresseClient = '') LIMIT 1";
			$params = [':offre' => $offre];
		} else {
			$sql = "SELECT 1 FROM offres_projets WHERE offre = :offre AND adresseClient = :adr LIMIT 1";
			$params = [':offre' => $offre, ':adr' => $adr];
		}

		$st = $this->cnx->prepare($sql);
		$st->execute($params);
	    return $st->fetch();
	}
}
$offre=new OffresPrix();


