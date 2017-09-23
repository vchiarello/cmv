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
include("../bean/Traffico.php");
include("../bean/TrafficoPage.php");
include("./MngTrafficoWs.php");

use rest\database\ConnectionStatic;
use rest\web\MngTrafficoWs;
use rest\bean\TrafficoPage;



//error_log("Sessione: ".session_id() . " - Utente: " . $_SESSION["userid"] );


function listaTraffico(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$where = MngTrafficoWs::getWhere($_GET);
	
	
	$query = "select count(*) from cmv.traffico_unito_cres_descres_v ".$where;
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	$numeroRighe=$stmt->fetch(\PDO::FETCH_NUM);

	//estrazione dei colori del traffico
	//vecchia gestione dei colori non usata perché messo tutto nella vista
// 	$select = "SELECT tipo_strada, velocita_inizio, velocita_fine, colore from cmv.colore_velocita ";
// 	$stmt = $conn->pdo->prepare($select, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
// 	$stmt -> execute();
// 	$colore = array();
// 	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
// 		//error_log("Riga: " . print_r($row, TRUE));
// 		$colore[] = $row;
// 	}
	
	
	$select = "SELECT tipo, cod_strada, nome_strada, compartimento, id_traffico_crescente, direzione_crescente,  progressiva_crescente,  progressiva_fine_crescente,  velocita_crescente,direzione_decrescente,coloreCrescente,id_traffico_decrescente, progressiva_decrescente,progressiva_fine_decrescente,velocita_decrescente,coloreDecrescente from cmv.traffico_unito_cres_descres_v ";
	$orderby = MngTrafficoWs::getOrderBy($_GET);
	$limit = MngTrafficoWs::getLimit($_GET,$numeroRighe);
	
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
//		$appo->coloreCrescente=MngTrafficoWs::getColore($row->cod_strada,$row->velocita_crescente,$colore);
//		$appo->coloreDecrescente=MngTrafficoWs::getColore($row->cod_strada,$row->velocita_decrescente,$colore);
		$out[] = $row;
	}
	$res = new TrafficoPage();
	$res->obj=$out;
	$res->totaleRecord=$numeroRighe[0];
	$res->paginaCorrente=$pagina;
	$res->totalePagine=ceil($res->totaleRecord/30);
	return $res;
	//	return $out;
}

function getCompartimenti(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$query= "select distinct compartimento id_compartimento, Initcap(if (compartimento='','Non definito',compartimento)) compartimento from cmv.traffico order by compartimento";
	
	error_log("Query estrazione dati traffico - compartimento. ".$query);
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

function getStrade(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$query= "select distinct strada from cmv.traffico_unito_cres_descres_v order by strada";
	
	error_log("Query estrazione dati traffico - compartimento. ".$query);
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

function getTipoTraffico(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$query= "select distinct tipo id_tipo, Initcap(tipo) tipo from cmv.traffico ";
	
	error_log("Query estrazione dati traffico - tipo. ".$query);
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
	
	$query= "SELECT id_traffico, tipo, velocita, cod_strada, nome_strada, latitudine_inizio, longitudine_inizio, latitudine_fine, longitudine_fine, direzione, compartimento, localita_inizio, localita_fine, timestamp_orig, progressiva, progressiva_fine, dataScarico FROM cmv.traffico where " ;
	if (isset($_GET['idCrescente']) && isset($_GET['idDecrescente'])) 
		$query= $query ." id_traffico = :id1 or id_traffico = :id2";
	else if (isset($_GET['idCrescente']) && !isset($_GET['idDecrescente']))
		$query= $query ." id_traffico = :id1";
	else if (!isset($_GET['idCrescente']) && isset($_GET['idDecrescente']))
		$query= $query ." id_traffico = :id2";
				
		
		
		error_log("Query estrazione dati. ".$query);
		$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
		
		if (!$stmt) {
			error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
		}

		if (isset($_GET['idCrescente']))
			$stmt->bindParam(':id1', $_GET['idCrescente']);
		if (isset($_GET['idDecrescente']))
			$stmt->bindParam(':id2', $_GET['idDecrescente']);
		
		$stmt -> execute();
		$out = array();
		while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
			//error_log("Riga: " . print_r($row, TRUE));
			$out[] = $row;
		}
		
		return $out;
		//	return $out;
}

function aggiornaVisibilita($id,$visibilita){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}

	$query= "SELECT count(*) from traffico_visibilita where id_traffico=:id ";
	
	error_log("Query estrazione dati. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	$stmt->bindParam(':id', $id);
	$stmt -> execute();
	
	$row = $stmt->fetchColumn(0);
	
	if ($row > 0)
		$query= "update cmv.traffico_visibilita set visibilita = :visibilita, Data_Aggiornamento=now() where id_traffico = :id ";
	else
		$query= "insert into cmv.traffico_visibilita (id_traffico, visibilita, Data_Aggiornamento) values (:id, :visibilita,now()) ";
	
	
	error_log($query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	$stmt->bindParam(':visibilita', $visibilita);
	$stmt->bindParam(':id', $id);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();

	if ($stmt->errorInfo()[0]!='00000'){
		error_log("Errore aggiornamento visibilita telecamera ".$stmt->errorInfo()[0]." ".$stmt->errorInfo()[1]." ".$stmt->errorInfo()[2]);
	}
}


error_log("arrivato in TrafficoWs");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['azione']) && (isset($_GET['idCrescente']) || isset($_GET['idDecrescente']))&& $_GET['azione']=='dettaglio' ) {
		error_log("GET TrafficoWs");
		$out=mostraDettaglio();
		echo json_encode($out);
	}else if (isset($_GET['azione']) && $_GET['azione']=='compartimenti' ) {
		error_log("GET compartimento traffico");
		$out=getCompartimenti();
		echo json_encode($out);
	}else if (isset($_GET['azione']) && $_GET['azione']=='tipo' ) {
		error_log("GET Tipo traffico");
		$out=getTipoTraffico();
		echo json_encode($out);
	}else if (isset($_GET['azione']) && $_GET['azione']=='strade' ) {
		error_log("GET Strade traffico");
		$out=getStrade();
		echo json_encode($out);
	}else{
		error_log("GET TrafficoWs");
		$out = listaTraffico();
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
