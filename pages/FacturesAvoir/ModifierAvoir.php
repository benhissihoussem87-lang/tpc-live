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

function avoir_compute_totals(array $lines): array {
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

// Helper: compute next invoice number like AjoutFacture (e.g. "205/2025")
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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$info = $id > 0 ? $avoir->getById($id) : null;
if (!$info) { echo '<div class="alert alert-danger">Avoir introuvable.</div>'; return; }

$clients  = $clt->getAllClients();
$factures = $facture->AfficherFactures();
$projetsList = $projet->getAllProjets();
$projetsMap  = [];
foreach ($projetsList as $projRow) {
    $label = $projRow['classement'] ?? $projRow['designation'] ?? ('Projet '.$projRow['id']);
    $projetsMap[$projRow['id']] = avoir_convert_utf8($label);
}

// defaults from DB
$num_avoir   = $info['num_avoir'];
$date_avoir  = $info['date_avoir'];
$id_client   = (int)$info['id_client'];
$num_fact    = $info['num_fact'];
$num_fact_new = $info['num_facture_nouveux'] ?? ($info['num_fact_new'] ?? '');
if ($num_fact_new === null) { $num_fact_new = ''; }
if ($num_fact_new === '') {
    $num_fact_new = avoir_next_facture_number();
}
$total_ht    = (float)$info['total_ht'];
$total_tva   = (float)$info['total_tva'];
$total_ttc   = (float)$info['total_ttc'];
$mat_fisc_db = ($info['mf_avoir'] ?? '') ?: ($info['mf_client'] ?? '');
$tva_pct     = ($total_ht > 0) ? round(($total_tva / $total_ht) * 100, 2) : 19;
$lineBuffer  = $avoir->getLines($id);

if (empty($lineBuffer) && !empty($info['num_fact'])) {
    $sourceLines = $facture->get_AllProjets_ByFacture($info['num_fact']);
    foreach ($sourceLines as $line) {
        $labelParts = [];
        if (!empty($line['projet_classement'])) {
            $labelParts[] = avoir_convert_utf8($line['projet_classement']);
        }
        if (!empty($line['adresseClient'])) {
            $labelParts[] = avoir_convert_utf8($line['adresseClient']);
        }
        $label = implode(' - ', array_filter($labelParts));
        $lineBuffer[] = [
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
}

if (empty($lineBuffer)) {
    $lineBuffer[] = [
        'projet' => null,
        'prix_unit_htv' => 0,
        'qte' => 0,
        'tva' => 19,
        'remise' => '',
        'prixForfitaire' => '',
        'prixTTC' => '',
        'adresseClient' => '',
        'projet_label' => '',
    ];
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnUpdateAvoir'])) {
    $id_client  = (int)($_POST['id_client'] ?? 0);
    $date_avoir = trim($_POST['date_avoir'] ?? '');
    $num_fact   = trim($_POST['num_fact'] ?? '');
    $num_fact_new = trim($_POST['num_fact_new'] ?? '');
    $num_avoir  = trim($_POST['num_avoir'] ?? '');
    $mat_fisc   = trim($_POST['matriculeFiscale'] ?? '');
    $total_ht   = (float)($_POST['total_ht'] ?? 0);
    $tva_pct    = (float)($_POST['tva_pct'] ?? 19);
    $total_tva  = round($total_ht * ($tva_pct/100), 3);
    $total_ttc  = round($total_ht + $total_tva, 3);

$pu    = $_POST['line_prix_unit_htv'] ?? [];
$qte   = $_POST['line_qte'] ?? [];
$tva   = $_POST['line_tva'] ?? [];
$rem   = $_POST['line_remise'] ?? [];
$pf    = $_POST['line_prixForfitaire'] ?? [];
$pttc  = $_POST['line_prixTTC'] ?? [];
$proj  = $_POST['line_projet'] ?? [];
$addr  = $_POST['line_adresse'] ?? [];
$labels= $_POST['line_label'] ?? [];

    $lineBuffer = [];
    $count = max(count($pu), count($qte), count($tva), count($proj));
    for ($i=0; $i<$count; $i++) {
        $linePu  = isset($pu[$i]) ? (float)$pu[$i] : 0;
        $lineQte = isset($qte[$i]) ? (float)$qte[$i] : 0;
        $lineTva = isset($tva[$i]) ? (float)$tva[$i] : 0;
        $lineProj= isset($proj[$i]) && $proj[$i] !== '' ? (int)$proj[$i] : null;
        $lineRem = isset($rem[$i]) ? trim($rem[$i]) : '';
        $linePf  = isset($pf[$i]) ? trim($pf[$i]) : '';
        $linePttc= isset($pttc[$i]) ? trim($pttc[$i]) : '';
        $lineAdr = isset($addr[$i]) ? trim($addr[$i]) : '';
        $lineLabel = isset($labels[$i]) ? trim($labels[$i]) : '';

        if ($linePu === 0 && $lineQte === 0 && $lineProj === null && $linePf === '' && $linePttc === '' && $lineAdr === '') {
            continue;
        }

        $label = '';
        if ($lineProj && isset($projetsMap[$lineProj])) {
            $label = $projetsMap[$lineProj];
        } elseif ($lineLabel !== '') {
            $label = $lineLabel;
        }

        $lineBuffer[] = [
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

    if (empty($lineBuffer)) {
        $errors[] = "Veuillez conserver au moins une ligne de projet.";
    }

    // For forfaitaire avoirs, keep the header totals (HT + TVA %) and
    // align the single line with those values.
    // For detailed avoirs, recompute HT from lines but ALWAYS apply the
    // header TVA (%) to derive TVA and TTC so a change in the header rate
    // is reflected in the totals.
    if (($info['type_avoir'] ?? '') === 'forfaitaire') {
        // total_ht and tva_pct already computed from the header fields above
        $total_tva = round($total_ht * ($tva_pct/100), 3);
        $total_ttc = round($total_ht + $total_tva, 3);

        if (count($lineBuffer) === 1) {
            $lineBuffer[0]['prix_unit_htv']  = $total_ht;
            $lineBuffer[0]['qte']            = 1;
            $lineBuffer[0]['tva']            = $tva_pct;
            $lineBuffer[0]['prixForfitaire'] = $total_ht;
            $lineBuffer[0]['prixTTC']        = $total_ttc;
        }
    } else {
        // Detailed (non forfaitaire): sum HT from lines,
        // then apply the header TVA (%) as a global rate.
        $totals   = avoir_compute_totals($lineBuffer);
        $total_ht = $totals['ht'];
        $total_tva = round($total_ht * ($tva_pct/100), 3);
        $total_ttc = round($total_ht + $total_tva, 3);
    }

    if ($id_client <= 0) $errors[] = 'Client requis';
    if ($date_avoir === '') $errors[] = 'Date requise';
    if ($num_avoir === '') $errors[] = 'Num Avoir requis';

    // Ensure matricule from client if not posted
    if ($mat_fisc === '' && $id_client > 0) {
        $clientRow = $clt->getClient($id_client);
        if ($clientRow && !empty($clientRow['matriculeFiscale'])) {
            $mat_fisc = $clientRow['matriculeFiscale'];
        }
    }

    if (!$errors) {
        $ok = $avoir->update($id, [
            'num_avoir'        => $num_avoir,
            'num_fact'         => ($num_fact !== '' ? $num_fact : null),
            'num_facture_nouveux' => ($num_fact_new !== '' ? $num_fact_new : null),
            'id_client'        => $id_client,
            'date_avoir'       => $date_avoir,
            'total_ht'         => $total_ht,
            'total_tva'        => $total_tva,
            'total_ttc'        => $total_ttc,
            'type_avoir'       => $info['type_avoir'] ?? null,
            'pourcentage'      => $info['pourcentage'] ?? null,
            'matriculeFiscale' => ($mat_fisc !== '' ? $mat_fisc : null),
            'lines'            => $lineBuffer,
        ]);
        if ($ok) {
            echo "<script>document.location.href='main.php?Avoir'</script>";
            exit;
        } else {
            $errors[] = "Erreur de mise à jour";
        }
    }
}
?>

<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary">Modifier Avoir</h6>
  </div>
  <div class="card-body">
    <?php if (!empty($errors)) { echo '<div class="alert alert-danger">'.implode('<br>', array_map('htmlspecialchars', $errors)).'</div>'; } ?>
    <form method="post">
      <div class="row">
        <div class="col-md-3 mb-3">
          <label>Num Avoir</label>
          <input type="text" name="num_avoir" class="form-control" value="<?= htmlspecialchars($num_avoir) ?>" readonly />
        </div>
        <div class="col-md-3 mb-3">
          <label>Date</label>
          <input type="date" name="date_avoir" class="form-control" value="<?= htmlspecialchars($date_avoir) ?>" required />
        </div>
        <div class="col-md-6 mb-3">
          <label>Client</label>
          <input type="text" class="form-control mb-2" id="client_filter" placeholder="Rechercher client..."/>
          <select class="form-control" name="id_client" id="id_client_select" required>
            <option value="">-- choisir --</option>
            <?php foreach ($clients as $c) { $sel = ((int)$c['id'] === $id_client) ? 'selected' : ''; ?>
              <option value="<?= (int)$c['id'] ?>" <?= $sel ?> data-mf="<?= htmlspecialchars($c['matriculeFiscale'] ?? '') ?>" data-search="<?= htmlspecialchars(($c['nom_client'] ?? '').' '.($c['adresse'] ?? '').' '.($c['matriculeFiscale'] ?? '')) ?>"><?= htmlspecialchars($c['nom_client']) ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label>Choisir Facture (optionnel)</label>
          <input type="text" class="form-control mb-2" id="facture_filter" placeholder="Rechercher facture..."/>
          <select class="form-control" name="num_fact" id="num_fact_select">
            <option value="">-- aucune --</option>
            <?php if (!empty($factures)) { foreach ($factures as $f) { $sel = ($f['num_fact'] == $num_fact) ? 'selected' : ''; ?>
              <option value="<?= htmlspecialchars($f['num_fact']) ?>" <?= $sel ?> data-search="<?= htmlspecialchars($f['num_fact'].' '.($f['nom_client'] ?? '').' '.($f['date'] ?? '')) ?>"><?= htmlspecialchars($f['num_fact']) ?> — <?= htmlspecialchars($f['nom_client'] ?? '') ?> (<?= htmlspecialchars($f['date'] ?? '') ?>)</option>
            <?php }} ?>
          </select>
          <input type="hidden" name="matriculeFiscale" id="matriculeFiscale" value="<?= htmlspecialchars($mat_fisc_db) ?>" />
        </div>
        <div class="col-md-6 mb-3">
          <label>Nouvelle Facture (avoir)</label>
          <input type="text" name="num_fact_new" class="form-control" value="<?= htmlspecialchars($num_fact_new) ?>" />
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label>Total HT</label>
          <input type="number" step="0.001" name="total_ht" id="total_ht" class="form-control" value="<?= number_format($total_ht,3,'.','') ?>" />
        </div>
        <div class="col-md-4 mb-3">
          <label>TVA (%)</label>
          <input type="number" step="0.01" name="tva_pct" id="tva_pct" class="form-control" value="<?= htmlspecialchars($tva_pct) ?>" />
        </div>
        <div class="col-md-4 mb-3">
          <label>Total TVA (calculé)</label>
          <input type="number" step="0.001" name="total_tva" id="total_tva" class="form-control" value="<?= number_format($total_tva,3,'.','') ?>" readonly />
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 mb-3">
          <label>Total TTC (calculé)</label>
          <input type="number" step="0.001" name="total_ttc" id="total_ttc" class="form-control" value="<?= number_format($total_ttc,3,'.','') ?>" readonly />
        </div>
      </div>

      <div class="mb-4">
        <h6 class="font-weight-bold mb-3">Lignes de l'avoir</h6>
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
              <?php foreach ($lineBuffer as $line) {
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
                    <input type="hidden" name="line_label[]" value="<?= htmlspecialchars($line['projet_label'] ?? '') ?>">
                  </td>
                  <td><input type="number" step="0.001" class="form-control" name="line_prix_unit_htv[]" value="<?= htmlspecialchars($line['prix_unit_htv'] ?? '') ?>" /></td>
                  <td><input type="number" step="0.001" class="form-control" name="line_qte[]" value="<?= htmlspecialchars($line['qte'] ?? '') ?>" /></td>
                  <td><input type="number" step="0.01" class="form-control" name="line_tva[]" value="<?= htmlspecialchars($line['tva'] ?? '') ?>" /></td>
                  <td><input type="text" class="form-control" name="line_remise[]" value="<?= htmlspecialchars($line['remise'] ?? '') ?>" /></td>
                  <td><input type="number" step="0.001" class="form-control" name="line_prixForfitaire[]" value="<?= htmlspecialchars($line['prixForfitaire'] ?? '') ?>" /></td>
                  <td><input type="number" step="0.001" class="form-control" name="line_prixTTC[]" value="<?= htmlspecialchars($line['prixTTC'] ?? '') ?>" /></td>
                  <td><input type="text" class="form-control" name="line_adresse[]" value="<?= htmlspecialchars($line['adresseClient'] ?? '') ?>" /></td>
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
              <input type="hidden" name="line_label[]" value="">
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
      </div>

      <div class="text-right">
        <button type="submit" name="btnUpdateAvoir" class="btn btn-primary">Enregistrer</button>
        <a href="?Avoir" class="btn btn-secondary">Annuler</a>
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
    filterSelect('client_filter','id_client_select');
    filterSelect('facture_filter','num_fact_select');

    function recalc(){
      const ht = parseFloat(document.getElementById('total_ht').value)||0;
      const pct = parseFloat(document.getElementById('tva_pct').value)||0;
      const tva = +(ht * (pct/100)).toFixed(3);
      const ttc = +(ht + tva).toFixed(3);
      document.getElementById('total_tva').value = tva.toFixed(3);
      document.getElementById('total_ttc').value = ttc.toFixed(3);
    }
    ['total_ht','tva_pct'].forEach(id=>{
      const el = document.getElementById(id);
      if (el) el.addEventListener('input', recalc);
    });
    recalc();

    const selClient = document.getElementById('id_client_select');
    const mf = document.getElementById('matriculeFiscale');
    if (selClient && mf){
      selClient.addEventListener('change', () => {
        const opt = selClient.options[selClient.selectedIndex];
        if (opt) mf.value = opt.getAttribute('data-mf') || '';
      });
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
