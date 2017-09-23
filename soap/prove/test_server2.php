<?php
function getUsers($args) {
	$args = (array)$args;
	return array("User1" =>	array(array("id1"=>"1","firstname1"=>"Barney","surname1"=>"Rubble","message1"=>$args["message"]),
			     array("id1"=>"2","firstname1"=>"Barney2","surname1"=>"Rubble2","message1"=>$args["message"]))
	
	);
}
error_log("Vito pippo");
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$server = new SoapServer("test2.wsdl");
$server->addFunction("getUsers");
try {
	$server->handle();
}
catch (Exception $e) {
	$server->fault('Sender', $e->getMessage());
}
?>