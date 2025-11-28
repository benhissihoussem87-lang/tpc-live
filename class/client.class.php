<?php
include_once 'connexion.db.php';
class Clients{
	private $cnx;
	public function __construct(){$this->cnx=connexion();}
	
	// Ajout Client
	public function Ajout($type,$convention,$pieceConvention,$nom,$code,$adresse,$matriculeFiscale,$exonoration,$pieceExonoration,$tel,$email,$numExonoration,$ValiditeExonoration){
		$sql = "INSERT INTO clients (`id`, `type_client`, `convention`, `pieceConvention`, `nom_client`, `code_client`, `adresse`, `matriculeFiscale`, `exonoration`, `pieceExonoration`, `tel`, `email`, `numexonoration`, `ValiditeExonoration`)
				VALUES (NULL, :type, :convention, :pieceConvention, :nom, :code, :adresse, :matriculeFiscale, :exonoration, :pieceExonoration, :tel, :email, :numexonoration, :validite)";
		$stmt = $this->cnx->prepare($sql);
		return $stmt->execute([
			':type' => $type,
			':convention' => $convention,
			':pieceConvention' => $pieceConvention,
			':nom' => $nom,
			':code' => $code,
			':adresse' => $adresse,
			':matriculeFiscale' => $matriculeFiscale,
			':exonoration' => $exonoration,
			':pieceExonoration' => $pieceExonoration,
			':tel' => $tel,
			':email' => $email,
			':numexonoration' => $numExonoration,
			':validite' => $ValiditeExonoration,
		]);
	}
	
	// Afficher Clients 
	public function getAllClients(){
		$sql="SELECT * FROM clients";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	
	// Afficher code dernier client  
	public function getDernierCodeClient(){
		 $sql="SELECT * FROM clients order By id desc limit 1";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
	// delete Client 
	public function deleteClient($id){
		$sql = "DELETE FROM clients WHERE id = :id";
		$stmt = $this->cnx->prepare($sql);
		return $stmt->execute([':id' => $id]);
	}

    // Client By Id
	public function getClient($id){
		$sql = "SELECT * FROM clients WHERE id = :id";
		$req = $this->cnx->prepare($sql);
		$req->execute([':id' => $id]);
		return $req->fetch();
	}
	
	
  // Modifier Client
    
	public function Modifier($type,$convention,$piececonvention,$nom,$code,$adresse,$matriculeFiscale,$exonoration,$pieceExonoration,$tel,$email,$numexonoration,$ValiditeExonoration,$id){
		$sql = "UPDATE clients SET
			`type_client` = :type,
			`convention` = :convention,
			`pieceConvention` = :piececonvention,
			`nom_client` = :nom,
			`code_client` = :code,
			`adresse` = :adresse,
			`matriculeFiscale` = :matriculeFiscale,
			`exonoration` = :exonoration,
			`pieceExonoration` = :pieceExonoration,
			`tel` = :tel,
			`email` = :email,
			`numexonoration` = :numexonoration,
			`ValiditeExonoration` = :validite
			WHERE id = :id";
		$stmt = $this->cnx->prepare($sql);
		return $stmt->execute([
			':type' => $type,
			':convention' => $convention,
			':piececonvention' => $piececonvention,
			':nom' => $nom,
			':code' => $code,
			':adresse' => $adresse,
			':matriculeFiscale' => $matriculeFiscale,
			':exonoration' => $exonoration,
			':pieceExonoration' => $pieceExonoration,
			':tel' => $tel,
			':email' => $email,
			':numexonoration' => $numexonoration,
			':validite' => $ValiditeExonoration,
			':id' => $id,
		]);
	}
	
	// Get Mes Offres  
	
	public function getOffresClient($client){
		  $sql="SELECT * FROM offre_prix as ofP where  Ofp.client='$client'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	// Afficher Projets 
	public function getAllProjetsByOffre($offre){
		$sql="SELECT * FROM offres_projets as OP,projet as p where OP.offre='$offre' and p.id=OP.projet";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
 public function getClientByNom($nom){
		$sql = "SELECT * FROM clients WHERE nom_client = :nom";
		$req = $this->cnx->prepare($sql);
		$req->execute([':nom' => $nom]);
		return $req->fetch();
	}
	// Afficher Clients 
    public function RapportClient($client){
         $sql="SELECT * FROM facture as f, facture_projets as FP,projet as p where f.client=$client  and FP.facture=f.num_fact and p.id=FP.projet order by f.date desc";
         $req=$this->cnx->query($sql);
         $resultat=$req->fetchAll();
         return $resultat;
    }
	
    public function RapportClientArchive($client){
         $sql="SELECT * FROM archivefacture as f where f.client=$client order by f.date desc";
         $req=$this->cnx->query($sql);
         $resultat=$req->fetchAll();
         return $resultat;
    }
	
 
}
$clt=new Clients();


