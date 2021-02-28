<?php
// ini_set('session.cache_limiter','public');
// session_cache_limiter(false); //prevents "document expired" error when going back to this page.
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="main.css">
    <?php
    if (isset($_GET['query'])) {
        echo "<title>Search Results for: " . htmlspecialchars($_GET['query']) . " </title>";
    } else {
        echo "<title> Empty search query </title>";
        include 'toolbar.php';
        die;
    }
    ?>




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
        if (isset($_GET['query'])) {
        echo "<span class='subheading'>Search Results for: " . htmlspecialchars($_GET['query']) . " </span>";
        echo "<div class='center-panel'>";


            include 'mysql-connect.php';

            $n_results = 0;

            $query_tokens = array_map('strtolower', explode(" ", $_GET['query'])); //each entry is a single token of the query, and lowercase it

            //match first name and last name from students
            $sql = "SELECT student_id, first_name, last_name, path_to_bio FROM students";
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
                        <div class="search-result" onclick="window.location.href='student.php?id=<?php echo $row['student_id'] ?>'" >
                            <?php echo htmlspecialchars($row['first_name']) ." ". htmlspecialchars($row['last_name']) ?>
                            <br>
                            <?php echo htmlspecialchars($bio) ?>
                        </div>
                        <?php
                        $n_results++;
                    }
                }
            }

            //match title from projects, if non-private.
            $sql = "SELECT project_id, title, private, path_to_description, students.first_name, students.last_name FROM projects "
                 . "INNER JOIN students ON projects.student_id=students.student_id AND private=0";
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

                        //not reading descrpition below
                        // $desc = "";
                        // if (isset($row['path-to-description'])) {
                        //     $fh = fopen(htmlspecialchars($row['path_to_description']), 'r');
                        //     while ($line = fgets($fh)) {
                        //         $desc .= $line;
                        //     }
                        //     fclose($fh);
                        // } else {
                        //     $desc = "Description unavailable.";
                        // }

                        ?>
                        <div class="search-result" onclick="window.location.href='project.php?id=<?php echo $row['project_id'] ?>'" >
                            <?php echo htmlspecialchars($row['title'])?>
                            <br>
                            by <?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?>
                        </div>
                        <?php
                        $n_results++;
                    }
                }
            }

            if ($n_results == 0) {
                echo "No results";
            }
            echo "</div>";
        }

        ?>

    </div>

    </div>
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