<?php
namespace rest\web;

class MngTelecamereWs{
	public static function getOrderBy(array $parametri){
		$nomeCampo="id_cam";
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
		
		if (isset($parametri['visibilita'])) 
			$risultato=$risultato."visibilita='".$parametri['visibilita']."'";
		
			if (isset($parametri['disponibilita'])) {
				if (strlen($risultato)>0) $risultato=$risultato." AND ";
				$risultato=$risultato."disponibilita = '".$parametri['disponibilita']."'";
			}
			
			
			if (isset($parametri['nomeStrada'])) {
				if (strlen($risultato)>0) $risultato=$risultato." AND ";
				$risultato=$risultato."upper(strada) like upper('%".$parametri['nomeStrada']."%')";
			}
			
			if (isset($parametri['regioneSelezionata'])){
			if (strlen($risultato)>0) $risultato=$risultato." AND ";
			
			if (is_array($parametri['regioneSelezionata'])){
				$risultato=$risultato."regione in (";
				for ($i = 0; $i < count($parametri['regioneSelezionata'])-1;$i++){
					$risultato = $risultato."'".$parametri['regioneSelezionata'][$i]."',";
				}
				$risultato = $risultato."'".$parametri['regioneSelezionata'][count($parametri['regioneSelezionata'])-1]."')";
			}else{
				$risultato=$risultato."regione='".$parametri['regioneSelezionata']."' ";
			}
		}
		if (strlen($risultato)>0)
			$risultato="Where ".$risultato;

		return $risultato;	
	}
}