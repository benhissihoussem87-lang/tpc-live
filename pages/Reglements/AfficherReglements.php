<?php
include 'class/Reglements.class.php';


$Reglements=$reglement->getAll();
	
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered dt-extra-controls" id="dataTable" width="100%" cellspacing="0" data-year-column="5" data-order-column="5" data-order-direction="asc">
                                    <thead>
                                        <tr> 
										<th>Facture</th>
										   
											<th >Client</th>
											<th width="10%" >Prix TTC</th>
											<th >Reglement</th>
											<th >Type Reglement</th>
											
											<th width="15%">Date Cheque</th>
											<th >Date Retenue</th>
											<th >Piece Retenue</th>
											<th>Ajouter</th>
											<th >Modifier</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									 <?php if(!empty($Reglements)){
										foreach($Reglements as $key){
						$DetailReglements=$reglement->getAllReglement($key['num_fact']);
						$searchText = strtolower(trim(
							($key['num_fact'] ?? '').' '.
							($key['nom_client'] ?? '').' '.
							implode(' ', array_column($DetailReglements, 'etat_reglement'))
						));
						// Build year tags from facture num + cheque/retention dates
						$yearTags = [];
						if (preg_match_all('/\\b(20\\d{2})\\b/', (string)$key['num_fact'], $m)) {
							$yearTags = array_merge($yearTags, $m[1]);
						}
						foreach ($DetailReglements as $cle) {
							$datesAll = [];
							if (!empty($cle['date_cheque'])) {
								$datesAll = array_merge($datesAll, explode(',', $cle['date_cheque']));
							}
							if (!empty($cle['retenue_date'])) {
								$datesAll = array_merge($datesAll, explode(',', $cle['retenue_date']));
							}
							foreach ($datesAll as $d) {
								if (preg_match('/\\b(20\\d{2})\\b/', $d, $m)) {
									$yearTags[] = $m[1];
								}
							}
						}
						$yearTags = array_unique($yearTags);
						$yearValuesAttr = htmlspecialchars(implode(' ', $yearTags));
						?>
									 <tr data-search-text="<?= htmlspecialchars($searchText) ?>"<?php if (!empty($yearValuesAttr)) { ?> data-year-values="<?= $yearValuesAttr ?>"<?php } ?>>
									<td><?=$key['num_fact']?></td>
									<td><?=$key['nom_client']?></td>
									 <td align="right">
									
									 <?php foreach($DetailReglements as $cle){?>
									<?=$cle['prix_ttc']?>
									 <?php }?>
									 
									 </td>
									 <td>
									<?php foreach($DetailReglements as $cle){?>
									 <?=$cle['etat_reglement']?>
									 <?php }?>
									 
									 </td>
									 
									 <td>
									 
									 <?php foreach($DetailReglements as $cle){$typesReglements=explode(',',$cle['TypeReglement']);
					    for($i=0;$i<count($typesReglements);$i++){
							//if(!empty($typesReglements[$i])){
							echo $typesReglements[$i].'<br><br>';
							//}
						}
					 }?>
									
									 </td>
									 
									<!-- <td>
									<?php /*foreach($DetailReglements as $cle){
									$num_cheques=explode(',',$cle['num_cheque']);
									for($i=0;$i<count($num_cheques);$i++){
										if(!empty($num_cheques[$i])){
										echo $num_cheques[$i].'<hr>';
										//echo $num_cheques[$i].'<hr>';
										}
									}
								  }*/?>
									 </td>-->
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
									  <td>
									  <?php foreach($DetailReglements as $cle){$retenue_dates=explode(',',$cle['retenue_date']);
									for($i=0;$i<count($retenue_dates);$i++){
										if(!empty($retenue_dates[$i])){
										echo $retenue_dates[$i].'<hr>';
										}
									}
								  }?>
									
									 </td>
									 <td>
												<?php if($cle['pieceRs']){?>
												<a href="./pages/Reglements/PiecesRS/<?=$cle['pieceRs']?>">Télécharger </a>
												<?php } ?>
												
												
												</td>
									<td>
									 <a href="?Reglements&Add&Facture=<?=$key['num_fact']?>" class="btn btn-primary active">Ajout </a>
									</td>
									 <td>
									  <a href="?Reglements&Modifier&Facture=<?=$key['num_fact']?>" class="btn btn-warning">Modifier</a>
									 
									 </td>
									  </tr>
									 <?php }}?>
									</tbody>


								   </table>
                            </div>
                        </div>
                    </div>
