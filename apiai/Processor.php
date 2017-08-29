<?php

namespace MenickaCZbot\ApiAi;

require_once 'index.php';
require_once 'Request.php';

use MenickaCZ\MenickaCZ;
use MenickaCZ\Structures\Menu;

class Processor
{
    private $request;

    private $menicka;

    private $endpoint;

    //private $guzzle;

    public function __construct(string $request, MenickaCZ $menicka, Endpoint $endpoint)
    {
        $this->request = new Request($request);
        $this->menicka = $menicka;
        $this->endpoint = $endpoint;

        //$this->guzzle = new GuzzleHttp\Client();
    }

    public function process() // TODO check if in group
    {
        switch($this->request->intentId()){
            case '666c3add-3773-4f2d-a805-f7228d085e55': // Vypiš města
                /*$cities = [];
                foreach($this->menicka->getAvailableCities() as $city){
                    $cities[] = [
                        'text' => $city->getName(),
                        'value' => 'Restaurace ' . $city->getName()
                    ];
                }*/

                $cities = [];
                foreach($this->menicka->getAvailableCities() as $city) {
                    $cities[] = '_' . $city->getName() . '_';
                }

                $this->respond(
                    "K dispozici jsou tato města: \n " . implode(', ', $cities) . "\n\nMůžeš si zobrazit restaurace některého z nich pomocí napsání `restaurace Prostějov`.",
                $this->request->contexts()
                /*[],
                null
                ['slack' => [
                    'text' => 'Našel jsem nějaká města.',
                    'attachments' => [[
                        'text' => 'Můžeš si vybrat z těchhle:',
                        'fallback' => 'Ve Slacku se něco porouchalo, nemůžeš si bohužel vybrat z nabídky měst.',
                        'callback_id' => 'cityCallbackId',
                        'attachment_type' => 'default',
                        'actions' => [[
                            'name' => 'cities',
                            'text' => 'Města: ',
                            'type' => 'select',
                            'options' => $cities
                        ]]
                    ]],
                ]]*/
                );

                break;

            case '8eb93c9d-c003-4845-95cf-86f46e1a8142': // Restaurace ve městě

                $cityName = $this->request->getParameter('city');

                $city = $this->menicka->getCityByName($cityName);
                $restaurants = [];

                foreach($this->menicka->getAvailableRestaurants($city) as $restaurant){
                    $restaurants[] = '_' . $restaurant->getName() . '_';
                }

                $this->respond('Město *' . $city->getName() . '* má tyto restaurace: ' . implode(', ', $restaurants) . "\n\n Můžeš si vypsat jídelní lístek některé z nich pomocí zadání `menu Prostějov Guru jídelna`.", $this->request->contexts());

                break;

            case 'da6c4500-4f6d-41fa-b0ea-0a1a02bcd7d9': // Výpis menu restaurace

                $cityName = $this->request->getParameter('city');
                $restaurantName = $this->request->getParameter('restaurant');

                $city = $this->menicka->getCityByName($cityName);

                if($city == null){
                    $this->respond('Zdá se, že tohle město neexistuje. Zkus nějaké jiné.');
                    return;
                }

                $restaurant = $this->menicka->getRestaurantByNameAndCity($restaurantName, $city);

                if($restaurant == null){
                    $this->respond('Zdá se, že taková restaurace ve městě ' . $city->getName() . ' není. Zkus nějakou jinou.');
                    return;
                }

                /** @var Menu $todaysMenu */
                $todaysMenu = $this->menicka->getMenuSet($restaurant)->getTodaysMenu();

                if($todaysMenu == null)
                    $this->respond('Bohužel, ' . $restaurant->getName() . ' dnes nevaří :slightly_frowning_face: Budeš muset zajít jinam.');
                else {
                    $foodLines = [];
                    foreach($todaysMenu->getFoods() as $food){
                        $foodLines[] = ($food->hasOrder() ? $food->getOrder() . '. ' : '') .
                            '*' . $food->getName() . '*' .
                            ($food->hasPrice() ? ' (' . $food->getPrice() . ' Kč)' : '');
                    }

                    $this->respond('Dnešní menu restaurace ' . $restaurant->getName() . ":\n>" . implode("\n>", $foodLines));
                }

                break;

            default:
                $this->respond('Na tohle zatím neumím odpovědět :slightly_frowning_face: Zkus něco jiného.');
        }
    }

    private function respond(string $text, array $contexts = [], string $followupEvent = null, array $data = null, string $source = 'Meníčka.cz'){
        $this->endpoint->respondJson([
            'speech' => $text,
            'displayText' => $text,
            'data' => $data,
            'contextOut' => $contexts,
            'source' => $source,
            'followupEvent' => $followupEvent
        ]);
    }
}

class ProcessorException extends \Exception { }
