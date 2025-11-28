<?php
include_once '../../class/connexion.db.php';
include_once '../../class/OffresPrix.class.php';
include_once '../../class/Factures.class.php';
$infosOffre=$offre->detailOffreByNumOffre($_GET['offre']);
$offreId = isset($infosOffre['id_offre']) ? $infosOffre['id_offre'] : null;
$ProjetsOffre=$offre->get_AllProjets_ByOffre($_GET['offre'], $offreId);
// Use the same loader (falls back to facture_projets) for print
$Projets=$ProjetsOffre;
$adressesClient=$offre->get_All_AdressesClient_ProjetsByOffre($_GET['offre']);

/**** Adresses Offres *********/
if(empty($adressesClient)){
	$adressesClient=$offre->get_All_AdressesClient_ProjetsByOffre($_GET['offre']);
}
$nbAdresse=count($adressesClient);

// Final print-specific fallback: if still no lines, read from facture_projets directly
if (empty($ProjetsOffre)) {
  $pdo = connexion();
  $st = $pdo->prepare("
    SELECT fp.*, p.classement, p.id AS projet_id
    FROM facture_projets AS fp
    LEFT JOIN projet AS p ON fp.projet = p.id
    WHERE fp.facture = :num
  ");
  $st->execute([':num' => $_GET['offre']]);
  $ProjetsOffre = $st->fetchAll(PDO::FETCH_ASSOC);
  $Projets = $ProjetsOffre;
  if (empty($adressesClient)) {
    $st2 = $pdo->prepare("SELECT DISTINCT(adresseClient) AS adresseClient FROM facture_projets WHERE facture = :num");
    $st2->execute([':num' => $_GET['offre']]);
    $adressesClient = $st2->fetchAll(PDO::FETCH_ASSOC);
  }
}
// Verification si l'offre est forfitaire ou non 
$verifForfitaireOffre=null;
if(!empty($ProjetsOffre)){foreach($ProjetsOffre as $projet){
	if($projet['prixForfitaire']!=''){$verifForfitaireOffre='forfitaire';}
	else if($projet['prix_unit_htv']!=''){$verifForfitaireOffre='Nonforfitaire';}
	
}}
/********** Adresse Client *****/

//echo '<h1>NB Adresse '.$nbAdresse.'</h1>';
if(!empty($Projets)){foreach($Projets as $p){
	if($p['adresseClient']!=''){$verifMultiAdresseFacture='oui';}
	else {$verifMultiAdresseFacture='non';}
}}
// No else; Projets already set to loader result

//echo '<h1 style="text-align:center"> Etat Facture '.$verifMultiAdresseFacture.'</h1>';
@$dates=explode('-',$infosOffre['date']);
$mois=null;
switch (@$dates[1]) {
  case '01':
    $mois="janvier";
    break;
  case '02':
    $mois="février";
    break;
  case '03':
    $mois="mars";
    break;
  case '04':
    $mois="avril";
    break;
  case '05':
    $mois="mai";
    break;
  case '06':
    $mois="juin";
    break;
  case '07':
    $mois="juillet";
    break;
  case '08':
    $mois="août";
    break;
  case '09':
    $mois="septembre";
    break;
  case '10':
    $mois="octobre";
    break;
  case '11':
    $mois="novembre";
    break;
  case '12':
    $mois="décembre";
    break;
}
@$date =$dates['2'].' '.$mois.' '.$dates['0'];

function asLetters($number,$separateur=".") {
    $convert = explode($separateur, $number);
    $num[17] = array('zero', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit',
                     'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize');
                      
    $num[100] = array(20 => 'vingt', 30 => 'trente', 40 => 'quarante', 50 => 'cinquante',
                      60 => 'soixante', 70 => 'soixante-dix', 80 => 'quatre-vingt', 90 => 'quatre-vingt-dix');
                                      
    if (isset($convert[1]) && $convert[1] != '') {
		if($convert[1]!='0'){
      return asLetters($convert[0]).' dinars et '.asLetters($convert[1]).' millimes';
		}
		else {
			return asLetters($convert[0]).' dinars '.asLetters($convert[1]);
		}
    }
    if ($number < 0) return 'moins '.asLetters(-$number);
    if ($number < 17) {
      return @$num[17][$number];
    }
    elseif ($number < 20) {
      return 'dix-'.asLetters($number-10);
    }
    elseif ($number < 100) {
      if ($number%10 == 0) {
        return @$num[100][$number];
      }
      elseif (substr($number, -1) == 1) {
        if( ((int)($number/10)*10)<70 ){
          return asLetters((int)($number/10)*10).'-et-un';
        }
        elseif ($number == 71) {
          return 'soixante-et-onze';
        }
        elseif ($number == 81) {
          return 'quatre-vingt-un';
        }
        elseif ($number == 91) {
          return 'quatre-vingt-onze';
        }
      }
      elseif ($number < 70) {
        return asLetters($number-$number%10).'-'.asLetters($number%10);
      }
      elseif ($number < 80) {
        return asLetters(60).'-'.asLetters($number%20);
      }
      else {
        return asLetters(80).'-'.asLetters($number%20);
      }
    }
    elseif ($number == 100) {
      return 'cent';
    }
    elseif ($number < 200) {
      return asLetters(100).' '.asLetters($number%100);
    }
    elseif ($number < 1000) {
      return asLetters((int)($number/100)).' '.asLetters(100).($number%100 > 0 ? ' '.asLetters($number%100): '');
    }
    elseif ($number == 1000){
      return 'mille';
    }
    elseif ($number < 2000) {
      return asLetters(1000).' '.asLetters($number%1000).' ';
    }
    elseif ($number < 1000000) {
      return asLetters((int)($number/1000)).' '.asLetters(1000).($number%1000 > 0 ? ' '.asLetters($number%1000): '');
    }
    elseif ($number == 1000000) {
      return 'millions';
    }
    elseif ($number < 2000000) {
      return asLetters(1000000).' '.asLetters($number%1000000);
    }
    elseif ($number < 1000000000) {
      return asLetters((int)($number/1000000)).' '.asLetters(1000000).($number%1000000 > 0 ? ' '.asLetters($number%1000000): '');
    }
  }

?>
<link href="assetsModelFacture/css/bootstrap.css" rel="stylesheet" id="bootstrap-css">
<script src="assetsModelFacture/js/bootstrap.js"></script>
<script src="assetsModelFacture/js/jquery.js"></script>
<script>
$(function(){
	$("#imprimer").click(function(){
		$(this).html('')
		window.print()
	})
})

</script>
<!------ Include the above in your HEAD tag ---------->
<style>
body{padding:0;margin:0}
.invoice-title h2, .invoice-title h3 {
    display: inline-block;
}

.table > tbody > tr > .no-line {
    border-top: none;
}

.table > thead > tr > .no-line {
    border-bottom: none;
}

.table > tbody > tr > .thick-line {
    border-top: 2px solid;
}
ol,ul{list-style:none;padding:0}
ul li{text-transform:uppercase;text-align:right;font-size:12px;margin-bottom:5px}
</style>
<?php 
//echo '<h1 style="padding-top:150px"> Offre : '.@$verifForfitaireOffre.'<br>Multi Adresse '.@$verifMultiAdresseFacture.'</h1>';
    				// si le facture est Non forfitaire 
		    if($verifForfitaireOffre=='forfitaire'){
				include_once 'ModeleOffrePrixForfitaire.php';
			} 
			else if($verifForfitaireOffre!='forfitaire'){
			 include_once 'ModeleOffrePrix.php'; 
			}
			?>			
<!--<span id="imprimer" style="float:right"><img src="../../assets/images/imprimante.png" width=75 height=75/></span>-->
