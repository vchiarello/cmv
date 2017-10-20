<?php
namespace rest\web;

class MngUtenteWs{
	public static function getOrderBy(array $parametri){
		$nomeCampo="descrizione_canale";
		$direzione = "asc";
		if (isset($parametri['campoOrdinamento'])) $nomeCampo=$parametri['campoOrdinamento'];
		if (isset($parametri['direzione'])) $direzione=$parametri['direzione'];
		return "order by ". $nomeCampo. " " . $direzione;
		
	}

	public static function getLimit(array $parametri, $fine){
		$inizio=0;
		$finePagina = 30;
		if (isset($parametri['pagina'])) {
			$inizio=($parametri['pagina'])*30;
			if ($inizio+30>$fine[0]) $finePagina=$fine[0]-$inizio;
		}
		return " limit ". $inizio. ", " . $finePagina;
		
	}
	
	public static function getWhere(array $parametri){
		$risultato="";
		
		if (isset($parametri['filtro'])){
			$f = $parametri['filtro'];
			$risultato = "username like '%".$f."%' or "."cognome like '%".$f."%' or nome like '%".$f."%' ";
		}
		if (strlen($risultato)>0)
			$risultato="Where ".$risultato;
				
		return $risultato;
	}
	
}