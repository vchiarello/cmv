<?php
include("rest/database/ConnectionStatic.php");
include("rest/bean/Telecamere.php");


use rest\bean\ConnectionStatic;


try
{
  $dbh = ConnectionStatic::factory();
  
  $stmt = $dbh->prepare('SELECT * from Telecamere');
  
  $stmt -> execute();
  
  
  while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
  	error_log("Riga: " . print_r($row, TRUE));
  	$out[] = $row;
  }
  $dbh = null;
  echo json_encode($out);
}
catch (Exception $e)
{
  echo "Unable to connect: " . $e->getMessage() ."<p>";
}