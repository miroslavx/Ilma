<?php
include('config.php');
session_start();

if (!isset($_SESSION['tuvastamine'])) {
    header('Location: login.php');
    exit();
}

if (!empty($_POST['login']) && !empty($_POST['pass'])) {
    $login = htmlspecialchars(trim($_POST['login']));
    $pass = htmlspecialchars(trim($_POST['pass']));

    if (strlen($pass) < 5) {
        echo "Parool peab olema vähemalt 5 tähemärki";
    } else {
        $sool = 'taiestisuvalinetekst';
        $kryp = crypt($pass, $sool);

        $kontroll = "SELECT COUNT(*) AS kogus FROM kasutajad WHERE kasutaja='$login'";
        $tulemus = mysqli_query($yhendus, $kontroll);
        $rida = mysqli_fetch_assoc($tulemus);

        if ($rida['kogus'] > 0) {
            echo "Selline kasutaja on juba olemas!";
        } else {
            $lisa = "INSERT INTO kasutajad (kasutaja, parool) VALUES ('$login', '$kryp')";
            if (mysqli_query($yhendus, $lisa)) {
                echo "Kasutaja lisatud!";
            } else {
                echo "Midagi läks valesti!";
            }
        }
    }
}
?>

<h1>Registreeri uus kasutaja</h1>
<form action="" method="post">
    Login: <input type="text" name="login"><br>
    Password: <input type="password" name="pass"><br>
    <input type="submit" value="Registreeri">
</form>
