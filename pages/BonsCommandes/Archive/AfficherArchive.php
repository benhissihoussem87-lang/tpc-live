<?php
include 'class/BonsCommandes.class.php';

$ArchiveBonsCommandes=$bonCommande->getAllARchive();
	
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Bons_commandes&Archive&Add" class="btn btn-primary active " style="position:relative; top:20px;"  >Ajout  Archive Bon Commande</a>
							</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered dt-extra-controls" id="dataTable" width="100%" cellspacing="0" data-year-column="1" data-order-column="2" data-order-direction="asc">
                                    <thead>
                                        <tr> 
										    <th >Numéro Bon commande Facture</th>
											<th >Numéro Bon commande Fournisseur</th>
											<th >Date Bon commande</th>
											<th >Client</th>
											
                                            <th >Télécharger</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($ArchiveBonsCommandes)){
										foreach($ArchiveBonsCommandes as $key){?>
											 <tr>
												<td>
												<?=$key['num_bon_commande']?>
												</td>
												<td>
												<?=$key['num_bon_commandeFournisseur']?>
												</td>
												 <td><?=$key['date_bon_commande']?></td>
												<td><?=$key['nom_client']?></td>
												
												
												 
												   <td>
												   <?php if($key['piecejointe']!=null){?>
												   
												   <a href="./pages/BonsCommandes/Archive/Archive_piecesJointe/<?=$key['piecejointe']?>">Télécharger</a>
												   <?php } ?>
												   </td>
											 </tr>
									<?php }}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
