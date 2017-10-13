//controller per la creazione degli item
angular.module("cmv").controller("manageCanale", ['$scope', '$http','$stateParams', function ($scope, $http, $stateParams) {

	
	function init() {
		console.log("Manage Canale...");
		if ($stateParams.azione=="New"){
			nuovo();
			getCompartimentiStrade(null);
		}else if ($stateParams.azione=="Edit"){
			get($stateParams.id, 'Edit');
			getCompartimentiStrade($stateParams.id);
		}else if ($stateParams.azione=="View"){
			get($stateParams.id, 'View');
			getCompartimentiStrade($stateParams.id);
		}


	}
    init();
	
    function getCompartimentiStrade(id){
    	var urlC = 'rest/web/CanaleWs.php?azione=Compartimento';
    	if (id != null) urlC += "&id="+id;
    	$scope.promessa= $http({method: 'GET',url:urlC}).
		then(function successCallback(response){
			console.log("ricevuta risposta dei compartimenti dal server");
			$scope.compartimenti = response.data;

		},
		function errorCallback(response){alert("Errore nel get dei compartimenti")});
    }
    
    
    
    function nuovo(){
    	
    	$scope.promessa= $http({method: 'GET',url:'rest/web/CanaleWs.php?azione=New'}).
		then(function successCallback(response){
			console.log("ricevuta risposta dal server");
			$scope.canale = response.data;
			$scope.canale.azione = 'New';
		},
		function errorCallback(response){alert("Errore nella gestione dei canali")});

    }

    function get(id, operazione){
    	
    	$scope.promessa= $http({method: 'GET',url:'rest/web/CanaleWs.php?azione=Get&id='+id}).
		then(function successCallback(response){
			console.log("ricevuta risposta dal server");
			$scope.canale = response.data[0];
			$scope.canale.azione = operazione;
		},
		function errorCallback(response){alert("Errore nella gestione dei canali")});

    }


	$scope.edit = function(){
		$scope.canale.azione = "Edit";
	}
    
	
	$scope.salva = function(){

		//$.param funzione jquery che serve per serializzare i parametri mandati via post
		var dati = $.param({
            id: $scope.canale.id_canale,
            descrizione: $scope.canale.descrizione_canale,
            token: $scope.canale.token,
			indirizzo: $scope.canale.indirizzo,
			azione: $scope.canale.azione,
			nodiSelezionati:getNodiSelezionati($scope.compartimenti[0])
        });
		
		$scope.promessa= $http({method: 'POST',
			url:'rest/web/CanaleWs.php', 
			data:dati,
			headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'} //necessario per mandare i parametri via post
		}).
		then(function successCallback(response){
			console.log("Canale correttamente salvato.");
			$scope.risultato = response.data;
			
		},
		function errorCallback(response){alert("Errore. Canale NON salvato.")});
	}

	$scope.nodiSelezionati = function(nodo){
		var nodiDaSalvare = "";
		if (nodo===undefined) return "";
		return getNodiSelezionati(nodo);
	}
	
	function getNodiSelezionati(nodo){
		var risultato = "";
		if (isFoglia(nodo) && nodo.selezionato) return ", "+nodo.codice;
		if (isFoglia(nodo) && !nodo.selezionato) return "";
		
		for (var i = 0; i < nodo.figli.length; i++)
			risultato += getNodiSelezionati(nodo.figli[i])
		return risultato;	
		
	}
	
	//FUNZIONI ALBERO
    $scope.toggle = function(n){
        if (!isFoglia(n))
            n.aperto=!n.aperto;
    }


    $scope.imgSelezioneNodo = function(n){
        if (statoSelezioneFigli(n)==1) return "./images/TuttoSelezione.png";
        if (statoSelezioneFigli(n)==0) return "./images/AlmenoUnoSelezione.png";
        return "./images/nessunaSelezione.png";
    }

    function isFoglia(n){
    	if(n.figli === undefined || n.figli == null || n.figli.length==0) return true
    	return false;
    }
    
    function statoSelezioneFigli(n){
        if ((n.figli === undefined || n.figli == null || n.figli.length==0) && n.selezionato)
            return 1;
        else if ((n.figli === undefined || n.figli == null || n.figli.length==0) && !n.selezionato)    
            return -1;

        var tuttiSelezionati = true;
        var almenoUnoSelezionato = false;
        
        for (var i = 0; i < n.figli.length;i++){
            sFigli = statoSelezioneFigli(n.figli[i]);

            if (tuttiSelezionati && sFigli!=1)tuttiSelezionati = false;
            if (sFigli!=-1)almenoUnoSelezionato = true;
        }

        if (tuttiSelezionati)return 1;
        if (!tuttiSelezionati && almenoUnoSelezionato) return 0;
        return -1

    }

    $scope.cambiaSelezioneNodo = function(n){
        if (n.figli === undefined || n.figli == null || n.figli.length==0 ){
            n.selezionato = ! n.selezionato;
            return;
        }    

        if (statoSelezioneFigli(n) == 1)
            mettiSelezioneNodo(n,0)
        else    
            mettiSelezioneNodo(n,1)
        
    }
    
    function mettiSelezioneNodo(n,valore){
        if ((n.figli === undefined || n.figli == null || n.figli.length==0) && valore ==0){
            n.selezionato = false;
            return;
        }
        else if ((n.figli === undefined || n.figli == null || n.figli.length==0) && valore ==1){
            n.selezionato = true; 
            return;
        }
               
       for (var i = 0; i < n.figli.length;i++)
            mettiSelezioneNodo(n.figli[i],valore);
    }

    $scope.imgNodo = function(n){
        //se è una foglia l'immagine è foglia
        if ((n.imgAlbero ===undefined) && (n.figli === undefined || n.figli == null || n.figli.length==0))
            n.imgAlbero = "./images/foglia.png";
        else if ((n.figli === undefined || n.figli == null || n.figli.length==0))
            n.imgAlbero = "./images/foglia.png";
        else if (n.imgAlbero ===undefined) // altrimenti c'è albero chiuso
            n.imgAlbero = "./images/chiuso.png";
        else if (n.aperto && n.figli.length>0) // se albero aperto icona aperto
            n.imgAlbero = "./images/aperto.png";    
        else if (!n.aperto && n.figli.length>0){ // se albero chiuso icona chiuso
            n.imgAlbero = "./images/chiuso.png";    
        }

        return n.imgAlbero;
    }
	//FINE FUNZIONI ALBERO

	

}]);
