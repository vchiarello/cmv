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
include("./MngUtenteWs.php");
include("../bean/Utente.php");
include("../bean/RisultatoPost.php");
include("../bean/FunzioneCompartimentoPage.php");

use rest\database\ConnectionStatic;
use rest\web\MngUtenteWs;
use rest\bean\RisultatoPost;
use rest\bean\FunzioneCompartimentoPage;
use rest\bean\Utente;



//error_log("Sessione: ".session_id() . " - Utente: " . $_SESSION["userid"] );


function listaFunzioniCompartimenti($id){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}

	//questa query serve per estrarre l'albero delle iscrizioni di un utente la prima parte della query serve ad estrarre
	//tutti i compartimenti non selezionati (è una join per fare la minus) la seconda per estrarre i nodi selezionati
	$query =
	"select fcv.id_funzione, fcv.compartimento, dd_funzione, null idlogin ".
	"from cmv.funzione_compartimento_v fcv ".
	"left join cmv.utenti_funzioni uf on fcv.compartimento = uf.compartimento and fcv.id_funzione=uf.id_funzione and uf.idlogin=:id ".
	"where uf.compartimento is null ".
	"and uf.id_funzione is null ".
	"union ".
	"select uf.id_funzione, uf.compartimento, dd_funzione, idlogin ".
	"from cmv.utenti_funzioni uf,  cmv.funzione_compartimento_v fcv ".
	"where fcv.compartimento = uf.compartimento ".
	"and fcv.id_funzione=uf.id_funzione ".
	"and uf.idlogin=:id1  order by id_funzione, compartimento ";
	
		
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
			$funzionePrecendente = null;
			$appo = null;
			while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
				if ($row->id_funzione!= $funzionePrecendente){
					$funzionePrecendente= $row->id_funzione;
					$appo = FunzioneCompartimentoPage::nodo($row->id_funzione,$row->dd_funzione);
					$out[]=$appo;
				}
				if ($row->idlogin == null)
					$appo->aggiungiCompartimento($row->compartimento, false);
				else
					$appo->aggiungiCompartimento($row->compartimento, true);
			}
			$radice = FunzioneCompartimentoPage::nodo("tutto","tutto");
			$radice->aperto=true;
			$radice->figli=$out;
			$risultato = array();
			$risultato[] = $radice;
			
			return $risultato;
}

function listaFunzioniCompartimentiVuoti(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
		$query = "select distinct id_funzione, dd_funzione, Compartimento from cmv.funzione_compartimento_v order by dd_funzione,Compartimento; ";
		
		$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
		
			
			error_log("Query estrazione numero righe. ".$query);
			$stmt -> execute();
			
			if (!$stmt) {
				error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
			}
			
			$stmt -> execute();
			$out = array();

			$funzionePrecendente = null;
			$appo = null;
			while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
				if ($row->id_funzione!= $funzionePrecendente){
					$funzionePrecendente= $row->id_funzione;
					$appo = FunzioneCompartimentoPage::nodo($row->id_funzione,$row->dd_funzione);
					$out[]=$appo;
				}

				$appo->aggiungiCompartimento($row->compartimento, false);
			}
			
			$radice = FunzioneCompartimentoPage::nodo("tutto","tutto");
			$radice->aperto=true;
			$radice->figli=$out;
			$risultato = array();
			$risultato[] = $radice;
			
			return $risultato;
}

function elencoUtenti(){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$where = MngUtenteWs::getWhere($_GET);
	
	$query = "select count(*) from cmv.login ".$where;
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	$numeroRighe=$stmt->fetch(\PDO::FETCH_NUM);
	
	
	$select = "select  idlogin ,username, password,cognome, nome from cmv.login ";
	$orderby = MngUtenteWs::getOrderBy($_GET);
	$limit = MngUtenteWs::getLimit($_GET,$numeroRighe);
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

function getUtente($id){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$query = "select  idlogin ,username, password,cognome, nome from cmv.login where idlogin = :id";
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

function deleteUtente($id){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$query = "delete from cmv.login where idlogin = :id";
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


function aggiungiUtente($utente){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$query = "insert into cmv.login ( username,password,cognome,nome) values(:username,:password,:cognome,:nome)";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':username', $utente->username);
	$stmt->bindParam(':password', sha1($utente->password));
	$stmt->bindParam(':cognome', $utente->cognome);
	$stmt->bindParam(':nome', $utente->nome);
	
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	if ($stmt->errorInfo()[0]!='00000'){
		error_log($stmt->errorInfo());
		error_log($conn->pdo->errorCode());
		
	}
	$nuovoIdUtente = $conn->pdo->lastInsertId();
	salvaFunzioniCompartimenti($_POST['nodiSelezionati'],$utente->username, $nuovoIdUtente);
	return $nuovoIdUtente;
}

function editUtente($utente){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	$utenteAppo = getUtente($utente->idlogin);
	
	
	$query = "update cmv.login set username=:username ,password = :password,cognome = :cognome, nome=:nome where idlogin = :idlogin";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':username', $utente->username);
	//se la password è cambiata allora si cripta la nuova altrimenti si mette la vecchia senza fare il cripting
	if($utenteAppo[0]->password != $utente->password)
		$stmt->bindParam(':password', sha1($utente->password));
	else 
		$stmt->bindParam(':password', $utente->password);
	$stmt->bindParam(':cognome', $utente->cognome);
	$stmt->bindParam(':nome', $utente->nome);
	$stmt->bindParam(':idlogin', $utente->idlogin);
	error_log("Query estrazione numero righe. ".$query);
	$stmt -> execute();
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	if ($stmt->errorInfo()[0]!='00000'){
		error_log($stmt->errorInfo());
		error_log($conn->pdo->errorCode());
		
	}
	salvaFunzioniCompartimenti($_POST['nodiSelezionati'],$utente->username,$utente->idlogin);
	
}

function salvaFunzioniCompartimenti($stringa,$username,$idlogin){
	$elenco = explode(",", $stringa);
	cancellaRigheFunzioniCompartimenti($idlogin);
	for ($i = 0; $i < count($elenco); $i++){
		if ( strpos ( $elenco[$i],"#")>0){
			$riga = explode("#",$elenco[$i]);
			salvaRigaFunzioneCompartimento(trim($riga[0]),trim($riga[1]),$username,$idlogin);
		}
	}
}

function cancellaRigheFunzioniCompartimenti($username){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	$query = "delete from utenti_funzioni where username = :username";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':username', $username);
	$stmt -> execute();
}

function salvaRigaFunzioneCompartimento($funzione,$compartimento,$username,$idlogin){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	$query = "select count(*) from utenti_funzioni where compartimento = :compartimento and id_funzione = :id_funzione and username = :username and idlogin=:idlogin";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':compartimento', $compartimento);
	$stmt->bindParam(':id_funzione', $funzione);
	$stmt->bindParam(':username', $username);
	$stmt->bindParam(':idlogin', $idlogin);
	$stmt -> execute();
	$numeroRighe=$stmt->fetch(\PDO::FETCH_NUM);
	
	if ($numeroRighe[0]==0)
		inserisciRiga($compartimento, $funzione, $username,$idlogin);
	else
		updateRiga($compartimento, $funzione, $username,$idlogin);
			
			
}

function inserisciRiga($compartimento, $funzione, $username,$idlogin){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	$query = "insert into cmv.utenti_funzioni (compartimento, id_funzione, username,idlogin ) values(:compartimento,:id_funzione,:username,:idlogin)";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':compartimento', $compartimento);
	$stmt->bindParam(':id_funzione', $funzione);
	$stmt->bindParam(':username', $username);
	$stmt->bindParam(':idlogin', $idlogin);
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

function updateRiga($compartimento,$funzione,$username,$idlogin){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	$query = "update cmv.utenti_funzioni set compartimento = :compartimento, id_funzione = :id_funzione, username = :username, idlogin=:idlogin where compartimento = :compartimento_new and id_funzione = :id_funzione_new and username = :username_new and idlogin = :idlogin_new ";
	$stmt = $conn->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	$stmt->bindParam(':compartimento', $compartimento);
	$stmt->bindParam(':id_funzione', $funzione);
	$stmt->bindParam(':username', $username);
	$stmt->bindParam(':idlogin', $idlogin);
	$stmt->bindParam(':compartimento_new', $compartimento);
	$stmt->bindParam(':id_funzione_new', $funzione);
	$stmt->bindParam(':username_new', $username);
	$stmt->bindParam(':idlogin_new', $idlogin);
	
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


error_log("arrivato in UtenteWs");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['azione']) && $_GET['azione']=='Funzioni' ) {
		error_log("UtenteWs GET info Utente per funzioni");
		if ($_GET['id']==null)
			$out=listaFunzioniCompartimentiVuoti();
		else 
			$out=listaFunzioniCompartimenti($_GET['id']);
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='Utente' ) {
		error_log("UtenteWS GET Elenco utenti");
		$out=elencoUtenti();
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='New' ) {
		error_log("GET UtenteWs, New utente...");
		$out=listaFunzioniCompartimenti();
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='Get' && isset($_GET['id'])) {
		error_log("GET UtenteWs, get utente...");
		$out=getUtente($_GET['id']);
		echo json_encode($out);
	}elseif (isset($_GET['azione']) && $_GET['azione']=='Delete' && isset($_GET['id'])) {
		error_log("GET UtenteWs, cancellazione utente...");
		$out=deleteUtente($_GET['id']);
		echo json_encode($out);
	}else{
		error_log("GET ElencoUtenti");
		$out = elencoUtenti();
		echo json_encode($out);
	}	
	
}elseif ($_SERVER['REQUEST_METHOD'] === 'POST'){
	error_log("POST UtenteWs");
	if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['cognome']) && isset($_POST['nome']) && isset($_POST['azione']) && $_POST['azione']=='New') {
		$c = new Utente($_POST);
		error_log("POST UtenteWs, New ");
		$idNuovoUtente = aggiungiUtente($c);	
		$out=getUtente($idNuovoUtente);
		$risultato = new RisultatoPost('Record salvato.', '');
		echo json_encode($out);
	}else if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['cognome']) && isset($_POST['nome']) && isset($_POST['azione']) && $_POST['azione']=='Edit'){
		$c = new Utente($_POST);
		error_log("POST UtenteWs, Edit ");
		editUtente($c);
		$out=getUtente($c->idlogin);
		$risultato = new RisultatoPost('Record salvato.', '');
		echo json_encode($out);
	}
	
	
}

?>
