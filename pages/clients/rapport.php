<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

<?php
include_once '../../class/client.class.php';
include_once '../../class/Factures.class.php';

$clients = $clt->getAllClients();
$yearFilter = (isset($_GET['year']) && $_GET['year'] !== '')
  ? preg_replace('/[^0-9]/', '', $_GET['year'])
  : null;
?>

<!-- Year Filter -->
<form method="get" class="mb-3" style="width:95%;margin:10px auto;">
  <div class="form-inline">
    <label for="year" class="mr-2">Année</label>
    <input type="number" id="year" name="year" class="form-control mr-2" min="2000" max="2100"
           value="<?= $yearFilter !== null ? htmlspecialchars($yearFilter) : '' ?>">
    <button class="btn btn-primary btn-sm" type="submit">Filter</button>
    <a href="rapport.php" class="btn btn-secondary btn-sm ml-2">Reset</a>
  </div>
  <?php if ($yearFilter !== null): ?>
    <small class="text-muted">Filtered by year: <?= htmlspecialchars($yearFilter) ?></small>
  <?php endif; ?>
</form>

<table border="2" style="border:black 1px solid" width="95%" align="center" height="100%" cellspacing="0">
  <thead>
    <tr bgcolor="grey" class="text-center">
      <th width="25%">Client</th>
      <th width="10%">Année</th>
      <th>Projets</th>
      <th width="25%">Observation</th>
    </tr>
  </thead>

  <tbody>
  <?php
    $printed = 0;

    if (!empty($clients)) {
      foreach ($clients as $key) {
        $projetsClient        = $clt->RapportClient($key['id']);
        $projetsClientArchive = $clt->RapportClientArchive($key['id']);
        $items = [];

        // Live factures
        if (!empty($projetsClient)) {
          foreach ($projetsClient as $p) {
            $d = $p['date'] ?? '';
            $y = $d ? @date('Y', strtotime($d)) : '';
            if ($yearFilter && $y !== $yearFilter) continue;
            $items[] = [
              'date'  => $d,
              'year'  => $y,
              'label' => trim(strtolower($p['classement'] ?? ''))
            ];
          }
        }

        // Archives
        if (!empty($projetsClientArchive)) {
          foreach ($projetsClientArchive as $pA) {
            $d = $pA['date'] ?? '';
            $y = $d ? @date('Y', strtotime($d)) : '';
            if ($yearFilter && $y !== $yearFilter) continue;
            $items[] = [
              'date'  => $d,
              'year'  => $y,
              'label' => trim(strtolower($pA['Projets'] ?? ''))
            ];
          }
        }

        // Skip if nothing for this client
        if (empty($items)) continue;

        // Group duplicates by label
        $grouped = [];
        foreach ($items as $it) {
          $label = $it['label'] ?: '(sans nom)';
          if (!isset($grouped[$label])) {
            $grouped[$label] = ['count' => 0, 'dates' => [], 'year' => $it['year']];
          }
          $grouped[$label]['count']++;
          if (!empty($it['date'])) $grouped[$label]['dates'][] = $it['date'];
        }

        $printed++;
        ?>
        <tr>
          <td><b><?= htmlspecialchars((string)$key['nom_client']) ?></b></td>
          <td class="text-center">
            <?= htmlspecialchars($yearFilter ?? (reset($grouped)['year'] ?? '')) ?>
          </td>
          <td>
            <?php
              foreach ($grouped as $label => $info) {
                $count = $info['count'];
                $dates = '';
                if (!empty($info['dates'])) {
                  $uniqueDates = array_unique($info['dates']);
                  $dates = ' dans les dates : ';
                  foreach ($uniqueDates as $d) {
                    $dates .= '(' . htmlspecialchars($d) . ')';
                  }
                }
                echo "{$count}× " . htmlspecialchars(ucfirst($label)) . $dates . "<br>";
              }
            ?>
          </td>
          <td></td>
        </tr>
        <?php
      } // foreach clients
    } // if clients

    if ($printed === 0): ?>
      <tr><td colspan="4" class="text-center text-muted">Aucun résultat pour l’année choisie.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
