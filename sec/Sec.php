<?php
namespace sec;
include_once ($_SERVER['DOCUMENT_ROOT'] . "/cmv/rest/database/ConnectionStatic.php");
use rest\database\ConnectionStatic;
use Exception;
use PDO;

class Sec
{
	public $utente;

	public function __construct()
	{
		//se non c'è la sessione registrata
		if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 600)) {
			// last request was more than 30 minutes ago
			session_unset();     // unset $_SESSION variable for the run-time 
			session_destroy();   // destroy session data in storage
		}
		$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
		if (!isset($_SESSION['CREATED'])) {
			$_SESSION['CREATED'] = time();
		} else if (time() - $_SESSION['CREATED'] > 1800) {
			// session started more than 30 minutes ago
			session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
			$_SESSION['CREATED'] = time();  // update creation time
		}
		if (!isset($_SESSION['userid']) or !isset($_SESSION['autorizzato'])) {
			error_log(json_encode(['sid' => '', 'uid' => '', 'msg' => 'Sessione non valida', 'file' => basename(__FILE__) . '|' . __METHOD__, 'in' => basename($_SERVER['PHP_SELF'])]));
			unset($_SESSION['msg']);
			echo "<h1>Area riservata, accesso negato.</h1>";
			echo "Per effettuare il login clicca <a href='login.php'><font color='blue'>qui</font></a>";
			die;
		}

		//Altrimenti Prelevo il codice identificatico dell'utente loggato
		$utente = $_SESSION['userid']; //id cod recuperato nel file di verifica
		error_log(json_encode(['sid' => session_id(), 'uid' => $utente, 'msg' => 'Sessione OK', 'file' => basename(__FILE__) . '|' . __METHOD__, 'in' => basename($_SERVER['PHP_SELF'])]));
	}

	public function menu($utente, $funzione)
	{
		error_log(json_encode(['sid' => session_id(), 'uid' => $utente, 'msg' => 'Metodo', 'file' => basename(__FILE__) . '|' . __METHOD__, 'in' => basename($_SERVER['PHP_SELF'])]));

		$result = array();
		$configFile = $_SERVER['DOCUMENT_ROOT'] . "/cmv/rest/config.php";
		$config = include $configFile;
		$conn = new ConnectionStatic($config);

		$query = "";
		$select = "SELECT f.pos, uf.id_funzione, f.dd_funzione, f.tag_html, uf.fl_scrittura FROM utenti_funzioni uf join funzioni f on f.id_funzione = uf.id_funzione where uf.username = :username and f.id_funzione_padre = :funzione ";
		$orderby = "ORDER BY 1 ASC";
		$query = $select . $orderby;

		$stmt = $conn->pdo->prepare($query, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);

		if (!$stmt) {
			error_log(json_encode(['sid' => session_id(), 'uid' => $utente, 'msg' => "Errore nell'esecuzione della query:" . $query . ": motivo: ". print_r($conn->pdo->errorInfo(),true), 'file' => basename(__FILE__) . '|' . __METHOD__, 'in' => basename($_SERVER['PHP_SELF'])]));
		} else {
			$stmt->execute(['username' => $utente, 'funzione' => $funzione]);
		}

		$out = array();
		while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
			$out[$row->id_funzione] = ['dd' => $row->dd_funzione, 'tag' => $row->tag_html];
		}
		// error_log("Risultato: n.righe=" . sizeof($out) . ":::" . print_r($out, TRUE));

		return $out;
	}
}
?>