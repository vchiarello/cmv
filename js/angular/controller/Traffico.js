angular.module("cmv").controller("TrafficoController", ['$scope', '$http', function ($scope, $http) {

    $scope.campoOrdinato = 'tipo';
    $scope.direzione ='asc';
    $scope.pagina=0;
    $scope.dettaglio=false;

    
    //campi relativi ai filtri
    $scope.filtro=true;
    $scope.tipoSelezionato = null;
    $scope.compartimentoSelezionato = null;
    $scope.nomeStrada = null;
    $scope.operatoreVelocita=null;
    $scope.velocitaSelezionata=null;
    
	function init() {
		console.log("Traffico controller")
		getTraffico();
		getCompartimentiTraffico();
		getTipoTraffico();
		getStrade();
	}
    

	function getTraffico(){
		var urlTraffico = 'rest/web/TrafficoWs.php?campoOrdinamento='+$scope.campoOrdinato+'&direzione='+$scope.direzione+'&pagina='+$scope.pagina;
		if ($scope.tipoSelezionato != null && $scope.tipoSelezionato != '')urlTraffico+='&tipo='+$scope.tipoSelezionato;
		if ($scope.compartimentoSelezionato!=null)
			for (i = 0; i < $scope.compartimentoSelezionato.length;i++)
				urlTraffico+="&compartimento[]="+$scope.compartimentoSelezionato[i];
		if ($scope.nomeStrada!=null)urlTraffico+='&strada='+$scope.nomeStrada;
		if ($scope.operatoreVelocita != null && $scope.velocitaSelezionata!=null )urlTraffico+='&operatore='+$scope.operatoreVelocita+'&velocita='+$scope.velocitaSelezionata;

		$scope.promessa= $http({method: 'GET',url:urlTraffico}).
		then(function successCallback(response){
			console.log("Traffico scaricato correttamente");
			$scope.traffico = response.data;

			console.log("scaricati " + $scope.traffico.obj.length + "record")
			$scope.navigatore = riempiNavigatore($scope.traffico.paginaCorrente,$scope.traffico.totalePagine);
		},
		function errorCallback(response){alert("Errore nello scarico del traffico")});
	}

	function getCompartimentiTraffico(){
		$http({method: 'GET',url:'rest/web/TrafficoWs.php?azione=compartimenti'}).
		then(function successCallback(response){
			console.log("Compartimenti del traffico scaricati correttamente");
			$scope.compartimenti = response.data;

			console.log("scaricati " + $scope.compartimenti.length + "record")
		},
		function errorCallback(response){alert("Errore nello scarico dei compartimenti del traffico")});
	}
	
	function getTipoTraffico(){
		$http({method: 'GET',url:'rest/web/TrafficoWs.php?azione=tipo'}).
		then(function successCallback(response){
			console.log("Tipo del traffico scaricati correttamente");
			$scope.tipo = response.data;

			console.log("scaricati " + $scope.tipo.length + "record")
		},
		function errorCallback(response){alert("Errore nello scarico dei tipo del traffico")});
	}

	function getStrade(){
		var url = 'rest/web/TrafficoWs.php?azione=strade';

		$scope.promessa= $http({method: 'GET',url:url}).
		then(function successCallback(response){
			console.log("Strade Traffico scaricate correttamente");
			stradeAvailable = new Array();
			for (i=0; i < response.data.length;i++){
				stradeAvailable[i]=response.data[i].strada;
			}
			console.log("scaricati " + stradeAvailable.length + "record");
			$( "#strada" ).autocomplete({source: stradeAvailable});

		},
		function errorCallback(response){alert("Errore nello scarico delle Strade Telecamere")});
	}
	
	$scope.applicaFiltro=function(){
		getTraffico();
	}
	
	$scope.mostraFiltro=function(valore){
		$scope.filtro=valore;
	}
	
	function aggiornaStato(id, visibilita){
		//$.param funzione jquery che serve per serializzare i parametri mandati via post
		var dati = $.param({
            id: id,
            visibilita: visibilita
        });
		
		$scope.promessa= $http({method: 'POST',
			url:'rest/web/TrafficoWs.php', 
			data:dati,
			headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'} //necessario per mandare i parametri via post
		}).
		then(function successCallback(response){
			console.log("Stato Traffico correttamente aggiornato.");
			getTraffico();
			//se il dettaglio è visibile si aggiorna
			if ($scope.dettaglio)caricaDettaglio(id);
		},
		function errorCallback(response){alert("Errore. Stato Traffico NON aggiornato.")});
	}
	
	function caricaDettaglio(idCrescente, idDecrescente){
		$scope.promessaDettaglio= $http({method: 'GET',url:'rest/web/TrafficoWs.php?azione=dettaglio&idCrescente='+idCrescente+'&idDecrescente='+idDecrescente}).
		then(function successCallback(response){
			console.log("Traffico dettaglio scaricato correttamente");
			$scope.dettaglioTraffico = response.data;

			bootbox.confirm({
		        title: "Dettaglio traffico",
		        message: getHtmlPopup(),
		        buttons: {
		            cancel: {
		                label: '<i class="fa fa-times"></i> Chiudi'
		            },
		            confirm: {
		                label: '<i class="fa fa-times"></i> Chiudi'
		            }
		        },
		        callback: function (result) {
		        	//alert("funzione di callback");
		        }
		    });

		},
		function errorCallback(response){alert("Errore nello scarico del dettaglio traffico "+ id)});
	}
	
	$scope.mostra=function (id){
		aggiornaStato(id,1);
	}
	
	$scope.nascondi=function (id){
		aggiornaStato(id,0);
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
		getTraffico();
	}

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
		if (n < 0 || n>=$scope.traffico.totalePagine) return;
		$scope.pagina = n;
		getTraffico();
	}

	$scope.disableFirst = function () {
		if ($scope.pagina==0) return "disabled";
		return"";
	}
	
	$scope.disableLast = function () {
		if ($scope.pagina+1==$scope.traffico.totalePagine) return "disabled";
		return "";
	}
	
	$scope.isPaginaAttiva = function (indice){
		if (indice == $scope.pagina) return "active";
		return "";
	}
	
	$scope.mostraDettaglio=function(idCrescente,idDecrescente){
		caricaDettaglio(idCrescente,idDecrescente);
	}
	
	$scope.nascondiDettaglio=function(){
		$scope.dettaglioTraffico = null;
		$scope.dettaglio=false;
	}
	
	function getHtmlPopup() {
		if ($scope.dettaglioTraffico.length ==2)
			return "<table><tr>"
			+"<td style='padding:8px;'></td><td style='padding:8px;'><b> " + $scope.dettaglioTraffico[0].direzione+"</b></td><td style='padding:8px;'><b>" + $scope.dettaglioTraffico[1].direzione+"</b></td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>id_traffico:</b></td><td style='padding:8px;'> " + $scope.dettaglioTraffico[0].id_traffico+"</td><td style='padding:8px;'>" + $scope.dettaglioTraffico[1].id_traffico+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>velocita:</b></td><td style='padding:8px;'> " + $scope.dettaglioTraffico[0].velocita+"</td><td style='padding:8px;'>" + $scope.dettaglioTraffico[1].velocita+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>codice strada:</b></td><td style='padding:8px;'> " + $scope.dettaglioTraffico[0].cod_strada+"</td><td style='padding:8px;'>" + $scope.dettaglioTraffico[1].cod_strada+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>nome strada:</b></td><td style='padding:8px;'> " + $scope.dettaglioTraffico[0].nome_strada+"</td><td style='padding:8px;'>" + $scope.dettaglioTraffico[1].nome_strada+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>compartimento:</b></td><td style='padding:8px;'> " + $scope.dettaglioTraffico[0].compartimento+"</td><td style='padding:8px;'>" + $scope.dettaglioTraffico[1].compartimento+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>Localita inizio:</b></td><td style='padding:8px;'> " + $scope.dettaglioTraffico[0].localita_inizio+"</td><td style='padding:8px;'>" + $scope.dettaglioTraffico[1].localita_inizio+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>Localita fine:</b> </td><td style='padding:8px;'>" + $scope.dettaglioTraffico[0].localita_fine+"</td>"+"<td style='padding:8px;'> " + $scope.dettaglioTraffico[1].localita_fine+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>velocità:</b> </td><td style='padding:8px;'>" + $scope.dettaglioTraffico[0].velocita+"</td>"+"<td style='padding:8px;'>" + $scope.dettaglioTraffico[1].velocita+"</td>"
			+"</tr></table>"
					;
		else if ($scope.dettaglioTraffico.length ==1)
			return "<table><tr>"
			+"<td style='padding:8px;'></td><td style='padding:8px;'><b> " + $scope.dettaglioTraffico[0].direzione+"</b></td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>id_traffico:</b> </td><td style='padding:8px;'>" + $scope.dettaglioTraffico[0].id_traffico+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>codice strada:</b> </td><td style='padding:8px;'>" + $scope.dettaglioTraffico[0].cod_strada+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>nome strada:</b> </td><td style='padding:8px;'>" + $scope.dettaglioTraffico[0].nome_strada+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>compartimento:</b> </td><td style='padding:8px;'>" + $scope.dettaglioTraffico[0].compartimento+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>Localita inizio:</b> </td><td style='padding:8px;'>" + $scope.dettaglioTraffico[0].localita_inizio+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>Localita fine:</b> </td><td style='padding:8px;'>" + $scope.dettaglioTraffico[0].localita_fine+"</td>"
			+"</tr><tr>"
			+"<td style='padding:8px;'><b>velocità:</b> </td><td style='padding:8px;'>" + $scope.dettaglioTraffico[0].velocita+"</td>"
			+"</tr></table>"
					;
	}
	
	function labelConfirmPopup(){
		if ($scope.dettaglioTraffico[0].visibilita==0) return"<i class=\"fa fa-thumbs-up\"></i> Mostra";
		else return "<i class=\"fa fa-thumbs-down\"></i> Nascondi";
	}

	
    init();

	
}]);
