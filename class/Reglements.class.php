<?php
include_once 'connexion.db.php';
class Reglements{
	private $cnx;
	public function __construct(){$this->cnx=connexion();}
	
	
    private function normalizeDecimal($value) {
        return ($value === '' || $value === null) ? null : (float)$value;
    }

    private function normalizeString($value) {
        $value = isset($value) ? trim((string)$value) : '';
        return ($value === '') ? null : $value;
    }

    private function normalizeDate($value) {
        $value = isset($value) ? trim((string)$value) : '';
        return ($value === '') ? null : $value;
    }

    private function normalizeEtatReglement($value) {
        $value = $this->normalizeString($value);
        if ($value === null) { return null; }
        $normalized = strtolower($value);
        if ($normalized === 'avance') { return 'Oui'; }
        if ($normalized === 'oui')    { return 'Oui'; }
        if ($normalized === 'avoir')  { return 'Avoir'; }
        if ($normalized === 'non')    { return 'Non'; }
        return ucfirst($normalized);
    }

    public function Ajout($client,$facture,$prix_ttc,$etat_reglement,$num_cheque,$date_cheque,$retenue_cheque,$reglementEspece,$montant,$dateReglement,$pieceRs){
        $prix_ttc = $this->normalizeDecimal($prix_ttc);
        $montant  = $this->normalizeDecimal($montant);
        $retenue_cheque = $this->normalizeDate($retenue_cheque);
        $date_cheque    = $this->normalizeDate($date_cheque);
        $dateReglement  = $this->normalizeDate($dateReglement);
        $etat_reglement = $this->normalizeEtatReglement($etat_reglement);
        $num_cheque     = $this->normalizeString($num_cheque);
        $reglementEspece= $this->normalizeString($reglementEspece);
        $pieceRs        = $this->normalizeString($pieceRs);

        // Explicit columns; let id_reglement auto-increment
        $sql = "INSERT INTO reglement (`client`,`num_fact`,`prix_ttc`,`etat_reglement`,`num_cheque`,`date_cheque`,`retenue_date`,`TypeReglement`,`montant`,`dateReglement`,`pieceRs`)
                VALUES (:client,:facture,:prix_ttc,:etat_reglement,:num_cheque,:date_cheque,:retenue_date,:TypeReglement,:montant,:dateReglement,:pieceRs)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':client'=>$client,
            ':facture'=>$facture,
            ':prix_ttc'=>$prix_ttc,
            ':etat_reglement'=>$etat_reglement,
            ':num_cheque'=>$num_cheque,
            ':date_cheque'=>$date_cheque,
            ':retenue_date'=>$retenue_cheque,
            ':TypeReglement'=>$reglementEspece,
            ':montant'=>$montant,
            ':dateReglement'=>$dateReglement,
            ':pieceRs'=>$pieceRs
        ]);
    }	
	
    public function getReglementByFacture($facture){
        // Avoid MySQL type coercion bugs where a facture like "217/2025"
        // accidentally matches id_reglement = 217. If the input is purely
        // numeric we treat it as an id; otherwise we match by num_fact.
        if (is_scalar($facture) && preg_match('/^\d+$/', (string)$facture)) {
            $st = $this->cnx->prepare("SELECT * FROM reglement WHERE id_reglement = :id LIMIT 1");
            $st->execute([':id' => $facture]);
        } else {
            $st = $this->cnx->prepare("SELECT * FROM reglement WHERE num_fact = :num ORDER BY id_reglement DESC LIMIT 1");
            $st->execute([':num' => $facture]);
        }
        return $st->fetch();
    }
	
    public function getReglementByFactureArchive($facture){
        if (is_scalar($facture) && preg_match('/^\d+$/', (string)$facture)) {
            $st = $this->cnx->prepare("SELECT * FROM archive_reglement WHERE id = :id LIMIT 1");
            $st->execute([':id' => $facture]);
        } else {
            $st = $this->cnx->prepare("SELECT * FROM archive_reglement WHERE num_fact_archive = :num ORDER BY id DESC LIMIT 1");
            $st->execute([':num' => $facture]);
        }
        return $st->fetch();
    }
	
	public function getAllReglement($reglement){
	   $sql="SELECT * FROM reglement  where num_fact='$reglement'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	public function getAllReglementArchive($reglement){
	   $sql="SELECT * FROM archive_reglement  where num_fact_archive='$reglement'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	
	public function Modifier($facture,$prix_ttc,$etat_reglement,$num_cheque,$date_cheque,$retenue_cheque,$TypeReglement,$montant,$dateReglement,$pieceRs){
        $prix_ttc = $this->normalizeDecimal($prix_ttc);
        $montant  = $this->normalizeDecimal($montant);
        $retenue_cheque = $this->normalizeDate($retenue_cheque);
        $date_cheque    = $this->normalizeDate($date_cheque);
        $dateReglement  = $this->normalizeDate($dateReglement);
        $etat_reglement = $this->normalizeEtatReglement($etat_reglement);
        $num_cheque     = $this->normalizeString($num_cheque);
        $TypeReglement  = $this->normalizeString($TypeReglement);
        $pieceRs        = $this->normalizeString($pieceRs);

		$sql="UPDATE `reglement` SET prix_ttc=:prix_ttc,etat_reglement=:etat_reglement,num_cheque=:num_cheque,date_cheque=:date_cheque,retenue_date=:retenue_cheque,TypeReglement=:TypeReglement,montant=:montant,dateReglement=:dateReglement,pieceRs=:pieceRs where num_fact =:facture";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':prix_ttc' => $prix_ttc,
            ':etat_reglement' => $etat_reglement,
            ':num_cheque' => $num_cheque,
            ':date_cheque' => $date_cheque,
            ':retenue_cheque' => $retenue_cheque,
            ':TypeReglement' => $TypeReglement,
            ':montant' => $montant,
            ':dateReglement' => $dateReglement,
            ':pieceRs' => $pieceRs,
            ':facture' => $facture,
        ]);
	}
	
	
	public function getAll(){
		 $sql="SELECT * FROM facture as RG,clients as clt where RG.client=clt.id ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
/******************************** Archive ::::: **************/
public function AjoutArchive($client,$facture,$prix_ttc,$etat_reglement,$num_cheque,$date_cheque,$retenue_cheque,$reglementEspece,$montant,$dateReglement,$pieceRs){
        $prix_ttc = $this->normalizeDecimal($prix_ttc);
        $montant  = $this->normalizeDecimal($montant);
        $retenue_cheque = $this->normalizeDate($retenue_cheque);
        $date_cheque    = $this->normalizeDate($date_cheque);
        $dateReglement  = $this->normalizeDate($dateReglement);
        $etat_reglement = $this->normalizeEtatReglement($etat_reglement);
        $num_cheque     = $this->normalizeString($num_cheque);
        $reglementEspece= $this->normalizeString($reglementEspece);
        $pieceRs        = $this->normalizeString($pieceRs);

        // Explicit columns for archive_reglement; let id auto-increment
        $sql = "INSERT INTO archive_reglement (`client`,`num_fact_archive`,`prix_ttc`,`etat_reglement`,`num_cheque`,`date_cheque`,`retenue_date`,`TypeReglement`,`montant`,`dateReglement`,`pieceRs`)
                VALUES (:client,:num_fact_archive,:prix_ttc,:etat_reglement,:num_cheque,:date_cheque,:retenue_date,:TypeReglement,:montant,:dateReglement,:pieceRs)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':client'=>$client,
            ':num_fact_archive'=>$facture,
            ':prix_ttc'=>$prix_ttc,
            ':etat_reglement'=>$etat_reglement,
            ':num_cheque'=>$num_cheque,
            ':date_cheque'=>$date_cheque,
            ':retenue_date'=>$retenue_cheque,
            ':TypeReglement'=>$reglementEspece,
            ':montant'=>$montant,
            ':dateReglement'=>$dateReglement,
            ':pieceRs'=>$pieceRs
        ]);
    }	
public function ModifierArchive($facture,$prix_ttc,$etat_reglement,$num_cheque,$date_cheque,$retenue_cheque,$TypeReglement,$montant,$dateReglement,$pieceRs){
        $etat_reglement = $this->normalizeEtatReglement($etat_reglement);
		 $sql="UPDATE `archive_reglement` SET prix_ttc='$prix_ttc',etat_reglement='$etat_reglement',num_cheque='$num_cheque',date_cheque='$date_cheque',retenue_date='$retenue_cheque',TypeReglement='$TypeReglement',montant='$montant',dateReglement='$dateReglement',pieceRs='$pieceRs' where num_fact_archive ='$facture'  ";
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
		
	}
public function getAllARchive(){
		 $sql="SELECT * FROM archive_reglement as AR,clients as clt where AR.client=clt.id ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	public function getArchiveReglementByFacture($facture){
	  	 $sql="SELECT * FROM archive_reglement  where  num_fact_archive ='$facture'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
}
$reglement=new Reglements();


