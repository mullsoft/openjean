<?php
//PUT THIS HEADER ON TOP OF EACH UNIQUE PAGE
//echo "here0aa\n";
//$directory = session_save_path();
//$filecount = count(glob($directory . "/*"));

//echo $directory." file count = ".$filecount;
session_start();
//echo "here0b\n";
if (!isset($_SESSION['username'])) {
//echo "here0c\n";
    header("location:login/main_login.php");
}
