<?php
require './vendor/autoload.php';
include_once './includes/_db.php';
include_once './includes/_function.php';
session_start();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Ma to do list</title>
</head>

<body>

    <header>
        <nav id="mySidenav" class="sidenav">
            <a id="closeBtn" href="#" class="close">✖</a>
            <ul>
                <?php
                    $query = $dbCo->prepare("SELECT * FROM theme");
                    $query->execute();
                    $result = $query->fetchAll();
                    foreach ($result as $theme) {
                ?>
                    <li><a href="action.php?theme=<?= $theme['id_theme'] ?>"><?= $theme['name'] ?></a></li>
                <?php
                    };
                ?>
                <a href="action.php?theme=none">Toutes les tâches</a>
                <a href="?action=manage-theme">Gérer les catégories</a>
                <a href="?action=manage-color">Gérer les couleurs</a>
            </ul>
        </nav>
        <a href="#" id="openBtn" class="burger-icon">🍔</a>
        <h1>Ma to do list</h1>
        <?php
            $query = $dbCo->prepare("SELECT alarm_date FROM task WHERE alarm_date IS NOT NULL;");
            $query->execute();
            $result = $query->fetchAll();
            $date = substr(getActualDate(), 0, -9);
            foreach ($result as $alarmDate) {
                if ($date === substr($alarmDate['alarm_date'], 0, -9)) {
                        $msg = "Une tâche est notée pour aujourd'hui.";
                        echo "<script>alert('$msg');</script>";
                ?>
                    <p class="alert"><span>⚠</span><br>Une tâche est notée pour aujourd'hui.</p>
                <?php 
                };
            };
        ?>
        <a id="connexion-link" class="connexion-link" href="#">Connexion ⚡</a>
        <div id="connexion-container" class="connexion-container hidden">
            <form action="action.php" method="POST">
                <input class="task-name" type="text" name="username" value="Identifiant">
                <input class="task-name" type="text" name="password" value="Mot de passe">
                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                <input class="task-valid" type="submit" value="✔">
            </form>
            <a href="#">Créer un compte</a>
            <a href="#">Mot de passe oublié</a>
            <a class="arrow-back" href="index.php">🔙</a>
        </div>
    </header>

    <main>
        <?php
            // NOTIFS
            if (isset($_SESSION['msg'])) {
                $query = $dbCo->prepare("SELECT name FROM msg WHERE id_msg = :id");
                $query->execute([
                    'id' => intval(strip_tags($_SESSION['msg']))
                ]);
                unset($_SESSION['msg']);
                $result = $query->fetch();
        ?>
            <div id="notif" class="notif">
                <h3><?= $result['name'] ?></h3>
            </div>
        <?php
            };
        ?>
        <div class="task-list">
            <ul>
                <?php
                    // MANAGE THEME
                    if (isset($_GET['action']) && $_GET['action'] === 'manage-theme') {
                        $query = $dbCo->prepare("SELECT * FROM theme");
                        $query->execute();
                        $result = $query->fetchAll();
                        foreach ($result as $theme) {
                ?>
                    <form class="theme-container" action="action.php" method="POST">
                        <input class="task-valid delete" type="submit" name="theme-delete" value="❌">
                        <input class="task-name" type="text" name="theme_mod" value="<?= $theme['name'] ?>">
                        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                        <input type="hidden" name="id_theme" value="<?= $theme['id_theme'] ?>">
                        <input class="task-valid" type="submit" name="theme-valid" value="✔">
                    </form>
                <?php

                        };
                ?>
                        <div class="theme-utils">
                            <form class="theme-container" action="action.php" method="POST">
                                <input class="task-name" type="text" name="theme_add" placeholder="Nouvelle catégorie">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <input class="task-valid" type="submit" name="theme-valid" value="➕">
                            </form>
                            <a class="arrow-back" href="index.php">🔙</a>
                        </div>
                <?php
                    exit;
                    }
                    // MANAGE COLOR
                    if (isset($_GET['action']) && $_GET['action'] === 'manage-color') {
                        $query = $dbCo->prepare("SELECT * FROM color");
                        $query->execute();
                        $result = $query->fetchAll();
                        foreach ($result as $color) {
                ?>
                            <form class="theme-container" action="action.php" method="POST">
                                <input class="task-valid delete" type="submit" name="color-delete" value="❌">
                                <input class="task-name" type="text" name="color_name" value="<?= $color['name'] ?>">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <input type="hidden" name="id_color" value="<?= $color['id_color'] ?>">
                                <input type="color" name="color_value" value="<?= $color['hex_value'] ?>" />
                                <input class="task-valid" type="submit" name="color-valid" value="✔">
                            </form>
                <?php
                        };
                ?>
                        <div class="theme-utils">
                            <form class="theme-container" action="action.php" method="POST">
                                <input class="task-name" type="text" name="color_add" placeholder="Nouvelle couleur">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <input type="color" name="color_value" value="#ffffff" />
                                <input class="task-valid" type="submit" name="theme-valid" value="➕">
                            </form>
                            <a class="arrow-back" href="index.php">🔙</a>
                        </div>
                <?php
                        exit;
                    }
                    // DISPLAY
                    else if (isset($_SESSION['theme'])) {
                        $query = $dbCo->prepare("SELECT * FROM task JOIN category c1 USING(id_task) WHERE state = false AND :id_theme IN ( SELECT id_theme FROM category c2 WHERE c2.id_theme = c1.id_theme ) ORDER BY priority DESC;");
                        $query->execute([
                            'id_theme' => intval(strip_tags($_SESSION['theme']))
                        ]);
                        $result = $query->fetchAll();
                    } else {
                        $query = $dbCo->prepare("SELECT * FROM task WHERE state = false ORDER BY priority DESC;");
                        $query->execute();
                        $result = $query->fetchAll();
                    };
                    foreach ($result as $task) {
                        if (isset($task['id_color'])) {
                            $query = $dbCo->prepare("SELECT hex_value FROM color WHERE id_color = :id_color");
                            $query->execute([
                                'id_color' => intval(strip_tags($task['id_color']))
                            ]);
                            $color = $query->fetch();
                ?>
                    <div class="task-container" style="background-color: <?= $color['hex_value'] ?>">
                <?php
                        }
                        else {
                ?>
                    <div class="task-container">
                <?php
                        };
                            // MODIFY
                            if (isset($_GET['action']) && $_GET['action'] === 'mod' && isset($_GET['id']) && $task['id_task'] === $_GET['id']) {
                                $query = $dbCo->prepare("SELECT name FROM task WHERE id_task = :id");
                                $query->execute([
                                    'id' => intval(strip_tags($_GET['id']))
                                ]);
                                $result = $query->fetch();
                        ?>
                            <form action="action.php" method="POST">
                                <a class="arrow-back" href="index.php">🔙</a>
                                <input class="task-name" type="text" name="task-modify" value="<?= $result['name'] ?>">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                                <input class="task-valid" type="submit" value="✔">
                            </form>
                    </div>
                        <?php
                            }
                            // ALARM
                            else if (isset($_GET['action']) && $_GET['action'] === 'alarm' && isset($_GET['id']) && $task['id_task'] === $_GET['id']) {
                                $date = substr(getActualDate(), 0, -3);
                        ?>
                            <div class="task-alarm">
                        <?php
                            if (isset($task['alarm_date'])) {
                        ?>
                            <a class="arrow-back" href="index.php">🔙</a>
                            <form action="action.php" method="POST">
                                <input class="task-name" type="submit" name="alarm-delete" value="❌ Supprimer l'alarme">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                            </form>
                            </div>
                    </div>
                        <?php
                            } else {
                        ?>    
                                <a class="arrow-back" href="index.php">🔙</a>
                                <form action="action.php" method="POST">
                                    <input class="date" type="datetime-local" name="alarm" value="<?= $date ?>" min="<?= $date ?>" max="">
                                    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                    <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                                    <input class="task-valid" class="bg-blue" type="submit" value="➕">
                                </form>
                            </div>
                    </div>
                        <?php
                            };
                            }
                            // THEME
                            else if (isset($_GET['action']) && $_GET['action'] === 'theme' && isset($_GET['id']) && $task['id_task'] === $_GET['id']) {
                                $query = $dbCo->prepare("SELECT * FROM theme");
                                $query->execute();
                                $result = $query->fetchAll();
                                $query = $dbCo->prepare("SELECT * FROM category");
                                $query->execute();
                                $categories = $query->fetchAll();
                        ?>
                            <div class="theme">
                                <a class="arrow-back" href="index.php">🔙</a>
                        <?php
                            foreach ($result as $theme) {
                                $filteredCategories = array_filter($categories, fn($category) => $category['id_task'] === $_GET['id'] && $category['id_theme'] === $theme['id_theme']);
                                if (!empty($filteredCategories)) {
                        ?>
                            <form action="action.php" method="POST">
                                <input class="theme-name bg-blue" type="submit" name="remove-theme" value="<?= $theme['name'] ?>">
                                <input type="hidden" name="id_theme" value="<?= $theme['id_theme'] ?>">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                            </form>
                        <?php
                                } else {
                        ?>
                            <form action="action.php" method="POST">
                                <input class="theme-name" type="submit" name="theme" value="<?= $theme['name'] ?>">
                                <input type="hidden" name="id_theme" value="<?= $theme['id_theme'] ?>">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                            </form>
                        <?php
                                };
                            };
                        ?>
                            </div>
                    </div>
                        <?php
                            }
                            // COLOR
                            else if (isset($_GET['action']) && $_GET['action'] === 'color' && isset($_GET['id']) && $task['id_task'] === $_GET['id']) {
                                $query = $dbCo->prepare("SELECT * FROM color");
                                $query->execute();
                                $result = $query->fetchAll();
                        ?>
                            <div class="theme">
                                <a class="arrow-back" href="index.php">🔙</a>
                        <?php
                            foreach ($result as $color) {
                        ?>
                            <form action="action.php" method="POST">
                                <input style="background-color: <?= $color['hex_value'] ?>" class="theme-name" type="submit" name="color" value="<?= $color['name'] ?>">
                                <input type="hidden" name="id_color" value="<?= $color['id_color'] ?>">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                            </form>
                        <?php
                            };
                        ?>
                            </div>
                    </div>
                        <?php
                            }              
                            else {
                        ?>
                            <li id=<?= $task['id_task'] ?> class="task">
                                <div>
                                    <h2><?= $task['name'] ?></h2>
                                    <time class="alarm" datetime="<?= $task['alarm_date'] ?>">
                                        <?php
                                            echo substr($task['alarm_date'], 0, -3);
                                            $date = substr(getActualDate(), 0, -9);
                                            if ($task['alarm_date'] <> NULL && $date === substr($task['alarm_date'], 0, -9)) echo '🚩';
                                        ?>
                                    </time>
                                </div>
                                <div>
                                <?php 
                                    $query = $dbCo->prepare("SELECT * FROM category");
                                    $query->execute();
                                    $categories = $query->fetchAll();
                                    foreach ($categories as $category) {
                                        if ($task['id_task'] === $category['id_task']) {
                                            $query = $dbCo->prepare("SELECT name FROM theme WHERE id_theme = :id_theme");
                                            $query->execute([
                                                'id_theme' => $category['id_theme']
                                            ]);
                                            $result = $query->fetch();
                                ?>
                                    <form action="action.php" method="POST">
                                        <input class="task-theme" type="submit" name="remove-theme" value="<?= $result['name'] ?>">
                                        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                        <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                                        <input type="hidden" name="id_theme" value="<?= $category['id_theme'] ?>">
                                    </form>
                                <?php                                      
                                        };
                                    };
                                ?>
                                </div>
                                <ul class="task-utils">
                                    <li><a href="?id=<?= $task['id_task'] ?>&action=color">🎨</a></li>
                                    <li><a href="?id=<?= $task['id_task'] ?>&action=alarm">🔔</a></li>
                                    <li><a href="?id=<?= $task['id_task'] ?>&action=theme">🔖</a></li>
                                    <li><a href="action.php?id=<?= $task['id_task'] ?>&action=up">🔼</a></li>
                                    <li><a href="action.php?id=<?= $task['id_task'] ?>&action=down">🔽</a></li>
                                    <li><a href="action.php?id=<?= $task['id_task'] ?>&action=done">✅</a></li>
                                    <li><a href="?id=<?= $task['id_task'] ?>&action=mod">📝</a></li>
                                    <li><a href="action.php?id=<?= $task['id_task'] ?>&action=del">❌</a></li>
                                </ul>
                            </li>
                    </div>
                <?php
                    };
                    };
                ?>
            </ul>
        </div>
        <!-- ADD -->
        <div class="task-add">
            <form class="task-form" action="action.php" method="POST">
                <input class="task-name" type="text" name="task-name" placeholder="Ajouter une tâche">
                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                <input class="task-valid" class="bg-blue" type="submit" value="➕">
            </form>
        </div>
        <div class="task-done">    
            <?php
                // TASK ALREADY DONE
                if (isset($_GET['action']) && $_GET['action'] === 'display-done') {
        ?>
            <h2><a href="action.php">Masquer les tâches terminées ⏫</a></h2>
        <?php
                    $query = $dbCo->prepare("SELECT * FROM task WHERE state = true ORDER BY done_date DESC;");
                    $query->execute();
                    $result = $query->fetchAll();
                    foreach ($result as $task) {
        ?>
            <div class="task-container">
                <li id=<?= $task['id_task'] ?> class="task">
                    <form action="action.php" method="POST">
                        <input class="task-back" type="submit" name="back" value="♻️">
                        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                        <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                    </form>
                    <h2><?= $task['name'] ?></h2>
                    <div>
                        <p>Tâche effectuée le :</p>
                        <time class="done-time" datetime="<?= $task['done_date'] ?>"><?= $task['done_date'] ?></time>
                    </div>
                </li>
            </div>
        <?php
                    };       
                } else {
                if (isset($_SESSION['theme'])) echo '<h2><a href="action.php?theme=none">Afficher toutes les tâches 🔄</a></h2>'
        ?>
                <h2><a href="?action=display-done">Afficher les tâches terminées ⏬</a></h2>
            <?php
                };
            ?>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>

</html>