<?php
//homepage but search actually works and featured actually grabs from db.

//attempt to login to db at the very beginning, in order to show the featured work.

//then in search bar, upong clicking search, run the query, then display the results on search.php page.



//check if admin
session_start();
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="./main.css">
    <style>


    </style>

</head>

<body>
    <div class="topnav">
        <a href="index.php" class="active-nav">Home</a>
        <a href="#about">About</a>
        <a href="#contact">Contact</a>
        <?php
        //echo $admin ? 'true' : 'false';
        if ($admin === true) {
            //heredoc
            $out = <<<'EOF'
                <a href="admin/logout.php" style='float: right;'>LOGOUT</a>
                <a href="admin/upload.php" style='float: right;'>UPLOAD</a>
EOF;
        } else {
            $out = <<<'EOF'
                <a href="admin/admin-login.php" style='float: right;'>Instructor Login</a>
EOF;
        }
        echo $out; //Tabs on top-right are different, depends on whether you are admin or not.
        ?>



    </div>


    <h1 id='title'>Shakespeare and the Common Good of Atlanta</h1>

    <div style='width: 70%; margin:auto;'>
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

        <p>
            Welcome! Here you can find student projects from the Shakespeare literary course
            hosted by Common Good Atlanta.
            You can search any project or click on any Featured project below.
        </p>

        <p style='text-align: center;'>
        </p>

        <div id='homepage-search-wrapper'>
            <form action="search.php" method="get">
                <input class='search-bar' type="text" placeholder="Search..." name="query" required>
                <span class='custom-button' onclick="document.getElementById('search-submit').click();"> <i class="fa fa-search"></i> </span>

                <span class='custom-button' onclick=filters_dropdown_onclick()>(TODO Filters)</span>

                <input type='submit' id="search-submit" hidden>
            </form>


        </div>

        <div class='filter-panel' style="display:none;">
            Sort by:
            <input type="radio" id="Alphabetical" name='sort' checked>
            <label for="Alphabetical">Alphabetical</label>
            <input type="radio" id="Date" name='sort'>
            <label for="Date">Date</label>
            <br>
            Has Media Type:
            <input type="checkbox" id="Videos" checked>
            <label for="Videos">Videos</label>
            <input type="checkbox" id="Images" checked>
            <label for="Images">Images</label>
        </div>

        <span class='custom-button' onclick="window.location.href='list-students.php'">List of Students</span>
        <span class='custom-button' onclick="window.location.href='list-projects.php'">List of Projects</span>
        <br>
        <div id='featured-section'>
            <h1>Featured Projects! (TODO)</h1>
            <a class='featured-prev' onclick="prevSlide()">&#10094;</a>
            <a class='featured-next' onclick="nextSlide()">&#10095;</a>


            <div class='featured-slide'>
                <div class='featured-caption'> <a href="project-jshelley-1.html">"A Sonnet to Typos"</div></a>
                <div class='author'>
                    by <a href="student-jshelley.html">Jonathan Shelley</a>
                </div>
                <div>
                    <i>No description available.</i>
                </div>
                <img src="./data/home/img1.png">

            </div>
            <div class='featured-slide'>
                <div class='featured-caption'> <a href="project-jshelley-2.html">"Ode to an Orchid"</div></a>
                <div class='author'>
                    by <a href="student-jshelley.html">Jonathan Shelley</a>
                </div>
                <div>
                    <i>No description available.</i>
                </div>
                <img src="./data/home/img2.png">
            </div>
            <div class='featured-slide'>
                <div class='featured-caption'> <a href="project-jshelley-3.html"> Intro to Poetic Meter, Fall 2020</div>
                </a>
                <div class='author'>
                    by <a href="student-jshelley.html">Jonathan Shelley</a>
                </div>
                <div>
                    <i>No description available.</i>
                </div>
                <img src="./data/home/img3.png">
            </div>
            <div class='featured-slide'>
                <div class='featured-caption'> <a href="project-mclark-1.html">Final essay Shakespeare</div></a>
                <div class='author'>
                    by <a href="student-mclark.html">Michael Clark</a>
                </div>
                <div>
                    <i>No description available.</i>
                </div>
                <img src="./data/home/img4.png">
            </div>



            <script>
                //Script for slide display

                //set 1st project to be visible
                var proj1 = document.querySelector("#featured-section .featured-slide");
                proj1.style.display = 'block';

                var slides = document.querySelectorAll("#featured-section .featured-slide");

                var total_featured = slides.length;

                var currentSlide = 0;

                function nextSlide() {
                    if (currentSlide < total_featured - 1) {
                        currentSlide++;
                    }
                    gotoSlide(currentSlide);
                }

                function prevSlide() {
                    if (currentSlide > 0) {
                        currentSlide--;
                    }
                    gotoSlide(currentSlide);
                }
                //input: index of which slide to goto
                function gotoSlide(index) {


                    for (let i = 0; i < total_featured; i++) { //set all other slides to hidden
                        slides[i].style.display = "none";
                    }
                    console.log(slides);
                    console.log(index);
                    slides[index].style.display = 'block'; //then only show the selected slide
                }
            </script>
        </div>
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
</body>

</html>