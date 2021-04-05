<!-- NOT DONE ADDING BACKEND -->
<?php
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
$upload_status = ""; //for project files
$upload_status_ci = ""; //for cover image
//Any POST request will have client query the database
//upon submitting upload, will do an xhr call which will self reference and go here.

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    //must select existing student (Form forces a student to be selected, using the "required" attribute on <select>)
    $student_id = $_POST["students"];


    //store the inputted data (title, s_id, path, private, featured, path_ci) into the projects table and project_files table. then retrieve project id
    $title = trim($_POST["title"]); //inputted title
    if (empty($title)) {
        $error = "Must have a title. Project not submitted.";
        goto end;
    }

    $stmt = $conn->prepare("INSERT INTO projects (student_id, title, path_to_description, private, featured, path_to_cover_image) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("issiis", $student_id, $title, $p2d, $priv, $feat, $p2ci);
    $p2d = "data/home/default_desc.txt"; //default paths first
    $priv = isset($_POST["priv"]) ? 1 : 0;
    $feat = isset($_POST["feat"]) ? 1 : 0;
    $p2ci = "data/home/img2.PNG";
    $stmt->execute();
    $stmt->close();
    //need project id
    $project_id = $conn->insert_id;

    //now take the files to upload and actually upload them by storing to filesystem. using $_FILES["foo"]. technically not required.
    //the files to upload (attachments) will be located at new location, at /data/pro/<project_id>/files/file.pdf
    //modify the table entries to point to those new locations

    //for each possible upload, up to 3
    for ($i = 1; $i <= 3; $i++) {

        if (isset($_FILES['uploaded_file_' . $i])) {

            /////////////////  PROJECT FILES (up to 3)

            $folder = "data/pro/" . $project_id;
            if (!is_dir($folder)) {
                // dir doesn't exist, make it
                mkdir($folder);
            }
            $folder = "data/pro/" . $project_id . "/files";
            if (!is_dir($folder)) {
                // dir doesn't exist, make it
                mkdir($folder);
            }

            $tmpFilePath = $_FILES['uploaded_file_' . $i]['tmp_name'];
            if ($tmpFilePath != "") {
                $target_file = $folder . "/" . basename($_FILES["uploaded_file_" . $i]["name"]);
                //Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $target_file)) {
                    $filename = htmlspecialchars(basename($_FILES["uploaded_file_" . $i]["name"]));
                    $upload_status .= "The file " . $filename . " has been uploaded. <br>";

                    //create project_files entry
                    $stmt = $conn->prepare("INSERT INTO project_files (project_id, path, file_type) VALUES (?,?,?)");
                    $stmt->bind_param("iss", $project_id, $path, $file_type);
                    $path = "data/pro/" . $project_id . "/files/" . $filename;
                    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }


    //if cover image was specified, then store that at /data/pro/<project_id>/cover_image.png, & update table to point to it
    if (is_uploaded_file($_FILES['uploaded_image']['tmp_name'])) {

        /////////////////////  COVER IMAGE

        $folder = "data/pro/" . $project_id;
        if (!is_dir($folder)) {
            // dir doesn't exist, make it
            mkdir($folder);
        }
        $imageFileType = strtolower(pathinfo($_FILES['uploaded_image']['name'], PATHINFO_EXTENSION));
        // $target_file = $folder . "/" . "cover_image." . $imageFileType; //preserve the image file type. Don't convert it into PNGs for example.
        // $uploadOk = 1;


        // Only allow png files
        if (
            $imageFileType != "png"
        ) {
            $upload_status_ci = "Cover image must be a PNG image file, so the image was not uploaded. <br>";
            // $uploadOk = 0;
        } else {
            $target_file = "data/pro/{$project_id}/cover_image.png";

            if (move_uploaded_file($_FILES["uploaded_image"]["tmp_name"], $target_file)) {
                $upload_status_ci = "The cover image " . htmlspecialchars(basename($_FILES["uploaded_image"]["name"])) . " has been updated.";

                //point to the new file
                $sql = "UPDATE projects SET path_to_cover_image=\"" . "data/pro/" . $project_id . "/cover_image.png" . "\" WHERE project_id=" . $project_id;
                if ($conn->query($sql) === TRUE) {
                }
            } else {
                $upload_status_ci = "Sorry, there was an error uploading the cover image...";
            }
            // $uploaded = true;
        }

        //NOT CONVERTING- instead having PNGs only!

        // if ($uploadOk == 1) {
        //   //convert to png and upload it
        //   imagepng(imagecreatefromstring(
        //     file_get_contents($_FILES['uploaded_image']['tmp_name'])
        //   ), "data/pro/{$project_id}/cover_image.png");
        //   $upload_status_ci = "The file " . htmlspecialchars(basename($_FILES["uploaded_image"]["name"])) . " has been uploaded.";

        //   //point to the new file in SQL
        //   $sql = "UPDATE projects SET path_to_cover_image=\"" . "data/pro/" . $project_id . "/cover_image.png" . "\" WHERE project_id=" . $project_id;
        //   if ($conn->query($sql) === TRUE) {
        //   }
        // }


        // // Check if file already exists (Will never happen because project folder is the ID, and ID's are unique and won't be created a second time, unless deleted and re-created)
        // if (file_exists($target_file)) {
        //   $upload_status_ci = "Sorry, file already exists.";
        //   $uploadOk = 0;
        // }


        // // Check if $uploadOk is set to 0 by an error
        // if ($uploadOk == 0) {
        //   //$upload_status_ci = "Sorry, your file was not uploaded.";
        // } else {
        //   // if everything is ok, try to upload file
        //   if (move_uploaded_file($_FILES["uploaded_image"]["tmp_name"], $target_file)) {
        //     $upload_status_ci = "The file " . htmlspecialchars(basename($_FILES["uploaded_image"]["name"])) . " has been uploaded.";

        //     //point to the new file
        //     $sql = "UPDATE projects SET path_to_cover_image=\"" . "data/pro/" . $project_id . "/cover_image.png" . "\" WHERE project_id=" . $project_id;
        //     if ($conn->query($sql) === TRUE) {
        //     }
        //   } else {
        //     $upload_status_ci = "Sorry, there was an error uploading your file.";
        //   }
        // }
    }

    //if description was not empty, then write to text file /data/pro/<project_id>/description.txt, AND update the row entry to point to that description!
    if (!empty($_POST["description"])) {
        // $myfile = fopen("../data/pro/" . $project_id . "/description.txt", "w") or die("Unable to open file!");
        // fwrite($myfile, $_POST["description"]);
        // fclose($myfile);
        $folder = "data/pro/" . $project_id;
        if (!is_dir($folder)) {
            // dir doesn't exist, make it
            mkdir($folder);
        }
        file_put_contents($folder . "/description.txt", $_POST["description"]);

        //point to description
        $sql = "UPDATE projects SET path_to_description=\"" . "data/pro/" . $project_id . "/description.txt" . "\" WHERE project_id=" . $project_id;

        if ($conn->query($sql) === TRUE) {
        }
    }

    //code made it this far, success message to show

    $success = "Added project " . $title . " successfully.";
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

    <title>Upload Project</title>
</head>

<body>
    <?php include 'get-topnav.php'; ?>

    <div class="jumbotron vertical-center">


        <div class="container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="header">
                    <h1>Upload Project</h1>
                </div>
                <div class="row row-cols-lg-auto g-3 align-items-center">
                    <!-- <div class="col-12">
            <label class="visually-hidden" for="inlineFormSelectPref">Preference</label>
            <select class="form-select" id="inlineFormSelectPref">
              <option selected>Class</option>
              <option value="1">Class 1</option>
              <option value="2">Class 2</option>
              <option value="3">Class 3</option>
            </select>
          </div> -->


                    <div class="col-12">
                        <label class="visually-hidden" for="inlineFormSelectPref">Preference</label>
                        <select class="form-select" id="inlineFormSelectPref stu-sel" name="students" required>
                            <option value="" selected>Select student...</option>
                            <?php
                            //query students table, value is their id

                            $sql = "SELECT student_id, first_name, last_name FROM students"; // SQL with parameters
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                // output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value=" . $row["student_id"] . "> " . $row["first_name"] . " " . $row["last_name"] . "</option>";
                                }
                            }

                            ?>
                        </select>
                    </div>


                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault1" name="feat" value=<?php echo isset($_POST['feat']) ? htmlspecialchars($_POST['feat']) : "" ?>>
                            <label class="form-check-label" for="flexSwitchCheckDefault1">Featured Project</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault2" name="priv" value=<?php echo isset($_POST['priv']) ? htmlspecialchars($_POST['priv']) : "" ?>>
                            <label class="form-check-label" for="flexSwitchCheckDefault2">Private Project</label>
                        </div>
                    </div>


                </div>

                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Project Title</label>
                    <input type="text" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : "" ?>" class="form-control" id="exampleFormControlInput1" required>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlTextarea1" class="form-label">Project Description</label>
                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="5" name="description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : "" ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="formFile" class="form-label">Project Cover Image</label>
                    <input class="form-control" type="file" id="formFile" name="uploaded_image">
                </div>
                <div class="mb-3">
                    <label for="formFileMultiple" class="form-label">Project Files (up to 3)</label>
                    <input class="form-control" type="file" name="uploaded_file_1">
                    <input class="form-control" type="file" name="uploaded_file_2">
                    <input class="form-control" type="file" name="uploaded_file_3">
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
                        echo $upload_status;
                        echo "<br>";
                        echo $upload_status_ci;
                        ?>
                    </span>
                </div>



                <!-- Optional JavaScript; choose one of the two! -->

                <!-- Option 1: Bootstrap Bundle with Popper -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>

                <!-- Option 2: Separate Popper and Bootstrap JS -->
                <!--
      <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js" integrity="sha384-KsvD1yqQ1/1+IA7gi3P0tyJcT3vR+NdBTt13hSJ2lnve8agRGXTTyNaBYmCR/Nwi" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js" integrity="sha384-nsg8ua9HAw1y0W1btsyWgBklPnCUAFLuTMS2G72MMONqmOymq585AcH49TLBQObG" crossorigin="anonymous"></script>
      -->
            </form>

            <a href="admin-remove-projects.php">Remove projects...</a>
        </div>
    </div>

    <?php
    include "get-footer.php";
    ?>
</body>

</html>