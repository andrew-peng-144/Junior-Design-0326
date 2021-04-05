<?php
// ini_set('session.cache_limiter','public');
// session_cache_limiter(false); //prevents "document expired" error when going back to this page.
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <!-- <link rel="stylesheet" href="css/main.css"> -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous"> -->
    <link rel="stylesheet" href="css/search.css">
    <title>List of projects</title>

</head>

<body>
    <?php
    include 'get-topnav.php';
    ?>

    <div style='width: 80%; margin:auto;'>
        <br><br>
        <div>List of all Projects</div>
        <div class="row row-cols-4 g-4">
            <?php



            include 'mysql-connect.php';

            //only admin can see private projects
            if ($admin) {
                $sql = "SELECT project_id, title, private, path_to_description, path_to_cover_image, students.first_name, students.last_name FROM projects "
                . "INNER JOIN students ON projects.student_id=students.student_id";
            } else {
                $sql = "SELECT project_id, title, private, path_to_description, path_to_cover_image, students.first_name, students.last_name FROM projects "
                    . "INNER JOIN students ON projects.student_id=students.student_id AND private=0";
            }
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                // output data of each row
                while ($row = $result->fetch_assoc()) {
                    // reading descrpition below
                    $desc = "";
                    if (isset($row['path_to_description'])) {
                        $fh = fopen(htmlspecialchars($row['path_to_description']), 'r');
                        while ($line = fgets($fh)) {
                            $desc .= $line;
                        }
                        fclose($fh);
                    } else {
                        $desc = "Description unavailable.";
                    }
            ?>

                    <div class="col">
                        <a style="" href="project.php?id=<?php echo htmlspecialchars($row['project_id']) ?>">
                            <div class="card">
                                <img style="" src="<?php echo $row['path_to_cover_image'] ?>" class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['title']) ?></h5>
                                    <p class="card-text">by <?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></p>
                                    <p class="card-text truncated-description"><?php echo $desc ?></p>
                                </div>
                            </div>
                        </a>
                    </div>

            <?php
                }
            }


            ?>
        </div>
    </div>

    <?php
    include "get-footer.php";
    ?>
</body>

</html>