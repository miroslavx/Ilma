<?php
$db_host = "localhost";
$db_user = "burdyga";
$db_pass = "23051982";
$db_name = "ilmadb";

$yhendus = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($yhendus->connect_error) {
    die("Connection failed: " . $yhendus->connect_error);
}

$yhendus->set_charset("UTF8");
?>