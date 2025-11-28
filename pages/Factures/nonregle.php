<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
<?php
// Robust includes: work whether loaded directly or via main.php
$__ROOT = realpath(__DIR__ . '/../../');
if ($__ROOT === false) { $__ROOT = __DIR__ . '/../../'; }
include_once  $__ROOT . '/class/client.class.php';
include_once  $__ROOT . '/class/Reglements.class.php';
include_once  $__ROOT . '/class/Projet.class.php';
include_once  $__ROOT . '/class/Factures.class.php';
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$factures=$facture->AfficherFacturesNonRegle();
// Générer le numOffre
$anne=date('Y');
		if($factures){
			$nb=count($factures);	
		$numFacture=intval($nb+1).'/'.$anne;
		}
		else {$numFacture='1/'.$anne;}
/*********** delete Facture ***************/
 if(isset($_GET['deleteFacture'])){
	 if($facture->deleteFacture($_GET['deleteFacture'])){
		 echo "<script>document.location.href='main.php?Factures'</script>";
	 }
 }		
 ?>
<!-- DataTales Example -->
                   
                                <table  border=2 style="border:black 1px solid" width="100%" height="100%" cellspacing="0">
                                    <thead>
                                        <tr>
											<th >Num Facture</th>
											<th >Date Facture</th>
											<th >Client</th>
                                            <th style="width:15%">Prix TTC</th>
                                            <th width=40%>Observation</th>
											
                                           
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($factures)){
										foreach($factures as $key){
										$reglementFacture=$facture->GetReglementByFacture($key['num_fact']);
										$bonCommandeFacture=$facture->GetBonCommandeByFacture($key['num_fact']);
						/**** Verifier si la facture est forfitaire ou non Forfitaire **********/
				@$verifTypeFacture=$facture->getTypeFacture($key['num_fact']);
                 $typeFacture=null;
                foreach($verifTypeFacture as $f){
					//echo '<h1> Type '.getType($f).'</h1>';
					if(!empty($f['prixForfitaire'])){ $typeFacture='forfitaire'; break;}
					else {$typeFacture='non forfitaire';  break;}
				}				
								
											
											?>
											 <tr>
												<td>
												<b><?=$key['num_fact']?></b>
												</td>
												 <td><?=$key['date']?></td>
												<td><b><?=$key['nom_client']?></b><br>
												Adresse : <?=$key['adresse']?>
												</td>
												<?php
                                                // Compute Prix TTC: prefer reglement.prix_ttc, fallback to sum of facture_projets
                                                $prixTTCVal = '';
                                                if (!empty($reglementFacture) && isset($reglementFacture['prix_ttc']) && $reglementFacture['prix_ttc'] !== '') {
                                                    $prixTTCVal = $reglementFacture['prix_ttc'];
                                                } else {
                                                    $lines = $facture->get_AllProjets_ByFacture($key['num_fact']);
                                                    $isExo = !empty($key['numexonoration']);
                                                    $sumTTC = 0.0;
                                                    if (!empty($lines)) {
                                                        foreach ($lines as $ln) {
                                                            $pttc = $ln['prixTTC'] ?? '';
                                                            $pf   = $ln['prixForfitaire'] ?? '';
                                                            $q    = $ln['qte'] ?? '';
                                                            $pu   = $ln['prix_unit_htv'] ?? '';
                                                            if ($pttc !== '' && is_numeric($pttc)) {
                                                                $sumTTC += (float)$pttc;
                                                            } elseif ($pf !== '' && is_numeric($pf)) {
                                                                $sumTTC += $isExo ? (float)$pf : round((float)$pf * 1.19, 3);
                                                            } elseif ($q !== '' && is_numeric($q) && $pu !== '' && is_numeric($pu)) {
                                                                $ht = (float)$q * (float)$pu;
                                                                $sumTTC += $isExo ? $ht : round($ht * 1.19, 3);
                                                            }
                                                        }
                                                    }
                                                    if ($sumTTC > 0) {
                                                        $prixTTCVal = number_format($sumTTC, 3, '.', '');
                                                    }
                                                }
                                                ?>
												<td style="text-align:right;"><?=$prixTTCVal !== '' ? $prixTTCVal : '-'?></td>
												
												<td></td>
											
												
												 
												
											 </tr>
									<?php }}?>
									 </tbody>
                                </table>
                            
