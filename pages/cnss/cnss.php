<?php
include 'class/CNSS.class.php';
$getCnss=$cns->Afficher();
// delete CNSS
if(isset($_GET['delete'])){
	$detail=$cns->getDetailCNSS($_GET['delete']);
	//echo 'Recu declaration = '.$detail['recu_declaration'].'<br> Recu Paiement '.$detail['recu_paiement'];
	@unlink('pages/cnss/Recus_Envois_Declarations/'.$detail['recu_declaration']);
	@unlink('pages/cnss/Recus_Paiement/'.$detail['recu_paiement']);
	if($cns->deleteCNSS($_GET['delete'])){
		
		echo "<script>document.location.href='main.php?CNSS'</script>";
	}
}
/*********Update ************/
if(isset($_GET['Modifier'])){
	$detail=$cns->getDetailCNSS($_GET['Modifier']);
}
// Ajout Reçu de paiement 
if(isset($_POST['AjoutReçuPaiement'])){
	if($cns->AjoutRecuPaiement(@$_FILES['Recupaiement']['name'],@$_POST['idCnss'])){
			if($_FILES['Recupaiement']['name']!=''){
	@copy($_FILES['Recupaiement']['tmp_name'],'pages/cnss/Recus_Paiement/'.$_FILES['Recupaiement']['name']);
		}
		echo "<script>document.location.href='main.php?CNSS'</script>";
		
	}
}
// Ajout CNSS
 if(isset($_REQUEST['btnSubmitAjout'])){
	if($cns->Ajout(@$_FILES['recu_declaration']['name'],str_replace("'","\'",@$_POST['trimestre']),str_replace("'","\'",@$_POST['annee']),@$_FILES['recu_paiement']['name']))
	{
	if($_FILES['recu_declaration']['name']!=''){
	@copy($_FILES['recu_declaration']['tmp_name'],'pages/cnss/Recus_Envois_Declarations/'.$_FILES['recu_declaration']['name']);
		}
		if($_FILES['recu_paiement']['name']!=''){
	@copy($_FILES['recu_paiement']['tmp_name'],'pages/cnss/Recus_Paiement/'.$_FILES['recu_paiement']['name']);
		}
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?CNSS'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }
 // Modifier CNSS
 if(isset($_REQUEST['btnSubmitModifier'])){
	if($cns->ModifierCnss(@$_FILES['recu_declaration']['name'],str_replace("'","\'",@$_POST['trimestre']),str_replace("'","\'",@$_POST['annee']),@$_FILES['recu_paiement']['name'],$_GET['Modifier']))
	{
	if($_FILES['recu_declaration']['name']!=''){
	@copy($_FILES['recu_declaration']['tmp_name'],'pages/cnss/Recus_Envois_Declarations/'.$_FILES['recu_declaration']['name']);
		}
		if($_FILES['recu_paiement']['name']!=''){
	@copy($_FILES['recu_paiement']['tmp_name'],'pages/cnss/Recus_Paiement/'.$_FILES['recu_paiement']['name']);
		}
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?CNSS'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }

?>

<!-- Form Ajout  -->
    <div class="card shadow mb-4">
                      
                          <div class="card-body">
				
					<form method="post"  enctype="multipart/form-data"  >
		  <div class="modal-body row">
			   
			
			<div class="mb-3  col-4">
				<label for="recu_declaration" class="col-form-label">Recu d'envoi de déclaration:</label>
				<input type="file"  class="form-control" id="recu_declaration" name="recu_declaration"/>
				   
			</div>
			<div class="mb-3  col-2">
				<label for="Trimestre" class="col-form-label">Trimestre:</label>
				<input type="number" min="1" value="<?=@$detail['trimestre']?>"   class="form-control" id="Trimestre" name="trimestre"/>
				   
			</div>
			 <div class="mb-3 col-2">
				<label for="annee" class="col-form-label">Année:</label>
				<input type="text" value="<?=@$detail['annee']?>"  class="form-control" id="annee" name="annee"/>
			</div>
			
			
			 <div class="mb-3 col-4">
				<label for="recu_paiement" class="col-form-label">Recu de Paiement:</label>
				<input type="file"  class="form-control" id="recu_paiement" name="recu_paiement"/>
			</div>
			</div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<?php if(!isset($_GET['Modifier'])){?>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
			<?php } else {?>
			<button type="submit" class="btn btn-primary" name="btnSubmitModifier" >Modifier</button>
			
			<?php } ?>
		  </div>
		  </form>
	 <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
	  <thead>
             <tr>
				<th >Reçus Envoi de déclaration</th><th >Trimestre</th><th >Année </th><th >Reçus de Paiement</th><th >Modifier</th>
				<th>Supprimer</th>
				</tr>
		</thead>
		<tbody>
		<?php if(!empty($getCnss)){
			foreach($getCnss as $key){
		  ?>
		<!-------- Modal Ajout Reçu Paiement ------>
		  <div class="modal fade" id="AddRecuPaiement<?=$key['id_cnss']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ajouter Reçu de Paiement</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idCnss" value="<?=$key['id_cnss']?>"/>
          <div class="form-group">
            <label for="paiement" class="col-form-label">Reçu de Paiement:</label>
            <input type="file" class="form-control" required id="paiement" name="Recupaiement">
          </div>
         
       
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="AjoutReçuPaiement" class="btn btn-primary">Ajouter</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
             <tr>
				<td >
				<a href="./pages/cnss/Recus_Envois_Declarations/<?=$key['recu_declaration']?>">Télécharger</a>
				</td>
				<td>
				<?=$key['trimestre']?>
				</td>
				<td >
				<?=$key['annee']?>
				</td>
				
				<td>
				<?php if(empty($key['recu_paiement'])){?>
				<a href="#" data-toggle="modal" data-target="#AddRecuPaiement<?=$key['id_cnss']?>">Ajouter Reçu de Paiement</a>
				<?php } else {?>
				<a href="./pages/cnss/Recus_Paiement/<?=$key['recu_paiement']?>">Télécharger</a>
				<?php }?>
				</td>
				<td><a href="?CNSS&Modifier=<?=$key['id_cnss']?>" class="btn btn-warning">Modifier </a></td>
				<td><a href="?CNSS&delete=<?=$key['id_cnss']?>" class="btn btn-danger">Supprimer </a></td>
				</tr>
		<?php }} ?>
		</tbody>
	 
	 
	 </table>
				
						  </div>
                     
                    </div>
