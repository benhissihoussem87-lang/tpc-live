<?php
// pages/Factures/Archive/ArchivesFacture.php

// ---------- Robust loader (handles .class.php / .class / .php) ----------
function include_class_file(string $name): void {
  $root = realpath(__DIR__ . '/../../../');              // -> C:\xampp\htdocs\tpc
  if ($root === false) { die("Root path resolution failed."); }
  $classDir = $root . DIRECTORY_SEPARATOR . 'class';

  $candidates = [
    $classDir . DIRECTORY_SEPARATOR . $name . '.class.php',
    $classDir . DIRECTORY_SEPARATOR . $name . '.class',
    $classDir . DIRECTORY_SEPARATOR . $name . '.php',
  ];

  foreach ($candidates as $file) {
    if (is_file($file)) { require_once $file; return; }
  }
  // last try with case-insensitive scan (Windows should be fine, but just in case)
  $dir = @opendir($classDir);
  if ($dir) {
    while (($f = readdir($dir)) !== false) {
      if (preg_match('/^' . preg_quote($name, '/') . '\.(class\.php|class|php)$/i', $f)) {
        closedir($dir);
        require_once $classDir . DIRECTORY_SEPARATOR . $f;
        return;
      }
    }
    closedir($dir);
  }
  die("Unable to load class file for {$name} from {$classDir}");
}

// ---------- Includes ----------
include_class_file('client');
include_class_file('Projet');
include_class_file('Factures');
include_class_file('OffresPrix');

// ---------- Ensure instances (in case globals aren’t created in class files) ----------
if (!isset($clt)    || !($clt instanceof Clients))   { $clt    = new Clients(); }
if (!isset($projet) || !($projet instanceof Projet)) { $projet = new Projet(); }
// Use OffresPrix (actual class name)
if (!isset($offre)  || !($offre instanceof OffresPrix))  { $offre  = new OffresPrix(); }
if (!isset($facture)|| !($facture instanceof Factures)) { $facture = new Factures(); }

// ---------- Data for selects & numbering ----------
$clients  = $clt->getAllClients();
$projets  = $projet->getAllProjets();

// Existing archived invoices (for next number display)
$facturesArchives = $facture->AfficherFacturesArchives();
$offres           = $offre->AfficherOffres();

$anne = date('Y');
if (!empty($facturesArchives)) {
  $nb = count($facturesArchives);
  $numFacture = ((intval($nb + 1)) < 10 ? '0' : '') . intval($nb + 1) . '/' . $anne;
} else {
  $numFacture = '01/' . $anne;
}
if (!empty($offres)) {
  $nb = count($offres);
  $numOffre = ((intval($nb + 1)) < 10 ? '0' : '') . intval($nb + 1) . '/' . $anne;
} else {
  $numOffre = '01/' . $anne;
}

// ---------- Handle form submit ----------
if (isset($_POST['btnSubmitAjout'])) {
  $num_fact  = isset($_POST['num_fact']) ? trim($_POST['num_fact']) : '';
  $client    = isset($_POST['client']) ? (int)$_POST['client'] : 0;
  $date      = isset($_POST['date']) ? $_POST['date'] : null;

  $reglement     = isset($_POST['reglement'])     ? trim($_POST['reglement'])     : '';
  $typereglement = isset($_POST['typereglement']) ? trim($_POST['typereglement']) : '';
  $numcheque     = isset($_POST['numcheque'])     ? trim($_POST['numcheque'])     : '';
  $datecheque    = isset($_POST['datecheque'])    ? trim($_POST['datecheque'])    : '';
  $retenu        = isset($_POST['retenu'])        ? trim($_POST['retenu'])        : '';
  $projetsPayload = ''; // free text column if needed later

  if ($num_fact === '' || $client <= 0 || empty($date)) {
    echo "<script>alert('Veuillez remplir N° Facture, Client et Date.');</script>";
  } else {
    // upload (optional)
    $uploadedName = '';
    if (!empty($_FILES['facture']['name']) && is_uploaded_file($_FILES['facture']['tmp_name'])) {
      $safeBase     = basename($_FILES['facture']['name']);
      $targetDirAbs = realpath(__DIR__ . '/../../Factures/Factures_Archive');
      if ($targetDirAbs === false) {
        $targetDirAbs = __DIR__ . '/../../Factures/Factures_Archive';
        @mkdir($targetDirAbs, 0775, true);
      }
      $targetAbs = $targetDirAbs . DIRECTORY_SEPARATOR . $safeBase;
      if (@move_uploaded_file($_FILES['facture']['tmp_name'], $targetAbs)) {
        $uploadedName = $safeBase;
      } else {
        echo "<script>alert('Le fichier n\\'a pas pu être téléversé.');</script>";
      }
    }

    $ok = $facture->AjoutArchive(
      $num_fact,
      $client,
      $date,
      $uploadedName,
      $reglement,
      $typereglement,
      $numcheque,
      $datecheque,
      $retenu,
      $projetsPayload
    );

    if ($ok) {
      echo "<script>document.location.href='main.php?ArchivesFacture&Afficher'</script>";
      exit;
    } else {
      echo "<script>alert('Erreur lors de l\\'ajout dans l\\'archive.');</script>";
    }
  }
}
?>
<!-- UI -->
<div class="card shadow mb-4">
  <div style="width:100%;text-align:center" class="col-12">
    <a href="?ArchivesFacture&Afficher" class="btn btn-primary active" style="position:relative; top:20px;">
      Afficher Archive Factures
    </a>
  </div>

  <div class="card-body">
    <div class="accordion col-12" id="accordionExample">
      <div class="card">
        <div class="card-header" id="headingOne">
          <h2 class="mb-0">
            <button class="btn btn-link btn-block text-left" type="button"
                    data-toggle="collapse" data-target="#collapseOne"
                    aria-expanded="true" aria-controls="collapseOne">
              Archive Facture
            </button>
          </h2>
        </div>

        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
          <div class="card-body">
            <form method="post" enctype="multipart/form-data">
              <div class="modal-body row">
                <div class="mb-3 col-3">
                  <label for="num_fact" class="col-form-label">N° Facture:</label>
                  <input type="text" value="<?= htmlspecialchars($numFacture) ?>"
                         readonly class="form-control" id="num_fact" name="num_fact"/>
                </div>

                <div class="mb-3 col-3">
                  <label for="client" class="col-form-label">Client:</label>
                  <select class="form-control" id="client" name="client" required>
                    <?php if (!empty($clients)): foreach ($clients as $key): ?>
                      <option value="<?= (int)$key['id'] ?>"><?= htmlspecialchars($key['nom_client']) ?></option>
                    <?php endforeach; endif; ?>
                  </select>
                </div>

                <div class="mb-3 col-3">
                  <label for="date" class="col-form-label">Date Facture:</label>
                  <input type="date" value="<?= date('Y-m-d') ?>" required class="form-control" id="date" name="date"/>
                </div>

                <div class="mb-3 col-3">
                  <label for="facture" class="col-form-label">Joindre Facture:</label>
                  <input type="file" class="form-control" id="facture" name="facture" />
                </div>

                <div class="mb-3 col-1">
                  <label for="reglement" class="col-form-label">Règlement:</label>
                  <input type="text" class="form-control" id="reglement" name="reglement" />
                </div>

                <div class="mb-3 col-2">
                  <label for="typereglement" class="col-form-label">Type Règlement:</label>
                  <input type="text" class="form-control" id="typereglement" name="typereglement" />
                </div>

                <div class="mb-3 col-2">
                  <label for="numcheque" class="col-form-label">N° chèque:</label>
                  <input type="text" class="form-control" id="numcheque" name="numcheque" />
                </div>

                <div class="mb-3 col-2">
                  <label for="datecheque" class="col-form-label">Date chèque:</label>
                  <input type="date" class="form-control" id="datecheque" name="datecheque" />
                </div>

                <div class="mb-3 col-2">
                  <label for="retenu" class="col-form-label">Retenu:</label>
                  <input type="text" class="form-control" id="retenu" name="retenu" />
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="submit" class="btn btn-primary" name="btnSubmitAjout">Ajouter</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
