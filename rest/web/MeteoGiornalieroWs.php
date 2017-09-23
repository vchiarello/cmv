<?php
namespace rest\web;
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
include("../bean/MeteoGiornaliero.php");
include("../bean/MeteoGiornalieroPage.php");
include("./MngMeteoGiornalieroWs.php");

use rest\database\ConnectionStatic;
use rest\web\MngMeteoGiornalieroWs;
use rest\bean\MeteoGiornalieroPage;



//error_log("Sessione: ".session_id() . " - Utente: " . $_SESSION["userid"] );


function listaMeteoGiornaliero(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$where = MngMeteoGiornalieroWs::getWhere($_GET);
	
	
	$query = "select count(*) from cmv.meteo_v ".$where;
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	$numeroRighe=$stmt->fetch(\PDO::FETCH_NUM);


	$select = "SELECT id_meteo, nome_stazione, nome_comune, nome_provincia, nome_regione, temperatura, max_temp_24h, min_temp_24h, icona FROM cmv.meteo_v ";
	$orderby = MngMeteoGiornalieroWs::getOrderBy($_GET);
	$limit = MngMeteoGiornalieroWs::getLimit($_GET,$numeroRighe);
	
	if (isset($_GET['pagina']))	$pagina=$_GET['pagina'];
	else $pagina=1;
		
	$query = $select.$where.$orderby.$limit;
	error_log("Query estrazione dati. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$out = array();
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		$appo=$row;
		$out[] = $row;
	}
	$res = new MeteoGiornalieroPage();
	$res->obj=$out;
	$res->totaleRecord=$numeroRighe[0];
	$res->paginaCorrente=$pagina;
	$res->totalePagine=ceil($res->totaleRecord/30);
	return $res;
	//	return $out;
}

function getRegioni(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$query= "select distinct nome_regione from cmv.meteo_v order by nome_regione";
	
	error_log("Query estrazione dati MeteoGiornaliero - regioni. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$out = array();
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		$out[] = $row;
	}
	
	return $out;
}

function getProvince(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$query= "select distinct nome_provincia from cmv.meteo_v order by nome_provincia ";
	
	error_log("Query estrazione dati MeteoGiornaliero - province. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$out = array();
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		$out[] = $row;
	}
	
	return $out;
}

function getComuni(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$query= "select distinct nome_comune from cmv.meteo_v order by nome_comune ";
	
	error_log("Query estrazione dati MeteoGiornaliero - province. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$out = array();
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		$out[] = $row;
	}
	
	return $out;
}


function mostraDettaglio(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$query= "select * from cmv.meteo where id_meteo=:id";
	
	error_log("Query estrazione dati. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	$stmt->bindParam(':id', $_GET['id']);
	
	$stmt -> execute();
	$out = array();
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		//error_log("Riga: " . print_r($row, TRUE));
		$out[] = $row;
	}
	
	return $out;
}



error_log("arrivato in MeteoGiornalieroWs");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['azione']) &&  $_GET['azione']=='dettaglio' && isset($_GET['id'])) {
		error_log("GET Dettaglio MeteoGiornalieroWs");
		$out=mostraDettaglio();
		echo json_encode($out);
	}else if (isset($_GET['azione']) && $_GET['azione']=='regione' ) {
		error_log("GET compartimento MeteoGiornalieroWs");
		$out=getRegioni();
		echo json_encode($out);
	}else if (isset($_GET['azione']) && $_GET['azione']=='province' ) {
		error_log("GET Province MeteoGiornalieroWs");
		$out=getProvince();
		echo json_encode($out);
	}else if (isset($_GET['azione']) && $_GET['azione']=='comuni' ) {
		error_log("GET Comuni MeteoGiornalieroWs");
		$out=getComuni();
		echo json_encode($out);
	}else{
		error_log("GET MeteoGiornalieroWs");
		$out = listaMeteoGiornaliero();
		echo json_encode($out);
	}	
	
}elseif ($_SERVER['REQUEST_METHOD'] === 'POST'){
	error_log("POST MeteoGiornalieroWs");
	
	
}

?>
