<?php
include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/Factures.class.php';
include 'class/BonsCommandes.class.php';
include 'class/OffresPrix.class.php';
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$projetsOptionsHtml = '';
if (!empty($projets)) {
	foreach ($projets as $projRow) {
		$projId = (int)($projRow['id'] ?? 0);
		$projLabel = htmlspecialchars($projRow['classement'] ?? $projRow['designation'] ?? ('Projet '.$projId));
		$projetsOptionsHtml .= "<option value=\"{$projId}\">{$projLabel}</option>";
	}
}
$infosFacture=$facture->detailFacture($_GET['modifier']);
$ProjetsFacture=$facture->get_AllProjets_ByFacture($_GET['modifier']);
$numFacture=$_GET['modifier'];
$adresseFacture='';
if (!empty($ProjetsFacture)) {
	$firstLine = $ProjetsFacture[0];
	$adresseFacture = $firstLine['adresseClient'] ?? '';
}
$factureProjectLines = [];
if (!empty($ProjetsFacture)) {
	foreach ($ProjetsFacture as $line) {
		$factureProjectLines[] = [
			'projet' => isset($line['projet']) ? (int)$line['projet'] : 0,
			'prix_unit_htv' => $line['prix_unit_htv'] ?? '',
			'qte' => $line['qte'] ?? 1,
			'tva' => $line['tva'] ?? '',
			'remise' => $line['remise'] ?? '',
			'prixForfitaire' => $line['prixForfitaire'] ?? '',
			'prixTTC' => $line['prixTTC'] ?? ''
		];
	}
}
if (empty($factureProjectLines)) {
	$factureProjectLines[] = [
		'projet' => 0,
		'prix_unit_htv' => '',
		'qte' => 1,
		'tva' => '',
		'remise' => '',
		'prixForfitaire' => '',
		'prixTTC' => ''
	];
}

//echo '<h1> Adresse '.$adresseFacture.'</h1>';
// Get Num Bon commande Client 
$bonCommandeClient=$bonCommande->getDetailBonCommandeByNumFacture($_GET['modifier']);
//var_dump($bonCommandeClient);

?>

 <?php
 // Ajout
 if(isset($_REQUEST['btnSubmitModifier'])){
    $Numero_Facture=$_POST['num_fact'];
    $numBonCommandeClient=$_POST['numboncommande'];
    // Default address to the existing facture address if left empty, so new lines
    // are attached to the same address section and show up in the print.
    if (!isset($_POST['adresseClient']) || trim($_POST['adresseClient'])==='') {
        $_POST['adresseClient'] = isset($adresseFacture) ? $adresseFacture : '';
    }
/********************/
////// si la facture existe déja 
 $verifFactureInProjetsFacture=$facture->VerifFacturedansFacturesProjet($_POST['num_fact'],$_POST['adresseClient']);
 if($verifFactureInProjetsFacture){
	 
 // Modifier Facture
 	$facture->Modifier(@$_POST['num_fact'],@$_POST['client'],@$_POST['numboncommande'],@$_POST['date'],@$_POST['reglement']);
	
    // delete All Project lines then re-insert from the form (for the selected address)
    if($facture->delete_All_Projets_By_FactureAndMultiAdress(@$_POST['num_fact'],@$_POST['adresseClient'])){
      $n = max(
        isset($_POST['projet']) ? count((array)$_POST['projet']) : 0,
        isset($_POST['idProjet']) ? count((array)$_POST['idProjet']) : 0
      );
      for($i=0; $i<$n; $i++){
        $idp = isset($_POST['idProjet'][$i]) && is_numeric($_POST['idProjet'][$i]) ? (int)$_POST['idProjet'][$i] : 0;
        $hasPrice = (!empty($_POST['prix_unit_htv'][$i]) || !empty($_POST['prixForfitaire'][$i]) || !empty($_POST['prixTTC'][$i]));
        if($idp > 0 && $hasPrice){
          $facture->ModifierProjets_Facture(
            @$_POST['num_fact'],
            @$_POST['prix_unit_htv'][$i],
            @$_POST['qte'][$i],
            @$_POST['tva'][$i],
            @$_POST['remise'][$i],
            @$_POST['prixForfitaire'][$i],
            @$_POST['prixTTC'][$i],
            $idp,
            $_POST['adresseClient']
          );
        }
      }
    }
/***************************/	
	if($_POST['reglement']=='oui')
	{
		
		echo "<script>document.location.href='main.php?Reglements&Add&Facture=$numFacture&BonCommandeClient=$numBonCommandeClient&Modifier'</script>";
		// si le bon de commmande est !=null 
		
	}
	else if(!empty($_POST['numboncommande']))
	{
		 echo "<script>document.location.href='main.php?Bons_commandes&Add&ModifierBC&Facture=$Numero_Facture&BonCommande=$numBonCommandeClient'</script>";
		
	}
	else{echo "<script>document.location.href='main.php?Factures&modifier=$numFacture'</script>";}
	
 }
 else {
	
    // Ajout Projet Facture (no previous lines under this address)
    $n = max(
      isset($_POST['projet']) ? count((array)$_POST['projet']) : 0,
      isset($_POST['idProjet']) ? count((array)$_POST['idProjet']) : 0
    );
    for($i=0; $i<$n; $i++){
      $idp = isset($_POST['idProjet'][$i]) && is_numeric($_POST['idProjet'][$i]) ? (int)$_POST['idProjet'][$i] : 0;
      $hasPrice = (!empty($_POST['prix_unit_htv'][$i]) || !empty($_POST['prixForfitaire'][$i]) || !empty($_POST['prixTTC'][$i]));
      if($idp > 0 && $hasPrice){
        // Ajout Projet Facture
        $facture->AjoutProjets_Facture(
          @$_POST['num_fact'],
          @$_POST['prix_unit_htv'][$i],
          @$_POST['qte'][$i],
          @$_POST['tva'][$i],
          @$_POST['remise'][$i],
          @$_POST['prixForfitaire'][$i],
          @$_POST['prixTTC'][$i],
          $idp,
          @$_POST['adresseClient']
        );
        // Ajout Projet Offre (mirror)
        $offre->AjoutProjets_Offre(
          @$_POST['num_fact'],
          @$_POST['prix_unit_htv'][$i],
          @$_POST['qte'][$i],
          @$_POST['tva'][$i],
          @$_POST['remise'][$i],
          @$_POST['prixForfitaire'][$i],
          @$_POST['prixTTC'][$i],
          $idp,
          @$_POST['adresseClient']
        );
      }
    }
		
 }
 }
?>
<!--  Détail ****-->
 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
					 <div class="row col-12">
                       <div style="width:40%;text-align:center;float:left" class="col-9">
                            <a href="?Factures" class="btn btn-primary active " style="position:relative; top:20px;">Afficher Les Factures</a>
							
							</div>
						<div style="width:40%;text-align:center;float:right" class="col-3">
                            <a href="./pages/Factures/ModeleFacture.php?facture=<?=$numFacture?>" class="btn btn-info active " style="position:relative; top:20px;" >Imprimer </a>
							</div>
						</div>
                          <div class="card-body">
					<!--Form Add Facture-->
					<form method="post"   >
		  <div class="modal-body row">
			<div class="mb-3 col-2">
			
				<label for="num_fact " class="col-form-label">N° Facture:</label>
				<input type="text" value="<?=@$infosFacture['num_fact']?>"  class="form-control" id="num_fact " name="num_fact"/>
				   
			</div>
			  <div class="mb-3 col-3">
				<label for="client" class="col-form-label">Client:</label>
				
				<select class="form-control" id="client" name="client">
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>" <?php if($infosFacture['nom_client']==$key['nom_client']){echo 'selected';}?>>
				 <?=$key['nom_client']?>
				 </option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			  <div class="mb-3 col-2">
			<label for="numboncommande  " class="col-form-label">Bon.Commande Client:</label>
				<input type="text" value="<?=@$bonCommandeClient['num_bon_commandeClient']?>"  class="form-control" id="numboncommande"
				name="numboncommande"/>
				   
			</div>
			  
			  <div class="mb-3 col-3" >
				<label for="date" class="col-form-label">Date Facture:</label>
				<input type="date" value="<?=$infosFacture['date'] ?>" required  class="form-control" id="date" name="date"/>
				   
			</div>
			<div class="mb-3 col-2">
			<label for="reglement" class="col-form-label">Reglement :</label>
			<select class="form-control" id="reglement" name="reglement">
			  <option value="oui" <?php if($infosFacture['reglement']=='oui'){?> selected <?php } ?> >Oui</option>
			  <option value="non" <?php if($infosFacture['reglement']=='non'){?> selected <?php } ?> >Non</option>
			  <option value="Avance" <?php if($infosFacture['reglement']=='Avance'){?> selected <?php } ?> >Avance</option>
			</select>
				
				   
			</div>
			 <div class="mb-3 col-12" >
				<label for="adresseClient" class="col-form-label">Adresse:</label>
				<input type="text"  class="form-control" id="adresseClient" name="adresseClient"/>
				   
			</div>
			<div id="factureProjectsContainer">
				<?php foreach ($factureProjectLines as $line) {
					$lineProj = (int)$line['projet'];
					$lineLabel = '';
					if ($lineProj > 0) {
						foreach ($projets as $projRow) {
							if ((int)$projRow['id'] === $lineProj) {
								$lineLabel = $projRow['classement'] ?? $projRow['designation'] ?? '';
								break;
							}
						}
					}
					?>
				<div class="row g-2 align-items-end mb-2 projet-line" data-line>
					<div class="col-md-3">
						<label class="form-label">Projet</label>
						<select class="form-control projet-select">
							<option value="">-- Choisir --</option>
							<?php foreach ($projets as $proj) {
								$idOpt = (int)$proj['id'];
								$labelOpt = htmlspecialchars($proj['classement'] ?? $proj['designation'] ?? ('Projet '.$idOpt));
								$sel = $idOpt === $lineProj ? 'selected' : '';
								echo "<option value=\"{$idOpt}\" {$sel}>{$labelOpt}</option>";
							} ?>
						</select>
						<input type="hidden" name="idProjet[]" class="line-id" value="<?=$lineProj?>">
						<input type="hidden" name="projet[]" class="line-label" value="<?=htmlspecialchars($lineLabel)?>">
					</div>
					<div class="col-md-2">
						<label class="form-label">Prix Unitaire H.TVA</label>
						<input type="text" class="form-control" name="prix_unit_htv[]" value="<?=htmlspecialchars($line['prix_unit_htv'])?>">
					</div>
					<div class="col-md-1">
						<label class="form-label">Qte</label>
						<input type="number" class="form-control" name="qte[]" min="1" value="<?=htmlspecialchars($line['qte'])?>">
					</div>
					<div class="col-md-1">
						<label class="form-label">TVA</label>
						<input type="text" class="form-control" name="tva[]" value="<?=htmlspecialchars($line['tva'])?>">
					</div>
					<div class="col-md-1">
						<label class="form-label">Remise</label>
						<input type="text" class="form-control" name="remise[]" value="<?=htmlspecialchars($line['remise'])?>">
					</div>
					<div class="col-md-2">
						<label class="form-label">Prix Forfitaire</label>
						<input type="text" class="form-control" name="prixForfitaire[]" value="<?=htmlspecialchars($line['prixForfitaire'])?>">
					</div>
					<div class="col-md-2">
						<label class="form-label">Prix TTC</label>
						<input type="text" class="form-control" name="prixTTC[]" value="<?=htmlspecialchars($line['prixTTC'])?>">
					</div>
					<div class="col-md-12 text-end">
						<button type="button" class="btn btn-outline-danger btn-sm remove-project-line">Supprimer</button>
					</div>
				</div>
				<?php } ?>
			</div>
			<div class="mb-3">
				<button type="button" class="btn btn-outline-primary btn-sm" id="addProjectLine">Ajouter un projet</button>
			</div>
			<template id="project-line-template">
				<div class="row g-2 align-items-end mb-2 projet-line" data-line>
					<div class="col-md-3">
						<label class="form-label">Projet</label>
						<select class="form-control projet-select">
							<option value="">-- Choisir --</option>
							<?=$projetsOptionsHtml?>
						</select>
						<input type="hidden" name="idProjet[]" class="line-id" value="">
						<input type="hidden" name="projet[]" class="line-label" value="">
					</div>
					<div class="col-md-2">
						<label class="form-label">Prix Unitaire H.TVA</label>
						<input type="text" class="form-control" name="prix_unit_htv[]" value="">
					</div>
					<div class="col-md-1">
						<label class="form-label">Qte</label>
						<input type="number" class="form-control" name="qte[]" min="1" value="1">
					</div>
					<div class="col-md-1">
						<label class="form-label">TVA</label>
						<input type="text" class="form-control" name="tva[]" value="">
					</div>
					<div class="col-md-1">
						<label class="form-label">Remise</label>
						<input type="text" class="form-control" name="remise[]" value="">
					</div>
					<div class="col-md-2">
						<label class="form-label">Prix Forfitaire</label>
						<input type="text" class="form-control" name="prixForfitaire[]" value="">
					</div>
					<div class="col-md-2">
						<label class="form-label">Prix TTC</label>
						<input type="text" class="form-control" name="prixTTC[]" value="">
					</div>
					<div class="col-md-12 text-end">
						<button type="button" class="btn btn-outline-danger btn-sm remove-project-line">Supprimer</button>
					</div>
				</div>
			</template>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitModifier" >Modifier</button>
		  </div>
		  </form>
		
					<!--Fin Form Add Facture-->
						  </div>
                     
                    </div>
<script>
document.addEventListener('DOMContentLoaded', function(){
	const container=document.getElementById('factureProjectsContainer');
	const template=document.getElementById('project-line-template');
	const addBtn=document.getElementById('addProjectLine');
	function updateLine(row){
		if(!row) return;
		const select=row.querySelector('.projet-select');
		const idInput=row.querySelector('.line-id');
		const labelInput=row.querySelector('.line-label');
		if(select && idInput){
			idInput.value=select.value || '';
			if(labelInput){
				const opt=select.options[select.selectedIndex] || null;
				labelInput.value=opt ? opt.text.trim() : '';
			}
		}
	}
	if(container){
		container.querySelectorAll('.projet-line').forEach(updateLine);
		container.addEventListener('change',function(e){
			if(e.target.matches('.projet-select')){
				updateLine(e.target.closest('.projet-line'));
			}
		});
		container.addEventListener('click',function(e){
			const removeBtn=e.target.closest('.remove-project-line');
			if(removeBtn){
				const line=removeBtn.closest('.projet-line');
				if(!line) return;
				const allLines=container.querySelectorAll('.projet-line');
				if(allLines.length>1){
					line.remove();
				}else{
					line.querySelectorAll('input').forEach(function(input){
						if(input.type==='number') input.value=1;
						else input.value='';
					});
					const sel=line.querySelector('.projet-select');
					if(sel){ sel.value=''; }
					updateLine(line);
				}
			}
		});
	}
	if(addBtn && template && container){
		addBtn.addEventListener('click',function(){
			const fragment=template.content.cloneNode(true);
			const newLine=fragment.querySelector('.projet-line');
			container.appendChild(fragment);
			const lines=container.querySelectorAll('.projet-line');
			if(lines.length){ updateLine(lines[lines.length-1]); }
		});
	}
});
</script>
