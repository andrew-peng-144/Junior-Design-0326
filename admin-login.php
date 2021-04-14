<?php
//admin login page

error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//If already logged in as admin, just redirect to homepage.
if (isset($_SESSION["administrator"]) && $_SESSION["administrator"] === true) {
    header("location: index.php");
    exit;
}


$entered_username = $entered_password = "";
$general_err = ""; //an error happened (either wrong username or wrong password) but purposefully vague. will show under login button

// when form is submitted, this whole script will re-run, but will execute below as well:
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //if username and password fields are both non-empty:
    if (
        !empty(trim(htmlspecialchars($_POST["username"])))
        && !empty(htmlspecialchars($_POST["password"]))
    ) {
        $entered_username = htmlspecialchars($_POST["username"]);
        $entered_password = htmlspecialchars($_POST["password"]);
        //login to the database as view-only user.
        require_once "mysql-connect.php";

        //prepared statement to select the admin user
        $sql = "SELECT id, username, password FROM admins WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $entered_username);
            if ($stmt->execute()) {
                $result = $stmt->get_result();

                if ($result->num_rows == 1) { //should only have 1 record returned.
                    while ($row = $result->fetch_assoc()) {
                        if (password_verify($entered_password, $row['password'])) {
                            //Correct password

                            // Store a boolean as session variable called "administrator". This is how an administrator will be tracked.
                            $_SESSION["administrator"] = true;
                            header("location: index.php");
                        } else {
                            //Wrong password
                            $general_err = "Invalid Login";
                        }
                    }
                } else {
                    //Wrong username (that username wasn't in the table)
                    $general_err = "Invalid Login";
                }
            } else {
                //SELECT query failed somehow
                $general_err = "Something went wrong. Please try again later"; 
            }
            // Close prepared statement
            $stmt->close();
        } else {
            //failed to prepare the statement somehow
            $general_err = "Oops! Something went wrong."; 
        }

        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>


<head>
    <title> CGA Administrator Login</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/admin-login-stylesheet.css">
</head>

<body>
    <?php
    include 'get-topnav.php';
    ?>
    <div class="loginBox">
        <header>
            <div class="cgaLogo">
                <center><img src="data/home/cgalogo.png"></center>
            </div>
            <p class="loginHead" align="center">Administrator Login</p>
        </header>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class=login-form>
            <div class="textField">
                <input class="userName" type="text" name="username" placeholder="Username" value="<?php echo $entered_username; ?>" required>
            </div>
            <div class="textField">
                <input class="password" type="password" name="password" placeholder="Password" required>
            </div>
            <hr class="hrline">
            <div class="button">
                <input class="loginButton" type="submit" name="submit" value="Log In">
            </div>
        </form>
        <div>
            <?php echo $general_err; ?>
        </div>
    </div>

    <?php
    include "get-footer.php";
    ?>
</body>

</html>