<?php
//The homepage.


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$n_projects = 0;

/**
 * Output the HTML for the slides in the featured projects slideshow.
 * @param integer &$n_projects pass-by-reference the number of projects to reference globally 
 */
function display_slides(&$n_projects) {
    //attempt to login to db to display all featured works.
    include 'mysql-connect.php';
    //select featured projects info, and also grab names of authors (using inner join)
    $select_what = "project_id, path_to_description, title, private, featured, path_to_cover_image, students.student_id, students.first_name, students.last_name";
    $sql = "SELECT {$select_what} from projects INNER JOIN students on projects.student_id=students.student_id and projects.featured=1";

    $result = $conn->query($sql);
    $n_projects = $result->num_rows;
    if ($result->num_rows > 0) {
        $i = 0; //counter for each featured project
        while ($row = $result->fetch_assoc()) {

            //Output the HTML for each slide.
            slide_html($row['title'], $row['student_id'], $row['first_name'], $row['last_name'], $row['path_to_description'], $row['project_id'], $row['path_to_cover_image']);

            $i++;
        }
    } else {
        echo "No featured projects, or there was an error.";
    }
} //End display_slides function

/**
 * Simply output a <div> element that's a single slide of the featured project slideshow.
 * Only called by display_slides()
 */
function slide_html($title, $student_id, $first_name, $last_name, $path_to_description, $project_id, $path_to_cover_image) {

    // read the description into a single string variable $desc
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

    ?>
        <div class="mySlides fade">
            <div class="topleft-caption">
                <span class="caption-title">
                    <?php echo $title ?>
                </span>
                <span class="caption-author">
                    by <a href="student.php?id=<?php echo $student_id ?>"> <?php echo $first_name . " " . $last_name ?> </a>
                </span>
                <br>
                <div class="caption-description">
                    <?php echo $desc; ?>
                </div>

            </div>
            <a href="project.php?id=<?php echo $project_id ?>">
                <img class="cover-image" src="<?php echo $path_to_cover_image; ?>" style="width:100%">
            </a>
        </div>
        
    <?php
}

?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">

    <title>CGA Project Showcase</title>
    <meta name="description" content="Student-led pedagogical materials for higher education in prison and re-entry programs">
    <meta name="author" content="Gatech Junior Design Team JID-0326">
    <link rel="stylesheet" href="css/index.css">

    <style>

    </style>
</head>

<body>
    <?php
    include 'get-topnav.php';
    ?>


    <div style='width: 85%; margin:auto;'>
        <br><br>
        <h1 style="">FEATURED PROJECTS</h1>

        <!--featured -->
        <div class="slideshow-container">

            <?php
                display_slides($n_projects);
            ?>
            <!-- Next and previous buttons -->
            <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
            <a class="next" onclick="plusSlides(1)">&#10095;</a>
        </div>
        <br>
        <br>

        <!-- The dots -->
        <div style="text-align:center; padding: 20px">
            <?php
            for ($i = 0; $i < $n_projects; $i++) {
            ?>
                <span class="dot" onclick="currentSlide(<?php echo $i + 1?>)"></span>
            <?php
            }
            ?>
        </div>

        <script>
            //script for the Featured slideshow
            
            var slideIndex = 1;
            showSlides(slideIndex);

            // Next/previous controls
            function plusSlides(n) {
                showSlides(slideIndex += n);
            }

            // Thumbnail image controls
            function currentSlide(n) {
                showSlides(slideIndex = n);
            }

            /**
             * Set the specified slide to be visible and all the other slides to be hidden.
             */
            function showSlides(n) {
                var i;
                var slides = document.getElementsByClassName("mySlides");
                var dots = document.getElementsByClassName("dot");
                if (n > slides.length) {
                    slideIndex = 1
                }
                if (n < 1) {
                    slideIndex = slides.length;
                }
                for (i = 0; i < slides.length; i++) {
                    slides[i].style.display = "none";
                }
                for (i = 0; i < dots.length; i++) {
                    dots[i].className = dots[i].className.replace(" active", "");
                }
                slides[slideIndex - 1].style.display = "block";
                dots[slideIndex - 1].className += " active";
            }
        </script>


        <div style="">
            <br><br>
            <h1 style="">
                Search for Projects:
            </h1>
            <div id='homepage-search-wrapper'>
                <form action="search.php" method="get">
                    <input class='search-bar' type="text" placeholder="Search..." name="query" required>
                    <span class='custom-button' onclick="document.getElementById('search-submit').click();"> <img src="data/home/search.png" style="width:20px"> </span>

                    <span class='custom-button' onclick=onclick_filters_dropdown()>Filters</span>


                    <div class='filter-panel' style="display:none;">
                        Result Type: <br>

                        <input type="radio" id="radio-project" name='result-type' value='only-projects'>
                        <label for="radio-project">Only Projects</label>
                        <br>

                        <input type="radio" id="radio-student" name='result-type' value='only-students'>
                        <label for="radio-student">Only Student Profiles</label>
                        <br>

                        <input type="radio" id="radio-both" name='result-type' value='both' checked>
                        <label for="radio-both">Both</label>
                        <br><br>

                        Project File Media Type: <br>
                        <input type="checkbox" id="checkbox-any" name="has-any" onclick=onclick_media_type_any() checked>
                        <label for="checkbox-any">Any</label>

                        <input type="checkbox" id="checkbox-Text" name="has-text" checked disabled>
                        <label for="checkbox-Text">Text Documents</label>

                        <input type="checkbox" id="checkbox-Videos" name="has-video" checked disabled>
                        <label for="checkbox-Videos">Videos</label>

                        <input type="checkbox" id="checkbox-Images" name="has-image" checked disabled>
                        <label for="checkbox-Images">Images</label>
                        
                    </div>

                    <input type='submit' id="search-submit" hidden>
                </form>

            </div>

            <script>
                //script for show/hide the advanced search filter panel.
                var search_wrapper = document.getElementById("homepage-search-wrapper");
                var panel = document.querySelector(".filter-panel")

                /**
                 * Onclick handler for clicking the "Filters" button
                 * 
                 * Toggles between showing and hiding the filter panel.
                 */
                function onclick_filters_dropdown() {

                    if (panel.style.display === "none") {
                        panel.style.display = "block";
                    } else {
                        panel.style.display = "none";
                    }
                }
            </script>

            <script>
                //script for enable/disable the media types in the advanced search panel
                var any_checkbox = document.getElementById("checkbox-any");

                var text_checkbox = document.getElementById("checkbox-Text");
                var video_checkbox = document.getElementById("checkbox-Videos");
                var image_checkbox = document.getElementById("checkbox-Images");

                /**
                 * Onclick handler for clicking on the "any" checkbox in the filter panel.
                 * 
                 * Toggles between enabling and disabling the filetype checkboxes.
                 */
                function onclick_media_type_any() {
                    if (any_checkbox.checked) {
                        video_checkbox.disabled = true;
                        image_checkbox.disabled = true;
                        text_checkbox.disabled = true;

                    } else {
                        video_checkbox.disabled = false;
                        image_checkbox.disabled = false;
                        text_checkbox.disabled = false;
                    }
                }
            
            </script>

            <p style="text-align:center;">
                We are currently exhibiting student projects from the course "Shakespeare and the Common Good in Atlanta". Learn more <a href="aboutus.php" style="color:#353940;">here.</a>

            </p>
            <table style="padding: 10px;width: 50%;margin: auto;">
                <tbody>
                    <tr>
                        <td> <img src="./data/home/20201023_155555.jpg" style="width: 100%;"></td>
                        <td>
                            <img src="./data/home/class photo_revised.png" style="width: 100%;">
                            <img src="./data/home/Sh&CGAFlyer.jpg" style="width: 100%;">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <?php
    include "get-footer.php";

    ?>

</body>

</html>