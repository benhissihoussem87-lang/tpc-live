 <?php 
 include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/OffresPrix.class.php';
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$offres=$offre->AfficherOffres();
$ProjetsOffre=$offre->get_AllProjets_ByOffre($_GET['modifierOffreForfitaire']);
$detailOffre=$offre->detailOffre($_GET['idoffre']);
foreach($ProjetsOffre as $detail);
//********* Générer le numOffre ***********/
$anne=date('Y');
if($offres){$nb=count($offres);
              if((intval($nb+1))<10){$numOffre='0'.intval($nb+1).'/'.$anne;}
			 else{$numOffre=intval($nb+1).'/'.$anne;}
		     }
		  else {$numOffre='01/'.$anne;}

/*************** Offre Forfitaire  *********************/
$arrayProjets=array();
$arrayPrixForfitaire=array();
foreach($ProjetsOffre as $p){
	array_push($arrayProjets,$p['projet']);
	array_push($arrayPrixForfitaire,$p['prixForfitaire']);
}
/*********************/
 if(isset($_REQUEST['btnSubmitModifier'])){
  //verifier si l'offre avec l'adresse existe ou non
    $verifOffreInProjetsOffre=$offre->VerifOffredansFacturesProjet($_POST['num_offre'],$_POST['adresse']);
  /********Memoriser le num Offre dans une session*********/ 
   $session['numOffre']=$_POST['num_offre'];
   $session['client']=$_POST['client'];
   
   /********** Vérification si l'offre est ajouté ou non *************/
  // si l'offre et l'addresse existe 
 if($verifOffreInProjetsOffre){
       // echo '<h1> Offre '.$_POST['num_offre'].' et Adresse '.$_POST['adresse'].' existe déja</h1>';
	  // Modifier Offre
 	$offre->Modifier(@$_POST['num_offre'],@$_POST['date'],@$_POST['client'],$_GET['idoffre']);
	// delete All Project By Offre de num $_POST['num_offre']
	 if($offre->delete_All_Projets_By_OffreAndAdresse(@$_POST['num_offre'],$_POST['adresse'])){
		// Ajout Projet Offres 
		 $offre->AjoutProjets_Offre(@$_POST['num_offre'],'','ENS','','',@$_POST['prixForfitaire'][0],'',@$_POST['projet'][0],$_POST['adresse']);
   /************ Fin Ajout Offre Forfitaire       ********************/
	for($i=1;$i<count($_POST['projet']);$i++){
		
	 // Ajout Projet Offre 
		$offre->AjoutProjets_Offre(@$_POST['num_offre'],'','','','',@$_POST['prixForfitaire'][$i],'',@$_POST['projet'][$i],$_POST['adresse']);	
	}
	 }
	echo "<script>document.location.href='main.php?Offres_Prix'</script>";
   
 }
 else {
	$offre->Modifier(@$_POST['num_offre'],@$_POST['date'],@$_POST['client'],$_GET['idoffre']);
	  $offre->AjoutProjets_Offre(@$_POST['num_offre'],'','ENS','','',@$_POST['prixForfitaire'][0],'',@$_POST['projet'][0],$_POST['adresse']);
   /************ Fin Ajout Offre Forfitaire       ********************/
	for($i=1;$i<count($_POST['projet']);$i++){
		
	 // Ajout Projet Offre 
		$offre->AjoutProjets_Offre(@$_POST['num_offre'],'','','','',@$_POST['prixForfitaire'][$i],'',@$_POST['projet'][$i],$_POST['adresse']);	
	}
	 
 }
 }
 ?>
 <div class="accordion col-12" id="accordionExample">
 <div class="card">
    <div class="card-header" id="headingTwo">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Offre de Prix Forfitaire
        </button>
      </h2>
    </div>
 
 <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
      <div class="card-body">
       	<!--Form Add Facture-->
					<form method="post"   >
		  <div class="modal-body row">
			<div class="mb-3 col-2">
			
				<label for="num_offre " class="col-form-label">Num Offre::</label>
				<input type="text" required value="<?=@$detailOffre['num_offre']?>"  class="form-control" id="num_offre " name="num_offre"/>
				   
			</div>
			<div class="mb-3  col-4">
				<label for="client" class="col-form-label">Client:</label>
				<select class="form-control" id="client" name="client">
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>" <?php if(@$detailOffre['client']==$key['id']){echo 'selected';}?>><?=$key['nom_client']?></option>
				 <?php }} ?>				 						
				</select>  
			  </div>
			   <div class="mb-3  col-2">
				<label for="date" class="col-form-label">Date Offre:</label>
				<input type="date" value="<?=$detailOffre['date']?>"  class="form-control" id="date" name="date"/>
				   
			</div>
			 <div class="mb-3  col-4">
			
				<label for="adresse " class="col-form-label">Adresse:</label>
				<input type="text" value="<?=$detail['adresseClient']?>"    class="form-control" id="adresse " name="adresse"/>
				   
			</div>
			<div class="mb-3 col-12">
				<label for="projet" class="col-form-label col-12">Projets:</label>
				<?php if(!empty($projets)){
						foreach($projets as $cle){
							if(in_array($cle['id'],$arrayProjets)){
						 $check="checked";
					 }
					 else {
						  $check="";
					 }
							?>
					<div class="form-check form-check-inline col-3" >
				  <input class="form-check-input" name="projet[]" multiple type="checkbox" id="inlineCheckbox<?=$cle['id']?>" <?=$check ?> value="<?=$cle['id']?>">
				  <label class="form-check-label" for="inlineCheckbox<?=$cle['id']?>"><?=$cle['classement']?></label>
				</div>
			 
				 <?php }} ?>				 
	
			</div>	
		<!-- Champs Forfitaire **************-->
		     <div class="mb-3 col-12" style="display:flex">
	<label for="projet" class="col-form-label col-2">Prix Forfitaire:</label>
	
	<div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]" value="<?=@$arrayPrixForfitaire[0] ?>"/>
	  
	</div>
	<div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]" value="<?=@$arrayPrixForfitaire[1] ?>"/> 
	</div>
	<div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]" value="<?=@$arrayPrixForfitaire[2] ?>"/>
	 </div>
  <div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]" value="<?=@$arrayPrixForfitaire[3] ?>"/>
	</div>
   <div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]" value="<?=@$arrayPrixForfitaire[4] ?>"/>
	</div>	
</div>	
		<!-- End Forfitaire --------------------->
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitModifier" >Ajouter</button>
		  </div>
		  </form>
		
					<!--Fin Form Add Facture-->
      </div>
    </div>
 
 
 </div>