<?php
/* Bordereau — A4, self-pagination, fully editable, 3 base rows, print button only
   D.E stays as "xx/xx/2025" (user can edit it). The small "Date :" shows the DB date. */

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/* ---- NEW helper: format DB date safely to dd/mm/YYYY ---- */
function fmtDate($s){
  $s = trim((string)$s);
  if ($s === '') return '';
  $ts = strtotime($s);
  return $ts ? date('d/m/Y', $ts) : $s; // fallback to raw if not parseable
}

/* ---- Inputs ---- */
$numeroB = $infosBordereau['num_bordereaux'] ?? '';
$ref     = $infosBordereau['ref']            ?? $numeroB;
$attn    = $infosBordereau['attention']      ?? ($infosBordereau['nom_client'] ?? '');
$codeDoc = $infosBordereau['code'] ?? 'DAS-TEC-06';
$irDoc   = $infosBordereau['ir']   ?? '00';
// Related facture number for this bordereau (from DB)
$numFact = $infosBordereau['num_fact'] ?? '';
// Track if we place the facture info inside the table (to avoid footer duplicate)
$facturePlacedInTable = false;

/* ---- NEW: take date from DB column `bordereaux.date` ---- */
$dbDateRaw  = $infosBordereau['date'] ?? '';          // e.g. "2025-01-09"
$displayDate = fmtDate($dbDateRaw);                    // "09/01/2025" or raw

/* Build initial table rows from DB */
$rows = [];
$idx  = 0;

// Insert the invoice as first row if available
if ($numFact !== '') {
  $facturePlacedInTable = true;
  $idx = 1; // first row index used
  $rows[] = [
    'num' => '1',
    'des' => '<div><strong>Facture N&deg;:</strong> '.e($numFact).'</div>',
    'nb'  => '1',
    'obs' => 'pour reglement',
  ];
}
foreach ((array)$infosBordereaux as $row) {
  $idx++;
  $des = '';
  if (!empty($row['adresse_bordereaux'])) {
    $des .= '<div style="font-weight:700;text-transform:uppercase">'.e($row['adresse_bordereaux']).'</div>';
  }
  // Base type string as stored in DB (may include quantity like "3 x Attestation")
  $typeRaw = isset($row['type']) ? (string)$row['type'] : '';
  if ($typeRaw !== '') {
    $parts = preg_split('/<br>|&/i', $typeRaw);
    foreach ($parts as $p) {
      $p = trim($p);
      if ($p === '') continue;
      // Strip leading "N x " if present, since quantity is shown in its own column
      if (preg_match('/^\\s*\\d+\\s*x\\s*(.+)$/i', $p, $mLine)) {
        $p = $mLine[1];
      }
      $des .= '<div>'.e($p).'</div>';
    }
  }
  if ($des === '') { $des = '&nbsp;'; }

  // Default quantity = 1; if type looks like "N x Something", extract N
  $qty = 1;
  if ($typeRaw !== '' && preg_match('/^\\s*(\\d+)\\s*x\\s*/i', $typeRaw, $mQty)) {
    $qty = (int)$mQty[1];
    if ($qty < 1) { $qty = 1; }
  }

  $rows[] = [
    'num' => (string)$idx,
    'des' => $des,
    'nb'  => isset($row['nb']) ? (string)$row['nb'] : (string)$qty,
    'obs' => isset($row['obs']) ? (string)$row['obs'] : 'pour archive',
  ];
}
/* Ensure at least 3 rows (editable blanks) */
while (count($rows) < 3) {
  $rows[] = ['num'=>'', 'des'=>'', 'nb'=>'', 'obs'=>''];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Bordereau <?= e($numeroB) ?></title>
<style>
  *{ box-sizing:border-box }
  html,body{ background:#f6f6f6; margin:0; font-family:Arial,Helvetica,sans-serif; color:#111 }

  .sheet{
    width: calc(210mm - 24mm);
    height: calc(297mm - 24mm);
    margin: 12mm auto;
    background:#fff;
    border:1px solid #cfcfcf;
    padding:10mm;
    overflow:hidden;
    position:relative;
  }
  .sheet-body{ position:relative; display:flex; flex-direction:column; height:100% }
  .page-num{ font-size:3mm; position:absolute; right:10mm; bottom:4mm; color:#222 }

  @page { size:A4; margin:12mm }
  @media print{
    html,body{ background:#fff }
    .sheet{ margin:0; border:none; page-break-after:always; }
    .sheet:last-child{ page-break-after:auto; }
    .print-btn{ display:none !important }
  }

  /* ===== Header ===== */
  .header-card{
    display:grid;
    grid-template-columns:46mm 1fr 62mm;
    align-items:stretch;
    border:1px solid #000;
    background:#fff;
  }
  .header-cell.logo{
    padding:0;
    border-right:1px solid #000;
    align-self:stretch;
    display:flex;
  }
  .header-cell.logo .pad{
    padding:4mm;
    height:100%;
    width:100%;
    display:flex; align-items:center; justify-content:center;
  }
  .header-cell.title{ padding:4mm; display:flex; align-items:center; }
  .doc-title{ margin:0; text-transform:uppercase; font-weight:800; font-size:5mm; line-height:1.1; letter-spacing:.2mm }

  .header-cell.meta{
    padding:0;
    border-left:1px solid #000;
    align-self:stretch;
    display:flex;
  }
  .header-cell.meta .meta-pad{
    padding:3mm;
    height:100%;
    width:100%;
  }
  .header-cell.logo img{ display:block; height:16mm; width:auto }

  .meta-card{ height:100%; display:grid; gap:2mm; background:#fff; font-size:3.2mm }
  .meta-table{ width:100%; border-collapse:separate; border-spacing:0; font-size:3.2mm }
  .meta-table th, .meta-table td{ padding:2.2mm 3mm; vertical-align:top; }
  .meta-table th{ text-align:left; font-weight:700; white-space:nowrap }
  .meta-table td{ text-align:left; white-space:nowrap } /* keep values on one line */
  .meta-table td.nowrap{ white-space:nowrap }

  /* Info */
  .content{ margin-top:5mm }
  .info-card{ border:1px solid #000; padding:4mm; background:#fff }
  .info-grid{ display:grid; grid-template-columns:1fr 1fr; gap:4mm 10mm; font-size:3.4mm }
  .info-grid .attn{ grid-column:1 / -1; border-top:1px solid #000; padding-top:3mm; margin-top:1mm }

  .section{ margin-top:5mm; font-size:3.4mm }

  /* Table */
  table.items{ width:100%; border-collapse:collapse; table-layout:fixed; background:#fff; border:1px solid #000; }
  .items thead th{
    background:#f4f4f4; text-transform:uppercase; font-weight:700; font-size:3.2mm;
    border:1px solid #000; padding:2.2mm; line-height:1.2; text-align:center
  }
  .items td{ border:1px solid #000; padding:2.2mm; font-size:3.2mm; line-height:1.35; vertical-align:top }
  .col-num{ width:16mm; text-align:center }
  .col-nb { width:40mm; text-align:center }
  .col-obs{ width:48mm; text-align:center }

  .push{ flex:1 }
  .bottom-block{ margin-top:8mm }
  .facture-info{ border:1px solid #000; background:#fff; padding:3mm; margin-bottom:6mm; font-size:3.4mm }
  .signs{ display:grid; grid-template-columns:1fr 1fr; gap:4mm; }
  .sign{ border:1px solid #000; background:#fff; min-height:36mm; padding:4mm }
  .sign h4{ margin:0 0 2mm; text-align:center; text-transform:uppercase; font-size:3.4mm }
  .sign .hint{ text-align:center; font-size:3mm; color:#333; margin-bottom:2mm }
  .sign .box{ border:1px dashed #000; min-height:22mm }

  .company{ margin-top:8mm; font-size:3mm; line-height:1.45; text-align:center }

  .print-btn{ margin:10px auto; width:186mm; display:flex; gap:8px; justify-content:flex-end }
  .print-btn button{ padding:6px 10px; border:1px solid #aaa; background:#fff; cursor:pointer }

  [contenteditable="true"]{ outline:0; cursor:text }
  [contenteditable="true"]:focus{ outline:1px dotted #777; outline-offset:2px }
  @media print{ [contenteditable="true"]:focus{ outline:0 } }
</style>
</head>
<body>

<!-- Hidden “source” rows (will be updated as you type) -->
<table style="display:none"><tbody id="rows-src">
<?php foreach ($rows as $r): ?>
  <tr data-idx>
    <td class="col-num" contenteditable="true"><?= $r['num'] ?></td>
    <td class="celldes"  contenteditable="true"><?= $r['des'] ?></td>
    <td class="col-nb"   contenteditable="true"><?= e($r['nb']) ?></td>
    <td class="col-obs"  contenteditable="true"><?= e($r['obs']) ?></td>
  </tr>
<?php endforeach; ?>
</tbody></table>

<!-- Print button only -->
<div class="print-btn"><button onclick="window.print()">Imprimer</button></div>

<!-- Where paginated pages will be created -->
<div id="print-root"></div>

<!-- Templates (header/intro/table/bottom) -->
<template id="header-tpl">
  <div class="header-card">
    <div class="header-cell logo">
      <div class="pad">
        <img src="../../assets/images/logo.png" alt="TPC">
      </div>
    </div>
    <div class="header-cell title">
      <h1 class="doc-title" contenteditable="true">BORDEREAUX <?= e($numeroB) ?></h1>
    </div>
    <div class="header-cell meta">
      <div class="meta-pad">
        <div class="meta-card">
          <table class="meta-table">
            <tr><th>Code :</th><td contenteditable="true"><?= e($codeDoc) ?></td></tr>
            <tr><th>I.R :</th><td contenteditable="true"><?= e($irDoc) ?></td></tr>
            <!-- Keep D.E editable and independent -->
            <tr><th>D.E :</th><td class="nowrap de-field" contenteditable="true">21/10/2025</td></tr>
            <tr><th>Page :</th><td><span class="page-text" contenteditable="true"></span></td></tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<template id="intro-tpl">
  <div class="content">
    <div class="info-card">
      <div class="info-grid">
        <div><strong>Réf :</strong> <span contenteditable="true"><?= e($ref) ?></span></div>
        <!-- CHANGED: Date comes from DB and is NOT editable -->
        <div><strong>Date :</strong> <span id="dateField"><?= e($displayDate) ?></span></div>
        <div class="attn"><strong>À l’attention de :</strong> <span contenteditable="true"><?= e($attn) ?></span></div>
      </div>
    </div>
    <div class="section" contenteditable="true">Veuillez trouver ci-jointes les pièces suivantes :</div>
  </div>
</template>

<template id="table-tpl">
  <table class="items">
    <thead>
      <tr>
        <th class="col-num" contenteditable="true">N°</th>
        <th contenteditable="true">Désignation</th>
        <th class="col-nb" contenteditable="true">Nombres exemplaires</th>
        <th class="col-obs" contenteditable="true">Observations</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</template>

<template id="bottom-tpl">
  <div class="bottom-block">
    <?php if (!$facturePlacedInTable && $numFact !== ''): ?>
    <div class="facture-info"><strong>Facture N°:</strong> <span><?= e($numFact) ?></span></div>
    <?php endif; ?>
    <div class="signs">
      <div class="sign">
        <h4 contenteditable="true">ÉMETTEUR</h4>
        <div class="hint" contenteditable="true">(Nom / Visa &amp; cachet)</div>
        <div class="box" contenteditable="true"></div>
      </div>
      <div class="sign">
        <h4 contenteditable="true">DESTINATAIRE</h4>
        <div class="hint" contenteditable="true">(Nom / Visa &amp; cachet)</div>
        <div class="box" contenteditable="true"></div>
      </div>
    </div>
    <div class="company" contenteditable="true">
      <div><b>T.P.C : TUNISIA POLYCONTROLS</b></div>
      <div>S.A.R.L. au capital de 20.000 DT – Code T.V.A : 1426729 H/A/M/000 – R.C. : B 2621285205</div>
      <div>BUREAU : 5 Rue Benghazi – Bureau 4.5 – 4<sup>ème</sup> Étage – 1002 Tunis</div>
      <div>Tél : 36 131 731 – GSM : 24 131 544 – Email : tunisia.polycontrols.tpc@gmail.com</div>
    </div>
  </div>
</template>

<script>
(function(){
  const root    = document.getElementById('print-root');
  const srcBody = document.querySelector('#rows-src');

  const headerTpl = document.getElementById('header-tpl');
  const introTpl  = document.getElementById('intro-tpl');
  const tableTpl  = document.getElementById('table-tpl');
  const bottomTpl = document.getElementById('bottom-tpl');

  function newSheet(){
    const sheet = document.createElement('div');
    sheet.className = 'sheet';
    const body  = document.createElement('div');
    body.className = 'sheet-body';
    sheet.appendChild(body);
    return {sheet, body};
  }
  function addHeader(target){
    const h = headerTpl.content.firstElementChild.cloneNode(true);
    target.appendChild(h);
  }
  function addIntro(target){ target.appendChild(introTpl.content.cloneNode(true)); }
  function addTable(target){
    const tbl = tableTpl.content.firstElementChild.cloneNode(true);
    target.appendChild(tbl);
    return tbl.querySelector('tbody');
  }
  function addBottom(target){
    const spacer = document.createElement('div'); spacer.className = 'push';
    target.appendChild(spacer);
    target.appendChild(bottomTpl.content.cloneNode(true));
  }

  function buildPages(){
    root.innerHTML = '';

    const rows = Array.from(srcBody.children).map((tr, i) => {
      const clone = tr.cloneNode(true);
      clone.dataset.idx = i;
      return clone;
    });

    const sheets = [];
    let cur = newSheet();
    addHeader(cur.body);
    addIntro(cur.body);
    let tbody = addTable(cur.body);
    root.appendChild(cur.sheet);
    sheets.push(cur);

    for (let i = 0; i < rows.length; i++){
      const row = rows[i].cloneNode(true);
      row.dataset.idx = rows[i].dataset.idx;
      tbody.appendChild(row);

      if (cur.sheet.scrollHeight > cur.sheet.clientHeight){
        tbody.removeChild(row);
        cur = newSheet();
        addHeader(cur.body);
        tbody = addTable(cur.body);
        root.appendChild(cur.sheet);
        sheets.push(cur);
        tbody.appendChild(row);
      }
    }

    let last = sheets[sheets.length - 1];
    addBottom(last.body);
    if (last.sheet.scrollHeight > last.sheet.clientHeight){
      last.body.removeChild(last.body.lastElementChild); // bottom
      last.body.removeChild(last.body.lastElementChild); // spacer
      const s = newSheet();
      addHeader(s.body);
      addBottom(s.body);
      root.appendChild(s.sheet);
      sheets.push(s);
    }

    const total = sheets.length;
    sheets.forEach((s, i) => {
      const pt = s.sheet.querySelector('.page-text');
      if (pt) pt.textContent = (i+1)+' / '+total;
      const pn = document.createElement('div');
      pn.className = 'page-num';
      pn.textContent = (i+1)+' / '+total;
      s.sheet.appendChild(pn);
    });
  }

  buildPages();

  // Keep edits in rows in sync with hidden source, then rebuild
  let debounce;
  root.addEventListener('input', (ev)=>{
    const row  = ev.target.closest('tr[data-idx]');
    if (!row) return;
    const idx = +row.dataset.idx;
    const srcRow = srcBody.children[idx];
    if (!srcRow) return;

    const renderedCells = row.children;
    for (let c=0; c<renderedCells.length; c++) {
      srcRow.children[c].innerHTML = renderedCells[c].innerHTML;
    }

    clearTimeout(debounce);
    debounce = setTimeout(buildPages, 300);
  });

  // Keep D.E single-line (unchanged behavior)
  document.addEventListener('keydown', function(e){
    const el = e.target;
    if (el && el.classList && el.classList.contains('de-field') && el.isContentEditable) {
      if (e.key === 'Enter') e.preventDefault();
    }
  });
  document.addEventListener('paste', function(e){
    const el = e.target;
    if (el && el.classList && el.classList.contains('de-field') && el.isContentEditable) {
      e.preventDefault();
      const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\s*\n+\s*/g,' ');
      document.execCommand('insertText', false, text);
    }
  });

})();
</script>
</body>
</html>

