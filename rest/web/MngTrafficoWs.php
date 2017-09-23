<?php
namespace rest\web;

class MngTrafficoWs{
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
	
	public static function getColore($cod_strada, $velocita, array $arrayColori){
		$numVelocita = (int)$velocita;
		for ($i=0; $i< count($arrayColori);$i++){
			if (substr($cod_strada,0,1)==$arrayColori[$i]->tipo_strada && 
					$numVelocita>= $arrayColori[$i]->velocita_inizio &&
					$numVelocita<= $arrayColori[$i]->velocita_fine
					)
				return 	$arrayColori[$i]->colore;
		}
		return "000000";
	}

	public static function getWhere(array $parametri){
		$risultato="";
		
		if (isset($parametri['tipo']))
			$risultato=$risultato."tipo='".$parametri['tipo']."'";
			
			// 			if (isset($parametri['strada'])) {
			// 				if (strlen($risultato)>0) $risultato=$risultato." AND ";
			// 				$risultato=$risultato."(upper(cod_strada) like upper('%".$parametri['strada']."%') OR ".
			// 						"upper(nome_strada) like upper('%".$parametri['strada']."%'))";
			// 			}

			if (isset($parametri['strada'])) {
				if (strlen($risultato)>0) $risultato=$risultato." AND ";
				$risultato=$risultato." upper(strada) like upper('%".$parametri['strada']."%') ";
			}
			
			if (isset($parametri['operatore']) && isset($parametri['velocita'])) {
				if (strlen($risultato)>0) $risultato=$risultato." AND ";
				$risultato=$risultato."(velocita_crescente ".$parametri['operatore']." ". $parametri['velocita']." OR ".
						               "velocita_decrescente ".$parametri['operatore']." ". $parametri['velocita']. ")";
			}
			
			if (isset($parametri['compartimento'])){
				if (strlen($risultato)>0) $risultato=$risultato." AND ";
				
				if (is_array($parametri['compartimento'])){
					$risultato=$risultato."compartimento in (";
					for ($i = 0; $i < count($parametri['compartimento']);$i++){
						$risultato = $risultato."'".$parametri['compartimento'][$i]."'";
						if ($i < count($parametri['compartimento'])-1)$risultato = $risultato.", ";
						else $risultato = $risultato.") ";
					}
					
				}else{
					$risultato=$risultato."compartimento='".$parametri['compartimento']."' ";
				}
			}
			if (strlen($risultato)>0)
				$risultato="Where ".$risultato;
				
				return $risultato;
	}
	
}