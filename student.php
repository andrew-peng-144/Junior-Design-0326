<?php
//recieves id from GET in URL
if (!empty($_GET['id'])) {
    require 'mysql-connect.php';
    $id = htmlspecialchars($_GET["id"]);
    $sql = "SELECT first_name, last_name, path_to_bio, path_to_portrait FROM students WHERE student_id={$id}";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $full_name = $row['first_name'] . " " . $row['last_name'];
            $bio = "";
            if (file_exists(htmlspecialchars($row['path_to_bio']))) {
                $fh = fopen(htmlspecialchars($row['path_to_bio']), 'r');
                while ($line = fgets($fh)) {
                    $bio .= $line;
                }
                fclose($fh);
            }
            $path_to_portrait =  $row['path_to_portrait'];
        }
    }
}
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">

    <title><?php echo $full_name ?>'s Profile</title>

    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <?php
    include 'toolbar.php';
    if (empty($_GET['id'])) {
        echo "Invalid";
        die;
    }
    ?>
    <h1 id='title'>Student Project Showcase</h1>

    <div style='width: 80%; margin:auto;'>
        <span class='custom-button' onclick="window.history.back()">Back</span>


        <br><br>
        <span class='title'><?php echo $full_name ?></span>

        <table style="width:100%">

            <tr>
                <td width="60%">
                    <div class='panel'><img style='width: 200px;' src="<?php echo $path_to_portrait ?>">
                        <span class='subheading'>About me</span>
                        <br>
                        <?php echo $bio ?>
                    </div>
                </td>

                <?php

                //keep data for each project
                $titles = array();
                $project_ids = array();
                $descs = array();

                //list projects
                $sql = "SELECT title, projects.project_id, path_to_description FROM projects INNER JOIN students ON projects.student_id=students.student_id AND projects.student_id={$id} AND private=0";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $desc = "";
                        $path_to_desc = htmlspecialchars($row['path_to_description']);
                        if (isset($row['path_to_bio']) && file_exists($path_to_desc)) {
                            $fh = fopen($path_to_desc, 'r');
                            while ($line = fgets($fh)) {
                                $desc .= $line;
                            }
                            fclose($fh);
                        } else {
                            $desc = "Decription unavailable.";
                        }

                        array_push($descs, $desc);

                        array_push($titles, htmlspecialchars($row['title']));
                        array_push($project_ids, htmlspecialchars($row['project_id']));
                    }
                }


                ?>


                <td width="40%">
                    <div class='panel'>
                        <?php if (!empty($project_ids[0])) { ?>

                            <div class='subheading'><a href="project.php?id=<?php echo $project_ids[0] ?>"><?php echo $titles[0] ?></a></div>
                            <img src='data/home/essay.png' style='width:200px'>
                            <div> <?php echo $descs[0] ?></div>

                        <?php
                            } else { echo "<span class='subheading'> Featured project 1 </span>"; }
                       ?>

                    </div>
                </td>

            </tr>
            <tr>
                <td>
                    <div class='panel'>
                        <?php if (!empty($project_ids[1])) { ?>
                            <div class='subheading'><a href="project.php?id=<?php echo $project_ids[1] ?>"><?php echo $titles[1] ?></a></div>
                            <img src='data/home/essay.png' style='width:200px'>
                            <div> <?php echo $descs[1] ?></div>
                        <?php
                            } else { echo "<span class='subheading'> Featured project 2 </span>"; }
                        ?>
                    </div>
                </td>
                <td>
                    <div class='panel' style="height: 100%;">
                        <span class='subheading'>Other projects by <?php echo $full_name ?></span>

                        <?php
                        for ($i = 2; $i < count($project_ids) - 2; $i++) {
                            if (!empty($project_ids[$i])) {
                        ?>
                                <div class='search-result' onclick="window.location.href='project.php?id=<?php echo $project_ids[$i]?>'"><?php echo $titles[$i] ?></div>
                        <?php
                            }
                        }

                        ?>


                    </div>
                </td>

            </tr>
        </table>

        <input class='search-bar' type="text" placeholder="Search...">
        <span class='custom-button' onclick="location.href ='search.html';"> <i class="fa fa-search"></i> </span>
    </div>
</body>

</html>