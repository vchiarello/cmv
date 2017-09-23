<?php
namespace rest\thread;

class StatoThread extends Thread {
	
	public function __construct($stato,$risultatoAzione){
		$this->stato=$stato;
		$this->risultatoAzione=$risulstatoAzione;
	}
	
}
