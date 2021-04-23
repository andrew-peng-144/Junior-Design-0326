<?php

///////////// Change $password to a password that you want to hash.
$password = "passwordtesting123";
echo password_hash($password, PASSWORD_DEFAULT);

?>