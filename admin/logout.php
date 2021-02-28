<?php
session_start();
$_SESSION = array(); //clear all session variables
session_destroy();
header("location: ../index.php");
exit;
?>