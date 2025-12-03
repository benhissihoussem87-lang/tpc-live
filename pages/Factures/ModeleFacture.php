<?php
include_once '../../class/Factures.class.php';

$infosFacture   = $facture->detailFacture($_GET['facture']);
$bonCommandeFacture = $facture->GetBonCommandeByFacture($_GET['facture']);
$ProjetsFacture = $facture->get_AllProjets_ByFacture($_GET['facture']);

// Adresses group
$adressesClient = $facture->get_All_AdressesClient_ProjetsFacture($_GET['facture']);
$nbAdresse      = count($adressesClient);

// Detect type (forfaitaire / non) robustly across all rows
$verifForfitaireFacture   = null;
$verifMultiAdresseFacture = 'non';
if (!empty($ProjetsFacture)) {
  $hasUnit = false; $hasForfait = false; $hasAdresse = false;
  foreach ($ProjetsFacture as $projet) {
    if (!empty($projet['prix_unit_htv']))   { $hasUnit = true; }
    if (!empty($projet['prixForfitaire']))  { $hasForfait = true; }
    if (!empty($projet['adresseClient']))   { $hasAdresse = true; }
  }
  // Prefer Nonforfitaire when any unit price exists; else Forfitaire when only forfait values found
  if ($hasUnit)        { $verifForfitaireFacture = 'Nonforfitaire'; }
  elseif ($hasForfait) { $verifForfitaireFacture = 'forfitaire'; }
  else                 { $verifForfitaireFacture = 'Nonforfitaire'; } // safe default
  $verifMultiAdresseFacture = $hasAdresse ? 'oui' : 'non';
}

// Date humanisée
$dates = explode('-', $infosFacture['date']);
$mois  = null;
switch (@$dates[1]) {
  case '01': $mois="janvier";   break;  case '02': $mois="février";   break;
  case '03': $mois="mars";      break;  case '04': $mois="avril";     break;
  case '05': $mois="mai";       break;  case '06': $mois="juin";      break;
  case '07': $mois="juillet";   break;  case '08': $mois="août";      break;
  case '09': $mois="septembre"; break;  case '10': $mois="octobre";   break;
  case '11': $mois="novembre";  break;  case '12': $mois="décembre";  break;
}
@$date = $dates[2].' '.$mois.' '.$dates[0];

/* -------------------- KEY FIX: normalize the exonoration flag -------------------- */
$exonRaw   = isset($infosFacture['client_exonoration'])
  ? (string)$infosFacture['client_exonoration']
  : (isset($infosFacture['exonoration']) ? (string)$infosFacture['exonoration'] : '');
$isExonore = (strcasecmp(trim($exonRaw), 'oui') === 0);
/* ------------------------------------------------------------------------------- */

// Helper for amount in letters (unchanged from your file)
function asLetters($number,$separateur=".") {
  $convert = explode($separateur, $number);
  $num[17] = array('zero','un','deux','trois','quatre','cinq','six','sept','huit','neuf','dix','onze','douze','treize','quatorze','quinze','seize');
  $num[100]= array(20=>'vingt',30=>'trente',40=>'quarante',50=>'cinquante',60=>'soixante',70=>'soixante-dix',80=>'quatre-vingt',90=>'quatre-vingt-dix');

  if (isset($convert[1]) && $convert[1] != '') {
    if ($convert[1] != '0') return asLetters($convert[0]).' dinars et '.asLetters($convert[1]).' millimes';
    else                    return asLetters($convert[0]).' dinars '.asLetters($convert[1]);
  }
  if ($number < 0)  return 'moins '.asLetters(-$number);
  if ($number < 17) return @$num[17][$number];
  elseif ($number < 20) {
    return 'dix-'.asLetters($number-10);
  } elseif ($number < 100) {
    if ($number%10 == 0) return @$num[100][$number];
    elseif (substr($number,-1) == 1) {
      if(((int)($number/10)*10)<70) return asLetters((int)($number/10)*10).'-et-un';
      elseif ($number==71) return 'soixante-et-onze';
      elseif ($number==81) return 'quatre-vingt-un';
      elseif ($number==91) return 'quatre-vingt-onze';
    } elseif ($number < 70) {
      return asLetters($number-$number%10).'-'.asLetters($number%10);
    } elseif ($number < 80) {
      return asLetters(60).'-'.asLetters($number%20);
    } else {
      return asLetters(80).'-'.asLetters($number%20);
    }
  } elseif ($number == 100) {
    return 'cent';
  } elseif ($number < 200) {
    return asLetters(100).' '.asLetters($number%100);
  } elseif ($number < 1000) {
    return asLetters((int)($number/100)).' '.asLetters(100).($number%100>0?' '.asLetters($number%100):'');
  } elseif ($number == 1000) {
    return 'mille';
  } elseif ($number < 2000) {
    return asLetters(1000).' '.asLetters($number%1000).' ';
  } elseif ($number < 1000000) {
    return asLetters((int)($number/1000)).' '.asLetters(1000).($number%1000>0?' '.asLetters($number%1000):'');
  } elseif ($number == 1000000) {
    return 'millions';
  } elseif ($number < 2000000) {
    return asLetters(1000000).' '.asLetters($number%1000000);
  } elseif ($number < 1000000000) {
    return asLetters((int)($number/1000000)).' '.asLetters(1000000).($number%1000000>0?' '.asLetters($number%1000000):'');
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
<script>
// Always-on writing mode: make the invoice content editable for on-page tweaks.
document.addEventListener('DOMContentLoaded', function(){
  var root = document.querySelector('.container');
  if (root) {
    root.setAttribute('contenteditable', 'true');
  }
});
</script>
<style>
@media print { body { margin: 1cm !important; } .container { width: 100% !important; } }
body{padding:0;margin:0}
.invoice-title h2, .invoice-title h3 { display:inline-block; }
.table > tbody > tr > .no-line { border-top: none; }
.table > thead > tr > .no-line { border-bottom: none; }
.table > tbody > tr > .thick-line { border-top: 2px solid; }
ol,ul{list-style:none;padding:0}
ul li{text-transform:uppercase;text-align:right;font-size:12px;margin-bottom:5px}
/* No toolbar; content is always editable. Avoid outlines for cleaner look. */
</style>

<?php
// Route to the right template
if ($verifForfitaireFacture=='Nonforfitaire') {
  // $isExonore is already defined above & available to the template
  include_once 'ModeleFactureNonForfitaire.php';
} else if ($verifForfitaireFacture=='forfitaire') {
  include_once 'ModeleFactureForfitaire.php';
} else {
  // Fallback to non-forfaitaire to avoid blank page when detection is inconclusive
  include_once 'ModeleFactureNonForfitaire.php';
}
?>
