<?php
// Safe helper (if you later pass data from DB)
if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// You can later populate these from DB if you want
$numOrdre      = $numOrdre      ?? '';  // e.g. 1410/2025
$dateMission   = $dateMission   ?? '';  // e.g. 14/10/2025
$codeDoc       = $codeDoc       ?? 'DAS-TEC-02';
$irDoc         = $irDoc         ?? '00';
$deDoc         = $deDoc         ?? '26/04/2024'; // D.E in header
$pageText      = $pageText      ?? '1 / 1';      // static for now (1 page)

// Client data (optional – all fields are contenteditable anyway)
$clientAttn    = $clientAttn    ?? '';
$clientRaison  = $clientRaison  ?? '';
$clientAdresse = $clientAdresse ?? '';
$clientGsm     = $clientGsm     ?? '';
$clientFax     = $clientFax     ?? '';
$clientEmail   = $clientEmail   ?? '';
$clientRefCmd  = $clientRefCmd  ?? '';

$inspecteur    = $inspecteur    ?? '';
$inspecteur2   = $inspecteur2   ?? '';
$objetMission  = $objetMission  ?? '';
$equipement    = $equipement    ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Ordre de mission <?= e($numOrdre) ?></title>
<style>
  *{ box-sizing:border-box }
  html,body{
    background:#f6f6f6;
    margin:0;
    font-family:Arial,Helvetica,sans-serif;
    color:#111;
  }

  .print-toolbar{
    width:210mm;
    max-width:100%;
    margin:8px auto 0;
    display:flex;
    justify-content:flex-end;
  }
  .print-toolbar button{
    padding:6px 10px;
    border:1px solid #aaa;
    background:#fff;
    cursor:pointer;
    border-radius:4px;
    font-size:13px;
  }

  .sheet{
    width:210mm;
    max-width:calc(100% - 20px);
    min-height:297mm;
    margin:8px auto 16px;
    background:#fff;
    border:1px solid #cfcfcf;
    padding:10mm;
    position:relative;
  }

  @page{ size:A4; margin:10mm }
  @media print{
    html,body{ background:#fff; }
    .print-toolbar{ display:none !important; }
    .sheet{
      margin:0;
      border:none;
      page-break-after:auto;
    }
  }

  /* ===== HEADER ===== */
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
    display:flex;
    align-items:stretch;
  }
  .header-cell.logo .pad{
    padding:4mm;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    width:100%;
  }
  .header-cell.logo img{
    display:block;
    height:16mm;
    width:auto;
    margin-bottom:2mm;
  }
  .header-cell.logo .tpc-name{
    font-size:3.4mm;
    font-weight:700;
    text-align:center;
    text-transform:uppercase;
  }

  .header-cell.title{
    padding:4mm;
    display:flex;
    justify-content:center;
    align-items:center;
    text-align:center;
  }
  .doc-title{
    margin:0;
    text-transform:uppercase;
    font-weight:800;
    font-size:5mm;
    line-height:1.2;
    letter-spacing:.2mm;
  }
  .doc-title .num{
    margin-left:2mm;
  }

  .header-cell.meta{
    padding:0;
    border-left:1px solid #000;
    display:flex;
    align-items:stretch;
  }
  .header-cell.meta .meta-pad{
    padding:3mm;
    width:100%;
    height:100%;
  }
  .meta-card{
    height:100%;
    display:grid;
    background:#fff;
    font-size:3.2mm;
  }
  .meta-table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
  }
  .meta-table th,
  .meta-table td{
    padding:1.6mm 2mm;
    vertical-align:top;
    text-align:left;
    white-space:nowrap;
  }
  .meta-table th{
    font-weight:700;
  }

  /* ===== Titres de sections ===== */
  .section-title{
    margin:8mm 0 3mm;
    text-align:center;
    font-weight:700;
    font-size:4mm;
    text-transform:uppercase;
    text-decoration:underline;
  }

  /* ===== PARTIE CLIENT ===== */
  .bloc{
    border:1px solid #000;
    background:#fff;
    font-size:3.4mm;
  }
  .bloc-table{
    width:100%;
    border-collapse:collapse;
  }
  .bloc-table td{
    border-top:1px solid #000;
    padding:2mm 3mm;
    vertical-align:middle;
  }
  .bloc-table tr:first-child td{
    border-top:none;
  }

  .bloc-table .two-cols{
    border-top:none;
  }
  .bloc-table .two-cols td{
    border-top:none;
    border-bottom:1px solid #000;
  }

  .bloc-table .three-cols-row td{
    border-bottom:1px solid #000;
  }

  .bloc-table .two-cols td + td,
  .bloc-table .three-cols-row td + td{
    border-left:1px solid #000;
  }

  .bloc-table .label{
    font-weight:700;
  }

  .bloc-table .q-line{
    border-bottom:1px solid #000;
  }

  /* ===== PARTIE INSPECTEUR ===== */
  .bloc-inspecteur{
    border:1px solid #000;
    background:#fff;
    font-size:3.4mm;
  }
  .bloc-inspecteur table{
    width:100%;
    border-collapse:collapse;
  }
  .bloc-inspecteur td{
    border-top:1px solid #000;
    padding:2mm 3mm;
    vertical-align:top;
  }
  .bloc-inspecteur tr:first-child td{
    border-top:none;
  }
  .indent{
    padding-left:6mm;
  }

  /* ===== SIGNATURES ===== */
  .signatures-title{
    margin:8mm 0 4mm;
    text-align:center;
    font-weight:700;
    font-size:4mm;
    text-transform:uppercase;
  }
  .signs{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:4mm;
    font-size:3.4mm;
  }
  .sign{
    border:1px solid #000;
    background:#fff;
    padding:4mm;
  }
  .sign h4{
    margin:0 0 2mm;
    text-align:center;
    text-transform:uppercase;
    font-size:3.4mm;
  }
  .sign .hint{
    text-align:center;
    font-size:3mm;
    margin-bottom:2mm;
  }
  .sign .box{
    border:1px dashed #000;
    min-height:22mm;
  }

  /* ===== FOOTER ===== */
  .footer{
    margin-top:10mm;
    font-size:3mm;
    text-align:center;
    line-height:1.4;
  }

  [contenteditable="true"]{
    outline:0;
    cursor:text;
  }
  [contenteditable="true"]:focus{
    outline:1px dotted #777;
    outline-offset:1px;
  }
  @media print{
    [contenteditable="true"]:focus{ outline:0; }
  }
</style>
</head>
<body>

<div class="print-toolbar">
  <button onclick="window.print()">Imprimer</button>
</div>

<div class="sheet">

  <!-- HEADER -->
  <div class="header-card">
    <!-- Colonne logo + TPC -->
    <div class="header-cell logo">
      <div class="pad">
        <img src="../../assets/images/logo.png" alt="TPC">
        <div class="tpc-name" contenteditable="true">TUNISIA POLYCONTROLS</div>
      </div>
    </div>

    <!-- Colonne titre -->
    <div class="header-cell title">
      <h1 class="doc-title">
        <span contenteditable="true">ORDRE DE MISSION</span>
        <span class="num" contenteditable="true"><?= e($numOrdre) ?></span>
      </h1>
    </div>

    <!-- Colonne méta -->
    <div class="header-cell meta">
      <div class="meta-pad">
        <div class="meta-card">
          <table class="meta-table">
            <tr>
              <th>Code :</th>
              <td contenteditable="true"><?= e($codeDoc) ?></td>
            </tr>
            <tr>
              <th>I.R :</th>
              <td contenteditable="true"><?= e($irDoc) ?></td>
            </tr>
            <tr>
              <th>D.E :</th>
              <td contenteditable="true"><?= e($deDoc) ?></td>
            </tr>
            <tr>
              <th>Page :</th>
              <td contenteditable="true"><?= e($pageText) ?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- PARTIE CLIENT -->
  <div class="section-title">Partie client</div>
  <div class="bloc">
    <table class="bloc-table">
      <!-- Ligne 1 : À l’attention / Date mission (2 colonnes) -->
      <tr class="two-cols">
        <td>
          <span class="label">À l’attention de :</span>
          <span contenteditable="true"><?= e($clientAttn) ?></span>
        </td>
        <td>
          <span class="label">Date mission :</span>
          <span contenteditable="true"><?= e($dateMission) ?></span>
        </td>
      </tr>

      <!-- Raison sociale -->
      <tr>
        <td colspan="2">
          <span class="label">Raison sociale du client :</span>
          <span contenteditable="true"><?= e($clientRaison) ?></span>
        </td>
      </tr>

      <!-- Adresse -->
      <tr>
        <td colspan="2">
          <span class="label">Adresse du client :</span>
          <span contenteditable="true"><?= e($clientAdresse) ?></span>
        </td>
      </tr>

      <!-- GSM / Fax / Email (3 colonnes) -->
      <tr class="three-cols-row">
        <td style="width:33%;">
          <span class="label">GSM :</span>
          <span contenteditable="true"><?= e($clientGsm) ?></span>
        </td>
        <td style="width:33%;">
          <span class="label">Fax :</span>
          <span contenteditable="true"><?= e($clientFax) ?></span>
        </td>
        <td style="width:34%;">
          <span class="label">Email :</span>
          <span contenteditable="true"><?= e($clientEmail) ?></span>
        </td>
      </tr>

      <!-- Question relation -->
      <tr>
        <td colspan="2" class="q-line">
          <span class="label">Avez-vous une relation familiale, amicale ou autre avec le client ?</span>
        </td>
      </tr>

      <!-- Oui / Non -->
      <tr>
        <td colspan="2">
          <span>Oui [ ]</span>
          <span style="margin-left:15mm;">Non [ ]</span>
        </td>
      </tr>

      <!-- Réf commande / contrat -->
      <tr>
        <td colspan="2">
          <span class="label">Réf Commande et/ou Contrat :</span>
          <span contenteditable="true"><?= e($clientRefCmd) ?></span>
        </td>
      </tr>
    </table>
  </div>

  <!-- PARTIE INSPECTEUR -->
  <div class="section-title">Partie inspecteur</div>
  <div class="bloc-inspecteur">
    <table>
      <tr>
        <td>
          <span class="label">Nom &amp; Prénom de l’inspecteur :</span>
          <span contenteditable="true"><?= e($inspecteur) ?></span>
        </td>
      </tr>
      <tr>
        <td>
          <span class="label">Inspecteur(s) accompagné(s) :</span>
          <span contenteditable="true"><?= e($inspecteur2) ?></span>
        </td>
      </tr>
      <tr>
        <td>
          <span class="label">Objet de la mission :</span>
          <div class="indent" contenteditable="true"><?= e($objetMission) ?></div>
        </td>
      </tr>
      <tr>
        <td>
          <span class="label">Équipement de MESURE :</span>
          <div class="indent" contenteditable="true"><?= e($equipement) ?></div>
        </td>
      </tr>
    </table>
  </div>

  <!-- SIGNATURES -->
  <div class="signatures-title">Signatures</div>
  <div class="signs">
    <div class="sign">
      <h4 contenteditable="true">Signature de l’inspecteur</h4>
      <div class="hint" contenteditable="true">(Nom / Visa &amp; cachet)</div>
      <div class="box" contenteditable="true"></div>
    </div>
    <div class="sign">
      <h4 contenteditable="true">Signature du Directeur Technique</h4>
      <div class="hint" contenteditable="true">(Nom / Visa &amp; cachet)</div>
      <div class="box" contenteditable="true"></div>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="footer" contenteditable="true">
    <div><b>T.P.C — TUNISIA POLYCONTROLS</b></div>
    <div>Siège Social : 5 rue Benghazi — Bureau 4.5 — 4<sup>ème</sup> étage — 1002 Tunis</div>
    <div>Tél : 36 131 731 — GSM : 24 131 544 — Email : tunisia.polycontrols.tpc@gmail.com</div>
  </div>

</div>
</body>
</html>
