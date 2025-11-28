<?php
include_once 'class/Avoir.class.php';
include_once 'class/client.class.php';
include_once 'class/Factures.class.php';
include_once 'class/Projet.class.php';

if (!function_exists('avoir_convert_utf8')) {
    function avoir_convert_utf8($value) {
        if (!is_string($value) || $value === '') {
            return $value;
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        }
        if (function_exists('iconv')) {
            $converted = @iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $value);
            if ($converted !== false) {
                return $converted;
            }
        }
        return $value;
    }
}

// Compute the next invoice number like AjoutFacture (e.g. "205/2025")
if (!function_exists('avoir_next_facture_number')) {
    function avoir_next_facture_number(): string {
        $year = (int)date('Y');
        try {
            $pdo = connexion();
            $st = $pdo->prepare(
                "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(num_fact,'/',1) AS UNSIGNED)), 0) AS max_seq
                   FROM facture
                  WHERE RIGHT(num_fact, 4) = :yr"
            );
            $st->execute([':yr' => (string)$year]);
            $next = ((int)$st->fetchColumn()) + 1;
            return $next . '/' . $year;
        } catch (\PDOException $e) {
            return '1/'.$year;
        }
    }
}

$clients     = $clt->getAllClients();
$factures    = $facture->AfficherFactures();
$projetsList = $projet->getAllProjets();
$projetsMap  = [];
foreach ($projetsList as $projRow) {
    $label = $projRow['classement'] ?? $projRow['designation'] ?? ('Projet '.$projRow['id']);
    $projetsMap[$projRow['id']] = avoir_convert_utf8($label);
}

if (!isset($_SESSION['avoir_wizard']) || !is_array($_SESSION['avoir_wizard'])) {
    $_SESSION['avoir_wizard'] = [];
}
$wizard =& $_SESSION['avoir_wizard'];

if (isset($_GET['resetWizard'])) {
    $wizard = [];
    $wizard['step'] = 1;
}

function computeWizardTotals(array $lines): array {
    $totals = ['ht' => 0.0, 'tva' => 0.0, 'ttc' => 0.0];
    foreach ($lines as $line) {
        $htBase = (isset($line['prixForfitaire']) && $line['prixForfitaire'] !== '' && is_numeric($line['prixForfitaire']))
            ? (float)$line['prixForfitaire']
            : (float)$line['prix_unit_htv'] * (float)$line['qte'];
        $htBase = round($htBase, 3);

        $ttcBase = (isset($line['prixTTC']) && $line['prixTTC'] !== '' && is_numeric($line['prixTTC']))
            ? (float)$line['prixTTC']
            : round($htBase * (1 + ((float)$line['tva'] / 100)), 3);
        $tvaBase = round($ttcBase - $htBase, 3);

        $totals['ht']  += $htBase;
        $totals['tva'] += $tvaBase;
        $totals['ttc'] += $ttcBase;
    }
    $totals['ht']  = round($totals['ht'], 3);
    $totals['tva'] = round($totals['tva'], 3);
    $totals['ttc'] = round($totals['ttc'], 3);
    return $totals;
}

$errors = [];
$currentStep = (int)($wizard['step'] ?? 1);
if ($currentStep < 1 || $currentStep > 3) {
    $currentStep = 1;
}

// Step 1 : choisir la facture
if (isset($_POST['btnSelectFacture'])) {
    $num = trim($_POST['facture_selected'] ?? '');
    if ($num === '') {
        $errors[] = 'Veuillez sélectionner une facture.';
        $currentStep = 1;
    } else {
        $details = $facture->detailFacture($num);
        $lines   = $facture->get_AllProjets_ByFacture($num);
        if (!$details) {
            $errors[] = "Facture introuvable.";
            $currentStep = 1;
        }

        $normalized = [];
        foreach ($lines as $line) {
            $label = '';
            if (!empty($line['projet_classement'])) {
                $label = avoir_convert_utf8($line['projet_classement']);
            } elseif (!empty($line['projet'])) {
                $label = avoir_convert_utf8($line['projet']);
            }

            $normalized[] = [
                'projet'         => $line['projet'] ?? null,
                'prix_unit_htv'  => (float)$line['prix_unit_htv'],
                'qte'            => (float)$line['qte'],
                'tva'            => (float)$line['tva'],
                'remise'         => avoir_convert_utf8($line['remise'] ?? ''),
                'prixForfitaire' => $line['prixForfitaire'],
                'prixTTC'        => $line['prixTTC'],
                'adresseClient'  => avoir_convert_utf8($line['adresseClient'] ?? ''),
                'projet_label'   => $label,
            ];
        }

        $manualNotice = null;
        if (empty($normalized)) {
            $normalized[] = [
                'projet'         => null,
                'prix_unit_htv'  => 0,
                'qte'            => 0,
                'tva'            => 19,
                'remise'         => '',
                'prixForfitaire' => '',
                'prixTTC'        => '',
                'adresseClient'  => '',
                'projet_label'   => '',
            ];
            $manualNotice = "Aucune ligne n'a été trouvée pour cette facture. Renseignez manuellement les détails de l'avoir.";
        }

        $wizard = [
            'step'           => 2,
            'facture_num'    => $num,
            'facture_header' => $details,
            'lines'          => $normalized,
            'selected_client'=> $details['client'] ?? null,
            'manual_notice'  => $manualNotice,
        ];
        $currentStep = 2;
    }
}

// Step 2 : modifier les lignes
if (isset($_POST['btnBackStep1'])) {
    $wizard['step'] = 1;
    $currentStep = 1;
}

if (isset($_POST['btnLinesNext'])) {
    $pu    = $_POST['line_prix_unit_htv'] ?? [];
    $qte   = $_POST['line_qte'] ?? [];
    $tva   = $_POST['line_tva'] ?? [];
    $rem   = $_POST['line_remise'] ?? [];
    $pf    = $_POST['line_prixForfitaire'] ?? [];
    $pttc  = $_POST['line_prixTTC'] ?? [];
    $proj  = $_POST['line_projet'] ?? [];
    $addr  = $_POST['line_adresse'] ?? [];

    $count = max(count($pu), count($qte), count($tva), count($proj));
    $newLines = [];
    for ($i = 0; $i < $count; $i++) {
        $linePu  = isset($pu[$i]) ? (float)$pu[$i] : 0;
        $lineQte = isset($qte[$i]) ? (float)$qte[$i] : 0;
        $lineTva = isset($tva[$i]) ? (float)$tva[$i] : 0;
        $lineProj= isset($proj[$i]) && $proj[$i] !== '' ? (int)$proj[$i] : null;
            $lineRem = isset($rem[$i]) ? trim($rem[$i]) : '';
            $linePf  = isset($pf[$i]) ? trim($pf[$i]) : '';
            $linePttc= isset($pttc[$i]) ? trim($pttc[$i]) : '';
            $lineAdr = isset($addr[$i]) ? trim($addr[$i]) : '';

        if ($linePu === 0 && $lineQte === 0 && $lineProj === null && $linePf === '' && $linePttc === '' && $lineAdr === '') {
            continue;
        }

        $label = '';
        if ($lineProj && isset($projetsMap[$lineProj])) {
            $label = $projetsMap[$lineProj];
        }

        $newLines[] = [
            'projet'         => $lineProj,
            'prix_unit_htv'  => $linePu,
            'qte'            => $lineQte,
            'tva'            => $lineTva,
            'remise'         => avoir_convert_utf8($lineRem),
            'prixForfitaire' => $linePf,
            'prixTTC'        => $linePttc,
            'adresseClient'  => avoir_convert_utf8($lineAdr),
            'projet_label'   => avoir_convert_utf8($label),
        ];
    }

    if (empty($newLines)) {
        $errors[] = "Veuillez conserver au moins une ligne de projet.";
        $currentStep = 2;
    } else {
        $wizard['lines'] = $newLines;
        $wizard['step']  = 3;
        $currentStep = 3;
    }
}

// Step 3 : confirmation + infos client
if (isset($_POST['btnBackStep2'])) {
    $wizard['step'] = 2;
    $currentStep = 2;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSaveAvoir'])) {
    $id_client   = (int)($_POST['id_client'] ?? 0);
    $date_avoir  = trim($_POST['date_avoir'] ?? '');
    $num_avoir   = trim($_POST['num_avoir'] ?? '');
    $mat_fisc    = trim($_POST['matriculeFiscale'] ?? '');
    $num_fact    = $wizard['facture_num'] ?? '';
    $lineBuffer  = $wizard['lines'] ?? [];

    $wizard['num_avoir']       = $num_avoir;
    $wizard['selected_client'] = $id_client;
    $wizard['selected_date']   = $date_avoir;

    if (empty($num_fact)) $errors[] = "Aucune facture sélectionnée.";
    if (empty($lineBuffer)) $errors[] = "Aucune ligne disponible pour l'avoir.";
    if ($id_client <= 0) $errors[] = 'Client requis';
    if ($date_avoir === '') $errors[] = 'Date requise';
    if ($num_avoir === '') { $num_avoir = $avoir->nextNumber(); }

    if ($mat_fisc === '' && $id_client > 0) {
        $clientRow = $clt->getClient($id_client);
        if ($clientRow && !empty($clientRow['matriculeFiscale'])) {
            $mat_fisc = $clientRow['matriculeFiscale'];
        }
    }

    $totals = computeWizardTotals($lineBuffer);

    if (!$errors) {
        $ok = $avoir->create([
            'num_avoir'            => $num_avoir,
            'num_fact'             => $num_fact,
            'num_facture_nouveux'  => avoir_next_facture_number(),
            'id_client'            => $id_client,
            'date_avoir'           => $date_avoir,
            'total_ht'             => $totals['ht'],
            'total_tva'            => $totals['tva'],
            'total_ttc'            => $totals['ttc'],
            'type_avoir'           => 'detail',
            'pourcentage'          => null,
            'matriculeFiscale'     => ($mat_fisc !== '' ? $mat_fisc : null),
            'lines'                => $lineBuffer,
        ]);
        if ($ok) {
            unset($_SESSION['avoir_wizard']);
            echo "<script>document.location.href='main.php?Avoir'</script>";
            exit;
        } else {
            $errors[] = "Erreur d'enregistrement";
        }
    }
    $currentStep = 3;
}

$wizard['step'] = $currentStep;

$selectedFacture   = $wizard['facture_header'] ?? null;
$selectedLines     = $wizard['lines'] ?? [];
$selectedFactureNo = $wizard['facture_num'] ?? '';
$defaultNumAvoir   = $_POST['num_avoir'] ?? ($wizard['num_avoir'] ?? $avoir->nextNumber());
$defaultDate       = $_POST['date_avoir'] ?? ($wizard['selected_date'] ?? date('Y-m-d'));
$defaultClient     = $_POST['id_client'] ?? ($wizard['selected_client'] ?? ($selectedFacture['client'] ?? ''));
$defaultMatricule  = $_POST['matriculeFiscale'] ?? ($selectedFacture['matriculeFiscale'] ?? '');
$totalsPreview     = computeWizardTotals($selectedLines);
?>

<div class="card shadow mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h6 class="m-0 font-weight-bold text-primary">Ajouter Avoir</h6>
      <small class="text-muted">Étape <?= $currentStep ?> / 3</small>
    </div>
    <div>
      <a href="main.php?Avoir&Add&resetWizard=1" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
      <a href="?Avoir" class="btn btn-sm btn-outline-danger ml-2">Fermer</a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!empty($errors)) { echo '<div class="alert alert-danger">'.implode('<br>', array_map('htmlspecialchars', $errors)).'</div>'; } ?>

    <?php if ($currentStep === 1) { ?>
      <form method="post">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Choisir une facture</label>
            <input type="text" class="form-control mb-2" id="facture_filter" placeholder="Rechercher facture..." />
            <select class="form-control" name="facture_selected" id="facture_select" required>
              <option value="">-- sélectionner --</option>
              <?php foreach ($factures as $f) { ?>
                <option value="<?= htmlspecialchars($f['num_fact']) ?>"
                        data-search="<?= htmlspecialchars($f['num_fact'].' '.($f['nom_client'] ?? '').' '.($f['date'] ?? '')) ?>">
                  <?= htmlspecialchars($f['num_fact']) ?> — <?= htmlspecialchars($f['nom_client'] ?? '') ?> (<?= htmlspecialchars($f['date'] ?? '') ?>)
                </option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="text-right">
          <button type="submit" name="btnSelectFacture" class="btn btn-primary">Confirmer la facture</button>
        </div>
      </form>
    <?php } elseif ($currentStep === 2) { ?>

      <div class="mb-4">
        <h6 class="font-weight-bold mb-1">Facture sélectionnée</h6>
        <?php if ($selectedFacture) { ?>
          <div class="alert alert-info mb-3">
            <strong><?= htmlspecialchars($selectedFactureNo) ?></strong> —
            Client : <?= htmlspecialchars($selectedFacture['nom_client'] ?? '') ?>,
            Date : <?= htmlspecialchars($selectedFacture['date'] ?? '') ?>
          </div>
        <?php } ?>
        <?php if (!empty($wizard['manual_notice'])) { ?>
          <div class="alert alert-warning">
            <?= htmlspecialchars($wizard['manual_notice']) ?>
          </div>
        <?php } ?>
      </div>

      <form method="post">
        <div class="table-responsive">
          <table class="table table-bordered" id="linesTable">
            <thead class="thead-light">
              <tr>
                <th>Projet</th>
                <th>Prix Unit HT</th>
                <th>Quantité</th>
                <th>TVA %</th>
                <th>Remise</th>
                <th>Prix Forfaitaire</th>
                <th>Prix TTC</th>
                <th>Adresse Client</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="linesBody">
              <?php foreach ($selectedLines as $line) {
                  $lineProjIdRaw = $line['projet'] ?? null;
                  $lineProjId = ($lineProjIdRaw === '' || $lineProjIdRaw === null) ? null : (int)$lineProjIdRaw;
                  $savedLabel = avoir_convert_utf8($line['projet_label'] ?? '');
                  $knownProject = ($lineProjId !== null && isset($projetsMap[$lineProjId]));
                  $needsFallbackLabel = ($savedLabel !== '' && !$knownProject);
              ?>
                <tr>
                  <td>
                    <select class="form-control" name="line_projet[]">
                      <option value="">--</option>
                      <?php foreach ($projetsList as $p) {
                        $projLabel = avoir_convert_utf8($p['classement'] ?? $p['designation'] ?? ('Projet '.$p['id']));
                      ?>
                        <option value="<?= (int)$p['id'] ?>" <?= ($lineProjId !== null && $lineProjId == $p['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($projLabel) ?>
                        </option>
                      <?php } ?>
                      <?php if ($needsFallbackLabel) { ?>
                        <option value="<?= $lineProjId !== null ? $lineProjId : '' ?>" selected><?= htmlspecialchars($savedLabel) ?></option>
                      <?php } ?>
                    </select>
                  </td>
                  <td><input type="number" step="0.001" class="form-control" name="line_prix_unit_htv[]" value="<?= htmlspecialchars($line['prix_unit_htv']) ?>" /></td>
                  <td><input type="number" step="0.001" class="form-control" name="line_qte[]" value="<?= htmlspecialchars($line['qte']) ?>" /></td>
                  <td><input type="number" step="0.01" class="form-control" name="line_tva[]" value="<?= htmlspecialchars($line['tva']) ?>" /></td>
                  <td><input type="text" class="form-control" name="line_remise[]" value="<?= htmlspecialchars(avoir_convert_utf8($line['remise'])) ?>" /></td>
                  <td><input type="number" step="0.001" class="form-control" name="line_prixForfitaire[]" value="<?= htmlspecialchars($line['prixForfitaire']) ?>" /></td>
                  <td><input type="number" step="0.001" class="form-control" name="line_prixTTC[]" value="<?= htmlspecialchars($line['prixTTC']) ?>" /></td>
                  <td><input type="text" class="form-control" name="line_adresse[]" value="<?= htmlspecialchars(avoir_convert_utf8($line['adresseClient'])) ?>" /></td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)">&times;</button>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="mb-3">
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLine()">Ajouter une ligne</button>
        </div>
        <template id="lineRowTemplate">
          <tr>
            <td>
              <select class="form-control" name="line_projet[]">
                <option value="">--</option>
                <?php foreach ($projetsList as $p) {
                  $projLabel = avoir_convert_utf8($p['classement'] ?? $p['designation'] ?? ('Projet '.$p['id']));
                ?>
                  <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($projLabel) ?></option>
                <?php } ?>
              </select>
            </td>
            <td><input type="number" step="0.001" class="form-control" name="line_prix_unit_htv[]" /></td>
            <td><input type="number" step="0.001" class="form-control" name="line_qte[]" /></td>
            <td><input type="number" step="0.01" class="form-control" name="line_tva[]" value="19" /></td>
            <td><input type="text" class="form-control" name="line_remise[]" /></td>
            <td><input type="number" step="0.001" class="form-control" name="line_prixForfitaire[]" /></td>
            <td><input type="number" step="0.001" class="form-control" name="line_prixTTC[]" /></td>
            <td><input type="text" class="form-control" name="line_adresse[]" /></td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)">&times;</button>
            </td>
          </tr>
        </template>
        <div class="d-flex justify-content-between">
          <button type="submit" name="btnBackStep1" class="btn btn-secondary">Retour à l'étape 1</button>
          <button type="submit" name="btnLinesNext" class="btn btn-primary">Valider les lignes</button>
        </div>
      </form>

    <?php } else { ?>

      <div class="mb-4">
        <h6 class="font-weight-bold mb-1">Récapitulatif</h6>
        <?php if (!empty($wizard['manual_notice'])) { ?>
          <div class="alert alert-warning">
            <?= htmlspecialchars($wizard['manual_notice']) ?>
          </div>
        <?php } ?>
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead class="thead-light">
              <tr>
                <th>Projet</th>
                <th>Prix Unit HT</th>
                <th>Quantité</th>
                <th>TVA %</th>
                <th>Prix Forfaitaire</th>
                <th>Prix TTC</th>
                <th>Adresse</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($selectedLines as $line) { ?>
                <tr>
                  <?php
                    $displayLabel = $line['projet_label'] ?? '';
                    if ($displayLabel === '' && !empty($line['projet']) && isset($projetsMap[$line['projet']])) {
                        $displayLabel = $projetsMap[$line['projet']];
                    } elseif ($displayLabel === '') {
                        $displayLabel = avoir_convert_utf8($line['projet'] ?? '-');
                    }
                  ?>
                  <td><?= htmlspecialchars($displayLabel) ?></td>
                  <td><?= number_format((float)$line['prix_unit_htv'], 3, '.', ' ') ?></td>
                  <td><?= number_format((float)$line['qte'], 3, '.', ' ') ?></td>
                  <td><?= number_format((float)$line['tva'], 2, '.', ' ') ?></td>
                  <td><?= htmlspecialchars($line['prixForfitaire']) ?></td>
                  <td><?= htmlspecialchars($line['prixTTC']) ?></td>
                  <td><?= htmlspecialchars(avoir_convert_utf8($line['adresseClient'])) ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

      <form method="post">
        <div class="row">
          <div class="col-md-3 mb-3">
            <label>Num Avoir</label>
            <input type="text" name="num_avoir" class="form-control" value="<?= htmlspecialchars($defaultNumAvoir) ?>" />
          </div>
          <div class="col-md-3 mb-3">
            <label>Date</label>
            <input type="date" name="date_avoir" class="form-control" value="<?= htmlspecialchars($defaultDate) ?>" required />
          </div>
          <div class="col-md-6 mb-3">
            <label>Client</label>
            <input type="text" class="form-control mb-2" id="client_filter" placeholder="Rechercher client..." />
            <select class="form-control" name="id_client" id="id_client_select" required>
              <option value="">-- choisir --</option>
              <?php foreach ($clients as $c) { ?>
                <option value="<?= (int)$c['id'] ?>"
                        data-mf="<?= htmlspecialchars($c['matriculeFiscale'] ?? '') ?>"
                        data-search="<?= htmlspecialchars(($c['nom_client'] ?? '').' '.($c['adresse'] ?? '').' '.($c['matriculeFiscale'] ?? '')) ?>"
                        <?= ((int)$defaultClient === (int)$c['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['nom_client']) ?>
                </option>
              <?php } ?>
            </select>
          </div>
        </div>

        <input type="hidden" name="matriculeFiscale" id="matriculeFiscale" value="<?= htmlspecialchars($defaultMatricule) ?>" />

        <div class="row">
          <div class="col-md-4 mb-3">
            <label>Total HT</label>
            <input type="number" step="0.001" class="form-control" value="<?= number_format($totalsPreview['ht'], 3, '.', '') ?>" readonly />
          </div>
          <div class="col-md-4 mb-3">
            <label>Total TVA</label>
            <input type="number" step="0.001" class="form-control" value="<?= number_format($totalsPreview['tva'], 3, '.', '') ?>" readonly />
          </div>
          <div class="col-md-4 mb-3">
            <label>Total TTC</label>
            <input type="number" step="0.001" class="form-control" value="<?= number_format($totalsPreview['ttc'], 3, '.', '') ?>" readonly />
          </div>
        </div>

        <div class="d-flex justify-content-between">
          <button type="submit" name="btnBackStep2" class="btn btn-secondary">Retour à l'étape 2</button>
          <button type="submit" name="btnSaveAvoir" class="btn btn-primary">Enregistrer l'avoir</button>
        </div>
      </form>

    <?php } ?>
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
    filterSelect('client_filter','id_client_select');
    filterSelect('facture_filter','facture_select');

    const selClient = document.getElementById('id_client_select');
    const mf = document.getElementById('matriculeFiscale');
    if (selClient && mf){
      const updateMf = () => {
        const opt = selClient.options[selClient.selectedIndex];
        if (opt) mf.value = opt.getAttribute('data-mf') || '';
      };
      selClient.addEventListener('change', updateMf);
      updateMf();
    }
  })();

  function removeLine(btn){
    const row = btn.closest('tr');
    if (row && row.parentNode) {
      row.parentNode.removeChild(row);
    }
  }

  function addLine(){
    const body = document.getElementById('linesBody');
    const tpl = document.getElementById('lineRowTemplate');
    if (!body || !tpl) return;
    const clone = tpl.content.cloneNode(true);
    body.appendChild(clone);
  }
</script>
