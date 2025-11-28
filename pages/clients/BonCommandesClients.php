
<?php
include 'class/client.class.php';
include 'class/BonsCommandes.class.php';
$BonsCommandes=$bonCommande->getBonCommandes_Client($_GET['BonsCommandesclient']);
$client=$clt->getClient($_GET['BonsCommandesclient']);
 ?>


 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
		 <button type="button" class="btn btn-info col-12" style="font-size:x-large">Client <span class="btn btn-dark text-wheit" ><?=$client['nom_client']?></span></button>			
                     <div class="btn-group" role="group" aria-label="Basic mixed styles example">
	<a href="?Gestion_Clients&Offreclient=<?=$client['id']?>" class="btn btn-primary"> Liste des Offres   </a>
 
  <a href="?Gestion_Clients&Facturesclient=<?=$client['id']?>" class="btn btn-primary">Liste des Factures</a>
   <a href="?Gestion_Clients&BonsCommandesclient=<?=$client['id']?>"  class="btn btn-primary active">Bon commande</a>
</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" data-order-column="2" data-order-direction="asc">
                                    <thead>
                                        <tr>
											<th >Num Bon Commande Facture</th>
											<th >Num Bon Commande Client</th>
											<th >Date </th>
											<th >Piece Jointe</th>
                                         </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($BonsCommandes)){
										foreach($BonsCommandes as $key){
											?>
											 <tr>
												<td><?=$key['num_bon_commande']?></td>
												 <td><?=$key['num_bon_commandeClient']?></td>
												
												<td>
												<?=$key['date_bon_commande']?>
												</td>
												
												<td>
												<?php if(!empty($key['piecejointe'])){?>
												<a href="../BonsCommandes/BonCommandes_piecesJointe/"<?=$key['piecejointe']?>><?=$key['piecejointe']?> Télécharger</a>
												<?php }?>
												</td>
												
												
												
											
												  
											 </tr>
									<?php }}?>
									
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
