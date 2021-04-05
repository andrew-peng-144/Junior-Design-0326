<?php
//Student editing page (Similar to student adding page)
//Edit the student with the student ID in the URL (as "example.php?id=#")

error_reporting(E_ALL);

//only admins allowed:
session_start();
if (!isset($_SESSION["administrator"]) || $_SESSION["administrator"] !== true) {
    header("location: ./admin-login.php");
    exit;
}
include "./mysql-root-connect.php";

// $error = "";
// $success = "";
// $upload_status_p = ""; //for portrait

$status_messages = ""; //strings separated by <br>'s, each says what field has been updated after submission

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //the student ID is in the url

    if (!isset($_GET["id"])) {
        echo "Invalid student ID.";
        die;
    } else {
        //get the name of the student.
        $student_id = $_GET["id"];
        $sql = 'SELECT student_id, first_name, last_name from students where student_id=' . $student_id;

        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                //should be only 1 result
                $fn = $row['first_name'];
                $ln = $row['last_name'];
                $name = $fn . " " . $ln;
            }
        } else {
            echo "No student exists with that ID";
            die;
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_id = $_POST["id"];

    //get the name of the student (only for displaying on the form)
    $sql = 'SELECT student_id, first_name, last_name from students where student_id=' . $student_id;
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            //should be only 1 result
            $fn = $row['first_name'];
            $ln = $row['last_name'];
            $name = $fn . " " . $ln;
        }
    }

    //edit entry for the student
    $ln_in = trim($_POST["new-student-ln"]); //inputted last name
    $fn_in = trim($_POST["new-student-fn"]); //inputted first name
    $bio_in = trim($_POST["new-student-bio"]); //inputted bio

    if (!empty($ln_in)) {
        $stmt = $conn->prepare("UPDATE students set last_name=? where student_id=?");
        $stmt->bind_param("si", $ln_in, $student_id);
        $stmt->execute();
        $stmt->close();
        $status_messages .= "Last name has been updated to {$ln_in}.<br>";
    }

    if (!empty($fn_in)) {
        $stmt = $conn->prepare("UPDATE students set first_name=? where student_id=?");
        $stmt->bind_param("si", $fn_in, $student_id);
        $stmt->execute();
        $stmt->close();
        $status_messages .= "First name has been updated to {$fn_in}.<br>";
    }

    //Now replace all the text in the old bio.html with the new bio, if the bio entry was not empty
    if (!empty($bio_in)) {
        $folder = "data/stu/" . $student_id;
        file_put_contents($folder . "/bio.txt", ""); //clear the old bio
        file_put_contents($folder . "/bio.txt", $bio_in);
        $status_messages .= "Bio has been updated.<br>";

        //point to new bio
        $sql = "UPDATE students SET path_to_bio=\"" . "data/stu/" . $student_id . "/bio.txt" . "\" WHERE student_id=" . $student_id;

        if ($conn->query($sql) === TRUE) {
        }
    }

    $uploaded = false;
    //if portrait image was specified, then replace the old one and store at /data/stu/<student_id>/portrait.png
    if (is_uploaded_file($_FILES['uploaded_portrait']['tmp_name'])) {

        /////////////////////  PORTRAIT

        $folder = "data/stu/" . $student_id;
        $imageFileType = strtolower(pathinfo($_FILES['uploaded_portrait']['name'], PATHINFO_EXTENSION));
        // $target_file = $folder . "/" . "portrait." . $imageFileType; //preserve the image file type. Don't convert it into PNGs for example.
        // $prev_file_jpg = $folder . "/portrait.jpg";
        // $prev_file_png = $folder . "/portrait.png";
        // $prev_file_jpeg = $folder . "/portrait.jpeg";
        // $prev_file_gif = $folder . "/portrait.gif";

        //$uploadOk = 1;


        // Only allow PNG files
        if (
            $imageFileType != "png"
        ) {
            $status_messages .= "New portrait must be a PNG image file, so it was not uploaded. <br>";
            // $uploadOk = 0;
        } else {


            //PNG detected

            //delete old image (which is assumed to be stored at data/stu/[id]/portrait.png, though the database entry may not point to there. Like the default image is in data/home instead)
            $target_file = "data/stu/{$student_id}/portrait.png";
            if (file_exists($target_file)) { //old image may not be in the above, such as a default image, which is at data/home instead.
                unlink($target_file);
            }

            // delete the old image(which could be any of 4 image filetypes) from the filesystem, because it's getting replaced
            // if (file_exists($prev_file_jpg)) {
            //     unlink($prev_file_jpg);
            // } else if (file_exists($prev_file_png)) {
            //     unlink($prev_file_png);
            // } else if (file_exists($prev_file_jpeg)) {
            //     unlink($prev_file_jpeg);
            // } else if (file_exists($prev_file_gif)) {
            //     unlink($prev_file_gif);
            // }

            //not converting, only allowing PNGs.
            // if ($uploadOk == 1) {
            // //convert to png and upload new portrait
            // imagepng(imagecreatefromstring(
            //     file_get_contents($_FILES['uploaded_portrait']['tmp_name'])
            // ), $target_file);
            // $uploaded = true;
            // $status_messages .= "The portrait image has been updated.";

            // //point to the new file (only necessary if the previous image was not at data/.../portrait.png, which is the case for default portraits.)
            // $sql = "UPDATE students SET path_to_portrait=\"" . "data/stu/" . $student_id . "/portrait.png" . "\" WHERE student_id=" . $student_id;
            // if ($conn->query($sql) === TRUE) {
            // }
            // }

            if (move_uploaded_file($_FILES["uploaded_portrait"]["tmp_name"], $target_file)) {
                $status_messages .= "The portrait image " . htmlspecialchars(basename($_FILES["uploaded_portrait"]["name"])) . " has been updated.";
                //point to the new file
                $sql = "UPDATE students SET path_to_portrait=\"" . "data/stu/" . $student_id . "/portrait.png" . "\" WHERE student_id=" . $student_id;
                if ($conn->query($sql) === TRUE) {
                }
            } else {
                $status_messages .= "Sorry, there was an error uploading the portrait image...";
            }
            $uploaded = true;
        }
    }

    if (empty($ln_in) && empty($fn_in) && empty($bio_in) && !$uploaded) {
        $status_messages .= "Please fill the fields that you want to update.";
    }
}

end:

?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/upload.css">

    <title>Editing Student Info for <?php echo $name ?> </title>
</head>

<body style="background-color:lightgray">
    <?php include 'get-topnav.php'; ?>

    <div class="jumbotron vertical-center">


        <div class="container" style="background-color: #5f9ea0">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="header">
                    <h1>Edit Student Info for <a href="student.php?id=<?php echo $student_id ?>"><?php echo $name ?></a></h1>
                </div>

                <label>(Leave a field blank to leave it unchanged.)</label>
                <br><br>

                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">New First Name</label>
                    <input type="text" name="new-student-fn" value="" class="form-control" id="exampleFormControlInput1">
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlInput2" class="form-label">New Last Name</label>
                    <input type="text" name="new-student-ln" value="" class="form-control" id="exampleFormControlInput2">
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlTextarea1" class="form-label">New Bio (Overwrites previous bio)</label>
                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="5" name="new-student-bio"></textarea>
                </div>

                <div class="mb-3">
                    <label for="formFile" class="form-label">New Portrait Image</label>
                    <input class="form-control" type="file" id="formFile" name="uploaded_portrait">
                </div>

                <div class="d-grid gap-2 col-6 mx-auto">
                    <input type="submit" class="btn btn-light" value="Submit">
                    <span style="color:black">
                        <?php
                        echo $status_messages;
                        ?>
                    </span>
                </div>

                <input type="text" style="display:none" name="id" value="<?php echo $student_id; ?>">
                <!--Invisible form element that just holds the Student ID, to submit to POST-->
            </form>

            <a href="admin-remove-students.php">Remove students...</a>
        </div>
    </div>

    <?php
    include "get-footer.php";
    ?>

</body>

</html>