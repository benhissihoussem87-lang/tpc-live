<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>TPC: Login</title>
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<!-- Font-->
	
	<link rel="stylesheet" type="text/css" href="assets/home/css/opensans-font.css">
	<link rel="stylesheet" type="text/css" href="assets/home/css/montserrat-font.css">
	<link rel="stylesheet" type="text/css" href="assets/home/fonts/material-design-iconic-font/css/material-design-iconic-font.min.css">
	<!-- Main Style Css -->
    <link rel="stylesheet" href="assets/home/css/style.css"/>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" >
	<link href="assets/home/bootstrap/css/bootstrap.min.css" rel="stylesheet" >
	<!-- ******************** -->
	</head>
<body>
<?php 
if(isset($_POST['S_Administration'])){
	if($_POST['login']=='safa123' and $_POST['pwd']=='4055'){
		echo "<script>document.location.href='main.php'</script>";
	}
}
?>
	<div class="page-content">
		<div class="wizard-heading" style="font-size:25px;margin-bottom:50px">TPC</div>
		<div class="wizard-v7-content" >
			<div class="wizard-form" >
		        
		        	<div id="form-total"class="form-register" >
		        		<!-- SECTION 1 -->
			            <h2>
			            	<p class="step-icon"><span>1</span></p>
			            	<div class="step-text">
			            		<span class="step-inner-1" style="text-align:center">Administration</span>
			            		<span class="step-inner-2"></span>
			            	</div>
			            </h2>
			            <section >
			                <div class="inner"   >
			                	<div class="wizard-header">
									<h3 class="heading">Espace Administration</h3>
								</div>
							<!--debut Form login Administration-->
							<form class="form-register"  method="post" >
								<div class="form-row">
									<div class="form-holder form-holder-2">
									<label for="login">Login</label>
								
										<input type="text" name="login" autofocus id="login" class="form-control" placeholder="login" required>
									</div>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-2">
									<label for="pwd">Password</label>
								
										<input type="password" name="pwd"  id="pwd" class="form-control" placeholder="Mot de passe" required>
									</div>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-2">
									
								
										<button type="submit" style="float:right"  class="btn btn-primary" name="S_Administration" >Login</button>
									</div>
								</div>
							</form>
							<!-- Fin Form login Administration -->
								</div>
			            </section>
						
						<!-- SECTION 2 -->
			            <h2>
			            	<p class="step-icon"><span>2</span></p>
			            	<div class="step-text">
			            		<span class="step-inner-1" style="text-align:center"> Technique</span>
			            		<span class="step-inner-2"></span>
			            	</div>
			            </h2>
			            <section >
			                <div class="inner"   >
			                	<div class="wizard-header">
									<h3 class="heading">Service Technique</h3>
								</div>
								<!--debut Form login Administration-->
							<form class="form-register" action="#" method="post" >
								<div class="form-row">
									<div class="form-holder form-holder-2">
									<label for="login">Login</label>
								
										<input type="text" name="login" autofocus id="login" class="form-control" placeholder="login" required>
									</div>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-2">
									<label for="pwd">Password</label>
								
										<input type="password" name="pwd"  id="pwd" class="form-control" placeholder="Mot de passe" required>
									</div>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-2">
				<button type="submit" name="S_technique" style="float:right"  class="btn btn-primary" >Login</button>
									</div>
								</div>
							</form>
							<!-- Fin Form login Administration -->
								</div>
			            </section>
						
						
			</div>
		</div>
	</div>
	<script src="assets/home/js/jquery-3.3.1.min.js"></script>
	<script src="assets/home/js/jquery.steps.js"></script>
	<script src="assets/home/js/main.js"></script>
</body>
</html>