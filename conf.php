<?php
$yhendus = new mysqli("localhost", "burdyga", "23051982", "kaupade_andmebaas");
if ($yhendus->connect_error) {
    die("Andmebaasi ühenduse viga: " . $yhendus->connect_error);
}
?>
