<?php

//Create the 4 tables: "students",  "projects",  "project_files", and "admins"
//include("mysql-connect.php");


//root access
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cga_showcase";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


///////////////STUDENTS
$sql = "CREATE TABLE students (
    student_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    last_name VARCHAR(255),
    first_name VARCHAR(255),
    path_to_bio VARCHAR(255) NOT NULL,
    path_to_portrait VARCHAR(255),
    added DATETIME DEFAULT CURRENT_TIMESTAMP
)";
    
if ($conn->query($sql) === TRUE) {
    echo "Table students created successfully\n";
} else {
    echo "Error creating students table: " . $conn->error . ". ";
}

///////////////PROJECTS
$sql = "CREATE TABLE projects (
    project_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students (student_id),
    title VARCHAR(255),
    path_to_description VARCHAR(255),
    private TINYINT(1),
    featured TINYINT(1),
    path_to_cover_image VARCHAR(255),
    added DATETIME DEFAULT CURRENT_TIMESTAMP
)";
    
if ($conn->query($sql) === TRUE) {
    echo "Table projects created successfully\n";
} else {
    echo "Error creating projects table: " . $conn->error . ". ";
}
//////Project_files  (a project can have many downloadable files. Store the paths and ther filetypes here.)
$sql = "CREATE TABLE project_files (
    file_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    FOREIGN KEY (project_id)
        REFERENCES projects (project_id),
    path VARCHAR(255),
    file_type VARCHAR(20)
)";
    
if ($conn->query($sql) === TRUE) {
    echo "Table project_files created successfully\n";
} else {
    echo "Error creating project_files table: " . $conn->error . ". ";
}


///////////////ADMINS
$sql = "CREATE TABLE admins (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    added DATETIME DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table admins created successfully\n";
} else {
    echo "Error creating admins table: " . $conn->error . ". ";
}
?>