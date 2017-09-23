<?php
session_start();
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
include("../database/ConnectionStatic.php");
include("./MngThreadTelecamere.php");
include("../thread/StatoThread.php");

use rest\database\ConnectionStatic;
use rest\web\MngThreadTelecamere;
use rest\web\MngThreadTelecamereTelecamere;
use rest\thread\StatoThread;

error_log("Sessione: ".session_id() . " - Utente: " . $_SESSION["userid"] );

$configFile = __DIR__ . "/../config.php";
$config = include $configFile;
$conn = new ConnectionStatic($config);

if (!isset($conn)){
	error_log("Connessione non valida.");
	echo json_encode("");
	return;
}

$azione = MngThreadTelecamereTelecamere::getAzione($_GET);

if ($azione=="start"){
	echo json_encode(MngThreadTelecamereTelecamere::info(),MngThreadTelecamereTelecamere::start());
}
elseif($azione=="pausa") MngThreadTelecamereTelecamere::stop();
elseif ($azione=="reset")MngThreadTelecamereTelecamere::reset();
elseif ($azione=="info")MngThreadTelecamereTelecamere::info();
elseif ($azione=="restart")MngThreadTelecamereTelecamere::restart();
elseif ($azione=="stop")MngThreadTelecamereTelecamere::stop();
