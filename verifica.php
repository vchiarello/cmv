<?php
session_start(); //inizio la sessione
unset($_SESSION['msg']);

//includo i file necessari a collegarmi al db con relativo script di accesso
 
include("./rest/database/ConnectionStatic.php");
use rest\database\ConnectionStatic;

error_log("Dir: " . __DIR__ . PHP_EOL);

$configFile = "rest/config.php";
$config = include $configFile;
$conn = new ConnectionStatic($config);
 
//variabili POST con anti sql Injection
if (isset($_POST['username'])) {
	$username=$_POST['username']; //faccio l'escape dei caratteri dannosi
}

if (isset($_POST['password'])) {
	$password=sha1($_POST['password']); //sha1 cifra la password anche qui in questo modo corrisponde con quella del db
}

$query = "";
$select = "SELECT * FROM login ";
$orderby = "ORDER BY 1 ASC";

if (isset($username)) {
	$query = $select . "where username = :username and password = :password " . $orderby;

	$stmt = $conn->pdo->prepare($query, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);

	if (!$stmt) {
		error_log("Errore nell'esecuzione della query, motivo: ". print_r($conn->pdo->errorInfo(),true) );
	} else {
		$stmt->execute(['username' => $username, 'password' => $password]);
	}
} 

$row = $stmt->fetch(PDO::FETCH_OBJ);

if ($row) {
	/*Prelevo l'identificativo dell'utente */
	$cod=$row->username;
}

/* Username e password corrette */
if (isset($cod)) {
	error_log("Avvio sessione: " . $cod . ' - ' . session_id());
	/*Registro la sessione*/
	$_SESSION["autorizzato"] = 1;

	/*Registro il codice dell'utente*/
	$_SESSION['userid'] = $cod;

	/*Redirect alla pagina riservata*/
	echo '<script language=javascript>document.location.href="index.php"</script>'; 
} else {
	/*Username e password errati, redirect alla pagina di login*/
	$_SESSION["msg"] = "Utenza o password errati, accesso negato.";
	unset($_SESSION["autorizzato"]);
	unset($_SESSION['userid']);

	echo '<script language=javascript>document.location.href="login.php"</script>';
}
?>