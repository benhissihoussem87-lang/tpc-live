<?php
include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/OffresPrix.class.php';
$clients = $clt->getAllClients();
$projets = $projet->getAllProjets();
$offres  = $offre->AfficherOffres();

// Genere le prochain numero d'offre simple <seq>/<annee>
$anne = date('Y');
if ($offres) {
  $nb       = count($offres);
  $numOffre = ((intval($nb + 1)) < 10 ? '0' : '') . intval($nb + 1) . '/' . $anne;
} else {
  $numOffre = '01/' . $anne;
}
?>

<?php
// Ajout
if (isset($_REQUEST['btnSubmitAjout'])) {

  // Ajout Offre de Prix
  if ($offre->Ajout(@$_POST['num_offre'], @$_POST['date'], @$_POST['client'])) {

    // Ajout Projet Offres
    for ($i = 0; $i < count($_POST['projet']); $i++) {
      if (!empty($_POST['prix_unit_htv'][$i]) || !empty($_POST['prixForfitaire'][$i]) || !empty($_POST['prixTTC'][$i])) {
        $offre->AjoutProjets_Offre(
          @$_POST['num_offre'],
          @$_POST['prix_unit_htv'][$i],
          @$_POST['qte'][$i],
          @$_POST['tva'][$i],
          @$_POST['remise'][$i],
          @$_POST['prixForfitaire'][$i],
          @$_POST['prixTTC'][$i],
          @$_POST['idProjet'][$i],
          $_POST['adresse']
        );
      }
    }

    echo "<script>document.location.href='main.php?Offres_Prix'</script>";
  } else {
    echo "<script>alert('Erreur !!! ')</script>";
  }
}
?>
<!--  Detail ****-->

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
    <a href="?Offres_Prix" class="btn btn-primary active " style="position:relative; top:20px;">Afficher Offres de Prix</a>
  </div>
  <div class="card-body">
    <!--Form Add Offre-->
    <form method="post">
      <div class="modal-body row">
        <div class="mb-3 col-3">
          <label for="num_offre" class="col-form-label">Num Offre:</label>
          <input type="text" required class="form-control" id="num_offre" name="num_offre" value="<?= htmlspecialchars($numOffre ?? '') ?>"/>
        </div>
        <div class="mb-3 col-5">
          <label for="client" class="col-form-label">Client:</label>
          <select class="form-control" id="client" name="client">
          <?php if (!empty($clients)) { foreach ($clients as $key) { ?>
            <option value="<?= $key['id'] ?>"><?= htmlspecialchars($key['nom_client']) ?></option>
          <?php }} ?>
          </select>
        </div>
        <div class="mb-3 col-4">
          <label for="date" class="col-form-label">Date Offre:</label>
          <input type="date" value="<?= date('Y-m-d') ?>" class="form-control" id="date" name="date"/>
        </div>
        <div class="mb-3 col-4">
          <label for="adresse" class="col-form-label">Adresse:</label>
          <input type="text" class="form-control" id="adresse" name="adresse"/>
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
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        <button type="submit" class="btn btn-primary" name="btnSubmitAjout">Ajouter</button>
      </div>
    </form>
    <!--Fin Form Add Facture-->
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

  // Dynamic projects builder for Offre de prix
  document.addEventListener('DOMContentLoaded', function(){
    const list = document.getElementById('projectsList');
    const search = document.getElementById('projetSearch');
    const addBtn = document.getElementById('btnAddRow');
    const datalist = document.getElementById('projetsList');
    if (!list || !search || !addBtn || !datalist) return;

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
