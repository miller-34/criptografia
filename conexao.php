<?php 

$host = "localhost";
$user = "root";
$pass = "";
$bd = "senhas";

$mysqli = new mysqli($host, $user, $pass, $bd);


// check connection

if ($mysqli->connect_errno) {
    echo "connect failed: " . $mysqli->connect_error;
    exit();
}

?>