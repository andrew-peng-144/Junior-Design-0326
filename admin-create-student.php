<?php
//Student Adding page

error_reporting(E_ALL);

//only admins allowed:
session_start();
if (!isset($_SESSION["administrator"]) || $_SESSION["administrator"] !== true) {
    header("location: ./admin-login.php");
    exit;
}
include "./mysql-root-connect.php";

$error = "";
$success = "";
$upload_status_p = ""; //for portrait
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $student_id = 0;

    //Create entry for new student in students table
    //with default image and empty text file bio.

    $ln_in = trim($_POST["new-student-ln"]); //inputted last name
    $fn_in = trim($_POST["new-student-fn"]); //inputted first name
    $bio_in = trim($_POST["new-student-bio"]); //inputted bio (not required)

    $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, path_to_portrait, path_to_bio) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $ln, $fn, $p2p, $p2b);
    $ln = $ln_in;
    $fn = $fn_in;
    $p2p = "data/home/default_portrait.PNG"; //default portrait path, relative to homepage.
    $p2b = "data/home/default_bio.txt"; //default bio path first.
    $stmt->execute();
    $stmt->close();
    //need student id
    $student_id = $conn->insert_id; //gets the autoincremented id of the prev query

    //Now create bio.txt if the bio entry was not empty
    if (!empty($bio_in)) {
        $folder = "data/stu/" . $student_id;
        if (!is_dir($folder)) {
            // dir doesn't exist, make it
            mkdir($folder);
        }
        file_put_contents($folder . "/bio.txt", $bio_in);

        //point to new bio
        $sql = "UPDATE students SET path_to_bio=\"" . "data/stu/" . $student_id . "/bio.txt" . "\" WHERE student_id=" . $student_id;

        if ($conn->query($sql) === TRUE) {
        }
    }

    $success = "Student added.";

    //if portrait image was specified, then store that at /data/stu/<student_id>/portrait.png, & update table to point to it
    if (is_uploaded_file($_FILES['uploaded_portrait']['tmp_name'])) {

        /////////////////////  PORTRAIT

        $folder = "data/stu/" . $student_id;
        if (!is_dir($folder)) {
            // dir doesn't exist, make it
            mkdir($folder);
        }
        $imageFileType = strtolower(pathinfo($_FILES['uploaded_portrait']['name'], PATHINFO_EXTENSION));
        // $target_file = $folder . "/" . "portrait." . $imageFileType;
        // $uploadOk = 1;

        $target_file = "data/stu/{$student_id}/portrait.png";
        // Only allow image files
        if (
            $imageFileType != "png"
        ) {
            $upload_status_p = "Portrait must be a PNG image file, so the image was not uploaded. <br>";
            // $uploadOk = 0;
        } else {
            if (move_uploaded_file($_FILES["uploaded_portrait"]["tmp_name"], $target_file)) {
                $upload_status_p = "The portrait image " . htmlspecialchars(basename($_FILES["uploaded_portrait"]["name"])) . " has been updated.";

                //point to the new file
                $sql = "UPDATE students SET path_to_portrait=\"" . "data/stu/" . $student_id . "/portrait.png" . "\" WHERE student_id=" . $student_id;
                if ($conn->query($sql) === TRUE) {
                }
            } else {
                $upload_status_p = "Sorry, there was an error uploading the portrait image...";
            }
            $uploaded = true;
        }
    }

    //code made it this far, success message to show
    $success = "Added student " . $fn_in . " " . $ln_in . " successfully.";
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
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous"> -->
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
                    <input type="text" name="new-student-fn" value="<?php echo isset($_POST['new-student-fn']) ? htmlspecialchars($_POST['new-student-fn']) : "" ?>" class="form-control" id="exampleFormControlInput1" required>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlInput2" class="form-label">Student's Last Name</label>
                    <input type="text" name="new-student-ln" value="<?php echo isset($_POST['new-student-ln']) ? htmlspecialchars($_POST['new-student-ln']) : "" ?>" class="form-control" id="exampleFormControlInput2" required>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlTextarea1" class="form-label">Student Bio</label>
                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="5" name="new-student-bio"><?php echo isset($_POST['new-student-bio']) ? htmlspecialchars($_POST['new-student-bio']) : "" ?></textarea>
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



                <!-- Optional JavaScript; choose one of the two! -->

                <!-- Option 1: Bootstrap Bundle with Popper -->
                <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script> -->

                <!-- Option 2: Separate Popper and Bootstrap JS -->
                <!--
      <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js" integrity="sha384-KsvD1yqQ1/1+IA7gi3P0tyJcT3vR+NdBTt13hSJ2lnve8agRGXTTyNaBYmCR/Nwi" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js" integrity="sha384-nsg8ua9HAw1y0W1btsyWgBklPnCUAFLuTMS2G72MMONqmOymq585AcH49TLBQObG" crossorigin="anonymous"></script>
      -->
            </form>

            <a href="admin-remove-students.php">Remove students...</a>
        </div>
    </div>

    <?php
    include "get-footer.php";
    ?>

</body>

</html>