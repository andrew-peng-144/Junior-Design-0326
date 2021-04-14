<?php
//include this file to print the topnav to html. (The topnav printed depends on whether the user is logged in as admin or not.)
//Files that include this file can also use the $admin boolean variable to determine if the current user is logged in as an admin or not.

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
//check if admin
$admin = false;
if (isset($_SESSION["administrator"]) && $_SESSION["administrator"] === true) {
  $admin = true;
}

  //directly output contents of topnav.html (or admin-topnav.html if admin)
  $topnav_html = "";
  $fh = fopen($admin ? "admin-topnav.html" : "topnav.html", 'r');
  while ($line = fgets($fh)) {
    $topnav_html .= $line;
  }
  fclose($fh);
  echo $topnav_html;
  //end topnav retrieval

?>