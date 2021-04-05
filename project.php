<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//check if admin
$admin = false;
if (isset($_SESSION["administrator"]) && $_SESSION["administrator"] === true) {
    $admin = true;
}


if (isset($_GET['id'])) {

    //get all info about this project, given its ID was sent to this page
    require 'mysql-connect.php';
    $id = htmlspecialchars($_GET["id"]);
    $sql = "SELECT project_id, students.student_id, title, private, featured, path_to_description, students.first_name, students.last_name, path_to_cover_image FROM projects INNER JOIN students ON projects.student_id=students.student_id AND project_id={$id}";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['private'] && !$admin) { //hide private projects unless viewer is an admin.
                echo 'Private project';
                die;
            }
            $student_name = $row['first_name'] . " " . $row['last_name'];
            $student_id = $row['student_id'];
            $title = $row['title'];

            $desc = "";
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
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Project Page</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous"> -->

    <link rel="stylesheet" href="css/project.css">
</head>

<body>

    <?php include "get-topnav.php";

    if (empty($_GET['id'])) {
        echo "Invalid";
        die;
    }

    ?>
    <br><br>



    <div id="project">
        <div id="">
            <img id='cover-image' src="<?php echo $path_to_cover_image ?>" class="card-img-top" alt="..." style="">
            <h6><?php echo $title ?>
                <span style="font-size: 57%">by <a href="student?id=<?php echo $student_id ?>"> <u><?php echo $student_name ?></u> </a></span>

                <?php
                //if admin, have an edit button
                if ($admin == TRUE) {
                ?>
                    <span id="edit-button" onclick="window.location.href='admin-edit-project.php?id=<?php echo $_GET['id'] ?>'">
                        Edit
                        <img src="data/home/edit.png" style="width:20px"></span>
                <?php
                }
                ?>
            </h6>


            <div style="padding:2em" id="project-description">
                <?php
                if ($admin && $priv) {
                    $priv_style = " color: rgb(150,150,150);";
                    echo "<div style={$priv_style}>(Private Project)</div>";
                }
                if ($feat) {
                    $feat_style = "font-style: italic; color: rgb(150,150,150);";
                    echo "<div style={$feat_style}>This project is featured on the homepage!</div>";
                }
                echo $desc;
                ?>
            </div>
        </div>

        <?php
        //place links for project files
        $sql = "SELECT path, projects.project_id FROM project_files INNER JOIN projects ON projects.project_id=project_files.project_id AND projects.project_id={$id}";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
        ?>

                <a href="<?php echo $row["path"] ?>"><img src="data/home/dl.png" width=30> Download <?php echo basename($row['path']) ?> </a>
                <br><br>
        <?php
            }
        }

        ?>


        <!--OTHER PROJECTS ----------->
        <h6 style="font-size: 25px">Other Projects:</h6>
        <div class="row row-cols-4 g-4">
            <?php

            ///////////// COPY PASTED FROM LIST-PROJECTS.PHP ///////////////////
            //so it just lists every project for now

            include 'mysql-connect.php';

            $sql = "SELECT project_id, title, private, path_to_description, path_to_cover_image, students.first_name, students.last_name FROM projects "
                . "INNER JOIN students ON projects.student_id=students.student_id AND private=0";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                // output data of each row
                while ($row = $result->fetch_assoc()) {
                    // reading descrpition below
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


            ?>
        </div>
    </div>
    <?php
    include "get-footer.php";
    ?>
</body>

</html>