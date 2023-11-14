<?php
require './vendor/autoload.php';
include_once './includes/_function.php';

// if (!isset($_REQUEST['action'])) {
//     $_SESSION['msg'] = 19;
//     header('location: index.php');
//     exit;
// };

include_once './includes/_db.php';

// Start user session
session_start();
getToken();

// Check for CSRF and redirect in case of invalid token or referer
// checkCSRF('index.php');

// Prevent XSS fault
checkXSS($_REQUEST);

// $_SERVER['REQUEST_METHOD'] === 'POST';

// ADD
if (isset($_REQUEST['task-name']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token'] && strlen($_REQUEST['task-name']) > 0) {
    $date = getActualDate();
    $query = $dbCo->prepare("SELECT COUNT(state) FROM task WHERE state = 0");
    $query->execute();
    $priority = $query->fetchColumn() + 1;
    $query = $dbCo->prepare("INSERT INTO task(name, creation_date, state, priority) VALUES (:name, :date, '0', :priority);");
    $query->execute([
        'name' => $_REQUEST['task-name'],
        'date' => $date,
        'priority' => $priority
    ]);
    $_SESSION['msg'] = 4;
}

// DELETE
else if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'del' && isset($_REQUEST['id'])) {
    $query = $dbCo->prepare("SELECT priority FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval($_REQUEST['id'])
    ]);
    $priority = $query->fetchColumn();
    $query = $dbCo->prepare("DELETE FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval($_REQUEST['id'])
    ]);
    $query = $dbCo->prepare("UPDATE task SET priority = priority - 1 WHERE state = 0 AND priority > :priority");
    $query->execute([
        'priority' => $priority
    ]);
    $_SESSION['msg'] = 2;
}

// DONE
else if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'done' && isset($_REQUEST['id'])) {
    $date = getActualDate();
    $query = $dbCo->prepare("SELECT priority FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval($_REQUEST['id'])
    ]);
    $priority = $query->fetchColumn();
    $query = $dbCo->prepare("UPDATE task SET priority = priority - 1 WHERE state = 0 AND priority > :priority");
    $query->execute([
        'priority' => $priority
    ]);
    $query = $dbCo->prepare("UPDATE task SET done_date = :date, state = true, priority = 0 WHERE id_task = :id");
    $query->execute([
        'date' => $date,
        'id' => intval($_REQUEST['id'])
    ]);
    $_SESSION['msg'] = 1;
}

// MODIFY
else if (isset($_REQUEST['task-modify']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token'] && isset($_REQUEST['id'])) {
    $query = $dbCo->prepare("UPDATE task SET name = :modifyTask WHERE id_task = :id");
    $query->execute([
        'modifyTask' => $_REQUEST['task-modify'],
        'id' => intval($_REQUEST['id'])
    ]);
    $_SESSION['msg'] = 3;
}

// MOVE
else if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'up' && isset($_REQUEST['id'])) {
    $query = $dbCo->prepare("SELECT priority FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval($_REQUEST['id'])
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
            'id' => intval($_REQUEST['id'])
        ]);
    };
}
else if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'down' && isset($_REQUEST['id'])) {
    $query = $dbCo->prepare("SELECT priority FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval($_REQUEST['id'])
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
            'id' => intval($_REQUEST['id'])
        ]);
    };
}

// ALARM
else if (isset($_REQUEST['alarm']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token'] && strlen($_REQUEST['alarm']) > 0 && isset($_REQUEST['id'])) {
    $query = $dbCo->prepare("UPDATE task SET alarm_date = :alarm WHERE id_task = :id");
    $query->execute([
        'alarm' => $_REQUEST['alarm'],
        'id' => intval($_REQUEST['id'])
    ]);
    $_SESSION['msg'] = 6;
}

// REMOVE ALARM
else if (isset($_REQUEST['alarm-delete']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token'] && isset($_REQUEST['id'])) {
    $query = $dbCo->prepare("UPDATE task SET alarm_date = NULL WHERE id_task = :id");
    $query->execute([
        'id' => intval($_REQUEST['id'])
    ]);
    $_SESSION['msg'] = 9;
}

// TAKE BACK A TASK
else if (isset($_REQUEST['back']) && isset($_REQUEST['id']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token']) {
    $query = $dbCo->prepare("UPDATE task SET priority = priority + 1 WHERE state = 0");
    $query->execute();
    $query = $dbCo->prepare("UPDATE task SET state = 0, done_date = NULL, priority = 1 WHERE id_task = :id");
    $query->execute([
        'id' => intval($_REQUEST['id'])
    ]);
    $_SESSION['msg'] = 4;
}

// ADD THEME
else if (isset($_REQUEST['theme']) && isset($_REQUEST['id']) && isset($_REQUEST['id_theme']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token']) {
    $query = $dbCo->prepare("SELECT * FROM category");
    $query->execute();
    $categories = $query->fetchAll();
    $filteredCategories = array_filter($categories, fn($category) => $category['id_task'] === $_REQUEST['id'] && $category['id_theme'] === $_REQUEST['id_theme']);
    if (!empty($filteredCategories)) {
        header('location: index.php');
    };
    $query = $dbCo->prepare("INSERT INTO category(id_task, id_theme) VALUES (:id, :id_theme)");
    $query->execute([
        'id' => intval($_REQUEST['id']),
        'id_theme' => intval($_REQUEST['id_theme'])
    ]);
    $_SESSION['msg'] = 7;
}

// REMOVE THEME
else if (isset($_REQUEST['remove-theme']) && isset($_REQUEST['id']) && isset($_REQUEST['id_theme']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token']) {
    $query = $dbCo->prepare("DELETE FROM category WHERE id_task = :id AND id_theme = :id_theme");
    $query->execute([
        'id' => intval($_REQUEST['id']),
        'id_theme' => intval($_REQUEST['id_theme'])
    ]);
    $_SESSION['msg'] = 8;
}

// COLOR
else if (isset($_REQUEST['color']) && isset($_REQUEST['id']) && isset($_REQUEST['id_color']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token']) {
    $query =$dbCo->prepare("SELECT id_color FROM task WHERE id_task = :id");
    $query->execute([
        'id' => intval($_REQUEST['id'])
    ]);
    $result = $query->fetch();
    if (isset($result['id_color']) && $result['id_color'] === $_REQUEST['id_color']) {
        $query = $dbCo->prepare("UPDATE task SET id_color = NULL WHERE id_task = :id");
        $query->execute([
            'id' => intval($_REQUEST['id'])
        ]);
        $_SESSION['msg'] = 11;
    }
    else {
        $query = $dbCo->prepare("UPDATE task SET id_color = :id_color WHERE id_task = :id");
        $query->execute([
            'id' => intval($_REQUEST['id']),
            'id_color' => intval($_REQUEST['id_color'])
        ]);
        $_SESSION['msg'] = 10;
    };
}

// MANAGE THEME
else if (isset($_REQUEST['theme_mod']) && isset($_REQUEST['id_theme']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token']) {
    if (isset($_REQUEST['theme-delete'])) {
        $query = $dbCo->prepare("DELETE FROM theme WHERE id_theme = :id_theme");
        $query->execute([
            'id_theme' => intval($_REQUEST['id_theme'])
        ]);
        $query = $dbCo->prepare("DELETE FROM category WHERE id_theme = :id_theme");
        $query->execute([
            'id_theme' => intval($_REQUEST['id_theme'])
        ]);
        $_SESSION['msg'] = 14;
    }
    else if (isset($_REQUEST['theme-valid']) && strlen($_REQUEST['theme_mod']) > 0) {
        $query = $dbCo->prepare("UPDATE theme SET name = :theme_mod WHERE id_theme = :id_theme");
        $query->execute([
            'theme_mod' => $_REQUEST['theme_mod'],
            'id_theme' => intval($_REQUEST['id_theme'])
        ]);
        $_SESSION['msg'] = 16;
    };
}
else if (isset($_REQUEST['theme_add']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token']) {
    $query = $dbCo->prepare("INSERT INTO theme(name) VALUES (:theme_add);");
    $query->execute([
        'theme_add' => $_REQUEST['theme_add']
    ]);
    $_SESSION['msg'] = 15;
}

// MANAGE COLOR
else if (isset($_REQUEST['color_name']) && isset($_REQUEST['id_color']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token']) {
    if (isset($_REQUEST['color-delete'])) {
        $query = $dbCo->prepare("UPDATE task SET id_color = NULL WHERE id_color = :id_color");
        $query->execute([
            'id_color' => intval($_REQUEST['id_color'])
        ]);
        $query = $dbCo->prepare("DELETE FROM color WHERE id_color = :id_color");
        $query->execute([
            'id_color' => intval($_REQUEST['id_color'])
        ]);
        $_SESSION['msg'] = 21;
    }
    else if (isset($_REQUEST['color-valid']) && strlen($_REQUEST['color_name']) > 0) {
        $query = $dbCo->prepare("UPDATE color SET name = :color_name, hex_value = :color_value WHERE id_color = :id_color");
        $query->execute([
            'color_name' => $_REQUEST['color_name'],
            'color_value' => $_REQUEST['color_value'],
            'id_color' => intval($_REQUEST['id_color'])
        ]);
        $_SESSION['msg'] = 22;
    };
}
else if (isset($_REQUEST['color_add']) && isset($_SESSION['token']) && isset($_REQUEST['token']) && $_SESSION['token'] === $_REQUEST['token']) {
    $query = $dbCo->prepare("INSERT INTO color(name, hex_value) VALUES (:color_name, :color_value);");
    $query->execute([
        'color_name' => $_REQUEST['color_add'],
        'color_value' => $_REQUEST['color_value']
    ]);
    $_SESSION['msg'] = 23;
}

// DISPLAY THEME
else if (isset($_REQUEST['theme'])) {
    if ($_REQUEST['theme'] === 'none') {
        unset($_SESSION['theme']);
    }
    else {
        $_SESSION['theme'] = $_REQUEST['theme'];
    };
};

header('location: index.php');
