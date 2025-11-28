<?php
include 'class/Reglements.class.php';

$ArchiveReglements=$reglement->getAllARchive();
	
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Reglements&Archive&Add" class="btn btn-primary active " style="position:relative; top:20px;"  >Ajout  Archive Reglement</a>
							</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered dt-extra-controls" id="dataTable" width="100%" cellspacing="0" data-year-column="1" data-order-column="5" data-order-direction="asc">
                                    <thead>
                                        <tr> 
										    <th >N° Facture</th>
											<th >Client</th>
											<th >Prix TTC</th>
											<th >Etat Reglement</th>
											<th >Type Reglement</th>
											<!--<th >N° Cheque</th>-->
											<th >Date Cheque</th>
											<th >Date Retenue</th>
											<th >Piece Retenue</th>
											<th>Ajouter</th>
											<th >Modifier</th>
											
                                           
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($ArchiveReglements)){
										foreach($ArchiveReglements as $key){
										$DetailReglements=$reglement->getAllReglementArchive($key['num_fact_archive']);
											?>
											 <tr>
												<td>
												<?=$key['num_fact_archive']?>
												</td>
												 <td><?=$key['nom_client']?></td>
												<td>
												  <?php foreach($DetailReglements as $cle){?>
									<?=$cle['prix_ttc']?>
									 <?php }?>
												</td>
												<td><?php foreach($DetailReglements as $cle){?>
									 <?=$cle['etat_reglement']?>
									 <?php }?></td>
							<td>
							 <?php foreach($DetailReglements as $cle){$typesReglements=explode(',',$cle['TypeReglement']);
					    for($i=0;$i<count($typesReglements);$i++){
							//if(!empty($typesReglements[$i])){
							echo $typesReglements[$i].'<br><br>';
							//}
						}
					 }?>
							
							</td>
						<!--<td><?=$key['num_cheque']?></td>-->
					<td>
					 <?php foreach($DetailReglements as $cle){
										 $date_cheques=explode(',',$cle['date_cheque']);
										// echo '<b> Nb Date '.count($date_cheques).'</b><br>';
									for($i=0;$i<count($date_cheques);$i++){
										//if(!empty($date_cheques[$i])){
										echo $date_cheques[$i].'<br><br>';
										//}
									}
								  }?>
					</td>
					<td> <?php foreach($DetailReglements as $cle){$retenue_dates=explode(',',$cle['retenue_date']);
									for($i=0;$i<count($retenue_dates);$i++){
										if(!empty($retenue_dates[$i])){
										echo $retenue_dates[$i].'<hr>';
										}
									}
								  }?></td>
								   <td>
												<?php if($cle['pieceRs']){?>
												<a href="./pages/Reglements/Archive/PiecesRS_Archive/<?=$cle['pieceRs']?>">Télécharger </a>
												<?php } ?>
												
												
												</td>
												<td>
									 <a href="?Reglements&Archive&Add&Facture=<?=$key['num_fact_archive']?>" class="btn btn-primary active">Ajout </a>
									</td>
												<td><a href="?Reglements&Archive&Modifier&Facture=<?=$key['num_fact_archive']?>" class="btn btn-warning">Modifier</a></td>
												
												
												 
												  
											 </tr>
									<?php }}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
