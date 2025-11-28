<?php
include 'class/Reglements.class.php';
include 'class/client.class.php';
include 'class/Factures.class.php';
$clients=$clt->getAllClients();
$factures=$facture->AfficherFacturesArchives();
// verfif Facture a reglement ou Non
@$reglementFacture=$reglement->getReglementByFactureArchive($_GET['Facture']);
$typeReglement=explode(',',$reglementFacture['TypeReglement']);
$NumCheques=explode(',',$reglementFacture['num_cheque']);
$DateCheques=explode(',',$reglementFacture['date_cheque']);
$Montants=explode(',',$reglementFacture['montant']);
$DateReglements=explode(',',$reglementFacture['dateReglement']);
//var_dump($reglementFacture);
//******** detailFacture  ********//
@$detailFacture=$facture->detailFactureArchive($_GET['Facture']);
//var_dump($detailFacture);
if(isset($detailFacture)){
	$date=@$detailFacture['date'];
	$nbCommandeClient=@$detailFacture['numboncommande'];
	//echo '<h1> Nb commande client '.$nbCommandeClient.'</h1>';
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
if(isset($_GET['BonCommandeClient']) ){
				
				 $numBonCommandeClient=$_GET['BonCommandeClient'];
			 }
else {
 $numBonCommandeClient='';	
}
 @$Numero_Facture=$_GET['Facture'];
/***************************************/
 if(isset($_REQUEST['btnSubmitAjout'])){
	/*if(!isset($_GET['Facture'])){
		@$reglementFacture=$reglement->getReglementByFacture($_POST['facture']);
	}*/
	
	 	if(isset($_GET['Facture'])){$client=$idClient;}
		else {$client=$_POST['client'];}
   //echo '<h1>Client === '.$client.'</h1>';
		 $typeReglement=@implode(",",$_POST['typeReglement']);
		$numCheque=@implode(",",$_POST['numCheque']);
	  $etatReglement=$_POST['etatReglement'];
	  $dateCheque=@implode(",",$_POST['dateCheque']);
	  $retenueCheque=$_POST['retenueCheque'];
      $montant=@implode(",",$_POST['montant']);
	  $dateReglement=@implode(",",$_POST['dateReglement']);
       if($reglement->ModifierArchive(@$_POST['facture'],@$_POST['prixTTC'],@$etatReglement,@$numCheque,@$dateCheque,@$retenueCheque,@$typeReglement,@$montant,@$dateReglement,@$_FILES['pieceRs']['name']))
		 {
			 if($_FILES['pieceRs']['name']!=''){
	@copy($_FILES['pieceRs']['tmp_name'],'pages/Reglements/Archive/PiecesRS_Archive/'.$_FILES['pieceRs']['name']);
		}
	 echo "<script>document.location.href='main.php?Reglements&Archive'</script>";	
	}
	 

	
else {echo "<script>alert('Erreur !!! ')</script>";}
	
/**********************/
}
 /********* Modfiier Reglement **********/
  if(isset($_REQUEST['btnSubmitModifier'])){
	 
	  $typeReglement=@implode(",",$_POST['typeReglement']);
		$numCheque=@implode(",",$_POST['numCheque']);
	  $etatReglement=$_POST['etatReglement'];
	  $dateCheque=@implode(",",$_POST['dateCheque']);
	  $retenueCheque=$_POST['retenueCheque'];
      $montant=@implode(",",$_POST['montant']);
	  $dateReglement=@implode(",",$_POST['dateReglement']);
	
   // si la facture  a reglement 
   if($reglementFacture){
	   // Modifier Reglement 
	   if($reglement->Modifier(@$_POST['facture'],@$_POST['prixTTC'],@$etatReglement,@$numCheque,@$dateCheque,@$retenueCheque,@$typeReglement,@$montant,@$dateReglement,@$_FILES['pieceRs']['name']))		 {
		    if($_FILES['pieceRs']['name']!=''){
	@copy($_FILES['pieceRs']['tmp_name'],'pages/Reglements/PiecesRS/'.$_FILES['pieceRs']['name']);
		}
			 if(empty($_GET['BonCommandeClient'])){
				  echo "<script>document.location.href='main.php?Reglements'</script>";	
			 }else {
				 echo "<script>document.location.href='main.php?Bons_commandes&Add&ModifierBC&Facture=$Numero_Facture&BonCommande=$numBonCommandeClient'</script>";
			 }
	 
	  }
	  else {
		   if(empty($_GET['BonCommandeClient'])){
				  echo "<script>document.location.href='main.php?Reglements'</script>";	
			 }else {
				 echo "<script>document.location.href='main.php?Bons_commandes&Add&ModifierBC&Facture=$Numero_Facture&BonCommande=$numBonCommandeClient'</script>";
			 }
	  }
  
   }
 	else {
		if($reglement->Ajout(@$_POST['client'],@$_POST['facture'],@$_POST['prixTTC'],@$_POST['etatReglement'],@$_POST['numCheque'],@$_POST['dateCheque'],@$_POST['retenueCheque'],@$_POST['typeReglement']))		 {
	  echo "<script>document.location.href='main.php?Reglements'</script>";		}
   else {echo "<script>alert('Erreur !!! ')</script>";}			
	}
	
 }
?>
<!--  Détail ****-->



 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center;margin-bottom:20px" class="col-12">
                            <a href="?Reglements&Archive" class="btn btn-primary active " style="position:relative; top:20px;" >Afficher  Reglements Archive</a>
							</div>
		

                          <div class="card-body">
						  <!-- ************ Scrolling *****************************---->
<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
         Ajout  Reglements Archive
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
				<?php if(isset($_GET['Facture'])){?>
				    <input type="text" class="form-control" value="<?=$_GET['Facture']?>" readOnly id="facture" name="facture" />
				<?php } else {?>
				<select class="form-control" id="facture" name="facture" required>
				
				 <?php if(!empty($factures)){
						foreach($factures as $key){?>
				 <option value="<?=$key['num_fact']?>"><?=$key['num_fact']?></option>
				 <?php }} } ?>				 
										
				</select>
				   
			  </div>
			  
			  <div class="mb-3 col-3">
				<label for="client" class="col-form-label">Client:</label>
				<?php if(isset($_GET['Facture'])){?>
				  <input type="text" name="client"  class="form-control"  value="<?=$detailFacture['nom_client']?>" />
				<?php }else {?>
				<select class="form-control" id="client" name="client" required>
				
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>"><?=$key['nom_client']?></option>
				 <?php }} ?>				 
										
				</select>
				<?php } ?>
				   
			  </div>
			 
			  <div class="mb-3 col-1" >
				<label for="prixTTC" class="col-form-label">Prix :</label>
				<input type="text" autocomplete='off' <?php if($reglementFacture){?> value='<?=$reglementFacture['prix_ttc']?>'<?php } ?>    class="form-control" id="prixTTC" name="prixTTC"/>
				   
			</div>
			<div class="mb-3 col-2" >
				<label for="etatReglement" class="col-form-label">Reglement:</label>
				<?php if(isset($_GET['Facture'])){?>
				    <input type="text" id="etatReglement" name="etatReglement" class="form-control" value="<?=$detailFacture['reglement']?>"  id="facture" name="facture" />
				<?php } else {?>
				<select class="form-control" id="etatReglement" name="etatReglement">
				<option >Oui</option>
				<option>Non</option>
				<option>Avance</option>
				</select>
				<?php } ?>
				   
			</div>
			 <div class="mb-3 col-2" >
				<label for="pieceRs" class="col-form-label">Piece RS :</label>
				<input type="file"  class="form-control" id="pieceRs" name="pieceRs"/>
				   
			</div>
			<div class="mb-3 col-2" >
				<label for="retenueCheque" class="col-form-label">Retenue Date:</label>
				<input type="date" class="form-control" value="<?=@$detailReglement['retenue_date']?>" id="retenueCheque" name="retenueCheque"/>
				   
			</div>
	<?php for($i=0;$i<5;$i++){?>
		<h3 class="col-12"> Reglement <?=intval($i+1)?></h3>
			
			<div class="mb-3 col-2" >
				<label for="typeReglement" class="col-form-label">Type Reglement :</label>
			<input list="typesReglement" id="typeReglement" <?php if($reglementFacture){?>value='<?=@$typeReglement[$i]?>'<?php } ?> class="form-control" name="typeReglement[]"  />
				<datalist id="typesReglement">
					  <option value="Chèque"></option>
					  <option value="Espèce"></option>
					  
					</datalist>
				   
			</div>
			<div class="mb-3 col-2" >
				<label for="montant" class="col-form-label">Montant :</label>
				<input type="text" class="form-control" <?php if($reglementFacture){?>value='<?=@$Montants[$i]?>'<?php } ?> id="montant" name="montant[]"/>
				   
			</div>
			<div class="mb-3 col-3" >
				<label for="numCheque" class="col-form-label">N° Cheque :</label>
				<input type="text" class="form-control" <?php if($reglementFacture){?>value='<?=@$NumCheques[$i]?>'<?php } ?> id="numCheque" name="numCheque[]"/>
				   
			</div>
			
			<div class="mb-3 col-2" >
				<label for="dateCheque" class="col-form-label">Date Cheque:</label>
				<input type="date" class="form-control" <?php if($reglementFacture){?> value='<?=@$DateCheques[$i]?>'<?php } ?> id="dateCheque" name="dateCheque[]"/>
				   
			</div>
			
			<div class="mb-3 col-2" >
				<label for="dateReglement" class="col-form-label">Date Reglement:</label>
				<input type="date" class="form-control" <?php if($reglementFacture){?> value='<?=@$DateReglements[$i]?>'<?php } ?> id="dateReglement" name="dateReglement[]"/>
				   
			</div>

    <?php }?>
  
		  <div class="modal-footer d-flex w-100" >
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<?php if(!isset($_GET['Modifier'])){?>
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
