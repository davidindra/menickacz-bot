<?php

namespace MenickaCZbot\ApiAi;

require_once 'Processor.php';

use MenickaCZ\GuzzleHttpClient;
use MenickaCZ\MenickaCZ;
use MenickaCZ\SqliteCache;

class Endpoint
{
    private $get;

    private $body;

    private $menicka;

    private $token;

    private $responded = false;

    public function __construct(array $get, string $body, string $token, string $cacheFile)
    {
        $this->get = $get;
        $this->body = $body;
        $this->token = $token;

        $this->menicka = new MenickaCZ(
            new GuzzleHttpClient(
                'https://menicka.cz/',
                new SqliteCache('cache.db')
            )
        );
    }

    public function process()
    {
        $this->validateToken();

        header('Content-type: application/json');

        $processor = new Processor($this->body, $this->menicka, $this);
        $processor->process();

        return true;
    }

    private function validateToken(){
        if(trim($this->get['token']) != trim($this->token))
            throw new EndpointException('Verification tokens doesn\'t match (received ' . $this->get['token'] . ')!');

        return true;
    }

    public function log(string $message){
        file_put_contents(__DIR__ . '/info.log', date('Y-m-d H:i:s') . ': ' . $message, FILE_APPEND);
    }

    public function respondJson(array $json){
        if($this->responded) throw new EndpointException('Already responded!');
        $this->responded = true;
        echo json_encode($json);
    }
}

class EndpointException extends \Exception { }
