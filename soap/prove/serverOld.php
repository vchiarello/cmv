<?php
// Con public ottengo errore in PHP protected è senza nulla.
class TelecamereWS{
	
	function getWebUrl($name){
		echo($name."<br>");
		$engines = array(
				'google'    => 'www.google.it',
				'yahoo' => 'www.yahoo.it'
		);
		return $engines[$name] ? $engines[$name] : "Search Engine unknown";
	}
	
}
/* OPZIONALMENTE: Definire la versione del messaggio soap. Il secondo parametro non è obbligatorio. */
$server= new SoapServer("Telecamere.wsdl", array('soap_version' => SOAP_1_2));
//$server=new SoapServer("search_engine.wsdl");
$server->setClass("TelecamereWS");
// Infine la funzione handle processa una richiesta SOAP e manda un messaggio di ritorno
// al client che l’ha richiesta.
$server->handle();