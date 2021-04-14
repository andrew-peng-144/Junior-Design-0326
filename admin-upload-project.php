<?php
//The page that admins use to upload new projects. Admins input fields in a form to add a new project.

error_reporting(E_ALL);

//only admins allowed:
session_start();
if (!isset($_SESSION["administrator"]) || $_SESSION["administrator"] !== true) {
    header("location: ./admin-login.php");
    exit;
}
include "./admin-mysql-connect.php";

$error = "";
$success = "";
$upload_status = ""; //for project files
$upload_status_ci = ""; //for cover image

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //if on form submit (form method=post)

    //must select existing student (Form forces a student to be selected, using the "required" attribute on <select>)
    $student_id = $_POST["students"];

    //store the inputted data (title, s_id, path, private, featured, path_ci) into the projects table and project_files table. then retrieve project id
    $title = trim($_POST["title"]); //inputted title (required by input element)

    $stmt = $conn->prepare("INSERT INTO projects (student_id, title, path_to_description, private, featured, path_to_cover_image) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("issiis", $student_id, $title, $p2d, $priv, $feat, $p2ci);
    $p2d = "data/home/default_desc.txt"; //default paths first
    $priv = isset($_POST["priv"]) ? 1 : 0;
    $feat = isset($_POST["feat"]) ? 1 : 0;
    $p2ci = "data/home/img2.png";
    $stmt->execute();
    $stmt->close();
    $project_id = $conn->insert_id; //gets the autoincremented id of the query, which is the project id of the new project being added.

    //now take the files (up to 3) and actually upload them by storing to filesystem. using $_FILES["foo"].
    //the files to upload (attachments) will be located at new location, at /data/pro/<project_id>/files/file.pdf
    //modify the table entries to point to those new locations
    //for loop below is identical to the file upload scheme in admin-edit-project except the $upload_status line.
    for ($i = 1; $i <= 3; $i++) {

        if (is_uploaded_file($_FILES['uploaded_file_' . $i]['tmp_name'])) {

            ///////////////// UPLOAD PROJECT FILES (up to 3)

            $folder = "data/pro/" . $project_id;
            if (!is_dir($folder)) {
                // project folder doesn't exist, make it
                mkdir($folder);
            }
            $folder = "data/pro/" . $project_id . "/files";
            if (!is_dir($folder)) {
                // project's files folder doesn't exist, make it
                mkdir($folder);
            }

            $tmpFilePath = $_FILES['uploaded_file_' . $i]['tmp_name'];
            if ($tmpFilePath != "") {
                $target_file = $folder . "/" . basename($_FILES["uploaded_file_" . $i]["name"]); //such as: "data/pro/2/files/example.pdf"
                //Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $target_file)) {
                    $filename = htmlspecialchars(basename($_FILES["uploaded_file_" . $i]["name"])); //such as: "example.pdf"
                    $upload_status .= "The file " . $filename . " has been uploaded. <br>";

                    //create project_files entry in the table
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

        // Only allow png files
        if ($imageFileType != "png") {
            $upload_status_ci = "Cover image must be a PNG image file, so the image was not uploaded. <br>";
        } else {
            $target_file = "data/pro/{$project_id}/cover_image.png";

            if (move_uploaded_file($_FILES["uploaded_image"]["tmp_name"], $target_file)) {
                $upload_status_ci = "The cover image " . htmlspecialchars(basename($_FILES["uploaded_image"]["name"])) . " has been updated.";

                //update db record to point to the cover image
                $sql = "UPDATE projects SET path_to_cover_image=\"" . "data/pro/" . $project_id . "/cover_image.png" . "\" WHERE project_id=" . $project_id;
                $conn->query($sql);
            } else {
                $upload_status_ci = "Sorry, there was an error uploading the cover image...";
            }
        }
    }

    //if description was not empty, then write to text file /data/pro/<project_id>/description.txt, AND update the row entry to point to that description!
    if (!empty($_POST["description"])) {
        $folder = "data/pro/" . $project_id;
        if (!is_dir($folder)) {
            // dir doesn't exist, make it
            mkdir($folder);
        }
        file_put_contents($folder . "/description.txt", $_POST["description"]);

        //update db record to point to description
        $sql = "UPDATE projects SET path_to_description=\"" . "data/pro/" . $project_id . "/description.txt" . "\" WHERE project_id=" . $project_id;
        $conn->query($sql);
    }

    //code made it this far, success message to show
    $success = "Added project " . $title . " successfully.";

    //Note that if no description, cover image, or files were specified, then there will be no project folder.
    //The project folder would then be created when the project is edited and a desc, cover image, or files are added.
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
                    <div class="col-12">
                        <label class="visually-hidden" for="inlineFormSelectPref">Preference</label>
                        <select class="form-select" id="inlineFormSelectPref stu-sel" name="students" required>
                            <option value="" selected>Select student...</option>
                            <?php
                            //query students table, value is their id

                            $sql = "SELECT student_id, first_name, last_name FROM students"; // SQL with parameters
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {

                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value=" . $row["student_id"] . "> " . $row["first_name"] . " " . $row["last_name"] . "</option>";
                                }
                            }

                            ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault1" name="feat" value="">
                            <label class="form-check-label" for="flexSwitchCheckDefault1">Featured Project</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault2" name="priv" value="">
                            <label class="form-check-label" for="flexSwitchCheckDefault2">Private Project</label>
                        </div>
                    </div>


                </div>

                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Project Title</label>
                    <input type="text" name="title" value="" class="form-control" id="exampleFormControlInput1" required>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlTextarea1" class="form-label">Project Description</label>
                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="5" name="description"></textarea>
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
            </form>

            <a href="admin-remove-projects.php">Remove projects...</a>
        </div>
    </div>

    <?php
    include "get-footer.php";
    ?>
</body>

</html>