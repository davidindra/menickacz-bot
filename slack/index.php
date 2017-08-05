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

try {

    $slack = new SlackProcessor(
        $_GET,
        $_POST,
        __DIR__ . '/secrets.json',
        __DIR__ . '/cache.db'
    );

    $slack->process();

}catch(\Exception $e){
    file_put_contents(__DIR__ . '/error.log', $e->__toString(), FILE_APPEND);
}

die();