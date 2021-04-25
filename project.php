<?php
//This page displays all the info about a specific project. Which project? The project whose ID is $_GET['id'].

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//check if admin
$admin = false;
if (isset($_SESSION["administrator"]) && $_SESSION["administrator"] === true) {
  $admin = true;
}

include 'listing-functions.php';

if (is_numeric($_GET['id'])) {

    //get all info about this project, given its ID was sent to this page
    //And store the info as global variables: $id, $student_name, $student_id, $title, $desc, $path_to_cover_image, $feat, $priv

    require 'mysql-connect.php';
    $id = htmlspecialchars($_GET["id"]);

    $select_what = "project_id, students.student_id, title, private, featured, path_to_description, students.first_name, students.last_name, path_to_cover_image";
    $sql = "SELECT {$select_what} FROM projects INNER JOIN students ON projects.student_id=students.student_id AND project_id={$id}";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            if ($row['private'] && !$admin) {
                //hide private projects unless viewer is an admin.
                echo 'Private project';
                die;
            }

            $student_name = $row['first_name'] . " " . $row['last_name'];
            $student_id = $row['student_id'];
            $title = $row['title'];

            $desc = "";
            //Fill the $desc variable with the entire description, as a string.
            if (isset($row['path_to_description']) && file_exists(htmlspecialchars($row['path_to_description']))) {
                $fh = fopen(htmlspecialchars($row['path_to_description']), 'r');
                while ($line = fgets($fh)) {
                    $desc .= $line;
                }
                fclose($fh);
            }
            
            $path_to_cover_image = $row['path_to_cover_image'];
            $feat = $row['featured'];
            $priv = $row['private'];
        }
    }
} else {
    //No ID? no project to display!
    echo "Invalid ID in URL";
    die;
}

/**
* Output the HTML of the download links for the project's files.
* @param mysqli $conn mysqli object
*/
function display_download_links($conn) {

    $sql = "SELECT path, projects.project_id FROM project_files INNER JOIN projects ON projects.project_id=project_files.project_id AND projects.project_id={$_GET['id']}";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
    ?>

            <a href="<?php echo $row["path"] ?>"><img src="data/home/dl.png" width=30> Download <?php echo basename($row['path']) ?> </a>
            <br><br>
    <?php
        }
    }
}


/**
* Output the HTML of the edit button ONLY IF the user is an admin.
*/
function display_edit_button($admin) {
    if ($admin == TRUE) {
        ?>
            <span id="edit-button" onclick="window.location.href='admin-edit-project.php?id=<?php echo $_GET['id'] ?>'">
                Edit
                <img src="data/home/edit.png" style="width:20px"></span>
        <?php
    }
}


?>

<!DOCTYPE html>
<html>

<head>
    <title><?php echo $title?></title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/project.css">
</head>

<body>

    <?php include "get-topnav.php";

    ?>
    <br><br>

    <div id="project">
        <div id="">
            <img id='cover-image' src="<?php echo $path_to_cover_image ?>" class="card-img-top" alt="..." style="">
            <h6><?php echo $title ?>
                <span style="font-size: 57%">
                    by <a href="student.php?id=<?php echo $student_id ?>"> 
                        <u><?php echo $student_name ?></u>
                    </a>
                </span>

                <?php
                    display_edit_button($admin);
                ?>
            </h6>


            <div style="padding:2em" id="project-description">
                <?php
                //In addition to the description, also say whether a project is featured and/or private here.
                if ($admin && $priv) {
                    $priv_style = "'color: rgb(150,150,150);'";
                    echo "<div style={$priv_style}>(Private Project)</div>";
                }
                if ($feat) {
                    $feat_style = "'font-style: italic; color: rgb(150,150,150);'";
                    echo "<div style={$feat_style}>This project is featured on the homepage!</div>";
                }
                echo $desc;
                ?>
            </div>
        </div>

        <?php
            display_download_links($conn);
        ?>


        <!--OTHER PROJECTS ----------->
        <h6 style="font-size: 25px">Other Projects:</h6>
        <div class="row row-cols-4 g-4">
            <?php

            //For now, "other projects" just shows every project in the system.
            list_all_projects($conn, $admin);


            ?>
        </div>
    </div>
    <?php
    include "get-footer.php";
    ?>
</body>

</html>