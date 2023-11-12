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
                    foreach ($result as $task) {
                ?>
                    <div class="task-container" draggable="true">
                        <?php
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
                            // ALERT
                            else if (isset($_GET['action']) && $_GET['action'] === 'alarm' && isset($_GET['id']) && $task['id_task'] === $_GET['id']) {
                                $thisDate = new DateTime();
                                $thisDate->setTimezone(new DateTimeZone('Europe/Paris'));
                                $formattedDate = $thisDate->format("Y-m-d H:i");
                        ?>
                            <form action="action.php" method="POST">
                                <input class="date" type="datetime-local" name="alarm" value="<?= $formattedDate ?>" min="<?= $formattedDate ?>" max="">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                                <input class="task-valid" class="bg-blue" type="submit" value="➕">
                            </form>
                    </div>
                        <?php
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
                                
                        ?>
                            <form action="action.php" method="POST">
                                <input class="theme-name" type="submit" name="theme" value="<?= $theme['name'] ?>">
                                <input type="hidden" name="id_theme" value="<?= $theme['id_theme'] ?>">
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
                                <ul class="task-utils">
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
            <h2><a href="?action=display-done">Afficher les tâches terminées ⬇</a></h2>
            <?php
                // TASK ALREADY DONE
                if (isset($_GET['action']) && $_GET['action'] === 'display-done') {
                    $query = $dbCo->prepare("SELECT * FROM task WHERE state = true ORDER BY done_date DESC;");
                    $query->execute();
                    $result = $query->fetchAll();
                    foreach ($result as $task) {
                        ?>
                            <li id=<?= $task['id_task'] ?> class="task">
                                <form action="action.php" method="POST">
                                    <input type="submit" name="back" value="🔄">
                                    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                    <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                                </form>
                                <h2><?= $task['name'] ?></h2>
                                <time datetime="<?= $task['done_date'] ?>"><?= $task['done_date'] ?></time>
                            </li>
                        <?php
                    };
            ?>
                    <h2><a href="action.php">Masquer les tâches terminées ⬆</a></h2>
            <?php
                };
            ?>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>

</html>