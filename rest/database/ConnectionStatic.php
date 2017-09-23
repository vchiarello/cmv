<?php
namespace rest\database;

use Exception;
use PDO;

class ConnectionStatic
{
	const ERROR_UNABLE = 'ERRORE: connessione non disponibile';
	public $pdo;

	public function __construct(array $config)
	{
		if (!isset($config['driver'])) {
			$msg = __METHOD__ . ' : ' . self::ERROR_UNABLE . PHP_EOL;
			throw new Exception($msg);
		}
		$dsn = $this->makeDsn($config);
		// error_log("DSN: " . $dsn);
		try {
			$this->pdo = new PDO(
					$dsn, $config['user'], $config['pwd'],
					[PDO::ATTR_ERRMODE => $config['errmode']]);
			return TRUE;
		} catch (PDOException $e) {
			error_log($e->getMessage());
			return FALSE;
		}
	}
	
	
	public static function factory()
	{
		$config= 'mySetting.ini';
		if (!$settings = parse_ini_file($config, TRUE)) throw new \exception('Impossibile aprire il file: <' . $config . '>');
		
		$dns = $settings['database']['driver'] .
		':host=' . $settings['database']['host'] .
		((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '') .
		';dbname=' . $settings['database']['schema'];
		
		if (!isset(ConnectionStatic::$pdo)){
			ConnectionStatic::$pdo= new \PDO($dns,
					$settings['database']['username'],
					$settings['database']['password'],
					array(\PDO::ATTR_PERSISTENT => true));
		}
		return ConnectionStatic::$pdo;
	}
	
	
	public function makeDsn($config)
	{
		$dsn = $config['driver'] . ':';
		unset($config['driver']);
		foreach ($config as $key => $value) {
			$dsn .= $key . '=' . $value . ';';
		}
		return substr($dsn, 0, -1);
	}
	
}
?>