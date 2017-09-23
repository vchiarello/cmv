<?php 
session_start();
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
}?>
<!DOCTYPE html>
<html>
<head> 
    <title>Pagina di Login CMV</title>
    <link href="css/login.css" rel="stylesheet" type="text/css" /> 
</head>
<body>
	<h1>
<?php
	if (isset($_SESSION["msg"])) {
		echo $_SESSION["msg"];
	}
?>
	</h1>
    <form id="login" action="verifica.php" method="post">
        <fieldset id="inputs">
            <input id="username" name="username" type="text" placeholder="Username" autofocus required>
            <input id="password" name="password" type="password" placeholder="Password" required>
        </fieldset>
        <fieldset id="actions">
            <input type="submit" id="submit" value="Collegati">
        </fieldset>
    </form>
 
</body>
</html>