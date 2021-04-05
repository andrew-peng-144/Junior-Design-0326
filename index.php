<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//check if admin
$admin = false;
if (isset($_SESSION["administrator"]) && $_SESSION["administrator"] === true) {
    $admin = true;
}
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">

    <title>CGA Student Project Showcase</title>
    <meta name="description" content="A showcase of student projects from the free Shakespeare course provided by Common Good Atlanta.">
    <meta name="author" content="Gatech Junior Design Team JID-0326">

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> -->

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
        <!-- <p>
            Welcome! Here you can find student projects from the Shakespeare literary course
            hosted by Common Good Atlanta.
            You can search any project or click on any Featured project below.
        </p> -->

        <!--featured -->
        <div class="slideshow-container">

            <?php

            //attempt to login to db to display all featured works.
            include 'mysql-connect.php';
            //select featured projects info, and also grab names of authors (using inner join)
            $sql = 'SELECT project_id, path_to_description, title, private, featured, path_to_cover_image, students.student_id, students.first_name, students.last_name from projects INNER JOIN students on projects.student_id=students.student_id and projects.featured=1';

            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $i = 0; //counter for each featured project
                while ($row = $result->fetch_assoc()) {
            ?>
                    <!-- Full-width images with number and caption text -->
                    <div class="mySlides fade">
                        <div class="topleft-caption">
                            <span class="caption-title">
                                <?php echo $row['title'] ?>
                            </span>
                            <span class="caption-author">
                                by <a href="student.php?id=<?php echo $row['student_id'] ?>"> <?php echo $row['first_name'] . " " . $row['last_name'] ?> </a>
                            </span>
                            <br>
                            <div class="caption-description">
                                <?php
                                //read from description text file, and output the description right here.
                                $myfile = fopen($row['path_to_description'], "r") or die("Unable to open file!");
                                echo fread($myfile, filesize($row['path_to_description']));
                                fclose($myfile);
                                ?>
                            </div>

                        </div>
                        <a href="project.php?id=<?php echo $row['project_id'] ?>">
                            <img class="cover-image" src="<?php echo $row['path_to_cover_image']; ?>" style="width:100%">
                        </a>
                    </div>
            <?php
                    $i++;
                }
            } else {
                echo "No featured projects, or there was an error.";
            }

            ?>
            <!-- Next and previous buttons -->
            <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
            <a class="next" onclick="plusSlides(1)">&#10095;</a>
        </div>
        <br>
        <!-- Slideshow container -->
        <!-- <div class="slideshow-container"> -->

        <!-- Full-width images with number and caption text -->
        <!-- <div class="mySlides fade">
                <div class="numbertext">1 / 3</div>
                <img src="cgalogo.png" style="width:100%">
                <div class="text">PROJECT DESCRIPTION</div>
            </div>

            <div class="mySlides fade">
                <div class="numbertext">2 / 3</div>
                <img src="PROJECT_IMAGE.jpg" style="width:100%">
                <div class="text">PROJECT DESCRIPTION</div>
            </div>

            <div class="mySlides fade">
                <div class="numbertext">3 / 3</div>
                <img src="PROJECT_IMAGE.jpg" style="width:100%">
                <div class="text">PROJECT DESCRIPTION</div>
            </div> -->

        <!-- Next and previous buttons -->
        <!-- <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
            <a class="next" onclick="plusSlides(1)">&#10095;</a> -->
        <!-- </div> -->
        <br>

        <!-- The dots/circles -->
        <div style="text-align:center; padding: 20px">
            <!-- <span class="dot" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
            <span class="dot" onclick="currentSlide(3)"></span> -->
            <?php
            for ($i = 0; $i < $result->num_rows; $i++) {
            ?>
                <span class="dot" onclick="currentSlide(<?php echo $i + 1;
                                                        //i+1 because slide number argument is 1,2,3 instead of 0,1,2.
                                                        ?>)"></span>
            <?php
            }
            ?>

        </div>


        <div style="">
            <br><br>
            <h1 style="">
                Search for Projects:
            </h1>
            <div id='homepage-search-wrapper'>
                <form action="search.php" method="get">
                    <input class='search-bar' type="text" placeholder="Search..." name="query" required>
                    <span class='custom-button' onclick="document.getElementById('search-submit').click();"> <img src="data/home/search.png" style="width:20px"> </span>

                    <span class='custom-button' onclick=filters_dropdown_onclick()>Filters (todo)</span>


                    <div class='filter-panel' style="display:none;">
                        Sort by:
                        <input type="radio" id="Alphabetical" name='sort-alpha' checked>
                        <label for="Alphabetical">Alphabetical</label>
                        <input type="radio" id="Date" name='sort-date'>
                        <label for="Date">Date</label>
                        <br>
                        Has Project Media Type:
                        <input type="checkbox" id="Videos" name="has-video">
                        <label for="Videos">Videos</label>
                        <input type="checkbox" id="Images" name="has-image">
                        <label for="Images">Images</label>
                    </div>

                    <input type='submit' id="search-submit" hidden>
                </form>


            </div>







            <script>
                //script for show/hide the filter panel.
                var search_wrapper = document.getElementById("homepage-search-wrapper");
                var panel = document.querySelector(".filter-panel")


                function filters_dropdown_onclick() {
                    //toggle between showing and hiding the filter panel.
                    if (panel.style.display === "none") {
                        panel.style.display = "block";
                    } else {
                        panel.style.display = "none";
                    }
                }
            </script>
            <script>
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

                function showSlides(n) {
                    var i;
                    var slides = document.getElementsByClassName("mySlides");
                    var dots = document.getElementsByClassName("dot");
                    if (n > slides.length) {
                        slideIndex = 1
                    }
                    if (n < 1) {
                        slideIndex = slides.length
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


            <p style="text-align:center;">
                We are presenting student projects from our Shakespeare literary course. Learn more <a href="aboutus.php" style="color:#353940;">here.</a>

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