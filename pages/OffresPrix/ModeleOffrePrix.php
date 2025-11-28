<?php
// ------- Safe defaults -------
$infosOffre            = $infosOffre            ?? [];
$ProjetsOffre          = $ProjetsOffre          ?? [];
$Projets               = $Projets               ?? [];
$adressesClient        = $adressesClient        ?? [];
$nbAdresse             = $nbAdresse             ?? 0;
$verifForfitaireOffre  = $verifForfitaireOffre  ?? 'Nonforfitaire';
$date                  = $date                  ?? '';
$montant_lettres       = $montant_lettres       ?? '';

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

// ---- Build rows (server) ----
$rows = !empty($ProjetsOffre) ? $ProjetsOffre : (!empty($Projets) ? $Projets : []);
$numOffrePrint = $_GET['offre'] ?? '';
// Final fallback: fetch directly from facture_projets if still empty
if (empty($rows) && $numOffrePrint !== '') {
  require_once '../../class/connexion.db.php';
  $pdo = connexion();
  $stmt = $pdo->prepare("
    SELECT fp.*, p.classement, p.id AS projet_id
    FROM facture_projets AS fp
    LEFT JOIN projet AS p ON fp.projet = p.id
    WHERE fp.facture = :num
  ");
  $stmt->execute([':num' => $numOffrePrint]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if (empty($adressesClient)) {
    $stAddr = $pdo->prepare("SELECT DISTINCT(adresseClient) AS adresseClient FROM facture_projets WHERE facture = :num");
    $stAddr->execute([':num' => $numOffrePrint]);
    $adressesClient = $stAddr->fetchAll(PDO::FETCH_ASSOC);
  }
}
$totalHT = 0.0;
ob_start();
$addressLabels = [];
foreach ($adressesClient as $a) {
  $label = is_array($a) ? ($a['adresseClient'] ?? ($a[0] ?? '')) : (string)$a;
  if ($label !== '') $addressLabels[] = $label;
}
if (!empty($addressLabels)) {
  $groups = [];
  foreach ($rows as $r){
    $lab = $r['adresseClient'] ?? ($r['adresse'] ?? 'AUTRE');
    $groups[$lab][] = $r;
  }
  foreach ($addressLabels as $label){
    if (!isset($groups[$label])) continue;
    echo '<tr class="address-title"><td colspan="4" contenteditable="true">'.htmlspecialchars($label).'</td></tr>';
    foreach ($groups[$label] as $projet){
      $qte = $projet['qte'] ?? '';
      $pu  = ($qte!=='ENS' && $qte!=='') ? ($projet['prix_unit_htv'] ?? '') : ($projet['prixForfitaire'] ?? '');
      if ($verifForfitaireOffre === 'forfitaire' && ($projet['prixForfitaire'] ?? '') !== '') {
        $pth = (float)$projet['prixForfitaire'];
      } else {
        $pth = ($qte!=='ENS' && $qte!=='') ? (float)($projet['qte'] ?? 0) * (float)($projet['prix_unit_htv'] ?? 0)
                                           : (float)($projet['prixForfitaire'] ?? 0);
      }
      $totalHT += $pth;
      $desc = $projet['projet_classement'] ?? ($projet['classement'] ?? ($projet['projet_designation'] ?? ($projet['designation'] ?? '')));
      $desc = trim((string)$desc);
      if ($desc === '') { $desc = 'INSPECTION PERIODIQUE'; }
      echo '<tr class="item-row">';
      echo '<td class="desc" contenteditable="true">'.htmlspecialchars($desc).'</td>';
      echo '<td class="t-center" contenteditable="true">'.htmlspecialchars($qte).'</td>';
      echo '<td class="t-center" contenteditable="true">'.htmlspecialchars($pu).'</td>';
      echo '<td class="t-right" contenteditable="true">'.number_format($pth,3,'.','').'</td>';
      echo '</tr>';
    }
  }
} else {
  foreach ($rows as $projet){
    $qte = $projet['qte'] ?? '';
    $pu  = ($qte!=='ENS' && $qte!=='') ? ($projet['prix_unit_htv'] ?? '') : ($projet['prixForfitaire'] ?? '');
    if ($verifForfitaireOffre === 'forfitaire' && ($projet['prixForfitaire'] ?? '') !== '') {
      $pth = (float)$projet['prixForfitaire'];
    } else {
      $pth = ($qte!=='ENS' && $qte!=='') ? (float)($projet['qte'] ?? 0) * (float)($projet['prix_unit_htv'] ?? 0)
                                         : (float)($projet['prixForfitaire'] ?? 0);
    }
    $totalHT += $pth;
    $desc = $projet['projet_classement'] ?? ($projet['classement'] ?? ($projet['projet_designation'] ?? ($projet['designation'] ?? '')));
    $desc = trim((string)$desc);
    if ($desc === '') { $desc = 'INSPECTION PERIODIQUE'; }
    echo '<tr class="item-row">';
    echo '<td class="desc" contenteditable="true">'.htmlspecialchars($desc).'</td>';
    echo '<td class="t-center" contenteditable="true">'.htmlspecialchars($qte).'</td>';
    echo '<td class="t-center" contenteditable="true">'.htmlspecialchars($pu).'</td>';
    echo '<td class="t-right" contenteditable="true">'.number_format($pth,3,'.','').'</td>';
    echo '</tr>';
  }
}
$serverRenderedRows = trim(ob_get_clean());

// Totals (server), still editable in UI
$tva_rate = 0.19;
$tva      = round($totalHT * $tva_rate, 3);
$ttc      = round($totalHT + $tva, 3);
$timbre   = 1.000;
$sum      = $ttc + $timbre;
$txt_sum  = tnd_amount_in_words($sum);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Offre <?= htmlspecialchars($infosOffre['num_offre'] ?? '') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
/* ===== A4 sheet system ===== */
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

@media print{
  html,body{ background:#fff }
  @page{ size:A4; margin:0 }
  #controls{ display:none !important }
  .sheets{ margin:0 }
  .sheet{
    box-shadow:none;
    padding:9.5mm;
    margin:0 !important;
    min-height:auto !important;
    height:auto !important;
    break-inside:avoid; page-break-inside:avoid;
    -webkit-print-color-adjust:exact; print-color-adjust:exact;
  }
  .sheet + .sheet{ break-before:page; page-break-before:always; }
  .single-sheet .sheet{ break-before:auto !important; page-break-before:auto !important; }
}

/* ===== Header (same robust separators as Facture) ===== */
.header-card{
  display:grid;
  grid-template-columns:46mm 1fr 62mm; /* logo | title | meta */
  align-items:stretch;                 /* stretch children to full height */
  border:1px solid #000;
}

/* Left cell (logo) with PRINT-SAFE separator */
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

/* Right cell (meta) with PRINT-SAFE separator */
.header-cell.meta{
  padding:0;                           /* padding inside .meta-pad */
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
.doc-title{ margin:0; text-transform:uppercase; font-weight:800; font-size:5mm; letter-spacing:.2mm }
.doc-no{ font-weight:800; font-size:5mm }
.meta-card{ height:100%; display:grid; gap:2mm; background:#fff; font-size:3.2mm } /* no extra border here */
.meta-row{ display:grid; grid-template-columns:16mm 1fr; gap:2mm; line-height:1.2 }
.meta-label{ font-weight:700 }

/* ===== Client box (fixed left, 90mm) ===== */
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
.address-title td{ font-weight:700; text-decoration:underline }

/* ===== Totals (compact, right) ===== */
.totals{
  margin-top:2mm;
  width:90mm;
  margin-left:auto;   /* push to the right */
  display:grid;
  grid-template-columns:1fr 38mm;
  gap:2mm;
  border:1px solid #000;
  background:#fff;
}
.totals .labels, .totals .values{ padding:2mm }
.totals .labels div, .totals .values div{ margin:1mm 0; font-size:3.2mm }
.totals .values div{ text-align:right }

/* ===== Amount & Signature ===== */
.amount-letters{ margin-top:3mm; font-size:3.2mm }
.amount-letters .line{ display:inline-block; min-width:80mm; border-bottom:1px dashed #777; text-transform:uppercase }
/* Remarque (editable line) */
.remark{ margin-top:3mm; font-size:3.2mm }
.remark .line{ display:inline-block; min-width:120mm; min-height:6mm; border-bottom:1px dashed #777 }

.signature{
  margin-top:auto;
  display:flex; gap:6mm; align-items:flex-end;
}
.signature-note{ font-size:3.2mm; font-weight:400 }
.sig-pad{
  border:1px dashed #666; height:26mm; flex:0 0 70mm; background:#fafafa; display:flex; align-items:flex-end; justify-content:center; padding:3mm;
  font-size:3mm; color:#555
}

/* ===== Legal block at end of last page ===== */
.legal{
  border-top:1px solid #000; padding-top:3mm; font-size:3mm; margin-top:3mm
}
.legal .cols{ display:grid; grid-template-columns:62% 36%; gap:2% }
.legal p{ margin:2mm 0 }

/* Page numbers (plain text via JS) */
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
      <h1 class="doc-title" contenteditable="true">
        OFFRE DE PRIX <span class="doc-no" contenteditable="true"><?= htmlspecialchars($infosOffre['num_offre'] ?? '') ?></span>
      </h1>
    </div>
    <div class="header-cell meta">
      <div class="meta-pad">
        <div class="meta-card">
          <div class="meta-row"><div class="meta-label">Code :</div><div contenteditable="true"><?= htmlspecialchars($infosOffre['code'] ?? 'DAS-ACH-03') ?></div></div>
          <div class="meta-row"><div class="meta-label">I.R :</div><div contenteditable="true"><?= htmlspecialchars($infosOffre['ir'] ?? '00') ?></div></div>
          <div class="meta-row"><div class="meta-label">D.E :</div><div contenteditable="true"><?= htmlspecialchars('21/10/2025') ?></div></div>
          <div class="meta-row"><div class="meta-label">Page :</div><div><span class="page-i">1</span> / <span class="page-n">1</span></div></div>
        </div>
      </div>
    </div>
  </div>
</template>

<template id="tpl-client">
  <div class="client-card">
    <ol>
      <li>Date de l’offre : <b contenteditable="true"><?= htmlspecialchars($date) ?></b></li>
      <li>Client : <b contenteditable="true"><?= htmlspecialchars($infosOffre['nom_client'] ?? '') ?></b></li>
      <li>Adresse : <b contenteditable="true"><?= htmlspecialchars($infosOffre['adresse'] ?? '') ?></b></li>
      <li>Matricule Fiscale : <b contenteditable="true"><?= htmlspecialchars($infosOffre['mat_fisc'] ?? '') ?></b></li>
    </ol>
  </div>
</template>

<template id="tpl-table">
  <div class="table-wrap">
    <table class="invoice-table">
      <thead>
        <tr>
          <th class="col-desc">Désignations</th>
          <th class="col-qte t-center">Qté</th>
          <th class="col-puh t-center">P.U. H.T</th>
          <th class="col-pth t-center">Total H.T</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</template>

<template id="tpl-totals">
  <div class="totals" contenteditable="true">
    <div class="labels">
      <div>Total H.T</div>
      <div>taux de t.v.a</div>
      <div>t.v.a</div>
      <div>t.t.c</div>
      <div>droit de timbre</div>
      <div>total</div>
    </div>
    <div class="values">
      <div><?= number_format($totalHT,3,'.','') ?></div>
      <div>19%</div>
      <div><?= number_format($tva,3,'.','') ?></div>
      <div><?= number_format($ttc,3,'.','') ?></div>
      <div><?= number_format($timbre,3,'.','') ?></div>
      <div><?= number_format($sum,3,'.','') ?></div>
    </div>
  </div>
</template>

<template id="tpl-amount">
  <div class="amount-letters" contenteditable="true">
    Arrêtée la présente offre à la somme de :
    <span class="line"><?= htmlspecialchars($txt_sum) ?></span>
  </div>
</template>

<template id="tpl-remark">
  <div class="remark">
    Remarque : <span class="line" contenteditable="true"></span>
  </div>
 </template>

<template id="tpl-signature">
  <div class="signature">
    <div class="signature-note">MERCI DE VOTRE CONFIANCE &mdash; LA DIRECTION &mdash; TPC :</div>
    <div class="sig-pad">Signature &amp; cachet</div>
  </div>
</template>

<template id="tpl-legal">
  <div class="legal">
    <div class="cols">
      <div>
        <p contenteditable="true">
          Veuillez rédiger tous les chèques à l'ordre de <b>TUNISIA POLYCONTROLS</b>.<br>
          Pour toute question concernant cette offre, veuillez nous contacter par e-mail:
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

<!-- ===== Hidden source of rows (flat list); JS paginates without splitting rows ===== -->
<div id="rows-source" style="display:none">
  <table><tbody id="rows-body">
    <?= $serverRenderedRows !== '' ? $serverRenderedRows : '<tr><td colspan="4" class="t-center" contenteditable="true">Aucune ligne trouvée.</td></tr>' ?>
  </tbody></table>
</div>

<script>
/* ========= Pagination engine: no split rows, cloned header, Page i / N ========= */
(function(){
  const sheetsRoot = document.getElementById('sheets');
  const rowsSrc    = Array.from(document.querySelectorAll('#rows-body > tr'));
  const EPS = 2; // px tolerance

  const createSheet = () => {
    const s = document.createElement('div');
    s.className = 'sheet';
    s.append( document.getElementById('tpl-header').content.cloneNode(true) );
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

  const fits = (container, node) => {
    container.append(node);
    const sheet = container.classList?.contains('sheet') ? container : container.closest('.sheet');
    const ok = (sheet.scrollHeight - sheet.clientHeight) <= EPS;
    if (!ok) node.remove();
    return ok;
  };

  const sheets = [];
  let sheet = createSheet();
  addClientTo(sheet);
  sheetsRoot.appendChild(sheet);
  sheets.push(sheet);

  // Fill rows without splitting
  for (const row of rowsSrc){
    const clone = row.cloneNode(true);
    if (!fits(getTBody(sheet), clone)){
      sheet = createSheet();
      sheetsRoot.appendChild(sheet);
      sheets.push(sheet);
      if (!fits(getTBody(sheet), clone)){
        getTBody(sheet).appendChild(clone);
      }
    }
  }

  // Totals, amount, signature, legal at the end
  let last = sheets[sheets.length-1];

  const totalsFrag    = document.getElementById('tpl-totals').content.cloneNode(true);
  if (!fits(last, totalsFrag)){ last = createSheet(); sheetsRoot.appendChild(last); sheets.push(last); }
  last.append(totalsFrag);

  const amountFrag    = document.getElementById('tpl-amount').content.cloneNode(true);
  if (!fits(last, amountFrag)){ last = createSheet(); sheetsRoot.appendChild(last); sheets.push(last); }
  last.append(amountFrag);

  const remarkFrag    = document.getElementById('tpl-remark').content.cloneNode(true);
  if (!fits(last, remarkFrag)){ last = createSheet(); sheetsRoot.appendChild(last); sheets.push(last); }
  last.append(remarkFrag);

  const signatureFrag = document.getElementById('tpl-signature').content.cloneNode(true);
  if (!fits(last, signatureFrag)){ last = createSheet(); sheetsRoot.appendChild(last); sheets.push(last); }
  last.append(signatureFrag);

  const legalFrag     = document.getElementById('tpl-legal').content.cloneNode(true);
  if (!fits(last, legalFrag)){ last = createSheet(); sheetsRoot.appendChild(last); sheets.push(last); }
  last.append(legalFrag);

  // Number pages
  const pagesTotal = sheets.length;
  if (pagesTotal === 1) document.body.classList.add('single-sheet');
  sheets.forEach((s, idx)=>{
    const i = idx+1;
    s.querySelectorAll('.page-i').forEach(n=>n.textContent = String(i));
    s.querySelectorAll('.page-n').forEach(n=>n.textContent = String(pagesTotal));
  });
})();
</script>

</body>
</html>
