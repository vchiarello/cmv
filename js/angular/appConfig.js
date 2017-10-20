angular.module("cmv").config(function ($stateProvider, $urlRouterProvider) {
	
//	$stateProvider.state("home", {
//		  template: "<h1>HELLO!</h1>"
//		})	
//	
    $urlRouterProvider.otherwise("blank");

    $stateProvider
	    .state('login', {
	        url:'/login',
	        templateUrl: "<h1>Login</h>",
	        controller: 'LoginCtrl'
	    }).state('blank', {
	        url:'/blank',
	        templateUrl: './html/blank.html',
	        controller: 'BlankController'
	    }).state('/', {
	        url:'/',
	        templateUrl: './html/Telecamere.html',
	        controller: 'TelecamereController'
	    }).state('telecamere', {
	        url:'/telecamere',
	        templateUrl: './html/Telecamere.html',
	        controller: 'TelecamereController'
	    }).state('eventi', {
	        url:'/eventi',
	        templateUrl: './html/Eventi.html',
	        controller: 'EventiController'
	    }).state('eventiDettaglio', {
	        url:'/eventiDettaglio/:id',
	        templateUrl: './html/DettaglioEvento.html',
	        controller: 'DettaglioEventoController'
	    }).state('eventiModifica', {
	        url:'/eventiModifica/:id',
	        templateUrl: './html/ModificaEvento.html',
	        controller: 'ModificaEventoController'
	    }).state('traffico', {
	        url:'/traffico',
	        templateUrl: './html/Traffico.html',
	        controller: 'TrafficoController'
	    }).state('meteoGiornaliero', {
	        url:'/meteoGiornaliero',
	        templateUrl: './html/MeteoGiornaliero.html',
	        controller: 'MeteoGiornalieroController'
	    }).state('meteoSettimanale', {
	        url:'/meteoSettimanale',
	        templateUrl: './html/MeteoSettimanale.html',
	        controller: 'MeteoSettimanaleController'
	    }).state('listaCanali', {
	        url:'/listaCanali',
	        templateUrl: './html/listaCanali.html',
	        controller: 'listaCanali'
	    }).state('manageCanale', {
	        url:'/manageCanale/:azione/:id',
	        templateUrl: './html/ManageCanale.html',
	        controller: 'manageCanale'
	    }).state('listaUtenti', {
	        url:'/listaUtenti',
	        templateUrl: './html/listaUtenti.html',
	        controller: 'listaUtenti'
	    }).state('manageUtente', {
	        url:'/manageUtente/:azione/:id',
	        templateUrl: './html/ManageUtente.html',
	        controller: 'manageUtente'
        });
});
