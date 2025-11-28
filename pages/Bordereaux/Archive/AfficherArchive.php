<?php
include 'class/Bordereaux.class.php';

$ArchiveBordereaux=$bordereau->getAllARchive();
	
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Bordereaux&Archive&Add" class="btn btn-primary active " style="position:relative; top:20px;"  >Ajout Archive Bordereaux</a>
							</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered dt-extra-controls" id="dataTable" width="100%" cellspacing="0" data-year-column="1" data-order-column="2" data-order-direction="asc">
                                    <thead>
                                        <tr> 
										    <th >Numéro Bordereau</th>
											<th >Num Facture</th>
											<th >Date Bordereau</th>
											<th >Télécharger</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($ArchiveBordereaux)){
										foreach($ArchiveBordereaux as $key){?>
											 <tr>
												<td>
												<?=$key['num_bordereaux']?>
												</td>
												 <td><?=$key['num_fact']?></td>
												<td><?=$key['date']?></td>
												
												
												 
												   <td>
												   
												   <a href="./pages/Bordereaux/Archive/bordereauxArchive_piecesJointe/<?=$key['pieces_jointe']?>">Imprimer</a></td>
											 </tr>
									<?php }}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
