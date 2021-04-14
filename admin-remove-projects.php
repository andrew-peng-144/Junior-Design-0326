<?php
//Page for admins to remove projects. They are shown a list of all projects and they just select which ones to remove then submit.

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
    include "./admin-mysql-connect.php";

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

                $dirPath = "data/pro/" . $row['project_id'];
                try {
                    deleteDir($dirPath);
                    echo "Project folder ".$pid." deleted.";
                } catch (Exception $e) {
                    echo 'Folder didnt exist: ' . $dirPath;
                }
            }
        }


        //delete the database record for the project's files first
        $sql = "DELETE from project_files where project_files.project_id=" . $pid;
        if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $conn->error;
        }     

        
        //delete the project record from database
        $sql = "DELETE from projects where projects.project_id=" . $pid;
        if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }

    //
}


include "get-topnav.php";
?>


<h1>Select project(s) to remove from the database.</h1>
<h3>Note that all its files will also be removed.</h3>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <?php
    //list all projects and have a checkbox next to each.

    include 'mysql-connect.php';

    $sql = "SELECT project_id, title, path_to_description, students.first_name, students.last_name FROM projects "
        . "LEFT JOIN students ON projects.student_id=students.student_id"; //LEFT JOIN will still list projects whose author isn't in the database for some reason.
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            $pid = htmlspecialchars($row['project_id'])
    ?>
            <input type="checkbox" name="p<?php echo $pid ?>" id="p<?php echo $pid ?>">
            <label for="p<?php echo $pid ?>">
                <?php echo '#' . $pid . " " . htmlspecialchars($row['title']) . " by " . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?>
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

<?php include "get-footer.php"?>