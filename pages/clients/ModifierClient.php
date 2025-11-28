<?php
include '../../class/client.class.php';
$id=$_GET['id_client'];
$client=$clt->getclient($id);
  

?>
<script src="../../assets/main/vendor/jquery/jquery.min.js"></script>
<script >
$(function(){
	var type=$("#typeClient").val()
	console.log('Type ::: '+type)
	if(type=='passager'){$("#ConventionClientUpdate").hide();}
	else if(type=='conventionner'){$("#ConventionClientUpdate").show();}
	var exonoration=$("#defaultExonoration").val()
	  if(exonoration=='oui'){$("#PieceExonorationClientUpdate").show();}
		else if(exonoration=='non'){$("#PieceExonorationClientUpdate").hide();}
	//Type Client
	$("#typeClient").change(function(){
		type=$(this).val()
		console.log('Type Change::: '+type)
		if(type=='conventionner'){$("#ConventionClientUpdate").show();}
		else {$("#ConventionClientUpdate").hide();}
	})
	//Exonoration
	$("#ExonorationClientUpdate input[type=radio]").click(function(){
		 exonoration=$(this).val()
		if(exonoration=='oui'){$("#PieceExonorationClientUpdate").show();}
		else if(exonoration=='non'){$("#PieceExonorationClientUpdate").hide();}
	})
})
</script>

<!-- Form Modifier Client -->

		<form method="post" enctype="multipart/form-data" id="formClient" action="pages/clients/updateClient.php" >
		<input type="hidden" readOnly id="defaultExonoration"  value="<?=$client['exonoration']?>"/>
		<div class="modal-body">
			<div class="mb-3">
			<input type="hidden" name='idClient' readOnly value="<?=$client['id']?>"   />
				<label for="code_client" class="col-form-label">Code Client:</label>
				<input type="text" value="<?=$client['code_client']?>" readOnly class="form-control" id="code_client" name="code"/>
				   
			  </div>
			  <div class="mb-3">
				<label for="nom_client" class="col-form-label">Nom Client:</label>
				<input type="text" value="<?=$client['nom_client']?>"  class="form-control" id="nom_client" name="nom"/>
				   
			  </div>
			  <div class="mb-3">
				<label for="typeClient" class="col-form-label">Type Client:</label>
				<select class="form-control" id="typeClient" name="type">
				   <option value="passager" <?php if($client['type_client']=='passager'){echo 'selected';}?>>Passager</option>
				   <option value="conventionner" <?php if($client['type_client']=='conventionner'){echo 'selected';}?>>Conventionner</option>
				</select>
			  </div>
			  <div class="mb-3" id="ConventionClientUpdate">
				  <label  class="col-form-label col-3">Convention:</label>
					<div class="form-check form-check-inline col-3">
				  <input class="form-check-input" type="radio" name="convention" id="oui" value="oui" <?php if($client['convention']=='oui'){echo 'checked';}?>>
				  <label class="form-check-label" for="oui">Oui</label>
				</div>
				<div class="form-check form-check-inline col-3">
				  <input class="form-check-input" type="radio" <?php if($client['convention']=='non'){echo 'checked';}?> name="convention" id="non" value="non">
				  <label class="form-check-label" for="non">non</label>
				</div>
					<div class="mb-3" id="PieceConvention">
				<label for="piececonvention" class="form-label">Dossier Convention </label>
			   <input type="file" class="form-control" id="piececonvention" name="piececonvention"/>
	  
			</div>
			   </div>
			<div class="mb-3">
				<label for="adresse" class="form-label">Adresse</label>
<textarea class="form-control" id="adresse" name="adresse" required><?=$client['adresse']?></textarea>
	  
			</div>
			<div class="mb-3">
				<label for="matriculeFiscale " class="form-label">matricule Fiscale </label>
			   <input type="text" class="form-control" id="matriculeFiscale " name="matriculeFiscale" value="<?=$client['matriculeFiscale']?>"/>
	  
			</div>
			<div class="mb-3" id="ExonorationClientUpdate">
			  <label  class="col-form-label col-3">Exonoration:</label>
				<div class="form-check form-check-inline col-3">
			  <input class="form-check-input" type="radio" name="exonoration" id="ouiexonoration" value="oui" <?php if($client['exonoration']=='oui'){echo 'checked';}?>>
			  <label class="form-check-label" for="ouiexonoration">Oui</label>
			</div>
			<div class="mb-3">
			<label for="numexonoration" class="form-label">Num Exonoration</label>
			   <input type="text" class="form-control" id="numexonoration" name="numexonoration" value="<?=$client['numexonoration']?>" />
			  </div>
			 <div class="mb-3">
	           <label for="ValiditeExonoration" class="form-label">Validité Exonoration</label>
			   <input type="date" class="form-control" id="ValiditeExonoration" name="ValiditeExonoration" value="<?=$client['ValiditeExonoration']?>" />
			 </div>
			<div class="form-check form-check-inline col-3">
			  <input class="form-check-input" type="radio" name="exonoration" id="nonexonoration" value="non" <?php if($client['exonoration']=='non'){echo 'checked';}?>>
			  <label class="form-check-label" for="nonexonoration">non</label>
			</div>
			   </div>
			<div class="mb-3" id="PieceExonorationClientUpdate">
				<label for="pieceExonoration " class="form-label">Piece Exonoration </label>
			   <input type="file" class="form-control" id="pieceExonoration " name="pieceExonoration"/>
	  
			</div>
			<div class="mb-3">
				<label for="tel" class="form-label">Téléphone </label>
			   <input type="text" class="form-control" id="tel" name="tel" value="<?=$client['tel']?>"/>
	  
			</div>
			<div class="mb-3">
				<label for="email" class="form-label">Email </label>
			   <input type="email" class="form-control" id="email" name="email" value="<?=$client['email']?>"/>
	  
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitModifier" >Modifier</button>
		  </div>
		  </form>
 
<!--  Fin Form Update Client-->