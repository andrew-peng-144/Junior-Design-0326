<?php
//Page for admins to remove students. They are shown a list of all students and they just select which ones to remove then submit.

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

    //for each student:
    foreach ($_POST as $key => $val) {
        //echo $key;
        //the key is 's' followed by the student id, e.g. "s15" is student id 15.
        $sid = substr($key, 1);

        //Delete the student's folder
        $sql = "SELECT student_id FROM students WHERE student_id=" . $sid;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                //completely delete the student's folder.
                //Note that this assumes that the student folder is data/stu/<id>.
                $dirPath = "data/stu/" . $row['student_id'];
                try {
                    deleteDir($dirPath);
                    echo 'student folder ' . $row['student_id'] . " deleted.";
                } catch (Exception $e) {
                    echo 'Folder didnt exist: ' . $dirPath;
                }
            }
        }

        //Delete the project's folder.
        $sql = "SELECT project_id, student_id FROM projects WHERE student_id=" . $sid;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                //for each project, completely delete its folder.
                //Note that this assumes that the project folder is data/pro/<id>.
                $pid = $row['project_id'];

                $dirPath = "data/pro/" . $pid;
                try {
                    deleteDir($dirPath);
                    echo 'project folder ' .  $pid . " deleted.";
                } catch (Exception $e) {
                    echo 'Folder didnt exist: ' . $dirPath;
                }


                //also delete its records from the database.

                //delete the record for the project's files first
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
        }

        //Delete the student record from students table.
        $sql = "DELETE from students where student_id=" . $sid;
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
<a href="index.php">Home</a>

<h1>Select student(s) to remove.</h1>
<h3>Note that all their projects and files will also be removed.</h3>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <?php
    //list all students and have a checkbox next to each.

    include 'mysql-connect.php';

    $sql = "SELECT student_id, first_name, last_name FROM students";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            $sid = htmlspecialchars($row['student_id'])
    ?>
            <input type="checkbox" name="s<?php echo $sid ?>" id="s<?php echo $sid ?>">
            <label for="s<?php echo $sid ?>">
                <?php echo '#' . $sid . " " . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?>
            </label>
            <br>

    <?php
        }
    } else {
        echo "No students in database.";
    }
    ?>
    <input type="submit" value="Submit">
</form>

<?php include "get-footer.php" ?>