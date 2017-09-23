<?php
// Con public ottengo errore in PHP protected è senza nulla.

class weburl{
	public $IDCam;
	public $Nome;
}

class TelecamereWS{
	
	function getWebUrl($name){
// 		echo($name."<br>");
// 		$res = new weburl();
// 		$res->IDCam="idCamera1";
// 		$res->Nome="nomeCamera1";
		
		error_log("Vito pippo1");
		//return $res;
		return array("row"=> array("IDCam"=>"idCamera1", "Nome"=>"nomeCamera1") );
	}
	
}
/* OPZIONALMENTE: Definire la versione del messaggio soap. Il secondo parametro non è obbligatorio. */
$server= new SoapServer("Telecamere.wsdl", array('soap_version' => SOAP_1_2));
//$server=new SoapServer("search_engine.wsdl");
$server->setClass("TelecamereWS");
// Infine la funzione handle processa una richiesta SOAP e manda un messaggio di ritorno
// al client che l’ha richiesta.
$server->handle();