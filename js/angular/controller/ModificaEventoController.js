//controller per la creazione degli item
angular.module("cmv").controller("ModificaEventoController", ['$scope', '$state', '$http', '$stateParams', function ($scope, $state, $http, $stateParams) {

	
	$scope.idEvento = $stateParams.id;

	function init() {
		console.log("DettaglioEventoController controller")
		caricaDettaglio($scope.idEvento);
	}

	
	//questo carica dettaglio è diverso dal quello del controller di dettaglio.
	//questo carica la singola riga di database accedendo per id_evento
	//quello carica tutti gli eventi associati ad un id (compreso anche delle evoluzioni multiple che ci possono essere)
	function caricaDettaglio(id){
		$scope.promessaDettaglio= $http({method: 'GET',url:'rest/web/EventiWs.php?azione=dettaglio&id_evento='+id}).
		then(function successCallback(response){
			console.log("Evento dettaglio scaricato correttamente");
			$scope.dettaglioEvento = response.data;
			if ($scope.dettaglioEvento.length>0){
				$scope.descrizione = $scope.dettaglioEvento[0].descrizione;
				$scope.note_cciss = $scope.dettaglioEvento[0].note_cciss;
			}
		},
		function errorCallback(response){alert("Errore nello scarico del dettaglio traffico "+ id)});
	}
	
	$scope.salva = function(){
		
	}
	
	$scope.annulla = function(){
		$state.transitionTo("eventi");
	}
	

    init();
    
    
}]);
