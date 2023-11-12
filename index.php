<?php
session_start();
include_once 'includes/_db.php';
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
                    <li><a href="?theme=<?= $theme['id_theme'] ?>"><?= $theme['name'] ?></a></li>
                <?php
                    };
                ?>
                <a href="index.php">Toutes les tâches</a>
            </ul>
        </nav>
        <a href="#" id="openBtn" class="burger-icon">🍔</a>
        <h1>Ma to do list</h1>
        <?php
            $query = $dbCo->prepare("SELECT alarm_date FROM task WHERE alarm_date IS NOT NULL;");
            $query->execute();
            $result = $query->fetchAll();
            $thisDate = new DateTime();
            $thisDate->setTimezone(new DateTimeZone('Europe/Paris'));
            $formattedDate = $thisDate->format("Y-m-d");
            foreach ($result as $alarmDate) {
                if ($formattedDate === substr($alarmDate['alarm_date'], 0, -9)) {
                        $msg = "Une tâche est notée pour aujourd'hui.";
                        echo "<script>alert('$msg');</script>";
                ?>
                    <p class="alert"><span>⚠</span><br>Une tâche est notée pour aujourd'hui.</p>
                <?php 
                };
            };
        ?>
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
                    // DISPLAY
                    $query = $dbCo->prepare("SELECT * FROM task WHERE state = false ORDER BY priority DESC;");
                    $query->execute();
                    $result = $query->fetchAll();
                    if (isset($_GET['theme'])) {
                        $query = $dbCo->prepare("SELECT * FROM task WHERE state = false AND id_task IN ( SELECT id_task FROM category ) AND :id_theme IN ( SELECT id_theme FROM category );");
                        $query->execute([
                            'id_theme' => intval(strip_tags($_GET['theme']))
                        ]);
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
                                $thisDate = new DateTime();
                                $thisDate->setTimezone(new DateTimeZone('Europe/Paris'));
                                $formattedDate = $thisDate->format("Y-m-d H:i");
                        ?>
                            <div class="task-alarm">
                        <?php
                            if (isset($task['alarm_date'])) {
                        ?>
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
                                <form action="action.php" method="POST">
                                    <input class="date" type="datetime-local" name="alarm" value="<?= $formattedDate ?>" min="<?= $formattedDate ?>" max="">
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
                        <?php
                            foreach ($result as $theme) {
                                $filteredCategories = array_filter($categories, fn($category) => $category['id_task'] === $_GET['id'] && $category['id_theme'] === $theme['id_theme']);
                                if (!empty($filteredCategories)) {
                        ?>
                            <form action="action.php" method="POST">
                                <input class="theme-name bg-blue" type="submit" name="theme" value="<?= $theme['name'] ?>">
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
                            // CLASSIC                 
                            else {
                        ?>
                            <li id=<?= $task['id_task'] ?> class="task">
                                <div>
                                    <h2><?= $task['name'] ?></h2>
                                    <time class="alarm" datetime="<?= $task['alarm_date'] ?>">
                                        <?php
                                            echo substr($task['alarm_date'], 0, -3);
                                            $thisDate = new DateTime();
                                            $thisDate->setTimezone(new DateTimeZone('Europe/Paris'));
                                            $formattedDate = $thisDate->format("Y-m-d");
                                            if ($task['alarm_date'] <> NULL && $formattedDate === substr($task['alarm_date'], 0, -9)) echo '🚩';
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
                                        <input class="task-back" type="submit" name="back" value="🔄">
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