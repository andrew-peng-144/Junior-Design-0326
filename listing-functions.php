<?php
//Contains a function for displaying all students as a list of cards, and a function for displaying all projects as a list of cards.
//included by: list-projects.php, list-students.php, project.php

include 'card.php';

/**
* Outputs all student profiles in the database as <div>'s in HTML.
* @param mysqli $conn the mysqli object.
*/
function list_all_students($conn)
{

    $sql = "SELECT student_id, first_name, last_name, path_to_bio, path_to_portrait FROM students";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            card_display_profile($row['student_id'], $row['path_to_portrait'], $row['first_name'], $row['last_name'], $row['path_to_bio']);

        }
    }
}//End function list_all_students

/**
*
*Outputs all projects in the database as <div>'s in HTML.
*
* @param mysqli $conn the mysqli object.
*/
function list_all_projects($conn, $admin)
{
    if ($admin) {
        //only admin can see private projects
        $sql = "SELECT project_id, title, private, path_to_description, path_to_cover_image, students.first_name, students.last_name FROM projects "
            . "INNER JOIN students ON projects.student_id=students.student_id";
    } else {

        //normal user is the exact same query, except can't see private projects.
        $sql = "SELECT project_id, title, private, path_to_description, path_to_cover_image, students.first_name, students.last_name FROM projects "
            . "INNER JOIN students ON projects.student_id=students.student_id AND private=0";
    }

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            card_display_project($row['project_id'], $row['path_to_cover_image'], $row['title'], $row['first_name'], $row['last_name'], $row['path_to_description']);

        }
    }


}//End function list_all_projects

?>