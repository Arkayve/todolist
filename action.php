<?php
include_once 'includes/_db.php';
session_start();

// ADD
if (isset($_POST['task-name']) && isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token'] && strlen($_POST['task-name']) > 0) {
    $thisDate = new DateTime();
    $thisDate->setTimezone(new DateTimeZone('Europe/Paris'));
    $formattedDate = $thisDate->format("Y-m-d H:i:s");
    $query = $dbCo->prepare("SELECT COUNT(state) FROM task WHERE state = 0");
    $query->execute();
    $priority = $query->fetchColumn() + 1;
    $query = $dbCo->prepare("INSERT INTO task(name, creation_date, state, priority) VALUES (:name, :date, '0', :priority);");
    $query->execute([
        'name' => strip_tags($_POST['task-name']),
        'date' => $formattedDate,
        'priority' => $priority
    ]);
    $_SESSION['msg'] = 4;
}

// DELETE
else if (isset($_GET['action']) && $_GET['action'] === 'del' && isset($_GET['id'])) {
    $query = $dbCo->prepare("SELECT priority FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval(strip_tags($_GET['id']))
    ]);
    $priority = $query->fetchColumn();
    $query = $dbCo->prepare("DELETE FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval(strip_tags($_GET['id']))
    ]);
    $query = $dbCo->prepare("UPDATE task SET priority = priority - 1 WHERE state = 0 AND priority > :priority");
    $query->execute([
        'priority' => $priority
    ]);
    $_SESSION['msg'] = 2;
}

// DONE
else if (isset($_GET['action']) && $_GET['action'] === 'done' && isset($_GET['id'])) {
    $thisDate = new DateTime();
    $thisDate->setTimezone(new DateTimeZone('Europe/Paris'));
    $formattedDate = $thisDate->format("Y-m-d H:i:s");
    $query = $dbCo->prepare("SELECT priority FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval(strip_tags($_GET['id']))
    ]);
    $priority = $query->fetchColumn();
    $query = $dbCo->prepare("UPDATE task SET priority = priority - 1 WHERE state = 0 AND priority > :priority");
    $query->execute([
        'priority' => $priority
    ]);
    $query = $dbCo->prepare("UPDATE task SET done_date = :date, state = true, priority = 0 WHERE id_task = :id");
    $query->execute([
        'date' => $formattedDate,
        'id' => intval(strip_tags($_GET['id']))
    ]);
    $_SESSION['msg'] = 1;
}

// MODIFY
else if (isset($_POST['task-modify']) && isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token'] && isset($_POST['id'])) {
    $query = $dbCo->prepare("UPDATE task SET name = :modifyTask WHERE id_task = :id");
    $query->execute([
        'modifyTask' => strip_tags($_POST['task-modify']),
        'id' => intval(strip_tags($_POST['id']))
    ]);
    $_SESSION['msg'] = 3;
}

// MOVE
else if (isset($_GET['action']) && $_GET['action'] === 'up' && isset($_GET['id'])) {
    $query = $dbCo->prepare("SELECT priority FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval(strip_tags($_GET['id']))
    ]);
    $priority = $query->fetchColumn();
    $query = $dbCo->prepare("SELECT COUNT(state) FROM task WHERE state = 0");
    $query->execute();
    $maxPriority = $query->fetchColumn();
    if (intval($priority) === intval($maxPriority)) {
        $_SESSION['msg'] = 5;
        header('location: index.php');
    } else {
        $query = $dbCo->prepare("UPDATE task SET priority = :priority WHERE priority = :target");
        $query->execute([
            'priority' => $priority,
            'target' => $priority + 1
        ]);
        $priority ++;
        $query = $dbCo->prepare("UPDATE task SET priority = :priority WHERE id_task = :id");
        $query->execute([
            'priority' => $priority,
            'id' => intval(strip_tags($_GET['id']))
        ]);
    };
}
else if (isset($_GET['action']) && $_GET['action'] === 'down' && isset($_GET['id'])) {
    $query = $dbCo->prepare("SELECT priority FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval(strip_tags($_GET['id']))
    ]);
    $priority = $query->fetchColumn();
    if (intval($priority) === 1) {
        $_SESSION['msg'] = 5;
        header('location: index.php');
    } else {
        $query = $dbCo->prepare("UPDATE task SET priority = :priority WHERE priority = :target");
        $query->execute([
            'priority' => $priority,
            'target' => $priority - 1
        ]);
        $priority --;
        $query = $dbCo->prepare("UPDATE task SET priority = :priority WHERE id_task = :id");
        $query->execute([
            'priority' => $priority,
            'id' => intval(strip_tags($_GET['id']))
        ]);
    };
}

// ALARM
else if (isset($_POST['alarm']) && isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token'] && strlen($_POST['alarm']) > 0 && isset($_POST['id'])) {
    $query = $dbCo->prepare("UPDATE task SET alarm_date = :alarm WHERE id_task = :id");
    $query->execute([
        'alarm' => $_POST['alarm'],
        'id' => intval(strip_tags($_POST['id']))
    ]);
    $_SESSION['msg'] = 6;
}

// REMOVE ALARM
else if (isset($_POST['alarm-delete']) && isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token'] && isset($_POST['id'])) {
    $query = $dbCo->prepare("UPDATE task SET alarm_date = NULL WHERE id_task = :id");
    $query->execute([
        'id' => intval(strip_tags($_POST['id']))
    ]);
    $_SESSION['msg'] = 9;
}

// TAKE BACK A TASK
else if (isset($_POST['back']) && isset($_POST['id']) && isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token']) {
    $query = $dbCo->prepare("UPDATE task SET priority = priority + 1 WHERE state = 0");
    $query->execute();
    $query = $dbCo->prepare("UPDATE task SET state = 0, done_date = NULL, priority = 1 WHERE id_task = :id");
    $query->execute([
        'id' => intval(strip_tags($_POST['id']))
    ]);
    $_SESSION['msg'] = 4;
}

// ADD THEME
else if (isset($_POST['theme']) && isset($_POST['id']) && isset($_POST['id_theme']) && isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token']) {
    $query = $dbCo->prepare("SELECT * FROM category");
    $query->execute();
    $categories = $query->fetchAll();
    $filteredCategories = array_filter($categories, fn($category) => $category['id_task'] === $_POST['id'] && $category['id_theme'] === $_POST['id_theme']);
    if (!empty($filteredCategories)) {
        header('location: index.php');
    };
    $query = $dbCo->prepare("INSERT INTO category(id_task, id_theme) VALUES (:id, :id_theme)");
    $query->execute([
        'id' => intval(strip_tags($_POST['id'])),
        'id_theme' => intval(strip_tags($_POST['id_theme']))
    ]);
    $_SESSION['msg'] = 7;
}

// REMOVE THEME
else if (isset($_POST['remove-theme']) && isset($_POST['id']) && isset($_POST['id_theme']) && isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token']) {
    $query = $dbCo->prepare("DELETE FROM category WHERE id_task = :id AND id_theme = :id_theme");
    $query->execute([
        'id' => intval(strip_tags($_POST['id'])),
        'id_theme' => intval(strip_tags($_POST['id_theme']))
    ]);
    $_SESSION['msg'] = 8;
};

header('location: index.php');