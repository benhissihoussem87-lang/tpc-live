<?php
include 'class/GRH.class.php';
$getGRH=$grh->getInfoGRHByNomPersonnel($_GET['Modifier']);
if(isset($_GET['idUpdate'])){
$detailGrh=$grh->getDetailGRH($_GET['idUpdate']);
//var_dump($detailGrh);

}
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
 if(isset($_REQUEST['btnSubmitModifier'])){
	
  // Ajout Offre de Prix
 	if($grh->Modifier(@$_POST['nomPersonnel'],@$_FILES['ADRESS']['name'],str_replace("'","\'",@$_POST['LibelleAdresse']),str_replace("'","\'",@$_POST['TitreDiplome']),@$_FILES['DIPLOME']['name'],str_replace("'","\'",@$_POST['TitreFormation']),@$_FILES['FORMATION']['name'],@$_FILES['fiche_paie']['name'],str_replace("'","\'",@$_POST['typeContrat']),@$_FILES['CONTRAT']['name'],str_replace("'","\'",@$_POST['post']),$_GET['idUpdate']))
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

?>

<!-- Form Modifier Personnel -->
    <div class="card shadow mb-4">
                      
   <div class="card-body">
	<?php if(isset($_GET['idUpdate'])){?>
	<form method="post"  enctype="multipart/form-data"  >
		  <div class="modal-body row">
			   
			  <div class="mb-3  col-3">
				<label for="nomPersonnel" class="col-form-label">Nom & Prénom Personnel:</label>
				
				<input list="personnels" value="<?=$_GET['Modifier']?>" class="form-control" name="nomPersonnel" id="nomPersonnel">
				<datalist id="personnels">
				<?php if(!empty($getGRH)){
					foreach($getGRH as $key){?>
				  <option value="<?=$key['nomPersonnel']?>">
				<?php }} ?>
				  
				</datalist>
				   
			  </div>
			  <div class="mb-3  col-3">
				<label for="LibelleAdresse" class="col-form-label">Libelle adresse:</label>
				<input type="text" value="<?=@$detailGrh['LibelleAdresse']?>"  class="form-control" id="LibelleAdresse" name="LibelleAdresse"/>
				   
			 </div>
			<div class="mb-3  col-3">
				<label for="ADRESS" class="col-form-label">ADRESS:</label>
				<input type="file"  class="form-control" id="ADRESS" name="ADRESS"/>
				   
			</div>
			<div class="mb-3  col-3">
				<label for="TitreDiplome" class="col-form-label">Libelle Diplome:</label>
				<input type="text" value="<?=@$detailGrh['TitreDiplome']?>"  class="form-control" id="TitreDiplome" name="TitreDiplome"/>
				   
			</div>
			 <div class="mb-3 col-3">
				<label for="DIPLOME" class="col-form-label">DIPLOME:</label>
				<input type="file"  class="form-control" id="DIPLOME" name="DIPLOME"/>
			</div>
			
			<div class="mb-3  col-3">
				<label for="TitreFormation" class="col-form-label">Titre Formation:</label>
				<input type="text" value="<?=@$detailGrh['TitreFormation']?>"  class="form-control" id="TitreFormation" name="TitreFormation"/>
				   
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
				<input type="text" value="<?=@$detailGrh['typeContrat']?>"  class="form-control" id="typeContrat" name="typeContrat"/>   
			</div>
			
				<div class="mb-3 col-3">
				<label for="CONTRAT" class="col-form-label">Contrat:</label>
				<input type="file"  class="form-control" id="CONTRAT" name="CONTRAT"/>
			</div>
				<div class="mb-3 col-3">
				<label for="post" class="col-form-label">Poste:</label>
				<input type="text" value="<?=@$detailGrh['POST']?>"  class="form-control" id="post" name="post"/>
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitModifier" >Modifier</button>
		  </div>
		</div>
  </form>
 </div>
 
 <?php }?>
	 <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
	  <thead>
             <tr>
				<th >Adresse</th><th >Diplômes </th><th >Formations</th><th >Fiche de paie</th><th >Contrats</th><th >Modifier</th>
				
				</tr>
		</thead>
		<tbody>
		<?php if(!empty($getGRH)){
			foreach($getGRH as $key){
			$getInfoGRH=$grh->getInfoGRHByNomPersonnel($key['nomPersonnel']);
				?>
		 <tr>
				
				<td>
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['LibelleAdresse'])){?>
				<p>-<?=$detail['LibelleAdresse']?><br>
				<a href="./pages/grh/adresses/<?=$detail['ADRESS']?>">Télécharger</a>
				<br>
				
				</p>
				<?php }} }?>
				</td>
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['TitreDiplome'])){?>
				<p>-<?=$detail['TitreDiplome']?><br>
				<a href="./pages/grh/diplomes/<?=$detail['DIPLOME']?>">Télécharger</a>
				<br>
				
			<?php } }}?>
				</td>
				
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['TitreFormation'])){?>
				<p>-<?=$detail['TitreFormation']?><br>
				<a href="./pages/grh/Formations/<?=$detail['FORMATION']?>">Télécharger</a><br>
				
			<?php }} }?>
				</td>
				
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				if(!empty($detail['fiche_paie'])){?>
				<p>-<a href="./pages/grh/fichesPaie/<?=$detail['fiche_paie']?>">Télécharger</a>
				<br>
				
			<?php }} }?>
				</td>
				
				<!--<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
			 if(!empty($detail['typeContrat'])){?>
				<p>-<?=$detail['typeContrat']?>
				 <br>
				<a href="?GRH&deleteElement&element=typeContrat&id=<?=$detail['id_grh']?>" class="btn btn-warning" title="supprimer Type Contrat"><i class="fa fa-eye" aria-hidden="true"></i></a>
				</p>
			<?php } } }?>
				</td>-->
				
				<td >
				<?php if(!empty($getInfoGRH)){
			foreach($getInfoGRH as $detail){
				 if(!empty($detail['CONTRAT'])or !empty($detail['typeContrat'])){?>
				 <p>Type Contrat : <b><?=$detail['typeContrat']?></b></p>
				<p>-<a href="./pages/grh/contrats/<?=$detail['CONTRAT']?>">Télécharger</a><br>
				
			<?php }} }?>
				</td>
				<td><a href="?GRH&Modifier=<?=$key['nomPersonnel']?>&idUpdate=<?=$detail['id_grh']?>" class="btn btn-warning">Modifier </a></td>
				
				</tr>
		<?php }} ?>
		</tbody>
	 
	 
	 </table>
				
						  </div>
                     
                    </div>
