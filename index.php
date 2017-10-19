<!DOCTYPE html>
<?php
session_start();
//se non c'è la sessione registrata
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 600)) {
    // last request was more than 30 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 1800) {
    // session started more than 30 minutes ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}
if (!isset($_SESSION['userid']) or !isset($_SESSION['autorizzato'])) {
	unset($_SESSION['msg']);
	echo "<h1>Area riservata, accesso negato.</h1>";
	echo "Per effettuare il login clicca <a href='login.php'><font color='blue'>qui</font></a>";
	die;
}

//Altrimenti Prelevo il codice identificatico dell'utente loggato
$utente = $_SESSION['userid']; //id cod recuperato nel file di verifica
error_log("Sessione ok: " . $utente . ' - ' . session_id());
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
                    </ul>
                    <!-- /.dropdown-messages -->
                </li>
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-tasks fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-tasks">
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>Task 1</strong>
                                        <span class="pull-right text-muted">40% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
                                            <span class="sr-only">40% Complete (success)</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>Task 2</strong>
                                        <span class="pull-right text-muted">20% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
                                            <span class="sr-only">20% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>Task 3</strong>
                                        <span class="pull-right text-muted">60% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                                            <span class="sr-only">60% Complete (warning)</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>Task 4</strong>
                                        <span class="pull-right text-muted">80% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%">
                                            <span class="sr-only">80% Complete (danger)</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a class="text-center" href="#">
                                <strong>See All Tasks</strong>
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                    <!-- /.dropdown-tasks -->
                </li>
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-bell fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-alerts">
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-comment fa-fw"></i> New Comment
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-twitter fa-fw"></i> 3 New Followers
                                    <span class="pull-right text-muted small">12 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-envelope fa-fw"></i> Message Sent
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-tasks fa-fw"></i> New Task
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-upload fa-fw"></i> Server Rebooted
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a class="text-center" href="#">
                                <strong>See All Alerts</strong>
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                    <!-- /.dropdown-alerts -->
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
                        <li>
                            <a ui-sref="telecamere"><i class="fa fa-table fa-fw"></i>Telecamere</a>
                        </li>
                        <li>
                            <a ui-sref="eventi"><i class="fa fa-table fa-fw"></i> Eventi/segnalazioni/ordinanze</a>
                        </li>
                        <li>
                            <a ui-sref="traffico"><i class="fa fa-table fa-fw"></i>Traffico</a>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-table fa-fw"></i>Meteo<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a ui-sref="meteoGiornaliero">Giornaliero</a>
                                </li>
                                <li>
                                    <a ui-sref="meteoSettimanale">Settimanale</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
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
