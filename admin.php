<?php
session_start();
if (!isset($_SESSION['tuvastamine'])) {
    header('Location: login.php');
    exit();
}
?>

<h1>TERE ADMIN</h1>
<p>See leht on ainult sisseloginud kasutajale.</p>
<form action="logout.php" method="post">
    <input type="submit" value="Logi välja" name="logout">
</form>
