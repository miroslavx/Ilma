<?php
require("ilm_abifunktsioonid.php");

$message = ""; // Для сообщений пользователю
//Обработка добавления нового уезда
if (isset($_REQUEST["maakond_lisamine"]) && !empty(trim($_REQUEST["uus_maakonnanimi"]))) {
    $uus_maakonnanimi = trim($_REQUEST["uus_maakonnanimi"]);
    $uus_maakonnakeskus = trim($_REQUEST["uus_maakonnakeskus"]); // Может быть пустым

    if (maakonnanimiKontroll($uus_maakonnanimi) == 0) {
        lisaMaakond($uus_maakonnanimi, $uus_maakonnakeskus);
        // Перенаправляем, чтобы избежать повторной отправки формы при обновлении
        header("Location: ilm_display.php?message=" . urlencode("Maakond '$uus_maakonnanimi' lisatud!"));
        exit();
    } else {
        $message = "Sellise nimega maakond ('" . htmlspecialchars($uus_maakonnanimi) . "') on juba olemas!";
    }
}

//Обработка добавления данных о погоде
if (isset($_REQUEST["ilma_lisamine"])) {
    $temp = $_REQUEST["temperatuur_form"];
    $aeg = $_REQUEST["kuupaev_kellaaeg_form"];
    $mk_id = $_REQUEST["maakond_id_form"];

    if (!empty($temp) && !empty($aeg) && !empty($mk_id) && is_numeric($temp)) {
        lisaIlmaAndmed($temp, $aeg, $mk_id);
        header("Location: ilm_display.php?message=" . urlencode("Ilmaandmed lisatud!"));
        exit();
    } else {
        $message = "Viga ilmaandmete lisamisel: kõik väljad peavad olema täidetud ja temperatuur peab olema number.";
    }
}

// Получение параметров для сортировки и поиска для основной таблицы
$sort_by = $_REQUEST["sort"] ; 'i.kuupaev_kellaaeg';
$sort_dir = $_REQUEST["sort_dir"] ; 'DESC';
$search_date = $_REQUEST["search_kuupaev"] ; '';
$search_temp = $_REQUEST["search_temp"] ;
// Данные для основной таблицы
$ilmaAndmed = kysiIlmaAndmedKoosNimedega($sort_by, $sort_dir, $search_date, $search_temp);
$miinuskraadid = kysiMiinuskraadiAndmed();
$haapsaluAndmed = kysiHaapsaluAndmed();
if (isset($_REQUEST['message'])) {
    $message = htmlspecialchars($_REQUEST['message']);
}

// Функция для создания ссылок сортировки
function genereeriSordiLink($veerg, $kuvatav_nimi, $praegune_sort_veerg, $praegune_sort_suund, $otsingu_parameetrid) {
    $uus_sort_suund = ($praegune_sort_veerg == $veerg && $praegune_sort_suund == 'ASC') ? 'DESC' : 'ASC';
    $link = "ilm_display.php?sort=$veerg&sort_dir=$uus_sort_suund";
    if (!empty($otsingu_parameetrid['kuupaev'])) {
        $link .= "&search_kuupaev=" . urlencode($otsingu_parameetrid['kuupaev']);
    }
    if (!empty($otsingu_parameetrid['temp'])) {
        $link .= "&search_temp=" . urlencode($otsingu_parameetrid['temp']);
    }

    $noolemärk = "";
    if ($praegune_sort_veerg == $veerg) {
        $noolemärk = ($praegune_sort_suund == 'ASC') ? ' ↑' : ' ↓';
    }
    return "<a href='$link'>$kuvatav_nimi$noolemärk</a>";
}
$otsingu_params_lingile = ['kuupaev' => $search_date, 'temp' => $search_temp];

?>
<!DOCTYPE html>
<html lang="et">
<head>
    <title>Ilmaandmete Leht</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin-bottom: 20px; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        form { margin-bottom: 20px; padding: 10px; border: 1px solid #eee; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="number"], input[type="datetime-local"], select {
            margin-bottom: 10px; padding: 5px; width: 250px;
        }
        input[type="submit"] { padding: 8px 15px; }
        .message { padding: 10px; background-color: #e6ffe6; border: 1px solid #b3ffb3; margin-bottom:15px; }
        hr { margin: 30px 0; }
    </style>
</head>
<body>

<h1>Ilmaandmete Haldus</h1>

<?php if (!empty($message)): ?>
    <p class="message"><?php echo $message; ?></p>
<?php endif; ?>

<h2>Lisa Uus Maakond</h2>
<form action="ilm_display.php" method="POST">
    <div>
        <label for="uus_maakonnanimi">Maakonna nimi:</label>
        <input type="text" id="uus_maakonnanimi" name="uus_maakonnanimi" required>
    </div>
    <div>
        <label for="uus_maakonnakeskus">Maakonnakeskus:</label>
        <input type="text" id="uus_maakonnakeskus" name="uus_maakonnakeskus">
    </div>
    <input type="submit" name="maakond_lisamine" value="Lisa Maakond">
</form>
<?php
// Проверка на существование имени уезда (как в вашем примере с grupilisamine)
if (isset($_REQUEST["maakond_lisamine"]) && !empty(trim($_REQUEST["uus_maakonnanimi"]))) {
    if (maakonnanimiKontroll(trim($_REQUEST["uus_maakonnanimi"])) > 0 && empty($message) /* Не показываем если уже было сообщение об успешном добавлении */) {
        echo "<p style='color:red;'>Sisestatud maakonna nimi on juba olemas!</p>";
    }
}
?>

<hr>

<h2>Lisa Ilmaandmed</h2>
<form action="ilm_display.php" method="POST">
    <div>
        <label for="temperatuur_form">Temperatuur (°C):</label>
        <input type="number" step="0.1" id="temperatuur_form" name="temperatuur_form" required>
    </div>
    <div>
        <label for="kuupaev_kellaaeg_form">Kuupäev ja kellaaeg:</label>
        <input type="datetime-local" id="kuupaev_kellaaeg_form" name="kuupaev_kellaaeg_form" required>
    </div>
    <div>
        <label for="maakond_id_form">Maakond:</label>
        <?php echo looRippMenyy("SELECT id, maakonnanimi FROM maakonnad ORDER BY maakonnanimi", "maakond_id_form"); ?>
    </div>
    <input type="submit" name="ilma_lisamine" value="Lisa Ilmaandmed">
</form>

<hr>

<h2>Ilmaandmete Tabel</h2>
<form action="ilm_display.php" method="GET">
    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by); ?>">
    <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($sort_dir); ?>">

    Otsi kuupäeva järgi: <input type="text" name="search_kuupaev" value="<?php echo htmlspecialchars($search_date); ?>" placeholder="YYYY-MM-DD">
    Otsi temperatuuri järgi: <input type="number" step="0.1" name="search_temp" value="<?php echo htmlspecialchars($search_temp); ?>" placeholder="-2.5">
    <input type="submit" value="Otsi">
    <a href="ilm_display.php">Tühjenda otsing</a>
</form>

<?php if (!empty($ilmaAndmed)): ?>
    <table>
        <thead>
        <tr>
            <th><?php echo genereeriSordiLink('i.temperatuur', 'Temperatuur (°C)', $sort_by, $sort_dir, $otsingu_params_lingile); ?></th>
            <th><?php echo genereeriSordiLink('i.kuupaev_kellaaeg', 'Kuupäev/Kellaaeg', $sort_by, $sort_dir, $otsingu_params_lingile); ?></th>
            <th><?php echo genereeriSordiLink('m.maakonnanimi', 'Maakond', $sort_by, $sort_dir, $otsingu_params_lingile); ?></th>
            <th><?php echo genereeriSordiLink('m.maakonnakeskus', 'Maakonnakeskus', $sort_by, $sort_dir, $otsingu_params_lingile); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($ilmaAndmed as $andmeRida): ?>
            <tr>
                <td><?php echo $andmeRida->temperatuur; ?></td>
                <td><?php echo $andmeRida->kuupaev_kellaaeg_formatted; ?></td>
                <td><?php echo $andmeRida->maakonnanimi; ?></td>
                <td><?php echo $andmeRida->maakonnakeskus; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Andmed puuduvad või ei vasta otsingukriteeriumitele.</p>
<?php endif; ?>

<hr>

<h2>Miinuskraadid</h2>
<?php if (!empty($miinuskraadid)): ?>
    <table>
        <thead>
        <tr>
            <th>Temperatuur (°C)</th>
            <th>Maakond</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($miinuskraadid as $rida): ?>
            <tr>
                <td><?php echo $rida->temperatuur; ?></td>
                <td><?php echo $rida->maakonnanimi; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Miinuskraadidega andmeid ei leitud.</p>
<?php endif; ?>

<hr>

<h2>Ilmaandmed Haapsalus</h2>
<?php if (!empty($haapsaluAndmed)): ?>
    <table>
        <thead>
        <tr>
            <th>Temperatuur (°C)</th>
            <th>Kuupäev/Kellaaeg</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($haapsaluAndmed as $rida): ?>
            <tr>
                <td><?php echo $rida->temperatuur; ?></td>
                <td><?php echo $rida->kuupaev_kellaaeg_formatted; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Haapsalu kohta ilmaandmeid ei leitud.</p>
<?php endif; ?>

</body>
</html>