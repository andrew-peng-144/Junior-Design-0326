<?php
// ini_set('session.cache_limiter','public');
// session_cache_limiter(false); //prevents "document expired" error when going back to this page.
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous"> -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <?php
    if (isset($_GET['query'])) {
        echo "<title>Search Results for: " . htmlspecialchars($_GET['query']) . " </title>";
    } else {
        echo "<title> Empty search query </title>";
        include 'get-topnav.php';
        die;
    }
    ?>
    <link rel="stylesheet" href="css/search.css">


</head>

<body>
    <?php
    include 'get-topnav.php';
    ?>

    <div style='width: 70%; margin:auto;'>
        <br><br>

        <?php
        if (isset($_GET['query'])) {
            echo "<div>Search Results for: " . htmlspecialchars($_GET['query']) . " </div><br>";
        ?>

            <div class="row row-cols-4 g-4">

                <?php


                include 'mysql-connect.php';

                $n_results = 0;

                $query_tokens = array_map('strtolower', explode(" ", $_GET['query'])); //each entry is a single token of the query, and lowercase it

                //match first name and last name from students
                $sql = "SELECT student_id, first_name, last_name, path_to_bio, path_to_portrait FROM students";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    // output data of each row
                    while ($row = $result->fetch_assoc()) {
                        if ( //TODO: actually use WHERE clause in sql instead of php's in_array
                            in_array(strtolower($row['last_name']), $query_tokens)
                            || in_array(strtolower($row['first_name']), $query_tokens)
                        ) { //if firstname or lastname matches
                            //echo a row
                            $bio = "";
                            $bio_path = htmlspecialchars($row['path_to_bio']);
                            if (isset($row['path_to_bio']) && file_exists($bio_path)) {
                                //save bio as string if exists
                                $fh = fopen($bio_path, 'r');
                                while ($line = fgets($fh)) {
                                    $bio .= $line;
                                }
                                fclose($fh);
                            } else {
                                $bio = "Bio unavailable.";
                            }

                ?>

                            <div class="col">
                                <a href="student.php?id=<?php echo htmlspecialchars($row['student_id']) ?>"><img src="<?php echo $row['path_to_portrait'] ?>" class="card-img-top" alt="...">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></h5>
                                            <p class="card-text">Student Profile</p>
                                            <p class="card-text truncated-description"><?php $bio ?></p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php
                            $n_results++;
                        }
                    }
                }

                //match title from non-private projects, unless admin
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
                        $title_tokens = array_map('strtolower', explode(" ", $row['title'])); //title split into words
                        if (
                            count(array_intersect($title_tokens, $query_tokens)) > 0
                            || in_array(strtolower($row['last_name']), $query_tokens)
                            || in_array(strtolower($row['first_name']), $query_tokens)
                        ) { // title matches or author matches

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
                            $n_results++;
                        }
                    }
                }

                if ($n_results == 0) {
                    echo "No results";
                }
            }

            ?>

            </div>

    </div>

    <?php
    include "get-footer.php";
    ?>
</body>
<script>
    // //all search results are links to the page
    // all_results = document.querySelectorAll(".search-result");
    // all_results.forEach(element => {
    //     let dest = "project.php";
    //     if (element.classList.contains("sr-student")) {
    //         dest = "student.php";
    //     }
    //     element.addEventListener('click', function() {
    //         window.location.href = dest;
    //     })
    // });
</script>

</html>