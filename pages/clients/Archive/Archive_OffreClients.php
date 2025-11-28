
<?php
include 'class/client.class.php';
include 'class/OffresPrix.class.php';
$clients=$clt->getAllClients();

$offres=$offre->getAllOffres();
$ArchiveOffresClient=$offre->getOffres_PrixArchiveByClient($_GET['client']);
$client=$clt->getClient($_GET['client']);
$idClient=$client['id'];
	
// Add Offre 
 if(isset($_REQUEST['btnSubmitAjout'])){

	 if($offre->Ajout(@$_POST['num_offre'],@$_POST['date'],@$_POST['client'],@$_POST['projet'],@$_POST['prix_unit_htv'],@$_POST['qte'],@$_POST['tva'],@$_POST['prix_ttc']))
	{
	echo "<script>document.location.href='main.php?Gestion_Clients&client=$idClient'</script>";}
else {echo "<script>alert('Erreur !!! ')</script>";}
 }
 ?>


 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
		 <button type="button" class="btn btn-info col-12" style="font-size:x-large">Listes des Offres Archive de Client <span class="btn btn-outline-dark"><?=$client['nom_client']?></span></button>			
                     <div class="btn-group" role="group" aria-label="Basic mixed styles example">
	
 
  <a href="?Gestion_Clients&Archive&Facture=<?=$client['id']?>" class="btn btn-primary">Afficher Factures de Client:  <b><i><?=$client['nom_client']?></i></b></a>
   <button type="button" class="btn btn-primary">Afficher Bon commande</button>
</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" data-order-column="1" data-order-direction="asc">
                                    <thead>
                                        <tr>
											<th >Num Offre</th>
											<th >Date Offre</th>
											 <th >Projet</th>
                                            <th >Prix Unitaire</th>
											<th >Qte</th>
                                            <th >TVA</th>
                                            <th >TTC</th>
                                          
                                           
											<th >Supprimer</th>
											<th >Modifier</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($ArchiveOffresClient)){
										foreach($ArchiveOffresClient as $key){
											 $projets=$clt->getAllProjetsByOffre($key['num_offre']);?>
											 <tr>
												<td><?=$key['num_offre']?></td>
												 <td><?=$key['date']?></td>
												
												<td>
												<?php 
												
												if(!empty($projets)){
													echo '<ul>';
										foreach($projets as $p){?>
												
												<li><?=$p['classement']?></li>
												
												<?php }echo '</ul>';} ?>
												
												</td>
												<td>
												<?php 
												if(!empty($projets)){echo '<ul>';
									   	foreach($projets as $p){?>
												<li><?=$p['prix_unit_htv']?></li>
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
												 <td><?php 
												if(!empty($projets)){echo '<ul>';
									   	foreach($projets as $p){?>
												<li><?=$p['prixTTC']?></li>
												<?php }echo '</ul>';} ?></td>
												  <td>Supprimer</td>
												    <td>Modifier</td>
											 </tr>
									<?php }}?>
									
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
