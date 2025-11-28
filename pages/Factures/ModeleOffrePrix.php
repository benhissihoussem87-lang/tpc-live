<?php
// ------- Safe defaults so the template renders without notices -------
$infosOffre            = $infosOffre            ?? [];
$ProjetsOffre          = $ProjetsOffre          ?? [];
$Projets               = $Projets               ?? [];
$adressesClient        = $adressesClient        ?? [];
$nbAdresse             = $nbAdresse             ?? 0;
$verifForfitaireOffre  = $verifForfitaireOffre  ?? 'Nonforfitaire';
$date                  = $date                  ?? '';
$montant_lettres       = $montant_lettres       ?? '';

// If your project defines asLetters elsewhere, remove this stub.
if (!function_exists('asLetters')) {
  function asLetters($n){ return (string)$n; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Offre <?= htmlspecialchars($infosOffre['num_offre'] ?? '') ?></title>

<style>
  /* ===== Base page frame (SAME STYLE AS FACTURE) ===== */
  html,body{background:#f6f6f6}
  body{
    font-family: system-ui,-apple-system,"Segoe UI",Roboto,Arial,Helvetica,sans-serif;
    color:#111; margin:0
  }
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
  .vline{ width:1px; height:100%; background:#000; } /* vertical divider */
  .title-cell{ text-align:center }
  .facture-title{
    margin:0;
    text-transform:uppercase;
    font-weight:800;
    font-size:22px;
    line-height:1.1;
    letter-spacing:0.3px;
    display:inline-flex; align-items:baseline; gap:8px;
  }
  .facture-no{ font-weight:800; font-size:22px }

  .meta-card{
    border:1px solid #000;
    padding:8px 10px;
    font-size:12px;
    background:#fff;
  }
  .meta-row{ display:grid; grid-template-columns: 88px 1fr; gap:6px; margin:4px 0; line-height:1.2 }
  .meta-label{ font-weight:700 }

  /* ===== Client box (left side under header) ===== */
  .content{ margin-top:14px }
  .data-grid{ display:grid; grid-template-columns: 1fr; gap:12px }
  .client-card{
    border:1px solid #000; padding:10px 12px; background:#fff; width: 420px;
  }
  .client-card ol{ margin:0; padding-left:18px }
  .client-card li{ margin:4px 0; font-size:13px }
  .client-card input{ border:none; border-bottom:1px dashed #777; font:inherit; width:auto }

  /* ===== Items table ===== */
  .section{ margin-top:10px }
  .invoice-table{
    width:100%; border-collapse:collapse; table-layout:fixed; background:#fff; border:1px solid #000
  }
  .invoice-table thead th{
    background:#f4f4f4; text-transform:uppercase; font-weight:700; font-size:12px;
    border:1px solid #000; padding:8px 8px; line-height:1.2
  }
  .col-desc{width:70%}
  .col-qte, .col-puh, .col-pth{width:10%}
  .invoice-table td{
    border:1px solid #000; padding:8px 10px; font-size:13px; line-height:1.35;
    vertical-align:top; word-break:break-word; hyphens:auto;
  }
  .desc{ text-transform:uppercase; letter-spacing:.2px }
  .t-center{text-align:center}
  .t-right{text-align:right}

  /* ===== Totals ===== */
  .totals-wrap{ display:flex; justify-content:flex-end; margin-top:12px }
  .totals{
    display:grid; grid-template-columns: 1fr 160px; gap:8px; border:1px solid #000; background:#fff
  }
  .totals .labels, .totals .values{ padding:8px }
  .totals .labels div, .totals .values div{ margin:4px 0; font-size:12px }
  .totals .values div{ text-align:right }

  /* ===== Footer ===== */
  .foot-grid{
    display:grid; grid-template-columns: 62% 36%; gap:2%; margin-top:12px; font-size:11px
  }
  .foot-card{ background:#fff }
  .foot-card input{ border:none; border-bottom:1px dashed #777; font:inherit }
</style>
</head>
<body>
<div class="page">

  <!-- ===== HEADER ===== -->
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
        Offre de prix <span class="facture-no"><?= htmlspecialchars($infosOffre['num_offre'] ?? '') ?></span>
      </h1>
    </div>

    <!-- Code / I.R / D.E / Page -->
    <div class="meta-card">
      <div class="meta-row"><div class="meta-label">Code :</div><div><?= htmlspecialchars($infosOffre['code'] ?? 'DAS-ACH-03') ?></div></div>
      <div class="meta-row"><div class="meta-label">I.R :</div><div><?= htmlspecialchars($infosOffre['ir'] ?? '00') ?></div></div>
      <div class="meta-row"><div class="meta-label">D.E :</div><div><?= htmlspecialchars('21/10/2025') ?></div></div>
      <div class="meta-row">
        <div class="meta-label">Page :</div>
        <div>
          <span class="print-only"><span class="page-num"></span>/<span class="page-count"></span></span>
          <span class="screen-fallback">1/1</span>
        </div>
      </div>
    </div>
  </div>
  <!-- ===== /HEADER ===== -->

  <!-- ===== CLIENT BOX (left) ===== -->
  <div class="content">
    <div class="data-grid">
      <div class="client-card">
        <ol>
          <li>Date de l’offre :  <b><?= htmlspecialchars($date) ?></b></li>
          <li>Client : <b><?= htmlspecialchars($infosOffre['nom_client'] ?? '') ?></b></li>
          <li>Adresse : <b><?= htmlspecialchars($infosOffre['adresse'] ?? '') ?></b></li>
          <li>Matricule Fiscale : <b><?= htmlspecialchars($infosOffre['mat_fisc'] ?? '') ?></b></li>
          <!-- Offre: pas d'Exonoration ni Bon de commande -->
        </ol>
      </div>
    </div>
  </div>
  <!-- ===== /CLIENT BOX ===== -->

  <!-- ===== ITEMS TABLE ===== -->
  <div class="section">
    <table class="invoice-table">
      <thead>
      <tr>
        <th class="col-desc">Désignations</th>
        <th class="col-qte t-center">Qté</th>
        <th class="col-puh t-center">P.U. H.T</th>
        <th class="col-pth t-center">Total H.T</th>
      </tr>
      </thead>
      <tbody>
      <?php
        $totalHT = 0.0;
        // Prefer offres dataset; fallback to projets
        $rows = !empty($ProjetsOffre) ? $ProjetsOffre : (!empty($Projets) ? $Projets : []);

        // Normalize addresses list
        $addressLabels = [];
        foreach ($adressesClient as $a) {
          if (is_array($a)) {
            $label = $a['adresseClient'] ?? (isset($a[0]) ? $a[0] : '');
            if ($label !== '') { $addressLabels[] = $label; }
          } elseif (!empty($a)) {
            $addressLabels[] = (string)$a;
          }
        }
        $hasAddresses = count($addressLabels) > 0;

        $printedAny = false;
        if (!$hasAddresses) {
          foreach ($rows as $projet) {
            $qte = $projet['qte'] ?? '';
            $pu  = ($qte !== 'ENS' && $qte!=='') ? ($projet['prix_unit_htv'] ?? '') : ($projet['prixForfitaire'] ?? '');
            $pth = 0.0;
            if ($verifForfitaireOffre === 'forfitaire' && ($projet['prixForfitaire'] ?? '') !== '') {
              $pth = (float)$projet['prixForfitaire'];
            } else {
              if ($qte !== 'ENS' && $qte!=='') {
                $pth = (float)($projet['qte'] ?? 0) * (float)($projet['prix_unit_htv'] ?? 0);
              } elseif (!empty($projet['prixForfitaire'])) {
                $pth = (float)$projet['prixForfitaire'];
              }
            }
            $totalHT += $pth;
            $desc = $projet['classement'] ?? '';
            echo '<tr>';
              echo '<td class="desc">'.htmlspecialchars($desc).'</td>';
              echo '<td class="t-center">'.htmlspecialchars($qte).'</td>';
              echo '<td class="t-center">'.htmlspecialchars($pu).'</td>';
              echo '<td class="t-right">'.number_format($pth,3,'.','').'</td>';
            echo '</tr>';
            $printedAny = true;
          }
        } else {
          foreach ($addressLabels as $label) {
            echo '<tr><td class="desc" colspan="4" style="font-weight:700;text-decoration:underline">'.htmlspecialchars($label).'</td></tr>';
            foreach ($rows as $projet) {
              if (($projet['adresseClient'] ?? '') === $label) {
                $qte  = $projet['qte'] ?? '';
                $pu   = ($qte !== 'ENS' && $qte!=='') ? ($projet['prix_unit_htv'] ?? '') : ($projet['prixForfitaire'] ?? '');
                if ($verifForfitaireOffre === 'forfitaire' && ($projet['prixForfitaire'] ?? '') !== '') {
                  $pth = (float)$projet['prixForfitaire'];
                } else {
                  $pth = (float)($projet['qte'] ?? 0) * (float)($projet['prix_unit_htv'] ?? 0);
                }
                $totalHT += $pth;
                $desc = $projet['classement'] ?? '';
                echo '<tr>';
                  echo '<td class="desc">'.htmlspecialchars($desc).'</td>';
                  echo '<td class="t-center">'.htmlspecialchars($qte).'</td>';
                  echo '<td class="t-center">'.htmlspecialchars($pu).'</td>';
                  echo '<td class="t-right">'.number_format($pth,3,'.','').'</td>';
                echo '</tr>';
                $printedAny = true;
              }
            }
          }
        }

        // Fallback: if no rows printed, list all rows
        if (!$printedAny) {
          foreach ($rows as $projet) {
            $qte = $projet['qte'] ?? '';
            $pu  = ($qte !== 'ENS' && $qte!=='') ? ($projet['prix_unit_htv'] ?? '') : ($projet['prixForfitaire'] ?? '');
            $pth = 0.0;
            if ($verifForfitaireOffre === 'forfitaire' && ($projet['prixForfitaire'] ?? '') !== '') {
              $pth = (float)$projet['prixForfitaire'];
            } else {
              if ($qte !== 'ENS' && $qte!=='') {
                $pth = (float)($projet['qte'] ?? 0) * (float)($projet['prix_unit_htv'] ?? 0);
              } elseif (!empty($projet['prixForfitaire'])) {
                $pth = (float)$projet['prixForfitaire'];
              }
            }
            $totalHT += $pth;
            $desc = $projet['classement'] ?? '';
            echo '<tr>';
              echo '<td class="desc">'.htmlspecialchars($desc).'</td>';
              echo '<td class="t-center">'.htmlspecialchars($qte).'</td>';
              echo '<td class="t-center">'.htmlspecialchars($pu).'</td>';
              echo '<td class="t-right">'.number_format($pth,3,'.','').'</td>';
            echo '</tr>';
          }
        }
      ?></tbody>

  </table>
  </div>
  <!-- ===== /ITEMS TABLE ===== -->

  <!-- ===== TOTALS (Offre: align with Facture 6-line box) ===== -->
  <?php
    $tva      = round($totalHT * 0.19, 3);   // Offre: 19% standard
    $ttc      = round($totalHT + $tva, 3);
    $timbre   = 1.000;                       // Timbre fixe
    $txt_sum  = $montant_lettres ?: asLetters($ttc + $timbre);
  ?>
  <div class="totals-wrap">
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
        <div><?= number_format($totalHT,3,'.','') ?></div>
        <div>19%</div>
        <div><?= number_format($tva,3,'.','') ?></div>
        <div><?= number_format($ttc,3,'.','') ?></div>
        <div><?= number_format($timbre,3,'.','') ?></div>
        <div><?= number_format($ttc + $timbre,3,'.','') ?></div>
      </div>
    </div>
  </div>

  <!-- ===== Amount in letters (same style with input underline) ===== -->
  <div class="section" style="margin-top:8px; font-size:13px">
    Arrêtée la présente offre à la somme de :
    <input type="text" style="width:100%; border:none; border-bottom:1px dashed #777; text-transform:uppercase"
           value="<?= htmlspecialchars($txt_sum) ?>">
  </div>

  <!-- ===== FOOTER (same style; wording adapted to "offre") ===== -->
  <div class="foot-grid">
    <div class="foot-card">
      <p>
        Pour toute question concernant cette <b>offre</b>, veuillez nous contacter<br>
        par e-mail: <b>tunisia.polycontrols.tpc@gmail.com</b>.
      </p>
      <p style="margin-top:-2px">
        Veuillez rédiger les virements au nom de <b>T.P.C</b><br>
        sur le <b>RIB N° <input type="text" value="14 305 305 1017 00061 8 08"></b>
      </p>
      <p style="text-align:center;font-size:11px">MERCI DE VOTRE CONFIANCE &mdash; LA DIRECTION &mdash; TPC</p>
    </div>

    <div class="foot-card">
      <p>
        <b>TPC : TUNISIA POLYCONTROLS</b>.<br>
        S.A.R.L au capital de 20.000 DT -Code TVA: <br>
        1426729 H/A/M/000-R.C: B 2621285205
        <br>E-mail: <b>tunisia.polycontrols.tpc@gmail.com</b>.
      </p>
      <p>
        <b>Siège Social : </b> 5 rue Benghazi-bureau 4-5, 4<sup>ème</sup> étage -1002 Tunis<br>
        <b>Tél :36 131 731</b> &nbsp; <b>GSM :24 131 544</b>
      </p>
    </div>
  </div>

</div>
</body>
</html>
