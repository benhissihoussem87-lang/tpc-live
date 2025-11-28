<?php
// Do not render the sidebar on standalone print/model pages
// (facture models, offre de prix model, avoir model, etc.).
$script = $_SERVER['SCRIPT_NAME'] ?? '';
if (
    strpos($script, 'ModeleFacture') !== false ||
    strpos($script, 'ModeleOffrePrix.php') !== false ||
    strpos($script, 'ModeleAvoir.php') !== false
) {
    return;
}
?>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                
                <div class="sidebar-brand-text mx-3">TPC-Administration</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="?GRH">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>GRH</span></a>
            </li>
             <li class="nav-item">
                <a class="nav-link" href="?CNSS">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>CNSS</span></a>
            </li>
			
			 <li class="nav-item">
                <a class="nav-link" href="?Assurance">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Assurance</span></a>
            </li>
            <!-- Divider -->
            <hr class="sidebar-divider">

             <!-- Nav Item - Pages Collapse Menu -->
            
			<li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#clientsMenu" data-bs-toggle="collapse" data-bs-target="#clientsMenu">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Gestion Clients</span>
                </a>
                <div id="clientsMenu" class="collapse" data-parent="#accordionSidebar" data-bs-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="?Gestion_Clients">Clients</a>
                        <a class="collapse-item" href="?Gestion_Clients&Archive">Archive Clients</a>
                        <a class="collapse-item" href="?DossierClient">Dossier Clients</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
           
			<li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#Bons_commandes" data-bs-toggle="collapse" data-bs-target="#Bons_commandes"
                    aria-expanded="true" aria-controls="Bons_commandes">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Bons commandes</span>
                </a>
                <div id="Bons_commandes" class="collapse" data-parent="#accordionSidebar" data-bs-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                         <a class="collapse-item" href="?Bons_commandes">Bons commandes</a>
                        <a class="collapse-item" href="?Bons_commandes&Archive">Archive Bons commandes</a>
                       
                    </div>
                </div>
            </li>
				
				
			  <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#Bordereaux" data-bs-toggle="collapse" data-bs-target="#Bordereaux"
                    aria-expanded="true" aria-controls="Bordereaux">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Gestion Bordereaux</span>
                </a>
                <div id="Bordereaux" class="collapse" data-parent="#accordionSidebar" data-bs-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                         <a class="collapse-item" href="?Bordereaux">Bordereaux</a>
                        <a class="collapse-item" href="?Bordereaux&Archive">Archive Bordereaux</a>
                       
                    </div>
                </div>
            </li>
			  <li class="nav-item">
                <a class="nav-link" href="?ArchivesFacture&Add" >
                    <i class="fas fa-fw fa-arrow-alt-circle-right"></i>
                    <span>Archive Factures</span>
                </a>
                
              </li>

              <li class="nav-item">
                <a class="nav-link" href="?Avoir" >
                    <i class="fas fa-fw fa-arrow-alt-circle-right"></i>
                    <span>Gestion Avoirs</span>
                </a>
              </li>
			  
			  <!-- <li class="nav-item">
                <a class="nav-link" href="?ArchivesOffre&Add" >
                    <i class="fas fa-fw fa-arrow-alt-circle-right"></i>
                    <span>Archive Offre</span>
                </a>
                
              </li>-->
			  
			    <li class="nav-item">
                <a class="nav-link" href="?Factures" >
                    <i class="fas fa-fw fa-arrow-alt-circle-right"></i>
                    <span>Gestion Factures</span>
                </a>
                
              </li>
			   <li class="nav-item">
              
				<a class="nav-link" href="./pages/Factures/nonregle.php" >
                    <i class="fas fa-fw fa-arrow-alt-circle-right"></i>
                    <span>Facture non regle</span>
                </a>
                
              </li>
			  <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#Offres" data-bs-toggle="collapse" data-bs-target="#Offres"
                    aria-expanded="true" aria-controls="Offres">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Offres des Prix</span>
                </a>
                <div id="Offres" class="collapse" data-parent="#accordionSidebar" data-bs-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                         <a class="collapse-item" href="?Offres_Prix">Offres des Prix</a>
                        <a class="collapse-item" href="?Offres_Prix&Archive">Archive Offres</a>
                       
                    </div>
                </div>
            </li>
			   
			 
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#reglements" data-bs-toggle="collapse" data-bs-target="#reglements"
                    aria-expanded="true" aria-controls="reglements">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Réglements</span>
                </a>
                <div id="reglements" class="collapse" data-parent="#accordionSidebar" data-bs-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                         <a class="collapse-item" href="?Reglements">Réglements</a>
                        <a class="collapse-item" href="?Reglements&Archive">Archive Réglements</a>
                       
                    </div>
                </div>
            </li>
             
			  <li class="nav-item">
              
				<a class="nav-link" href="./pages/clients/rapport.php" >
                    <i class="fas fa-fw fa-arrow-alt-circle-right"></i>
                    <span>Rapport Clients</span>
                </a>
                
              </li>
			 
			 <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
       
