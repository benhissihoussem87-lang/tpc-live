<?php

include 'class/Factures.class.php';
$numFacture=$_GET['Projets'];
$Projets_facture=$facture->AfficherProjets_By_Facture($numFacture);
/*********** delete Projet Facture ***************/
 if(isset($_GET['deleteProject'])){
	 if($facture->deleteProjetFacture($_GET['deleteProject'])){
		 echo "<script>document.location.href='main.php?Factures&Projets=$numFacture'</script>";
	 }
 }	
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
					<div class="row align-items-start ">
					<div class="btn-group col-12"  role="group" aria-label="Basic mixed styles example">
  <a href="?Factures&Add" class="btn btn-primary active col-5" >Ajouter Projet au Facture <span><?=$_GET['Projets']?></span></a>
  <a href="?Factures&modifier=<?=$numFacture?>" class="btn btn-warning">Modifier</a>
  <a href="./pages/Factures/ModeleFacture.php?facture=<?=$_GET['Projets']?>" class="btn btn-success">Imprimer</a>
</div>
                       <div style="text-align:center" class="col-5">
                            
							</div>
						 
					</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
										
											<th >Projet</th>
											<th >P.U.H.Tva</th>
                                            <th >Qte</th>
                                            <th>TVA</th>
											<th>Remise</th>
											<th>Prix Forfitaire</th>
											<th>TTC</th>
											
											<th >Supprimer Projet </th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($Projets_facture)){
										foreach($Projets_facture as $key){?>
											 <tr>
											
												<td><?=$key['classement']?></td>
												<td><?=$key['prix_unit_htv']?></td>
												<td><?=$key['qte']?></td>
												<td><?=$key['tva']?></td>
												<td><?=$key['remise']?></td>
												<td><?=$key['prixForfitaire']?></td>
												<td><?=$key['prixTTC']?></td>
										       <td>
											   <a href="?Factures&Projets=<?=$_GET['Projets']?>&deleteProject=<?=$key['id_Projets_Facture']?>" class="btn btn-danger">Supprimer Projet</a>
												  </td>
											 </tr>
									<?php }}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
