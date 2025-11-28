<?php
include 'class/BonsCommandes.class.php';
include 'class/client.class.php';

include 'class/Factures.class.php';

$clients=$clt->getAllClients();
$BonsCommandes=$bonCommande->getAll();
// Ajout Bodereau
$numBonCommande=null;
$nbBonCommande=count($BonsCommandes);
if($nbBonCommande<10){$numBonCommande='0'.intval($nbBonCommande+1).'/'.date('Y');}
else {$numBonCommande=intval($nbBonCommande+1).'/'.date('Y');}
 //******** detailFacture  ********//
@$detailFacture=$facture->detailFacture($_GET['Facture']);
// detail Bon commande 
//@$detailBonCommande=$bonCommande->detailFacture($_GET['Facture']);
if(isset($detailFacture)){
	$date=@$detailFacture['date'];
	//$numBonCommandeClient=@$detailFacture['numboncommande'];
	if(isset($_GET['BonCommande'])){
		$numBonCommandeClient=@$_GET['BonCommande'];
	}
	else{
		$numBonCommandeClient=@$detailFacture['numboncommande'];
	}
//echo '<h1> Nb commande client '.$numBonCommandeClient.'</h1>';
}
/****************************************/
if(isset($_GET['Facture'])){
	// Get Infos Client 
	  $nomClient=$detailFacture['nom_client'];
	  $detailClient=$clt->getClientByNom($nomClient);
	  $idClient=$detailClient['id'];
	 // echo '<h1> Id Client '.$idClient.'</h1>';
	$numBonCommandeFacture=$_GET['Facture'];
	}
else{$numBonCommandeFacture=null;}

/***************************************/
// Ajout Bon Commande
 
 if(isset($_REQUEST['btnSubmitAjout'])){

	@$numFacture=$_POST['numBoncommandeFacture'];
	if($_GET['Facture']){$client=$idClient;}
 else {$client=$_POST['client'];}
 	if($bonCommande->Ajout(@$_POST['numBoncommandeFacture'],@$_POST['date'],@$client,@$_FILES['bonCommande']['name'],@$_POST['numBoncommandeClient']))
		 {
			if($_FILES['bonCommande']['name']!=''){
	@copy($_FILES['bonCommande']['tmp_name'],'pages/BonsCommandes/BonCommandes_piecesJointe/'.$_FILES['bonCommande']['name']);
		}
		if(!isset($_GET['Facture']))
		   {
			 //echo 'OK 1111';
			echo "<script>document.location.href='main.php?Bons_commandes'</script>";
			}
	else {
		//echo 'OK 2222222';
			echo "<script>document.location.href='main.php?Reglements&Add&Facture=$numFacture'</script>";	
	    }			
	     }
	 
	
else {echo "<script>alert('Erreur !!! ')</script>";}
			
	}
// Fin Ajout Bon commande
/***********  Modifier Bon e comande ***************/
 if(isset($_REQUEST['btnSubmitModifier'])){

	@$numFacture=$_POST['numBoncommandeFacture'];
 	if($bonCommande->ModifierBonCommandeFacture(@$_POST['date'],@$_FILES['bonCommande']['name'],@$_POST['numBoncommandeClient'],@$_POST['numBoncommandeFacture']))
		 {
			if($_FILES['bonCommande']['name']!=''){
	@copy($_FILES['bonCommande']['tmp_name'],'pages/BonsCommandes/BonCommandes_piecesJointe/'.$_FILES['bonCommande']['name']);
		}
		
		// Modifier 
		echo "<script>document.location.href='main.php?Bons_commandes'</script>";
		/*if(!isset($_GET['Facture']))
		{echo "<script>document.location.href='main.php?Bons_commandes'</script>";}
	else {
			echo "<script>document.location.href='main.php?Reglements&Add&Modifier&Facture=$numFacture'</script>";	
	    }	*/		
	     }
	 
	
else {
	echo "<script>document.location.href='main.php?Bons_commandes'</script>";
	}
			
	}

/********* Fin Modifier Bon commande ***********/

/*if(!isset($detailFacture)){
if(isset($_GET['NumBonCommande'])){
	$numBonCommandeClient=$_GET['NumBonCommande'];}
else{$numBonCommandeClient=null;}
}*/
?>
<!--  Détail ****-->



 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Bons_commandes" class="btn btn-primary active " style="position:relative; top:20px;" >Afficher Bons Commandes</a>
							</div>
		

                          <div class="card-body">
						  <!-- ************ Scrolling *****************************---->
<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          Ajout Bon Commande 
        </button>
      </h2>
    </div>

   <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
       <div class="card-body">
					<!--Form Add Facture-->
					<form method="post" enctype="multipart/form-data" id="formBordereau"   >
		  <div class="modal-body row">
			
			  <div class="mb-3 col-2">
				<label for="numBoncommandeFacture" class="col-form-label">N° Bon commande Facture:</label>
				<input type="text" value="<?=$numBonCommandeFacture?>"   required  class="form-control" id="numBoncommandeFacture" name="numBoncommandeFacture"/>
				   
			  </div>
			  <div class="mb-3 col-2">
				<label for="numBoncommandeClient" class="col-form-label">N° Bon commande Client:</label>
				<input type="text"   class="form-control" value="<?=$numBonCommandeClient?>" id="numBoncommandeClient" name="numBoncommandeClient"/>
				   
			  </div>
			  <div class="mb-3 col-3">
				<label for="client" class="col-form-label">Client:</label>
				<?php if(isset($_GET['Facture'])){?>
				  <input type="text" name="client" class="form-control"  value="<?=$detailFacture['nom_client']?>" />
				<?php }else {?>
				
				<select class="form-control" id="client" name="client" required>
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>"><?=$key['nom_client']?></option>
				 <?php }} ?>				 
										
				</select>
				<?php } ?>
				   
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
			<?php if(!isset($_GET['ModifierBC'])){?>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
			<?php } else {?>
			
			<button type="submit" class="btn btn-primary" name="btnSubmitModifier" >Modifier</button>
			<?php } ?>
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
