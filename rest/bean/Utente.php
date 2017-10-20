<?php 

namespace rest\bean;

class Utente {
	function __construct($arr){
		$this->idlogin = $arr['id'];
		$this->username = $arr['username'];
		$this->cognome = $arr['cognome'];
		$this->nome = $arr['nome'];
		$this->password = $arr['password'];
		
	}
	
	
}

?>