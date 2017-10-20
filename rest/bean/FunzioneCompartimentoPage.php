<?php
namespace rest\bean;

class FunzioneCompartimentoPage {
	
	
	function __construct(){
	}
	
	public static function nodo( $idFunzione, $nomeFunzione ) {
		$instance = new self();
		$instance->nome=$nomeFunzione;
		$instance->codice=$idFunzione;
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
	
	function aggiungiCompartimento($compartimento, $selezionato){
		if ($this->figli == null) $this->figli= array();
		$this->figli[count($this->figli)]= FunzioneCompartimentoPage::foglia($compartimento, $this->codice."#".$compartimento, $selezionato);
	}
	
	public $codice;
	public $nome;
	public $selezionato;
	public $aperto;
	public $figli;
	
}


?>