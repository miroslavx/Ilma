<?php
include('conf.php');
session_start();

if (isset($_SESSION['tuvastamine'])) {
    header('Location: kaubaHaldus.php');
    exit();
}

if (!empty($_POST['login']) && !empty($_POST['pass'])) {
    $login = htmlspecialchars(trim($_POST['login']));
    $pass = htmlspecialchars(trim($_POST['pass']));
    $sool = 'taiestisuvalinetekst';
    $kryp = crypt($pass, $sool);

    $paring = "SELECT * FROM kasutajad WHERE kasutajanimi='$login' AND parool='$kryp'";
    $valjund = mysqli_query($yhendus, $paring);

    if (mysqli_num_rows($valjund) == 1) {
        $_SESSION['tuvastamine'] = 'jah';

        // Kui kasutaja on admin, suuna admin.php lehele
        if ($login === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: kaubaHaldus.php');
        }
        exit();
    } else {
        echo "Kasutajanimi või parool on vale.";
    }
}
?>

<h1>Logi sisse</h1>
<form action="" method="post">
    Kasutajanimi: <input type="text" name="login"><br>
    Parool: <input type="password" name="pass"><br>
    <input type="submit" value="Logi sisse">
</form>
