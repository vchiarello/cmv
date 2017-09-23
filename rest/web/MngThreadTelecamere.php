<?php
namespace rest\web;

include("../thread/TelecamereThread.php");
use rest\thread\TelecamereThread;


class MngThreadTelecamereTelecamere{
	public static function getAzione(array $parametri){
		if (isset($parametri['azione'])) return $parametri['azione'];
	}
	
	
	public static function start(){
		if (TelecamereThread::getInstance()->isRunning())return "Il thread era già in esecuzione.";
		else {
			TelecamereThread::getInstance()->start();
			return "Thread partito.";
		}
	}
	public static function pausa(){
		if (TelecamereThread::getInstance()->isRunning()){
			if (TelecamereThread::pausa()) return "Il thread è stato messo in pausa";
		}
		return "Il thread non sta girando";
	}
	public static function stop(){
		if (TelecamereThread::getInstance()->isRunning()){
			if (TelecamereThread::stop()) return "Il thread è stato fermato";
		}
		return "Il thread non sta girando";
	}
	public static function restart(){
		if (!TelecamereThread::getInstance()->isRunning()){
			if (TelecamereThread::restart()) return "Il thread è ripartito";
		}
		return "Il thread non è inizializzato.";
	}
	
	public static function reset(){
		if (TelecamereThread::getInstance()->isRunning()){
			TelecamereThread::resetInstance;
			return "Il thread è stato resettato.";
		}
		return "Il thread non è inizializzato.";
	}
	public static function info(){
		if (TelecamereThread::getInstance()->isRunning()){
			if (TelecamereThread::info()==0)
				return "Il thread è fermo.";
				elseif (TelecamereThread::info()==1) return "Il thread è in pausa.";
				elseif (TelecamereThread::info()==2) return "Il thread sta girando.";
		}
		return "Il thread non è inizializzato.";
	}
}