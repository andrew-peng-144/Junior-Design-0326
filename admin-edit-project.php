<?php
//Project editing page. Given a project ID in the URL, admins input fields in a form to edit data about the project.

error_reporting(E_ALL);

//only admins allowed:
session_start();
if (!isset($_SESSION["administrator"]) || $_SESSION["administrator"] !== true) {
    header("location: ./admin-login.php");
    exit;
}
include "./admin-mysql-connect.php";

$status_messages = ""; //strings separated by <br>'s, each says what field has been updated after submission

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //if an admin arrived on this page via GET request, then we just determine which project to edit.
    //the project to edit is indicated by the project ID, which is in the url

    if (!isset($_GET["id"])) {
        echo "Invalid project ID.";
        die;

    } else {

        //get the title of the project and set that to be the $title variable, so that the admin can see the title of which project they are editing.
        $project_id = $_GET["id"];
        $sql = 'SELECT project_id, title from projects where project_id=' . $project_id; //SQL injection vulnerable

        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                //should be only 1 result
                $title = $row['title'];
            }
        } else {
            echo "No project exists with that ID";
            die;
        }
    }

}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //On form submit, we gather values from $_POST variables and make database calls with the POST values.

    $project_id = $_POST["id"];


    //these 4 variables are the 4 aspects of a project to edit
    $title = trim($_POST["title"]); //the $title variable also gets displayed after "Edit Project:"...
    $feat = isset($_POST["feat"]) ? 1 : 0; //1=featured, 0=not
    $priv = isset($_POST["priv"]) ? 1 : 0;
    $desc = trim($_POST["description"]);


    if (!empty($title)) {
        //update title if title was specified
        $stmt = $conn->prepare("UPDATE projects set title=? where project_id=?");
        $stmt->bind_param("si", $title, $project_id);
        $stmt->execute();
        $stmt->close();
        $status_messages .= "Title has been updated to {$title}.<br>";
    } else {

        //Didn't specify a title, but we still set the $title variable to the original title instead, so it still gets displayed after "Edit Project:"...
        $sql = 'SELECT project_id, title from projects where project_id=' . $project_id;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                //should be only 1 result
                $title = $row['title'];
            }
        }
    } //END update title

    //set if featured
    $stmt = $conn->prepare("UPDATE projects set featured=? where project_id=?");
    $stmt->bind_param("ii", $feat, $project_id);
    $stmt->execute();
    $stmt->close();
    if ($feat == 1) {
        $status_messages .= "Project is now featured.<br>";
    } else {
        $status_messages .= "Project is now NOT featured.<br>";
    } //END set if featured

    //set if private
    $stmt = $conn->prepare("UPDATE projects set private=? where project_id=?");
    $stmt->bind_param("ii", $priv, $project_id);
    $stmt->execute();
    $stmt->close();
    if ($priv == 1) {
        $status_messages .= "Project is now private.<br>";
    } else {
        $status_messages .= "Project is now NOT private.<br>";
    } //END set if private


    //If new description is not empty, then replace all the text in the old description.txt with the new desc.
    if (!empty($desc)) {
        $folder = "data/pro/" . $project_id;
        if (!is_dir($folder)) {
            // project folder doesn't exist, make it
            mkdir($folder);
        }

        file_put_contents($folder . "/description.txt", $desc); //put in new description (overwrites the old description)
        $status_messages .= "Description has been updated.<br>";

        //point to new description
        $sql = "UPDATE projects SET path_to_description=\"" . "data/pro/" . $project_id . "/description.txt" . "\" WHERE project_id=" . $project_id;
        $conn->query($sql);
    } //END update description


    //if cover image was specified, then replace the old one
    if (is_uploaded_file($_FILES['uploaded_image']['tmp_name'])) {

        $folder = "data/pro/" . $project_id;
        $imageFileType = strtolower(pathinfo($_FILES['uploaded_image']['name'], PATHINFO_EXTENSION));

        if (!is_dir($folder)) {
            // project folder doesn't exist, make it
            mkdir($folder);
        }

        // Only allow png files
        if ($imageFileType != "png") {
            $status_messages .= "New cover image must be a PNG image file, so it was not uploaded. <br>";

        } else {
            //If it's a png file:
            //delete old image at the path data/pro/<id>/cover_image.png
            $target_file = "data/pro/{$project_id}/cover_image.png";
            if (file_exists($target_file)) {
                unlink($target_file);
            }

            //move the new uploaded image into the server's filesystem
            if (move_uploaded_file($_FILES["uploaded_image"]["tmp_name"], $target_file)) {
                $status_messages .= "The cover image " . htmlspecialchars(basename($_FILES["uploaded_image"]["name"])) . " has been updated.";
                //update the database entry to point to the new file
                $sql = "UPDATE projects SET path_to_cover_image=\"" . "data/pro/" . $project_id . "/cover_image.png" . "\" WHERE project_id=" . $project_id;
                $conn->query($sql);

            } else {
                $status_messages .= "Sorry, there was an error uploading the cover image...";
            }
        }
    } //END update cover image


    //if reset-files is checked, then reset the project's files and add in the new ones specified
    if (isset($_POST['reset-files'])) {

        //if data/pro/<id>/files folder exists
        $folder = "data/pro/" . $project_id . "/files";
        if (is_dir($folder)) {

            //delete everything inside '/files' folder
            $files = glob($folder . '/*'); // get all file names
            foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                    unlink($file); // delete file
                }
            }

            //delete '/files' folder itself
            rmdir($folder);
            $status_messages .= "This project's \"files\" folder has been deleted. <br>";

            //delete the entry in the project_files table
            $sql = "DELETE from project_files where project_files.project_id=" . $project_id;
            if ($conn->query($sql) === TRUE) {
                echo "Project files record deleted successfully";
            } else {
                echo "Error deleting record: " . $conn->error;
            }     
        }

        //for each new file, up to 3, put the file into the folder 'data/pro/<id>/files/'
        //(for loop is identical to the file upload scheme in admin-upload-project.php, except for the $status_messages line. Violation of DRY principle)
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

                        //status message: the only difference between here and the file upload code snippet in admin-upload-project.php
                        $status_messages .= "The file " . $filename . " has been uploaded. <br>";

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
        } //end for loop, for each file (1 to 3)



    } //END reset project files
}

?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/upload.css">

    <title>Editing Project: <?php echo $title ?> </title>
</head>

<body style="background-color:lightgray">
    <?php include 'get-topnav.php'; ?>

    <div class="jumbotron vertical-center">


        <div class="container" style="background-color: #6495ED">

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="header">
                    <h1>Edit Project:
                        <a href="project.php?id=<?php echo $project_id ?>">
                            <?php echo $title ?>
                        </a>
                    </h1>
                </div>

                <br><br>
                <div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault1" name="feat" value="">
                            <label class="form-check-label" for="flexSwitchCheckDefault1">Featured (Will submit this new value)</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault2" name="priv" value="">
                            <label class="form-check-label" for="flexSwitchCheckDefault2">Private (Will submit this new value)</label>
                        </div>
                    </div>


                </div>

                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">New Title</label>
                    <input type="text" name="title" value="" style="color: #212529">
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlTextarea1" class="form-label">New Description</label>
                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="5" name="description"></textarea>
                </div>

                <div class="mb-3">
                    <label for="formFile" class="form-label">New Cover Image</label>
                    <input class="form-control" type="file" id="formFile" name="uploaded_image">
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault3" name="reset-files" value="" onclick="toggle()">
                    <label class="form-check-label" for="flexSwitchCheckDefault3">Reset uploaded files</label>
                </div>
                <script>
                    function toggle() {
                        //Toggles whether the 3 file inputs are enabled
                        var inputs = document.getElementsByClassName("new-file");
                        for (let i = 0; i < inputs.length; i++) {
                            inputs.item(i).toggleAttribute("disabled");
                        }
                    }
                </script>
                <div class="mb-3">
                    <label for="formFileMultiple" class="form-label">New Project Files (up to 3)</label>
                    <input class="form-control new-file" type="file" name="uploaded_file_1" disabled>
                    <input class="form-control new-file" type="file" name="uploaded_file_2" disabled>
                    <input class="form-control new-file" type="file" name="uploaded_file_3" disabled>
                </div>

                <div class="d-grid gap-2 col-6 mx-auto">
                    <input type="submit" class="btn btn-light" value="Submit">
                    <span style="color:black">
                        <?php
                        echo $status_messages;
                        ?>
                    </span>
                </div>

                <input type="text" style="display:none" name="id" value="<?php echo $project_id; ?>">
                <!--Invisible form element that just holds the project ID, to submit to POST-->
            </form>

            <a href="admin-remove-projects.php">Remove projects...</a>
        </div>
    </div>

    <?php
    include "get-footer.php";
    ?>

</body>

</html>