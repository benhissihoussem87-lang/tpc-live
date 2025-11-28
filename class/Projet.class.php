<?php
include_once 'connexion.db.php';

// Unified class name: Projet (with alias Projets for backward compatibility)
class Projet {
    private $cnx;
    public function __construct() { $this->cnx = connexion(); }

    // Afficher Projets
    public function getAllProjets() {
        $sql = "SELECT * FROM projet";
        $req = $this->cnx->query($sql);
        $resultat = $req->fetchAll();
        return $resultat;
    }
}

// Backward compat: many legacy pages reference Projets
if (!class_exists('Projets')) {
    class Projets extends Projet {}
}

// Ensure global instance as used across legacy pages
if (!isset($projet) || !($projet instanceof Projet)) {
    $projet = new Projet();
}


