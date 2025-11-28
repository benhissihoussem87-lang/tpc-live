<?php 
include '../../class/client.class.php';

// Collect inputs safely
$type = @$_POST['type'];
$convention = @$_POST['convention'];
$nom = @$_POST['nom'];
$code = @$_POST['code'];
$adresse = @$_POST['adresse'];
$matriculeFiscale = @$_POST['matriculeFiscale'];
$exonoration = @$_POST['exonoration'];
$tel = @$_POST['tel'];
$email = @$_POST['email'];
$numexonoration = @$_POST['numexonoration'];
$validite = @$_POST['ValiditeExonoration'];
$idClient = @$_POST['idClient'];

// File names (use basename to avoid path tricks)
$pieceConventionName = isset($_FILES['piececonvention']['name']) ? basename($_FILES['piececonvention']['name']) : '';
$pieceExonorationName = isset($_FILES['pieceExonoration']['name']) ? basename($_FILES['pieceExonoration']['name']) : '';

if ($clt->Modifier($type, $convention, $pieceConventionName, $nom, $code, $adresse, $matriculeFiscale, $exonoration, $pieceExonorationName, $tel, $email, $numexonoration, $validite, $idClient)) {
    // Ensure upload directories exist
    if (!is_dir('pieceExonorationClients')) { @mkdir('pieceExonorationClients', 0777, true); }
    if (!is_dir('pieceConventionClients')) { @mkdir('pieceConventionClients', 0777, true); }

    // Move uploaded files if provided
    if (!empty($_FILES['pieceExonoration']['name']) && is_uploaded_file($_FILES['pieceExonoration']['tmp_name'])) {
        @move_uploaded_file($_FILES['pieceExonoration']['tmp_name'], 'pieceExonorationClients/' . $pieceExonorationName);
    }
    if (!empty($_FILES['piececonvention']['name']) && is_uploaded_file($_FILES['piececonvention']['tmp_name'])) {
        @move_uploaded_file($_FILES['piececonvention']['tmp_name'], 'pieceConventionClients/' . $pieceConventionName);
    }

    // Redirect back to the clients list (root deployment path)
    echo "<script>document.location.href='/main.php?Gestion_Clients'</script>";
} else {
    echo "<script>alert('Erreur !!! ')</script>";
}
?>
