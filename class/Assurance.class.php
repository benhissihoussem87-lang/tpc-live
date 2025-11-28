<?php
include_once 'connexion.db.php';
class Assurances{
	private $cnx;
	public function __construct(){$this->cnx=connexion();}
	
	// Ajout Assurance
	public function Ajout($contrat,$annee,$attestation_Bureau,$attestation_Voiture,$quitance_Bureau,$quitance_Voiture){
        $sql = "INSERT INTO assurance (contrat, annee, attestation_Bureau, attestation_Voiture, quitance_Bureau, quitance_Voiture)
                VALUES (:contrat,:annee,:attestation_Bureau,:attestation_Voiture,:quitance_Bureau,:quitance_Voiture)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':contrat' => $contrat,
            ':annee' => $annee,
            ':attestation_Bureau' => $attestation_Bureau,
            ':attestation_Voiture' => $attestation_Voiture,
            ':quitance_Bureau' => $quitance_Bureau,
            ':quitance_Voiture' => $quitance_Voiture,
        ]);
	}
	
	// Ajouter Recu Paiement
	public function GestionPieceJointe($libelle,$PieceJointe,$idAssurance){
		
	echo $sql="update assurance set $libelle='$PieceJointe' where id_assurance='$idAssurance'";
		
		 $result=$this->cnx->exec($sql);
		 if($result) return true;
		 else return false;
	}
	// Afficher Assurance 
	public function Afficher(){
		$sql="SELECT * FROM assurance";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	
	public function getDetailAssurance($id){
		$sql="SELECT * FROM assurance where id_assurance='$id'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
	public function deleteAssurance($id){
		$sql="delete FROM assurance where id_assurance='$id'";
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
	}
	
	public function ModifierAssurance($contrat,$annee,$attestation_Bureau,$attestation_Voiture,$quitance_Bureau,$quitance_Voiture,$idAssurance){
		
	$sql="update assurance set contrat='$contrat',annee='$annee',attestation_Bureau='$attestation_Bureau',attestation_Voiture='$attestation_Voiture',quitance_Bureau='$quitance_Bureau',quitance_Voiture='$quitance_Voiture' where id_assurance='$idAssurance'";
		
		 $result=$this->cnx->exec($sql);
		 if($result) return true;
		 else return false;
	}
	}
$assurance=new Assurances();


