<?php
namespace rest\web;

class MngMeteo7ggWs{
	public static function getOrderBy(array $parametri){
		$nomeCampo="id_cam";
		$direzione = "asc";
		if (isset($parametri['campoOrdinamento'])) $nomeCampo=$parametri['campoOrdinamento'];
		if (isset($parametri['direzione'])) $direzione=$parametri['direzione'];
		if (strrpos($nomeCampo,',')) {
			$campi = explode(",", $nomeCampo);
			for ($i=0; $i< count($campi);$i++){
				$risultato = $risultato . $campi[$i]. " " . $direzione;
				if ($i < count($campi)-1) $risultato = $risultato . ',';
			}
			return "order by ". $risultato;
		}
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
		
		if (isset($parametri['regione'])){
			if (is_array($parametri['regione'])){
				$risultato=$risultato."nome_regione in (";
				for ($i = 0; $i < count($parametri['regione']);$i++){
					$risultato = $risultato."'".$parametri['regione'][$i]."'";
					if ($i < count($parametri['regione'])-1)$risultato = $risultato.", ";
					else $risultato = $risultato.") ";
				}
				
			}else{
				$risultato=$risultato."nome_regione='".$parametri['regione']."' ";
			}
		}
		if (isset($parametri['provincia'])){
			if (strlen($risultato)>0) $risultato=$risultato." AND ";
			if (is_array($parametri['provincia'])){
				$risultato=$risultato."nome_provincia in (";
				for ($i = 0; $i < count($parametri['provincia']);$i++){
					$risultato = $risultato."'".$parametri['provincia'][$i]."'";
					if ($i < count($parametri['provincia'])-1)$risultato = $risultato.", ";
					else $risultato = $risultato.") ";
				}
				
			}else{
				$risultato=$risultato."nome_provincia like '".$parametri['provincia']."%' ";
			}
		}
			
		if (isset($parametri['comune'])){
			if (strlen($risultato)>0) $risultato=$risultato." AND ";
			if (is_array($parametri['comune'])){
				$risultato=$risultato."nome_comune in (";
				for ($i = 0; $i < count($parametri['comune']);$i++){
					$risultato = $risultato."'".$parametri['comune'][$i]."'";
					if ($i < count($parametri['comune'])-1)$risultato = $risultato.", ";
					else $risultato = $risultato.") ";
				}
				
			}else{
				$risultato=$risultato."nome_comune like '".$parametri['comune']."%' ";
			}
		}
		if (strlen($risultato)>0)
			$risultato="Where ".$risultato;
			
		return $risultato;
	}
	
}