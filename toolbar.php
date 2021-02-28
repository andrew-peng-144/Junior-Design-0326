<?php
//To be placed at the top of most pages, using php include
//check if admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$admin = false;
if (isset($_SESSION["administrator"]) && $_SESSION["administrator"] === true) {
    $admin = true;
}
?>

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