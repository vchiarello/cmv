<?php 

namespace rest\bean;

class Canale {
	function __construct($arr){
		$this->id = $arr['id'];
		$this->descrizione = $arr['descrizione'];
		$this->token = $arr['token'];
		$this->indirizzo = $arr['indirizzo'];
		
	}
	
	
}

?>