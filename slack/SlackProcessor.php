<?php
require_once 'SlackCommands.php';

use MenickaCZ\GuzzleHttpClient;
use MenickaCZ\MenickaCZ;
use MenickaCZ\SqliteCache;

class SlackProcessor
{
    private $get;

    private $post;

    private $menicka;

    private $secrets;

    public function __construct(array $get, array $post, string $secretsFile, string $cacheFile)
    {
        $this->get = $get;
        $this->post = $post;

        if(!file_exists($secretsFile))
            throw new SlackProcessorException('Secrets file doesn\'t exist.');

        $this->secrets = json_decode(file_get_contents($secretsFile), true);

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

        switch (@$this->get['type']){
            case 'command':
                $commands = new SlackCommands($this->post, $this->menicka, $this->secrets);
                $commands->process();
                break;

            default:
                throw new SlackProcessorException('Unknown/missing request type received ' . $this->get['command']);
        }

        return true;
    }

    private function validateToken(){
        if($this->post['token'] != $this->secrets['verification_token'])
            throw new SlackProcessorException('Verification tokens doesn\'t match (received ' . $this->post['token'] . ')!');

        return true;
    }
}

class SlackProcessorException extends Exception { }