<?php
include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/OffresPrix.class.php';

$clients       = $clt->getAllClients();
$projets       = $projet->getAllProjets();
$offreNum      = isset($_GET['modifier']) ? $_GET['modifier'] : '';
$offreId       = isset($_GET['idoffre']) ? $_GET['idoffre'] : '';
$ProjetsOffre  = $offre->get_AllProjets_ByOffre($offreNum, $offreId);
$detailOffre   = $offre->detailOffre($offreId);
$adressesOffre = $offre->get_All_AdressesClient_ProjetsByOffre($offreNum);
$adresseChoisie = '';
// Try to capture an existing address (preferring the first non-empty)
if (!empty($adressesOffre)) {
    foreach ($adressesOffre as $row) {
        $adr = isset($row['adresseClient']) ? trim((string)$row['adresseClient']) : '';
        if ($adr !== '') { $adresseChoisie = $adr; break; }
        if ($adresseChoisie === '' && isset($row[0])) { $adresseChoisie = trim((string)$row[0]); }
    }
}
if ($adresseChoisie === '' && !empty($ProjetsOffre)) {
    $adr = isset($ProjetsOffre[0]['adresseClient']) ? trim((string)$ProjetsOffre[0]['adresseClient']) : '';
    $adresseChoisie = $adr;
}

// Modifier Offre
if (isset($_REQUEST['btnSubmitModifier'])) {
    $numOffre = $_POST['num_offre'];
    $clientId = $_POST['client'];
    $dateOffre = $_POST['date'];
    $adresseExist = isset($_POST['adresse_exist']) ? trim((string)$_POST['adresse_exist']) : '';
    $adresseNew   = isset($_POST['adresse_new']) ? trim((string)$_POST['adresse_new']) : '';
    $adresseFinal = ($adresseNew !== '') ? $adresseNew : $adresseExist;

    // Update header then replace lines for the selected adresse
    $offre->Modifier(@$numOffre, @$dateOffre, @$clientId, $offreId);
    $offre->delete_All_Projets_By_OffreAndAdresse(@$numOffre, $adresseExist);

    $ids      = isset($_POST['idProjet']) ? $_POST['idProjet'] : [];
    $puList   = isset($_POST['prix_unit_htv']) ? $_POST['prix_unit_htv'] : [];
    $qteList  = isset($_POST['qte']) ? $_POST['qte'] : [];
    $tvaList  = isset($_POST['tva']) ? $_POST['tva'] : [];
    $remList  = isset($_POST['remise']) ? $_POST['remise'] : [];
    $pfList   = isset($_POST['prixForfitaire']) ? $_POST['prixForfitaire'] : [];
    $ttcList  = isset($_POST['prixTTC']) ? $_POST['prixTTC'] : [];

    $count = count($ids);
    for ($i = 0; $i < $count; $i++) {
        $idp = isset($ids[$i]) && is_numeric($ids[$i]) ? (int)$ids[$i] : 0;
        $hasPrice = (!empty($puList[$i]) || !empty($pfList[$i]) || !empty($ttcList[$i]));
        if ($idp > 0 && $hasPrice) {
            $offre->AjoutProjets_Offre(
                @$numOffre,
                @$puList[$i],
                @$qteList[$i],
                @$tvaList[$i],
                @$remList[$i],
                @$pfList[$i],
                @$ttcList[$i],
                $idp,
                $adresseFinal
            );
        }
    }

    echo "<script>document.location.href='main.php?Offres_Prix'</script>";
    exit;
}
?>

<style>
  /* Layout for the editable project rows */
  #projectsList .project-row {
    display: grid;
    grid-template-columns: 1.3fr 0.8fr 0.55fr 0.55fr 0.7fr 0.8fr 0.8fr 0.55fr;
    gap: 10px;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px dashed #e5e5e5;
  }
  #projectsList .project-row.head {
    font-weight: 600;
    background: #f8f9fa;
    padding: 10px 0;
    border: 1px solid #e5e5e5;
  }
  #projectsList .project-row:last-child { border-bottom: none; }
</style>

<!-- DataTales Example -->
<div class="card shadow mb-4">
  <div style="width:100%;text-align:center" class="col-12">
    <a href="?Offres_Prix" class="btn btn-primary active " style="position:relative; top:20px;" >Afficher Offres de Prix</a>
  </div>
  <div class="card-body">
    <!--Form Modifier Offre-->
    <form method="post">
      <div class="modal-body row">
        <div class="mb-3  col-2">
          <label for="num_offre" class="col-form-label">Num Offre:</label>
          <input type="text" value="<?= htmlspecialchars($detailOffre['num_offre'] ?? '') ?>" class="form-control" id="num_offre" name="num_offre"/>
        </div>
        <div class="mb-3  col-4">
          <label for="client" class="col-form-label">Client:</label>
          <select class="form-control" id="client" name="client">
            <?php if(!empty($clients)){ foreach($clients as $key){ ?>
              <option value="<?=$key['id']?>" <?php if(($detailOffre['client'] ?? null)==$key['id']){echo 'selected';}?>><?=htmlspecialchars($key['nom_client'])?></option>
            <?php }} ?>
          </select>
        </div>
        <div class="mb-3  col-3">
          <label for="date" class="col-form-label">Date Offre:</label>
          <input type="date" value="<?= htmlspecialchars($detailOffre['date'] ?? '') ?>" class="form-control" id="date" name="date"/>
        </div>
        <div class="mb-3  col-3">
          <label for="adresse_exist" class="col-form-label">Adresse enregistrée:</label>
          <select class="form-control" id="adresse_exist" name="adresse_exist">
            <?php if(!empty($adressesOffre)){ foreach($adressesOffre as $adrRow){ 
              $adrVal = isset($adrRow['adresseClient']) ? $adrRow['adresseClient'] : (isset($adrRow[0]) ? $adrRow[0] : '');
              $adrVal = trim((string)$adrVal);
              ?>
              <option value="<?= htmlspecialchars($adrVal) ?>" <?php if($adrVal === $adresseChoisie){echo 'selected';}?>><?= $adrVal !== '' ? htmlspecialchars($adrVal) : 'Adresse vide' ?></option>
            <?php }} else { ?>
              <option value="" selected>Adresse vide</option>
            <?php } ?>
          </select>
        </div>
        <div class="mb-3  col-3">
          <label for="adresse_new" class="col-form-label">Nouvelle adresse (optionnel):</label>
          <input type="text" value="<?=htmlspecialchars($adresseChoisie)?>" class="form-control" id="adresse_new" name="adresse_new" placeholder="Saisir la nouvelle adresse"/>
        </div>

        <!-- Project search + add -->
        <div class="mb-3 col-12">
          <label class="col-form-label">Projets:</label>
          <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
            <input type="search" id="projetSearch" class="form-control" list="projetsList" placeholder="Rechercher un projet..." autocomplete="off" style="max-width:400px;">
            <datalist id="projetsList">
              <?php if (!empty($projets)) { foreach ($projets as $cle) { ?>
                <option value="<?= htmlspecialchars($cle['classement']) ?>" data-id="<?= htmlspecialchars((string)$cle['id']) ?>"></option>
              <?php }} ?>
            </datalist>
            <button type="button" class="btn btn-outline-primary" id="btnAddRow">Ajouter projet</button>
          </div>
        </div>

        <!-- Dynamic project lines -->
        <div id="projectsList" class="col-12">
          <div class="project-row head">
            <div>Projet</div>
            <div>Prix Unit HT</div>
            <div>Qte</div>
            <div>TVA</div>
            <div>Remise</div>
            <div>Prix Forfitaire</div>
            <div>Prix TTC</div>
            <div>Action</div>
          </div>
          <?php if (!empty($ProjetsOffre)) { foreach ($ProjetsOffre as $p) { ?>
            <?php
              $label = isset($p['classement']) && $p['classement'] !== '' ? $p['classement'] : (isset($p['projet']) ? $p['projet'] : '');
            ?>
            <div class="project-row">
              <div>
                <input type="hidden" name="idProjet[]" value="<?= htmlspecialchars($p['projet']) ?>">
                <input type="text" class="form-control" name="projet[]" value="<?= htmlspecialchars($label) ?>" readonly>
              </div>
              <div><input type="text" class="form-control" name="prix_unit_htv[]" value="<?= htmlspecialchars($p['prix_unit_htv']) ?>"></div>
              <div><input type="number" min="1" class="form-control" name="qte[]" value="<?= htmlspecialchars($p['qte']) ?>"></div>
              <div><input type="text" class="form-control" name="tva[]" value="<?= htmlspecialchars($p['tva']) ?>"></div>
              <div><input type="text" class="form-control" name="remise[]" value="<?= htmlspecialchars($p['remise']) ?>"></div>
              <div><input type="text" class="form-control" name="prixForfitaire[]" value="<?= htmlspecialchars($p['prixForfitaire']) ?>"></div>
              <div><input type="text" class="form-control" name="prixTTC[]" value="<?= htmlspecialchars($p['prixTTC']) ?>"></div>
              <div><button type="button" class="btn btn-sm btn-outline-danger btnRemove">Suppr</button></div>
            </div>
          <?php }} ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        <button type="submit" class="btn btn-primary" name="btnSubmitModifier">Modifier</button>
      </div>
    </form>
    <!--Fin Form Modifier Offre-->
  </div>
</div>

<script>
  // Prevent Enter from submitting the form while building lines
  document.addEventListener('keydown', function (event) {
    const tag = (event.target && event.target.tagName || '').toLowerCase();
    if (event.key === 'Enter' && (tag === 'input' || tag === 'select')) {
      if (event.target && event.target.id === 'projetSearch') {
        event.preventDefault();
        return false;
      }
    }
  });

  // Dynamic projects builder for Offre de prix (modifier)
  document.addEventListener('DOMContentLoaded', function(){
    const list = document.getElementById('projectsList');
    const search = document.getElementById('projetSearch');
    const addBtn = document.getElementById('btnAddRow');
    const datalist = document.getElementById('projetsList');
    const adrSelect = document.getElementById('adresse_exist');
    const adrInput  = document.getElementById('adresse_new');
    if (!list || !search || !addBtn || !datalist) return;

    // Keep "nouvelle adresse" in sync with the currently selected saved address,
    // but let the user overwrite it.
    if (adrSelect && adrInput) {
      adrSelect.addEventListener('change', function(){
        if (!adrInput.dataset.userEdited || adrInput.value === '') {
          adrInput.value = this.value;
        }
      });
      adrInput.addEventListener('input', function(){
        this.dataset.userEdited = '1';
      });
    }

    // Wire remove buttons on pre-rendered rows
    list.querySelectorAll('.btnRemove').forEach(btn => {
      btn.addEventListener('click', () => {
        const row = btn.closest('.project-row');
        if (row && !row.classList.contains('head')) row.remove();
      });
    });

    function getIdByLabel(label){
      const lbl = (label || '').toLowerCase();
      const opts = Array.from(datalist.options || []);
      if (!lbl) return '';
      // exact match first
      let opt = opts.find(o => (o.value || '').toLowerCase() === lbl);
      if (!opt) {
        opt = opts.find(o => (o.value || '').toLowerCase().includes(lbl));
      }
      return opt ? (opt.getAttribute('data-id') || '') : '';
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
        <div><input type="text" class="form-control" name="prixForfitaire[]"></div>
        <div><input type="text" class="form-control" name="prixTTC[]"></div>
        <div><button type="button" class="btn btn-sm btn-outline-danger btnRemove">Suppr</button></div>
      `;
      const rm = row.querySelector('.btnRemove');
      if (rm) rm.addEventListener('click', () => row.remove());
      return row;
    }

    function addCurrent(){
      const label = (search.value || '').trim();
      if (!label) return;
      const id = getIdByLabel(label);
      if (!id) {
        alert('Veuillez selectionner un projet valide dans la liste.');
        return;
      }
      const r = makeRow(id, label);
      list.appendChild(r);
      search.value = '';
      search.focus();
    }

    addBtn.addEventListener('click', addCurrent);
    search.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        addCurrent();
      }
    });
  });
</script>
