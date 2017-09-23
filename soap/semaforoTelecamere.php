<?php

include("../rest/database/ConnectionStatic.php");
use rest\database\ConnectionStatic;


class SemaforoTelecamere{
	
	function putSemaforo($name){
		
		$configFile = __DIR__ . "/../rest/config.php";
		$config = include $configFile;
		$conn = new ConnectionStatic($config);
		
		if (!isset($conn)){
			error_log("Connessione non valida.");
			echo json_encode("");
			return;
		}
		

		$select = "update cmv.anag_schedulazioni set semaforo = :valore where descrizione_schedulazione='Telecamere'";
		error_log("Query update dati. ".$select);
		$stmt = $conn->pdo->prepare($select, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);

		$valore = 0;
		if (isset($name) && isset($name->semaforo) && $name->semaforo!='0'){
			$valore = 1;
			error_log("valore del parametro. ".$name->semaforo);
			
		}
		
		$stmt->bindParam(':valore', $valore);
			
			
		if (!$stmt) {
			error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
		}
		
		$stmt -> execute();
		$out = array();
		while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$out[] = $row;
		}

		return array("semaforo" =>array("valore"=>"0","messaggioEsito"=>"Aggiornamento eseguito"));
	}
	
}



ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$server = new SoapServer("SemaforoTelecamere.wsdl");
$server->setClass("SemaforoTelecamere");
// Infine la funzione handle processa una richiesta SOAP e manda un messaggio di ritorno
// al client che l’ha richiesta.
try {
	$server->handle();
}
catch (Exception $e) {
	$server->fault('Sender', $e->getMessage());
}
?>