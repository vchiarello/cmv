<?php
namespace rest\web;

include("../database/ConnectionStatic.php");

use rest\database\ConnectionStatic;



//error_log("Sessione: ".session_id() . " - Utente: " . $_SESSION["userid"] );


function listaExportTelecamere($token, $suffisso){
	$configFile = __DIR__ . "/../config.php";
	$config = include $configFile;
	$conn = new ConnectionStatic($config);
	
	if (!isset($conn)){
		error_log("Connessione non valida.");
		echo json_encode("");
		return;
	}
	
	
	$select = "select riga_xml from telecamere_export_xml_v ";
		
	error_log("Query estrazione dati. ".$select);
	$stmt = $conn->pdo->prepare($select, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
	
	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
	}
	
	$stmt -> execute();
	$out = array();
	
	$myfile = fopen("/tmp/telecamere".$suffisso.".xml", "w");
	
	while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
		fwrite($myfile, $row->riga_xml."\n");
	}
	fclose($myfile);
}

error_log("arrivato in Telecamere Export Json");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['token']) ) {
		
		$out=listaExportTelecamere($_GET['token'],"vito");
		$zip = new \ZipArchive();
		$filename = "/tmp/testVito.zip";
		if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
			return;
		}
		$zip->addFile("telecamerevito.xml");
		$zip->close();
	}
}
?>
