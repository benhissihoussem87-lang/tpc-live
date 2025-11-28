<?php
include 'class/Bordereaux.class.php';

include 'class/Factures.class.php';
$factures=$facture->AfficherAllFactures();
$detailBordereau=$bordereau->detailBordereauById($_GET['Update']);

// Modifier Bodereau
 
 if(isset($_REQUEST['btnSubmitModifier'])){
	 $facture=$_POST['facture'];
	 $adresse= $_POST['adresse_bordereaux'];
	 $date= $_POST['adresse_bordereaux'];
	
	$numBordereau=$_POST['facture'];
	 
	// echo '<h1> Adresse Bordereaux '.$adresse.'</h1>';
// Get Num Facture 
	
 	if($bordereau->ModifierBordereau(@$_GET['Update'],@$_FILES['bordereau']['name'],@$_POST['date'],str_replace("'","\'",$_POST['type']),str_replace("'","\'",$adresse),@$facture,$numBordereau))
		 {
			 if($_FILES['bordereau']['name']!=''){
	@copy($_FILES['bordereau']['tmp_name'],'pages/Bordereaux/bordereaux_piecesJointe/'.$_FILES['bordereau']['name']);
	}
		echo "<script>document.location.href='main.php?Bordereaux'</script>";	
			
	     }
	 

	
else {echo "<script>alert('Erreur !!! ')</script>";}
		
	}

?>
<!--  Détail ****-->



 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                      
		

                          <div class="card-body">
						  <!-- ************ Scrolling *****************************---->
<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOnes" aria-expanded="true" aria-controls="collapseOne">
          Modifier Bordereaux
        </button>
      </h2>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
      <div class="card-body">
					<!--Form Add Facture-->
					<form method="post" enctype="multipart/form-data" id="formBordereau"   >
		  <div class="modal-body row">
			
			  <div class="mb-3 col-2">
				<label for="facture" class="col-form-label">Num Facture:</label>
				  
				<input type="text" class="form-control" value="<?=@$detailBordereau['num_fact']?>" readonly id="facture" name="facture"    />
				
				
			  </div>
			 
			  <div class="mb-3 col-2" >
				<label for="date" class="col-form-label">Date :</label>
				<input type="date" value="<?=@$detailBordereau['date']?>" required  class="form-control" id="date" name="date"/>
				   
			</div>
			 <div class="mb-3 col-2" >
				<label for="adresse_bordereaux" class="col-form-label">Adresse  :</label>
				<input type="text" value="<?=@$detailBordereau['adresse_bordereaux']?>"   class="form-control" id="adresse_bordereaux" name="adresse_bordereaux"/>
				   
			</div>
			
			 <div class="mb-3 col-3" >
				<label for="type" class="col-form-label">Type :</label>
				<input list="types" class="form-control" value="<?=@$detailBordereau['type']?>" name="type" id="type" placeholder="Choisir/ecrire le type de bordereau">
				<datalist id="types">
				<option value="ATTESTATION GAZ & ATTESTATION ELECTRIQUE">
				  <option value="Attestation">
				  <option value="Rapport">
				 
				</datalist>
				
				   
			</div>
			<div class="mb-3 col-3" >
				<label for="bordereau" class="col-form-label">Joindre bordereau:</label>
				<input type="file" class="form-control" id="bordereau" name="bordereau"/>
				   
			</div>
			

		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitModifier" >Modifier</button>
		  </div>
		  </form>
		
					<!--Fin Form Add Facture-->
      </div>
    </div>
  </div>
  
 
</div>
		
<!-- *********** Fin Scrolling **********************--------->
					
						  </div>
                     
                    </div>
