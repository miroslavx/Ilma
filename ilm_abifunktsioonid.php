<?php
require_once('conf.php');
function looRippMenyy($sqllause, $valikunimi, $valitudid = "") {
    global $yhendus;
    $kask = $yhendus->prepare($sqllause);
    $kask->bind_result($id, $sisu);
    $kask->execute();
    $tulemus = "<select name='$valikunimi'>";
    $tulemus .= "<option value=''>Vali...</option>";
    while ($kask->fetch()) {
        $lisand = "";
        if ($id == $valitudid) {
            $lisand = " selected='selected'";
        }
        $tulemus .= "<option value='$id'$lisand>" . htmlspecialchars($sisu) . "</option>";
    }
    $tulemus .= "</select>";
    $kask->close();
    return $tulemus;
}

// Функция для получения данных о погоде с названиями уездов, сортировкой и поиском
function kysiIlmaAndmedKoosNimedega($sorttulp = "i.kuupaev_kellaaeg", $sort_dir = "DESC", $otsi_kuupaev = "", $otsi_temp = "") {
    global $yhendus;
    $lubatudtulbad = array("i.temperatuur", "i.kuupaev_kellaaeg", "m.maakonnanimi", "m.maakonnakeskus");
    if (!in_array($sorttulp, $lubatudtulbad)) {
        $sorttulp = "i.kuupaev_kellaaeg"; // Сортировка по умолчанию
    }
    if (strtoupper($sort_dir) !== "ASC" && strtoupper($sort_dir) !== "DESC") {
        $sort_dir = "DESC"; // Направление сортировки по умолчанию
    }

    $sql_base = "SELECT i.id, i.temperatuur, 
                        DATE_FORMAT(i.kuupaev_kellaaeg, '%Y-%m-%d %H:%i') as kuupaev_kellaaeg_formatted, 
                        m.maakonnanimi, m.maakonnakeskus
                 FROM ilmatemperatuurid i
                 LEFT JOIN maakonnad m ON i.maakond_id = m.id";

    $tingimused = array();
    $params_values = array();
    $types = "";

    // Поиск по дате (только дата, без времени, для простоты примера)
    if (!empty(trim($otsi_kuupaev))) {
        // Пробуем преобразовать в формат YYYY-MM-DD для поиска
        $date_obj = date_create(trim($otsi_kuupaev));
        if ($date_obj) {
            $tingimused[] = "DATE(i.kuupaev_kellaaeg) = ?";
            $params_values[] = date_format($date_obj, 'Y-m-d');
            $types .= "s";
        }
    }
    // Поиск по температуре
    if (!empty(trim($otsi_temp)) && is_numeric(trim($otsi_temp))) {
        $tingimused[] = "i.temperatuur = ?";
        $params_values[] = (float)trim($otsi_temp);
        $types .= "d";
    }

    if (count($tingimused) > 0) {
        $sql_base .= " WHERE " . implode(" AND ", $tingimused);
    }
    $sql_base .= " ORDER BY $sorttulp $sort_dir";

    $kask = $yhendus->prepare($sql_base);
    if ($yhendus->error) { // Проверка на ошибки SQL
        echo "SQL Error in kysiIlmaAndmedKoosNimedega: " . $yhendus->error;
        return array();
    }

    if (count($params_values) > 0) {
        // Cвязывание параметров
        $bind_params_with_types = array_merge(array($types), $params_values);
        $ref_params = array();
        foreach($bind_params_with_types as $key => $value) {
            $ref_params[$key] = &$bind_params_with_types[$key];
        }
        call_user_func_array(array($kask, 'bind_param'), $ref_params);
    }

    $kask->execute();
    $kask->bind_result($id, $temperatuur, $kuupaev_kellaaeg_formatted, $maakonnanimi, $maakonnakeskus);

    $hoidla = array();
    while ($kask->fetch()) {
        $rida = new stdClass();
        $rida->id = $id;
        $rida->temperatuur = $temperatuur;
        $rida->kuupaev_kellaaeg_formatted = $kuupaev_kellaaeg_formatted;
        $rida->maakonnanimi = htmlspecialchars($maakonnanimi , 'N/A'); // Обработка NULL от LEFT JOIN
        $rida->maakonnakeskus = htmlspecialchars($maakonnakeskus ,'N/A');
        array_push($hoidla, $rida);
    }
    $kask->close();
    return $hoidla;
}

// Функция для проверки, существует ли уезд с таким названием (аналог grupinimiKontroll)
function maakonnanimiKontroll($maakonnanimi) {
    global $yhendus;
    $maakonnanimi_trimmed = trim($maakonnanimi);
    if (empty($maakonnanimi_trimmed)) return 0; // Пустое имя не ищем

    $kask = $yhendus->prepare("SELECT id FROM maakonnad WHERE maakonnanimi = ?");
    $kask->bind_param("s", $maakonnanimi_trimmed);
    $kask->execute();
    $kask->store_result();
    $rida = $kask->num_rows;
    $kask->close();
    return $rida; // Возвращает количество, > 0 значит существует
}

// Функция для добавления нового уезда (аналог lisaGrupp)
function lisaMaakond($maakonnanimi, $maakonnakeskus) {
    global $yhendus;
    $maakonnanimi_trimmed = trim($maakonnanimi);
    $maakonnakeskus_trimmed = trim($maakonnakeskus);

    if (empty($maakonnanimi_trimmed)) return; // Не добавляем, если имя уезда пустое

    $kask = $yhendus->prepare("INSERT INTO maakonnad (maakonnanimi, maakonnakeskus) VALUES (?, ?)");
    $kask->bind_param("ss", $maakonnanimi_trimmed, $maakonnakeskus_trimmed);
    $kask->execute();
    $kask->close();
}

// Функция для добавления данных о погоде (аналог lisaKaup)
function lisaIlmaAndmed($temperatuur, $kuupaev_kellaaeg_str, $maakond_id) {
    global $yhendus;


    $mysql_datetime = str_replace('T', ' ', trim($kuupaev_kellaaeg_str));
    $temp_float = floatval($temperatuur);
    $maakond_int = intval($maakond_id);

    if (empty($mysql_datetime) || $maakond_int == 0) return; // Простая проверка

    $kask = $yhendus->prepare("INSERT INTO ilmatemperatuurid (temperatuur, kuupaev_kellaaeg, maakond_id) VALUES (?, ?, ?)");
    $kask->bind_param("dsi", $temp_float, $mysql_datetime, $maakond_int);
    $kask->execute();
    $kask->close();
}

// Функция для получения данных о минусовых температурах
function kysiMiinuskraadiAndmed() {
    global $yhendus;
    $sql = "SELECT i.temperatuur, m.maakonnanimi
            FROM ilmatemperatuurid i
            JOIN maakonnad m ON i.maakond_id = m.id
            WHERE i.temperatuur < 0
            ORDER BY i.temperatuur ASC";
    $kask = $yhendus->prepare($sql);
    $kask->execute();
    $kask->bind_result($temperatuur, $maakonnanimi);
    $hoidla = array();
    while ($kask->fetch()) {
        $rida = new stdClass();
        $rida->temperatuur = $temperatuur;
        $rida->maakonnanimi = htmlspecialchars($maakonnanimi);
        array_push($hoidla, $rida);
    }
    $kask->close();
    return $hoidla;
}

// Функция для получения данных о погоде в Хаапсалу
function kysiHaapsaluAndmed() {
    global $yhendus;
    $sql = "SELECT i.temperatuur, DATE_FORMAT(i.kuupaev_kellaaeg, '%Y-%m-%d %H:%i') as kuupaev_kellaaeg_formatted
            FROM ilmatemperatuurid i
            JOIN maakonnad m ON i.maakond_id = m.id
            WHERE m.maakonnakeskus = 'Haapsalu'
            ORDER BY i.kuupaev_kellaaeg DESC";
    $kask = $yhendus->prepare($sql);
    $kask->execute();
    $kask->bind_result($temperatuur, $kuupaev_kellaaeg_formatted);
    $hoidla = array();
    while ($kask->fetch()) {
        $rida = new stdClass();
        $rida->temperatuur = $temperatuur;
        $rida->kuupaev_kellaaeg_formatted = $kuupaev_kellaaeg_formatted;
        array_push($hoidla, $rida);
    }
    $kask->close();
    return $hoidla;
}
?>