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
include("./MngCanaleWs.php");
include("../bean/Canale.php");
include("../bean/RisultatoPost.php");
include("../bean/CanaleCompartimentoPage.php");

use rest\database\ConnectionStatic;
use rest\web\MngCanaleWs;
use rest\bean\Canale;
use rest\bean\RisultatoPost;
use rest\bean\CanaleCompartimentoPage;



//error_log("Sessione: ".session_id() . " - Utente: " . $_SESSION["userid"] );


function listaCompartimentiStrade($id){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
		$query = "select ccsv.cod_strada, ccsv.compartimento, null id_canale from ".
				"cmv.canali_compartimenti_strade_view ccsv ".
				"left join cmv.filtro_canale fc on ccsv.compartimento = fc.compartimento and ccsv.cod_strada=fc.cod_strada and fc.id_canale=:id ".
				"where fc.compartimento is null ".
				"and fc.cod_strada is null ".
				"union ".
				"select f.cod_strada, f.compartimento, id_canale from cmv.filtro_canale f ".
				"where f.id_Canale=:id1 order by compartimento, cod_strada";
		
		
		$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
		
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':id1', $id);
		
			error_log("Query estrazione numero righe. ".$query);
			$stmt -> execute();
			
			if (!$stmt) {
				error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
			}
			
			$stmt -> execute();
			$out = array();
			$comporPrecendente = null;
			$appo = null;
			while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
				if ($row->compartimento != $comporPrecendente){
					$comporPrecendente = $row->compartimento;
					$appo = CanaleCompartimentoPage::nodo($row->compartimento);
					$out[]=$appo;
				}
				if ($row->id_canale == null)
					$appo->aggiungiStrada($row->cod_strada, false);
				else
					$appo->aggiungiStrada($row->cod_strada, true);
			}
			$radice = CanaleCompartimentoPage::nodo("tutto");
			$radice->aperto=true;
			$radice->figli=$out;
			$risultato = array();
			$risultato[] = $radice;
			
			return $risultato;
}

function listaCompartimentiStradeVuoti(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
		$query = "select distinct compartimento, cod_strada from cmv.traffico_compartimenti_strade_v by compartimento,cod_strada; ";
		
		$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
		
			
			error_log("Query estrazione numero righe. ".$query);
			$stmt -> execute();
			
			if (!$stmt) {
				error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
			}
			
			$stmt -> execute();
			$out = array();
			$comporPrecendente = null;
			$appo = null;
			while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
				if ($row->compartimento != $comporPrecendente){
					$comporPrecendente = $row->compartimento;
					$appo = CanaleCompartimentoPage::nodo($row->compartimento);
					$out[]=$appo;
				}
				$appo->aggiungiStrada($row->cod_strada, true);
			}
			$radice = CanaleCompartimentoPage::nodo("tutto");
			$radice->aperto=true;
			$radice->figli=$out;
			$risultato = array();
			$risultato[] = $radice;
			
			return $risultato;
}

function elencoCanali(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$where = MngCanaleWs::getWhere($_GET);
	
	$query = "select count(*) from cmv.canale ".$where;
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	$numeroRighe=$stmt->fetch(\PDO::FETCH_NUM);
	
	
	$select = "select  id_canale ,descrizione_canale,token,indirizzo from cmv.canale ";
	$orderby = MngCanaleWs::getOrderBy($_GET);
	$limit = MngCanaleWs::getLimit($_GET,$numeroRighe);
	$query = $select.$orderby.$limit;
	
	
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	
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

function getCanale($id){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$query = "select  id_canale ,descrizione_canale,token,indirizzo from cmv.canale where id_canale = :id";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':id', $id);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	
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

function deleteCanale($id){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$query = "delete from cmv.canale where id_canale = :id";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':id', $id);
	error_log("Query cancellazione riga. ".$query);
	$stmt -> execute();
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$out = new RisultatoPost("Rercord cancellato", "");
	return $out;
}


function aggiungiCanale($canale){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$query = "insert into cmv.canale ( descrizione_canale,token,indirizzo) values(:descrizione_canale,:token,:indirizzo)";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':descrizione_canale', $canale->descrizione);
	$stmt->bindParam(':token', $canale->token);
	$stmt->bindParam(':indirizzo', $canale->indirizzo);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	if ($stmt->errorInfo()[0]!='00000'){
		error_log($stmt->errorInfo());
		error_log($conn->pdo->errorCode());
		
	}
	salvaCompartimentiStrade($_POST['nodiSelezionati'],$canale->id);
	
}

function editCanale($canale){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$query = "update cmv.canale set descrizione_canale=:descrizione_canale ,token = :token,indirizzo = :indirizzo where id_canale = :id_canale";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':descrizione_canale', $canale->descrizione);
	$stmt->bindParam(':token', $canale->token);
	$stmt->bindParam(':indirizzo', $canale->indirizzo);
	$stmt->bindParam(':id_canale', $canale->id);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	if ($stmt->errorInfo()[0]!='00000'){
		error_log($stmt->errorInfo());
		error_log($conn->pdo->errorCode());
		
	}
	salvaCompartimentiStrade($_POST['nodiSelezionati'],$canale->id);
	
}

function salvaCompartimentiStrade($stringa,$idCanale){
	$elenco = explode(",", $stringa);
	cancellaRigheCompartimentoStrada($idCanale);
	for ($i = 0; $i < count($elenco); $i++){
		if ( strpos ( $elenco[$i],"#")>0){
			$riga = explode("#",$elenco[$i]);
			salvaRigaCompartimentoStrada(trim($riga[0]),trim($riga[1]),$idCanale);
		}
	}
}

function cancellaRigheCompartimentoStrada($idCanale){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	$query = "delete from filtro_canale where id_canale = :id_canale";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':id_canale', $idCanale);
	$stmt -> execute();
}

function salvaRigaCompartimentoStrada($compartimento,$strada,$idCanale){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	$query = "select count(*) from filtro_canale where compartimento = :compartimento and cod_strada = :strada and id_canale = :id_canale";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':compartimento', $compartimento);
	$stmt->bindParam(':strada', $strada);
	$stmt->bindParam(':id_canale', $idCanale);
	$stmt -> execute();
	$numeroRighe=$stmt->fetch(\PDO::FETCH_NUM);
	
	if ($numeroRighe[0]==0)
		inserisciRiga($compartimento, $strada, $idCanale);
		else
			updateRiga($compartimento, $strada, $idCanale);
			
			
}

function inserisciRiga($compartimento,$strada,$idCanale){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	$query = "insert into cmv.filtro_canale ( funzione,cod_strada,compartimento,id_canale) values('*',:cod_strada,:compartimento,:id_canale)";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':compartimento', $compartimento);
	$stmt->bindParam(':cod_strada', $strada);
	$stmt->bindParam(':id_canale', $idCanale);
	$stmt->execute();
	error_log("Query estrazione numero righe. ".$query);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
 	if ($stmt->errorInfo()[0]!='00000'){
 		error_log($stmt->errorInfo()[0]);
 		error_log($conn->pdo->errorCode());
		
 	}
	
}

function updateRiga($compartimento,$strada,$idCanale){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	$query = "update cmv.filtro_canale set funzione='*',cod_strada = :strada_new,compartimento=:compartimento_new,id_canale=:id_canale_new where cod_strada = :strada and compartimento=:compartimento and id_canale=:id_canale ";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':compartimento', $compartimento);
	$stmt->bindParam(':strada', $strada);
	$stmt->bindParam(':id_canale', $idCanale);
	$stmt->bindParam(':compartimento_new', $compartimento);
	$stmt->bindParam(':strada_new', $strada);
	$stmt->bindParam(':id_canale_new', $idCanale);
	$stmt->execute();
	error_log("Query estrazione numero righe. ".$query);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	if ($stmt->errorInfo()[0]!='00000'){
		error_log($stmt->errorInfo());
		error_log($conn->pdo->errorCode());
		
	}
	
}


error_log("arrivato in CanaleWs");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['azione']) && $_GET['azione']=='Compartimento' ) {
		error_log("CanaleWs GET info Canali per compartimenti");
		if ($_GET['id']==null)
			$out=listaCompartimentiStradeVuoti();
		else 
			$out=listaCompartimentiStrade($_GET['id']);
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='Canale' ) {
		error_log("GET Elenco canali");
		$out=elencoCanali();
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='New' ) {
		error_log("GET CanaleWs, New canale...");
		$out=listaCompartimentiStrade("nuovo");
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='Get' && isset($_GET['id'])) {
		error_log("GET CanaleWs, get canale...");
		$out=getCanale($_GET['id']);
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='Delete' && isset($_GET['id'])) {
		error_log("GET CanaleWs, cancellazione canale...");
		$out=deleteCanale($_GET['id']);
		echo json_encode($out);
	}else{
		error_log("GET ElencoCanali");
		$out = elencoCanali();
		echo json_encode($out);
	}	
	
}elseif ($_SERVER['REQUEST_METHOD'] === 'POST'){
	error_log("POST CanaleWs");
	if (isset($_POST['descrizione']) && isset($_POST['token']) && isset($_POST['indirizzo']) && isset($_POST['azione']) && $_POST['azione']=='New') {
		$c = new Canale($_POST);
		error_log("POST CanaleWs, New ");
		aggiungiCanale($c);		
		$risultato = new RisultatoPost('Record salvato.', '');
		echo json_encode($risultato);
	}else if(isset($_POST['descrizione']) && isset($_POST['token']) && isset($_POST['indirizzo']) && isset($_POST['azione']) && $_POST['azione']=='Edit'){
		$c = new Canale($_POST);
		error_log("POST CanaleWs, Edit ");
		editCanale($c);
		$risultato = new RisultatoPost('Record salvato.', '');
		echo json_encode($risultato);
	}
	
	
}

?>
