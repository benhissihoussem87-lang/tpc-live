<?php
include_once 'connexion.db.php';
class Bordereaux{
	private $cnx;
	public function __construct(){$this->cnx=connexion();}
	
	
	public function Ajout($numbordereau,$date,$num_fact,$piece_jointe,$type,$adresse){
        $sql = "INSERT INTO bordereaux (num_bordereaux, date, num_fact, pieces_jointe, type, adresse_bordereaux)
                VALUES (:num_bordereaux, :datev, :num_fact, :pieces_jointe, :type, :adresse)";
        $st = $this->cnx->prepare($sql);
        $typeSanitized = str_replace("'", "\\'", (string)$type);
        $adresseSanitized = str_replace("'", "\\'", (string)$adresse);
        return $st->execute([
            ':num_bordereaux' => $numbordereau,
            ':datev' => $date,
            ':num_fact' => $num_fact,
            ':pieces_jointe' => $piece_jointe,
            ':type' => $typeSanitized,
            ':adresse' => $adresseSanitized,
        ]);
    }
	
	public function getAll(){
		 $sql="SELECT * FROM facture ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	public function getAllFacturesBordereaux($numBordereau){
		 $sql="SELECT * FROM bordereaux as bd where bd.num_fact='$numBordereau'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	public function GetInfosBordereau($numBordereau){
		 $sql="SELECT * FROM bordereaux where num_bordereaux='$numBordereau'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
  public function Modifier($Idbordereau,$bordereau,$date,$type){
		echo $sql="UPDATE `bordereaux` SET pieces_jointe='$bordereau',date='$date',type='$type' where num_bordereaux ='$Idbordereau'  ";
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
		
	}
	
	public function ModifierBordereau($Idbordereau,$bordereau,$date,$type,$adresse,$facture,$num_bordereaux){
		  $sql="UPDATE `bordereaux` SET pieces_jointe='$bordereau',date='$date',type='$type',adresse_bordereaux='$adresse',num_fact='$facture',num_bordereaux='$num_bordereaux' where id ='$Idbordereau'  ";
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
		
	}
/******************************** Archive ::::: **************/
public function AjoutArchive($numbordereau,$date,$num_fact,$piece_jointe){
        $sql = "INSERT INTO archive_bordereaux (num_bordereaux,date,num_fact,pieces_jointe)
                VALUES (:num,:datev,:num_fact,:pieces_jointe)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':num' => $numbordereau,
            ':datev' => $date,
            ':num_fact' => $num_fact,
            ':pieces_jointe' => $piece_jointe,
        ]);
	}	
public function getAllARchive(){
		 $sql="SELECT * FROM archive_bordereaux as bd,archivefacture as f where bd.num_fact=f.num_fact";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
public function detailBordereau($num){
		 $sql="SELECT * FROM bordereaux as b,facture as f,clients as clt where b.num_bordereaux='$num' and f.num_fact=b.num_bordereaux and f.client=clt.id    ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
public function detailBordereauById($id){
		 $sql="SELECT * FROM bordereaux where id='$id'  ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
}
$bordereau=new Bordereaux();


