<?php
include_once 'connexion.db.php';
class CNSS{
	private $cnx;
	public function __construct(){$this->cnx=connexion();}
	
	// Ajout CNSS
	public function Ajout($recu_declaration,$trimestre,$annee,$recu_paiement){
        $sql = "INSERT INTO cnss (recu_declaration, trimestre, annee, recu_paiement)
                VALUES (:recu_declaration,:trimestre,:annee,:recu_paiement)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':recu_declaration' => $recu_declaration,
            ':trimestre' => $trimestre,
            ':annee' => $annee,
            ':recu_paiement' => $recu_paiement,
        ]);
	}
	
	// Ajouter Recu Paiement
	public function AjoutRecuPaiement($recu_paiement,$idCnss){
		
	$sql="update cnss set recu_paiement='$recu_paiement' where id_cnss='$idCnss'";
		
		 $result=$this->cnx->exec($sql);
		 if($result) return true;
		 else return false;
	}
	// Afficher Cnss 
	public function Afficher(){
		$sql="SELECT * FROM cnss";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	
	public function getDetailCNSS($id){
		$sql="SELECT * FROM cnss where id_cnss='$id'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
	public function deleteCNSS($id){
		$sql="delete FROM cnss where id_cnss='$id'";
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
	}
	
	public function ModifierCnss($recu_declaration,$trimestre,$annee,$recu_paiement,$idCnss){
		
	$sql="update cnss set recu_paiement='$recu_paiement',recu_declaration='$recu_declaration',trimestre='$trimestre',annee='$annee' where id_cnss='$idCnss'";
		
		 $result=$this->cnx->exec($sql);
		 if($result) return true;
		 else return false;
	}
	}
$cns=new CNSS();


