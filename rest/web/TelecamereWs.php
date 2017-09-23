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



function base64_to_jpeg($base64_string, $output_file) {
	// open the output file for writing
	$ifp = fopen( $output_file, 'wb' );
	
	// split the string on commas
	// $data[ 0 ] == "data:image/png;base64"
	// $data[ 1 ] == <actual base64 string>
	$data = explode( ',', $base64_string );
	
	// we could add validation here with ensuring count( $data ) > 1
	if (count($data)>1)
		$ff=base64_decode( $base64_string);
	else 
		$ff=base64_decode( $base64_string);
	fwrite( $ifp,  $ff);
	
	// clean up the file resource
	fclose( $ifp );
	
	//return $output_file;
}

include("../database/ConnectionStatic.php");
include("../bean/Telecamere.php");
include("../bean/TelecamerePage.php");
include("./MngTelecamereWs.php");

use rest\database\ConnectionStatic;
use rest\web\MngTelecamereWs;
use rest\bean\TelecamerePage;



//error_log("Sessione: ".session_id() . " - Utente: " . $_SESSION["userid"] );


function listaTelecamere(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$where = MngTelecamereWs::getWhere($_GET);
	
	$query = "select count(*) from cmv.telecamere_v ".$where;
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	$numeroRighe=$stmt->fetch(\PDO::FETCH_NUM);
	
	$select = "SELECT id,id_cam,direzione,strada,descrizione,data_sovrimpressione,data_sovr,data_sovr_s, visibilita, data_scarico from cmv.telecamere_v ";
	$orderby = MngTelecamereWs::getOrderBy($_GET);
	$limit = MngTelecamereWs::getLimit($_GET,$numeroRighe);
	
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
		//error_log("Riga: " . print_r($row, TRUE));
		$out[] = $row;
	}
	$res = new TelecamerePage();
	$res->obj=$out;
	$res->totaleRecord=$numeroRighe[0];
	$res->paginaCorrente=$pagina;
	$res->totalePagine=ceil($res->totaleRecord/30);
	return $res;
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
	
	$query= "SELECT id,id_cam,nome, strada, latitudine, longitudine, km, owner,descrizione, direzione, disponibilita, visibilita from cmv.telecamere_v where id=:id";
	
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
	
	$query= "SELECT id_regione, regione from cmv.regione_telecamere_v order by regione";
	
	error_log("Query estrazione dati. ".$query);
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
	
	$query= "select distinct strada from cmv.telecamere_v  order by strada";
	
	error_log("Query estrazione dati. ".$query);
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

function aggiornaVisibilita($id,$visibilita){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}

	$query= "SELECT count(*) from telecamere_visibilita where id_cam=:id_cam ";
	
	error_log("Query estrazione dati. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	$stmt->bindParam(':id_cam', $id);
	$stmt -> execute();
	
	$row = $stmt->fetchColumn(0);
	
	if ($row > 0)
		$query= "update cmv.telecamere_visibilita set visibilita = :visibilita, Data_Aggiornamento=now() where id_cam = :id ";
	else
		$query= "insert into cmv.telecamere_visibilita (id_cam, visibilita, Data_Aggiornamento) values (:id, :visibilita,now()) ";
	
	
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


error_log("arrivato in TelecamereWs");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['azione']) && isset($_GET['id'])&& $_GET['azione']=='dettaglio' ) {
		error_log("GET TelecamereWs");
		$out=mostraDettaglio();
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='regioni' ) {
		error_log("GET Regioni telecamere");
		$out=getRegioni();
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='strade' ) {
		error_log("GET Strade telecamere");
		$out=getStrade();
		echo json_encode($out);
	}else{
		error_log("GET TelecamereWs");
		$out = listaTelecamere();
		echo json_encode($out);
	}	
	
}elseif ($_SERVER['REQUEST_METHOD'] === 'POST'){
	error_log("POST TelecamereWs");
	error_log($_POST['id']);
	error_log($_POST['visibilita']);
	if (isset($_POST['id']) && isset($_POST['visibilita'])) {
		$id=$_POST['id'];
		$visibilita=$_POST['visibilita'];
		error_log("POST TelecamereWs aggiornaVisibilita id, visibilita ". $id .", ".$visibilita);
		aggiornaVisibilita($id,$visibilita);		
	}
	
	
}

?>
