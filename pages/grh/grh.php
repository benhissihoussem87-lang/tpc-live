<?php
include 'class/GRH.class.php';
$getGRH=$grh->getGRH();
// delete GRH
if(isset($_GET['deletePersonnel'])){
	if($grh->deleteRH($_GET['deletePersonnel'])){
		
		echo "<script>document.location.href='main.php?GRH'</script>";
	}
}
// delete Element 
if(isset($_GET['deleteElement'])){
	/**************/
	
	echo '<p> File '.$_GET['file'].'</p>';
		// deleteFile 
		$filename = 'pages/grh/';
if($_GET['element']=='fiche_paie'){	$filename =$filename.'fichesPaie/'.$_GET['file'];}
else if($_GET['element']=='ADRESS'){	$filename =$filename.'adresses/'.$_GET['file'];}
else if($_GET['element']=='DIPLOME'){	$filename =$filename.'diplomes/'.$_GET['file'];}
else if($_GET['element']=='FORMATION'){	$filename =$filename.'Formations/'.$_GET['file'];}
else if($_GET['element']=='CONTRAT'){	$filename =$filename.'contrats/'.$_GET['file'];}
		/*if (file_exists($filename)) {
		if (unlink($filename)) {
			echo "File $filename has been deleted.";
		} else {
			echo "Unable to delete $filename.";
		}
		} else {
			echo "File $filename does not exist.";
		}*/
	
	/**************/
	if($grh->deleteElementRH($_GET['id'],$_GET['element'])){
		if (file_exists($filename)) {@unlink($filename);}
	echo "<script>document.location.href='main.php?GRH'</script>";
	}
}
// Ajout GRH
 if(isset($_REQUEST['btnSubmitAjout'])){
 	if($grh->Ajout(@$_POST['nomPersonnel'],@$_FILES['ADRESS']['name'],str_replace("'","\'",@$_POST['LibelleAdresse']),str_replace("'","\'",@$_POST['TitreDiplome']),@$_FILES['DIPLOME']['name'],str_replace("'","\'",@$_POST['TitreFormation']),@$_FILES['FORMATION']['name'],@$_FILES['fiche_paie']['name'],str_replace("'","\'",@$_POST['typeContrat']),@$_FILES['CONTRAT']['name'],str_replace("'","\'",@$_POST['post']),$_POST['cin']))
	{
		
	if($_FILES['ADRESS']['name']!=''){
	@copy($_FILES['ADRESS']['tmp_name'],'pages/grh/adresses/'.$_FILES['ADRESS']['name']);
		}
		if($_FILES['DIPLOME']['name']!=''){
	@copy($_FILES['DIPLOME']['tmp_name'],'pages/grh/diplomes/'.$_FILES['DIPLOME']['name']);
		}
		
		if($_FILES['FORMATION']['name']!=''){
	@copy($_FILES['FORMATION']['tmp_name'],'pages/grh/Formations/'.$_FILES['FORMATION']['name']);
		}
		
		if($_FILES['fiche_paie']['name']!=''){
	@copy($_FILES['fiche_paie']['tmp_name'],'pages/grh/fichesPaie/'.$_FILES['fiche_paie']['name']);
		}
		
		if($_FILES['CONTRAT']['name']!=''){
	@copy($_FILES['CONTRAT']['tmp_name'],'pages/grh/contrats/'.$_FILES['CONTRAT']['name']);
		}
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?GRH'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }
/*********** Modifier Diplome ***************/
if(isset($_REQUEST['ModifierDiplome'])){
	
 	if($grh->ModifierLibelle(@$_POST['idgrh'],'DIPLOME',@$_FILES['DIPLOME']['name'],$_POST['label'],$_POST['libelle']))
	{
		if($_FILES['DIPLOME']['name']!=''){
	@copy($_FILES['DIPLOME']['tmp_name'],'pages/grh/diplomes/'.$_FILES['DIPLOME']['name']);
		}
	
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?GRH'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }
/*********** Modifier Formation ***************/
if(isset($_REQUEST['ModifierFormation'])){
	
 	if($grh->ModifierLibelle(@$_POST['idgrh'],'FORMATION',@$_FILES['FORMATION']['name'],$_POST['label'],$_POST['libelle']))
	{
		if($_FILES['FORMATION']['name']!=''){
	@copy($_FILES['FORMATION']['tmp_name'],'pages/grh/formations/'.$_FILES['FORMATION']['name']);
		}
	
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?GRH'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }

/*********** Modifier Fiche de paie ***************/
if(isset($_REQUEST['ModifierFichePaie'])){
	
 	if($grh->ModifierLibelle(@$_POST['idgrh'],'fiche_paie',@$_FILES['fiche_paie']['name'],null,null))
	{
		if($_FILES['fiche_paie']['name']!=''){
	@copy($_FILES['fiche_paie']['tmp_name'],'pages/grh/fichesPaie/'.$_FILES['fiche_paie']['name']);
		}
	
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?GRH'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }

/*********** Modifier Contrat ***************/
if(isset($_REQUEST['ModifierContrat'])){
	
 	if($grh->ModifierLibelle(@$_POST['idgrh'],'CONTRAT',@$_FILES['CONTRAT']['name'],$_POST['label'],$_POST['libelle']))
	{
		if($_FILES['CONTRAT']['name']!=''){
	@copy($_FILES['CONTRAT']['tmp_name'],'pages/grh/contrats/'.$_FILES['CONTRAT']['name']);
		}
	
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?GRH'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }

/*********** Modifier Adresse ***************/
if(isset($_REQUEST['ModifierAdresse'])){
	
 	if($grh->ModifierLibelle(@$_POST['idgrh'],'ADRESS',@$_FILES['ADRESS']['name'],$_POST['label'],$_POST['libelle']))
	{
		if($_FILES['ADRESS']['name']!=''){
	@copy($_FILES['ADRESS']['tmp_name'],'pages/grh/adresses/'.$_FILES['ADRESS']['name']);
		}
	
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?GRH'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }

/*********** Modifier Poste ***************/
if(isset($_REQUEST['ModifierPoste'])){
	
 	if($grh->ModifierLibelle(@$_POST['idgrh'],'POSTE',$_POST['POSTE'],null,null))
	{
		
	
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?GRH'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }
/*********** Modifier CIN ***************/
if(isset($_REQUEST['ModifierCin'])){
	
 	if($grh->ModifierLibelle(@$_POST['idgrh'],'numCin',$_POST['cin'],null,null))
	{
	//echo '<h1> OKO</h1>';
	echo "<script>document.location.href='main.php?GRH'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }
?>

<!-- Form Ajout Dossier -->
    <div class="card shadow mb-4">
                      
                          <div class="card-body">
					<!--Form Add Facture-->
					<form method="post"  enctype="multipart/form-data"  >
		  <div class="modal-body row">
			   
			  <div class="mb-3  col-3">
				<label for="nomPersonnel" class="col-form-label">Nom & Prénom Personnel:</label>
				
				<input list="personnels" class="form-control" name="nomPersonnel" id="nomPersonnel">
				<datalist id="personnels">
				<?php if(!empty($getGRH)){
					foreach($getGRH as $key){?>
				  <option value="<?=$key['nomPersonnel']?>">
				<?php }} ?>
				  
				</datalist>
				   
			  </div>
			   <div class="mb-3  col-2">
				<label for="cin" class="col-form-label">N° Cin:</label>
				<input type="text"  class="form-control" id="cin" name="cin"/>
				   
			</div>
			  <div class="mb-3  col-2">
				<label for="LibelleAdresse" class="col-form-label">Libelle adresse:</label>
				<input type="text"  class="form-control" id="LibelleAdresse" name="LibelleAdresse"/>
				   
			</div>
			<div class="mb-3  col-3">
				<label for="ADRESS" class="col-form-label">ADRESS:</label>
				<input type="file"  class="form-control" id="ADRESS" name="ADRESS"/>
				   
			</div>
			<div class="mb-3  col-3">
				<label for="TitreDiplome" class="col-form-label">Libelle Diplome:</label>
				<input type="text"  class="form-control" id="TitreDiplome" name="TitreDiplome"/>
				   
			</div>
			 <div class="mb-3 col-3">
				<label for="DIPLOME" class="col-form-label">DIPLOME:</label>
				<input type="file"  class="form-control" id="DIPLOME" name="DIPLOME"/>
			</div>
			
			<div class="mb-3  col-3">
				<label for="TitreFormation" class="col-form-label">Titre Formation:</label>
				<input type="text"  class="form-control" id="TitreFormation" name="TitreFormation"/>
				   
			</div>
			 <div class="mb-3 col-3">
				<label for="FORMATION" class="col-form-label">Formation:</label>
				<input type="file"  class="form-control" id="FORMATION" name="FORMATION"/>
			</div>
			<div class="mb-3 col-3">
				<label for="fiche_paie" class="col-form-label">fiche de paie:</label>
				<input type="file"  class="form-control" id="fiche_paie" name="fiche_paie"/>
			</div>
			<div class="mb-3  col-3">
				<label for="typeContrat" class="col-form-label">Type Contrat:</label>
				<input type="text"  class="form-control" id="typeContrat" name="typeContrat"/>   
			</div>
			
				<div class="mb-3 col-3">
				<label for="CONTRAT" class="col-form-label">Contrat:</label>
				<input type="file"  class="form-control" id="CONTRAT" name="CONTRAT"/>
			</div>
				<div class="mb-3 col-3">
				<label for="post" class="col-form-label">Poste:</label>
				<input type="text"  class="form-control" id="post" name="post"/>
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
				<th >Personnel</th><th>N° Cin</th><th>Libelle Adresse</th><th >Adresse</th>
				<th >Titre de diplome</th><th width="10%">Diplômes </th><th >Titre Formation</th><th>Formations</th><th >Fiche de paie</th><th>Type Contrat</th><th >Contrat</th><th >Poste</th><th>Supprimer</th>
				</tr>
		</thead>
		<tbody>
		<?php if(!empty($getGRH)){
			foreach($getGRH as $key){
			$getInfoGRH=$grh->getInfoGRHByNomPersonnel($key['nomPersonnel']);
			if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				?>
<!-------- Modal Modifier Diplome ------>
<div class="modal fade" id="ModifierDiplome<?=$detail['id_grh']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier Diplôme</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idgrh" value="<?=$detail['id_grh']?>"/>
		<div class="form-group">
		 <input type="hidden" name="label" value="TitreDiplome"/>
            <label for="libelle" class="col-form-label">Titre de Diplôme:</label>
            <input type="text" value="<?=@$detail['TitreDiplome']?>" class="form-control" required id="libelle" name="libelle">
          </div>
          <div class="form-group">
            <label for="paiement" class="col-form-label">Diplôme:</label>
            <input type="file" class="form-control"  id="paiement" name="DIPLOME">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierDiplome" class="btn btn-primary">Modifier</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
<!-------- Modal Modifier Formation ------>
<div class="modal fade" id="ModifierFormation<?=$detail['id_grh']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier Formation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idgrh" value="<?=$detail['id_grh']?>"/>
		<div class="form-group">
		 <input type="hidden" name="label" value="TitreFormation"/>
            <label for="libelle" class="col-form-label">Titre de Formation:</label>
            <input type="text" value="<?=@$detail['TitreFormation']?>" class="form-control" required id="libelle" name="libelle">
          </div>
          <div class="form-group">
            <label for="paiement" class="col-form-label">Formation:</label>
            <input type="file" class="form-control"  id="paiement" name="FORMATION">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierFormation" class="btn btn-primary">Modifier</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
<!-------- Modal Modifier Fiche de Paie ------>
<div class="modal fade" id="ModifierFichePaie<?=$detail['id_grh']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier Fiche de paie</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idgrh" value="<?=$detail['id_grh']?>"/>
          <div class="form-group">
            <label for="paiement" class="col-form-label">Fiche de Paie:</label>
            <input type="file" class="form-control"  id="paiement" name="fiche_paie">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierFichePaie" class="btn btn-primary">Modifier</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
<!-------- Modal Modifier Contrat ------>
<div class="modal fade" id="ModifierContrat<?=$detail['id_grh']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier Contrat</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idgrh" value="<?=$detail['id_grh']?>"/>
		<div class="form-group">
		 <input type="hidden" name="label" value="typeContrat"/>
            <label for="libelle" class="col-form-label">Type Contrat:</label>
            <input type="text" value="<?=@$detail['typeContrat']?>" class="form-control" required id="libelle" name="libelle">
          </div>
          <div class="form-group">
            <label for="paiement" class="col-form-label">Contrat:</label>
            <input type="file" class="form-control"  id="paiement" name="CONTRAT">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierContrat" class="btn btn-primary">Modifier</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
<!-------- Modal Modifier Adresse ------>
<div class="modal fade" id="ModifierAdresse<?=$detail['id_grh']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier Adresse</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idgrh" value="<?=$detail['id_grh']?>"/>
		 <div class="form-group">
		 <input type="hidden" name="label" value="LibelleAdresse"/>
            <label for="libelle" class="col-form-label">Libelle Adresse:</label>
            <input type="text" value="<?=@$detail['LibelleAdresse']?>" class="form-control" required id="libelle" name="libelle">
          </div>
          <div class="form-group">
            <label for="paiement" class="col-form-label">Adresse:</label>
            <input type="file" class="form-control"  id="paiement" name="ADRESS">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierAdresse" class="btn btn-primary">Modifier</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
<!-------- Modal Modifier Poste ------>
<div class="modal fade" id="ModifierPoste<?=$detail['id_grh']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier Poste</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idgrh" value="<?=$detail['id_grh']?>"/>
		 <div class="form-group">
		 
            <label for="libelle" class="col-form-label">Poste:</label>
            <input type="text" value="<?=@$detail['POSTE']?>" class="form-control" required id="libelle" name="POSTE">
          </div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierPoste" class="btn btn-primary">Modifier</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
<!-------- Modal Modifier/Ajouter  CIN ------>
<div class="modal fade" id="ModalCin<?=$detail['id_grh']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Cin</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="idgrh" value="<?=$detail['id_grh']?>"/>
		<div class="form-group">
		 <input type="hidden" name="label" value="numCin"/>
            <label for="libelle" class="col-form-label">N° Cin:</label>
            <input type="text" value="<?=@$detail['numCin']?>" class="form-control"  id="libelle" name="cin">
          </div>
         
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="ModifierCin" class="btn btn-primary">Valider</button>
      </div>
	  </form>
    </div>
  </div>
</div>
		<!------- Fin Modal ------->
			<?php }}?>
             <tr>
				<td ><?=$key['nomPersonnel']?></td>
				<td >
				  <?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['numCin'])){?>
				<?=$detail['numCin']?>
				  <?php } }}?>
				     <a href="#" data-toggle="modal" data-target="#ModalCin<?=$detail['id_grh']?>">N° Cin</a>
					
				</td>
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['LibelleAdresse'])){?>
			
			<p>-<?=$detail['LibelleAdresse']?><br></p>
			<a href="?GRH&deleteElement&element=ADRESS&id=<?=$detail['id_grh']?>&file=<?=$detail['ADRESS']?>" style="float:left" title="supprimer Adresse">Supp</a>-<a href="#" data-toggle="modal" data-target="#ModifierAdresse<?=$detail['id_grh']?>">Mod </a>
				<?php }}}?>
				</td>
				<td>
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['ADRESS'])){?>
				
				<a href="./pages/grh/adresses/<?=$detail['ADRESS']?>">Télécharger</a>
				<br>
				<a href="?GRH&deleteElement&element=ADRESS&id=<?=$detail['id_grh']?>&file=<?=$detail['ADRESS']?>" style="float:left" title="supprimer Adresse">Supp</a>-<a href="#" data-toggle="modal" data-target="#ModifierAdresse<?=$detail['id_grh']?>">Mod </a>
				</p>
				<?php }}} ?>
				</td>
				<td >
				
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['TitreDiplome'])){?>
			<p style="height:'50px'">-<?=$detail['TitreDiplome']?><br></p>
				<a href="?GRH&deleteElement&element=DIPLOME&id=<?=$detail['id_grh']?>&file=<?=$detail['DIPLOME']?>" style="float:left"  title="supprimer diplôme">Supp</a>  - 
				<a href="#" data-toggle="modal" data-target="#ModifierDiplome<?=$detail['id_grh']?>">Mod </a>
				<?php }}}?>
				</td>
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
			if(!empty($detail['DIPLOME'])){?>
				
				<a href="./pages/grh/diplomes/<?=$detail['DIPLOME']?>">
				<?=$detail['DIPLOME']?>
				</a>
			   <p >
				<a href="?GRH&deleteElement&element=DIPLOME&id=<?=$detail['id_grh']?>&file=<?=$detail['DIPLOME']?>" style="float:left"  title="supprimer diplôme">Supp</a>  - 
				<a href="#" data-toggle="modal" data-target="#ModifierDiplome<?=$detail['id_grh']?>">Mod </a>
				</p>
			<?php } }}?>
				</td>
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['TitreFormation'])){?>
			<p>-<?=$detail['TitreFormation']?><br></p>
			<a href="?GRH&deleteElement&element=FORMATION&id=<?=$detail['id_grh']?>&file=<?=$detail['FORMATION']?>" style="float:left" title="supprimer Formation">Supp </a>- <a href="#" data-toggle="modal" data-target="#ModifierFormation<?=$detail['id_grh']?>">Mod </a>
				<?php }}}?>
				</td>
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['FORMATION'])){?>
				
				<a href="./pages/grh/Formations/<?=$detail['FORMATION']?>">Télécharger</a><br>
				<a href="?GRH&deleteElement&element=FORMATION&id=<?=$detail['id_grh']?>&file=<?=$detail['FORMATION']?>" style="float:left" title="supprimer Formation">Supp </a>- <a href="#" data-toggle="modal" data-target="#ModifierFormation<?=$detail['id_grh']?>">Mod </a>
				<?php }}} ?>
				</td>
				
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['fiche_paie'])){?>
				<p><a href="./pages/grh/fichesPaie/<?=$detail['fiche_paie']?>">Télécharger</a>
				<br>
				<a href="?GRH&deleteElement&element=fiche_paie&id=<?=$detail['id_grh']?>&file=<?=$detail['fiche_paie']?>" style="float:left" title="supprimer Fiche de paie">Supp</a>-<a href="#" data-toggle="modal" data-target="#ModifierFichePaie<?=$detail['id_grh']?>">Mod </a>
				<?php }}} ?>
				</td>
				
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
			if(!empty($detail['typeContrat'])){?>
				<p><?=$detail['typeContrat']?></p>
				<a href="?GRH&deleteElement&element=CONTRAT&id=<?=$detail['id_grh']?>&file=<?=$detail['CONTRAT']?>" style="float:left" title="supprimer contrat">Supp</a>-<a href="#" data-toggle="modal" data-target="#ModifierContrat<?=$detail['id_grh']?>">Mod </a>
			<?php } } } ?>
				</td>
				
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['CONTRAT'])){?>
				<p><a href="./pages/grh/contrats/<?=$detail['CONTRAT']?>">Télécharger</a><br>
				<a href="?GRH&deleteElement&element=CONTRAT&id=<?=$detail['id_grh']?>&file=<?=$detail['CONTRAT']?>" style="float:left" title="supprimer contrat">Supp</a>-<a href="#" data-toggle="modal" data-target="#ModifierContrat<?=$detail['id_grh']?>">Mod </a>
					
			<?php }}} ?>
				</td>
				<td >
				<?php if(!empty($getGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['POSTE'])){?>
			<?=$detail['POSTE']?><br>
			<a href="#" data-toggle="modal" data-target="#ModifierPoste<?=$detail['id_grh']?>">Modifier </a>
			<?php }}}?>
				</td>
				
				<td><a href="?GRH&deletePersonnel=<?=$key['nomPersonnel']?>" class="btn btn-danger">Supprimer </a></td>
				</tr>
		<?php }} ?>
		</tbody>
	 
	 
	 </table>
				
						  </div>
                     
                    </div>
