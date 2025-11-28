<?php
// pages/Bordereaux/ModeleBordereaux.php
include_once '../../class/Bordereaux.class.php';
include_once '../../class/Factures.class.php';

// Load data for this bordereau
$infosBordereaux = $bordereau->detailBordereau($_GET['bordereau']);
foreach ($infosBordereaux as $infosBordereau) { break; } // get first/basic row

// Optional: other data you already pull
$adressesClient = $facture->get_All_AdressesClient_ProjetsFacture($_GET['bordereau']);
$nbAdresse      = count($infosBordereaux);

// ---- Date formatting to French long form (same as before) ----
$dates = explode('-', $infosBordereau['date'] ?? '');
$mois  = [
  '01'=>'janvier','02'=>'février','03'=>'mars','04'=>'avril','05'=>'mai','06'=>'juin',
  '07'=>'juillet','08'=>'août','09'=>'septembre','10'=>'octobre','11'=>'novembre','12'=>'décembre'
];
if (count($dates) === 3) {
  $date = $dates[2] . ' ' . ($mois[$dates[1]] ?? '') . ' ' . $dates[0];
} else {
  $date = $infosBordereau['date'] ?? '';
}

// Keep your helper if needed elsewhere
function asLetters($n,$sep='.') { return (string)$n; } // stub; real function not used in the view

// ---- Include the print template (view) ----
include_once 'ModeleBordereau.php';
