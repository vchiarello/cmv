<!DOCTYPE html>
<?php
session_start();
include("./sec/Sec.php");
use sec\Sec;
$ut = $_SESSION['userid'];
$sec = new Sec();
# Primo livello. Il secondo livello viene creato a partire dalle voci del primo
$dd = $sec->menu($ut, '');
?>
<html lang="en"  ng-app="cmv">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Anas - CMV</title>

    <!-- Bootstrap Core CSS -->
    <link href="./webjars/bootstrap/css/bootstrap.css" rel="stylesheet">

	<!-- jqquery-ui Core CSS -->
	<link href="./webjars/jquery-ui/jquery-ui.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="./webjars/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="./css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom CSS for CMV-->
    <link href="./css/customCmv.css" rel="stylesheet">

    <!-- Custom CSS for dataTables-->
    <link href="./css/vitoDataTables.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="./webjars/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="./webjars/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

	<!-- jquery js -->
    <script src="./webjars/jquery/jquery.js"></script>
	<!-- jquery-ui js -->
    <script src="./webjars/jquery-ui/jquery-ui.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="./webjars/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="./js/sb-admin-2.js"></script>



    <!-- Bootstrap Core JavaScript -->
    <script src="./webjars/bootstrap/js/bootstrap.js"></script>
	<!-- bootbox js -->
    <script src="./webjars/bootbox/4.4.0/bootbox.js"></script>
	<!-- angular core -->
    <script src="./webjars/angularjs/1.5.8/angular.js"></script>
    <script src="./webjars/angular-animate/1.5.8/angular-animate.js"></script>
	<!-- angular per la gestione dell'ui-view con navigatore solo nell'index -->
    <script src="./webjars/angular-ui-router/0.2.8/angular-ui-router.js"></script>
	<!-- angular busy per spinner -->
    <script src="./webjars/angular-busy/4.1.1/angular-busy.js"></script>	
	
	<!-- definizione dell'applicazione -->
	<script src="./js/angular/app.js" ></script>
	<!-- definizione dell'applicazione -->
	<script src="./js/angular/appConfig.js" ></script>
	<!-- Controller di index -->
	<script src="./js/angular/controller/Index.js" ></script>
	<!-- controller delle telecamere -->
	<script src="./js/angular/controller/Telecamere.js" ></script>
	<!-- controller degli eventi -->
	<script src="./js/angular/controller/Eventi.js" ></script>
	<!-- controller del traffico -->
	<script src="./js/angular/controller/Traffico.js" ></script>
	<!-- controller del meteo -->
	<script src="./js/angular/controller/MeteoGiornaliero.js" ></script>
	<!-- controller del meteo settimanale-->
	<script src="./js/angular/controller/MeteoSettimanale.js" ></script>
	<!-- controller dell'anagrafica dei canali -->
	<script src="./js/angular/controller/listaCanali.js" ></script>
	<!-- controller dell'anagrafica dei canali -->
	<script src="./js/angular/controller/ManageCanale.js" ></script>
	<!-- controller dell'anagrafica dei canali -->
	<script src="./js/angular/controller/listaUtenti.js" ></script>
	<!-- controller dell'anagrafica dei canali -->
	<script src="./js/angular/controller/ManageUtente.js" ></script>
	<!-- controller Blank -->
	<script src="./js/angular/controller/BlankController.js" ></script>
	<!-- controller DettaglioEventoController -->
	<script src="./js/angular/controller/DettaglioEventoController.js" ></script>
	<!-- controller ModificaEventoController -->
	<script src="./js/angular/controller/ModificaEventoController.js" ></script>


</head>

<body>

    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0" ng-controller="Index">
            <div class="navbar-header toolbarCmv" style="width:300px;float: left;">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html" style="padding:5px;font-size:40px;"><img src="images/logoCmv.png" style="height:90px;"/></a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-gear fa-fw" style="font-size:40px;" ></i> <i class="fa fa-caret-down" style="font-size:40px;"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-messages">
                        <li>
                            <a ui-sref="listaCanali">
                                <div>
                                    <strong>Canali</strong>
                                    <span class="pull-right text-muted">
                                        <em></em>
                                    </span>
                                </div>
                                <div>Gestione canali</div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a ui-sref="listaUtenti">
                                <div>
                                    <strong>Utenti</strong>
                                    <span class="pull-right text-muted">
                                        <em></em>
                                    </span>
                                </div>
                                <div>Gestione utenti</div>
                            </a>
                        </li>
                    </ul>
                    <!-- /.dropdown-messages -->
                </li>
                <!-- /.dropdown -->

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="#"><i class="fa fa-user fa-fw"></i> User Profile</a>
                        </li>
                        <li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="login.html"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <?php foreach ($dd as $k => $v) { ?>
                            <li>
                            <?php 
                                $dd2 = $sec->menu($ut, $k);
                                if (sizeof($dd2) < 1) { 
							?>
                                <a ui-sref="<?php echo $v['tag']; ?>"><i class="fa fa-table fa-fw"></i><?php echo $v['dd']; ?></a>
                            <?php } else { ?>
                                <a href="#"><i class="fa fa-table fa-fw"></i><?php echo $v['dd']; ?><span class="fa arrow"></span></a>
                                <ul class="nav nav-second-level">
                                <?php foreach ($dd2 as $k2 => $v2) { ?>
                                    <li>
                                        <a ui-sref="<?php echo $v2['tag']; ?>"><?php echo $v2['dd']; ?></a>
                                    </li>
                                <?php } ?>
                                </ul>
                                <!-- /.nav-second-level -->
                            <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>

		 <div ui-view autoscroll="false"> CONTENUTO DI UI-VIEW SENZA FILE</div>
		 

    </div>
    <!-- /#wrapper -->


</body>

</html>
