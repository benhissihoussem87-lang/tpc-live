<?php
include_once 'connexion.db.php';
class GRHS{
	private $cnx;
	public function __construct(){$this->cnx=connexion();}
	
	// Ajout GRH
	public function Ajout($nomPersonnel,$ADRESS,$LibelleAdresse,$TitreDiplome,$DIPLOME,$TitreFormation,$FORMATION,$fiche_paie,$typeContrat,$CONTRAT,$post,$cin){
        $sql = "INSERT INTO grh (nomPersonnel, ADRESS, LibelleAdresse, TitreDiplome, DIPLOME, TitreFormation, FORMATION, fiche_paie, typeContrat, CONTRAT, POSTE, numCin)
                VALUES (:nomPersonnel,:ADRESS,:LibelleAdresse,:TitreDiplome,:DIPLOME,:TitreFormation,:FORMATION,:fiche_paie,:typeContrat,:CONTRAT,:post,:cin)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':nomPersonnel' => $nomPersonnel,
            ':ADRESS' => $ADRESS,
            ':LibelleAdresse' => $LibelleAdresse,
            ':TitreDiplome' => $TitreDiplome,
            ':DIPLOME' => $DIPLOME,
            ':TitreFormation' => $TitreFormation,
            ':FORMATION' => $FORMATION,
            ':fiche_paie' => $fiche_paie,
            ':typeContrat' => $typeContrat,
            ':CONTRAT' => $CONTRAT,
            ':post' => $post,
            ':cin' => $cin,
        ]);
	}
	
	// Modifier GRH
	public function Modifier($nomPersonnel,$ADRESS,$LibelleAdresse,$TitreDiplome,$DIPLOME,$TitreFormation,$FORMATION,$fiche_paie,$typeContrat,$CONTRAT,$post,$cin,$id){
		
	  echo	$sql="update grh set nomPersonnel='$nomPersonnel',ADRESS='$ADRESS',LibelleAdresse='$LibelleAdresse',TitreDiplome='$TitreDiplome',DIPLOME='$DIPLOME',TitreFormation='$TitreFormation',FORMATION='$FORMATION',fiche_paie='$fiche_paie',typeContrat='$typeContrat',CONTRAT='$CONTRAT',POSTE='$post',numCin='$cin' where id_grh='$id'";
		
		 $result=$this->cnx->exec($sql);
		 if($result) return true;
		 else return false;
	}
	
// Modifier Categ
public function ModifierLibelle($id,$libelle,$value,$title,$titre){
if($libelle=='fiche_paie' or $libelle=='POSTE' or $libelle=='numCin')
 {$sql="update grh set $libelle='$value' where id_grh='$id'";}
			
	else if($value!='') {$sql="update grh set $libelle='$value',$title='$titre' where id_grh='$id'";}
	else { $sql="update grh set $title='$titre' where id_grh='$id'";}
	 $result=$this->cnx->exec($sql);
		 if($result) return true;
		 else return false;
	}
	// Afficher GRH 
	public function getGRH(){
		$sql="SELECT distinct(nomPersonnel) FROM grh";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	public function getInfoGRHByNomPersonnel($nomPersonnel){
		$sql="SELECT * FROM grh where nomPersonnel='$nomPersonnel'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	public function getDetailGRH($id){
		$sql="SELECT * FROM grh where id_grh='$id'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
	public function deleteRH($nomPersonnel){
		$sql="delete FROM grh where nomPersonnel='$nomPersonnel'";
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
	}
	public function deleteElementRH($id,$element){
		if($element=='ADRESS'){ $sql="update  grh set $element='',LibelleAdresse='' where id_grh='$id'";}
     else if($element=='DIPLOME'){ $sql="update  grh set $element='',TitreDiplome='' where id_grh='$id'";}
	 else if($element=='FORMATION'){ $sql="update  grh set $element='',TitreFormation='' where id_grh='$id'";}
	  else if($element=='CONTRAT'){ $sql="update  grh set $element='',typeContrat='' where id_grh='$id'";}
	  else if($element=='CONTRAT'){ $sql="update  grh set $element='',typeContrat='' where id_grh='$id'";}
	   else if($element=='fiche_paie'){ $sql="update  grh set $element='' where id_grh='$id'";}
	   
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
	}
}
$grh=new GRHS();


