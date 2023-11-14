<?php

function checkCSRF(string $url): void 
{
    if (!isset($_SERVER['HTTP_REFERER']) || !str_contains($_SERVER['HTTP_REFERER'])) {
        $SESSION['msg'] = 17;
    } else if (!isset($_SESSION['token']) || !isset($_REQUEST['token']) || $_REQUEST['token'] !== $_SESSION['token'] || $_SESSION['tokenExpire'] < time()) {
        $_SESSION['msg'] = 18;
    };
    if (!isset($_SESSION['msg'])) return;
    header('location: ' . $url);
    exit;
};

/**
 * Apply treatment on given array to prevent XSS fault.
 *
 * @param array $array
 * @return void
 */
function checkXSS(array &$array): void
{
    $array = array_map('strip_tags', $array);
    // foreach ($array as $key => $value) {
    //     $array[$key] = strip_tags($value);
    // };
};

function getToken()
{
    if (!isset($_SESSION['token']) || time() > $_SESSION['tokenExpiry']) {
        $_SESSION['token'] = md5(uniqid(mt_rand(), true));
        $_SESSION['tokenExpiry'] = time() + 15 * 60;
    };
};

/**
 * Get actual date format YYYY-MM-DD HH:mn:ss
 *
 * @return string
 */

function getActualDate(): string
{
    $thisDate = new DateTime();
    $thisDate->setTimezone(new DateTimeZone('Europe/Paris'));
    return $thisDate->format("Y-m-d H:i:s");
};

function addErrorAndExit(int $number): void 
{
    $_SESSION['msg'] = $number;
    header('location: index.php');
    exit;
};

function addNotification(string $text): void 
{
    $_SESSION['msg'] = $text;
};