<?php
/*
Contains functions for displaying preview information about a student profile or about a project, in a "card" style.

Included by: search.php, list-projects.php, list-students.php, project.php, listing-functions.php
*/

/**
 * Output a HTML <div> element, in a "card" style, given the following attributes of a project.
 * 
 * @param number $student_id id of the student
 * @param string $path_to_portrait
 * @param string $first_name first name of the student
 * @param string $path_to_bio
 */
function card_display_profile($student_id, $path_to_portrait, $first_name, $last_name, $path_to_bio) {

    //Read the bio into a string variable $bio
    $bio = "";
    $bio_path = htmlspecialchars($path_to_bio);
    if (isset($path_to_bio) && file_exists($bio_path)) {
        //save bio as string if exists
        $fh = fopen($bio_path, 'r');
        while ($line = fgets($fh)) {
            $bio .= $line;
        }
        fclose($fh);
    } else {
        $bio = "Bio unavailable.";
    }

    //Close php tag, because we're printing out a complex section of HTML. It would be cleaner to write it directly as HTML, rather than to have many "echo" statements.
    ?>
        <div class="col">
            <a style="" href='student.php?id=<?php echo $student_id ?>'>
                <div class="card">
                    <img style="" src="<?php echo $path_to_portrait ?>" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title"> <?php echo htmlspecialchars($first_name ). " " . htmlspecialchars($last_name) ?></h5>
                        <p class="card-text">Student Profile</p>
                        <div class="card-text truncated-description">
                            <?php echo $bio; ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php
}

/**
 * Output a HTML <div> element, in a "card" style, given the following attributes of a project:
 * 
 * @param number $project_id id of the project
 * @param string $path_to_cover_image
 * @param string $title title of the project
 * @param string $first_name first name of the author of the project
 * @param string $path_to_description
 */
function card_display_project($project_id, $path_to_cover_image, $title, $first_name, $last_name, $path_to_description) {

    // Read the description of the project into the $desc variable.
    $desc = "";
    if (isset($path_to_description)) {
        $fh = fopen(htmlspecialchars($path_to_description), 'r');
        while ($line = fgets($fh)) {
            $desc .= $line;
        }
        fclose($fh);
    } else {
        $desc = "Description unavailable.";
    }


    //Close php tag, because we're printing out a complex section of HTML. It would be cleaner to write it directly as HTML, rather than to have many "echo" statements.
    ?>
        <div class="col">
            <a style="" href="project.php?id=<?php echo htmlspecialchars($project_id) ?>">
                <div class="card">
                    <img style="" src="<?php echo htmlspecialchars($path_to_cover_image) ?>" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($title) ?></h5>
                        <p class="card-text">by <?php echo htmlspecialchars($first_name) . " " . htmlspecialchars($last_name) ?></p>
                        <div class="card-text truncated-description">
                            <?php echo $desc; ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php
}


?>