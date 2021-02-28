<?php
// ini_set('session.cache_limiter','public');
// session_cache_limiter(false); //prevents "document expired" error when going back to this page.
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="main.css">
    <title>List of students</title>

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
        echo "<span class='subheading'> List of All Students </span>";
        echo "<div class='center-panel'>";


        include 'mysql-connect.php';

        $sql = "SELECT student_id, first_name, last_name, path_to_bio FROM students";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
                $bio = "";
                $path = htmlspecialchars($row['path_to_bio']);
                if (isset($row['path_to_bio']) && file_exists($path)) {
                    $fh = fopen($path, 'r');
                    while ($line = fgets($fh)) {
                        $bio .= $line;
                    }
                    fclose($fh);
                } else {
                    $bio = "Bio unavailable.";
                }
        ?>
                <div class="search-result" onclick="window.location.href='student.php?id=<?php echo $row['student_id'] ?>'">
                    <?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?>
                    <br>
                    <?php echo htmlspecialchars($bio) ?>
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