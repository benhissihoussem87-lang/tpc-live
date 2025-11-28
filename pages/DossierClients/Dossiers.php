<?php
include 'class/DossierClients.class.php';
include 'class/client.class.php';
$clients=$clt->getAllClients();
// Ajout Dossier Client
 if(isset($_REQUEST['btnSubmitAjout'])){
	
  // Ajout Offre de Prix
 	if($dossierClient->Ajout(@$_FILES['dossierTechnique']['name'],@$_FILES['dossierFournie']['name'],@$_POST['typeFournie'],@$_POST['client']))
	{
		
	if($_FILES['dossierTechnique']['name']!=''){
	@copy($_FILES['dossierTechnique']['tmp_name'],'pages/DossierClients/DossierTechnique/'.$_FILES['dossierTechnique']['name']);
		}
		if($_FILES['dossierFournie']['name']!=''){
	@copy($_FILES['dossierFournie']['tmp_name'],'pages/DossierClients/DossierFourni/'.$_FILES['dossierFournie']['name']);
		}
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?DossierClient'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }
$getAllDossier=$dossierClient->getAllDossierClients();
?>

<!-- Form Ajout Dossier -->
    <div class="card shadow mb-4">
                      
                          <div class="card-body">
					<!--Form Add Facture-->
					<form method="post"  enctype="multipart/form-data"  >
		  <div class="modal-body row">
			   
			  <div class="mb-3  col-3">
				<label for="client" class="col-form-label">Client:</label>
				<select class="form-control" id="client" name="client">
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>"><?=$key['nom_client']?></option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			  <div class="mb-3  col-3">
				<label for="dossierTechnique" class="col-form-label">Dossier Technique:</label>
				<input type="file"  class="form-control" id="dossierTechnique" name="dossierTechnique"/>
				   
			</div>
			 <div class="mb-3 col-3">
				<label for="dossierFournie" class="col-form-label">Dossier Fournie:</label>
				<input type="file"  class="form-control" id="dossierFournie" name="dossierFournie"/>
			</div>
			
			 <div class="mb-3 col-3">
				<label for="typeFournie" class="col-form-label">Type Fournie:</label>
				<textarea   class="form-control" id="typeFournie" name="typeFournie" ></textarea>
			</div>
			
				  
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
		  </div>
		  </form>
	 <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
	  <thead>
             <tr>
				<th >Client</th><th >Dossier Technique</th><th >Dossier Fournie </th><th >Type Fournie</th>
				</tr>
		</thead>
		<tbody>
		<?php if(!empty($getAllDossier)){
			foreach($getAllDossier as $key){?>
             <tr>
				<td ><?=$key['nom_client']?></td>
				<td ><a href="./pages/DossierClients/DossierTechnique/<?=$key['dossierTechnique']?>">Télécharger</a></td>
				<td ><a href="./pages/DossierClients/DossierFourni/<?=$key['dossierFournie']?>">Télécharger</a></td>
				<td ><?=$key['typePiecesDossierFournie']?></td>
				</tr>
		<?php }} ?>
		</tbody>
	 
	 
	 </table>
					<!--Fin Form Add Facture-->
						  </div>
                     
                    </div>
