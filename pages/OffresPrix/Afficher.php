<?php
include_once 'class/OffresPrix.class.php';
include_once 'class/client.class.php';
$clients=$clt->getAllClients();
$offres=$offre->AfficherOffres();
// Build year list from displayed date column and optional filter (client-side)
$selectedYear = '';
$offreYears = [];
if (!empty($offres)) {
    foreach ($offres as $row) {
        $year = isset($row['date']) ? substr((string)$row['date'], 0, 4) : '';
        if (preg_match('/^\d{4}$/', $year)) {
            $offreYears[$year] = true;
        }
    }
}
$offreYears = array_keys($offreYears);
rsort($offreYears, SORT_STRING);
// Générer le num Offre
$anne=date('Y');
		if($offres){
			$nb=count($offres);
			
		$numOffre=intval($nb+1).'/'.$anne;
		}
		else {$numOffre='1/'.$anne;}
// Suppression
 if(isset($_GET['deleteOffre'])){
	if($offre->deleteOffre($_GET['deleteOffre']))
	{echo "<script>document.location.href='main.php?Offres_Prix'</script>";}
 }
 /*** Detail Client ***/
/*********** Modifier Adresse Facture ***************/
 if(isset($_POST['btnModifierAdresse'])){
	 if($offre->ModifierAdresseOffre($_POST['adresseExiste'],$_POST['adresseUpdate'],$_POST['numOffre'])){
		 echo "<script>document.location.href='main.php?Offres_Prix'</script>";
	 }
 } 
 if(isset($_POST['btnSupprimerOffreAdresse'])){
	 if($offre->deleteOffreByAdresse($_POST['numOffre'],$_POST['adresseExiste'])){
		 echo "<script>document.location.href='main.php?Offres_Prix'</script>";
	 }
 } 
 // Ajout
 if(isset($_REQUEST['btnSubmitAjout'])){

	 if($offre->Ajout(@$_POST['num_offre'],@$_POST['date'],@$_POST['client'],@$_POST['projet'],@$_POST['prix_unit_htv'],@$_POST['qte'],@$_POST['tva'],@$_POST['prix_ttc']))
	{
	echo "<script>document.location.href='main.php?Offres_Prix'</script>";}
else {echo "<script>alert('Erreur !!! ')</script>";}
 }
 // Modifier
 if(isset($_REQUEST['btnSubmitModifier'])){

	 if($offre->Modifier(@$_POST['type'],@$_POST['convention'],@$_POST['nom'],@$_POST['code'],@$_POST['adresse'],@$_POST['matriculeFiscale'],@$_POST['exonoration'],@$_FILES['pieceExonoration']['name'],@$_POST['tel'],@$_POST['email'],@$_POST['idClient']))
	{
	
		if($_FILES['pieceExonoration']['name']!=''){
	@copy($_FILES['pieceExonoration']['tmp_name'],'pages/clients/pieceExonorationClients/'.$_FILES['pieceExonoration']['name']);
		}
		echo "<script>document.location.href='main.php?Gestion_Clients'</script>";}
else {echo "<script>alert('Erreur !!! ')</script>";}
 }

?>
<!--  Détail ****-->

 

		
<!-- Modal Add Offre -->
<div class="modal fade"  id="ModalAddOffre" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" >
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Offre de Prix :
		<b><?=$numOffre?></b></h1>
        
      </div>
	  
		  <form method="post"  id="formClient" >
		  <div class="modal-body">
			<div class="mb-3">
			
				<label for="num_offre " class="col-form-label">Num Offre:</label>
				<input type="text" value="<?=$numOffre?>" readOnly class="form-control" id="num_offre " name="num_offre"/>
				   
			</div>
			  <div class="mb-3">
				<label for="client" class="col-form-label">Client:</label>
				<select class="form-control" id="client" name="client">
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>"><?=$key['nom_client']?></option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			  <div class="mb-3">
				<label for="projet" class="col-form-label">Projet:</label>
			
				<select class="form-control" id="projet" name="projet">
				 <?php if(!empty($projets)){
						foreach($projets as $cle){?>
				 <option value="<?=$cle['id']?>"><?=$cle['classement']?></option>
				 <?php }} ?>				 
										
				</select>
			  </div>
			  <div class="mb-3">
				<label for="date" class="col-form-label">Date Offre:</label>
				<input type="date" value="<?=date('Y-m-d')?>"  class="form-control" id="date" name="date"/>
				   
			</div>
			  
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
		  </div>
		  </form>
		
	
	
						</div>
                    </div>
                </div>
<script>
document.addEventListener('DOMContentLoaded', function(){
	const search = document.getElementById('offresSearch');
	const table = document.getElementById('dataTable');
	const yearSel = document.getElementById('offreYearFilter');
	const btn = document.getElementById('offreYearApply');
	let dt = null;

	if (window.jQuery && $.fn && $.fn.DataTable) {
		if ($.fn.DataTable.isDataTable('#dataTable')) {
			dt = $('#dataTable').DataTable();
		} else {
			dt = $('#dataTable').DataTable();
		}
	}

	function normalize(str){
		return (str || '')
			.toLowerCase()
			.normalize('NFD').replace(/[\u0300-\u036f]/g,'') // strip accents
			.replace(/\s+/g,' ')
			.trim();
	}

	function applyFilter(){
		if (!table) return;
		const term = search ? normalize(search.value || '') : '';
		const year = yearSel ? yearSel.value : '';
		if (dt) {
			dt.search(term || '');
			if (year) {
				dt.column(1).search(year, false, false);
			} else {
				dt.column(1).search('');
			}
			dt.draw();
			return;
		}
		// manual fallback if DataTables is unavailable
		table.querySelectorAll('tbody tr').forEach(function(tr){
			const attrTxt = tr.getAttribute('data-search-text') || '';
			const txt = normalize(attrTxt || tr.innerText || '');
			const matchesTerm = term ? txt.indexOf(term) !== -1 : true;
			let matchesYear = true;
			if (year) {
				const attr = tr.getAttribute('data-year-values') || '';
				const yearTxt = attr || txt;
				matchesYear = yearTxt.indexOf(year) !== -1;
			}
			tr.style.display = (matchesTerm && matchesYear) ? '' : 'none';
		});
	}

	if (search) search.addEventListener('input', applyFilter);
	if (yearSel) yearSel.addEventListener('change', applyFilter);
	if (btn) btn.addEventListener('click', applyFilter);

	applyFilter();

	// Hide any DataTables-injected search/filter controls (we use the custom ones above)
	const dtFilters = document.querySelectorAll('#dataTable_wrapper .dataTables_filter');
	dtFilters.forEach(el => { el.style.display = 'none'; });
	const extraControls = document.querySelectorAll('.dt-extra-controls');
	extraControls.forEach(el => el.parentNode && el.parentNode.removeChild(el));
	const dtLengths = document.querySelectorAll('#dataTable_wrapper .dataTables_length');
	dtLengths.forEach(el => { el.style.display = 'none'; });
});
</script>
<style>
  /* Hide any DataTables auto-inserted controls on this page */
  #dataTable_wrapper .dataTables_filter,
  #dataTable_wrapper .dataTables_length,
  #dataTable_wrapper .dt-extra-controls {
    display: none !important;
  }
</style>
<!--  Fin Modal Add Offre-->

 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Offres_Prix&Add" class="btn btn-primary active " style="position:relative; top:20px;"  >
							Ajouter Offre de Prix</a>
							
							 <a href="?Offres_Prix&AddOffreForfitaire" class="btn btn-primary active " style="position:relative; top:20px;"  >
							Ajouter Offre de Prix Forfitaire</a>
							</div>
                        
                        <div class="card-body">
							<div class="row mb-3">
								<div class="col-md-4">
									<label for="offresSearch" class="form-label">Recherche rapide</label>
									<input type="search" class="form-control" id="offresSearch" placeholder="Num offre, client..." data-table-search="#dataTable">
								</div>
								<div class="col-md-3">
									<label for="offreYearFilter" class="form-label">Filtrer par ann&eacute;e</label>
									<select id="offreYearFilter" class="form-control">
										<option value="">Toutes les ann&eacute;es</option>
										<?php foreach ($offreYears as $year) { ?>
											<option value="<?= htmlspecialchars($year) ?>" <?= $selectedYear === $year ? 'selected' : '' ?>><?= htmlspecialchars($year) ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="col-md-2 d-flex align-items-end">
									<button type="button" class="btn btn-secondary w-100" id="offreYearApply">Filtrer</button>
								</div>
							</div>
                            <div class="table-responsive">
                                <table class="table table-bordered no-dt-controls" id="dataTable" width="100%" cellspacing="0" data-year-filter="#offreYearFilter" data-year-column="1" data-order-column="1" data-order-direction="asc">
                                    <thead>
                                         <tr>
                                            <th>Num Offre</th>
                                            <th>Date Offre</th>
                                            <th>Client</th>
                                            <th>Supprimer</th>
                                            <th>Modifier</th>
                                            <th>Imprimer</th>
                                         </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($offres)){
										foreach($offres as $key){
						/********************/
 $ProjetsOffre=$offre->get_AllProjets_ByOffre($key['num_offre'], $key['id_offre']);
 
 // Verification si l'offre est forfitaire ou non 
 $verifForfitaireOffre=null;
 if(!empty($ProjetsOffre)){foreach($ProjetsOffre as $projet){
 	if($projet['prixForfitaire']!=''){$verifForfitaireOffre='forfitaire'; break;}
 	else if($projet['prix_unit_htv']!=''){$verifForfitaireOffre='Nonforfitaire'; break;}
 	
 }}
 $modifUrl = "?Offres_Prix&modifier={$key['num_offre']}&idoffre={$key['id_offre']}";
 if ($verifForfitaireOffre === 'forfitaire') {
	$modifUrl = "?Offres_Prix&modifierOffreForfitaire={$key['num_offre']}&idoffre={$key['id_offre']}";
 }
 $yearValue = '';
 if (!empty($key['date'])) {
	$maybeYear = substr((string)$key['date'], 0, 4);
	if (preg_match('/^\\d{4}$/', $maybeYear)) { $yearValue = $maybeYear; }
 }
 ?>

<!------------------------------------ Modal Modifier Adresse Offre ----------------->
<div class="modal fade"  id="ModalUpdateOffre<?=$key['num_offre']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Gestion Offre - <?=$key['num_offre']?></h5>
		
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
     
      <div class="modal-footer">
         <a href="<?=$modifUrl?>" class="btn btn-warning">Modifier Offre</a>
		 <a href="#" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#ModalUpdateAdresse<?=$key['num_offre']?>"   data-bs-dismiss="modal">Modifier Adresse</a>
      </div>
    </div>
  </div>
</div>
<!-------- Modal Modifier Adresse Offre ---->
<!-------- Modal Modifier Adresse Offre ---->
<div class="modal fade"  id="ModalUpdateAdresse<?=$key['num_offre']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
<form method="post">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier Adresses de l'offre - <?=$key['num_offre']?></h5>
		 </div>
		<div class="modal-body row">
		<?php
		$adressesOffre=$offre->getAdresseOffreProjetByOffre($key['num_offre']);
		?>
		<h1><?//=count($adressesOffre)?></h1>
		 <div class="mb-3 col-6">
				<label for="adresseExiste" class="col-form-label">Choisir l'adresse a modifier:</label>
				<input type="hidden" name="numOffre" value="<?=$key['num_offre']?>"/>
				<select class="form-control" id="adresseExiste" name="adresseExiste">
				 <?php if(!empty($adressesOffre)){
						foreach($adressesOffre as $adr){?>
				 <option value="<?=$adr['adresseClient']?>">
				 <?php if(!empty($adr['adresseClient'])){?>
				 <?=$adr['adresseClient']?>
				 <?php } else {
				   echo 'Adresse Vide ';
				  }?>
				 </option>
				 <?php }} else { ?>
				 <option value="">Adresse Vide</option>
				 <?php } ?>				 
										
				</select>
				   
			  </div>
			   <div class="mb-3 col-6">
				<label for="adresseUpdate" class="col-form-label">Nouveau adresse:</label>
				<input type="text" class="form-control" name="adresseUpdate" placeholder="Nouveau adresse !!!" id="adresseUpdate"/>
				   
			  </div>
		</div>
		<div class="modal-footer">
         
		 <button type="submit" class="btn btn-primary" name="btnModifierAdresse">Modifier Adresse</button>
      </div>
    </div>
</form>
  </div>
</div>
<!---******************** Suppression **********-------------->
<!------------------------------------ Fin Modal Adresse Facture ----------------------->
<!------------------------------------ Modal Modifier Adresse Facture ----------------->
<div class="modal fade"  id="ModalSupprimerOffre<?=$key['num_offre']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Gestion Offre - <?=$key['num_offre']?></h5>
		
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
     
      <div class="modal-footer">
	 
         <a href="?Offres_Prix&deleteOffre=<?=$key['id_offre']?>" class="btn btn-danger">Supprimer Offre</a>
	
		 <a href="#" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#ModalDeleteOffreByAdresse<?=$key['num_offre']?>"   data-bs-dismiss="modal">Supprimer Offre-Adresse</a>
      </div>
    </div>
  </div>
</div>

<!-- Fin Modal Supprimer Facture -->
<!--------------- Modal Supprimer Facture et Adresse Facture --------------->

<div class="modal fade"  id="ModalDeleteOffreByAdresse<?=$key['num_offre']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
<form method="post">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Supprimer Adresses de l'offre - <?=$key['num_offre']?></h5>
		 </div>
		<div class="modal-body row">
		<?php
		$adressesOffre=$offre->getAdresseOffreProjetByOffre($key['num_offre']);
		?>
		<h1><?//=count($adressesOffre)?></h1>
		 <div class="mb-3 col-6">
				<label for="adresseExiste" class="col-form-label">Choisir l'adresse :</label>
				<input type="hidden" name="numOffre" value="<?=$key['num_offre']?>"/>
				<select class="form-control" id="adresseExiste" name="adresseExiste" required  >
				<option selected disabled>Choisir une adresse</option>
				 <?php if(!empty($adressesOffre)){
						foreach($adressesOffre as $adr){?>
				 <option value="<?=$adr['adresseClient']?>">
				 <?php if(!empty($adr['adresseClient'])){?>
				 <?=$adr['adresseClient']?>
				 <?php } else {
				   echo 'Adresse Vide ';
				  }?>
				 </option>
				 <?php }} else { ?>
				 <option value="">Adresse Vide</option>
				 <?php } ?>				 
										
				</select>
				   
			  </div>
			 
		</div>
		<div class="modal-footer">
         
		 <button type="submit" class="btn btn-danger" name="btnSupprimerOffreAdresse">Supprimer</button>
      </div>
    </div>
</form>
  </div>
</div>

											<?php
												$yearValue = '';
												if (!empty($key['date'])) {
													$maybeYear = substr((string)$key['date'], 0, 4);
													if (preg_match('/^\d{4}$/', $maybeYear)) { $yearValue = $maybeYear; }
												}
											?>
<?php
  $searchText = strtolower(trim($key['num_offre'].' '.$key['date'].' '.$key['nom_client']));
?>
											 <tr<?php if ($yearValue !== '') { ?> data-year-values="<?= htmlspecialchars($yearValue) ?>"<?php } ?> data-search-text="<?= htmlspecialchars($searchText) ?>">
												<td><a href="#">
												  <?=$key['num_offre']?>
												  </a>
												  </td>
												 <td><?=$key['date']?></td>
												<td><?=$key['nom_client']?></td>
												  <td>
                                                     <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#ModalSupprimerOffre<?=$key['num_offre']?>">Supp</button>
                                                    </td>
                                                    <td>
                                                       <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#ModalUpdateOffre<?=$key['num_offre']?>">Modif</button>
                                                    </td>
                                                    <td><a href="./pages/OffresPrix/ModeleOffre.php?offre=<?=$key['num_offre']?>">Imprimer</a></td>
											 </tr>
									<?php }}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
