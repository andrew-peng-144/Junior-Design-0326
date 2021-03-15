<?php

error_reporting(E_ALL);
//only admins allowed:
session_start();
if (!isset($_SESSION["administrator"]) || $_SESSION["administrator"] !== true) {
    header("location: ./admin-login.php");
    exit;
}

function deleteDir($dirPath)
{
    if (!is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}


//On form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "./mysql-root-connect.php";
    // isset($_POST['s1']);
    //echo print_r($_POST);

    //for each project:
    foreach ($_POST as $key => $val) {
        //echo $key;
        //the key is 'p' followed by the student id, e.g. "p15" is project id 15.
        $pid = substr($key, 1);

        //Delete the project's folder.

        $sql = "SELECT project_id, student_id FROM projects WHERE project_id=" . $pid;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                //for each project, completely delete its folder.
                //Note that this assumes that the project folder is data/pro/<id>.

                $dirPath = "../data/pro/" . $row['project_id'];
                try {
                    deleteDir($dirPath);
                    echo "Project folder ".$pid." deleted.";
                } catch (Exception $e) {
                    echo 'Folder didnt exist: ' . $dirPath;
                }
            }
        }


        //Remove the project's records from the projects table and the project_files table.
        $sql = "DELETE projects, project_files from projects inner join project_files on projects.project_id=project_files.project_id where projects.project_id=" . $pid;
        if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }

    //
}
?>


<h1>Select project(s) to remove from the database.</h1>
<h3>Note that all its files will also be removed.</h3>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <?php
    //list all projects and have a checkbox next to each.

    include '../mysql-connect.php';

    $sql = "SELECT project_id, title, path_to_description, students.first_name, students.last_name FROM projects "
        . "LEFT JOIN students ON projects.student_id=students.student_id"; //LEFT JOIN will still list projects whose author isn't in the database for some reason.
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $pid = htmlspecialchars($row['project_id'])
    ?>
            <input type="checkbox" name="p<?php echo $pid ?>" id="p<?php echo $pid ?>">
            <label for="p<?php echo $pid ?>">
                <?php echo '#' . $pid . " " . htmlspecialchars($row['title']) . "by" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?>
            </label>
            <br>

    <?php
        }
    } else {
        echo "No projects in database.";
    }
    ?>
    <input type="submit" value="Submit">
</form>