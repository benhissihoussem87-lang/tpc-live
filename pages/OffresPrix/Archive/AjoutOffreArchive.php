<?php
include 'class/client.class.php';

include 'class/Factures.class.php';
include 'class/OffresPrix.class.php';
$clients=$clt->getAllClients();

$factures_Archive=$facture->AfficherFacturesArchives();

// Générer le numOffre
$anne=date('Y');

?>

 <?php

 if(isset($_REQUEST['btnSubmitAjout'])){
	 $nbOffreJoindre=count($_FILES['offre']['name']);
	 $pieceJoindre=array();
	//var_dump($_FILES['offre']['name']);
	//echo 'nb Piece = '.$nbOffreJoindre;
	for($i=0;$i<$nbOffreJoindre;$i++){
		if($_FILES['offre']['tmp_name'][$i]!=''){
		@copy($_FILES['offre']['tmp_name'][$i],'pages/OffresPrix/Archive/Document_OffresArchive/'.$_FILES['offre']['name'][$i]);
		array_push($pieceJoindre,$_FILES['offre']['name'][$i]);
		}
		
	}
	
	$offres=implode(",",$pieceJoindre);
	//echo '<br>Offres'.$offres;
	if($offres==''){$offres='Offre téléphonique';}
	// Ajout Archive Offre
 	if($offre->AjoutArchive(@$_POST['factureArchive'],@$_POST['date'],@$_POST['client'],@$_POST['factureArchive'],@$offres))
		 {
			
		
	echo "<script>document.location.href='main.php?Offres_Prix&Archive'</script>";
	     }
	 

	
else {echo "<script>alert('Erreur !!! ')</script>";}
			
	}
	
		


?>
 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center;margin-bottom:20px" class="col-12">
                            <a href="?Offres_Prix&Archive" class="btn btn-primary active " style="position:relative; top:20px;" >Afficher Archive Offres</a>
							</div>
		

                          <div class="card-body">
						  <!-- ************ Scrolling *****************************---->
<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          Ajout Archive Offre
        </button>
      </h2>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
      <div class="card-body">
					<!--Form Add Archive Offre-->
		<form method="post" enctype="multipart/form-data"  >
		  <div class="modal-body row">
			  <!-- <div class="mb-3  col-2">
			
				<label for="num_offre " class="col-form-label">Num Offre:</label>
				<input type="text" value="<?=$numOffre?>" readOnly class="form-control" id="num_offre " name="num_offre"/>
				   
			</div>-->
			  <div class="mb-3  col-3">
				<label for="client" class="col-form-label">Client:</label>
				<select class="form-control" id="client" name="client">
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>"><?=$key['nom_client']?></option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			  <div class="mb-3  col-2">
				<label for="factureArchive" class="col-form-label">Facture:</label>
				<select class="form-control" id="factureArchive" name="factureArchive">
				 <?php if(!empty($factures_Archive)){
						foreach($factures_Archive as $key){?>
				 <option value="<?=$key['num_fact']?>"><?=$key['num_fact']?></option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			  <div class="mb-3  col-3">
				<label for="date" class="col-form-label">Date Offre:</label>
				<input type="date" value="<?=date('Y-m-d')?>"  class="form-control" id="date" name="date"/>
				   
			</div>
			
			 <div class="mb-3  col-4">
				<label for="offre" class="col-form-label">Joindre Offre:</label>
				<input type="file"   class="form-control" id="offre" name="offre[]" multiple />
				   
			</div>
			</div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
		  </div>
		  </form>
					<!--Fin Form Add Offre Archive-->
      </div>
    </div>
  </div>
 
 
</div>
		
<!-- *********** Fin Scrolling **********************--------->


                     
                    </div>
