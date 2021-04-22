<?php
//This file is the page that admins use to create new student profiles on the site. Admins input fields in a form to create a new student.

error_reporting(E_ALL);

//only admins allowed on this page:
session_start();
if (!isset($_SESSION["administrator"]) || $_SESSION["administrator"] !== true) {
    header("location: ./admin-login.php");
    exit;
}

//Connect to the database as admin
include "./admin-mysql-connect.php";

$error = "";
$success = "";
$upload_status_p = ""; //for portrait


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //if on form submit (form method=post)

    //Create entry for new student in students table
    //with default image and empty text file bio.

    $ln_in = trim($_POST["new-student-ln"]); //inputted last name
    $fn_in = trim($_POST["new-student-fn"]); //inputted first name
    $bio_in = trim($_POST["new-student-bio"]); //inputted bio (not required)

    //prepared statement
    $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, path_to_portrait, path_to_bio) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $ln_in, $fn_in, $p2p, $p2b);
    $p2p = "data/home/default_portrait.png"; //p2p is "path to portrait": default portrait path, relative to homepage.
    $p2b = "data/home/default_bio.txt"; //p2b is "path to bio": default bio path first.
    $stmt->execute();
    $stmt->close();
    $student_id = $conn->insert_id; //gets the autoincremented id of the query, which is the student id of the new student being added.

    //create bio.txt if the bio in the form was not empty
    if (!empty($bio_in)) {
        $folder = "data/stu/" . $student_id;
        if (!is_dir($folder)) {
            // student folder doesn't exist, make it
            mkdir($folder);
        }
        file_put_contents($folder . "/bio.txt", $bio_in);

        //update database record to point to new bio
        $sql = "UPDATE students SET path_to_bio=\"" . "data/stu/" . $student_id . "/bio.txt" . "\" WHERE student_id=" . $student_id;
        $conn->query($sql);
    }

    //if portrait image was specified, then store that at /data/stu/<student_id>/portrait.png, & update table to point to it
    if (is_uploaded_file($_FILES['uploaded_portrait']['tmp_name'])) {

        /////////////////////  PORTRAIT

        $folder = "data/stu/" . $student_id;
        if (!is_dir($folder)) {
            // student folder doesn't exist, make it
            mkdir($folder);
        }
        $imageFileType = strtolower(pathinfo($_FILES['uploaded_portrait']['name'], PATHINFO_EXTENSION));

        $target_file = "data/stu/{$student_id}/portrait.png";
        // Only allow png image files
        if ($imageFileType != "png") {
            $upload_status_p = "Portrait must be a PNG image file, so the image was not uploaded. <br>";
        } else {
            if (move_uploaded_file($_FILES["uploaded_portrait"]["tmp_name"], $target_file)) {
                $upload_status_p = "The portrait image " . htmlspecialchars(basename($_FILES["uploaded_portrait"]["name"])) . " has been updated.";

                //update db record to point to the new file
                $sql = "UPDATE students SET path_to_portrait=\"" . "data/stu/" . $student_id . "/portrait.png" . "\" WHERE student_id=" . $student_id;
                $conn->query($sql);
            } else {
                $upload_status_p = "Sorry, there was an error uploading the portrait image...";
            }
        }
    }

    //code made it this far, success message to show
    $success = "Added student " . $fn_in . " " . $ln_in . " successfully.";

    //Note that if no bio or no portrait was specified, then there will be no student folder.
    //The student folder would then be created when the student profile is edited and a bio or portrait is added.
}

end:

?>


<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

    <link rel="stylesheet" href="css/upload.css">
    <!--Same CSS as upload page.-->

    <title>Add Student</title>
</head>

<body>
    <?php include 'get-topnav.php'; ?>

    <div class="jumbotron vertical-center">


        <div class="container" style="background-color: #5f9ea0">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="header">
                    <h1>Add Student</h1>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Student's First Name</label>
                    <input type="text" name="new-student-fn" value="" class="form-control" id="exampleFormControlInput1" required>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlInput2" class="form-label">Student's Last Name</label>
                    <input type="text" name="new-student-ln" value="" class="form-control" id="exampleFormControlInput2" required>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlTextarea1" class="form-label">Student Bio</label>
                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="5" name="new-student-bio"></textarea>
                </div>

                <div class="mb-3">
                    <label for="formFile" class="form-label">Student's Portrait Image</label>
                    <input class="form-control" type="file" id="formFile" name="uploaded_portrait">
                </div>

                <div class="d-grid gap-2 col-6 mx-auto">
                    <input type="submit" class="btn btn-light" value="Submit">
                    <span style="color:black">
                        <?php
                        echo $error;
                        ?>
                    </span>
                    <br>
                    <span style="color:black">
                        <?php
                        echo $success;
                        ?>
                    </span>
                    <span>
                        <?php
                        echo $upload_status_p;
                        ?>
                    </span>
                </div>

            </form>

            <a href="admin-remove-students.php">Remove students...</a>
        </div>
    </div>

    <?php
    include "get-footer.php";
    ?>

</body>

</html>