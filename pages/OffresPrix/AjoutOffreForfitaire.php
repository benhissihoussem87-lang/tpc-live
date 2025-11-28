 <?php 
 include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/OffresPrix.class.php';
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$offres=$offre->AfficherOffres();

//********* Générer le numOffre ***********/
$anne=date('Y');
if($offres){$nb=count($offres);
              if((intval($nb+1))<10){$numOffre='0'.intval($nb+1).'/'.$anne;}
			 else{$numOffre=intval($nb+1).'/'.$anne;}
		     }
		  else {$numOffre='01/'.$anne;}

/*************** Offre Forfitaire  *********************/
 if(isset($_REQUEST['btnSubmitAjoutOffreForfitaire'])){
  var_dump(@$_POST['projet']);
		echo '<h1> Nb Prix Forfitaire = '.count($_POST['prixForfitaire']).'</h1>';
		var_dump(@$_POST['prixForfitaire']);
  /********Memoriser le num Offre dans une session*********/ 
   $session['numOffre']=$_POST['num_offre'];
   $session['client']=$_POST['client'];
   
   /********** Vérification si l'offre est ajouté ou non *************/
   $verifNumOffre=false;
   $verif=$offre->VerifExistenceOffre($_POST['num_offre']);
   if($verif){
	   $verifNumOffre=true;//  si l'offre existe déja
     }
   else {
	    $verifNumOffre=false;//  si l'offre n'existe déja
	   }
	if($verifNumOffre){ echo '<br> Offre existe déja '; }
	else { echo '<br> Offre n\'existe déja '; }
   /********** Fin Vérification ******/
   
  /*****si l'offre existe déja on va ajouter les projets de l'offre seulement*****/
  if($verifNumOffre){
   $offre->AjoutProjets_Offre(@$_POST['num_offre'],'','ENS','','',@$_POST['prixForfitaire'][0],'',@$_POST['projet'][0],$_POST['adresse']);
   /************ Fin Ajout Offre Forfitaire       ********************/
	for($i=1;$i<count($_POST['projet']);$i++){
		
	 // Ajout Projet Offre 
		$offre->AjoutProjets_Offre(@$_POST['num_offre'],'','','','',@$_POST['prixForfitaire'][$i],'',@$_POST['projet'][$i],$_POST['adresse']);	
	}
  }
  else {
/***si l'offre n'existe pas on va ajouter l'offre de prix puis les projets de l'offre***/
	// Ajout Offre + Projets Offre
 	if($offre->Ajout(@$_POST['num_offre'],@$_POST['date'],@$_POST['client']))
	{
	
	$offre->AjoutProjets_Offre(@$_POST['num_offre'],'','ENS','','',@$_POST['prixForfitaire'][0],'',@$_POST['projet'][0],$_POST['adresse']);
   /************ Fin Ajout Offre Forfitaire       ********************/
	for($i=1;$i<count($_POST['projet']);$i++){
		
	 // Ajout Projet Offre 
		$offre->AjoutProjets_Offre(@$_POST['num_offre'],'','','','',@$_POST['prixForfitaire'][$i],'',@$_POST['projet'][$i],$_POST['adresse']);	
	}
	echo "<script>document.location.href='main.php?Offres_Prix'</script>";
	
	}
  }
//else {echo "<script>alert('Erreur !!! ')</script>";}


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
			<div class="mb-3 col-3">
			
				<label for="num_offre " class="col-form-label">Num Offre::</label>
				<input type="text" required value="<?=@$session['numOffre']?>"  class="form-control" id="num_offre " name="num_offre"/>
				   
			</div>
			  <div class="mb-3 col-4">
				<label for="client" class="col-form-label">Client:</label>
				
				<select class="form-control" id="client" name="client">
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>" <?php if(@$session['client']==$key['id']){ echo 'selected';}?>><?=$key['nom_client']?></option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			  <div class="mb-3 col-2" >
				<label for="date" class="col-form-label">Date Offre:</label>
				<input type="date" value="<?=date('Y-m-d')?>" required  class="form-control" id="date" name="date"/>
				   
			</div>
			
			 <div class="mb-3  col-3">
			
				<label for="adresse " class="col-form-label">Adresse:</label>
				<input type="text"   class="form-control" id="adresse " name="adresse"/>
				   
			</div>
			<div class="mb-3 col-12">
				<label for="projet" class="col-form-label col-12">Projets:</label>
				<?php if(!empty($projets)){
						foreach($projets as $cle){?>
					<div class="form-check form-check-inline col-3" >
				  <input class="form-check-input" name="projet[]" multiple type="checkbox" id="inlineCheckbox<?=$cle['id']?>" value="<?=$cle['id']?>">
				  <label class="form-check-label" for="inlineCheckbox<?=$cle['id']?>"><?=$cle['classement']?></label>
				</div>
			 
				 <?php }} ?>				 
	
			</div>	
		<!-- Champs Forfitaire **************-->
		     <div class="mb-3 col-12" style="display:flex">
	<label for="projet" class="col-form-label col-2">Prix Forfitaire:</label>
	
	<div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]"/>
	  
	</div>
	<div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]"/> 
	</div>
	<div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]"/>
	 </div>
  <div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]"/>
	</div>
   <div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]"/>
	</div>	
</div>	
		<!-- End Forfitaire --------------------->
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjoutOffreForfitaire" >Ajouter</button>
		  </div>
		  </form>
		
					<!--Fin Form Add Facture-->
      </div>
    </div>
 
 
 </div>