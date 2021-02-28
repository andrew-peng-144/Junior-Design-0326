<?php
// ini_set('session.cache_limiter','public');
// session_cache_limiter(false); //prevents "document expired" error when going back to this page.
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="main.css">
    <title>List of projects</title>

</head>

<body>
    <?php
    include 'toolbar.php';
    ?>

    <h1 id='title'>Student Project Showcase</h1>
    <div style='width: 70%; margin:auto;'>
        <span class='custom-button' onclick="location.href='index.html'">Home</span>
        <br><br>

        <?php
        echo "<span class='subheading'> List of All Projects </span>"; //except private projects
        echo "<div class='center-panel'>";


        include 'mysql-connect.php';

        $sql = "SELECT project_id, title, private, path_to_description, students.first_name, students.last_name FROM projects "
            . "INNER JOIN students ON projects.student_id=students.student_id AND private=0";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {

        ?>
                <div class="search-result" onclick="window.location.href='project.php?id=<?php echo $row['project_id'] ?>'">
                    <?php echo htmlspecialchars($row['title']) ?>
                    <br>
                    by <?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?>
                </div>
        <?php
            }
        }


        echo "</div>";


        ?>

    </div>

    </div>
</body>

</html>