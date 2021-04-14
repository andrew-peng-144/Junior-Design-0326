<?php
//This page lists out all students in the database. It is in the same visual format as the Search results page (search.php)

include 'mysql-connect.php';

/**
* Outputs all student profiles in the database as <div>'s in HTML.
* @param mysqli $conn the mysqli object.
*/
function list_all_students($conn)
{

    $sql = "SELECT student_id, first_name, last_name, path_to_bio, path_to_portrait FROM students";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            // Saving the description of the project into the $desc variable.
            $bio = "";
            $path = htmlspecialchars($row['path_to_bio']);
            if (isset($row['path_to_bio']) && file_exists($path)) {
                $fh = fopen($path, 'r');
                while ($line = fgets($fh)) {
                    $bio .= $line;
                }
                fclose($fh);
            } else {
                $bio = "Bio unavailable.";
            }

            //The below HTML snippet is a single "card" search result. Since the snippet is inside a PHP while loop, multiple cards may be printed in total.
        ?>
            <div class="col">
                <a style="" href='student.php?id=<?php echo $row['student_id'] ?>'>
                    <div class="card">
                        <img style="" src="<?php echo $row['path_to_portrait'] ?>" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title"> <?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></h5>
                            <p class="card-text">Student Profile</p>
                            <p class="card-text truncated-description"><?php echo $bio ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php

        }
    }
}//End function list_all_students
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/search.css">
    <title>List of students</title>

</head>

<body>
    <?php
    include 'get-topnav.php';
    ?>

    <div style='width: 80%; margin:auto;'>
        <br><br>
        <div>List of all Students</div>
        <div class="row row-cols-4 g-4">

            <?php list_all_students($conn) ?>


        </div>

    </div>
    <?php
    include "get-footer.php";
    ?>
</body>

</html>