<?php
include 'class/Assurance.class.php';
$getAssurance=$assurance->Afficher();
// delete Assurance
if(isset($_GET['delete'])){
	$detail=$assurance->getDetailAssurance($_GET['delete']);
	
	@unlink('pages/assurance/AttestationsBureau/'.$detail['attestation_Bureau']);
	@unlink('pages/assurance/AttestationsVoiture/'.$detail['attestation_Voiture']);
	@unlink('pages/assurance/Contrats/'.$detail['contrat']);
	@unlink('pages/assurance/QuitanceBureau/'.$detail['quitance_Bureau']);
	@unlink('pages/assurance/QuitanceVoiture/'.$detail['quitance_Voiture']);
	if($assurance->deleteAssurance($_GET['delete'])){
		
		echo "<script>document.location.href='main.php?Assurance'</script>";
	}
}
/*********Update ************/
if(isset($_GET['Modifier'])){
	$detail=$assurance->getDetailAssurance($_GET['Modifier']);
}

// Ajout Assurance
 if(isset($_REQUEST['btnSubmitAjout'])){
	if($assurance->Ajout(@$_FILES['contrat']['name'],@$_POST['annee'],@$_FILES['attestation_Bureau']['name'],@$_FILES['attestation_Voiture']['name'],@$_FILES['quitance_Bureau']['name'],@$_FILES['quitance_Voiture']['name']))
	{
	if($_FILES['contrat']['name']!=''){
	@copy($_FILES['contrat']['tmp_name'],'pages/assurance/Contrats/'.$_FILES['contrat']['name']);
		}
		if($_FILES['attestation_Bureau']['name']!=''){
	@copy($_FILES['attestation_Bureau']['tmp_name'],'pages/assurance/AttestationsBureau/'.$_FILES['attestation_Bureau']['name']);
		}
		if($_FILES['attestation_Voiture']['name']!=''){
	@copy($_FILES['attestation_Voiture']['tmp_name'],'pages/assurance/AttestationsVoiture/'.$_FILES['attestation_Voiture']['name']);
		}
		if($_FILES['quitance_Voiture']['name']!=''){
	@copy($_FILES['quitance_Voiture']['tmp_name'],'pages/assurance/QuitanceVoiture/'.$_FILES['quitance_Voiture']['name']);
		}
		if($_FILES['quitance_Bureau']['name']!=''){
	@copy($_FILES['quitance_Bureau']['tmp_name'],'pages/assurance/QuitanceBureau/'.$_FILES['quitance_Bureau']['name']);
		}
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?Assurance'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }
 // Modifier Contrat Assurance
 if(isset($_REQUEST['ModifierContrat'])){
	 $detail=$assurance->getDetailAssurance($_POST['idAssurance']);
	@unlink('pages/assurance/Contrats/'.$detail['contrat']);
	if($assurance->GestionPieceJointe(@$_POST['libelle'],@$_FILES['contrat']['name'],$_POST['idAssurance']))
	{
	if($_FILES['contrat']['name']!=''){
	@copy($_FILES['contrat']['tmp_name'],'pages/assurance/Contrats/'.$_FILES['contrat']['name']);
   
		}
		
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?Assurance'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }
  // Modifier attestation Bureau
 if(isset($_REQUEST['ModifierAttestationBureau'])){
	 $detail=$assurance->getDetailAssurance($_POST['idAssurance']);
	@unlink('pages/assurance/AttestationsBureau/'.$detail['attestation_Bureau']);
	if($assurance->GestionPieceJointe(@$_POST['libelle'],@$_FILES['attestation_Bureau']['name'],$_POST['idAssurance']))
	{
	if($_FILES['attestation_Bureau']['name']!=''){
	@copy($_FILES['attestation_Bureau']['tmp_name'],'pages/assurance/AttestationsBureau/'.$_FILES['attestation_Bureau']['name']);
   }
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?Assurance'</script>";
	}
 else {echo "<script>alert('Erreur !!! ')</script>";}

 }
// Modifier attestation Voiture
 if(isset($_REQUEST['ModifierAttestationVoiture'])){
	 $detail=$assurance->getDetailAssurance($_POST['idAssurance']);
	@unlink('pages/assurance/AttestationsVoiture/'.$detail['attestation_Voiture']);
	if($assurance->GestionPieceJointe(@$_POST['libelle'],@$_FILES['attestation_Voiture']['name'],$_POST['idAssurance']))
	{
	if($_FILES['attestation_Voiture']['name']!=''){
	@copy($_FILES['attestation_Voiture']['tmp_name'],'pages/assurance/AttestationsVoiture/'.$_FILES['attestation_Voiture']['name']);
   }
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?Assurance'</script>";
	}
 else {echo "<script>alert('Erreur !!! ')</script>";}

 }
 
 // Modifier Quitance Voiture
 if(isset($_REQUEST['ModifierQuitanceVoiture'])){
	 $detail=$assurance->getDetailAssurance($_POST['idAssurance']);
	@unlink('pages/assurance/QuitanceVoiture/'.$detail['quitance_Voiture']);
	if($assurance->GestionPieceJointe(@$_POST['libelle'],@$_FILES['quitance_Voiture']['name'],$_POST['idAssurance']))
	{
	if($_FILES['quitance_Voiture']['name']!=''){
	@copy($_FILES['quitance_Voiture']['tmp_name'],'pages/assurance/QuitanceVoiture/'.$_FILES['quitance_Voiture']['name']);
   }
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?Assurance'</script>";
	}
 else {echo "<script>alert('Erreur !!! ')</script>";}

 }
 
 // Modifier Quitance Bureau
 if(isset($_REQUEST['ModifierQuitanceBureau'])){
	 $detail=$assurance->getDetailAssurance($_POST['idAssurance']);
	@unlink('pages/assurance/QuitanceBureau/'.$detail['quitance_Bureau']);
	if($assurance->GestionPieceJointe(@$_POST['libelle'],@$_FILES['quitance_Bureau']['name'],$_POST['idAssurance']))
	{
	if($_FILES['quitance_Bureau']['name']!=''){
	@copy($_FILES['quitance_Bureau']['tmp_name'],'pages/assurance/QuitanceBureau/'.$_FILES['quitance_Bureau']['name']);
   }
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?Assurance'</script>";
	}
 else {echo "<script>alert('Erreur !!! ')</script>";}

 }

/****** Modifier Assurance ******/

 if(isset($_REQUEST['btnSubmitModifier'])){
	 if($_FILES['contrat']['name']!=''){$contrat=$_FILES['contrat']['name'];}
	 else {$contrat=@$detail['contrat'];}
	  if($_FILES['attestation_Bureau']['name']!=''){$attestation_Bureau=$_FILES['attestation_Bureau']['name'];}else {$attestation_Bureau=@$detail['attestation_Bureau'];}
	  
	  if($_FILES['attestation_Voiture']['name']!=''){$attestation_Voiture=$_FILES['attestation_Voiture']['name'];}else {$attestation_Voiture=@$detail['attestation_Voiture'];}
	  
	   if($_FILES['quitance_Bureau']['name']!=''){$quitance_Bureau=$_FILES['quitance_Bureau']['name'];}else {$quitance_Bureau=@$detail['quitance_Bureau'];}
	   
	     if($_FILES['quitance_Voiture']['name']!=''){$quitance_Voiture=$_FILES['quitance_Voiture']['name'];}else {$quitance_Voiture=@$detail['quitance_Voiture'];}
	  
	if($assurance->ModifierAssurance(@$contrat,@$_POST['annee'],@$attestation_Bureau,@$attestation_Voiture,@$quitance_Bureau,@$quitance_Voiture,$_GET['Modifier']))
	{
	if($_FILES['contrat']['name']!=''){
	@copy($_FILES['contrat']['tmp_name'],'pages/assurance/Contrats/'.$_FILES['contrat']['name']);
		}
		if($_FILES['attestation_Bureau']['name']!=''){
	@copy($_FILES['attestation_Bureau']['tmp_name'],'pages/assurance/AttestationsBureau/'.$_FILES['attestation_Bureau']['name']);
		}
		if($_FILES['attestation_Voiture']['name']!=''){
	@copy($_FILES['attestation_Voiture']['tmp_name'],'pages/assurance/AttestationsVoiture/'.$_FILES['attestation_Voiture']['name']);
		}
		if($_FILES['quitance_Voiture']['name']!=''){
	@copy($_FILES['quitance_Voiture']['tmp_name'],'pages/assurance/QuitanceVoiture/'.$_FILES['quitance_Voiture']['name']);
		}
		if($_FILES['quitance_Bureau']['name']!=''){
	@copy($_FILES['quitance_Bureau']['tmp_name'],'pages/assurance/QuitanceBureau/'.$_FILES['quitance_Bureau']['name']);
		}
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?Assurance'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }

?>

<!-- Form Ajout  -->
    <div class="card shadow mb-4">
                      
                          <div class="card-body">
				
					<form method="post"  enctype="multipart/form-data"  >
		  <div class="modal-body row">
			   <div class="mb-3  col-2">
				<label for="annee" class="col-form-label">Annee:</label>
				<input type="number"  value="<?=@$detail['annee']?>"   class="form-control" id="annee" name="annee"/>
				   
			</div>
			
			<div class="mb-3  col-4">
				<label for="contrat" class="col-form-label">Contrat:</label>
				<input type="file"  class="form-control" id="contrat" name="contrat"/>
				   
			</div>
			
			 <div class="mb-3 col-4">
				<label for="attestation_Bureau" class="col-form-label">Attestation de bureau:</label>
				<input type="file"  class="form-control" id="attestation_Bureau" name="attestation_Bureau"/>
			</div>
			
			<div class="mb-3 col-4">
				<label for="attestation_Voiture" class="col-form-label">Attestation de Voiture:</label>
				<input type="file"  class="form-control" id="attestation_Voiture" name="attestation_Voiture"/>
			</div>
			<div class="mb-3 col-4">
				<label for="quitance_Bureau" class="col-form-label">Quitance Bureau:</label>
				<input type="file"  class="form-control" id="quitance_Bureau" name="quitance_Bureau"/>
			</div>
			
			<div class="mb-3 col-4">
				<label for="quitance_Voiture" class="col-form-label">Quitance Voiture:</label>
				<input type="file"  class="form-control" id="quitance_Voiture" name="quitance_Voiture"/>
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
				<th >Annee</th><th >Contrats</th><th >Attestation bureau </th><th >Attestation Voiture</th><th >Quitance bureau </th><th >Quitance Voiture </th><th >Modifier</th>
				<th>Supprimer</th>
				</tr>
		</thead>
		<tbody>
		<?php if(!empty($getAssurance)){
			foreach($getAssurance as $key){
		  ?>
		
    <!-------- Modal Modifier Contrat ------>
		  <div class="modal fade" id="ModifierContrat<?=$key['id_assurance']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Contrat </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idAssurance" value="<?=$key['id_assurance']?>"/>
		 <input type="hidden" name="libelle" value="contrat"/>
		
          <div class="form-group">
            <label for="contrat" class="col-form-label">Contrat:</label>
            <input type="file" class="form-control" required id="contrat" name="contrat">
          </div>
         
       
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierContrat" class="btn btn-primary">Valider</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
    <!-------- Modal Modifier Attestation Voiture ------>
		  <div class="modal fade" id="ModifierAttestationVoiture<?=$key['id_assurance']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"> Attestation Voiture </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idAssurance" value="<?=$key['id_assurance']?>"/>
		 <input type="hidden" name="libelle" value="attestation_Voiture"/>
		
          <div class="form-group">
            <label for="contrat" class="col-form-label">Attestation:</label>
            <input type="file" class="form-control"  id="contrat" name="attestation_Voiture">
          </div>
         
       
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierAttestationVoiture" class="btn btn-primary">Valider</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
  
<!-------- Modal Modifier Attestation Bureau ------>
		  <div class="modal fade" id="ModifierAttestationBureau<?=$key['id_assurance']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Attestation Bureau </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idAssurance" value="<?=$key['id_assurance']?>"/>
		 <input type="hidden" name="libelle" value="attestation_Bureau"/>
		
          <div class="form-group">
            <label for="contrat" class="col-form-label">Attestation:</label>
            <input type="file" class="form-control"  id="contrat" name="attestation_Bureau">
          </div>
         
       
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierAttestationBureau" class="btn btn-primary">Valider</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
    
<!-------- Modal Modifier Quitance bureau ------>
		  <div class="modal fade" id="ModifierQuitanceBureau<?=$key['id_assurance']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Quitance Bureau </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idAssurance" value="<?=$key['id_assurance']?>"/>
		 <input type="hidden" name="libelle" value="quitance_Bureau"/>
		
          <div class="form-group">
            <label for="contrat" class="col-form-label">Quitance:</label>
            <input type="file" class="form-control"  id="contrat" name="quitance_Bureau">
          </div>
         
       
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierQuitanceBureau" class="btn btn-primary">Valider</button>
      </div>
	  </form>
    </div>
  </div>
</div>
<!------- Fin Modal ------->
 
<!-------- Modal Modifier Quitance Voiture ------>
		  <div class="modal fade" id="ModifierQuitanceVoiture<?=$key['id_assurance']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Quitance Voiture </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idAssurance" value="<?=$key['id_assurance']?>"/>
		 <input type="hidden" name="libelle" value="quitance_Voiture"/>
		
          <div class="form-group">
            <label for="contrat" class="col-form-label">Quitance Voiture:</label>
            <input type="file" class="form-control"  id="contrat" name="quitance_Voiture">
          </div>
         
       
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierQuitanceVoiture" class="btn btn-primary">Valider</button>
      </div>
	  </form>
    </div>
  </div>
</div>
<!------- Fin Modal ------->
  

		<tr>
			 <td >
				<?=$key['annee']?>
				</td>
				<td >
				<?php if(empty($key['contrat'])){?>
				<a href="#" data-toggle="modal" data-target="#ModifierContrat<?=$key['id_assurance']?>">Ajouter Contrat</a>
				<?php } else {?>
				<a href="./pages/assurance/Contrats/<?=$key['contrat']?>">Télécharger</a><br>
				<a href="#" data-toggle="modal" data-target="#ModifierContrat<?=$key['id_assurance']?>">
				Modifier</a>
				<?php }?>
				</td>
				<td>
				<?php if(empty($key['attestation_Bureau'])){?>
				<a href="#" data-toggle="modal" data-target="#ModifierAttestationBureau<?=$key['id_assurance']?>">Ajouter Attestation Bureau</a>
				<?php } else {?>
				<a href="./pages/assurance/AttestationsBureau/<?=$key['attestation_Bureau']?>">Télécharger</a><br>
				<a href="#" data-toggle="modal" data-target="#ModifierAttestationBureau<?=$key['id_assurance']?>">
				Modifier</a>
				<?php }?>
				</td>
				<td>
				<?php if(empty($key['attestation_Voiture'])){?>
				<a href="#" data-toggle="modal" data-target="#ModifierAttestationVoiture<?=$key['id_assurance']?>">Ajouter Attestation</a>
				<?php } else {?>
				<a href="./pages/assurance/AttestationsVoiture/<?=$key['attestation_Voiture']?>">Télécharger</a><br>
				<a href="#" data-toggle="modal" data-target="#ModifierAttestationVoiture<?=$key['id_assurance']?>">
				Modifier</a>
				<?php }?>
				</td>
				<td>
				<?php if(empty($key['quitance_Bureau'])){?>
				<a href="#" data-toggle="modal" data-target="#ModifierQuitanceBureau<?=$key['id_assurance']?>">Ajouter Quitance Bureau</a>
				<?php } else {?>
				<a href="./pages/assurance/QuitanceBureau/<?=$key['quitance_Bureau']?>">Télécharger</a><br>
				<a href="#" data-toggle="modal" data-target="#ModifierQuitanceBureau<?=$key['id_assurance']?>">
				Modifier</a>
				<?php }?>
				</td>
				<td>
				<?php if(empty($key['quitance_Voiture'])){?>
				<a href="#" data-toggle="modal" data-target="#ModifierQuitanceVoiture<?=$key['id_assurance']?>">Ajouter Quitance</a>
				<?php } else {?>
				<a href="./pages/assurance/QuitanceVoiture/<?=$key['quitance_Voiture']?>">Télécharger</a><br>
				<a href="#" data-toggle="modal" data-target="#ModifierQuitanceVoiture<?=$key['id_assurance']?>">
				Modifier</a>
				<?php }?>
				</td>
				<td>
				
				<a href="?Assurance&Modifier=<?=$key['id_assurance']?>" class="btn btn-warning">Modifier </a></td>
				<td><a href="?Assurance&delete=<?=$key['id_assurance']?>" class="btn btn-danger">Supprimer </a></td>
				</tr>
		<?php }} ?>
		</tbody>
	 
	 
	 </table>
				
						  </div>
                     
                    </div>
