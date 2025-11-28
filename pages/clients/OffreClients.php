
<?php
include 'class/client.class.php';
include 'class/OffresPrix.class.php';
$clients=$clt->getAllClients();

$offresClient=$clt->getOffresClient($_GET['Offreclient']);
$client=$clt->getClient($_GET['Offreclient']);
$idClient=$client['id'];

 ?>


 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
		 <button type="button" class="btn btn-info col-12" style="font-size:x-large">Client <span class="btn btn-dark text-wheit" ><?=$client['nom_client']?></span></button>			
                     <div class="btn-group" role="group" aria-label="Basic mixed styles example">
	<a href="?Gestion_Clients&Offreclient=<?=$_GET['Offreclient']?>" class="btn btn-primary active"> Liste des Offres   </a>
 
  <a href="?Gestion_Clients&Facturesclient=<?=$client['id']?>" class="btn btn-primary">Liste des Factures</a>
   <a href="?Gestion_Clients&BonsCommandesclient=<?=$client['id']?>"  class="btn btn-primary">Bon commande</a>
</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" data-order-column="1" data-order-direction="asc">
                                    <thead>
                                        <tr>
											<th >Num Offre</th>
											<th >Date Offre</th>
											
                                            <th >Projets</th>
                                            <th >Prix Unitaire</th>
											<th >Qte</th>
                                            <th >TVA</th>
                                          
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($offresClient)){
										foreach($offresClient as $key){
											 $projets=$clt->getAllProjetsByOffre($key['num_offre']);?>
											 <tr>
												<td><?=$key['num_offre']?></td>
												 <td><?=$key['date']?></td>
												
												<td>
												<?php 
												
												if(!empty($projets)){
													echo '<ul >';
										foreach($projets as $p){?>
												
												<li><?=$p['classement']?></li>
												
												<?php }echo '</ul>';} ?>
												
												</td>
												<td>
												<?php 
												if(!empty($projets)){echo '<ul>';
									   	foreach($projets as $p){?>
												<li>
												<?php if(!empty($p['prix_unit_htv'])){?>
												<?=$p['prix_unit_htv']?>
												<?php } else {?>
												   <?=$p['prixForfitaire']?>
												<?php } ?>
												</li>
												<?php }echo '</ul>';} ?>
												</td>
												<td><?php 
												if(!empty($projets)){echo '<ul>';
									   	foreach($projets as $p){?>
												<li><?=$p['qte']?></li>
												<?php }echo '</ul>';} ?></td>
												<td><?php 
												if(!empty($projets)){echo '<ul>';
									   	foreach($projets as $p){?>
												<li><?=$p['tva'].'%'?></li>
												<?php }echo '</ul>';} ?></td>
											
												  
											 </tr>
									<?php }}?>
									
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
