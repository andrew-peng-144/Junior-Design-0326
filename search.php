<?php
//This is the Search results page that any user will get taken to after they enter a search query on the homepage.
//Search results will be either Projects or Student Profiles.



$n_results = 0; //number of search results

include 'mysql-connect.php';

//A search query that is entered will get split based on the space character (" ").
//So $query_tokens is the result of the split. (It's an array of words) And is also lowercase'd.
$query_tokens = array_map('strtolower', explode(" ", $_GET['query']));


/**
 * Searches the database for student profiles, and outputs the student profile search results as HTML <div>'s
 * 
 * @param array $query_tokens same array as above with same name
 * @param mysqli $conn the mysqli object.
 * @param integer &$n_results pass-by-reference argument to update the total number of search results
 */
function profile_search ($query_tokens, $conn, &$n_results)
{
    //match the query with the first name and last name from students
    $sql = "SELECT student_id, first_name, last_name, path_to_bio, path_to_portrait FROM students";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            if (
                in_array(strtolower($row['last_name']), $query_tokens) //Last name matches a word in the query
                || in_array(strtolower($row['first_name']), $query_tokens) //Or the first name...
            ) {
                //if firstname or lastname matches, then output a card search result

                //Set the $bio variable.
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


                //The below HTML snippet is a single "card" search result. Since the snippet is technically inside a PHP while loop, multiple cards may be printed in total.
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
} //End function profile_search



/**
 * Searches the database for projects, and outputs the project search results as HTML <div>'s
 * @param array $query_tokens same array as above with same name
 * @param mysqli $conn the mysqli object.
 * @param admin whether the user is an admin or not (boolean)
 * @param integer &$n_results pass-by-reference argument to update the total number of search results
 */
function project_search ($query_tokens, $conn, $admin, &$n_results)
{

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

        while ($row = $result->fetch_assoc()) {

            $title_tokens = array_map('strtolower', explode(" ", $row['title'])); //title split into words


            if (
                count(array_intersect($title_tokens, $query_tokens)) > 0 //A word from the title matches a word from the query
                || in_array(strtolower($row['last_name']), $query_tokens) //Or the author's last name matches query
                || in_array(strtolower($row['first_name']), $query_tokens) //Or their first name...
            ) {

                // Get a value for the $desc variable
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


                //The below HTML snippet is a single "card" search result. Since the snippet is inside a PHP while loop, multiple cards may be printed in total.
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
} //End function project_search

?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

    <link rel="stylesheet" href="css/search.css">

    <?php
    //Display the correct <title> tag
    if (isset($_GET['query'])) {
        echo "<title>Search Results for: " . htmlspecialchars($_GET['query']) . " </title>";
    } else {
        echo "<title> Empty search query </title>";
        include 'get-topnav.php';
        die;
    }
    ?>
</head>

<body>
    <?php
    include 'get-topnav.php';

    if (!isset($_GET['query'])) {
        echo "There is no search query!";
        die;
    }

    ?>

    <div style='width: 70%; margin:auto;'>
        <br><br>
        <div>Search Results for: "<?php echo htmlspecialchars($_GET['query']) ?>" </div><br>
        <div class="row row-cols-4 g-4">


            <?php
            //First, we'll output search results of Student Profiles.
            profile_search($query_tokens, $conn, $n_results);

            //Next, we'll output search results of Projects.
            project_search($query_tokens, $conn, $admin, $n_results);


            if ($n_results == 0) {
                echo "No results";
            }


            ?>

        </div>

        <?php
        include "get-footer.php";
        ?>
</body>

</html>