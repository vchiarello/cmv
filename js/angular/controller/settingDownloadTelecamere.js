//controller per la creazione degli item
angular.module("cmv").controller("settingDownloadTelecamere", ['$scope', '$http', function ($scope, $http) {

	
	function init() {
		console.log("Setting Download Telecamere controller")
	}
    init();
	
    function mngThread(azione){
    	
    	$scope.promessa= $http({method: 'GET',url:'rest/web/ThreadTelecamereWs.php?azione='+azione}).
		then(function successCallback(response){
			console.log("ricevuta risposta dal server");
			$scope.risultato = response.data;

		},
		function errorCallback(response){alert("Errore nella gestione del thread delle telecamere")});
//    	alert(azione);
    }
    $scope.reset = function(){
    	mngThread("reset");
    }
    $scope.pausa = function(){
    	mngThread("pausa");
    }
    $scope.start = function(){
    	mngThread("start");
    }
    $scope.info = function(){
    	mngThread("info");
    }
    $scope.restart = function(){
    	mngThread("restart");
    }
    $scope.stop = function(){
    	mngThread("stop");
    }
    $scope.play = function(){
    	mngThread("play");
    }


}]);
