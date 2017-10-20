//controller per la creazione degli item
angular.module("cmv").controller("listaUtenti", ['$scope', '$http', function ($scope, $http) {

	
	function init() {
		console.log("Getting Utenti");
		getUtenti();
	}
	
    $scope.delete = function(id){
		bootbox.confirm({
	        title: "Conferma",
	        message: "Il record verrà cancellato, vuoi continuare?",
	        buttons: {
	            cancel: {
	                label: '<i class="fa fa-times"></i> Chiudi'
	            },
	            confirm: {
	                label: '<i class="fa fa-times"></i> Sì'
	            }
	        },
	        callback: function (result) {//result vale true se è stato premuto l'ok nel caso il mostra/nascondi
	        	if (result)
	        		cancella(id);
	        }
	    });

    }
    
    function cancella(id){
    	$scope.promessa= $http({method: 'GET',url:'rest/web/UtenteWs.php?azione=Delete&id='+id}).
		then(function successCallback(response){
			console.log("Record cancellato");
			$scope.risultato = response.data;
			bootbox.alert({
		        title: "Notifica",
		        message: $scope.risultato.messaggio
		    });
			getCanali();
		},
		function errorCallback(response){alert("Errore nella gestione dei canali")});
    }

    function getUtenti(){
    	var urlCanali = 'rest/web/UtenteWs.php?azione=Utenti&campoOrdinamento='+$scope.campoOrdinato+'&direzione='+$scope.direzione+'&pagina='+$scope.pagina
    	if ($scope.daCercare != null && $scope.daCercare.trim()!="")
    		urlCanali += "&filtro="+$scope.daCercare;
    	$scope.promessa= $http({method: 'GET',url:urlCanali}).
		then(function successCallback(response){
			console.log("ricevuta risposta dal server");
			$scope.utenti = response.data;

		},
		function errorCallback(response){alert("Errore nella gestione degli utenti")});
    }
    
    
    //funzioni e variabili ordinamento della tabella
    $scope.campoOrdinato = 'username';
    $scope.direzione ='asc';
    $scope.pagina=0;

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
		getUtenti();
	}
    //funzioni ordinamento della tabella

    $scope.filtro=true;

    $scope.mostraFiltro=function(valore){
		$scope.filtro=valore;
	}
	$scope.applicaFiltro=function(){
		getUtenti();
	}
	
    init();


}]);
