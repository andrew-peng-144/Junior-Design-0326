<?php
//upload page // TODO


error_reporting(E_ALL);


//only admins allowed:
session_start();
if (!isset($_SESSION["administrator"]) || $_SESSION["administrator"] !== true) {
    header("location: ./admin-login.php");
    exit;
}

//mysql root access
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cga_showcase";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$error = "";
$success = "";
$upload_status = ""; //for project files
$upload_status_ci = ""; //for cover image
//Any POST request will have client query the database
//upon submitting upload, will do an xhr call which will self reference and go here.

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $student_id = 0;

    //first, if the student is new, create entry for them in students table. with default values. bio and stuff can be edited later rip with text editor..
    //with default image and empty text file bio.
    if ($_POST["students"] === "NEW_STUDENT") {
        $ln_in = trim($_POST["new-student-ln"]); //inputted last name
        $fn_in = trim($_POST["new-student-fn"]); //inputted first name
        if (empty($ln_in) || empty($fn_in)) {
            $error = "Must fill both first and last name. Project not submitted.";
            goto end;
        }
        $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, path_to_portrait, path_to_bio) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $ln, $fn, $p2p, $p2b);
        $ln = $ln_in;
        $fn = $fn_in;
        $p2p = "data/home/default_portrait.PNG"; //default paths first, relative to homepage.
        $p2b = "data/home/default_bio.txt";
        $stmt->execute();
        $stmt->close();
        //need student id
        $student_id = $conn->insert_id; //gets the autoincremented id of the prev query

        $success = "Student added.";
    } else {
        //selected existing student
        $student_id = $_POST["students"];
    }


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

        if (isset($_FILES['uploaded_file_'.$i])) {

            /////////////////  PROJECT FILES (up to 3)

            $folder = "../data/pro/" . $project_id;
            if (!is_dir($folder)) {
                // dir doesn't exist, make it
                mkdir($folder);
            }
            $folder = "../data/pro/" . $project_id . "/files";
            if (!is_dir($folder)) {
                // dir doesn't exist, make it
                mkdir($folder);
            }

            $tmpFilePath = $_FILES['uploaded_file_'.$i]['tmp_name'];
            if ($tmpFilePath != "") {
                $target_file = $folder . "/" . basename($_FILES["uploaded_file_".$i]["name"]);
                //Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $target_file)) {
                    $filename = htmlspecialchars(basename($_FILES["uploaded_file_".$i]["name"]));
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

        $folder = "../data/pro/" . $project_id;
        if (!is_dir($folder)) {
            // dir doesn't exist, make it
            mkdir($folder);
        }
        $target_file = $folder . "/" . "cover_image.png";
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file already exists
        if (file_exists($target_file)) {
            $upload_status_ci = "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (
            $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif"
        ) {
            $upload_status_ci = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $upload_status_ci = "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["uploaded_image"]["tmp_name"], $target_file)) {
                $upload_status_ci = "The file " . htmlspecialchars(basename($_FILES["uploaded_image"]["name"])) . " has been uploaded.";

                //point to the new file
                $sql = "UPDATE projects SET path_to_cover_image=\"" . "data/pro/" . $project_id . "/cover_image.png" . "\" WHERE project_id=" . $project_id;
                if ($conn->query($sql) === TRUE) {
                }
            } else {
                $upload_status_ci = "Sorry, there was an error uploading your file.";
            }
        }
    }

    //if description was not empty, then write to text file /data/pro/<project_id>/description.txt, AND update the row entry to point to that description!
    if (!empty($_POST["description"])) {
        // $myfile = fopen("../data/pro/" . $project_id . "/description.txt", "w") or die("Unable to open file!");
        // fwrite($myfile, $_POST["description"]);
        // fclose($myfile);
        $folder = "../data/pro/" . $project_id;
        if (!is_dir($folder)) {
            // dir doesn't exist, make it
            mkdir($folder);
        }
        file_put_contents($folder . "/description.txt", $_POST["description"]);

        $sql = "UPDATE projects SET path_to_description=\"" . "data/pro/" . $project_id . "/description.txt" . "\" WHERE project_id=" . $project_id;

        if ($conn->query($sql) === TRUE) {
        }
    }

    //code made it this far, success message to show
    if ($_POST["students"] === "NEW_STUDENT") {
        $success = "Added student " . $fn_in . " " . $ln_in . " and project " . $title . " successfully.";
    } else {
        $success = "Added project " . $title . " successfully.";
    }

    //later, put text editors (with basic B, I, U, image, stuff) into project and profiles pages...... txt, markdown, or html editors? live?
}

end:

?>



<!DOCTYPE html>
<!--Create username with associated version here. Submit username to mysql table 'users'
-->
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 50px;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <a href="../index.php">Home</a>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">

        <label for="students">Select Student Author:</label>
        <select name="students" id="stu-sel" onchange="stu_onchange()">

            <option value="NEW_STUDENT"> (New Student...) </option>

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
        <br>
        <label>New Student first name:</label>
        <input type="text" name="new-student-fn" id="fn-txt" value=<?php echo isset($_POST['new-student-fn']) ? htmlspecialchars($_POST['new-student-fn']) : "" ?>>
        <br>
        <label>New Student last name:</label>
        <input type="text" name="new-student-ln" id="ln-txt" value=<?php echo isset($_POST['new-student-ln']) ? htmlspecialchars($_POST['new-student-ln']) : "" ?>>
        <br>



        <label>TITLE:</label><input type="text" name="title" value=<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : "" ?>>
        <br>
        DESCRIPTION:<input type="text" name="description" value=<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : "" ?>>
        <br>
        <br>
        Select project files to upload (up to 3):
        <br>
        <input type="file" name="uploaded_file_1">
        <br>
        <input type="file" name="uploaded_file_2">
        <br>
        <input type="file" name="uploaded_file_3">
        <br>
        <br>
        Select cover image to upload:
        <br>
        <input type="file" name="uploaded_image">

        <br>
        <input type="checkbox" name="feat" value=<?php echo isset($_POST['feat']) ? htmlspecialchars($_POST['feat']) : "" ?>> <label>Featured</label>
        <input type="checkbox" name="priv" value=<?php echo isset($_POST['priv']) ? htmlspecialchars($_POST['priv']) : "" ?>> <label>Private</label>
        <br>
        <br>
        <input type="submit" value="Submit">
    </form>
    <span style="color:red">
        <?php
        echo $error;
        ?>
    </span>
    <br>
    <span style="color:green">
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
    <script>
        var stu = document.getElementById("stu-sel");
        var fn = document.getElementById("fn-txt");
        var ln = document.getElementById("ln-txt");

        function stu_onchange() {
            if (stu.value === "NEW_STUDENT") {
                fn.disabled = false;
                ln.disabled = false;
            } else {
                fn.disabled = true;
                ln.disabled = true;
            }
        }
    </script>

</body>

</html>

<?php

$conn->close();
?>