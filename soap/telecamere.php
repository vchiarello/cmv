<?php

include("../rest/database/ConnectionStatic.php");
use rest\database\ConnectionStatic;


class TelecamereExport{
	
	function getInfo($name){
		
		$configFile = __DIR__ . "/../rest/config.php";
		$config = include $configFile;
		$conn = new ConnectionStatic($config);
		
		if (!isset($conn)){
			error_log("Connessione non valida.");
			echo json_encode("");
			return;
		}

		$select = "select id_cam ,nome,codice_strada,latitudine,longitudine,km,owner,descrizione,direzione,disponibilita,regione,data_sovrimpressione,immagine from telecamere_export_v ";//where id_cam in ('to-CE_08','to-CE_09')";
		error_log("Query estrazione dati. ".$select);
		$stmt = $conn->pdo->prepare($select, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
		
		if (!$stmt) {
			error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->errorInfo(),true) );
		}
		
		$stmt -> execute();
		$out = array();
		while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$out[] = $row;
// 			$riga = array("id_cam"=> $row->id_cam,
// 						"nome"=> $row->nome,
// 					"codice_strada"=> $row->codice_strada,
// 					"latitudine"=> $row->latitudine,
// 					"longitudine"=> $row->longitudine,
// 					"km"=> $row->km,
// 					"owner"=> $row->owner,
// 					"descrizione"=> $row->descrizione,
// 					"direzione"=> $row->direzione,
// 					"disponibilita"=> $row->disponibilita,
// 					"regione"=> $row->regione,
// 					"data_sovrimpressione"=> $row->data_sovrimpressione,
// 					"immagine"=> $row->immagine);
// 			$out[]=$riga;
//			error_log($row->id_cam);
		}
		
				return array("telecamera" =>$out);
		
		// 		return array("telecamera" =>
		// 				array(array("id_cam"=>"1","Nome"=>"Barney"),
		// 						array("id_cam"=>"2","Nome"=>"Barney2"))
		// 		);
	}
	
}



ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$server = new SoapServer("Telecamere.wsdl");
$server->setClass("TelecamereExport");
// Infine la funzione handle processa una richiesta SOAP e manda un messaggio di ritorno
// al client che l’ha richiesta.
try {
	$server->handle();
}
catch (Exception $e) {
	$server->fault('Sender', $e->getMessage());
}
?>