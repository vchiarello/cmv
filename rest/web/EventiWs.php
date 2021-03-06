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
include("../bean/EventiPage.php");
include("./MngEventiWs.php");

use rest\database\ConnectionStatic;
use rest\web\MngEventiWs;
use rest\bean\EventiPage;



//error_log("Sessione: ".session_id() . " - Utente: " . $_SESSION["userid"] );


function listaEventi(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$where = MngEventiWs::getWhere($_GET);
	
	
	$query = "select count(*) from cmv.evento_v ".$where;
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	$numeroRighe=$stmt->fetch(\PDO::FETCH_NUM);

	
	$select = "SELECT id_evento,flagApertura, flagModifica, flagChiusura, tipo,id,id_evoluzione,versione,des_compartimento,descrizione,descrizione_causa,stato,strada,sigla_strada,direzione,dal_km,al_km,note_pubblice,note_cciss,id_ordinanza,dataora_apertura,dataora_chiusura,dataora_nota_cciss,dataora_mod,flag_blocco_invio_cciss,flag_invio_cciss,data_flag_blocco_cciss,data_invio_cciss,messaggio_esito,flag_inviato_cmv,data_inviato_cmv,esito_invio_cmv,esito_invio_cmv_desc,dataScarico from cmv.evento_v ";
	$orderby = MngEventiWs::getOrderBy($_GET);
	$limit = MngEventiWs::getLimit($_GET,$numeroRighe);
	
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
//		$appo->coloreCrescente=MngEventiWs::getColore($row->cod_strada,$row->velocita_crescente,$colore);
//		$appo->coloreDecrescente=MngEventiWs::getColore($row->cod_strada,$row->velocita_decrescente,$colore);
		$out[] = $row;
	}
	$res = new EventiPage();
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
	
	$query= "select distinct des_compartimento id_compartimento, Initcap(Coalesce(des_compartimento,'Non definito')) compartimento from cmv.evento ";
	
	error_log("Query estrazione dati Eventi - compartimento. ".$query);
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
	
	$query= "SELECT id_evento,flagApertura, flagModifica, flagChiusura, tipo,id,id_evoluzione,versione,des_compartimento,descrizione,descrizione_causa,stato,strada,sigla_strada,direzione,dal_km,al_km,note_pubblice,note_cciss,id_ordinanza,dataora_apertura,dataora_chiusura,dataora_nota_cciss,dataora_mod,flag_blocco_invio_cciss,flag_invio_cciss,data_flag_blocco_cciss,data_invio_cciss,messaggio_esito,flag_inviato_cmv,data_inviato_cmv,esito_invio_cmv,esito_invio_cmv_desc,dataScarico from cmv.evento_v where id_evento = :id_evento" ;
	
	
	
	error_log("Query estrazione dati. ".$query);
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt->bindParam(':id_evento', $_GET['id_evento']);
	
	$stmt -> execute();
	$out = array();
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		//error_log("Riga: " . print_r($row, TRUE));
		$out[] = $row;
	}
	
	return $out;
}

function mostraDettagli(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$query= "SELECT id_evento,flagApertura, flagModifica, flagChiusura, tipo,id,id_evoluzione,versione,des_compartimento,descrizione,descrizione_causa,stato,strada,sigla_strada,direzione,dal_km,al_km,note_pubblice,note_cciss,id_ordinanza,dataora_apertura,dataora_chiusura,dataora_nota_cciss,dataora_mod,flag_blocco_invio_cciss,flag_invio_cciss,data_flag_blocco_cciss,data_invio_cciss,messaggio_esito,flag_inviato_cmv,data_inviato_cmv,esito_invio_cmv,esito_invio_cmv_desc,dataScarico from cmv.evento_v where id = :id" ;
	
	
	
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

function aggiornaVisibilita($id,$visibilita){
// 	$configFile = __DIR__ . "/../config.php";
// 	$config = include $configFile;
// 	$conn = new ConnectionStatic($config);
	
// 	if (!isset($conn)){
// 		error_log("Connessione non valida.");
// 		echo json_encode("");
// 		return;
// 	}

// 	$query= "SELECT count(*) from traffico_visibilita where id_traffico=:id ";
	
// 	error_log("Query estrazione dati. ".$query);
// 	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
// 	if (!$stmt) {
// 		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
// 	}
// 	$stmt->bindParam(':id', $id);
// 	$stmt -> execute();
	
// 	$row = $stmt->fetchColumn(0);
	
// 	if ($row > 0)
// 		$query= "update cmv.traffico_visibilita set visibilita = :visibilita, Data_Aggiornamento=now() where id_traffico = :id ";
// 	else
// 		$query= "insert into cmv.traffico_visibilita (id_traffico, visibilita, Data_Aggiornamento) values (:id, :visibilita,now()) ";
	
	
// 	error_log($query);
// 	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
// 	$stmt->bindParam(':visibilita', $visibilita);
// 	$stmt->bindParam(':id', $id);
	
// 	if (!$stmt) {
// 		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
// 	}
	
// 	$stmt -> execute();

// 	if ($stmt->errorInfo()[0]!='00000'){
// 		error_log("Errore aggiornamento visibilita telecamera ".$stmt->errorInfo()[0]." ".$stmt->errorInfo()[1]." ".$stmt->errorInfo()[2]);
// 	}
}


error_log("arrivato in EventiWs");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['azione']) && $_GET['azione']=='dettaglio' && isset($_GET['id'])) {
		error_log("GET dettaglio EventiWs per id, potenzialmente più righe di db.");
		$out=mostraDettagli();
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='dettaglio' && isset($_GET['id_evento'])){
		error_log("GET dettaglio EventiWs per id_evento una sola riga.");
		$out=mostraDettaglio();
		echo json_encode($out);
	}else if (isset($_GET['azione']) && $_GET['azione']=='compartimenti' ) {
		error_log("GET compartimento eventi");
		$out=getCompartimenti();
		echo json_encode($out);
	}else{
		error_log("GET EventiWs");
		$out = listaEventi();
		echo json_encode($out);
	}	
	
}elseif ($_SERVER['REQUEST_METHOD'] === 'POST'){
	error_log("POST EventiWs");
	error_log($_POST['id']);
	error_log($_POST['visibilita']);
	if (isset($_POST['id']) && isset($_POST['visibilita'])) {
		$id=$_POST['id'];
		$visibilita=$_POST['visibilita'];
		error_log("POST EventiWs aggiornaVisibilita id, visibilita ". $id .", ".$visibilita);
		aggiornaVisibilita($id,$visibilita);		
	}
	
	
}

?>
