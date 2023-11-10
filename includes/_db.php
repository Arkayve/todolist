<?php

// Connection to todolist database
try {
    $dbCo = new PDO(
        'mysql:host=localhost;dbname=todolist;charset=utf8',
        'phpcrud',
        'phpcrudadmin'
    );
    $dbCo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch (Exception $e) {
    die('Unable to connect to the database. '.$e->getMessage());
};

// TOKEN
if (!isset($_SESSION['token']) || time() > $_SESSION['tokenExpiry']) {
    $_SESSION['token'] = md5(uniqid(mt_rand(), true));
    $_SESSION['tokenExpiry'] = time() + 15 * 60;
};
