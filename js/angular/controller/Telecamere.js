//controller per la creazione degli item
angular.module("cmv").controller("TelecamereController", ['$scope', '$http', function ($scope, $http) {

    $scope.campoOrdinato = 1;
    $scope.direzione ='asc';
    $scope.pagina=0;
    $scope.linkFotoDettaglio="";

    //campi relativi ai filtri
    $scope.filtro=true;
    $scope.visibilita = null;
    $scope.regioneSelezionata = null;
    $scope.nomeStrada = null;
    $scope.disponibilita = null;
    
	function init() {
		console.log("Telecamere controller");
		getTelecamere();
		getRegioniTelecamere();
		getStrade();
	}
    

	function getTelecamere(){
		urlTelecamere = 'rest/web/TelecamereWs.php?campoOrdinamento='+$scope.campoOrdinato+'&direzione='+$scope.direzione+'&pagina='+$scope.pagina;
		if ($scope.visibilita != null && $scope.visibilita != '')urlTelecamere+="&visibilita="+$scope.visibilita;
		if ($scope.disponibilita != null && $scope.disponibilita != '')urlTelecamere+="&disponibilita="+$scope.disponibilita;
		if ($scope.nomeStrada != null)urlTelecamere+="&nomeStrada="+$scope.nomeStrada;
		if ($scope.regioneSelezionata!=null)
			for (i = 0; i < $scope.regioneSelezionata.length;i++)
				urlTelecamere+="&regioneSelezionata[]="+$scope.regioneSelezionata[i];
		
		$scope.promessa= $http({method: 'GET',url:urlTelecamere}).
		then(function successCallback(response){
			console.log("Telecamere scaricate correttamente");
			$scope.telecamere = response.data;

			$scope.navigatore = riempiNavigatore($scope.telecamere.paginaCorrente,$scope.telecamere.totalePagine);
			console.log("scaricati " + $scope.telecamere.obj.length + "record")
		},
		function errorCallback(response){alert("Errore nello scarico delle telecamere")});
	}
	
	function getRegioniTelecamere(){
		$http({method: 'GET',url:'rest/web/TelecamereWs.php?azione=regioni'}).
		then(function successCallback(response){
			console.log("Regioni delle telecamere scaricate correttamente");
			$scope.regioni = response.data;

			console.log("scaricati " + $scope.regioni.length + "record")
		},
		function errorCallback(response){alert("Errore nello scarico delle regioni delle telecamere")});
	}
	
	function getStrade(){
		var url = 'rest/web/TelecamereWs.php?azione=strade';

		$scope.promessa= $http({method: 'GET',url:url}).
		then(function successCallback(response){
			console.log("Strade Telecamere scaricate correttamente");
			stradeAvailable = new Array();
			for (i=0; i < response.data.length;i++){
				stradeAvailable[i]=response.data[i].strada;
			}
			console.log("scaricati " + stradeAvailable.length + "record");
			$( "#strada" ).autocomplete({source: stradeAvailable});

		},
		function errorCallback(response){alert("Errore nello scarico delle Strade Telecamere")});
	}
	
	
	
	function aggiornaStato(id, visibilita){
		//$.param funzione jquery che serve per serializzare i parametri mandati via post
		var dati = $.param({
            id: id,
            visibilita: visibilita
        });
		
		$scope.promessa= $http({method: 'POST',
			url:'rest/web/TelecamereWs.php', 
			data:dati,
			headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'} //necessario per mandare i parametri via post
		}).
		then(function successCallback(response){
			console.log("Stato Telecamera correttamente aggiornato.");
			getTelecamere();
			//se il dettaglio è visibile si aggiorna
			if ($scope.dettaglio)caricaDettaglio(id);
		},
		function errorCallback(response){alert("Errore. Stato Telecamera NON aggiornato.")});
	}
	
	function caricaDettaglio(id){
		$scope.linkFotoDettaglio="rest/web/DownloadImageTelecamereWs.php?id="+id;
		$scope.promessaDettaglio= $http({method: 'GET',url:'rest/web/TelecamereWs.php?azione=dettaglio&id='+id}).
		then(function successCallback(response){
			console.log("Telecamere dettaglio scaricato correttamente");
			$scope.dettaglioTelecamera = response.data;

			bootbox.confirm({
		        title: "Dettaglio telecamera",
		        message: getHtmlPopup(),
		        buttons: {
		            cancel: {
		                label: '<i class="fa fa-times"></i> Chiudi'
		            },
		            confirm: {
		                label: labelConfirmPopup()
		            }
		        },
		        callback: function (result) {//result vale true se è stato premuto l'ok nel caso il mostra/nascondi
		        	if (result && $scope.dettaglioTelecamera[0].visibilita==0)
		            	aggiornaStato($scope.dettaglioTelecamera[0].id_cam,1);
		        	if (result && $scope.dettaglioTelecamera[0].visibilita==1)
		            	aggiornaStato($scope.dettaglioTelecamera[0].id_cam,0);
		        }
		    });

		},
		function errorCallback(response){alert("Errore nello scarico del dettaglio telecamera "+ id)});
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
		getTelecamere();
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
	
	$scope.range = function (inizio,fine){
		ris =[];
		for (i = inizio; i <fine;i++) ris.push(i);
		return ris;
	}
	
	$scope.vaiA = function (n){
		if (n < 0 || n>=$scope.telecamere.totalePagine) return;
		$scope.pagina = n;
		getTelecamere();
	}

	$scope.disableFirst = function () {
		if ($scope.pagina==0) return "disabled";
		return"";
	}
	
	$scope.disableLast = function () {
		if ($scope.pagina+1==$scope.telecamere.totalePagine) return "disabled";
		return"";
	}
	
	$scope.isPaginaAttiva = function (indice){
		if (indice == $scope.pagina) return "active";
		return "";
	}
	//FUNZIONI NAVIGATORE
	
	$scope.mostraDettaglio=function(id){
		caricaDettaglio(id);
	}
	
	$scope.nascondiDettaglio=function(){
		$scope.dettaglioTelecamera = null;
		$scope.linkFotoDettaglio="";
		$scope.dettaglio=false;
	}
	
	$scope.mostraFiltro=function(interruttore){
		$scope.filtro=interruttore;
	}

	$scope.applicaFiltro=function(){
		getTelecamere();
	}
	
	function getHtmlPopup() {
		return "<table><tr><td rowspan='7' style='padding:8px;'>" 
		+"<img src='" +$scope.linkFotoDettaglio +"'/>"
		+"</td></tr><tr><td style='padding:8px;'>"
		+"<b>id_cam:</b> " + $scope.dettaglioTelecamera[0].id_cam+""
		+"</td></tr><tr><td style='padding:8px;'>"
		+"<b>direzione:</b> " + $scope.dettaglioTelecamera[0].direzione+""
		+"</td></tr><tr><td style='padding:8px;'>"
		+"<b>strada:</b> " + $scope.dettaglioTelecamera[0].strada+""
		+"</td></tr><tr><td style='padding:8px;'>"
		+"<b>descrizione:</b> " + $scope.dettaglioTelecamera[0].descrizione+""
		+"</td></tr><tr><td style='padding:8px;'>"
		+"<b>km:</b> " + $scope.dettaglioTelecamera[0].km+""
		+"</td></tr><tr><td style='padding:8px;'>"
		+"<b>visibilità:</b> " + (($scope.dettaglioTelecamera[0].visibilita==0)?'No':'Si')+""
		+"</td></tr></table>"
				;
	}
	
	function labelConfirmPopup(){
		if ($scope.dettaglioTelecamera[0].visibilita==0) return"<i class=\"fa fa-thumbs-up\"></i> Mostra";
		else return "<i class=\"fa fa-thumbs-down\"></i> Nascondi";
	}

	
    init();
	
}]);
