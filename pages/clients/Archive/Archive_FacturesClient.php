
<?php
include 'class/client.class.php';
include 'class/Factures.class.php';
include 'class/Projet.class.php';
$client=$clt->getClient($_GET['Facturesclient']);
$idClient=$client['id'];
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$factures=$facture->getAllFactures();
$facturesClient=$facture->getAllFacturesClient($idClient);

// Générer le numFacture
$anne=date('Y');
		if($factures){
			$nb=count($factures);
			
		$numFacture=intval($nb+1).'/'.$anne;
		}
		else {$numFacture='1/'.$anne;}
	//echo '<h1> Code Client '.$idClient.'</h1>';
		
// Ajout
 if(isset($_REQUEST['btnSubmitAjout'])){

	 if($facture->Ajout(@$_POST['num_fact'],@$_POST['client'],@$_POST['numbondecommande'],@$_POST['date'],@$_POST['prix_unit_htv'],@$_POST['qte'],@$_POST['tva'],@$_POST['remise'],@$_POST['prixForfitaire'],@$_POST['prixTTC'],@$_POST['projet']))
	{
	echo "<script>document.location.href='main.php?Gestion_Clients&Facturesclient=$idClient'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}
 }
 ?>
<!-- Modal Add Facture -->
<div class="modal fade"  id="ModalAddFacture" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" >
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Facture :
		<b><?=$numFacture?></b></h1>
        
      </div>
	  
		  <form method="post"  id="formClient" >
		  <div class="modal-body">
			<div class="mb-3">
			
				<label for="num_fact " class="col-form-label">N° Facture:</label>
				<input type="text" value="<?=$numFacture?>" readOnly class="form-control" id="num_fact " name="num_fact"/>
				   
			</div>
			  <div class="mb-3">
				
				<input type="hidden" readOnly value="<?=$idClient?>" name="client">
				 
				   
			  </div>
			  <div class="mb-3">
			<label for="numboncommande  " class="col-form-label">N° Bon de commande:</label>
				<input type="text"  class="form-control" id="numboncommande  " name="numboncommande "/>
				   
			</div>
			  
			  <div class="mb-3">
				<label for="date" class="col-form-label">Date Facture:</label>
				<input type="date" value="<?=date('Y-m-d')?>"  class="form-control" id="date" name="date"/>
				   
			</div>
			  <div class="mb-3">
				<label for="prix_unit_htv" class="col-form-label">Prix Unitaire H.TVA:</label>
				<input type="text"  class="form-control" id="prix_unit_htv" name="prix_unit_htv"/>
				   
			</div>
			
			<div class="mb-3">
				<label for="qte" class="form-label">Qte </label>
			   <input type="number" min="1" class="form-control" id="qte" name="qte"/>
	  
			</div>
			<div class="mb-3">
				<label for="tva" class="form-label">TVA : </label>
			   <input type="text" class="form-control" id="tva" name="tva"/>
	  
			</div>
			<div class="mb-3">
				<label for="remise" class="form-label">Remise : </label>
			   <input type="text" class="form-control" id="remise" name="remise"/>
	  
			</div>
			<div class="mb-3">
				<label for="prixForfitaire" class="form-label">Prix Forfitaire : </label>
			   <input type="text" class="form-control" id="prixForfitaire" name="prixForfitaire"/>
	  
			</div>
			
			<div class="mb-3">
				<label for="prix_ttc" class="form-label">Prix TTC : </label>
			   <input type="text" class="form-control" id="prix_ttc" name="prixTTC"/>
	  
			</div>
			
			<div class="mb-3">
				<label for="projet" class="col-form-label">Projet:</label>
			
				<select class="form-control" id="projet" name="projet">
				 <?php if(!empty($projets)){
						foreach($projets as $cle){?>
				 <option value="<?=$cle['id']?>"><?=$cle['classement']?></option>
				 <?php }} ?>				 
										
				</select>
			  </div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
		  </div>
		  </form>
		
	
	
    </div>
  </div>
</div>
<!--  Fin Modal Add Offre-->


 <!-- DataTales Example -->
  <div class="card shadow mb-4">
		<button type="button" class="btn btn-info col-12" style="font-size:x-large">Listes des Factures de Client <span class="btn btn-outline-dark"><?=$client['nom_client']?></span></button>	
    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
	<a href="?Gestion_Clients&client=<?=$idClient?>" class="btn btn-primary" >Ajouter des Offres au Client <b><i><?=$client['nom_client']?></i></b></a>

  <button type="button" class="btn btn-primary active" data-bs-toggle="modal" data-bs-target="#ModalAddFacture">Ajouter Facture au Client <b><i><?=$client['nom_client']?></i></b></button>
    <button type="button" class="btn btn-primary ">Afficher Bon commande</button>
</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" data-order-column="1" data-order-direction="asc">
								  <thead>
                                        <tr>
											<th >Num Facture</th>
											<th >Date Facture</th>
                                            <th style="width:30%">Projet</th>
                                            <th >Prix Unitaire</th>
											<th >Qte</th>
                                            <th >TVA</th>
											<th >Remise</th>
											<th >Prix Forfitaire</th>
                                            <th >TTC</th>
                                           
											<th >Supprimer</th>
											<th >Modifier</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($facturesClient)){
										foreach($facturesClient as $key){
				$ProjetsFacture=$facture->get_AllProjets_ByFacture($key['num_fact']);
											?>
											 <tr>
												<td><?=$key['num_fact']?></td>
												 <td><?=$key['date']?></td>
												<td>
										<?php if(!empty($ProjetsFacture)){
											
										foreach($ProjetsFacture as $projet){?>
										 <p><?=$projet['classement']?></p>
										<?php }}?>
												</td>
												<td>
												<?php if(!empty($ProjetsFacture)){
											
										foreach($ProjetsFacture as $projet){?>
										 <p><?=$projet['prix_unit_htv']?></p>
										<?php }}?>
										</td>
												<td>
										<?php if(!empty($ProjetsFacture)){
										
										foreach($ProjetsFacture as $projet){?>
										 <p><?=$projet['qte']?></p>
										<?php }}?>
												</td>
										<td>
								<?php if(!empty($ProjetsFacture)){
											
										foreach($ProjetsFacture as $projet){?>
										 <p><?=$projet['tva']?></p>
										<?php }}?>		
										</td>
												<td>
										<?php if(!empty($ProjetsFacture)){
											
										foreach($ProjetsFacture as $projet){?>
										 <p><?=$projet['remise']?></p>
										<?php }}?>		
												</td>
												<td><?php if(!empty($ProjetsFacture)){
											
										foreach($ProjetsFacture as $projet){?>
										 <p><?=$projet['prixForfitaire']?></p>
										<?php }}?>		</td>
												 <td>
												 <?php if(!empty($ProjetsFacture)){
											
										foreach($ProjetsFacture as $projet){?>
										 <p><?=$projet['prixTTC']?></p>
										<?php }}?>		
												 </td>
												  <td>Supprimer</td>
												    <td>Modifier</td>
											 </tr>
									<?php }}?>
									 </tbody>
                               
                                    </table>
                            </div>
                        </div>
                    </div>
