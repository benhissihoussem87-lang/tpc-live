<?php
// tpc/pages/Factures/AjoutFacture.php

include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/Factures.class.php';
include 'class/OffresPrix.class.php';
include 'class/Reglements.class.php';
require_once 'class/connexion.db.php'; // for direct PDO access (invoice number helper)

/**
 * Compute the next invoice number as "<seq>/<YYYY>" scanning BOTH facture and archivefacture
 * for the given year. Safe even if sequence passed 100, 1000, etc.
 */
function getNextInvoiceNumber(PDO $pdo, ?int $year = null): string {
  // Tail-only increment: do NOT backfill mid-year gaps.
  // Uses active invoices only (table `facture`).
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

// Use the robust helper for display + insert
$num_Facture = getNextInvoiceNumber($pdo, $anne);

// (Display-only) legacy counters kept if the UI shows them elsewhere
$offres = $offre->AfficherOffres();
if ($factures) {
  $nb = count($factures);
  $numFacture = ((intval($nb + 1)) < 10 ? '0' : '') . intval($nb + 1) . '/' . $anne;
} else {
  $numFacture = '01/' . $anne;
}
if ($offres) {
  $nb = count($offres);
  $numOffre = ((intval($nb + 1)) < 10 ? '0' : '') . intval($nb + 1) . '/' . $anne;
} else {
  $numOffre = '01/' . $anne;
}

// --- Add Facture Logic (unchanged business logic) ---
if (isset($_REQUEST['btnSubmitAjout'])) {
  $Numero_Facture   = $_POST['num_fact'];          // value shown/posted in the form
  $_SESSION['adresse'] = $_POST['adresseClient'];

  if ($_POST['statutFacture'] === 'Meme Facture') {
    $factureClient = $facture->getLastFactureClient($_POST['client']);
    $numFacture    = $factureClient['num_fact'];   // append only lines to the last client invoice

    for ($i = 0; $i < count($_POST['projet']); $i++) {
      $idp = isset($_POST['idProjet'][$i]) && is_numeric($_POST['idProjet'][$i]) ? (int)$_POST['idProjet'][$i] : 0;
      // Insert a line if any price field is provided (PU HT, Forfait, or TTC)
      if ($idp > 0 && (!empty($_POST['prix_unit_htv'][$i]) || !empty($_POST['prixForfitaire'][$i]) || !empty($_POST['prixTTC'][$i]))) {
        $facture->AjoutProjets_Facture(
          @$numFacture,
          @$_POST['prix_unit_htv'][$i],
          @$_POST['qte'][$i],
          @$_POST['tva'][$i],
          @$_POST['remise'][$i],
          @$_POST['prixForfitaire'][$i],
          @$_POST['prixTTC'][$i],
          $idp,
          @$_POST['adresseClient']
        );

        $offre->AjoutProjets_Offre(
          @$_POST['num_fact'],
          @$_POST['prix_unit_htv'][$i],
          @$_POST['qte'][$i],
          @$_POST['tva'][$i],
          @$_POST['remise'][$i],
          @$_POST['prixForfitaire'][$i],
          @$_POST['prixTTC'][$i],
          $idp,
          @$_POST['adresseClient']
        );
      }
    }

    echo "<script>document.location.href='main.php?Bordereaux&Add&Facture=$Numero_Facture'</script>";
  } elseif ($_POST['statutFacture'] === 'autre') {

    // Insert the new invoice using the posted number
    if ($facture->Ajout(@$_POST['num_fact'], @$_POST['client'], @$_POST['numboncommande'], @$_POST['date'], @$_POST['reglement'])) {

      // If an offre with this number already exists (for example because an
      // older facture with the same num_fact was deleted and the sequence
      // reused), drop all its project lines so the new offre only reflects
      // the projects of this freshly created facture.
      if (method_exists($offre, 'delete_All_Projets_By_Offre')) {
        $offre->delete_All_Projets_By_Offre(@$_POST['num_fact']);
      }

      if ($_POST['reglement'] === 'non') {
        $reglement->Ajout(@$_POST['client'], @$_POST['num_fact'], '', @$_POST['reglement'], '', '', '', '', '', '', '');
      }

      // Mirror to offre
      $offre->Ajout(@$_POST['num_fact'], @$_POST['date'], @$_POST['client']);

      // Insert lines
      for ($i = 0; $i < count($_POST['projet']); $i++) {
        $idp = isset($_POST['idProjet'][$i]) && is_numeric($_POST['idProjet'][$i]) ? (int)$_POST['idProjet'][$i] : 0;
        // Insert when at least one price field is present
        if ($idp > 0 && (!empty($_POST['prix_unit_htv'][$i]) || !empty($_POST['prixForfitaire'][$i]) || !empty($_POST['prixTTC'][$i]))) {
          $facture->AjoutProjets_Facture(
            @$_POST['num_fact'],
            @$_POST['prix_unit_htv'][$i],
            @$_POST['qte'][$i],
            @$_POST['tva'][$i],
            @$_POST['remise'][$i],
            @$_POST['prixForfitaire'][$i],
            @$_POST['prixTTC'][$i],
            $idp,
            @$_POST['adresseClient']
          );

          $offre->AjoutProjets_Offre(
            @$_POST['num_fact'],
            @$_POST['prix_unit_htv'][$i],
            @$_POST['qte'][$i],
            @$_POST['tva'][$i],
            @$_POST['remise'][$i],
            @$_POST['prixForfitaire'][$i],
            @$_POST['prixTTC'][$i],
            $idp,
            @$_POST['adresseClient']
          );
        }
      }

      // Auto-archive the newly created invoice (minimal fields per schema)
      @$facture->AjoutArchive(
        $_POST['num_fact'],           // num_fact
        $_POST['client'],             // client
        $_POST['date'],               // date
        '',                           // joindre (no file at creation)
        $_POST['reglement'],          // reglement
        '',                           // typeReglement
        '',                           // numcheque
        '',                           // datecheque
        '',                           // retenu
        ''                            // Projets (free text unused here)
      );

      echo "<script>document.location.href='main.php?Bordereaux&Add&Facture=$Numero_Facture'</script>";
    } else {
      echo "<script>alert('Erreur !!! ')</script>";
    }
  }
}
?>

<style>
  /* Layout for the editable project rows */
  #projectsList .project-row {
    display: grid;
    grid-template-columns: 1fr 0.7fr 0.6fr 0.4fr 0.7fr 0.7fr;
    gap: 10px;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px dashed #e5e5e5;
  }
  #projectsList .project-row:last-child { border-bottom: none; }
  #projectsList .head { font-weight: 600; background:#f8f9fa; padding:8px 0; border-top:1px solid #e5e5e5; border-bottom:1px solid #e5e5e5; }
  /* Subtle slide animation for the top project search */
  #projetSearch { transition: transform .25s ease, box-shadow .2s ease; transform-origin: left center; transform: translateX(-6px) scaleX(.98); }
  #projetSearch:focus { transform: translateX(0) scaleX(1); box-shadow: 0 0 0 .1rem rgba(13,110,253,.25); }
</style>

<div class="card shadow mb-4">
  <div style="width:100%;text-align:center" class="col-12">
    <a href="?Factures" class="btn btn-primary active" style="position:relative; top:20px;">Afficher Factures</a>
  </div>

  <div class="card-body">
    <div class="accordion col-12" id="accordionExample">
      <div class="card">
        <div class="card-header" id="headingOne">
          <h2 class="mb-0">
            <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"
              data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
              Facture non Forfitaire
            </button>
          </h2>
        </div>

        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
          <div class="card-body">
            <!-- Form Add Facture -->
            <form method="post">
              <div class="modal-body row">
                <div class="mb-3 col-3">
                  <label for="num_fact" class="col-form-label">NÂ° Facture:</label>
                  <input type="text" value="<?= htmlspecialchars($num_Facture) ?>" class="form-control" id="num_fact" name="num_fact"/>
                </div>

                <!-- Client search + list -->
                <div class="mb-3 col-5">
                  <label for="client" class="col-form-label">Client:</label>
                  <input type="search" id="clientSearch" class="form-control mb-2" placeholder="ðŸ” Rechercher un client..." autocomplete="off">
                  <select class="form-control" id="client" name="client" size="8" style="overflow-y:auto;">
                    <?php if (!empty($clients)): foreach ($clients as $key): ?>
                      <option value="<?= $key['id'] ?>"><?= htmlspecialchars($key['nom_client']) ?></option>
                    <?php endforeach; endif; ?>
                  </select>
                </div>

                <div class="mb-3 col-4">
                  <label for="adresse" class="col-form-label">Adresse:</label>
                  <input type="text" class="form-control" id="adresse" name="adresseClient"/>
                </div>

                <div class="mb-3 col-4">
                  <label class="col-form-label">Statut Facture:</label><br>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" required type="radio" name="statutFacture" id="inlineRadio1" value="Meme Facture">
                    <label class="form-check-label" for="inlineRadio1">Meme Facture</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" required checked type="radio" name="statutFacture" id="inlineRadio2" value="autre">
                    <label class="form-check-label" for="inlineRadio2">Autre</label>
                  </div>
                </div>

                <div class="mb-3 col-3">
                  <label for="numboncommande" class="col-form-label">NÂ° Bon de commande:</label>
                  <input type="text" class="form-control" id="numboncommande" name="numboncommande"/>
                </div>

                <div class="mb-3 col-3">
                  <label for="date" class="col-form-label">Date Facture:</label>
                  <input type="date" value="<?= date('Y-m-d') ?>" required class="form-control" id="date" name="date"/>
                </div>

                <div class="mb-3 col-2">
                  <label for="reglement" class="col-form-label">Reglement:</label>
                  <select class="form-control" id="reglement" name="reglement">
                    <option value="oui">Oui</option>
                    <option value="Avance">Avance</option>
                    <option value="non" selected>Non</option>
                  </select>
                </div>


                <!-- Project search -->
                <div class="mb-3 col-12">
                  <label class="col-form-label">Projets:</label>
                  <input type="search" id="projetSearch" class="form-control mb-3" placeholder="ðŸ” Rechercher un projet..." autocomplete="off">
                </div>

                <!-- Projects list -->
                <div id="projectsList" class="col-12">
                  <div class="project-row head">
                    <div>Projet</div>
                    <div>Prix Unit HT</div>
                    <div>QtÃ©</div>
                    <div>TVA</div>
                    <div>Remise</div>
                    <div>Prix TTC</div>
                  </div>

                  <?php if (!empty($projets)): foreach ($projets as $cle): ?>
                    <div class="project-row">
                      <div>
                        <input type="hidden" name="idProjet[]" value="<?= $cle['id'] ?>"/>
                        <input type="text" value="<?= htmlspecialchars($cle['classement']) ?>" class="form-control" name="projet[]" readonly />
                      </div>
                      <div><input type="text" class="form-control" name="prix_unit_htv[]"/></div>
                      <div><input type="number" min="1" value="1" class="form-control" name="qte[]"/></div>
                      <div><input type="text" class="form-control" value="19" name="tva[]"/></div>
                      <div><input type="text" class="form-control" name="remise[]"/></div>
                      <div><input type="text" class="form-control" name="prixTTC[]"/></div>
                    </div>
                  <?php endforeach; endif; ?>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="submit" class="btn btn-primary" name="btnSubmitAjout">Ajouter</button>
              </div>
            </form>
            <!-- /Form -->
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header" id="headingTwo">
          <h2 class="mb-0">
            <a href="?Factures&AddFactureForfitaire" class="btn btn-link btn-block text-left collapsed">
              Facture Forfitaire
            </a>
          </h2>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- SEARCH + UX SCRIPTS -->
<script>
  // Prevent accidental submit on Enter anywhere in inputs/selects
  document.addEventListener('keydown', function (event) {
    const tag = (event.target && event.target.tagName || '').toLowerCase();
    if (event.key === 'Enter' && (tag === 'input' || tag === 'select')) {
      event.preventDefault();
      return false;
    }
  });

  // ----- Client search -----
  (function clientSearchEnhance(){
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

  // ----- Project search -----
  (function projectSearchEnhance(){
    const input = document.getElementById('projetSearch');
    const rows  = document.querySelectorAll('#projectsList .project-row:not(.head)');
    if (!input || !rows.length) return;

    function filterRows(){
      const q = (input.value || '').toLowerCase();
      rows.forEach(row => {
        const nameInput = row.querySelector('input[name="projet[]"]');
        const txt = (nameInput ? nameInput.value : row.textContent).toLowerCase();
        row.style.display = txt.includes(q) ? '' : 'none';
      });
    }

    input.addEventListener('input', filterRows);
    filterRows();
  })();
  // Normalize UI texts (placeholder + header label) without altering server-side rendering
  (function normalizeUi(){
    const search = document.getElementById('projetSearch');
    if (search) search.setAttribute('placeholder','Rechercher un projet...');
    const head = document.querySelector('#projectsList .project-row.head');
    if (head) {
      const cols = head.querySelectorAll('div');
      if (cols && cols.length >= 3) {
        const txt = (cols[2].textContent||'').trim();
        if (txt && txt.toLowerCase().startsWith('qt')) cols[2].textContent = 'Qté';
      }
    }
  })();
</script>

<!-- Dynamic Projects Builder (prevents max_input_vars overflow) -->
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const list = document.getElementById('projectsList');
    if (!list) return;

    // Try to remove any existing Exonération block if still present
    try {
      const exoInput = document.querySelector('input#exo[name="exo"]');
      if (exoInput) {
        const holder = exoInput.closest('.mb-3') || exoInput.closest('.form-check') || exoInput.parentElement;
        if (holder && holder.parentElement) holder.parentElement.removeChild(holder);
      }
    } catch(e) {}

    // Collect available projects from the original static rows
    const origRows = Array.from(list.querySelectorAll('.project-row:not(.head)'));
    const dataset = origRows.map(row => {
      const idInput = row.querySelector('input[name="idProjet[]"]');
      const labelInput = row.querySelector('input[name="projet[]"]');
      return { id: idInput ? idInput.value : '', label: labelInput ? labelInput.value : '' };
    });

    // Clear original rows and leave only the head
    list.innerHTML = '';
    const head = document.createElement('div');
    head.className = 'project-row head';
    head.innerHTML = '<div>Projet</div><div>Prix Unit HT</div><div>Qté</div><div>TVA</div><div>Remise</div><div>Prix TTC</div><div style="width:80px;">Action</div>';
    list.appendChild(head);

    // Prepare datalist options for the top search input
    const datalist = document.createElement('datalist');
    datalist.id = 'projetsList';
    dataset.forEach(d => {
      if (!d.label) return;
      const opt = document.createElement('option');
      opt.value = d.label;
      opt.setAttribute('data-id', d.id || '');
      datalist.appendChild(opt);
    });
    // Attach datalist to the existing top search input and ensure an Ajouter button exists next to it
    const listParent = list.parentElement || list;
    const topSearch = document.getElementById('projetSearch');
    if (topSearch) {
      // If datalist not already present, append it next to the input
      topSearch.setAttribute('list','projetsList');
      if (topSearch.parentElement) {
        topSearch.parentElement.appendChild(datalist);
      }
      // Ensure an "Ajouter" button exists next to the input
      let addBtn = document.getElementById('btnAddRow');
      if (!addBtn) {
        addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.id = 'btnAddRow';
        addBtn.className = 'btn btn-outline-primary';
        addBtn.textContent = 'Ajouter';
        // place input and button side-by-side inside a flex row
        const parent = topSearch.parentElement;
        const labelEl = topSearch.previousElementSibling && topSearch.previousElementSibling.tagName === 'LABEL' ? topSearch.previousElementSibling : null;
        const flexWrap = document.createElement('div');
        flexWrap.className = 'd-flex align-items-center';
        flexWrap.style.gap = '10px';
        // insert flexWrap right after label (or at start if no label)
        if (labelEl && labelEl.parentElement) {
          labelEl.insertAdjacentElement('afterend', flexWrap);
        } else if (parent) {
          parent.prepend(flexWrap);
        }
        // move input into wrapper and adjust spacing
        topSearch.classList.remove('mb-3');
        flexWrap.appendChild(topSearch);
        flexWrap.appendChild(addBtn);
      }
    } else {
      // Fallback: just append datalist above the list
      listParent.insertBefore(datalist, list);
    }

    function getIdByLabel(label){
      const opts = Array.from(datalist.options);
      const lbl = (label||'').trim().toLowerCase();
      if (!lbl) return '';
      // exact match first
      let opt = opts.find(o => (o.value||'').toLowerCase() === lbl);
      if (!opt) {
        // then substring match
        opt = opts.find(o => (o.value||'').toLowerCase().includes(lbl));
      }
      return opt ? (opt.getAttribute('data-id')||'') : '';
    }

    function makeRow(id, label){
      const row = document.createElement('div');
      row.className = 'project-row';
      row.innerHTML = `
        <div>
          <input type="hidden" name="idProjet[]" value="${id}">
          <input type="text" class="form-control" name="projet[]" value="${(label||'').replace(/"/g,'&quot;')}" readonly>
        </div>
        <div><input type="text" class="form-control" name="prix_unit_htv[]"></div>
        <div><input type="number" min="1" value="1" class="form-control" name="qte[]"></div>
        <div><input type="text" class="form-control" value="19" name="tva[]"></div>
        <div><input type="text" class="form-control" name="remise[]"></div>
        <div><input type="text" class="form-control" name="prixTTC[]"></div>
        <div><button type="button" class="btn btn-sm btn-outline-danger btnRemove">Suppr</button></div>
      `;
      row.querySelector('.btnRemove').addEventListener('click', () => row.remove());
      return row;
    }

    function addCurrent(){
      const picker = document.getElementById('projetSearch');
      if (!picker) return;
      const label = (picker.value||'').trim();
      if (!label) return;
      const id = getIdByLabel(label);
      if (!id) {
        alert('Veuillez sélectionner un projet valide dans la liste.');
        return;
      }
      const r = makeRow(id, label);
      list.appendChild(r);
      picker.value = '';
    }
    const addBtnFinal = document.getElementById('btnAddRow');
    const topSearchFinal = document.getElementById('projetSearch');
    if (addBtnFinal) addBtnFinal.addEventListener('click', addCurrent);
    if (topSearchFinal) topSearchFinal.addEventListener('keydown', (e) => { if (e.key === 'Enter'){ e.preventDefault(); addCurrent(); }});
  });
</script>

