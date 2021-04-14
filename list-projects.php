<?php
//This page lists out all projects in the database. It is in the same visual format as the Search results page (search.php)

include 'mysql-connect.php';


/**
*
*Outputs all projects in the database as <div>'s in HTML.
*
* @param mysqli $conn the mysqli object.
*/
function list_all_projects($conn, $admin)
{
    if ($admin) {
        //only admin can see private projects
        $sql = "SELECT project_id, title, private, path_to_description, path_to_cover_image, students.first_name, students.last_name FROM projects "
            . "INNER JOIN students ON projects.student_id=students.student_id";
    } else {

        //normal user is the exact same query, except can't see private projects.
        $sql = "SELECT project_id, title, private, path_to_description, path_to_cover_image, students.first_name, students.last_name FROM projects "
            . "INNER JOIN students ON projects.student_id=students.student_id AND private=0";
    }

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            // Saving the description of the project into the $desc variable.
            $desc = "";
            if (isset($row['path_to_description'])) {
                $fh = fopen(htmlspecialchars($row['path_to_description']), 'r');
                while ($line = fgets($fh)) {
                    $desc .= $line;
                }
                fclose($fh);
            } else {
                $desc = "Description unavailable.";
            }

            //The below HTML snippet is a single "card" search result. Since the snippet is inside a PHP while loop, multiple cards may be printed in total.
        ?>
            <div class="col">
                <a style="" href="project.php?id=<?php echo htmlspecialchars($row['project_id']) ?>">
                    <div class="card">
                        <img style="" src="<?php echo $row['path_to_cover_image'] ?>" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['title']) ?></h5>
                            <p class="card-text">by <?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></p>
                            <p class="card-text truncated-description"><?php echo $desc ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php
        }
    }


}//End function list_all_projects
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/search.css">
    <title>List of projects</title>

</head>

<body>
    <?php
    include 'get-topnav.php';
    ?>

    <div style='width: 80%; margin:auto;'>
        <br><br>
        <div>List of all Projects</div>
        <div class="row row-cols-4 g-4">
            <?php

            list_all_projects($conn, $admin);

            ?>
        </div>
        <!--End the <div> for the container of search result cards.-->
    </div>

    <?php
    include "get-footer.php";
    ?>
</body>

</html>