<?php
// Safe defaults so the template renders even if some vars are missing
$infosFacture        = $infosFacture        ?? [];
$ProjetsFacture      = $ProjetsFacture      ?? [];
$bonCommandeFacture  = $bonCommandeFacture  ?? null;
$adressesClient      = $adressesClient      ?? [];
$nbAdresse           = $nbAdresse           ?? 0;
$date                = $date                ?? '';
$isExonore           = $isExonore           ?? false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Facture <?= htmlspecialchars($infosFacture['num_fact'] ?? '') ?></title>

<style>
  /* ===== Base page frame (optional) ===== */
  html,body{background:#f6f6f6}
  body{font-family: system-ui,-apple-system,"Segoe UI",Roboto,Arial,Helvetica,sans-serif; color:#111; margin:0}
  .page{max-width: 960px; margin: 12px auto; background:#fff; padding:18px; border:1px solid #cfcfcf}

  @media print{
    html,body{background:#fff}
    .page{border:none; padding:0}
    @page{ margin: 12mm }
    .screen-fallback{display:none !important}
    .page-num::after{content: counter(page)}
    .page-count::after{content: counter(pages)}
  }
  @media screen{ .print-only{display:none !important} }

  /* ===== TOP HEADER (Logo | line | title | code box) ===== */
  .header-card{
    border:1px solid #000;
    background:#fff;
    padding:10px 12px;
    display:grid;
    grid-template-columns: 200px 1px 1fr 240px; /* logo | line | title | code */
    align-items:center;
    gap:12px;
  }
  .logo-cell img{ display:block }

  /* vertical divider between logo and the rest */
  .vline{ width:1px; height:100%; background:#000; }

  /* “Facture” + number (same style, inline, no box) */
  .title-cell{ text-align:center }
  .facture-title{
    margin:0;
    text-transform:uppercase;
    font-weight:800;
    font-size:22px;
    line-height:1.1;
    letter-spacing:0.3px;
    display:inline-flex;
    align-items:baseline;
    gap:8px;           /* small space between word and number */
  }
  .facture-no{
    font-weight:800;   /* match */
    font-size:22px;    /* match */
  }

  /* Right meta card (Code / I.R / D.E / Page) */
  .meta-card{
    border:1px solid #000;
    padding:8px 10px;
    font-size:12px;
    background:#fff;
  }
  .meta-row{ display:grid; grid-template-columns: 88px 1fr; gap:6px; margin:4px 0; line-height:1.2 }
  .meta-label{ font-weight:700 }

  /* ===== Optional layout helpers for your body ===== */
  .content{ margin-top:14px }
</style>
</head>
<body>
<div class="page">

  <!-- ===== TOP HEADER ONLY (updated) ===== -->
  <div class="header-card">
    <!-- Logo -->
    <div class="logo-cell">
      <img src="../../assets/images/logo.png" alt="TPC" height="62">
    </div>

    <!-- Vertical separator -->
    <div class="vline" aria-hidden="true"></div>

    <!-- Title + number (no box; same styling) -->
    <div class="title-cell">
      <h1 class="facture-title">
        Facture <span class="facture-no"><?= htmlspecialchars($infosFacture['num_fact'] ?? '') ?></span>
      </h1>
    </div>

    <!-- Code / I.R / D.E / Page -->
    <div class="meta-card">
      <div class="meta-row"><div class="meta-label">Code :</div><div>DAS-ACH-04</div></div>
      <div class="meta-row"><div class="meta-label">I.R :</div><div>00</div></div>
      <div class="meta-row"><div class="meta-label">D.E :</div><div>xx/xx/xx25</div></div>
      <div class="meta-row">
        <div class="meta-label">Page :</div>
        <div>
          <span class="print-only"><span class="page-num"></span>/<span class="page-count"></span></span>
          <span class="screen-fallback">1/1</span>
        </div>
      </div>
    </div>
  </div>
  <!-- ===== /TOP HEADER ===== -->

  <!-- ===== Your existing body goes below =====
       Paste your client box (on the left), items table, totals, and footer here.
       Nothing else about the top header needs changing.
  -->
  <div class="content">
    <!-- Example placeholder; remove and paste your existing content -->
    <!--
    <?php // include 'your-existing-invoice-body.php'; ?>
    -->
  </div>

</div>
</body>
</html>
