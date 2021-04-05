<?php
//project editing page (same idea as student editing page)

error_reporting(E_ALL);

//only admins allowed:
session_start();
if (!isset($_SESSION["administrator"]) || $_SESSION["administrator"] !== true) {
    header("location: ./admin-login.php");
    exit;
}
include "./mysql-root-connect.php";

$status_messages = ""; //strings separated by <br>'s, each says what field has been updated after submission

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //the project ID is in the url

    if (!isset($_GET["id"])) {
        echo "Invalid project ID.";
        die;
    } else {
        //get the title of the project.
        $project_id = $_GET["id"];
        $sql = 'SELECT project_id, title from projects where project_id=' . $project_id;

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
    $project_id = $_POST["id"];

    //edit project fields
    $title = trim($_POST["title"]); //variable also gets displayed after "Edit Project:"...
    $feat = isset($_POST["feat"]) ? 1 : 0;
    $priv = isset($_POST["priv"]) ? 1 : 0;
    $desc = trim($_POST["description"]);

    if (!empty($title)) {
        $stmt = $conn->prepare("UPDATE projects set title=? where project_id=?");
        $stmt->bind_param("si", $title, $project_id);
        $stmt->execute();
        $stmt->close();
        $status_messages .= "Title has been updated to {$title}.<br>";
    } else {

        //Didn't specify a title, so we set the $title variable to the old title instead, so it still gets displayed after "Edit Project:"...
        $sql = 'SELECT project_id, title from projects where project_id=' . $project_id;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                //should be only 1 result
                $title = $row['title'];
            }
        }
    }

    //set if featured
    $stmt = $conn->prepare("UPDATE projects set featured=? where project_id=?");
    $stmt->bind_param("ii", $feat, $project_id);
    $stmt->execute();
    $stmt->close();
    if ($feat == 1) {

        $status_messages .= "Project is now featured.<br>";
    } else {
        $status_messages .= "Project is now NOT featured.<br>";
    }

    //set if private
    $stmt = $conn->prepare("UPDATE projects set private=? where project_id=?");
    $stmt->bind_param("ii", $priv, $project_id);
    $stmt->execute();
    $stmt->close();
    if ($priv == 1) {

        $status_messages .= "Project is now private.<br>";
    } else {
        $status_messages .= "Project is now NOT private.<br>";
    }


    //Now replace all the text in the old description.html with the new desc, if not empty
    if (!empty($desc)) {
        $folder = "data/pro/" . $project_id;
        file_put_contents($folder . "/description.txt", ""); //clear the old bio
        file_put_contents($folder . "/description.txt", $desc);
        $status_messages .= "Description has been updated.<br>";

        //point to description
        $sql = "UPDATE projects SET path_to_description=\"" . "data/pro/" . $project_id . "/description.txt" . "\" WHERE project_id=" . $project_id;

        if ($conn->query($sql) === TRUE) {
        }
    }

    $uploaded = false;
    //if cover image was specified, then replace the old one
    if (is_uploaded_file($_FILES['uploaded_image']['tmp_name'])) {

        /////////////////////  PORTRAIT

        $folder = "data/pro/" . $project_id;
        $imageFileType = strtolower(pathinfo($_FILES['uploaded_image']['name'], PATHINFO_EXTENSION));

        // $uploadOk = 1;


        // Only allow png files
        if (
            $imageFileType != "png"
        ) {
            $status_messages .= "New cover image must be a PNG image file, so it was not uploaded. <br>";
            // $uploadOk = 0;
        } else {
            //delete old image at the assumed path
            $target_file = "data/pro/{$project_id}/cover_image.png";
            if (file_exists($target_file)) {
                unlink($target_file);
            }

            if (move_uploaded_file($_FILES["uploaded_image"]["tmp_name"], $target_file)) {
                $status_messages .= "The cover image " . htmlspecialchars(basename($_FILES["uploaded_image"]["name"])) . " has been updated.";
                //point to the new file
                $sql = "UPDATE projects SET path_to_cover_image=\"" . "data/pro/" . $project_id . "/cover_image.png" . "\" WHERE project_id=" . $project_id;
                if ($conn->query($sql) === TRUE) {
                }
            } else {
                $status_messages .= "Sorry, there was an error uploading the cover image...";
            }
            $uploaded = true;
        }



        // if ($uploadOk == 1) {
        //     //convert to png and upload new cover image
        //     imagepng(imagecreatefromstring(
        //         file_get_contents($_FILES['uploaded_image']['tmp_name'])
        //     ), $target_file);
        //     $uploaded = true;
        //     $status_messages .= "The new cover image has been updated.";

        //     //point to the new file in SQL
        //     $sql = "UPDATE projects SET path_to_cover_image=\"" . "data/pro/" . $project_id . "/cover_image.png" . "\" WHERE project_id=" . $project_id;
        //     if ($conn->query($sql) === TRUE) {
        //     }
        // }

        // // Check if $uploadOk is set to 0 by an error
        // if ($uploadOk == 0) {
        // } else {
        //     if (move_uploaded_file($_FILES["uploaded_portrait"]["tmp_name"], $target_file)) {
        //         $status_messages .= "The portrait image " . htmlspecialchars(basename($_FILES["uploaded_portrait"]["name"])) . " has been updated.";
        //     } else {
        //         $status_messages .= "Sorry, there was an error uploading the portrait image..";
        //     }
        //     $uploaded = true;
        // }
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
                <!-- <div class="mb-3">
                    <label for="formFileMultiple" class="form-label">New Project Files (up to 3)</label>
                    <input class="form-control" type="file" name="uploaded_file_1">
                    <input class="form-control" type="file" name="uploaded_file_2">
                    <input class="form-control" type="file" name="uploaded_file_3">
                </div> -->

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