<?php 
include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/Factures.class.php';
include 'class/BonsCommandes.class.php';
include 'class/OffresPrix.class.php';
require_once __DIR__ . '/forfait_form_helpers.php';

$clients = $clt->getAllClients();
$projets = $projet->getAllProjets();
$factures = $facture->AfficherFactures();
$infosFacture = $facture->detailFacture($_GET['modifierForfitaire']);
$ProjetsFacture = $facture->get_AllProjets_ByFacture($_GET['modifierForfitaire']);
$numFacture = $_GET['modifierForfitaire'];
$bonCommandeClient = $bonCommande->getDetailBonCommandeByNumFacture($_GET['modifierForfitaire']);

$forfaitLinesPrefill = tpc_prefill_lines_from_db($ProjetsFacture);
$forfaitFormErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['btnSubmitAjoutFactureForfitaire'])) {
    $rawLines = isset($_POST['lignes']) && is_array($_POST['lignes']) ? $_POST['lignes'] : [];
    $forfaitLinesPrefill = tpc_prepare_forfait_lines_prefill($rawLines);
    $validLines = tpc_extract_valid_forfait_lines($rawLines);

    if (empty($validLines)) {
        $forfaitFormErrors[] = "Ajoutez au moins une ligne avec un projet et un prix forfaitaire.";
    }

    $clientId       = $_POST['client'] ?? ($infosFacture['client'] ?? '');
    $bonCommandeNew = $_POST['numboncommande'] ?? '';
    $dateFacture    = $_POST['date'] ?? ($infosFacture['date'] ?? date('Y-m-d'));
    $etatReglement  = $_POST['reglement'] ?? ($infosFacture['reglement'] ?? 'non');

    if (empty($forfaitFormErrors)) {
        $facture->Modifier($numFacture, $clientId, $bonCommandeNew, $dateFacture, $etatReglement);
        $facture->delete_All_Projets_By_Facture($numFacture);
        if (method_exists($offre, 'delete_All_Projets_By_Offre')) {
            $offre->delete_All_Projets_By_Offre($numFacture);
        }

        foreach ($validLines as $idx => $line) {
            $qteFacture = ($idx === 0) ? 'ENS' : '';

            $facture->AjoutProjets_Facture(
                $numFacture,
                '',
                $qteFacture,
                '',
                '',
                $line['prix_raw'],
                '',
                $line['projet'],
                $line['adresse']
            );

            if ($idx === 0) {
                $offre->AjoutProjets_Offre(
                    $numFacture,
                    '',
                    '',
                    '',
                    '',
                    $line['prix_raw'],
                    '',
                    $line['projet'],
                    $line['adresse']
                );
            } else {
                $offre->AjoutProjets_Offre(
                    $numFacture,
                    '',
                    'ENS',
                    '',
                    '',
                    '',
                    $line['prix_raw'],
                    $line['projet'],
                    $line['adresse']
                );
            }
        }

        echo "<script>document.location.href='main.php?Factures'</script>";
        exit;
    }
}

$selectedClientId  = $_POST['client'] ?? ($infosFacture['client'] ?? '');
$postedBonCommande = $_POST['numboncommande'] ?? ($bonCommandeClient['num_bon_commandeClient'] ?? '');
$postedExonoration = $_POST['numexonoration'] ?? '';
$postedDate        = $_POST['date'] ?? ($infosFacture['date'] ?? date('Y-m-d'));
$postedReglement   = $_POST['reglement'] ?? ($infosFacture['reglement'] ?? 'non');
?>
 <div class="accordion col-12" id="accordionExample">
 <div class="card">
    <div class="card-header" id="headingTwo">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Facture Forfitaire
        </button>
      </h2>
    </div>
 
 <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
      <div class="card-body">
       	<!--Form Add Facture-->
					<form method="post"   >
	  <div class="modal-body row">
		<?php if (!empty($forfaitFormErrors)) { ?>
		<div class="col-12">
		  <div class="alert alert-danger">
			<ul class="mb-0">
			  <?php foreach ($forfaitFormErrors as $err) { ?>
			  <li><?= htmlspecialchars($err) ?></li>
			  <?php } ?>
			</ul>
		  </div>
		</div>
		<?php } ?>
		<div class="mb-3 col-3">
			<label for="num_fact " class="col-form-label">N&deg; Facture:</label>
			<input type="text" value="<?= htmlspecialchars($infosFacture['num_fact'] ?? $numFacture) ?>" readOnly class="form-control" id="num_fact " name="num_fact"/>
		</div>
		  <div class="mb-3 col-5">
			<label for="client" class="col-form-label">Client:</label>
			<select class="form-control" id="client" name="client">
			 <?php if(!empty($clients)){
				foreach($clients as $key){
				  $cid = (string)$key['id'];
			?>
		 <option value="<?= htmlspecialchars($cid) ?>" <?php if($selectedClientId == $cid){echo 'selected';}?>>
		 <?= htmlspecialchars($key['nom_client']) ?>
		 </option>
		 <?php }} ?>				 
			</select>
		  </div>
		  <div class="mb-3 col-3">
		<label for="numboncommande" class="col-form-label">N&deg; Bon de commande:</label>
			<input type="text" value="<?= htmlspecialchars($postedBonCommande) ?>"  class="form-control" id="numboncommande"
			name="numboncommande"/>
		  </div>
		  <div class="mb-3 col-3">
		<label for="numexonoration" class="col-form-label">N&deg; exonoration:</label>
			<input type="text"  class="form-control" id="numexonoration"
			name="numexonoration" value="<?= htmlspecialchars($postedExonoration) ?>"/>
		  </div>
		  
		  <div class="mb-3 col-3" >
			<label for="date" class="col-form-label">Date Facture:</label>
			<input type="date" value="<?= htmlspecialchars($postedDate) ?>" required  class="form-control" id="date" name="date"/>
		  </div>
		<div class="mb-3 col-2" >
			<label for="reglement" class="col-form-label">Reglement :</label>
		<select class="form-control" id="reglement" name="reglement">
		  <option value="oui" <?php if($postedReglement=='oui'){?> selected <?php } ?> >Oui</option>
		  <option value="non" <?php if($postedReglement=='non'){?> selected <?php } ?>>Non</option>
		  <option value="Avance" <?php if($postedReglement=='Avance'){?> selected <?php } ?>>Avance</option>
		</select>
		</div>
		<div class="mb-3 col-12">
		  <?php
			$forfaitLinesWidgetId = 'forfaitLinesEdit';
			include __DIR__ . '/partials/forfait_lines_widget.php';
		  ?>
		</div>
  	</div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
		<?php if(isset($_GET['modifierForfitaire'])){?>
		<button type="submit" class="btn btn-primary" name="btnSubmitAjoutFactureForfitaire" >Modifier</button>
		<?php } ?>
	  </div>
	  </form>
		<!--Fin Form Add Facture-->
      </div>
    </div>
 
 
 </div>
