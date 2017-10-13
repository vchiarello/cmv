<?php
namespace rest\web;

include("../database/ConnectionStatic.php");

use rest\database\ConnectionStatic;



//error_log("Sessione: ".session_id() . " - Utente: " . $_SESSION["userid"] );


function listaTelecamereExport($token){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$select = "select id_cam ,nome,codice_strada,latitudine,longitudine,km,owner,descrizione,direzione,disponibilita,regione,data_sovrimpressione,immagine from telecamere_export_v where token='".$token."'";
		
	error_log("Query estrazione dati. ".$select);
	$stmt = $conn->pdo->prepare($select, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
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

error_log("arrivato in Telecamere Export Json");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['token']) ) {
		
		$out=listaTelecamereExport($_GET['token']);
		echo json_encode($out);
	}
}
?>
