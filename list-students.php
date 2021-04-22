<?php
//This page lists out all students in the database. It is in the same visual format as the Search results page (search.php)

include 'mysql-connect.php';
include 'listing-functions.php';

?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/search.css">
    <title>List of Contributors</title>

</head>

<body>
    <?php
    include 'get-topnav.php';
    ?>

    <div style='width: 80%; margin:auto;'>
        <br><br>
        <div>List of Contributors</div>
        <div class="row row-cols-4 g-4">

            <?php list_all_students($conn) ?>


        </div>

    </div>
    <?php
    include "get-footer.php";
    ?>
</body>

</html>