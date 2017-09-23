//controller per la creazione degli item
angular.module("cmv").controller("MeteoSettimanaleController", ['$scope', '$http', function ($scope, $http) {

    $scope.campoOrdinato = 'nome_comune';
    $scope.direzione ='asc';
    $scope.pagina=0;
    $scope.dettaglio=false;

    //campi relativi ai filtri
    $scope.filtro=true;
    $scope.regioneSelezionata = null;
    $scope.provincia = null;
    $scope.comune = null;
    
    
	function init() {
		console.log("meteo settimanale controller")
		getMeteo7gg();
		getRegioni();
		getProvince();
		getComuni();
	}

	$scope.changeComune = function(){
		//if ($scope.comune.length>2) 
		getMeteo7gg();
	}
	
	$scope.changeProvincia = function(){
		//if ($scope.provincia.length>2) 
		getMeteo7gg();
	}
	
	$scope.changeRegione = function(){
		getMeteo7gg();
	}
	
	function getMeteo7gg(){
		var urlMeteo7gg = 'rest/web/Meteo7ggWs.php?campoOrdinamento='+$scope.campoOrdinato+'&direzione='+$scope.direzione+'&pagina='+$scope.pagina;
		if ($scope.regioneSelezionata != null)
			for (i=0; i < $scope.regioneSelezionata.length;i++)
				if ($scope.regioneSelezionata[i].length>0)
					urlMeteo7gg += '&regione[]='+$scope.regioneSelezionata[i];
		if ($scope.provincia != null)
			urlMeteo7gg += '&provincia='+$scope.provincia;
		if ($scope.comune!=null)
			urlMeteo7gg += '&comune='+$scope.comune;

		$scope.promessa= $http({method: 'GET',url:urlMeteo7gg}).
		then(function successCallback(response){
			console.log("Meteo7gg scaricato correttamente");
			$scope.meteo7gg = response.data;

			console.log("scaricati " + $scope.meteo7gg.obj.length + "record")
			$scope.navigatore = riempiNavigatore($scope.meteo7gg.paginaCorrente,$scope.meteo7gg.totalePagine);
		},
		function errorCallback(response){alert("Errore nello scarico del Meteo7gg")});
	}

	$scope.mostraDettaglio = function(idLocalita){
		caricaDettaglio(idLocalita);
	}

	function caricaDettaglio(idLocalita){
		$scope.promessaDettaglio= $http({method: 'GET',url:'rest/web/Meteo7ggWs.php?azione=dettaglio&id='+idLocalita}).
		then(function successCallback(response){
			console.log("Meteo 7gg dettaglio scaricato correttamente");
			$scope.dettaglioMeteo7gg = response.data;
	
			bootbox.alert({
		        title: "Dettaglio meteo settimanale",
		        message: getHtmlPopup(),
		        size:'large'
		    });
	
		},
		function errorCallback(response){alert("Errore nello scarico del dettaglio meteo 7gg "+ id)});
	}
	
	
	function getHtmlPopup() {
		return "<table style='width:100%'>"+
					"<tr> " +
						"<td colspan='8' style='padding:8px;text-align:center;font-size:20px;font-weight:bold;'>" +
							$scope.dettaglioMeteo7gg[0].nome_comune+ " (" + $scope.dettaglioMeteo7gg[0].nome_regione + " "+ $scope.dettaglioMeteo7gg[0].nome_provincia +" )"+
						"</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>giorno</td>"+
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>" +$scope.dettaglioMeteo7gg[0].data+""+"</td>"+
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>" +$scope.dettaglioMeteo7gg[1].data+""+"</td>"+
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>" +$scope.dettaglioMeteo7gg[2].data+""+"</td>"+
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>" +$scope.dettaglioMeteo7gg[3].data+""+"</td>"+
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>" +$scope.dettaglioMeteo7gg[4].data+""+"</td>"+
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>" +$scope.dettaglioMeteo7gg[5].data+""+"</td>"+
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>" +$scope.dettaglioMeteo7gg[6].data+""+"</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Meteo</td>"+
						"<td style='padding:8px;'><img style='width:90px;' src='" +imgMeteo($scope.dettaglioMeteo7gg[0].icona)+"' title='"+ $scope.dettaglioMeteo7gg[0].fenomeno_previsto+"'/></td>"+
						"<td style='padding:8px;'><img style='width:90px;' src='" +imgMeteo($scope.dettaglioMeteo7gg[1].icona)+"' title='"+ $scope.dettaglioMeteo7gg[1].fenomeno_previsto+"'/></td>"+
						"<td style='padding:8px;'><img style='width:90px;' src='" +imgMeteo($scope.dettaglioMeteo7gg[2].icona)+"' title='"+ $scope.dettaglioMeteo7gg[2].fenomeno_previsto+"'/></td>"+
						"<td style='padding:8px;'><img style='width:90px;' src='" +imgMeteo($scope.dettaglioMeteo7gg[3].icona)+"' title='"+ $scope.dettaglioMeteo7gg[3].fenomeno_previsto+"'/></td>"+
						"<td style='padding:8px;'><img style='width:90px;' src='" +imgMeteo($scope.dettaglioMeteo7gg[4].icona)+"' title='"+ $scope.dettaglioMeteo7gg[4].fenomeno_previsto+"'/></td>"+
						"<td style='padding:8px;'><img style='width:90px;' src='" +imgMeteo($scope.dettaglioMeteo7gg[5].icona)+"' title='"+ $scope.dettaglioMeteo7gg[5].fenomeno_previsto+"'/></td>"+
						"<td style='padding:8px;'><img style='width:90px;' src='" +imgMeteo($scope.dettaglioMeteo7gg[6].icona)+"' title='"+ $scope.dettaglioMeteo7gg[6].fenomeno_previsto+"'/></td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Temperature</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>Max: " + $scope.dettaglioMeteo7gg[0].temp_max+" <br>Min: "+ $scope.dettaglioMeteo7gg[0].temp_minima+"</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>Max: " + $scope.dettaglioMeteo7gg[1].temp_max+" <br>Min: "+ $scope.dettaglioMeteo7gg[1].temp_minima+"</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>Max: " + $scope.dettaglioMeteo7gg[2].temp_max+" <br>Min: "+ $scope.dettaglioMeteo7gg[2].temp_minima+"</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>Max: " + $scope.dettaglioMeteo7gg[3].temp_max+" <br>Min: "+ $scope.dettaglioMeteo7gg[3].temp_minima+"</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>Max: " + $scope.dettaglioMeteo7gg[4].temp_max+" <br>Min: "+ $scope.dettaglioMeteo7gg[4].temp_minima+"</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>Max: " + $scope.dettaglioMeteo7gg[5].temp_max+" <br>Min: "+ $scope.dettaglioMeteo7gg[5].temp_minima+"</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>Max: " + $scope.dettaglioMeteo7gg[6].temp_max+" <br>Min: "+ $scope.dettaglioMeteo7gg[6].temp_minima+"</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Vento</td>"+
						imgVento($scope.dettaglioMeteo7gg[0].vento_direzione, $scope.dettaglioMeteo7gg[0].vento_intensita, $scope.dettaglioMeteo7gg[0].indice_vento_intensita)+
						imgVento($scope.dettaglioMeteo7gg[1].vento_direzione, $scope.dettaglioMeteo7gg[1].vento_intensita, $scope.dettaglioMeteo7gg[1].indice_vento_intensita)+
						imgVento($scope.dettaglioMeteo7gg[2].vento_direzione, $scope.dettaglioMeteo7gg[2].vento_intensita, $scope.dettaglioMeteo7gg[2].indice_vento_intensita)+
						imgVento($scope.dettaglioMeteo7gg[3].vento_direzione, $scope.dettaglioMeteo7gg[3].vento_intensita, $scope.dettaglioMeteo7gg[3].indice_vento_intensita)+
						imgVento($scope.dettaglioMeteo7gg[4].vento_direzione, $scope.dettaglioMeteo7gg[4].vento_intensita, $scope.dettaglioMeteo7gg[4].indice_vento_intensita)+
						imgVento($scope.dettaglioMeteo7gg[5].vento_direzione, $scope.dettaglioMeteo7gg[5].vento_intensita, $scope.dettaglioMeteo7gg[5].indice_vento_intensita)+
						imgVento($scope.dettaglioMeteo7gg[6].vento_direzione, $scope.dettaglioMeteo7gg[6].vento_intensita, $scope.dettaglioMeteo7gg[6].indice_vento_intensita)+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Precipitazioni</td>"+
						imgPioggia($scope.dettaglioMeteo7gg[0].indice_precipitazione, $scope.dettaglioMeteo7gg[0].precipitazione)+
						imgPioggia($scope.dettaglioMeteo7gg[1].indice_precipitazione, $scope.dettaglioMeteo7gg[1].precipitazione)+
						imgPioggia($scope.dettaglioMeteo7gg[2].indice_precipitazione, $scope.dettaglioMeteo7gg[2].precipitazione)+
						imgPioggia($scope.dettaglioMeteo7gg[3].indice_precipitazione, $scope.dettaglioMeteo7gg[3].precipitazione)+
						imgPioggia($scope.dettaglioMeteo7gg[4].indice_precipitazione, $scope.dettaglioMeteo7gg[4].precipitazione)+
						imgPioggia($scope.dettaglioMeteo7gg[5].indice_precipitazione, $scope.dettaglioMeteo7gg[5].precipitazione)+
						imgPioggia($scope.dettaglioMeteo7gg[6].indice_precipitazione, $scope.dettaglioMeteo7gg[6].precipitazione)+
					"</tr>"+
				"</table>"
				;
	}

	function imgPioggia(intensitaDescrizione, intensitaNumero){
		var vImmagine = 'images/venti/Pioggia.png';
		if (intensitaNumero !=0) 
			return '<td><div style="text-align:center;"><table style="margin:auto;"><tr>' +
		'<td title="Pioggia: ' + intensitaDescrizione +', '+ intensitaNumero + '" style="font-size:11pt; font-family: verdana;text-align:center;vertical-align:middle;width:50px;height:50px; background-image: url(\''+vImmagine+'\');background-size:50px 50px;">'+
		'<span style="padding:0;color:#000000;background-repeat:no-repeat;font-weight:bold;">'+ intensitaNumero +'&nbsp;</span>'+
		'</td>'+
		'</tr>'+
		'</table></div></td>';
		else
			return '<td><div style="text-align:center;"><table style="margin:auto;"><tr>' +
			'<td title="Pioggia: assente" style="font-size:11pt; font-family: verdana;text-align:center;vertical-align:middle;width:50px;height:50px; background-image: url(\''+vImmagine+'\');background-size:50px 50px;">'+
			'<span style="padding:0;color:#000000;background-repeat:no-repeat;font-weight:bold;">'+ 0 +'&nbsp;</span>'+
			'</td>'+
			'</tr>'+
			'</table></div></td>';
	}
	
	function imgVento(direzione, intensitaDescrizione, intensitaNumero){
		var vDirezione;
		var vImmagine = 'images/venti/sfondoTras/';
		if (direzione == 'E'){
			vDirezione = 'Est';
			vImmagine += '01Est.png';
		}
		else if (direzione == 'ESE'){
			vDirezione = 'EstSudEst';
			vImmagine += '02EstSudEst.png';
		}
		else if (direzione == 'SE'){
			vDirezione = 'SudEst';
			vImmagine += '03SudEst.png';
		}
		else if (direzione == 'SSE'){
			vDirezione = 'SudSudEst';
			vImmagine += '04SudSudEst.png';
		}
		else if (direzione == 'S'){
			vDirezione = 'Sud';
			vImmagine += '05Sud.png';
		}
		else if (direzione == 'SSW'){
			vDirezione = 'SudSudOvest';
			vImmagine += '06SudSudOvest.png';
		}
		else if (direzione == 'SW'){
			vDirezione = 'SudOvest';
			vImmagine += '07SudOvest.png';
		}
		else if (direzione == 'WSW'){
			vDirezione = 'OvestSudOvest';
			vImmagine += '08OvestSudOvest.png';
		}
		else if (direzione == 'W'){
			vDirezione = 'Ovest';
			vImmagine += '09Ovest.png';
		}
		else if (direzione == 'WNW'){
			vDirezione = 'OvestNordOvest';
			vImmagine += '10OvestNordOvest.png';
		}
		else if (direzione == 'NW'){
			vDirezione = 'NordOvest';
			vImmagine += '11NordOvest.png';
		}
		else if (direzione == 'NNW'){
			vDirezione = 'NordNordOvest';
			vImmagine += '12NordNordOvest.png';
		}
		else if (direzione == 'N'){
			vDirezione = 'Nord';
			vImmagine += '13Nord.png';
		}
		else if (direzione == 'NNE'){
			vDirezione = 'NordNordEst';
			vImmagine += '14NordNordEst.png';
		}
		else if (direzione == 'NE'){
			vDirezione = 'NordEst';
			vImmagine += '15NordEst.png';
		}
		else if (direzione == 'ENE'){
			vDirezione = 'EstNordEst';
			vImmagine += '16EstNordEst.png';
		}
		
		return '<td><div style="text-align:center;"><table style="margin:auto;"><tr>' +
		'<td title="Direzione: ' + vDirezione +'; intensità: '+ intensitaNumero +' - ' + intensitaDescrizione + '" style="font-size:11pt; font-family: verdana;text-align:center;vertical-align:middle;width:50px;height:50px; background-image: url(\''+vImmagine+'\');background-size:50px 50px;">'+
		'<span style="padding:0;color:#000000;background-repeat:no-repeat;font-weight:bold;">'+ intensitaNumero +'&nbsp;</span>'+
		'</td>'+
		'</tr>'+
		'</table></div></td>';


	}
	
	$scope.mostraFiltro=function(valore){
		$scope.filtro=valore;
	}
	
	$scope.applicaFiltro=function(valore){
		getMeteo7gg();
	}
	

	function getRegioni(){
		var urlRegione = 'rest/web/Meteo7ggWs.php?azione=regioni';

		$scope.promessa= $http({method: 'GET',url:urlRegione}).
		then(function successCallback(response){
			console.log("Regioni Meteo7gg scaricate correttamente");
			$scope.regioni = response.data;

			console.log("scaricati " + $scope.regioni.length + "record")
		},
		function errorCallback(response){alert("Errore nello scarico delle regioni Meteo7gg")});
	}
	

	function getProvince(){
		var url = 'rest/web/Meteo7ggWs.php?azione=province';

		$scope.promessa= $http({method: 'GET',url:url}).
		then(function successCallback(response){
			console.log("Province Meteo7gg scaricate correttamente");
			provinceAvailable = new Array();
			for (i=0; i < response.data.length;i++){
				provinceAvailable[i]=response.data[i].nome_provincia;
			}
			console.log("scaricati " + provinceAvailable.length + "record");
			$( "#provincia" ).autocomplete({source: provinceAvailable});

		},
		function errorCallback(response){alert("Errore nello scarico delle province Meteo7gg")});
	}

	function getComuni(){
		var url = 'rest/web/Meteo7ggWs.php?azione=comuni';

		$scope.promessa= $http({method: 'GET',url:url}).
		then(function successCallback(response){
			console.log("Comuni Meteo7gg scaricate correttamente");
			comuniAvailable = new Array();
			for (i=0; i < response.data.length;i++){
				comuniAvailable[i]=response.data[i].nome_comune;
			}
			console.log("scaricati " + comuniAvailable.length + "record");
			$( "#comune" ).autocomplete({source: comuniAvailable});

		},
		function errorCallback(response){alert("Errore nello scarico delle comuni Meteo7gg")});
	}

	
	
	$scope.getUrlIcona = function (nome){
		return imgMeteo(nome);
	}

	
	function imgMeteo(nome){
		if (nome=='rain.gif') return 'images/meteo/VClouds Weather Icons/9.png'
		else if (nome=='showers.gif') return 'images/meteo/VClouds Weather Icons/11.png'
		else if (nome=='showers_in.gif') return 'images/meteo/VClouds Weather Icons/11.png'
		else if (nome=='mcloudy.gif') return 'images/meteo/VClouds Weather Icons/28.png'
		else if (nome=='coperto.gif') return 'images/meteo/VClouds Weather Icons/28.png'
		else if (nome=='pcloudy.gif') return 'images/meteo/VClouds Weather Icons/30.png'
		else if (nome=='sunny.gif') return 'images/meteo/VClouds Weather Icons/32.png'
		else if (nome=='fair.gif') return 'images/meteo/VClouds Weather Icons/34.png'
		else if (nome=='mcloudyr_in.gif') return 'images/meteo/VClouds Weather Icons/37.png'
		else if (nome=='mcloudyr.gif') return 'images/meteo/VClouds Weather Icons/37.png'
		else if (nome=='chancetstorm_in.gif') return 'images/meteo/VClouds Weather Icons/39.png'
		else if (nome=='chancetstorm.gif') return 'images/meteo/VClouds Weather Icons/39.png'
		else if (nome=='smoke.gif') return 'images/meteo/VClouds Weather Icons/22.png'
		else if (nome=='moltocloudy.gif') return 'images/meteo/VClouds Weather Icons/26.png'
		else if (nome=='') return 'images/meteo/VClouds Weather Icons/44.png'
		else return 'images/meteo/VClouds Weather Icons/na.png'
	}

	//FUNZIONI NAVIGATORE
	$scope.puntiniIniziali = function (corrente,fine){
		if (fine > 11 && corrente >5) return true;
		return false;
	}
	
	$scope.puntiniFinali = function (corrente,fine){
		if (fine > 11 && corrente < fine-6) return true;
		return false;
	}
	
	riempiNavigatore = function (inizio,fine){
		ris=[];
		//se fine è <= di 11 si visualizzano tutte le pagine a partire da 2 fino ad alla fine ricordo che 1 c'è sempre
		if (fine <=11)
			for (i = 1; i <fine;i++) ris.push(i);
		else if (inizio <= 5) //per i primi 5 record si visualizzano sempre i primi 9 elementi
			for (i = 1; i <10;i++) ris.push(i);
		else if (inizio > 5 && inizio < fine -5) //per i record compresi tra [6,fine] si visualizza da pagina corrente -4 a pagina corrente + 5
			for (i = inizio-4; i <inizio+5;i++) ris.push(i);
		else if (inizio >= fine-5) //per gli ultimi 9 record si visualizzano gli ultimi 9 record
			for (i = fine-10; i <fine-1;i++) ris.push(i);
		return ris;
	}

	$scope.almenoPagine = function(numeroPagina,totalePagine){
		if (numeroPagina <= totalePagine) return true
		return false;
	}	
	
	$scope.vaiA = function (n){
		if (n < 0 || n>=$scope.meteo7gg.totalePagine) return;
		$scope.pagina = n;
		getMeteo7gg();
	}

	$scope.disableFirst = function () {
		if ($scope.pagina==0) return "disabled";
		return"";
	}
	
	$scope.disableLast = function () {
		if ($scope.pagina+1==$scope.meteo7gg.totalePagine) return "disabled";
		return "";
	}
	$scope.isPaginaAttiva = function (indice){
		if (indice == $scope.pagina) return "active";
		return "";
	}

	//FINE FUNZIONI NAVIGATORE
	
	
	init();
	
}]);
