<?php
//FOR CGA SHOCASE:
//username: admintest
//password: passwordtesting123 (hashed to $2y$10$39YNjfOE.GG5rSuuE4aVJ.lVpg/tUZESbTlwIqbZUX4KxESKrRM.C)

//root access
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cga_showcase";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?,?)");
$stmt->bind_param("ss", $u, $p);
$u = "admintest";
$p = "$2y$10$39YNjfOE.GG5rSuuE4aVJ.lVpg/tUZESbTlwIqbZUX4KxESKrRM.C";
$stmt->execute();
$stmt->close();

?>