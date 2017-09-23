<?php
namespace rest\thread;

class TelecamereThread extends Thread {
	
	static $wt;
	
	public static function getInstance(){
		if (!isset(TelecamereThread::$wt)) TelecamereThread::$wt = new TelecamereThread();
		return TelecamereThread::$wt;
	}
	
	public static function resetInstance(){
		error_log("TelecamereThread, sto resettando...");
		if (isset(TelecamereThread::$wt)) {
			TelecamereThread::$wt->synchronized(
					function($thread){
						TelecamereThread::$wt -> eseguiEsterno=false;
						TelecamereThread::$wt -> eseguiInterno=false;
						TelecamereThread::$wt = NULL;
					}, TelecamereThread::$wt);
			
			error_log("TelecamereThread, fine reset.");
		}
		
	}
	
	
	private function __construct(){
		$this->eseguiInterno=true;
		$this->eseguiEsterno=true;
		$this->h=0;
	}
	
	public function run(){
		while (TelecamereThread::$wt->eseguiEsterno){
			error_log("TelecamereThread, sono attivo.");
			while (TelecamereThread::$wt->eseguiInterno){
				echo "Eseguito " .TelecamereThread::$wt->h++." volte "."\r\n";
				//questo sleep va calcolato con la select sul db.
				sleep(2);
			}
			TelecamereThread::$wt->synchronized(
					function($thread){
						if (TelecamereThread::$wt->eseguiEsterno){
							error_log("TelecamereThread, mi sto fermando...");
							$thread->wait();
						}
					}, TelecamereThread::$wt);
			
		}
	}
	
	public static function pausa(){
		if (TelecamereThread::getInstance()->isRunning()){
			$t=TelecamereThread::getInstance();
			$risultato=true;
			$t->synchronized(
					function($thread,$r){
						if ($thread->eseguiInterno==true){
							$thread->eseguiInterno= false;
						}else $r=false;
					},
					TelecamereThread::getInstance(),$risultato);
			return $risultato;
		}
		return false;
	}
	
	public static function stop(){
		if (TelecamereThread::getInstance()->isRunning()){
			$t=TelecamereThread::getInstance();
			$risultato=true;
			$t->synchronized(
					function($thread,$r){
						$thread->eseguiInterno=false;
						$thread->eseguiEsterno= false;
						$thread->notify();
					},
					TelecamereThread::getInstance(),$risultato);
			return $risultato;
		}
		return false;
	}
	
	public static function restart(){
		if (TelecamereThread::getInstance()->isRunning()){
			$risultato=true;
			TelecamereThread::getInstance()->synchronized(
				function($thread,$r){
					if ($thread->eseguiInterno==false){
						$thread->eseguiInterno= true;
					}else $r=false;
				}, 
				TelecamereThread::getInstance(),$risultato);
			return $risultato;
		}
		return false;
	}
	
	public static function info(){
		$risultato=0;
		TelecamereThread::getInstance()->synchronized(
			function($thread,$r){
				//è fermo
				if ($thread->eseguiEsterno==false)	$r=0;
				//è in pausa
				elseif ($thread->eseguiEsterno==true && $thread->eseguiInterno==false) $r=1;
				//sta girando
				else $r=2;
			},
			TelecamereThread::getInstance(),$risultato);
	}
	
}
