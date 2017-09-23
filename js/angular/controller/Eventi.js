//controller per la creazione degli item
angular.module("cmv").controller("EventiController", ['$scope', '$state', '$http', function ($scope, $state, $http) {

    $scope.campoOrdinato = 'tipo';
    $scope.direzione ='asc';
    $scope.pagina=0;
    $scope.dettaglio=false;
	
	function init() {
		console.log("eventi controller")
		getEventi();
	}
	
	function getEventi(){
		var urlEventi = 'rest/web/EventiWs.php?campoOrdinamento='+$scope.campoOrdinato+'&direzione='+$scope.direzione+'&pagina='+$scope.pagina;
//		if ($scope.tipoSelezionato != null)urlTraffico+='&tipo='+$scope.tipoSelezionato;
//		if ($scope.compartimentoSelezionato!=null)
//			for (i = 0; i < $scope.compartimentoSelezionato.length;i++)
//				urlTraffico+="&compartimento[]="+$scope.compartimentoSelezionato[i];
//		if ($scope.nomeStrada!=null)urlTraffico+='&strada='+$scope.nomeStrada;
//		if ($scope.operatoreVelocita != null && $scope.velocitaSelezionata!=null )urlTraffico+='&operatore='+$scope.operatoreVelocita+'&velocita='+$scope.velocitaSelezionata;

		$scope.promessa= $http({method: 'GET',url:urlEventi}).
		then(function successCallback(response){
			console.log("Eventi scaricati correttamente");
			$scope.eventi = response.data;

			console.log("scaricati " + $scope.eventi.obj.length + "record")
			$scope.navigatore = riempiNavigatore($scope.eventi.paginaCorrente,$scope.eventi.totalePagine);
		},
		function errorCallback(response){alert("Errore nello scarico degli eventi")});
	}

	
	function riempiNavigatore (corrente,fine){
		ris=[];
		//se fine è <= di 11 si visualizzano tutte le pagine a partire da 2 fino ad alla fine ricordo che 1 c'è sempre
		if (fine <=11)
			for (i = 1; i <fine;i++) ris.push(i);
		else if (corrente <= 5) //se la pagina corrente è tra le prime 5 si visualizzano sempre i primi 9 elementi del navigatore
			for (i = 1; i <10;i++) ris.push(i);
		else if (corrente > 5 && corrente < fine -5) //se la pagina corrente è compresa tra [6,fine-5] si visualizza da pagina corrente -4 a pagina corrente + 5
			for (i = corrente-4; i <corrente+5;i++) ris.push(i);
		else if (corrente >= fine-5) //se corrente è maggiore o uguale di fine-5 si visualizzano sempre gli ultimi 9 record
			for (i = fine-10; i <fine-1;i++) ris.push(i);
		return ris;
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
		getEventi();
	}


	
	$scope.almenoPagine = function(numeroPagina,totalePagine){
		if (numeroPagina <= totalePagine) return true
		return false;
	}	
	
	$scope.vaiA = function (n){
		if (n < 0 || n>=$scope.eventi.totalePagine) return;
		$scope.pagina = n;
		getEventi();
	}

	$scope.disableFirst = function () {
		if ($scope.pagina==0) return "disabled";
		return"";
	}
	
	$scope.disableLast = function () {
		if ($scope.pagina+1==$scope.eventi.totalePagine) return "disabled";
		return "";
	}
	
	$scope.isPaginaAttiva = function (indice){
		if (indice == $scope.pagina) return "active";
		return "";
	}
	
	$scope.iconaAsterisco = function(riga){
		return riga.flagApertura == 1;
	}

	$scope.iconaBandieraVerde = function(riga){
		return  riga.evento=='Evento' && riga.stato=='C';
	}

	$scope.iconaBandieraRossa = function(riga){
		return riga.evento=='Evento' && riga.stato!='C' && riga.flagModifica==-1;
	}

	$scope.iconaBandieraRossaLamp = function(riga){
		return riga.evento=='Evento' && riga.stato!='C' && riga.flagModifica==1;
	}

	$scope.iconaEsclamazioneGialla = function(riga){
		return  riga.evento=='Ordinanza' && riga.stato=='C';
	}

	$scope.iconaEsclamazioneRossa = function(riga){
		return riga.evento=='Ordinanza' && riga.stato!='C' && riga.flagModifica==-1;
	}

	$scope.iconaEsclamazioneGiallaLamp = function(riga){
		return riga.evento=='Ordinanza' && riga.stato!='C' && riga.flagModifica==1;
	}

	$scope.invioCCISS = function(riga){
		alert("Invio CCISS.")
	}

	$scope.modifica = function(riga){
		$state.transitionTo("eventiModifica",{id: riga.id_evento});
	}

	$scope.dettaglio = function(riga){
		$state.transitionTo("eventiDettaglio",{id: riga.id});
	}

    init();
	
}]);
