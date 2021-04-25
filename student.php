<?php
//This page displays a specific student profile. Which student profile? The one whose ID is $_GET['id'].


if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

include 'card.php';

if (is_numeric($_GET['id'])) {

	//get full name, bio, path to portrait.. store those as global variables.
	require 'mysql-connect.php';
	$id = htmlspecialchars($_GET["id"]);
	$sql = "SELECT first_name, last_name, path_to_bio, path_to_portrait FROM students WHERE student_id={$id}";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			// Only 1 loop iteration
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
	echo "Invalid ID in URL";
	die;
}

/**
* Output the HTML of the edit button ONLY IF the user is an admin.
*/
function display_edit_button($admin)
{
	if ($admin == TRUE) {
		?>
			<span id="edit-button" onclick="window.location.href='admin-edit-project.php?id=<?php echo $_GET['id'] ?>'">
				Edit
				<img src="data/home/edit.png" style="width:20px"></span>
		<?php
	}
}

/**
* Searches for all projects made by this student in the database, then outputs the HTML for all the projects that this student made.
* @param string $full_name The student's full name
*/
function display_my_projects($conn, $full_name) {

	//keep data for each project as global variables too
	$project_ids = array();
	$titles = array();
	$paths_to_descs = array();
	$paths_to_ci = array();

	//select all projects made by this student
	$select_what = "title, projects.project_id, path_to_description, path_to_cover_image";
	$sql = "SELECT {$select_what} FROM projects INNER JOIN students ON projects.student_id=students.student_id AND projects.student_id={$_GET['id']} AND private=0";

	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {

			array_push($project_ids, htmlspecialchars($row['project_id']));
			array_push($titles, htmlspecialchars($row['title']));
			array_push($paths_to_descs, htmlspecialchars($row['path_to_description']));
			array_push($paths_to_ci, htmlspecialchars($row['path_to_cover_image']));
		}
	}

	for ($i = 0; $i < count($project_ids); $i++) {
		//For each project, display it as card
		card_display_project($project_ids[$i], $paths_to_ci[$i], $titles[$i], $full_name, "", $paths_to_descs[$i]);
	}
}


?>

<!DOCTYPE html>
<html>

<head>
	<title><?php echo $full_name?>'s Profile Page</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="./css/student.css">

</head>

<body>

	<?php
	include "get-topnav.php";

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
				display_my_projects($conn, $full_name);
			?>
		</div>

	</div>

	<?php
	include "get-footer.php";
	?>
</body>

</html>