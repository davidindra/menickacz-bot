<?php

require_once '../vendor/autoload.php';
require_once 'Endpoint.php';

try {

    $endpoint = new MenickaCZbot\ApiAi\Endpoint(
        $_GET,
        file_get_contents('php://input'),
        file_get_contents(__DIR__ . '/token.txt'),
        __DIR__ . '/cache.db'
    );

    $endpoint->process();

}catch(MenickaCZbot\ApiAi\EndpointException $e){
    file_put_contents(__DIR__ . '/error.log', '[' . date('Y-m-d H:i:s') . '] Catched exception: ' . $e->__toString() . PHP_EOL . PHP_EOL, FILE_APPEND);
}catch(MenickaCZbot\ApiAi\ProcessorException $e){
    file_put_contents(__DIR__ . '/error.log', '[' . date('Y-m-d H:i:s') . '] Catched exception: ' . $e->__toString() . PHP_EOL . PHP_EOL, FILE_APPEND);
}catch(\Exception $e){
    file_put_contents(__DIR__ . '/error.log', '[' . date('Y-m-d H:i:s') . '] Missed exception: ' . $e->__toString() . PHP_EOL . PHP_EOL, FILE_APPEND);
}

die();
