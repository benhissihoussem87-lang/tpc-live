<?php
// tpc/pages/Factures/AjoutFactureForfitaire.php

include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/Factures.class.php';
include 'class/OffresPrix.class.php';
include 'class/Reglements.class.php';
require_once 'class/connexion.db.php';
require_once __DIR__ . '/forfait_form_helpers.php';

/** Same robust generator used here too */
function getNextInvoiceNumber(PDO $pdo, ?int $year = null): string {
  // Tail-only increment: do NOT backfill mid-year gaps.
  $year = $year ?? (int)date('Y');
  $st = $pdo->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(num_fact,'/',1) AS UNSIGNED)), 0) AS max_seq
                        FROM facture WHERE RIGHT(num_fact, 4) = :yr");
  $st->execute([':yr' => (string)$year]);
  $next = ((int)$st->fetchColumn()) + 1;
  return $next . '/' . $year;
}

$pdo      = connexion();
$clients  = $clt->getAllClients();
$projets  = $projet->getAllProjets();
$factures = $facture->AfficherFactures();
$anne     = (int)date('Y');

$num_Facture = getNextInvoiceNumber($pdo, $anne);
$forfaitFormErrors = [];
$forfaitLinesPrefill = [];

/*************** Facture Forfitaire  *********************/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['btnSubmitAjoutFactureForfitaire'])) {
  $rawLines = isset($_POST['lignes']) && is_array($_POST['lignes']) ? $_POST['lignes'] : [];
  $forfaitLinesPrefill = tpc_prepare_forfait_lines_prefill($rawLines);

  // Accept free-text projects; just require non-empty projet + prix
  $validLines = [];
  foreach ($rawLines as $ln) {
    if (!is_array($ln)) continue;
    $proj = trim((string)($ln['projet'] ?? ''));
    $prix = trim((string)($ln['prix'] ?? ''));
    $adr  = trim((string)($ln['adresse'] ?? ''));
    if ($proj === '' || $prix === '') continue;
    $validLines[] = [
      'projet' => $proj,
      'projet_id' => $proj, // free text accepted
      'prix_raw' => $prix,
      'adresse' => $adr,
    ];
  }

  if (empty($validLines)) {
    $forfaitFormErrors[] = "Ajoutez au moins une ligne avec un projet et un prix forfaitaire.";
  }

  $Numero_Facture   = $_POST['num_fact'] ?? '';
  $clientId         = $_POST['client'] ?? '';
  $bonCommandeInput = $_POST['numboncommande'] ?? '';
  $dateFacture      = $_POST['date'] ?? date('Y-m-d');
  $etatReglement    = $_POST['reglement'] ?? 'non';

  if (empty($forfaitFormErrors)) {
    if ($facture->Ajout($Numero_Facture, $clientId, $bonCommandeInput, $dateFacture, $etatReglement)) {

      // Offer header mirror
      $offre->Ajout($Numero_Facture, $dateFacture, $clientId);

      $seenAddress = [];
      foreach ($validLines as $line) {
        $adrKey = strtolower(trim($line['adresse'] ?? ''));
        $isFirstForAdr = !isset($seenAddress[$adrKey]);
        $seenAddress[$adrKey] = true;

        $qteFlag   = $isFirstForAdr ? 'ENS' : '';
        $pidOrText = $line['projet_id'];

        $facture->AjoutProjets_Facture(
          $Numero_Facture,
          '',
          $qteFlag,
          '',
          '',
          $line['prix_raw'],
          '',
          $pidOrText,
          $line['adresse']
        );

        // Mirror to offre with same ENS marker per address
        $offre->AjoutProjets_Offre(
          $Numero_Facture,
          '',
          $qteFlag,
          '',
          '',
          $line['prix_raw'],
          '',
          $pidOrText,
          $line['adresse']
        );
      }

      $reglementMontant = implode(' ', array_column($validLines, 'prix_raw'));
      $reglement->Ajout($clientId, $Numero_Facture, $reglementMontant, $etatReglement, '', '', '', '', '', '', '');

      // Auto-archive the newly created invoice (minimal fields per schema)
      @$facture->AjoutArchive(
        $Numero_Facture,  // num_fact
        $clientId,        // client
        $dateFacture,     // date
        '',               // joindre
        $etatReglement,   // reglement
        '',               // typeReglement
        '',               // numcheque
        '',               // datecheque
        '',               // retenu
        ''                // Projets
      );

      echo "<script>document.location.href='main.php?Bordereaux&Add&Facture=$Numero_Facture'</script>";
      exit;
    } else {
      $forfaitFormErrors[] = "Erreur lors de l'enregistrement de la facture.";
    }
  }
}

$selectedClientId  = $_POST['client'] ?? '';
$postedBonCommande = $_POST['numboncommande'] ?? '';
$postedExonoration = $_POST['numexonoration'] ?? '';
$postedDate        = $_POST['date'] ?? date('Y-m-d');
$postedReglement   = $_POST['reglement'] ?? 'non';
?>

<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingTwo">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Facture Forfitaire
        </button>
      </h2>
    </div>

    <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
      <div class="card-body">
        <!-- Form Add Facture -->
        <form method="post">
          <div class="modal-body row">

            <?php if (!empty($forfaitFormErrors)) { ?>
            <div class="col-12">
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($forfaitFormErrors as $err) { ?>
                  <li><?= htmlspecialchars($err) ?></li>
                  <?php } ?>
                </ul>
              </div>
            </div>
            <?php } ?>

            <div class="mb-3 col-3">
              <label for="num_fact" class="col-form-label">N&deg; Facture:</label>
              <input type="text" value="<?= htmlspecialchars($num_Facture) ?>" readonly class="form-control" id="num_fact" name="num_fact"/>
            </div>

            <div class="mb-3 col-5">
              <label for="client" class="col-form-label">Client:</label>
              <!-- Recherche client -->
              <input type="search" id="clientSearch" class="form-control mb-2" placeholder="Rechercher un client..." autocomplete="off">
              <select class="form-control" id="client" name="client" size="8" style="overflow-y:auto;">
                <?php if (!empty($clients)) {
                      foreach ($clients as $key) {
                        $cid = (string)$key['id'];
                ?>
                  <option value="<?= htmlspecialchars($cid) ?>" <?= $selectedClientId === $cid ? 'selected' : '' ?>><?= htmlspecialchars($key['nom_client']) ?></option>
                <?php }} ?>
              </select>
            </div>

            <div class="mb-3 col-3">
              <label for="numboncommande" class="col-form-label">N&deg; Bon de commande:</label>
              <input type="text" class="form-control" id="numboncommande" name="numboncommande" value="<?= htmlspecialchars($postedBonCommande) ?>"/>
            </div>

            <div class="mb-3 col-3">
              <label for="numexonoration" class="col-form-label">N&deg; exonoration:</label>
              <input type="text" class="form-control" id="numexonoration" name="numexonoration" value="<?= htmlspecialchars($postedExonoration) ?>"/>
            </div>

            <div class="mb-3 col-3">
              <label for="date" class="col-form-label">Date Facture:</label>
              <input type="date" value="<?= htmlspecialchars($postedDate) ?>" required class="form-control" id="date" name="date"/>
            </div>

            <div class="mb-3 col-2">
              <label for="reglement" class="col-form-label">Reglement:</label>
              <select class="form-control" id="reglement" name="reglement">
                <option value="oui" <?= $postedReglement === 'oui' ? 'selected' : '' ?>>Oui</option>
                <option value="Avance" <?= $postedReglement === 'Avance' ? 'selected' : '' ?>>Avance</option>
                <option value="non" <?= $postedReglement === 'non' ? 'selected' : '' ?>>Non</option>
              </select>
            </div>

            <div class="mb-3 col-12">
              <?php
                $forfaitLinesWidgetId = 'forfaitLinesAdd';
                include __DIR__ . '/partials/forfait_lines_widget.php';
              ?>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            <button type="submit" class="btn btn-primary" name="btnSubmitAjoutFactureForfitaire">Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Search + UX scripts -->
<script>
// Block Enter from submitting
document.addEventListener('keydown', function (e) {
  const tag = (e.target && e.target.tagName || '').toLowerCase();
  if (e.key === 'Enter' && (tag === 'input' || tag === 'select')) {
    e.preventDefault();
  }
});

// Client search
(function(){
  const search = document.getElementById('clientSearch');
  const select = document.getElementById('client');
  if (!search || !select) return;

  function filterClients() {
    const q = (search.value || '').toLowerCase();
    let firstVisibleIndex = -1;

    for (let i = 0; i < select.options.length; i++) {
      const opt = select.options[i];
      const visible = opt.text.toLowerCase().includes(q);
      opt.hidden = !visible;
      if (visible && firstVisibleIndex === -1) firstVisibleIndex = i;
    }
    if (firstVisibleIndex !== -1) {
      select.selectedIndex = firstVisibleIndex;
      const selOpt = select.options[firstVisibleIndex];
      if (selOpt) selOpt.scrollIntoView({ block: 'nearest' });
    }
  }
  search.addEventListener('input', filterClients);
  filterClients();
})();
</script>


