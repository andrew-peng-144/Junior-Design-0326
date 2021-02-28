<?php
//admin login page

error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(isset($_SESSION["administrator"]) && $_SESSION["administrator"] === true){
    header("location: ../index.php");
    exit;
}


$entered_username = $entered_password = "";
$username_err = $password_err = "";
$general_err = ""; //an error happened (either wrong username or wrong password) but purposefully vague. will show under login button
define("INVALID","Invalid");

// when form is submitted, this whole script will re-run, but will execute below as well:
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // check if username is empty
    if(trim($_POST["username"]) == false){
        $username_err = "Please enter username";
    } else{
        $entered_username = $_POST["username"];
    }
    
    // check if password is empty:
    if(trim($_POST["password"]) == false){
        $password_err = "Please enter password";
    } else{
        $entered_password = $_POST["password"];
    }
    
    //if they did enter a username and password:
    if(empty($username_err) && empty($password_err)){
        //THEN, access the database.
        require_once "../mysql-connect.php";

        //prepared statement
        $sql = "SELECT id, username, password FROM admins WHERE username = ?";
        //TODO TODO TODO OOOOOOOOOOOOOOOOOO
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $param_username);
            $param_username = $entered_username;
            
            if($stmt->execute()){
                $stmt->store_result(); //buffers the result of the query, and stmt->fetch puts the results into the binded variables
                
                //if the username exists in the table
                if($stmt->num_rows() == 1){
                    $stmt->bind_result($id, $entered_username, $hashed_password);

                    if($stmt->fetch()){
                        if(password_verify($entered_password, $hashed_password)){
                            //Correct password
                            
                            // Store data in session variables
                            $_SESSION["administrator"] = true;                      
                            
                            header("location: ../index.php");
                        } else{
                            //Not using $password_err = "The password you entered was not valid.";
                            $general_err = "Invalid Login";
                        }
                    }
                } else{
                    //Not using $username_err = "No account found with that username.";
                    $general_err = "Invalid Login";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close prepared statement
           $stmt->close();
        }
        
        $conn->close();
        
    //(Using mysqli::close() isn't usually necessary,
    //as non-persistent open links are automatically closed at the end of the script's execution.)
    }
    
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style type="text/css">
        body{ font: 14px sans-serif; }

    </style>
</head>
<body>
    <h2>Login</h2>
    <p>If you are not an administrator, you are not supposed to be on this page. Click <a href="index.html">here</a> to go back.</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="">
            <label>Username</label>
            <input type="text" name="username" class="" value="<?php echo $entered_username; ?>">
        </div>    
        <div class="">
            <label>Password</label>
            <input type="password" name="password" class="">
        </div>
        <div class="">
            <input type="submit" class="" value="Login">
        </div>
        <div><?php echo $general_err; ?></div>
    </form>
</body>
</html>