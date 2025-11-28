<?php
include_once 'connexion.db.php';
class DossierClients{
	private $cnx;
	public function __construct(){$this->cnx=connexion();}
	
	// Ajout Dossier
	public function Ajout($dossierTechnique,$dossierFournie,$typePiecesDossierFournie,$client){
        $sql = "INSERT INTO dossiersclients (dossierTechnique, dossierFournie, typePiecesDossierFournie, client_id)
                VALUES (:dossierTechnique,:dossierFournie,:typePieces,:client)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':dossierTechnique' => $dossierTechnique,
            ':dossierFournie' => $dossierFournie,
            ':typePieces' => $typePiecesDossierFournie,
            ':client' => $client,
        ]);
	}
	// Afficher Dossier 
	public function getAllDossierClients(){
		$sql="SELECT * FROM dossiersclients as dclient,clients as clt where dclient.client_id=clt.id";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
}
$dossierClient=new DossierClients();


