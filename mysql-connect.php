<?php
//Connect to the MySQL database as a view-only user. can use the $conn variable.


error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//*********************[EDIT BELOW]******************************//
$servername = "localhost";
$username = "visitor";
$password = "password123";
$dbname = "cga_showcase";
//*********************[EDIT ABOVE]******************************//

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>