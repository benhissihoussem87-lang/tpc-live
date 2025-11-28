<?php
include 'class/Bordereaux.class.php';

include 'class/Factures.class.php';
$ArchiveFactures=$facture->AfficherFacturesArchives();

// Ajout Bodereau
 
 if(isset($_REQUEST['btnSubmitAjout'])){

 	if($bordereau->AjoutArchive(@$_POST['facture'],@$_POST['date'],@$_POST['facture'],@$_FILES['bordereau']['name']))
		 {
			 if($_FILES['bordereau']['name']!=''){
	@copy($_FILES['bordereau']['tmp_name'],'pages/Bordereaux/Archive/bordereauxArchive_piecesJointe/'.$_FILES['bordereau']['name']);
		}
			echo "<script>document.location.href='main.php?Bordereaux&Archive'</script>";		
	     }
	 

	
else {echo "<script>alert('Erreur !!! ')</script>";}
			
	}
?>
<!--  Détail ****-->



 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Bordereaux&Archive" class="btn btn-primary active " style="position:relative; top:20px;" >Afficher Archive Bordereaux</a>
							</div>
		

                          <div class="card-body">
						  <!-- ************ Scrolling *****************************---->
<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          Archive Bordereaux
        </button>
      </h2>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
      <div class="card-body">
					<!--Form Add Facture-->
					<form method="post" enctype="multipart/form-data" id="formBordereau"   >
		  <div class="modal-body row">
			
			  <div class="mb-3 col-3">
				<label for="facture" class="col-form-label">Num Facture:</label>
				<select class="form-control" id="facture" name="facture" required>
				
				 <?php if(!empty($ArchiveFactures)){
						foreach($ArchiveFactures as $key){?>
				 <option value="<?=$key['num_fact']?>"><?=$key['num_fact']?></option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			 
			  <div class="mb-3 col-3" >
				<label for="date" class="col-form-label">Date :</label>
				<input type="date" value="<?=date('Y-m-d')?>" required  class="form-control" id="date" name="date"/>
				   
			</div>
			<div class="mb-3 col-5" >
				<label for="bordereau" class="col-form-label">Joindre bordereau:</label>
				<input type="file" class="form-control" id="bordereau" name="bordereau"/>
				   
			</div>
			

		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
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
