
<!-- Modal Add Client -->
<?php
include 'class/client.class.php';
// Générer le code Client 
$anne=date('y');
$nb=count($clt->getAllClients());
	/*	if($clt->getDernierCodeClient()){
			
		   $codeClient='TPC_'.$anne.'/'.intval($nb+1);
		}
		else {$codeClient='TPC_'.$anne.'/1';}
		//echo '<h1> Code Client '.$codeClient.'</h1>';
		*/
	if($clt->getDernierCodeClient()){
	$codes=$clt->getDernierCodeClient()['code_client'];
	$code=explode('_',$codes);
	$data=explode('/',$code[1]);
	$codeClient='TPC_'.$anne.'/'.intval($data[1]+1);
	//echo '<h1> Code Client '.$codeClient.'</h1>';
	
	}
else {
	$codeClient='TPC_'.$anne.'/1';
}	
// Affichage
 $clients=$clt->getAllClients();
 // Suppression
 if(isset($_GET['deleteClient'])){
	if($clt->deleteClient($_GET['deleteClient']))
	{echo "<script>document.location.href='main.php?Gestion_Clients'</script>";}
 }
 /*** Detail Client ***/
 ?>

 <?php
 // Ajout
 if(isset($_REQUEST['btnSubmitAjout'])){

	 if($clt->Ajout(@$_POST['type'],@$_POST['convention'],@$_FILES['piececonvention']['name'],str_replace("'","\'",@$_POST['nom']),@$_POST['code'],str_replace("'","\'",@$_POST['adresse']),@$_POST['matriculeFiscale'],@$_POST['exonoration'],@$_FILES['pieceExonoration']['name'],@$_POST['tel'],@$_POST['email'],str_replace("'","\'",@$_POST['numexonoration']),@$_POST['ValiditeExonoration']))
	{
		if($_FILES['pieceExonoration']['name']!=''){
	@copy($_FILES['pieceExonoration']['tmp_name'],'pages/clients/pieceExonorationClients/'.$_FILES['pieceExonoration']['name']);
		}
		if($_FILES['piececonvention']['name']!=''){
	@copy($_FILES['piececonvention']['tmp_name'],'pages/clients/pieceConventionClients/'.$_FILES['piececonvention']['name']);
		}
		echo "<script>document.location.href='main.php?Gestion_Clients'</script>";}
else {echo "<script>alert('Erreur !!! ')</script>";}
 }
/*******   Modifier Client*************/

?>
<!--  Détail ****-->

  <!-- Modal Modifier Client -->
<div class="modal fade"  id="ModalUpdateClient" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" >
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modifier Client</h1>
        
      </div>
	  <div id="detail"></div>
		</div>
	</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var table = document.getElementById('dataTable');
  var search = document.getElementById('clientsSearch');
  var btn = document.getElementById('clientsFilterApply');
  function apply(){
    if(!table) return;
    var term = search ? (search.value || '').toLowerCase() : '';
    table.querySelectorAll('tbody tr').forEach(function(tr){
      var txt = (tr.innerText || '').toLowerCase();
      tr.style.display = term ? (txt.indexOf(term) !== -1 ? '' : 'none') : '';
    });
  }
  if (search) search.addEventListener('input', apply);
  if (btn) btn.addEventListener('click', apply);

  // Hide DataTables default search/length controls to avoid duplicate search bars
  var dtFilters = document.querySelectorAll('#dataTable_wrapper .dataTables_filter');
  dtFilters.forEach(function(el){ el.style.display = 'none'; });
  var dtLengths = document.querySelectorAll('#dataTable_wrapper .dataTables_length');
  dtLengths.forEach(function(el){ el.style.display = 'none'; });
});
</script>
<style>
  /* Hide DataTables auto-added search/length controls (we use the custom search above) */
  #dataTable_wrapper .dataTables_filter,
  #dataTable_wrapper .dataTables_length {
    display: none !important;
  }
</style>
<!--  Fin Modal Add Client-->

		
<!-- Modal Add Client -->
<div class="modal fade"  id="ModalAddClient" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" >
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Ajout Client</h1>
        
      </div>
	  
		  <form method="post" enctype="multipart/form-data"  >
		  <div class="modal-body">
			<div class="mb-3">
			<input type="hidden" name='idClient' readOnly id="ClientId"/>
				<label for="code_client" class="col-form-label">Code Client:</label>
				<input type="text" value="<?=$codeClient?>" readOnly class="form-control" id="code_client" name="code"/>
				   
			  </div>
			  <div class="mb-3">
				<label for="nom_client" class="col-form-label">Nom Client:</label>
				<input type="text"   class="form-control" id="nom_client" name="nom"/>
				   
			  </div>
			  <div class="mb-3">
				<label for="typeClient" class="col-form-label">Type Client:</label>
				<select class="form-control" id="typeClient" name="type">
				   <option value="passager">Passager</option>
				   <option value="conventionner">Conventionner</option>
				</select>
			  </div>
			  <div class="mb-3" id="ConventionClient">
				  <label  class="col-form-label col-3">Convention:</label>
					<div class="form-check form-check-inline col-3">
				  <input class="form-check-input" type="radio" name="convention"  id="oui" value="oui">
				  <label class="form-check-label" for="oui">Oui</label>
				</div>
				<div class="form-check form-check-inline col-3">
				  <input class="form-check-input" type="radio" name="convention" id="non" value="non">
				  <label class="form-check-label" for="non">non</label>
				</div>
				<div class="mb-3" id="PieceConvention">
				<label for="piececonvention " class="form-label">Dossier Convention </label>
			   <input type="file" class="form-control" id="piececonvention " name="piececonvention"/>
	  
			</div>
			   </div>
			<div class="mb-3">
				<label for="adresse" class="form-label">Adresse</label>
			   <textarea class="form-control" id="adresse" name="adresse" required></textarea>
	  
			</div>
			<div class="mb-3">
				<label for="matriculeFiscale " class="form-label">matricule Fiscale </label>
			   <input type="text" class="form-control" id="matriculeFiscale " name="matriculeFiscale"/>
	  
			</div>
			<div class="mb-3" id="ExonorationClient">
			  <label  class="col-form-label col-3">Exonoration:</label>
				<div class="form-check form-check-inline col-3">
			  <input class="form-check-input" type="radio" name="exonoration" id="ouiexonoration" value="oui">
			  <label class="form-check-label" for="ouiexonoration">Oui</label>
			</div>
			<div class="form-check form-check-inline col-3">
			  <input class="form-check-input" type="radio" name="exonoration" id="nonexonoration" value="non">
			  <label class="form-check-label" for="nonexonoration">non</label>
			</div>
			
			   </div>
			<div class="mb-3" id="PieceExonorationClient">
				<label for="pieceExonoration " class="form-label">Piece Exonoration </label>
			   <input type="file" class="form-control" id="pieceExonoration " name="pieceExonoration"/>
			   
				<label for="numexonoration" class="form-label">Num Exonoration</label>
			   <input type="text" class="form-control" id="numexonoration" name="numexonoration" />
	           <label for="ValiditeExonoration" class="form-label">Validité Exonoration</label>
			   <input type="date" class="form-control" id="ValiditeExonoration" name="ValiditeExonoration" />
			
	  
			</div>
			<div class="mb-3">
				<label for="tel" class="form-label">Téléphone </label>
			   <input type="text" class="form-control" id="tel" name="tel"/>
	  
			</div>
			<div class="mb-3">
				<label for="email" class="form-label">Email </label>
			   <input type="email" class="form-control" id="email" name="email"/>
	  
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
		  </div>
		  </form>
		
	
	
    </div>
  </div>
</div>
<!--  Fin Modal Add Client-->

 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
					<div class="btn-group" role="group" aria-label="Basic mixed styles example">
	<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#ModalAddClient">Ajouter Client</button>
  
</div>
                      
                        
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-5">
                                    <label for="clientsSearch" class="form-label">Recherche rapide</label>
                                    <input type="search" class="form-control" id="clientsSearch" placeholder="Rechercher un client">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-secondary w-100" id="clientsFilterApply">Filtrer</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="90%" cellspacing="0" data-order-column="0" data-order-direction="asc">
                                    <thead>
                                        <tr>
											<th >Code</th>
											<th >Nom</th>
											<th >Matricule Fiscale</th>
                                            <th >Type Client</th>
                                            <th >Conventionné</th>
											 <th >Piece Convention</th>
											<th >Exonoré</th>
											<th >Num Exonoration</th>
											<th >Validité Exonoration</th>
                                            <th >Piece Exonoration</th>
                                            <th >N° Téléphone</th>
                                            <th >Email</th>
											<th >Supp</th>
											<th >Mod</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($clients)){
										 foreach($clients as $key){?>
                                        <tr>
                                            <td><a href="?Gestion_Clients&Offreclient=<?=$key['id']?>"><?=$key['code_client']?></a></td>
											 <td><?=$key['nom_client']?></td>
                                            <td><?=$key['matriculeFiscale']?></td>
                                            <td><?=$key['type_client']?></td>
                                            <td><?=$key['convention']?></td>
											<td><a href="pages/clients/pieceConventionClients/<?=$key['pieceConvention']?>"><?=$key['pieceConvention']?></a></td>
                                            <td><?=$key['exonoration']?></td>
											  <td><?=$key['numexonoration']?></td>
											  <td><?=$key['ValiditeExonoration']?></td>
                                            <td><a href="pages/clients/pieceExonorationClients/<?=$key['pieceExonoration']?>">
											<?=$key['pieceExonoration']?></a></td>
											 <td><?=$key['tel']?></td>
											  <td><?=$key['email']?></td>
											   <td><a href="?Gestion_Clients&deleteClient=<?=$key['id']?>" class="btn btn-danger">Supp</td>
											   <td class="updateClient"><a id="<?=$key['id']?>" data-bs-toggle="modal" data-bs-target="#ModalUpdateClient" href="#" class="btn btn-warning">Mod</td>
                                        </tr>
									<?php } } ?>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
