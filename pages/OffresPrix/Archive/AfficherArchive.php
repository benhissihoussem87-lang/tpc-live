<?php
include 'class/OffresPrix.class.php';

$OffresArchive=$offre->getOffres_PrixArchive();
	
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center;margin-bottom:20px" class="col-12">
                            <a href="?Offres_Prix&Archive&Add" class="btn btn-primary active " style="position:relative; top:20px;">Ajouter Offre de Prix a l'archive </a>
							</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered dt-extra-controls" id="dataTable" width="100%" cellspacing="0" data-year-column="1" data-order-column="1" data-order-direction="asc">
                                    <thead>
                                        <tr>
											<th >Num Offre</th>
											<th >Date Offre</th>
											<th >N° Facture</th>
											<th >Client</th>
											<th >Offres</th>
											
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($OffresArchive)){
										foreach($OffresArchive as $key){?>
											 <tr>
											  <td><?=$key['num_offre']?></td>
											  <td><?=$key['date']?></td>
												<td>
												<?=$key['num_fact']?>
												</td>
												 
												<td><?=$key['nom_client']?></td>
												
												
												 
												   <td>
												   <?php 
												   if($key['joindreOffre']!='Offre téléphonique'){
												   $offres=explode(',',$key['joindreOffre']);
												   
												   for($i=0;$i<count($offres);$i++){?>
												   <a href="pages/OffresPrix/Archive/Document_OffresArchive/<?=$offres[$i]?>"><?=$offres[$i]?></a><br>
												   <?php } } else { echo $key['joindreOffre'];} ?>
												   </td>
											 </tr>
									<?php }}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
