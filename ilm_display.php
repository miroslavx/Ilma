<?php
require_once "conf2.php"; // подключение параметров БД
require_once "ilm_abifunktsioonid.php";

// Соединение с базой
$yhendus = new mysqli($serverinimi, $kasutajanimi, $parool, $andmebaas);
if ($yhendus->connect_error) {
    die("Viga andmebaasi ühendamisel: " . $yhendus->connect_error);
}

// Обработка добавления
if (isset($_POST['lisa_maakond'])) {
    $nimi = $_POST['maakonnanimi'] ?? '';
    $keskus = $_POST['maakonnakeskus'] ?? '';
    if ($nimi && $keskus) {
        lisaMaakond($yhendus, $nimi, $keskus);
    }
}
if (isset($_POST['lisa_ilmatemp'])) {
    $temperatuur = floatval($_POST['temperatuur']);
    $kuupaev = $_POST['kuupaev_kellaaeg'];
    $maakond_id = intval($_POST['maakond_id']);
    if ($kuupaev && $maakond_id) {
        lisaIlmatemperatuur($yhendus, $temperatuur, $kuupaev, $maakond_id);
    }
}

// Поиск и сортировка
$search = [];
if (!empty($_GET['search_temperatuur'])) {
    $search['temperatuur'] = $_GET['search_temperatuur'];
}
if (!empty($_GET['search_kuupaev'])) {
    $search['kuupaev'] = $_GET['search_kuupaev'];
}
$sort_by = $_GET['sort_by'] ?? null;
$sort_dir = $_GET['sort_dir'] ?? "ASC";

// Получить данные
$ilmaandmed = saaIlmaAndmed($yhendus, $sort_by, $sort_dir, $search);
$maakonnad = saaMaakonnad($yhendus);

?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <title>Ilmatemperatuurid</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f7f8fa; }
        .container { display: flex; gap: 40px; }
        .table-wrap, .form-wrap { flex: 1; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { padding: 8px 10px; border: 1px solid #c6c6c6; text-align: left; }
        th { background: #e9ecef; cursor: pointer; }
        form { margin-bottom: 20px; }
        input, select { padding: 6px; border-radius: 4px; border: 1px solid #ccc; }
        .search-row input { width: 120px; }
        .form-title { margin-top: 0; }
    </style>
</head>
<body>
<h2>Ilmatemperatuurid ja maakonnad</h2>
<div class="container">
    <div class="table-wrap">
        <!-- Поиск -->
        <form method="get">
            <label>Temperatuur:
                <input type="number" step="0.1" name="search_temperatuur" value="<?= htmlspecialchars($_GET['search_temperatuur'] ?? '') ?>">
            </label>
            <label>Kuupäev:
                <input type="date" name="search_kuupaev" value="<?= htmlspecialchars($_GET['search_kuupaev'] ?? '') ?>">
            </label>
            <button type="submit">Otsi</button>
            <a href="ilm_display.php">Tühjenda</a>
        </form>

        <!-- Таблица с сортировкой -->
        <table>
            <tr>
                <?php
                $columns = [
                    'temperatuur' => 'Temperatuur',
                    'kuupaev_kellaaeg' => 'Kuupäev ja kellaaeg',
                    'maakonnanimi' => 'Maakond',
                    'maakonnakeskus' => 'Maakonna keskus'
                ];
                foreach ($columns as $key => $val) {
                    $dir = ($sort_by == $key && $sort_dir == "ASC") ? "DESC" : "ASC";
                    $arrow = ($sort_by == $key) ? ($sort_dir == "ASC" ? "↑" : "↓") : "";
                    echo "<th><a href='?sort_by=$key&sort_dir=$dir'>$val $arrow</a></th>";
                }
                ?>
            </tr>
            <?php while ($row = $ilmaandmed->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['temperatuur']) ?></td>
                    <td><?= htmlspecialchars($row['kuupaev_kellaaeg']) ?></td>
                    <td><?= htmlspecialchars($row['maakonnanimi']) ?></td>
                    <td><?= htmlspecialchars($row['maakonnakeskus']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <div class="form-wrap">
        <h3 class="form-title">Lisa maakond</h3>
        <form method="post">
            <label>Nimi: <input type="text" name="maakonnanimi" required></label><br>
            <label>Keskus: <input type="text" name="maakonnakeskus" required></label><br>
            <button type="submit" name="lisa_maakond">Lisa maakond</button>
        </form>

        <h3 class="form-title">Lisa ilmatemperatuur</h3>
        <form method="post">
            <label>Temperatuur (&deg;C): <input type="number" step="0.1" name="temperatuur" required></label><br>
            <label>Kuupäev ja kellaaeg: <input type="datetime-local" name="kuupaev_kellaaeg" required></label><br>
            <label>Maakond:
                <select name="maakond_id" required>
                    <?php foreach ($maakonnad as $mk): ?>
                        <option value="<?= $mk['id'] ?>"><?= htmlspecialchars($mk['maakonnanimi']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label><br>
            <button type="submit" name="lisa_ilmatemp">Lisa ilmatemperatuur</button>
        </form>
    </div>
</div>
</body>
</html>
