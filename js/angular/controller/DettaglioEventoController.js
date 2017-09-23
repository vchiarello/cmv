//controller per la creazione degli item
angular.module("cmv").controller("DettaglioEventoController", ['$scope', '$state','$http', '$stateParams', function ($scope, $state, $http, $stateParams) {

	$scope.idEvento = $stateParams.id;
	
	function init() {
		console.log("DettaglioEventoController controller")
		caricaDettaglio($scope.idEvento);
	}
	
	//questo carica dettaglio è diverso dal quello del controller di dettaglio.
	//questo carica tutti gli eventi associati ad un id (compreso anche delle evoluzioni multiple che ci possono essere)
	//quello carica la singola riga di database accedendo per id_evento
	function caricaDettaglio(id){
		$scope.promessaDettaglio= $http({method: 'GET',url:'rest/web/EventiWs.php?azione=dettaglio&id='+id}).
		then(function successCallback(response){
			console.log("Evento dettaglio scaricato correttamente");
			$scope.dettaglioEvento = response.data;
		},
		function errorCallback(response){alert("Errore nello scarico del dettaglio traffico "+ id)});
	}
	

    init();
	
}]);
