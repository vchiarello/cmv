//controller per la creazione degli item
angular.module("cmv").controller("MeteoGiornalieroController", ['$scope', '$http', function ($scope, $http) {

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
		console.log("meteo giornaliero controller")
		getMeteoGiornaliero();
		getRegioni();
		getProvince();
		getComuni();
	}

	$scope.changeComune = function(){
		//if ($scope.comune.length>2) 
			getMeteoGiornaliero();
	}
	
	$scope.changeProvincia = function(){
		//if ($scope.provincia.length>2) 
		getMeteoGiornaliero();
	}
	
	$scope.changeRegione = function(){
		getMeteoGiornaliero();
	}
	
	
    init();

	function getMeteoGiornaliero(){
		var urlMeteoGiornaliero = 'rest/web/MeteoGiornalieroWs.php?campoOrdinamento='+$scope.campoOrdinato+'&direzione='+$scope.direzione+'&pagina='+$scope.pagina;
		if ($scope.regioneSelezionata != null)
			for (i=0; i < $scope.regioneSelezionata.length;i++)
				if ($scope.regioneSelezionata[i].length>0)
					urlMeteoGiornaliero += '&regione[]='+$scope.regioneSelezionata[i];
		if ($scope.provincia != null)
			urlMeteoGiornaliero += '&provincia='+$scope.provincia;
		if ($scope.comune!=null)
			urlMeteoGiornaliero += '&comune='+$scope.comune;

		$scope.promessa= $http({method: 'GET',url:urlMeteoGiornaliero}).
		then(function successCallback(response){
			console.log("MeteoGiornaliero scaricato correttamente");
			$scope.meteoGiornaliero = response.data;

			console.log("scaricati " + $scope.meteoGiornaliero.obj.length + "record")
			$scope.navigatore = riempiNavigatore($scope.meteoGiornaliero.paginaCorrente,$scope.meteoGiornaliero.totalePagine);
		},
		function errorCallback(response){alert("Errore nello scarico del MeteoGiornaliero")});
	}
	
	$scope.mostraFiltro=function(valore){
		$scope.filtro=valore;
	}
	
	$scope.applicaFiltro=function(){
		getMeteoGiornaliero();
	}
	

	function getRegioni(){
		var urlRegione = 'rest/web/MeteoGiornalieroWs.php?azione=regione';

		$scope.promessa= $http({method: 'GET',url:urlRegione}).
		then(function successCallback(response){
			console.log("Regioni MeteoGiornaliero scaricate correttamente");
			$scope.regioni = response.data;

			console.log("scaricati " + $scope.regioni.length + "record")
		},
		function errorCallback(response){alert("Errore nello scarico delle regioni MeteoGiornaliero")});
	}

	function getProvince(){
		var url = 'rest/web/MeteoGiornalieroWs.php?azione=province';

		$scope.promessa= $http({method: 'GET',url:url}).
		then(function successCallback(response){
			console.log("Province MeteoGiornaliero scaricate correttamente");
			provinceAvailable = new Array();
			for (i=0; i < response.data.length;i++){
				provinceAvailable[i]=response.data[i].nome_provincia;
			}
			console.log("scaricati " + provinceAvailable.length + "record");
			$( "#provincia" ).autocomplete({source: provinceAvailable});

		},
		function errorCallback(response){alert("Errore nello scarico delle province MeteoGiornaliero")});
	}

	function getComuni(){
		var url = 'rest/web/MeteoGiornalieroWs.php?azione=comuni';

		$scope.promessa= $http({method: 'GET',url:url}).
		then(function successCallback(response){
			console.log("Comuni MeteoGiornaliero scaricate correttamente");
			comuniAvailable = new Array();
			for (i=0; i < response.data.length;i++){
				comuniAvailable[i]=response.data[i].nome_comune;
			}
			console.log("scaricati " + comuniAvailable.length + "record");
			$( "#comune" ).autocomplete({source: comuniAvailable});

		},
		function errorCallback(response){alert("Errore nello scarico delle comuni MeteoGiornaliero")});
	}

	$scope.frecce = function (numeroCampo) {
		if ($scope.campoOrdinato==numeroCampo && $scope.direzione=='asc') return "sorting_asc";
		else if ($scope.campoOrdinato==numeroCampo && $scope.direzione=='desc') return "sorting_desc";
		else return "sorting";
	}	
	
	$scope.cambiaOrdinamento = function (numeroCampo) {
		if($scope.campoOrdinato==numeroCampo && $scope.direzione=='asc') $scope.direzione='desc';
		else {
			$scope.campoOrdinato=numeroCampo;
			$scope.direzione='asc';
		}
		getMeteoGiornaliero();
	}

	
	$scope.mostraDettaglio = function(idLocalita){
		caricaDettaglio(idLocalita);
	}
	
	$scope.getIconaMeteo = function(icona){
		return imgMeteo(icona);
	}
	
	function caricaDettaglio(idMeteo){
		$scope.promessaDettaglio= $http({method: 'GET',url:'rest/web/MeteoGiornalieroWs.php?azione=dettaglio&id='+idMeteo}).
		then(function successCallback(response){
			console.log("Meteo giornaliero dettaglio scaricato correttamente");
			$scope.dettaglioMeteo = response.data;
	
			console.log("scaricati " + $scope.dettaglioMeteo.length + "record")
			bootbox.alert({
		        title: "Dettaglio meteo giornaliero",
		        message: getHtmlPopup()
		    });
	
		},
		function errorCallback(response){alert("Errore nello scarico del dettaglio meteo giornaliero "+ id)});
	}
	
	
	
	function getHtmlPopup() {
		return "<table style='width:70%;margin:auto;'>"+
					"<tr> " +
						"<td colspan='8' style='padding:8px;text-align:center;font-size:20px;font-weight:bold;'>" +
							$scope.dettaglioMeteo[0].nome_comune+" - Staz. "+ $scope.dettaglioMeteo[0].nome_stazione +" (" + $scope.dettaglioMeteo[0].nome_regione + " "+ $scope.dettaglioMeteo[0].nome_provincia +")" +
						"</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>giorno</td>"+
						"<td style='padding:8px;font-size:14px;text-align:center;font-weight:bold;'>" +$scope.dettaglioMeteo[0].aggiornamento+""+"</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Meteo</td>"+
						"<td style='padding:8px;text-align:center;'><img style='width:90px;' src='" +imgMeteo($scope.dettaglioMeteo[0].icona)+"' title='"+ $scope.dettaglioMeteo[0].nuvolosita+"'/></td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Alba</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>" + $scope.dettaglioMeteo[0].alba+"</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Tramonto</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>" + $scope.dettaglioMeteo[0].tramonto+"</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Temperature</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>" + $scope.dettaglioMeteo[0].temperatura+"°,<br> Max: " + $scope.dettaglioMeteo[0].max_temp_24h +"°,<br> Min: "+ $scope.dettaglioMeteo[0].min_temp_24h + "° <br>Percepita: " + $scope.dettaglioMeteo[0].temp_percepita + "° </td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Umidità</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>" + $scope.dettaglioMeteo[0].umidita_relativa+"%</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Vento</td>"+
						imgVento($scope.dettaglioMeteo[0].direzione_vento_card, '', $scope.dettaglioMeteo[0].velocita_vento)+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Visibilità</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>" + $scope.dettaglioMeteo[0].visibilita_testuale+"</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Indice disagio</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>" + $scope.dettaglioMeteo[0].indice_disagio+"%</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Altitudine</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>" + $scope.dettaglioMeteo[0].altitudine_comune+"</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>Latitudine</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>" + $scope.dettaglioMeteo[0].latitudine+"</td>"+
					"</tr>"+
					"<tr> " +
						"<td style='padding:8px;font-size:14px;font-weight:bold;'>longitudine</td>"+
						"<td style='padding:8px;text-align:center;font-weight:bold;'>" + $scope.dettaglioMeteo[0].longitudine+"</td>"+
					"</tr>"+
				"</table>"
				;
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
		if (n < 0 || n>=$scope.meteoGiornaliero.totalePagine) return;
		$scope.pagina = n;
		getMeteoGiornaliero();
	}

	$scope.disableFirst = function () {
		if ($scope.pagina==0) return "disabled";
		return"";
	}
	
	$scope.disableLast = function () {
		if ($scope.pagina+1==$scope.meteoGiornaliero.totalePagine) return "disabled";
		return "";
	}
	$scope.isPaginaAttiva = function (indice){
		if (indice == $scope.pagina) return "active";
		return "";
	}

	//FINE FUNZIONI NAVIGATORE
	
	
}]);
