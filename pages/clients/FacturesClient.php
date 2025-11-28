
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


 ?>

 <!-- DataTales Example -->
  <div class="card shadow mb-4">
		 <button type="button" class="btn btn-info col-12" style="font-size:x-large">Client <span class="btn btn-dark text-wheit" ><?=$client['nom_client']?></span></button>			
                     <div class="btn-group" role="group" aria-label="Basic mixed styles example">
	<a href="?Gestion_Clients&Offreclient=<?=$client['id']?>" class="btn btn-primary "> Liste des Offres   </a>
 
  <a href="?Gestion_Clients&Facturesclient=<?=$client['id']?>" class="btn btn-primary active">Liste des Factures</a>
   <a href="?Gestion_Clients&BonsCommandesclient=<?=$client['id']?>"  class="btn btn-primary">Bon commande</a>
</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" data-order-column="1" data-order-direction="asc">
								  <thead>
                                        <tr>
											<th >Num Facture</th>
											<th >Date Facture</th>
                                            <th style="width:30%">Projets</th>
                                            <th >Prix Unitaire</th>
											<th >Qte</th>
                                            <th >TVA</th>
											<th >Remise</th>
											<th >Prix Forfitaire</th>
                                           
                                            
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
												
												 
											 </tr>
									<?php }}?>
									 </tbody>
                               
                                    </table>
                            </div>
                        </div>
                    </div>
