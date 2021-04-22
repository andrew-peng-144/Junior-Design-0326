<?php
//This is the Search results page that any user will get taken to after they enter a search query on the homepage.
//Search results will be either Projects or Student Profiles.



$n_results = 0; //number of search results

include 'mysql-connect.php';
include 'card.php';

//A search query that is entered will get split based on the space character (" ").
//So $query_tokens is the result of the split. (It's an array of words) And is also lowercase'd.
$query_tokens = array_map('strtolower', explode(" ", htmlspecialchars($_GET['query'])));


/**
 * Searches the database for student profiles, and outputs the student profile search results as HTML <div>'s
 * 
 * @param array $query_tokens same as the global variable with the same name
 * @param mysqli $conn the mysqli object.
 * @param integer &$n_results pass-by-reference argument to update the total number of search results
 */
function profile_search($query_tokens, $conn, &$n_results)
{
    //match the query with the first name and last name from students
    $sql = "SELECT student_id, first_name, last_name, path_to_bio, path_to_portrait FROM students";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            //For each result of the SELECT query:
            if (
                in_array(strtolower($row['last_name']), $query_tokens) //Last name matches a word in the query
                || in_array(strtolower($row['first_name']), $query_tokens) //Or the first name...
            ) {
                //if firstname or lastname matches, then output a card search result

                card_display_profile(
                    $row['student_id'],
                    $row['path_to_portrait'],
                    $row['first_name'],
                    $row['last_name'],
                    $row['path_to_bio']
                );

                $n_results++;
            }
        }
    }
} //End function profile_search



/**
 * Searches the database for projects, and outputs the project search results as HTML <div>'s.
 * 
 * Also uses the $_GET superglobal to handle filters on file types. Currently, the filters are: image, video, other
 * 
 * @param array $query_tokens same as the global variable with same name
 * @param mysqli $conn the mysqli object.
 * @param admin whether the user is an admin or not (boolean)
 * @param integer &$n_results pass-by-reference argument to update the total number of search results
 */
function project_search($query_tokens, $conn, $admin, &$n_results)
{

    //select relevant fields from projects and associated students
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
            //for each project:

            $title_tokens = array_map('strtolower', explode(" ", $row['title'])); //title split into words

            if (
                count(array_intersect($title_tokens, $query_tokens)) > 0 //A word from the title matches a word from the query
                || in_array(strtolower($row['last_name']), $query_tokens) //Or the author's last name matches query
                || in_array(strtolower($row['first_name']), $query_tokens) //Or their first name...
            ) {

                //Search query matches a project name or author.

                //If "any" is checked
                //then we don't need to filter by file types at all
                if (isset($_GET['has-any'])) {
                    card_display_project(
                        $row['project_id'],
                        $row['path_to_cover_image'],
                        $row['title'],
                        $row['first_name'],
                        $row['last_name'],
                        $row['path_to_description']
                    );
                    $n_results++;
                    continue;
                }


                //Otherwise, only show the projects whose files have certain file extensions, based on which checkboxes were checked.


                $filetypes = '("filler_file_type"'; //filler type is to ensure there's at least one entry in this list to handle the case where 0 checkboxes are checked
                if (isset($_GET['has-video'])) {
                    $filetypes .= ', "mp4", "mov", "avi", "flv", "mkv", "wmv", "avchd", "webm"';
                }
                if (isset($_GET['has-image'])) {
                    $filetypes .= ', "png", "jpg", "jpeg", "gif", "tiff"';
                }
                if (isset($_GET['has-text'])) {
                    $filetypes .= ', "txt", "doc", "docx", "pdf", "rtf"';
                }
                $filetypes .= ")";

                $sql2 = "SELECT file_id from project_files where project_id=" . $row['project_id'] . " AND project_files.file_type IN " . $filetypes;
                echo $sql2;
                $result2 = $conn->query($sql2);

                if ($result2->num_rows > 0) {
                    //found at least 1 project file that has one of the file extentions in $filetypes.
                    //So we can display this current project as a search result.
                    card_display_project(
                        $row['project_id'],
                        $row['path_to_cover_image'],
                        $row['title'],
                        $row['first_name'],
                        $row['last_name'],
                        $row['path_to_description']
                    );
                    $n_results++;
                }
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

    if (
        !isset($_GET['query'])
        || !isset($_GET['result-type'])
    ) {

        //this search page needs the 'query' and 'result-type' GET fields
        echo "URL for search.php is invalid. Please search from the searchbar on index.php";
        die;
    }

    ?>

    <div style='width: 70%; margin:auto;'>
        <br><br>
        <div>Search Results for: "<?php echo htmlspecialchars($_GET['query']) ?>" </div><br>
        <div class="row row-cols-4 g-4">


            <?php
            //based on search filters

            if (strcmp($_GET['result-type'], "both") === 0) {
                profile_search($query_tokens, $conn, $n_results);
                project_search($query_tokens, $conn, $admin, $n_results);
            } else if (strcmp($_GET['result-type'], "only-projects") === 0) {
                project_search($query_tokens, $conn, $admin, $n_results);
            } else if (strcmp($_GET['result-type'], "only-students") === 0) {
                profile_search($query_tokens, $conn, $n_results);
            }


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