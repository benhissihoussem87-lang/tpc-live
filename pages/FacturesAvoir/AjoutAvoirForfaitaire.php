<?php
include_once __DIR__.'/../../class/Avoir.class.php';
include_once __DIR__.'/../../class/Factures.class.php';

$factures = $facture->AfficherFactures();
$errors = [];

// Defaults
$defaultNumAvoir = $avoir->nextNumber();
$defaultDate     = date('Y-m-d');
$selectedFacture = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSaveAvoirForfaitaire'])) {
    $num_fact    = trim($_POST['facture_selected'] ?? '');
    $num_avoir   = trim($_POST['num_avoir'] ?? '');
    $date_avoir  = trim($_POST['date_avoir'] ?? '');
    $pourcentage = trim($_POST['pourcentage'] ?? '');
    $montant_ht  = trim($_POST['montant_ht'] ?? '');
    $tva_pct_in  = trim($_POST['tva_pct'] ?? '19');

    if ($num_fact === '')   { $errors[] = 'Facture requise'; }
    if ($date_avoir === '') { $errors[] = 'Date requise'; }
    if ($montant_ht === '' || !is_numeric($montant_ht) || (float)$montant_ht <= 0) {
        $errors[] = 'Montant H.T valide requis';
    }

    $montant_ht_val = (float)$montant_ht;

    // Fetch facture + client info
    if ($num_fact !== '') {
        $selectedFacture = $facture->detailFacture($num_fact);
        if (!$selectedFacture) {
            $errors[] = "Facture introuvable.";
        }
    }

    if (empty($errors) && $selectedFacture) {
        $id_client = (int)($selectedFacture['client'] ?? 0);
        if ($id_client <= 0) {
            $errors[] = 'Client introuvable pour cette facture.';
        } else {
            if ($num_avoir === '') {
                $num_avoir = $avoir->nextNumber();
            }

            // Totals: TVA (%) configurable on the form
            $total_ht  = $montant_ht_val;
            $tva_rate  = is_numeric($tva_pct_in) ? ((float)$tva_pct_in / 100.0) : 0.19;
            $total_tva = round($montant_ht_val * $tva_rate, 3);
            $total_ttc = round($total_ht + $total_tva, 3);

            $mat_fisc = $selectedFacture['matriculeFiscale'] ?? null;
            $adresse  = $selectedFacture['adresse'] ?? '';

            // Single line: "totale avoir - {pourcentage}%"
            $label = 'totale avoir';
            if ($pourcentage !== '') {
                $label .= ' - '.$pourcentage.'%';
            }

            $line = [
                'projet'         => null,
                'prix_unit_htv'  => $montant_ht_val,
                'qte'            => 1,
                'tva'            => is_numeric($tva_pct_in) ? (float)$tva_pct_in : 19,
                'remise'         => '',
                'prixForfitaire' => $montant_ht_val,
                'prixTTC'        => $total_ttc,
                'adresseClient'  => $adresse,
                'projet_label'   => $label,
            ];

            $ok = $avoir->create([
                'num_avoir'            => $num_avoir,
                'num_fact'             => $num_fact,
                'num_facture_nouveux'  => null,
                'id_client'            => $id_client,
                'date_avoir'           => $date_avoir,
                'total_ht'             => $total_ht,
                'total_tva'            => $total_tva,
                'total_ttc'            => $total_ttc,
                'type_avoir'           => 'forfaitaire',
                'pourcentage'          => $pourcentage !== '' ? (float)$pourcentage : null,
                'matriculeFiscale'     => $mat_fisc,
                'lines'                => [$line],
            ]);

            if ($ok) {
                echo "<script>document.location.href='main.php?Avoir'</script>";
                exit;
            } else {
                $errors[] = "Erreur d'enregistrement";
            }
        }
    }

    $defaultNumAvoir = $num_avoir !== '' ? $num_avoir : $defaultNumAvoir;
    $defaultDate     = $date_avoir !== '' ? $date_avoir : $defaultDate;
} elseif (isset($_POST['facture_selected'])) {
    // keep selection on validation error
    $selectedFacture = $facture->detailFacture(trim($_POST['facture_selected']));
}

?>

<div class="card shadow mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h6 class="m-0 font-weight-bold text-primary">Ajouter Avoir Forfaitaire</h6>
      <small class="text-muted">Montant unique avec pourcentage</small>
    </div>
    <div>
      <a href="?Avoir" class="btn btn-sm btn-outline-danger ml-2">Fermer</a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!empty($errors)) { echo '<div class="alert alert-danger">'.implode('<br>', array_map('htmlspecialchars', $errors)).'</div>'; } ?>

    <form method="post">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label>Choisir une facture</label>
          <input type="text" class="form-control mb-2" id="facture_filter_ff" placeholder="Rechercher facture..." />
          <select class="form-control" name="facture_selected" id="facture_select_ff" required>
            <option value="">-- sélectionner --</option>
            <?php foreach ($factures as $f) {
              $val = htmlspecialchars($f['num_fact']);
              $sel = ($selectedFacture && $selectedFacture['num_fact'] === $f['num_fact']) ? 'selected' : '';
              ?>
              <option value="<?= $val ?>"
                      <?= $sel ?>
                      data-search="<?= htmlspecialchars($f['num_fact'].' '.($f['nom_client'] ?? '').' '.($f['date'] ?? '')) ?>">
                <?= htmlspecialchars($f['num_fact']) ?> — <?= htmlspecialchars($f['nom_client'] ?? '') ?> (<?= htmlspecialchars($f['date'] ?? '') ?>)
              </option>
            <?php } ?>
          </select>
        </div>

        <div class="col-md-3 mb-3">
          <label>Num Avoir</label>
          <input type="text" name="num_avoir" class="form-control" value="<?= htmlspecialchars($defaultNumAvoir) ?>" />
        </div>

        <div class="col-md-3 mb-3">
          <label>Date</label>
          <input type="date" name="date_avoir" class="form-control" value="<?= htmlspecialchars($defaultDate) ?>" required />
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label>Pourcentage d'avoir (%)</label>
          <input type="number" step="0.01" min="0" max="100" name="pourcentage" class="form-control" placeholder="ex: 70" />
        </div>
        <div class="col-md-4 mb-3">
          <label>Montant H.T de l'avoir</label>
          <input type="number" step="0.001" min="0" name="montant_ht" class="form-control" placeholder="ex: 700" required />
        </div>
        <div class="col-md-4 mb-3">
          <label>TVA (%)</label>
          <input type="number" step="0.01" min="0" name="tva_pct" class="form-control" value="19" />
        </div>
      </div>

      <div class="text-right">
        <button type="submit" name="btnSaveAvoirForfaitaire" class="btn btn-primary">Enregistrer l'avoir forfaitaire</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function(){
    function filterSelect(inputId, selectId){
      const search = document.getElementById(inputId);
      const sel = document.getElementById(selectId);
      if (!search || !sel) return;
      search.addEventListener('input', () => {
        const q = search.value.toLowerCase();
        Array.from(sel.options).forEach(opt => {
          if (!opt.value) { opt.hidden = false; return; }
          const hay = (opt.getAttribute('data-search') || opt.textContent).toLowerCase();
          opt.hidden = q !== '' && !hay.includes(q);
        });
      });
    }
    filterSelect('facture_filter_ff','facture_select_ff');
  })();
</script>
