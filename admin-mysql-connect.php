<?php
//Connect to the mysql database as an 'admin' user (can INSERT, DELETE, SELECT, and UPDATE the students, projects, project files tables)
//note that 'admin' user is different from the 'root' user (can do everything)

//only admins allowed:
if(!isset($_SESSION)) 
{ 
    session_start(); 
}
if (!isset($_SESSION["administrator"]) || $_SESSION["administrator"] !== true) {
    header("location: ./admin-login.php");
    exit;
}

//**********NOTE: Environment variables below are hardcoded **********//
$servername = "localhost";
$username = "cga-admin";
$password = "cgatestpassword678";
$dbname = "cga_showcase";
//************************************ */


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

unset($servername);
unset($username);
unset($password);
unset($dbname);
?>