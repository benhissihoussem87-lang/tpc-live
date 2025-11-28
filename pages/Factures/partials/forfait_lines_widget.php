<?php
/**
 * Forfaitaire lines widget — ultra simple:
 * 1) Click "Ajouter adresse" to create a block.
 * 2) In the block, type/select a projet + prix, click "Ajouter projet" to just add a row (no save/reload).
 * 3) Repeat for more projects or addresses; everything posts on form submit via hidden inputs lignes[][...].
 */

if (!isset($forfaitLinesWidgetId) || $forfaitLinesWidgetId === '') {
    $forfaitLinesWidgetId = 'forfaitLinesWidget';
}

$projectsOptions = [];
if (!empty($projets)) {
    foreach ($projets as $proj) {
        $projectsOptions[] = [
            'id'    => (int)($proj['id'] ?? 0),
            'label' => (string)($proj['classement'] ?? $proj['nom'] ?? ('Projet '.($proj['id'] ?? '')))
        ];
    }
}
$projectsJson = htmlspecialchars(json_encode($projectsOptions, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE), ENT_QUOTES);
$prefillJson  = htmlspecialchars(json_encode($forfaitLinesPrefill ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP), ENT_QUOTES);
?>

<style>
.fw-block .addr-card{border:1px solid #e5e5e5;border-radius:8px;padding:12px;background:#fafafa;margin-bottom:12px;}
.fw-block .addr-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;}
.fw-block .addr-title{margin:0;font-weight:700;text-transform:uppercase;}
.fw-block table td{vertical-align:middle;}
</style>

<div id="<?= $forfaitLinesWidgetId ?>" class="fw-block" data-projets="<?= $projectsJson ?>" data-initial="<?= $prefillJson ?>">
  <div class="mb-3">
    <label class="form-label" for="<?= $forfaitLinesWidgetId ?>AddrInput">Adresse</label>
    <div class="input-group">
      <input type="text" class="form-control" id="<?= $forfaitLinesWidgetId ?>AddrInput" placeholder="Ex: chantier / site / adresse">
      <button type="button" class="btn btn-outline-primary" id="<?= $forfaitLinesWidgetId ?>AddrAdd">Ajouter adresse</button>
    </div>
  </div>

  <div id="<?= $forfaitLinesWidgetId ?>List">
    <div class="alert alert-secondary mb-0">Aucune adresse/projet pour le moment.</div>
  </div>
</div>

<datalist id="<?= $forfaitLinesWidgetId ?>Projects">
  <?php foreach ($projectsOptions as $opt) { ?>
    <option value="<?= htmlspecialchars($opt['label'], ENT_QUOTES) ?>" data-id="<?= (int)$opt['id'] ?>"><?= htmlspecialchars($opt['label'], ENT_QUOTES) ?></option>
  <?php } ?>
</datalist>

<script>
(function(){
  var root = document.getElementById('<?= $forfaitLinesWidgetId ?>');
  if (!root) return;
  var addrInput = document.getElementById('<?= $forfaitLinesWidgetId ?>AddrInput');
  var addrBtn   = document.getElementById('<?= $forfaitLinesWidgetId ?>AddrAdd');
  var list      = document.getElementById('<?= $forfaitLinesWidgetId ?>List');
  var projects  = [];
  var lineId = 0; // unique index for lignes[<id>][...]
  try { projects = JSON.parse(root.getAttribute('data-projets')||'[]'); } catch(e){ projects=[]; }

  function escapeHtml(str){ return (str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

  function findProject(label){
    var q = (label||'').toLowerCase().trim();
    if (!q) return null;
    var p = projects.find(function(x){ return (x.label||'').toLowerCase() === q; });
    if (!p) p = projects.find(function(x){ return (x.label||'').toLowerCase().includes(q); });
    return p || null;
  }

  function ensureAlert(){
    if (!list.children.length) {
      var al = document.createElement('div');
      al.className = 'alert alert-secondary mb-0';
      al.textContent = 'Aucune adresse/projet pour le moment.';
      list.appendChild(al);
    }
  }
  function clearAlert(){
    if (list.firstElementChild && list.firstElementChild.classList.contains('alert')) {
      list.removeChild(list.firstElementChild);
    }
  }

  function makeCard(address){
    var card = document.createElement('div');
    card.className = 'addr-card';
    card.setAttribute('data-address', address);
    card.innerHTML = ''+
      '<div class="addr-header">'+
        '<h6 class="addr-title">'+escapeHtml(address)+'</h6>'+
        '<button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-addr">&times;</button>'+
      '</div>'+
      '<div class="row g-2 align-items-end mb-2">'+
        '<div class="col-md-6">'+
          '<label class="form-label">Projet</label>'+
          '<input type="search" list="<?= $forfaitLinesWidgetId ?>Projects" class="form-control" data-field="projet" placeholder="Rechercher ou saisir un projet">'+
        '</div>'+
        '<div class="col-md-3">'+
          '<label class="form-label">Prix forfaitaire (H.T)</label>'+
          '<input type="number" step="0.001" class="form-control" data-field="prix" placeholder="0.000">'+
        '</div>'+
        '<div class="col-md-3 d-flex">'+
          '<button type="button" class="btn btn-outline-primary w-100" data-action="add-projet" style="margin-top:28px;">Ajouter projet</button>'+
        '</div>'+
      '</div>'+
      '<div class="table-responsive mb-0">'+
        '<table class="table table-bordered table-sm mb-0">'+
          '<thead><tr><th style="width:65%;">Projet</th><th style="width:25%;">Prix forfaitaire</th><th style="width:10%;">Action</th></tr></thead>'+
          '<tbody><tr class="empty-row"><td colspan="3" class="text-center text-muted">Aucun projet pour cette adresse.</td></tr></tbody>'+
        '</table>'+
      '</div>';
    return card;
  }

  function addAddress(){
    var adr = (addrInput ? addrInput.value : '').trim();
    if (!adr) { alert('Saisissez une adresse.'); return; }
    clearAlert();
    var card = makeCard(adr);
    list.appendChild(card);
    if (addrInput) addrInput.value = '';
  }

  function addProject(card){
    var projInput = card.querySelector('input[data-field="projet"]');
    var prixInput = card.querySelector('input[data-field="prix"]');
    var tbody = card.querySelector('tbody');
    var address = card.getAttribute('data-address') || '';
    var projLabel = (projInput ? projInput.value : '').trim();
    var prix = (prixInput ? prixInput.value : '').trim();
    if (!projLabel && !prix) return; // allow adding only when something typed

    if (tbody && tbody.querySelector('.empty-row')) tbody.innerHTML = '';
    var id = lineId++;
    var tr = document.createElement('tr');
    tr.innerHTML = ''+
      '<td><input type="text" class="form-control form-control-sm" name="lignes['+id+'][projet]" placeholder="Projet" value="'+escapeHtml(projLabel)+'"></td>'+
      '<td><input type="number" step="0.001" class="form-control form-control-sm" name="lignes['+id+'][prix]" placeholder="0.000" value="'+escapeHtml(prix)+'"></td>'+
      '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-projet">&times;</button></td>'+
      '<input type="hidden" name="lignes['+id+'][adresse]" value="'+escapeHtml(address)+'">';
    tbody.appendChild(tr);
    if (projInput) projInput.value = '';
    if (prixInput) prixInput.value = '';
  }

  if (addrBtn) addrBtn.addEventListener('click', addAddress);

  list.addEventListener('click', function(e){
    var btn = e.target.closest('button');
    if (!btn) return;
    var action = btn.getAttribute('data-action');
    if (action === 'add-projet') {
      var card = btn.closest('.addr-card');
      if (card) addProject(card);
      return;
    }
    if (action === 'remove-projet') {
      var row = btn.closest('tr');
      if (!row) return;
      var tbody = row.parentElement;
      row.remove();
      if (tbody && !tbody.querySelector('tr')) {
        var empty = document.createElement('tr');
        empty.className = 'empty-row';
        empty.innerHTML = '<td colspan="3" class="text-center text-muted">Aucun projet pour cette adresse.</td>';
        tbody.appendChild(empty);
      }
      return;
    }
    if (action === 'remove-addr') {
      var card = btn.closest('.addr-card');
      if (card) card.remove();
      ensureAlert();
    }
  });

  var initial = [];
  try { initial = JSON.parse(root.getAttribute('data-initial') || '[]'); } catch(e){ initial=[]; }
  if (Array.isArray(initial) && initial.length) {
    clearAlert();
    var byAddr = {};
    initial.forEach(function(line){
      var adr = line.adresse || '';
      if (!byAddr[adr]) byAddr[adr] = [];
      byAddr[adr].push(line);
    });
    Object.keys(byAddr).forEach(function(adr){
      var card = makeCard(adr);
      list.appendChild(card);
      var tbody = card.querySelector('tbody');
      tbody.innerHTML = '';
      byAddr[adr].forEach(function(line){
        var id = lineId++;
        var tr = document.createElement('tr');
        tr.innerHTML = ''+
          '<td><input type="text" class="form-control form-control-sm" name="lignes['+id+'][projet]" value="'+escapeHtml(line.label || line.projet || line.projet_id || '')+'"></td>'+
          '<td><input type="number" step="0.001" class="form-control form-control-sm" name="lignes['+id+'][prix]" value="'+escapeHtml(line.prix || line.prixForfitaire || '')+'"></td>'+
          '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-projet">&times;</button></td>'+
          '<input type="hidden" name="lignes['+id+'][adresse]" value="'+escapeHtml(adr)+'">';
        tbody.appendChild(tr);
      });
    });
  }
})();
</script>
