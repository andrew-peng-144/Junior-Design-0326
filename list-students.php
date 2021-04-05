<?php
// ini_set('session.cache_limiter','public');
// session_cache_limiter(false); //prevents "document expired" error when going back to this page.
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous"> -->
    <link rel="stylesheet" href="css/search.css">
    <title>List of students</title>

</head>

<body>
    <?php
    include 'get-topnav.php';
    ?>

    <div style='width: 80%; margin:auto;'>
        <br><br>
        <div>List of all Students</div>
        <div class="row row-cols-4 g-4">
            <?php


            include 'mysql-connect.php';

            $sql = "SELECT student_id, first_name, last_name, path_to_bio, path_to_portrait FROM students";
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

                    <div class="col">
                        <a style="" href='student.php?id=<?php echo $row['student_id'] ?>'>
                            <div class="card">
                                <img style="" src="<?php echo $row['path_to_portrait'] ?>" class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title"> <?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></h5>
                                    <p class="card-text">Student Profile</p>
                                    <p class="card-text truncated-description"><?php echo $bio ?></p>
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