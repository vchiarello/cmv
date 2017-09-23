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
include("../bean/Meteo7gg.php");
include("../bean/Meteo7ggPage.php");
include("./MngMeteo7ggWs.php");

use rest\database\ConnectionStatic;
use rest\web\MngMeteo7ggWs;
use rest\bean\Meteo7ggPage;



//error_log("Sessione: ".session_id() . " - Utente: " . $_SESSION["userid"] );


function listaMeteo7gg(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$where = MngMeteo7ggWs::getWhere($_GET);
	
	//numero di comuni necessario per la paginazione	
	$query = "select count(*) from cmv.meteo7gg_comuni_v ".$where;
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	$numeroRighe=$stmt->fetch(\PDO::FETCH_NUM);

	//selezione degli 30 o meno comuni per i quali si vedranno le previsioni
	$select = "SELECT nome_regione, nome_provincia, nome_comune from cmv.meteo7gg_comuni_v ";
	$orderby = MngMeteo7ggWs::getOrderBy($_GET);
	$limit = MngMeteo7ggWs::getLimit($_GET,$numeroRighe);
	
	if (isset($_GET['pagina']))	$pagina=$_GET['pagina'];
	else $pagina=1;
	
	$query = $select.$where.$orderby.$limit;
	error_log("Query estrazione dati. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$comuni = "";
	$regioni = "";
	$province = "";
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		if(strlen($comuni)>0)
			$comuni =$comuni.", '".str_replace("'", "''", $row->nome_comune)."' ";
		else 
			$comuni =$comuni." '".str_replace("'", "''", $row->nome_comune)."' ";
		if (strlen($regioni)>0)
			$regioni=$regioni.", '".str_replace("'", "''", $row->nome_regione)."' ";
		else 
			$regioni=$regioni." '".str_replace("'", "''", $row->nome_regione)."' ";
		if (strlen($province)>0)
			$province=$province.", '".str_replace("'", "''", $row->nome_provincia)."' ";
		else
			$province=$province." '".str_replace("'", "''", $row->nome_provincia)."' ";
	}
	
	//selezione dei 7 giorni per i quali occorre far vedere le previsioni si prendono gli ultimi 7 nel caso ci fossero degli errori sui dati
	$select = "SELECT data_meteo, data  from cmv.meteo7gg_date_v order by data_meteo desc limit 0,7 ";
	
	$query = $select;
	error_log("Query estrazione dati. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$dateMeteo = array();
	$whereDate = "";
	$ii=0;
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		$dateMeteo[$ii++] = $row->data;
		if (strlen($whereDate)>0)
			$whereDate = $whereDate.", '".$row->data."' ";
		else
			$whereDate = $whereDate." '".$row->data."' ";
	}
	$intestazioneDate = array_reverse($dateMeteo);
	
	//query effettiva sulla vista del meteo
	$select = "SELECT nome_regione, nome_provincia, id_localita, nome_comune, data, icona, fenomeno_previsto from cmv.meteo7gg_v  ";
	$orderby = MngMeteo7ggWs::getOrderBy($_GET);
	
	
	
	$query = 
		$select."where  nome_comune in (".$comuni. ")". 
                 "and nome_provincia in (". $province . ")".
                 "and nome_regione in (". $regioni. ")".
                 "and data in (". $whereDate. ")"
		.$orderby;
	
	error_log("Query estrazione dati. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$comunePrecedente = "";
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		if ($comunePrecedente <> $row->nome_comune){
			$comunePrecedente = $row->nome_comune;
			$appo = new \stdClass();
			$appo->nome_comune=$row->nome_comune;
			$appo->nome_provincia=$row->nome_provincia;
			$appo->nome_regione=$row->nome_regione;
			$appo->id_localita=$row->id_localita;
			$appo->fenomeno_previsto = array();
			$appo->icona = array();
			$appo->data = array();
			$out[]=$appo;
		}	
		
		for ( $i = 0; $i < count($intestazioneDate); $i++){
			if ($intestazioneDate[$i]==$row->data){
				$appo->data[$i]=$row->data;
				$appo->icona[$i]=$row->icona;
				$appo->fenomeno_previsto[$i]=$row->fenomeno_previsto;
			}
		}
		
	}
	
	
	
	$res = new Meteo7ggPage();
	$res->obj=$out;
	$res->totaleRecord=$numeroRighe[0];
	$res->paginaCorrente=$pagina;
	$res->totalePagine=ceil($res->totaleRecord/30);
	$res->intestazione = $intestazioneDate;
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
	
	$query= "select distinct nome_regione from cmv.meteo7gg_comuni_v order by nome_regione";
	
	error_log("Query estrazione dati Meteo7gg - regioni. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$out = array();
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		//error_log("Riga: " . print_r($row, TRUE));
		$out[] = $row;
	}
	
	return $out;
	//	return $out;
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
	
	$query= "select distinct nome_provincia from cmv.meteo7gg_comuni_v order by nome_provincia ";
	
	error_log("Query estrazione dati Meteo7gg - province. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$out = array();
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		//error_log("Riga: " . print_r($row, TRUE));
		$out[] = $row;
	}
	
	return $out;
	//	return $out;
}

function getComune(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$query= "select distinct nome_comune from cmv.meteo7gg_comuni_v  ";
	
	error_log("Query estrazione dati Meteo7gg - tipo. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$out = array();
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		//error_log("Riga: " . print_r($row, TRUE));
		$out[] = $row;
	}
	
	return $out;
	//	return $out;
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
	
	$query= "select * from cmv.meteo7gg where id_localita=:id";
	
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


error_log("arrivato in Meteo7ggWs");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['azione']) &&  $_GET['azione']=='dettaglio' && isset($_GET['id'])){
		error_log("GET Dettaglio Meteo7ggWs");
		$out=mostraDettaglio();
		echo json_encode($out);
	}else if (isset($_GET['azione']) && $_GET['azione']=='regioni' ) {
		error_log("GET regione Meteo7ggWs");
		$out=getRegioni();
		echo json_encode($out);
	}else if (isset($_GET['azione']) && $_GET['azione']=='province' ) {
		error_log("GET Province Meteo7ggWs");
		$out=getProvince();
		echo json_encode($out);
	}else if (isset($_GET['azione']) && $_GET['azione']=='comuni' ) {
		error_log("GET Comune Meteo7ggWs");
		$out=getComune();
		echo json_encode($out);
	}else{
		error_log("GET Meteo7ggWs");
		$out = listaMeteo7gg();
		echo json_encode($out);
	}	
	
}elseif ($_SERVER['REQUEST_METHOD'] === 'POST'){
	error_log("POST TrafficoWs");
	error_log($_POST['id']);
	error_log($_POST['visibilita']);
	if (isset($_POST['id']) && isset($_POST['visibilita'])) {
		$id=$_POST['id'];
		$visibilita=$_POST['visibilita'];
		error_log("POST TrafficoWs aggiornaVisibilita id, visibilita ". $id .", ".$visibilita);
		aggiornaVisibilita($id,$visibilita);		
	}
	
	
}

?>
