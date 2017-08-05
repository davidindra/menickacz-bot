<?php
require '../vendor/autoload.php';

require_once 'SlackProcessor.php';

ignore_user_abort(true);

ob_start();

session_write_close();

header("Content-Encoding: none");
header("Content-Length: " . ob_get_length());
header("Connection: close");

ob_end_flush();
flush();

$slack = new SlackProcessor(
    $_GET,
    $_POST,
    __DIR__ . '/secrets.json',
    __DIR__ . '/cache.db'
);

$slack->process();

die();