<?php
//recieves id from GET in URL
if (isset($_GET['id'])) {
    require 'mysql-connect.php';
    $id = htmlspecialchars($_GET["id"]);
    $sql = "SELECT project_id, students.student_id, title, private, path_to_description, students.first_name, students.last_name, path_to_cover_image FROM projects INNER JOIN students ON projects.student_id=students.student_id AND project_id={$id}";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['private']) {
                echo 'Private project';
                die;
            }
            $student_name = $row['first_name'] . " " . $row['last_name'];
            $student_id = $row['student_id'];
            $title = $row['title'];

            $desc = "";
            if (isset($row['path_to_description']) && file_exists(htmlspecialchars($row['path_to_description']))) {
                $fh = fopen(htmlspecialchars($row['path_to_description']), 'r');
                while ($line = fgets($fh)) {
                    $desc .= $line;
                }
                fclose($fh);
            }

            $path_to_cover_image = $row['path_to_cover_image'];
        }
    }
}
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">

    <title><?php echo $title ?></title>

    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <?php
    include 'toolbar.php';
    if (empty($_GET['id'])) {
        echo "Invalid";
        die;
    }
    ?>
    <h1 id='title'>Student Project Showcase</h1>

    <div style='width: 70%; margin:auto;'>
        <span class='custom-button' onclick="window.history.back()">Back</span>


        <br><br>
        <span class='title'><?php echo $title ?></span>
        <span class='author'>by: <a href="student?id=<?php echo $student_id ?>"> <?php echo $student_name ?> </a></span>
        <div class='center-panel'>
            <img src="<?php echo $path_to_cover_image ?>" style="width:100%; max-height:600px">
            <p style='text-align: left;'>
                <?php echo $desc ?>
            </p>
            <?php
            //place links for project files
            $sql = "SELECT path, projects.project_id FROM project_files INNER JOIN projects ON projects.project_id=project_files.project_id AND projects.project_id={$id}";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>

                    <a href="<?php echo $row["path"] ?>"><img src="data/home/dl.png" width=50> Download <?php echo basename($row['path']) ?> </a>
                    <br>
            <?php
                }
            }

            ?>
        </div>




        <div class='center-panel' style='float:right; width: 100px'>
            OTHER PROJECTS
        </div>
        <input class='search-bar' type="text" placeholder="Search...">
        <span class='custom-button' onclick="location.href ='search.html';"> <i class="fa fa-search"></i> </span>
    </div>
</body>

</html>