<?php
namespace rest\bean;

class CanaleCompartimentoPage {
	
	
	function __construct(){
	}
	
	public static function nodo( $nome ) {
		$instance = new self();
		$instance->nome=$nome;
		$instance->codice=$nome;
		$instance->aperto=false;
		return $instance;
	}
	
	public static function foglia($nome, $codice, $selezionato) {
		$instance = new self();
		$instance->nome=$nome;
		$instance->selezionato=$selezionato;
		$instance->codice = $codice;
		$instance->aperto=false;
		return $instance;
	}
	
	function aggiungiStrada($strada, $selezionato){
		if ($this->figli == null) $this->figli= array();
		$this->figli[count($this->figli)]= CanaleCompartimentoPage::foglia($strada, $this->codice."#".$strada, $selezionato);
	}
	
	public $codice;
	public $nome;
	public $selezionato;
	public $aperto;
	public $figli;
	
}


?>