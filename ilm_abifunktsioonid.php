<?php
require_once "conf2.php";
function saaIlmaAndmed($yhendus, $sort_by = null, $sort_dir = "ASC", $search = []) {
    $sql = "SELECT ilmatemperatuurid.id, temperatuur, kuupaev_kellaaeg, maakonnad.maakonnanimi, maakonnad.maakonnakeskus 
            FROM ilmatemperatuurid 
            LEFT JOIN maakonnad ON ilmatemperatuurid.maakond_id = maakonnad.id";
    $where = [];
    $params = [];
    $types = "";

    // Поиск по температуре
    if (!empty($search['temperatuur'])) {
        $where[] = "temperatuur = ?";
        $params[] = $search['temperatuur'];
        $types .= "d";
    }
    // Поиск по дате
    if (!empty($search['kuupaev'])) {
        $where[] = "DATE(kuupaev_kellaaeg) = ?";
        $params[] = $search['kuupaev'];
        $types .= "s";
    }
    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    // Сортировка
    $allowed = ["temperatuur", "kuupaev_kellaaeg", "maakonnanimi", "maakonnakeskus"];
    if ($sort_by && in_array($sort_by, $allowed)) {
        $sql .= " ORDER BY $sort_by " . ($sort_dir === "DESC" ? "DESC" : "ASC");
    } else {
        $sql .= " ORDER BY kuupaev_kellaaeg DESC";
    }

    $stmt = $yhendus->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// Получить список всех регионов
function saaMaakonnad($yhendus) {
    $sql = "SELECT * FROM maakonnad";
    return $yhendus->query($sql);
}

// Добавить новый регион
function lisaMaakond($yhendus, $nimi, $keskus) {
    $stmt = $yhendus->prepare("INSERT INTO maakonnad (maakonnanimi, maakonnakeskus) VALUES (?, ?)");
    $stmt->bind_param("ss", $nimi, $keskus);
    return $stmt->execute();
}

// Добавить температуру
function lisaIlmatemperatuur($yhendus, $temperatuur, $kuupaev_kellaaeg, $maakond_id) {
    $stmt = $yhendus->prepare("INSERT INTO ilmatemperatuurid (temperatuur, kuupaev_kellaaeg, maakond_id) VALUES (?, ?, ?)");
    $stmt->bind_param("dsi", $temperatuur, $kuupaev_kellaaeg, $maakond_id);
    return $stmt->execute();
}
?>
