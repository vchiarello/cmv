<?php 

namespace rest\bean;

class RisultatoPost {
	function __construct($messaggio, $errore){
		$this->messaggio = $messaggio;
		$this->errore = $errore;
	}
	
	
}

?>