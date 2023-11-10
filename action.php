<?php
include_once 'includes/_db.php';
session_start();

// ADD
if (isset($_POST['task-name']) && isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token'] && strlen($_POST['task-name']) > 0) {
    $thisDate = new DateTime();
    $thisDate->setTimezone(new DateTimeZone('Europe/Paris'));
    $formattedDate = $thisDate->format("Y-m-d H:i:s");
    $query = $dbCo->prepare("INSERT INTO task(name, creation_date, state) VALUES (:name, :date, '0');");
    $query->execute([
        'name' => strip_tags($_POST['task-name']),
        'date' => $formattedDate
    ]);
    $_SESSION['msg'] = 4;
};

// DELETE
if (isset($_GET['action']) && $_GET['action'] === 'del' && isset($_GET['id'])) {
    $query = $dbCo->prepare("DELETE FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval(strip_tags($_GET['id']))
    ]);
    $_SESSION['msg'] = 2;
};

// DONE
if (isset($_GET['action']) && $_GET['action'] === 'done' && isset($_GET['id'])) {
    $query = $dbCo->prepare("UPDATE task SET state = true WHERE id_task = :id");
    $query->execute([
        'id' => intval(strip_tags($_GET['id']))
    ]);
    $_SESSION['msg'] = 1;
};

// MODIFY
if (isset($_POST['task-modify']) && isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token'] && isset($_POST['id'])) {
    $query = $dbCo->prepare("UPDATE task SET name = :modifyTask WHERE id_task = :id");
    $query->execute([
        'modifyTask' => strip_tags($_POST['task-modify']),
        'id' => intval(strip_tags($_POST['id']))
    ]);
    $_SESSION['msg'] = 3;
};

header('location: index.php');