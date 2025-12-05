<?php
// ------- Safe defaults -------
$infosFacture        = $infosFacture        ?? [];
$ProjetsFacture      = $ProjetsFacture      ?? [];
$bonCommandeFacture  = $bonCommandeFacture  ?? null;
$adressesClient      = $adressesClient      ?? [];
$nbAdresse           = $nbAdresse           ?? 0;
$date                = $date                ?? '';
$isExonore           = $isExonore           ?? false;

$bonCommandeValue = '';
$bonCommandeValue = trim((string)($infosFacture['numboncommande'] ?? ''));
$numFactValue = trim((string)($infosFacture['num_fact'] ?? ''));
if ($bonCommandeValue !== '' && $numFactValue !== '' && strcasecmp($bonCommandeValue, $numFactValue) === 0) {
  $bonCommandeValue = '';
}

// Nombre => lettres (FR) + Dinars/Millimes
if (!function_exists('tnd_amount_in_words')) {
  function _fr_under_20(int $n): string {
    $u = ['zéro','un','deux','trois','quatre','cinq','six','sept','huit','neuf','dix','onze','douze','treize','quatorze','quinze','seize','dix-sept','dix-huit','dix-neuf'];
    return $u[$n] ?? '';
  }
  function _fr_under_100(int $n): string {
    if ($n < 20) return _fr_under_20($n);
    $tens = [20=>'vingt',30=>'trente',40=>'quarante',50=>'cinquante',60=>'soixante'];
    if ($n < 70) {
      $d = intdiv($n,10)*10; $r = $n % 10; $w = $tens[$d];
      if ($r === 1) return $w.' et un';
      return $r ? $w.'-'. _fr_under_20($r) : $w;
    }
    if ($n < 80) { // 70..79
      if ($n === 71) return 'soixante et onze';
      return 'soixante-'. _fr_under_20($n-60);
    }
    // 80..99
    if ($n === 80) return 'quatre-vingt';
    $r = $n - 80;
    if ($r === 1) return 'quatre-vingt-un';
    return 'quatre-vingt-'. _fr_under_20($r);
  }
  function _fr_under_1000(int $n): string {
    if ($n < 100) return _fr_under_100($n);
    $h = intdiv($n,100); $r = $n % 100;
    if ($h === 1) $w = 'cent'; else $w = _fr_under_20($h).' cent';
    return $r ? $w.' '. _fr_under_100($r) : $w;
  }
  function _fr_number_words(int $n): string {
    if ($n === 0) return 'zéro';
    $parts = [];
    $millions = intdiv($n, 1000000);
    if ($millions) { $parts[] = _fr_under_1000($millions) . ($millions>1 ? ' millions' : ' million'); }
    $n %= 1000000;
    $thousands = intdiv($n, 1000);
    if ($thousands) {
      $parts[] = ($thousands===1 ? 'mille' : _fr_under_1000($thousands).' mille');
    }
    $rest = $n % 1000;
    if ($rest) $parts[] = _fr_under_1000($rest);
    return implode(' ', $parts);
  }
  function tnd_amount_in_words($amount): string {
    $a = round((float)$amount, 3);
    $din = (int)floor($a + 1e-9);
    $mil = (int)round(($a - $din) * 1000);
    if ($mil === 1000) { $din++; $mil = 0; }
    $dinTxt = _fr_number_words($din);
    $milTxt = ($mil === 0) ? 'zéro' : str_pad((string)$mil, 3, '0', STR_PAD_LEFT);
    $milUnit = ($mil === 1) ? 'millime' : 'millimes';
    return trim($dinTxt).' dinars et '.$milTxt.' '.$milUnit;
  }
}

/* ---- Compute totals (independent of pagination) ---- */
$totalHT = 0.0;
foreach ($ProjetsFacture as $p) {
  $q    = $p['qte'] ?? '';
  $pu   = $p['prix_unit_htv'] ?? null;
  $pf   = $p['prixForfitaire'] ?? null;
  $pttc = $p['prixTTC'] ?? null;

  if ($pf !== null && $pf !== '') {
    $totalHT += (float)$pf;
  } elseif ($q !== '' && $q !== 'ENS' && is_numeric($q) && is_numeric($pu)) {
    $totalHT += (float)$q * (float)$pu;
  } elseif ($pttc !== null && $pttc !== '' && is_numeric($pttc)) {
    // Fallback: if only TTC is provided, derive HT using current TVA rate
    $rate    = $isExonore ? 0.0 : 0.19;
    $baseHT  = $rate > 0 ? ((float)$pttc / (1.0 + $rate)) : (float)$pttc;
    $totalHT += round($baseHT, 3);
  }
}
$tva_rate = $isExonore ? 0.0 : 0.19;
$tva      = round($totalHT * $tva_rate, 3);
$ttc      = round($totalHT + $tva, 3);
$timbre   = 1.000;
$hon      = round($ttc + $timbre, 3);
$txt_montant = tnd_amount_in_words($hon);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Facture <?= htmlspecialchars($infosFacture['num_fact'] ?? '') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
/* ===== Sheet system ===== */
:root{ --sheet-w:210mm; --sheet-h:297mm; --pad:10mm; --gap:4mm; --font:Arial,Helvetica,sans-serif }
*{ box-sizing:border-box }
html,body{ background:#f6f6f6; margin:0; color:#111; font-family:var(--font) }
#controls{ position:sticky; top:0; z-index:999; background:#111; padding:8px 10px; text-align:right }
#printBtn{ appearance:none; border:0; padding:8px 14px; font-weight:700; cursor:pointer; border-radius:6px; background:#0b57d0; color:#fff }
#printBtn:active{ transform:translateY(1px) }

.sheets{ width:var(--sheet-w); margin:12px auto }
.sheet{
  width:var(--sheet-w);
  min-height:var(--sheet-h);
  background:#fff; margin:0 auto 12px;
  box-shadow:0 2px 8px rgba(0,0,0,.1);
  position:relative; overflow:hidden;
  padding:var(--pad); display:flex; flex-direction:column; gap:var(--gap)
}

/* ===== PRINT ===== */
@media print{
  html,body{ background:#fff }
  @page{ size:A4; margin:0 }
  #controls{ display:none !important }
  .sheets{ margin:0 }
  .sheet{
    box-shadow:none;
    min-height:auto !important;
    height:auto !important;
    margin:0 !important;
    padding:9.5mm;
    break-inside:avoid; page-break-inside:avoid;
    -webkit-print-color-adjust:exact; print-color-adjust:exact;
  }
  .sheet + .sheet{ break-before:page; page-break-before:always; }
  .single-sheet .sheet{ break-before:auto !important; page-break-before:auto !important; }
}

/* ===== Header (logo | title | meta) ===== */
.header-card{
  display:grid;
  grid-template-columns:46mm 1fr 62mm; /* logo | title | meta */
  align-items:stretch;                 /* stretch children to full height */
  border:1px solid #000;
}

/* Use borders on the OUTER cells so separators print and reach top/bottom */
.header-cell.logo{
  padding:0;
  border-right:1px solid #000;         /* separator between logo & title */
  align-self:stretch;
  display:flex;
}
.header-cell.logo .pad{
  padding:4mm;
  height:100%;
  width:100%;
  display:flex;
  align-items:center;
  justify-content:center;
}

.header-cell.meta{
  padding:0;                           /* move padding inside .meta-pad */
  border-left:1px solid #000;          /* separator between title & meta */
  align-self:stretch;
  display:flex;
}
.header-cell.meta .meta-pad{
  padding:3mm;
  height:100%;
  width:100%;
}

.header-cell{ padding:4mm; display:flex; align-items:center } /* title cell */
.header-cell.logo img{ display:block; height:16mm; width:auto }
.facture-title{ margin:0; text-transform:uppercase; font-weight:800; font-size:5mm; letter-spacing:.2mm }
.facture-no{ font-weight:800; font-size:5mm }
.meta-card{ height:100%; display:grid; gap:2mm; background:#fff; font-size:3.2mm } /* removed border-left here */
.meta-row{ display:grid; grid-template-columns:16mm 1fr; gap:2mm; line-height:1.2 }
.meta-label{ font-weight:700 }

/* ===== Client box ===== */
.client-card{
  border:1px solid #000; padding:4mm; background:#fff;
  width:90mm; align-self:flex-start;
}
.client-card ol{ margin:0; padding-left:5mm }
.client-card li{ margin:2mm 0; font-size:3.4mm }

/* ===== Items table ===== */
.table-wrap{ width:100% }
.invoice-table{ width:100%; border-collapse:collapse; table-layout:fixed; background:#fff; border:1px solid #000 }
.invoice-table thead th{
  background:#f4f4f4; text-transform:uppercase; font-weight:700; font-size:3.2mm;
  border:1px solid #000; padding:2.2mm; line-height:1.2
}
.col-desc{ width:120mm }
.col-qte,.col-puh,.col-pth{ width:22mm }
.invoice-table td{
  border:1px solid #000; padding:2.2mm; font-size:3.2mm; line-height:1.35; vertical-align:top
}
.desc{ text-transform:uppercase; letter-spacing:.1mm }
.t-center{ text-align:center }
.t-right{ text-align:right }

/* ===== Totals ===== */
.totals{
  margin-top:2mm;
  width:90mm;
  margin-left:auto;
  display:grid;
  grid-template-columns:1fr 38mm;
  gap:2mm;
  border:1px solid #000;
  background:#fff;
}
.totals .labels, .totals .values{ padding:2mm }
.totals .labels div, .totals .values div{ margin:1mm 0; font-size:3.2mm }
.totals .values div{ text-align:right }

/* ===== Amount in letters & signature ===== */
.amount-letters{ margin-top:3mm; font-size:3.2mm }
.amount-letters .line{ display:inline-block; min-width:80mm; border-bottom:1px dashed #777; text-transform:uppercase }

.signature{
  margin-top:auto; display:flex; gap:6mm; align-items:flex-end;
}
.signature-note{ font-size:3.2mm; font-weight:400 }
.sig-pad{
  border:1px dashed #666; height:26mm; flex:0 0 70mm; background:#fafafa; display:flex; align-items:flex-end; justify-content:center; padding:3mm;
  font-size:3mm; color:#555
}

/* ===== Legal block ===== */
.legal{
  border-top:1px solid #000;
  padding-top:3mm;
  font-size:3mm;
  margin-top:auto; /* push footer to bottom of page */
}
.legal .cols{ display:grid; grid-template-columns:62% 36%; gap:2% }
.legal p{ margin:2mm 0 }

/* Page numbers */
.page-number{ font-size:3.2mm; text-align:right; margin-top:auto }
</style>
</head>
<body>

<!-- Controls -->
<div id="controls"><button id="printBtn" onclick="window.print()">Imprimer</button></div>

<!-- Sheets container (JS will fill it) -->
<div class="sheets" id="sheets"></div>

<!-- ===== Templates (hidden) ===== -->
<template id="tpl-header">
  <div class="header-card">
    <div class="header-cell logo">
      <div class="pad">
        <img src="../../assets/images/logo.png" alt="TPC">
      </div>
    </div>
    <div class="header-cell">
      <h1 class="facture-title" contenteditable="true">
        FACTURE <span class="facture-no" contenteditable="true"><?= htmlspecialchars($infosFacture['num_fact'] ?? '') ?></span>
      </h1>
    </div>
    <div class="header-cell meta">
      <div class="meta-pad">
        <div class="meta-card">
          <div class="meta-row"><div class="meta-label">Code :</div><div contenteditable="true">DAS-ACH-04</div></div>
          <div class="meta-row"><div class="meta-label">I.R :</div><div contenteditable="true">00</div></div>
          <div class="meta-row"><div class="meta-label">D.E :</div><div contenteditable="true">21/10/2025</div></div>
          <div class="meta-row"><div class="meta-label">Page :</div><div><span class="page-i">1</span> / <span class="page-n">1</span></div></div>
        </div>
      </div>
    </div>
  </div>
</template>

<template id="tpl-client">
  <div class="client-card">
    <ol>
      <li>Date : <b contenteditable="true"><?= htmlspecialchars($date ?? '') ?></b></li>
      <li>Client : <b contenteditable="true"><?= htmlspecialchars($infosFacture['nom_client'] ?? '') ?></b></li>
      <li>Adresse : <b contenteditable="true"><?= htmlspecialchars($infosFacture['adresse'] ?? '') ?></b></li>
      <li>Matricule Fiscale : <b contenteditable="true"><?= htmlspecialchars($infosFacture['matriculeFiscale'] ?? '') ?></b></li>
      <li>Exonoration N° : <b contenteditable="true"><?= htmlspecialchars($infosFacture['numexonoration'] ?? '') ?></b></li>
      <li>Bon de commande N° : <b contenteditable="true"><?= htmlspecialchars($bonCommandeValue) ?></b></li>
    </ol>
  </div>
</template>

<template id="tpl-table">
  <div class="table-wrap">
    <table class="invoice-table">
      <thead>
        <tr>
          <th class="col-desc">DESCRIPTION INSPECTION PERIODIQUE</th>
          <th class="col-qte t-center">QTE</th>
          <th class="col-puh t-center">P.U. H.T</th>
          <th class="col-pth t-center">P.T. H.T</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</template>

<template id="tpl-totals">
  <div class="totals">
    <div class="labels">
      <div>Total H.T</div>
      <div>taux de t.v.a</div>
      <div>t.v.a</div>
      <div>t.t.c</div>
      <div>droit de timbre</div>
      <div>total</div>
    </div>
    <div class="values">
      <div contenteditable="true"><?= number_format($totalHT,3,'.','') ?></div>
      <div contenteditable="true"><?= $isExonore ? '0%' : '19%' ?></div>
      <div contenteditable="true"><?= number_format($tva,3,'.','') ?></div>
      <div contenteditable="true"><?= number_format($ttc,3,'.','') ?></div>
      <div contenteditable="true"><?= number_format($timbre,3,'.','') ?></div>
      <div contenteditable="true"><?= number_format($hon,3,'.','') ?></div>
    </div>
  </div>
</template>

<template id="tpl-amount">
  <div class="amount-letters">
    Arrêtée la présente facture à la somme de :
    <span class="line" contenteditable="true"><?= htmlspecialchars($txt_montant) ?></span>
  </div>
</template>

<template id="tpl-signature">
  <div class="signature">
    <div class="signature-note">MERCI DE VOTRE CONFIANCE &mdash; LA DIRECTION &mdash; TPC</div>
    <div class="sig-pad">Signature &amp; cachet</div>
  </div>
  </template>

<template id="tpl-legal">
  <div class="legal">
    <div class="cols">
      <div>
        <p contenteditable="true">
          Veuillez rédiger tous les chèques à l'ordre de <b>TUNISIA POLYCONTROLS</b>.<br>
          Pour toute question concernant cette facture, veuillez nous contacter par e-mail:
          <b>tunisia.polycontrols.tpc@gmail.com</b>.<br>
          Veuillez rédiger les virements au nom de <b>T.P.C</b> sur le <b>RIB N° 14 305 305 1017 00061 8 08</b><br>
        </p>
      </div>
      <div>
        <p contenteditable="true">
          <b>TPC : TUNISIA POLYCONTROLS. S.A.R.L</b> au capital de 20.000 DT<br>
          Code TVA: 1426729 H/A/M/000 — R.C: B 2621285205<br>
          E-mail: <b>tunisia.polycontrols.tpc@gmail.com</b><br>
          <b>Siège Social :</b> 5 rue Benghazi-bureau 4-5, 4<sup>ème</sup> étage - 1002 Tunis<br>
          <b>Tél : 36 131 731</b> — <b>GSM : 24 131 544</b>
        </p>
      </div>
    </div>
    <div class="page-number">Page <span class="page-i">1</span> / <span class="page-n">1</span></div>
  </div>
</template>

<!-- ===== Rows source ===== -->
<div id="rows-source" style="display:none">
  <table><tbody id="rows-body">
  <?php
    $printedAny = false;
    $isForfaitaire = isset($verifForfitaireFacture) && $verifForfitaireFacture === 'forfitaire';
    if ($nbAdresse == 0) {
      // Single block (no explicit addresses). For forfaitaire invoices,
      // show the summed Total H.T only on the first ENS line.
      $ensIndex = null;
      $groupTotalHT = 0.0;
      if ($isForfaitaire && !empty($ProjetsFacture)) {
        foreach ($ProjetsFacture as $idx => $projet) {
          $qte         = $projet['qte'] ?? '';
          $prixUnit    = $projet['prix_unit_htv'] ?? '';
          $prixForfait = $projet['prixForfitaire'] ?? '';
          $prixTTC     = $projet['prixTTC'] ?? '';
          if ($ensIndex === null && $qte === 'ENS') {
            $ensIndex = $idx;
          }
          $lineHT = null;
          if ($prixForfait !== '' && $prixForfait !== null) {
            $lineHT = (float)$prixForfait;
          } elseif ($qte !== '' && $qte !== 'ENS' && $prixUnit !== '') {
            $lineHT = (float)$qte * (float)$prixUnit;
          } elseif ($prixTTC !== '') {
            $rate   = $isExonore ? 0.0 : 0.19;
            $lineHT = $rate > 0 ? ((float)$prixTTC / (1.0 + $rate)) : (float)$prixTTC;
          }
          if (is_numeric($lineHT)) {
            $groupTotalHT += (float)$lineHT;
          }
        }
        if ($ensIndex === null) {
          $ensIndex = 0;
        }
      }
      foreach ($ProjetsFacture as $idx => $projet) {
        $qte  = $projet['qte'] ?? '';
        $prixUnit = $projet['prix_unit_htv'] ?? '';
        $prixForfait = $projet['prixForfitaire'] ?? '';
        $prixTTC = $projet['prixTTC'] ?? '';
        $pu   = ($qte !== 'ENS' && $qte!=='') ? $prixUnit : $prixForfait;
        $pth  = null;
        if ($prixForfait !== '') $pth = (float)$prixForfait;
        elseif ($qte!=='' && $qte!=='ENS' && $prixUnit!=='') $pth = (float)$qte * (float)$prixUnit;
        elseif ($prixTTC !== '') {
          $rate = $isExonore ? 0.0 : 0.19;
          $pth = $rate > 0 ? ((float)$prixTTC / (1.0 + $rate)) : (float)$prixTTC;
        }
        // If PU is missing but TTC is present, derive PU HT from TTC / QTE (or TTC if no QTE)
        if (($pu === '' || $pu === null) && $prixTTC !== '') {
          $rate = $isExonore ? 0.0 : 0.19;
          $baseHT = $rate > 0 ? ((float)$prixTTC / (1.0 + $rate)) : (float)$prixTTC;
          $qty = (is_numeric($qte) && (float)$qte > 0) ? (float)$qte : 1.0;
          $pu = $baseHT / $qty;
          if (!is_numeric($pth)) { $pth = $baseHT; }
        }
        // Prefer aliased names from LEFT JOIN; fallback to legacy keys
        $desc = $projet['projet_classement'] ?? ($projet['classement'] ?? ($projet['projet_designation'] ?? ($projet['designation'] ?? '')));
        if ($desc === '' && isset($projet['projet'])) { $desc = 'Projet '.(string)$projet['projet']; }
        $desc = trim((string)$desc);
        if ($desc === '') { $desc = 'INSPECTION PERIODIQUE'; }
        $printedAny = true;
        // For forfaitaire: only the first ENS line shows the summed Total H.T,
        // all other lines in the block keep empty PU/PT to match the desired layout.
        $puDisplay  = $pu;
        $pthDisplay = $pth;
        if ($isForfaitaire && !empty($ProjetsFacture)) {
          if ($idx === $ensIndex) {
            $puDisplay  = '';
            $pthDisplay = $groupTotalHT;
          } else {
            $puDisplay  = '';
            $pthDisplay = null;
          }
        }
        ?>
        <tr>
          <td class="desc" contenteditable="true"><?= htmlspecialchars($desc) ?></td>
          <td class="t-center" contenteditable="true"><?= htmlspecialchars($qte) ?></td>
          <td class="t-center" contenteditable="true"><?= htmlspecialchars($puDisplay) ?></td>
          <td class="t-right" contenteditable="true"><?= is_numeric($pthDisplay)?number_format($pthDisplay,3,'.',''):'' ?></td>
        </tr>
        <?php
      }
    } else {
      // 1) Blocks for each non-empty address
      foreach ($adressesClient as $addr) {
        $label = $addr['adresseClient'] ?? ($addr[0] ?? '');
        if (!$label) continue;
        echo '<tr data-kind="section"><td class="desc" colspan="4" style="font-weight:700;text-decoration:underline" contenteditable="true">'.htmlspecialchars($label).'</td></tr>';
        // Collect lines for this address
        $linesForAddr = [];
        foreach ($ProjetsFacture as $projet) {
          if (trim((string)($projet['adresseClient'] ?? '')) === trim((string)$label)) {
            $linesForAddr[] = $projet;
          }
        }
        if (empty($linesForAddr)) {
          continue;
        }
        // For forfaitaire invoices: sum Total H.T for this address and
        // remember the index of the first ENS line.
        $ensIndexAddr   = null;
        $addrTotalHT    = 0.0;
        if ($isForfaitaire) {
          foreach ($linesForAddr as $idx => $p) {
            $qte         = $p['qte'] ?? '';
            $prixUnit    = $p['prix_unit_htv'] ?? '';
            $prixForfait = $p['prixForfitaire'] ?? '';
            $prixTTC     = $p['prixTTC'] ?? '';
            if ($ensIndexAddr === null && $qte === 'ENS') {
              $ensIndexAddr = $idx;
            }
            $lineHT = null;
            if ($prixForfait !== '' && $prixForfait !== null) {
              $lineHT = (float)$prixForfait;
            } elseif ($qte !== '' && $qte !== 'ENS' && $prixUnit !== '') {
              $lineHT = (float)$qte * (float)$prixUnit;
            } elseif ($prixTTC !== '') {
              $rate   = $isExonore ? 0.0 : 0.19;
              $lineHT = $rate > 0 ? ((float)$prixTTC / (1.0 + $rate)) : (float)$prixTTC;
            }
            if (is_numeric($lineHT)) {
              $addrTotalHT += (float)$lineHT;
            }
          }
          if ($ensIndexAddr === null) {
            $ensIndexAddr = 0;
          }
        }
        foreach ($linesForAddr as $idx => $projet) {
          $qte  = $projet['qte'] ?? '';
          $prixUnit    = $projet['prix_unit_htv'] ?? '';
          $prixForfait = $projet['prixForfitaire'] ?? '';
          $prixTTC     = $projet['prixTTC'] ?? '';

          // Secure computation of unit and total HT:
          // - Use forfait value when present
          // - Else only multiply qte * unit when both are numeric
          $pu        = $prixForfait !== '' ? $prixForfait : $prixUnit;
          $qtyRaw    = $projet['qte'] ?? null;
          $qty       = (is_scalar($qtyRaw) && $qtyRaw !== '' && $qtyRaw !== 'ENS' && is_numeric($qtyRaw))
                        ? (float)$qtyRaw
                        : null;
          $unitValue = ($prixUnit !== '' && is_numeric($prixUnit)) ? (float)$prixUnit : null;

          if ($prixForfait !== '' && $prixForfait !== null) {
            $pth = (float)$prixForfait;
          } elseif ($qty !== null && $unitValue !== null) {
            $pth = $qty * $unitValue;
          } else {
            $pth = null;
          }

          if ((!isset($pth) || $pth === '' || !is_numeric($pth)) && $prixTTC !== '') {
            $rate = $isExonore ? 0.0 : 0.19;
            $pth = $rate > 0 ? ((float)$prixTTC / (1.0 + $rate)) : (float)$prixTTC;
          }
          if (($pu === '' || $pu === null) && $prixTTC !== '') {
            $rate = $isExonore ? 0.0 : 0.19;
            $baseHT = $rate > 0 ? ((float)$prixTTC / (1.0 + $rate)) : (float)$prixTTC;
            $qty = (is_numeric($qte) && (float)$qte > 0) ? (float)$qte : 1.0;
            $pu = $baseHT / $qty;
            if (!is_numeric($pth)) { $pth = $baseHT; }
          }
          $desc = $projet['projet_classement'] ?? ($projet['classement'] ?? ($projet['projet_designation'] ?? ($projet['designation'] ?? '')));
          if ($desc === '' && isset($projet['projet'])) { $desc = 'Projet '.(string)$projet['projet']; }
          $desc = trim((string)$desc);
          if ($desc === '') { $desc = 'INSPECTION PERIODIQUE'; }
          $printedAny = true;
          $puDisplay  = $pu;
          $pthDisplay = $pth;
          if ($isForfaitaire) {
            if ($idx === $ensIndexAddr) {
              $puDisplay  = '';
              $pthDisplay = $addrTotalHT;
            } else {
              $puDisplay  = '';
              $pthDisplay = null;
            }
          }
          ?>
          <tr>
            <td class="desc" contenteditable="true"><?= htmlspecialchars($desc) ?></td>
            <td class="t-center" contenteditable="true"><?= htmlspecialchars($qte) ?></td>
            <td class="t-center" contenteditable="true"><?= htmlspecialchars($puDisplay) ?></td>
            <td class="t-right" contenteditable="true"><?= is_numeric($pthDisplay)?number_format($pthDisplay,3,'.','') : '' ?></td>
          </tr>
          <?php
        }
      }
    }

    // Additional block for lines without any address (or not matching a known one)
    $knownAdrs = [];
    foreach ($adressesClient as $addr) {
      $val = trim((string)($addr['adresseClient'] ?? ($addr[0] ?? '')));
      if ($val !== '') { $knownAdrs[$val] = true; }
    }
    $printedSansAdresse = false;
    if ($nbAdresse > 0) {
      // Collect SANS ADRESSE lines
      $sansAdresseLines = [];
      foreach ($ProjetsFacture as $projet) {
        $adrLine = trim((string)($projet['adresseClient'] ?? ''));
        if ($adrLine !== '' && isset($knownAdrs[$adrLine])) {
          continue; // already printed in a named address section
        }
        $sansAdresseLines[] = $projet;
      }

      if (!empty($sansAdresseLines)) {
        echo '<tr data-kind="section"><td class="desc" colspan="4" style="font-weight:700;text-decoration:underline" contenteditable="true">SANS ADRESSE</td></tr>';
        $printedSansAdresse = true;

        // Compute total HT across SANS ADRESSE lines and remember first ENS index
        $ensIndexSans = null;
        $sansTotalHT  = 0.0;
        foreach ($sansAdresseLines as $idx => $p) {
          $qte         = $p['qte'] ?? '';
          $prixUnit    = $p['prix_unit_htv'] ?? '';
          $prixForfait = $p['prixForfitaire'] ?? '';
          $prixTTC     = $p['prixTTC'] ?? '';
          if ($ensIndexSans === null && $qte === 'ENS') {
            $ensIndexSans = $idx;
          }
          $lineHT = null;
          if ($prixForfait !== '' && $prixForfait !== null) {
            $lineHT = (float)$prixForfait;
          } elseif ($qte !== '' && $qte !== 'ENS' && $prixUnit !== '') {
            $lineHT = (float)$qte * (float)$prixUnit;
          } elseif ($prixTTC !== '') {
            $rate   = $isExonore ? 0.0 : 0.19;
            $lineHT = $rate > 0 ? ((float)$prixTTC / (1.0 + $rate)) : (float)$prixTTC;
          }
          if (is_numeric($lineHT)) {
            $sansTotalHT += (float)$lineHT;
          }
        }
        if ($ensIndexSans === null) {
          $ensIndexSans = 0;
        }

        // Print all SANS ADRESSE lines: keep their PU, but show total HT only on the ENS row
        foreach ($sansAdresseLines as $idx => $projet) {
          $qte         = $projet['qte'] ?? '';
          $prixUnit    = $projet['prix_unit_htv'] ?? '';
          $prixForfait = $projet['prixForfitaire'] ?? '';
          $prixTTC     = $projet['prixTTC'] ?? '';

          $pu  = ($qte !== 'ENS' && $qte !== '') ? $prixUnit : $prixForfait;
          $pth = null;

          if ($prixForfait !== '' && $prixForfait !== null) {
            $pth = (float)$prixForfait;
          } elseif ($qte !== '' && $qte !== 'ENS' && $prixUnit !== '') {
            $pth = (float)$qte * (float)$prixUnit;
          } elseif ($prixTTC !== '') {
            $rate = $isExonore ? 0.0 : 0.19;
            $pth  = $rate > 0 ? ((float)$prixTTC / (1.0 + $rate)) : (float)$prixTTC;
          }

          if (($pu === '' || $pu === null) && $prixTTC !== '') {
            $rate   = $isExonore ? 0.0 : 0.19;
            $baseHT = $rate > 0 ? ((float)$prixTTC / (1.0 + $rate)) : (float)$prixTTC;
            $qty    = (is_numeric($qte) && (float)$qte > 0) ? (float)$qte : 1.0;
            $pu     = $baseHT / $qty;
            if (!is_numeric($pth)) { $pth = $baseHT; }
          }

          $desc = $projet['projet_classement']
            ?? ($projet['classement']
            ?? ($projet['projet_designation']
            ?? ($projet['designation'] ?? '')));

          if ($desc === '' && isset($projet['projet'])) {
            $desc = 'Projet ' . (string)$projet['projet'];
          }

          $desc = trim((string)$desc);
          if ($desc === '') {
            $desc = 'INSPECTION PERIODIQUE';
          }

          $puDisplay  = $pu;
          $pthDisplay = $pth;
          if ($isForfaitaire) {
            if ($idx === $ensIndexSans) {
              $puDisplay  = '';
              $pthDisplay = $sansTotalHT;
            } else {
              $puDisplay  = '';
              $pthDisplay = null;
            }
          }
          ?>
          <tr>
            <td class="desc" contenteditable="true"><?= htmlspecialchars($desc) ?></td>
            <td class="t-center" contenteditable="true"><?= htmlspecialchars($qte) ?></td>
            <td class="t-center" contenteditable="true"><?= htmlspecialchars($puDisplay) ?></td>
            <td class="t-right" contenteditable="true"><?= $pthDisplay !== null ? number_format($pthDisplay,3,'.','') : '' ?></td>
          </tr>
          <?php
        }
      }
    }
  ?>
  </tbody></table>
</div>

<script>
/* ========= Simple pagination: fixed rows per page (no split rows) ========= */
(function(){
  const sheetsRoot = document.getElementById('sheets');
  const rowsSrc    = Array.from(document.querySelectorAll('#rows-body > tr'));
  if (!sheetsRoot || rowsSrc.length === 0) return;

  // Max project rows on first page, and on subsequent pages
  const MAX_FIRST_PAGE_ROWS  = 5;
  const MAX_OTHER_PAGE_ROWS  = 20;

  const createSheet = () => {
    const s = document.createElement('div');
    s.className = 'sheet';
    s.append(document.getElementById('tpl-header').content.cloneNode(true));
    const tableFrag = document.getElementById('tpl-table').content.cloneNode(true);
    s.append(tableFrag);
    return s;
  };

  const addClientTo = (sheet) => {
    const header = sheet.firstElementChild;
    const client = document.getElementById('tpl-client').content.cloneNode(true);
    sheet.insertBefore(client, header.nextSibling);
  };

  const getTBody = (sheet) => sheet.querySelector('tbody');

  const sheets = [];
  let sheetIndex = 0;
  let sheet = createSheet();
  sheetsRoot.appendChild(sheet);
  sheets.push(sheet);
  addClientTo(sheet);

  let rowIndex = 0;
  while (rowIndex < rowsSrc.length) {
    const maxRows = (sheetIndex === 0) ? MAX_FIRST_PAGE_ROWS : MAX_OTHER_PAGE_ROWS;
    const tbody   = getTBody(sheet);
    let count     = tbody.children.length;

    while (count < maxRows && rowIndex < rowsSrc.length) {
      tbody.appendChild(rowsSrc[rowIndex].cloneNode(true));
      rowIndex++;
      count++;
    }

    if (rowIndex < rowsSrc.length) {
      // More rows remaining => new sheet
      sheetIndex++;
      sheet = createSheet();
      sheetsRoot.appendChild(sheet);
      sheets.push(sheet);
    }
  }

  // Append totals + amount in words + signature on the last sheet only
  const last = sheets[sheets.length - 1];
  last.append(document.getElementById('tpl-totals').content.cloneNode(true));
  last.append(document.getElementById('tpl-amount').content.cloneNode(true));
  last.append(document.getElementById('tpl-signature').content.cloneNode(true));

  // Attach the legal/footer block to every sheet so the footer is present
  sheets.forEach(sh => {
    sh.append(document.getElementById('tpl-legal').content.cloneNode(true));
  });

  const N = sheets.length;
  if (N === 1) document.body.classList.add('single-sheet');
  sheets.forEach((s, idx) => {
    const i = idx + 1;
    s.querySelectorAll('.page-i').forEach(n => n.textContent = String(i));
    s.querySelectorAll('.page-n').forEach(n => n.textContent = String(N));
  });
})();
</script>
</body>
</html>
