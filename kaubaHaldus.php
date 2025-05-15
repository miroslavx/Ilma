<?php
require('conf.php');
require("abifunktsioonid.php");
session_start();
if (!isset($_SESSION['tuvastamine'])) {
    header('Location: login.php');
    exit();
}

if(isset($_REQUEST["grupilisamine"]) && !empty(trim($_REQUEST["uuegrupinimi"]))){
    if(grupinimiKontroll(trim($_REQUEST["uuegrupinimi"]))== 0){
        lisaGrupp(trim($_REQUEST["uuegrupinimi"]));
        header("Location: kaubaHaldus.php");
        exit();
    }
}
if(isset($_REQUEST["kaubalisamine"]) && !empty(trim($_REQUEST["nimetus"]))){
    lisaKaup(trim($_REQUEST["nimetus"]), $_REQUEST["kaubagrupi_id"], $_REQUEST["hind"]);
    header("Location: kaubaHaldus.php");
    exit();
}
if(isset($_REQUEST["kustutusid"])){
    kustutaKaup($_REQUEST["kustutusid"]);
}
if(isset($_REQUEST["muutmine"])){
    muudaKaup($_REQUEST["muudetudid"], $_REQUEST["nimetus"],
        $_REQUEST["kaubagrupi_id"], $_REQUEST["hind"]);
}
$kaubad=kysiKaupadeAndmed();
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <title>Kaupade leht</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
</head>
<body>
<form action="logout.php" method="post">
    <input type="submit" value="Logi välja" name="logout">
</form>

<h1>Kaubad | Kaubagrupid</h1>

<form action="kaubaHaldus.php">
    <h2>Kauba lisamine</h2>
    <dl>
        <dt>Nimetus:</dt>
        <dd><input type="text" name="nimetus" /></dd>
        <dt>Kaubagrupp:</dt>
        <dd><?php
            echo looRippMenyy("SELECT id, grupinimi FROM kaubagrupid", "kaubagrupi_id");
            ?></dd>
        <dt>Hind:</dt>
        <dd><input type="text" name="hind" /></dd>
    </dl>
    <input type="submit" name="kaubalisamine" value="Lisa kaup" />
    <h2>Grupi lisamine</h2>
    <input type="text" name="uuegrupinimi" />
    <input type="submit" name="grupilisamine" value="Lisa grupp" />
    <?php
    if(isset($_REQUEST["uuegrupinimi"])){
        if(grupinimiKontroll(trim($_REQUEST["uuegrupinimi"])) > 0){
            echo "Sisestatud grupinimi on juba olemas!";
        }
    }
    ?>
</form>

<form action="kaubaHaldus.php">
    <h2>Kaupade loetelu</h2>
    <table>
        <tr>
            <th>Haldus</th>
            <th>Nimetus</th>
            <th>Kaubagrupp</th>
            <th>Hind</th>
        </tr>
        <?php foreach($kaubad as $kaup): ?>
            <tr>
                <?php if(isset($_REQUEST["muutmisid"]) && intval($_REQUEST["muutmisid"])==$kaup->id): ?>
                    <td>
                        <input type="submit" name="muutmine" value="Muuda" />
                        <input type="submit" name="katkestus" value="Katkesta" />
                        <input type="hidden" name="muudetudid" value="<?=htmlspecialchars($kaup->id) ?>" />
                    </td>
                    <td><input type="text" name="nimetus" value="<?=htmlspecialchars($kaup->nimetus) ?>" /></td>
                    <td><?php
                        echo looRippMenyy("SELECT id, grupinimi FROM kaubagrupid", "kaubagrupi_id", $kaup->id);
                        ?></td>
                    <td><input type="text" name="hind" value="<?=htmlspecialchars($kaup->hind) ?>" /></td>
                <?php else: ?>
                    <td>
                        <a href="kaubaHaldus.php?kustutusid=<?=htmlspecialchars($kaup->id) ?>"
                           onclick="return confirm('Kas ikka soovid kustutada?')">x</a>
                        <a href="kaubaHaldus.php?muutmisid=<?=htmlspecialchars($kaup->id) ?>">m</a>
                    </td>
                    <td><?=htmlspecialchars($kaup->nimetus) ?></td>
                    <td><?=htmlspecialchars($kaup->grupinimi) ?></td>
                    <td><?=htmlspecialchars($kaup->hind) ?></td>
                <?php endif ?>
            </tr>
        <?php endforeach; ?>
    </table>
</form>
</body>
</html>
