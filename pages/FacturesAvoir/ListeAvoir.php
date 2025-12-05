<?php
include_once 'class/Avoir.class.php';

// Delete action
if (isset($_GET['Delete'])) {
    $id = (int)$_GET['Delete'];
    if ($avoir->delete($id)) {
        echo "<script>document.location.href='main.php?Avoir'</script>";
        exit;
    }
}

$list = $avoir->getAll();
$selectedYear = '';
if (!empty($_GET['year']) && preg_match('/^\d{4}$/', $_GET['year'])) {
    $selectedYear = $_GET['year'];
}
$avoirYears = [];
if (!empty($list)) {
    foreach ($list as $row) {
        $year = isset($row['date_avoir']) ? substr((string)$row['date_avoir'], 0, 4) : '';
        if (preg_match('/^\d{4}$/', $year)) {
            $avoirYears[$year] = true;
        }
    }
}
$avoirYears = array_keys($avoirYears);
rsort($avoirYears, SORT_STRING);
if ($selectedYear !== '') {
    $list = array_values(array_filter($list, function ($row) use ($selectedYear) {
        $date = isset($row['date_avoir']) ? (string)$row['date_avoir'] : '';
        $num  = isset($row['num_avoir']) ? (string)$row['num_avoir'] : '';
        if (strpos($date, $selectedYear) === 0) {
            return true;
        }
        if (strpos($num, '/'.$selectedYear) !== false || strpos($num, $selectedYear.'/') === 0) {
            return true;
        }
        return false;
    }));
}
?>

<div class="card shadow mb-4">
  <div style="width:100%;text-align:center" class="col-12 mb-3">
    <a href="?Avoir&Add" class="btn btn-primary mr-2">Ajouter Avoir (détaill├®)</a>
    <a href="?Avoir&AddForfaitaire" class="btn btn-outline-primary">Ajouter Avoir Forfaitaire</a>
  </div>
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-md-3">
        <label for="avoirYearFilter" class="form-label">Filtrer par année</label>
        <select id="avoirYearFilter" class="form-control">
          <option value="">Toutes les ann├®es</option>
          <?php foreach ($avoirYears as $year): ?>
            <option value="<?= htmlspecialchars($year) ?>" <?= $selectedYear === $year ? 'selected' : '' ?>><?= htmlspecialchars($year) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="button" class="btn btn-secondary w-100" id="avoirYearApply">Filtrer</button>
      </div>
      <div class="col-md-4 ml-md-auto mt-3 mt-md-0">
        <label for="avoirSearch" class="form-label">Recherche rapide</label>
        <input
          type="search"
          class="form-control"
          id="avoirSearch"
          placeholder="Rechercher un avoir"
          data-table-search="#dataTable"
        >
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" data-year-filter="#avoirYearFilter" data-year-column="1" data-order-column="1" data-order-direction="asc">
        <thead>
          <tr>
            <th>Num Avoir</th>
            <th>Date</th>
            <th>Client</th>
            <th>Facture Origine</th>
            <th>Nouvelle Facture</th>
            <th>Total HT</th>
            <th>Total TVA</th>
            <th>Total TTC</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($list)) { foreach ($list as $row) {
          $avoirSort = 0;
          $avoirYear = '';
          if (!empty($row['num_avoir'])) {
              $parts = explode('/', $row['num_avoir']);
              $numero = isset($parts[0]) ? (int)$parts[0] : 0;
              $annee  = isset($parts[1]) ? (int)$parts[1] : 0;
              $avoirSort = ($annee * 10000) + $numero;
              if ($annee > 0) {
                  $avoirYear = (string)$annee;
              }
          }
          $dateYear = '';
          if (!empty($row['date_avoir'])) {
              $maybeYear = substr((string)$row['date_avoir'], 0, 4);
              if (preg_match('/^\d{4}$/', $maybeYear)) {
                  $dateYear = $maybeYear;
              }
          }
          $yearTokens = array_unique(array_filter([$avoirYear, $dateYear]));
          $rowYearAttr = implode(' ', $yearTokens);
        ?>
          <tr<?php if (!empty($rowYearAttr)) { ?> data-year-values="<?= htmlspecialchars($rowYearAttr) ?>"<?php } ?>>
            <td data-order="<?= $avoirSort ?>"><?= htmlspecialchars($row['num_avoir']) ?></td>
            <td><?php if ($rowYearAttr !== ''): ?><span class="d-none year-marker"><?= htmlspecialchars($rowYearAttr) ?></span><?php endif; ?><?= htmlspecialchars($row['date_avoir']) ?></td>
            <td><?= htmlspecialchars($row['nom_client']) ?></td>
            <td><?= htmlspecialchars($row['num_fact'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['num_facture_nouveux'] ?? ($row['num_fact_new'] ?? '')) ?></td>
            <td><?= number_format((float)$row['total_ht'],3,'.','') ?></td>
            <td><?= number_format((float)$row['total_tva'],3,'.','') ?></td>
            <td><?= number_format((float)$row['total_ttc'],3,'.','') ?></td>
            <td style="white-space:nowrap">
              <a class="btn btn-sm btn-warning" href="?Avoir&Modifier&id=<?= (int)$row['id'] ?>">Modifier</a>
              <a class="btn btn-sm btn-success" href="pages/FacturesAvoir/ModeleAvoir.php?id=<?= (int)$row['id'] ?>" target="_blank">Imprimer</a>
              <a class="btn btn-sm btn-danger" href="?Avoir&Delete=<?= (int)$row['id'] ?>" onclick="return confirm('Supprimer cet avoir ?');">Supprimer</a>
            </td>
          </tr>
        <?php }} ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
(function(){
  var btn=document.getElementById('avoirYearApply');
  if(btn){
    btn.addEventListener('click', function(){
      var select=document.getElementById('avoirYearFilter');
      var year=select?select.value:'';
      var url=new URL(window.location.href);
      if(year){
        url.searchParams.set('year',year);
      }else{
        url.searchParams.delete('year');
      }
      window.location.href=url.toString();
    });
  }
})();

// Hook custom search input to DataTables (or fallback)
document.addEventListener('DOMContentLoaded', function(){
  var input = document.getElementById('avoirSearch');
  if (!input) return;
  var tableSelector = input.getAttribute('data-table-search') || '#dataTable';
  var $table = window.jQuery ? jQuery(tableSelector) : null;

  function apply() {
    var term = (input.value || '').trim();
    if ($table && $table.length && $table.DataTable) {
      var dt = $table.DataTable();
      dt.search(term).draw();
      return;
    }
    var table = document.querySelector(tableSelector);
    if (!table) return;
    var lower = term.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(function(tr){
      var txt = (tr.innerText || '').toLowerCase();
      tr.style.display = lower ? (txt.indexOf(lower) !== -1 ? '' : 'none') : '';
    });
  }

  input.addEventListener('input', apply);
  input.addEventListener('keydown', function(e){
    if (e.key === 'Enter') {
      e.preventDefault();
      apply();
    }
  });
});
</script>




