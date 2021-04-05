<?php

error_reporting(E_ALL);


//only admins allowed: (very important or else anyone can connect to root!)
if(!isset($_SESSION)) 
{ 
    session_start(); 
}
if (!isset($_SESSION["administrator"]) || $_SESSION["administrator"] !== true) {
    header("location: ./admin-login.php");
    exit;
}

//mysql root access
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cga_showcase";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>