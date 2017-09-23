<?php
function getUsers($args) {
	$args = (array)$args;
	return array("getUsersArray" => array(
			array("id"=>"1",
					"firstname"=>"Barney",
					"surname"=>"Rubble",
					"message"=>$args["message"]),
			array("id"=>"2",
					"firstname"=>"Fred",
					"surname"=>"Flintstone",
					"message"=>$args["message"])
	)
	);
}
error_log("Vito pippo");
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$server = new SoapServer("test1.wsdl");
$server->addFunction("getUsers");
try {
	$server->handle();
}
catch (Exception $e) {
	$server->fault('Sender', $e->getMessage());
}
?>