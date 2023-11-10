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
    </header>

    <main>
        <div class="task-list">
            <ul>
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
                    <div class="notif">
                        <h3><?= $result['name'] ?></h3>
                    </div>
                <?php
                    };

                    // DISPLAY
                    $query = $dbCo->prepare("SELECT * FROM task WHERE state = false ORDER BY creation_date DESC;");
                    $query->execute();
                    $result = $query->fetchAll();
                    foreach ($result as $task) {
                ?>
                    <div class="task-container" draggable="true">
                <?php
                    if (isset($_GET['action']) && $_GET['action'] === 'mod' && isset($_GET['id']) && $task['id_task'] === intval($_GET['id'])) {
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
                        <input class="task-valid" type="submit" value="&#x2714">
                    </form>
                <?php
                    } else {
                ?>
                    <li id=<?= $task['id_task'] ?> class="task">
                        <h2><?= $task['name'] ?></h2>
                        <ul class="task-utils">
                            <li class="done"><a href="action.php?id=<?= $task['id_task'] ?>&action=done">&#x2705</a></li>
                            <li class="modify"><a href="?id=<?= $task['id_task'] ?>&action=mod">&#x24C2</a></li>
                            <li class="delete"><a href="action.php?id=<?= $task['id_task'] ?>&action=del">&#x274C</a></li>
                        </ul>
                    </li>
                    </div>
                <?php
                    };
                    };
                ?>
            </ul>
        </div>
        <div class="task-add">
            <form class="task-form" action="action.php" method="POST">
                <input class="task-name" type="text" name="task-name" placeholder="Ajouter une tâche">
                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                <input class="task-valid" class="bg-blue" type="submit" value="&#x2795">
            </form>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>

</html>