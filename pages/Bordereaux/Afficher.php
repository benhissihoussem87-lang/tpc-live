<?php
include 'class/Bordereaux.class.php';

$bordereaux=$bordereau->getAll();

/*********** delete Facture ***************/
 if(isset($_GET['deleteFacture'])){
	 if($facture->deleteFacture($_GET['deleteFacture'])){
		 echo "<script>document.location.href='main.php?Factures'</script>";
	 }
 }
 
  if(isset($_REQUEST['btnSubmitUpdate'])){
@$types=$_POST['TypeBordereaux'].'<br>'.str_replace("'","\'",$_POST['TypeBordereaux']);
@$dates=$_POST['DateBordereaux'].'<br>'.$_POST['date'];
if($_FILES['bordereau']['name']!=''){
$docs=$_POST['DocsBordereaux'].'<br>'.$_FILES['bordereau']['name'];
}
else {
	$docs=$_POST['DocsBordereaux'];
}

	 if($bordereau->Modifier(@$_POST['idBordereaux'],@$_FILES['bordereau']['name'],@$_POST['DateBordereaux'],str_replace("'","\'",@$_POST['TypeBordereaux'])))
	{
	if($_FILES['bordereau']['name']!=''){
	@copy($_FILES['bordereau']['tmp_name'],'pages/Bordereaux/bordereaux_piecesJointe/'.$_FILES['bordereau']['name']);
		}
		
		echo "<script>document.location.href='main.php?Bordereaux'</script>";
		}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }
		
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Bordereaux&Add" class="btn btn-primary active " style="position:relative; top:20px;"  >Ajout Bordereaux</a>
							</div>
                        
                         <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered dt-extra-controls" id="dataTable" width="100%" cellspacing="0" data-year-column="1" data-order-column="2" data-order-direction="asc">
                                    <thead>
                                        <tr> 
										    <th >Numéro Bordereau</th>
											<th >Num Facture</th>
											<th width="15%">Date Bordereau</th>
											<th >Type Bordereau</th>
											
											<th  width="15%" >Supp/Mod</th>
											<th >Télécharger</th>
											<th >Imprimer</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($bordereaux)){
										foreach($bordereaux as $key){
					$detailBoredereau=$bordereau->GetInfosBordereau($key['num_fact']);
					$AllBordereauxByFacture=$bordereau->getAllFacturesBordereaux($key['num_fact']);
					
											?>
<div class="modal fade" id="ModalUpdateBordereaux<?=$key['num_fact']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content"> 
	<form method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modifier Bordereaux</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
       
          <div class="mb-3">
          
            <input type="text" class="form-control" name="idBordereaux" value="<?=$cle['id_bordereau']?>">
          </div>
		   <div class="mb-3 col-12" >
		   <input type="hidden" name="TypeBordereauxS" value="<?=$detailBoredereau['type']?>"/>
		   <input type="hidden" name="DateBordereauxS" value="<?=$detailBoredereau['date']?>"/>
		   <input type="hidden" name="DocsBordereauxS" value="<?=$detailBoredereau['pieces_jointe']?>"/>
				<label for="type" class="col-form-label">Type :</label>
				<input list="types" class="form-control" value="<?=$detailBoredereau['type']?>" name="TypeBordereaux" id="type" placeholder="Choisir/ecrire le type de bordereau">
				<datalist id="types">
				<option value="ATTESTATION GAZ & ATTESTATION ELECTRIQUE">
				  <option value="Attestation">
				  <option value="Rapport">
				</datalist>
			</div>
			 <div class="mb-3 col-12" >
				<label for="date" class="col-form-label">Date :</label>
				<input type="date"  required  class="form-control" value="<?=$detailBoredereau['date']?>" id="date" name="DateBordereaux"/>
				   
			</div>
			
          <div class="mb-3">
            <label for="message-text" class="col-form-label">Bordereaux</label>
            <input type="file" class="form-control" id="message-text" name="bordereau">
          </div>
      
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="btnSubmitUpdate">Modifier</button>
      </div>
	  </form>
    </div>
  </div>
</div>
<?php if(!empty($AllBordereauxByFacture)){?>
						<!--  Fin ModalUpdate Bordereaux-->
	                 <tr>
												<td>
												
												<?=$key['num_fact']?>
												
												
												</td>
												 <td><?=$key['num_fact']?></td>
												<td>
												<?php foreach($AllBordereauxByFacture as $cle){?>
												<?=$cle['date']?>
												<hr>
												<?php }?>
												
												</td>
												 <td>
												 <?php foreach($AllBordereauxByFacture as $cle){?>
												<?=$cle['type']?>
												<hr>
												
												<?php }?>
												 </td>
												
												  <td>
												   <?php foreach($AllBordereauxByFacture as $cle){?>
												  <a href="#" class="btn btn-danger">Supp</a>
												  
												   <a href="?Bordereaux&Update=<?=$cle['id']?>" class="btn btn-warning" > Mod</a>
												   <hr>
												   <?php } ?>
												   </td>
												   <td>
												   <?php 
												   foreach($AllBordereauxByFacture as $cle){
												   if($cle['pieces_jointe']!=''){
													 $pieces=explode('<br>', $cle['pieces_jointe']);  
													   for($i=0;$i<count($pieces);$i++){
														   if($pieces[$i]!=''){
													   ?>
												  <a href="./pages/Bordereaux/bordereaux_piecesJointe/<?=$pieces[$i]?>">Télécharger : <?=$pieces[$i]?></a><br>
													   <?php } }
												   } }
												   ?>
												   </td>
												      <td>
												   
												   <a href="./pages/Bordereaux/ModeleBordereaux.php?bordereau=<?=$key['num_fact']?>">Imprimer</a></td>
											 </tr>
									<?php }}}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
</div>
