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

<!DOCTYPE html>
<html>

<head>
	<title>Profile Page</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous"> -->
	<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous"> -->
	<link rel="stylesheet" href="./css/student.css">

</head>

<body>

	<?php
	include "get-topnav.php";

	//get full name, bio, path to portrait
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
	} else {
		echo "Invalid";
		die;
	}


	//keep data for each project
	$project_ids = array();
	$titles = array();
	$descs = array();
	$paths_to_ci = array();

	//list projects made by this student
	$sql = "SELECT title, projects.project_id, path_to_description, path_to_cover_image FROM projects INNER JOIN students ON projects.student_id=students.student_id AND projects.student_id={$id} AND private=0";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {

			$desc = "";
			$path_to_desc = htmlspecialchars($row['path_to_description']);
			if (isset($row['path_to_description']) && file_exists($path_to_desc)) {
				$fh = fopen($path_to_desc, 'r');
				while ($line = fgets($fh)) {
					$desc .= $line;
				}
				fclose($fh);
			} else {
				$desc = "Decription unavailable.";
			}

			array_push($project_ids, htmlspecialchars($row['project_id']));
			array_push($titles, htmlspecialchars($row['title']));
			array_push($descs, $desc);
			array_push($paths_to_ci, htmlspecialchars($row['path_to_cover_image']));
		}
	}

	?>

	<br><br>
	<div id="profile">
		<div id="">
			<img id="Profile_Picture" src="<?php echo $path_to_portrait; ?>">
			<h6 id="Username"> <?php echo $full_name; ?>'s Profile

				<?php
				//if admin, have an edit button
				if ($admin == TRUE) {
				?>
					<span id="edit-button" onclick="window.location.href='admin-edit-student.php?id=<?php echo $_GET['id'] ?>'">
						Edit
						<img src="data/home/edit.png" style="width:20px"></span>
				<?php
				}
				?>

			</h6>
			<p id="User_Bio"><?php echo $bio ?></p>
		</div>

		<h6 style="font-size: 25px">My Projects:</h6>
		<div class="row row-cols-1 row-cols-3 g-4">

			<?php

			for ($i = 0; $i < count($project_ids); $i++) {
			?>
				<div class="col">
					<a href="project.php?id=<?php echo $project_ids[$i] ?>">
						<div class="card">
							<img src="<?php echo $paths_to_ci[$i] ?>" class="card-img-top" alt="...">
							<div class="card-body">
								<h5 class="card-title"><?php echo $titles[$i] ?></h5>
								<p class="card-text">
									<div> <?php echo $descs[$i] ?></div>
								</p>
							</div>
						</div>
					</a>
				</div>
			<?php
			}
			?>




		</div>

	</div>

	<?php
	include "get-footer.php";
	?>
</body>

</html>