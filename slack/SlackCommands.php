<?php
require_once 'SlackProcessor.php';

use MenickaCZ\MenickaCZ;

class SlackCommands
{
    private $post;

    private $menicka;

    private $secrets;

    private $guzzle;

    public function __construct(array $post, MenickaCZ $menicka, array $secrets)
    {
        $this->post = $post;
        $this->menicka = $menicka;
        $this->secrets = $secrets;

        $this->guzzle = new GuzzleHttp\Client();
    }

    public function process()
    {
        switch ($this->post['command']){
            case '/obed':
                $this->handleLunchCommand($this->post['text']);
                break;

            default:
                throw new SlackProcessorException('Unknown command ' . $this->post['command']);
        }
    }

    private function handleLunchCommand(string $input)
    {
        $input = trim($input);

        $inputExploded = explode(' ', $input);

        if($input == '' || $input == 'help'){
            $h = [];
            $h[] = 'Ukázkové použití:';
            $h[] = ' - `/obed mesta`';
            $h[] = ' - `/obed restaurace Prostějov`';
            $h[] = ' - `/obed Prostějov Lázně`';
            $this->sendResponseText(implode("\n", $h));
            return;
        }

        if($input == 'mesta'){
            $cities = $this->menicka->getAvailableCities();

            $citiesNames = [];

            foreach($cities as $city){
                $citiesNames[] = $city->getName();
            }

            $this->sendResponseText('*Dostupná města:* ' . implode(', ', $citiesNames));
            return;
        }

        if($inputExploded[0] == 'restaurace'){
            $cityName = trim(str_replace('restaurace ', '', $input));

            $city = $this->menicka->getCityByName($cityName);

            $restaurants = $this->menicka->getAvailableRestaurants($city);

            $restaurantsNames = [];

            foreach ($restaurants as $restaurant){
                $restaurantsNames[] = $restaurant->getName();
            }

            $this->sendResponseText('*Dostupné restaurace ve městě ' . $city->getName() . ':* ' . implode(', ', $restaurantsNames));
            return;
        }

        $this->sendErrorResponse();
    }

    private function sendErrorResponse()
    {
        $this->sendResponseText('Bohužel nedokážu pochopit tvoje zadání, zkus to prosím znovu :slightly_smiling_face:');
    }

    private function sendResponseText($text, $public = false)
    {
        $this->sendResponse([
            'response_type' => $public ? 'in_channel' : 'ephemeral',
            'text' => $text
        ]);
    }

    private function sendResponse($json)
    {
        $response = $this->guzzle->post($this->post['response_url'], ['json' => $json]);

        if($response->getStatusCode() != 200)
            throw new SlackCommandsException('Slack responded with ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase() . '.');
    }
}

class SlackCommandsException extends \Exception { }