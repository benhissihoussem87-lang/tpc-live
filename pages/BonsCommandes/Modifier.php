<?php
include 'class/BonsCommandes.class.php';

// detail Bon commande 
$bcParam = $_GET['BC'] ?? null;
$detailBonCommande = null;
if ($bcParam !== null) {
    $detailBonCommande = $bonCommande->detailBC($bcParam);
}
$numBonCommandeFacture = $bcParam;

// Safeguard when nothing is found
if (!$detailBonCommande) {
    echo "<div class=\"alert alert-danger\">Bon de commande introuvable.</div>";
    return;
}

// Ajout Bon Commande
$infoFacture = $bonCommande->getInfosFactureByBonCommande($detailBonCommande['num_bon_commande']);
$info = null;
if (!empty($infoFacture)) {
    foreach ($infoFacture as $infoRow) { $info = $infoRow; }
}

$client = null;
if (empty($detailBonCommande['client'])) {
    $client = $info['nom_client'] ?? '';
} else {
    $client = $detailBonCommande['client'];
}

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

	
 	if($bonCommande->Modifier(@$_POST['date'],@$_FILES['bonCommande']['name'],@$_POST['numBoncommandeClient'],@$_POST['client'],@$_POST['numBoncommandeFacture']))
		 {
			if($_FILES['bonCommande']['name']!=''){
	@copy($_FILES['bonCommande']['tmp_name'],'pages/BonsCommandes/BonCommandes_piecesJointe/'.$_FILES['bonCommande']['name']);
		}
		echo "<script>document.location.href='main.php?Bons_commandes'</script>";
		}
			
	     
	 
	
else {echo "<script>alert('Erreur !!! ')</script>";}
			
	}

?>
<!--  Détail ****-->
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Bons_commandes" class="btn btn-primary active " style="position:relative; top:20px;" >Modifier Bon Commande</a>
							</div>
		

                          <div class="card-body">
						  <!-- ************ Scrolling *****************************---->
<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          Modifier Bon Commande
        </button>
      </h2>
    </div>

   <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
       <div class="card-body">
					<!--Form Add Facture-->
			<form method="post" enctype="multipart/form-data" id="formBordereau"   >
		  <div class="modal-body row">
			 <div class="mb-3 col-3">
				<label for="numBoncommandeFacture" class="col-form-label">N° Bon commande Facture:</label>
				<input type="text" value="<?=$numBonCommandeFacture?>" readonly   required  class="form-control" id="numBoncommandeFacture" name="numBoncommandeFacture"/>
				   
			  </div>
			  <div class="mb-3 col-2">
				<label for="numBoncommandeClient" class="col-form-label">N° Bon commande Client:</label>
				<input type="text"   class="form-control" value="<?=$detailBonCommande['num_bon_commandeClient']?>" id="numBoncommandeClient" name="numBoncommandeClient"/>
				   
			  </div>
			<div class="mb-3 col-2" >
				<label for="client" class="col-form-label">Client :</label>
				<input type="client" value="<?=$client?>" class="form-control" id="client" name="client"/>
				   
			</div>
			<div class="mb-3 col-2" >
				<label for="date" class="col-form-label">Date Bon Commande :</label>
				<input type="date" value="<?=$detailBonCommande['date_bon_commande']?>" required  class="form-control" id="date" name="date"/>
				   
			</div>
			<div class="mb-3 col-3" >
				<label for="bonCommande" class="col-form-label">Joindre Bon commande:</label>
				<input type="file" class="form-control" id="bonCommande" name="bonCommande"/>
				   
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
