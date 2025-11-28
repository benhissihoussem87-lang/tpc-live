<?php
session_start();
// When asking to view an Avoir in print mode via main.php, redirect to the standalone
// Avoir model page so the sidebar/layout are not rendered (same behavior as factures).
if (isset($_GET['Avoir']) && isset($_GET['View']) && isset($_GET['id'])) {
    $idAvoir = (int)$_GET['id'];
    if ($idAvoir > 0) {
        header('Location: pages/FacturesAvoir/ModeleAvoir.php?id='.$idAvoir);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>TPC</title>

    <!-- Custom fonts for this template-->
    <link href="assets/main/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/main/css/sb-admin-2.min.css" rel="stylesheet">
 <!-- Custom styles for this page -->
    <!-- DataTables -->
    <link href="assets/main/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
	<!-- Bootstrap -->
	
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>-->
<script src="assets/bootstrap/js/bootstrap.bundle.min.js" ></script>

</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
		<?php include 'pages/sidebar.php'?>
         <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Navbar Page  -->
                <?php include 'pages/navbar.php'?>
				<!-- End of Navbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">
					<?php 
					 if(isset($_GET['Gestion_Clients'])){
						 if(isset($_GET['Archive']))
					    {echo 'Interface Archive Clients';}
						else { echo 'Interface Clients'; }
						 
						 }
					  if(isset($_GET['Offres_Prix']))
					  { if(isset($_GET['Archive']))
					    {echo 'Interface Archive Offres de Prix';}
                       else {echo 'Interface  Offres de Prix';}
    				  }
					  if(isset($_GET['Bons_commandes']))
					  { if(isset($_GET['Archive']))
					    {echo 'Interface Archive Bons Commandes';}
                       else {echo 'Interface  Bons Commandes';}
    				  }
					 else if(!isset($_GET)) {echo ''; }
					?>
					</h1>
					<!--Main Page -->
					
					<?php
					if(isset($_GET['GRH'])){
						if(!isset($_GET['Modifier'])){
						 include 'pages/grh/grh.php';
						}
						else {
							include 'pages/grh/modifier.php';
						}
					}
				if(isset($_GET['CNSS'])){
					
						 include 'pages/cnss/cnss.php';
						
						
					}
				if(isset($_GET['Assurance'])){
					
						 include 'pages/assurance/assurance.php';
						
						
					}
			if(isset($_GET['Gestion_Clients']))
			   {
				   // ARchive Client 
				    if(isset($_GET['Archive']))
				   {
					     if(isset($_GET['Facture'])){
							 include 'pages/clients/Archive/Archive_FacturesClient.php';
						 }
						  if(isset($_GET['Offres'])){
							 include 'pages/clients/Archive/Archive_OffreClients.php';
						 }
						  if(isset($_GET['client']))
				        {include 'pages/clients/Archive/Archive_OffreClients.php';}
						 else {include 'pages/clients/Archive/Afficher.php';}
						
					}
					else if(!isset($_GET['Archive'])){
				   if(isset($_GET['Offreclient']))
				   {include 'pages/clients/OffreClients.php';}
					else if(isset($_GET['Facturesclient']))
				   {
					  include 'pages/clients/FacturesClient.php';
					  }
					  else if(isset($_GET['BonsCommandesclient']))
				   {
					  include 'pages/clients/BonCommandesClients.php';
					  }
			       else {include 'pages/clients/Afficher.php';}
					} 
				}
		/********* Dossier Client *********/
		if(isset($_GET['DossierClient'])){
			include 'pages/DossierClients/Dossiers.php';
		}
		/****  Gestion des Offres *****/
			if(isset($_GET['Offres_Prix'])){
				
			/********* ARchive  Offres Prix   *************/
				  if(isset($_GET['Archive']))
				   {
					     if(isset($_GET['Add'])){
							 include 'pages/OffresPrix/Archive/AjoutOffreArchive.php';
						 }
						 else {include 'pages/OffresPrix/Archive/AfficherArchive.php';}
					  }
			/************ Offres Prix *********/
				else if(!isset($_GET['Archive'])){
					 if(isset($_GET['Add']))
				         {include 'pages/OffresPrix/AjoutOffre.php';}
					 else if(isset($_GET['AddOffreForfitaire']))
				         {include 'pages/OffresPrix/AjoutOffreForfitaire.php';}
					else if(isset($_GET['modifier']))
				         {include 'pages/OffresPrix/ModifierOffre.php';}
					 else if(isset($_GET['modifierOffreForfitaire']))
				         {include 'pages/OffresPrix/ModifierOffreForfitaire.php';}
					  else  {include 'pages/OffresPrix/Afficher.php';}
					}
			      
			   // Fin ARchive Offres Prix
				
				}
			if(isset($_GET['Factures'])){
				if(isset($_GET['Add']))
				   {include 'pages/Factures/AjoutFacture.php';}
			else if(isset($_GET['AddFactureForfitaire']))
				   {include 'pages/Factures/FactureForfitaire.php';}
			    else if(isset($_GET['Projets']))
				   {include 'pages/Factures/ProjetsByFacture.php';}
			  else if(isset($_GET['modifier']))
				   {include 'pages/Factures/ModifierFacture.php';}
			   else if(isset($_GET['modifierForfitaire']))
				   {include 'pages/Factures/ModifierFactureForfitaire.php';}
			    else if(isset($_GET['NonRegle']))
				   {include 'pages/Factures/nonregle.php';}
			     else 
				   {include 'pages/Factures/Afficher.php';}
            }
            // Factures Avoir (Crédit)
            if(isset($_GET['Avoir'])){
                if(isset($_GET['Add'])) {
                    include 'pages/FacturesAvoir/AjoutAvoir.php';
                } elseif(isset($_GET['AddForfaitaire'])) {
                    include 'pages/FacturesAvoir/AjoutAvoirForfaitaire.php';
                } elseif(isset($_GET['View'])) {
                    // Render the Avoir print model inside the main layout
                    include 'pages/FacturesAvoir/ModeleAvoir.php';
                } elseif(isset($_GET['Modifier'])) {
                    include 'pages/FacturesAvoir/ModifierAvoir.php';
                } else {
                    include 'pages/FacturesAvoir/ListeAvoir.php';
                }
            }
            /******** Bordereaux  *******/
			    if(isset($_GET['Bordereaux'])){
			/************ Archive Bordereaux ***************/
					if(isset($_GET['Archive']))
				   {
					 if(isset($_GET['Add'])){
					 include 'pages/Bordereaux/Archive/AjoutBordereauArchive.php';
						 }
					else {include 'pages/Bordereaux/Archive/AfficherArchive.php';}
						
					}
			/**** New Bordereaux *********/
					else if(!isset($_GET['Archive'])){
					     if(isset($_GET['Add']))
						   {include 'pages/Bordereaux/Ajout.php';}
					   else if(isset($_GET['Update']))
						   {include 'pages/Bordereaux/ModifierBordereaux.php';}
						 else 
						   {include 'pages/Bordereaux/Afficher.php';}	
							}
					
				
			   }
		/****** Fin Bordereaux *******/	 
		
		 /******** Bons Commandes  *******/
			    if(isset($_GET['Bons_commandes'])){
			/************ Archive Bons Commandes ***************/
					if(isset($_GET['Archive']))
				   {
					 if(isset($_GET['Add'])){
					 include 'pages/BonsCommandes/Archive/AjoutBonCommandeArchive.php';
						 }
					else {include 'pages/BonsCommandes/Archive/AfficherArchive.php';}
						
					}
			/**** New Bons Commandes *********/
					else if(!isset($_GET['Archive'])){
					     if(isset($_GET['Add']))
						   {include 'pages/BonsCommandes/Ajout.php';}
					  else if(isset($_GET['ModifierBC']))
						   {include 'pages/BonsCommandes/Modifier.php';}
						 else 
						   {include 'pages/BonsCommandes/Afficher.php';}	
							}
					
				
			   }
		/****** Fin Bons Commandes *******/
		
			  // Archive Facture
			  if(isset($_GET['ArchivesFacture']))
				   {
					   if(isset($_GET['Add']))
					   { include 'pages/Factures/Archive/ArchivesFacture.php'; }
					 else if(isset($_GET['Afficher']))
				   {include 'pages/Factures/Archive/Afficher.php';}
				   }
	/********************* Reglements ***************/
	        if(isset($_GET['Reglements'])){
				
			    // ARchive Reglements
				  if(isset($_GET['Archive']))
				   {
					     if(isset($_GET['Add'])){
							 include 'pages/Reglements/Archive/AjoutReglementsArchive.php';
						 }
						 else if(isset($_GET['Modifier'])){
							 include 'pages/Reglements/Archive/ModifierReglementsArchive.php';
						 }
						 else {include 'pages/Reglements/Archive/AfficherArchiveReglements.php';}
					  }
					else if(!isset($_GET['Archive'])){
						if(isset($_GET['Add']))
				         {include 'pages/Reglements/AjoutReglements.php';}
					  else if(isset($_GET['Modifier'])){
							 include 'pages/Reglements/ModifierReglement.php';
					  }
					   else {include 'pages/Reglements/AfficherReglements.php';}
					}
			      
			   // Fin ARchive Offres Prix
				
				}
					  ?>
					<!-- Fin Main Page -->

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
              <?php include 'pages/footer.php'?>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    

    <!-- Bootstrap core JavaScript-->
    <script src="assets/main/vendor/jquery/jquery.min.js"></script>
    <script src="assets/main/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/main/js/ScriptClient.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="assets/main/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="assets/main/js/sb-admin-2.min.js"></script>
	<!-- Page level plugins -->
    <!-- DataTables -->
    <script src="assets/main/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/main/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="assets/main/js/demo/datatables-demo.js"></script>
    <script>
    // Hard fallback: ensure any #dataTable on the page gets initialized with search/filter UI.
    (function() {
      function init() {
        if (!window.jQuery || !$.fn || !$.fn.DataTable) { return; }
        var domLayout = "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
        $('table#dataTable').each(function(){
          var $t = $(this);
          if ($.fn.DataTable.isDataTable($t)) { $t.DataTable().destroy(); }
          $t.DataTable({ dom: domLayout });
        });
      }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }
    })();
    </script>

</body>

</html>
