<?php
include 'class/BonsCommandes.class.php';
include 'class/client.class.php';
$clients=$clt->getAllClients();
$ArchiveBonsCommandes=$bonCommande->getAllARchive();
// Ajout Bodereau
$numBonCommande=null;
$nbBonCommandeArchive=count($ArchiveBonsCommandes);
if($nbBonCommandeArchive<10){$numBonCommande='0'.intval($nbBonCommandeArchive+1).'/'.date('Y');}
else {$numBonCommande=intval($nbBonCommandeArchive+1).'/'.date('Y');}
 
 if(isset($_REQUEST['btnSubmitAjout'])){
   if(!empty($_POST['numBoncommandeFournisseur'])){$numBonCommandeFournisseur=@$_POST['numBoncommandeFournisseur'];}
   else{$numBonCommandeFournisseur='';}
 	if($bonCommande->AjoutArchive(@$_POST['numBoncommande'],@$_POST['date'],@$_POST['client'],@$_FILES['bonCommande']['name'],$numBonCommandeFournisseur))
		 {
			 if($_FILES['bonCommande']['name']!=''){
	@copy($_FILES['bonCommande']['tmp_name'],'pages/BonsCommandes/Archive/Archive_piecesJointe/'.$_FILES['bonCommande']['name']);
		}
			echo "<script>document.location.href='main.php?Bons_commandes&Archive'</script>";		
	     }
	 

	
else {echo "<script>alert('Erreur !!! ')</script>";}
			
	}
?>
<!--  Détail ****-->



 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center;margin-bottom:20px" class="col-12">
                            <a href="?Bons_commandes&Archive" class="btn btn-primary active " style="position:relative; top:20px;" >Afficher Archive Bons Commande</a>
							</div>
		

                          <div class="card-body">
						  <!-- ************ Scrolling *****************************---->
<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
         Ajout Archive Bon Commande
        </button>
      </h2>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
      <div class="card-body">
					<!--Form Add Facture-->
					<form method="post" enctype="multipart/form-data" id="formBordereau"   >
		  <div class="modal-body row">
			
			  <div class="mb-3 col-2">
				<label for="numBoncommande" class="col-form-label">N° Bon commande Facture:</label>
				<input type="text" value="<?=$numBonCommande?>" readOnly  required  class="form-control" id="numBoncommande" name="numBoncommande"/>
				   
			  </div>
			  <div class="mb-3 col-2">
				<label for="numBoncommandeFournisseur" class="col-form-label">N° Bon commande Fournisseur:</label>
				<input type="text"   class="form-control" id="numBoncommandeFournisseur" name="numBoncommandeFournisseur"/>
				   
			  </div>
			  <div class="mb-3 col-3">
				<label for="client" class="col-form-label">Client:</label>
				<select class="form-control" id="client" name="client" required>
				
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>"><?=$key['nom_client']?></option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			 
			  <div class="mb-3 col-2" >
				<label for="date" class="col-form-label">Date Bon Commande :</label>
				<input type="date" value="<?=date('Y-m-d')?>" required  class="form-control" id="date" name="date"/>
				   
			</div>
			<div class="mb-3 col-3" >
				<label for="bonCommande" class="col-form-label">Joindre Bon commande:</label>
				<input type="file" class="form-control" id="bonCommande" name="bonCommande"/>
				   
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
