<?php

// Connection to todolist database
try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
    $dbCo = new PDO(
        $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PWD']
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
