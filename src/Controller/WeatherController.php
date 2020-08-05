<?php

namespace App\Controller;

use App\Entity\Search;
use App\Form\SearchCityType;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as Req;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\WeatherService;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class WeatherController
 * @package App\Controller
 */
class WeatherController extends AbstractController
{
    const TOULOUSE_ID = "6453974";
    const TOULOUSE_LAT = "43.604259";
    const TOULOUSE_LON = "1.44367";
    const PATH_JSON_FILE = "../public/data/city.json";

    private $weatherService;
    private $cache;
    private $stopWatch;

    public function __construct(WeatherService $weather, CacheInterface $cache, Stopwatch $stopWatch)
    {
        $this->weatherService = $weather;
        $this->cache = $cache;
        $this->stopWatch = $stopWatch;
    }

    /**
     * @Route("/", name="weather", methods={"GET", "POST"})
     * @param Req $request
     * @return Response
     * @throws \Exception
     */
    public function index(Req $request)
    {
        /* Variable qui prend par défaut l'id de toulouse */
        $city_id = self::TOULOUSE_ID;
        $city_lat = self::TOULOUSE_LAT;
        $city_lon = self::TOULOUSE_LON;

        /* Formulaire pour récupérer la requête utilisateur */
        $search = new Search();
        $form = $this->createForm(SearchCityType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $city_search = $data->getSearch();
            $city_id = $data->getCityId();
            $city_lat = $data->getCityLat();
            $city_lon = $data->getCityLon();

            /* Ici je teste si lutilisateur à utiliser l'autocompletion de l'input
             * Si ce n'est pas le cas alors je rentre dans le if
             * Sinon je laisse les valeurs rentré
             * je teste ensuite si la valeur existe dans le fichier json
             * si elle existe je lance la requête via le service
             * sinon je renvoi un message d'erreur
            */
            if (!isset($city_id)) {
                $jsonContent = $this->getCityListJson()->getContent();
                $json = json_decode($jsonContent, true);
                $cityName = [];

                foreach ($json as $cityNameValue) {
                    if ($cityNameValue['name'] === ucfirst($city_search)) {
                        array_push($cityName, $cityNameValue['name']);
                    }
                }

                if (empty($cityName)) {
                    $this->addFlash('error', 'Désolée, la ville recherché n\'existe pas. Veuilez re essayer');
                } else {
                    $cityDatas = $this->weatherService->getDataWithSearch($city_search);
                    $city_id = $cityDatas['id'];
                    $city_lat = $cityDatas['coord']['lat'];
                    $city_lon = $cityDatas['coord']['lon'];
                }
            }
        }

        /* Requête utilisant le service WeatherService pour récuperer la météo de la ville */
        $weather = $this->weatherService->getWeather($city_lat, $city_lon, $city_id);

        /* On retourne le rendu avec les données météo dans la vue Twig pour mettre à jour le template */
        return $this->render('weather/index.html.twig', array(
            'meteo' => $weather,
            'form'  => $form->createView(),
        ));
    }

    /**
     * @Route("/data-city", name="data-city")
     * Methode qui permet de renvoyer les données des villes stockés sur le serveur
     * Je teste d'abord si j'ai les informations en cache
     * si je ne les ai pas je les m'est en cache et les return
     * @return JsonResponse
     */
    public function getCityListJson()
    {
        $cache = $this->cache;
        return $cache->get('cities', function () {
            return $this->getJsonFile(self::PATH_JSON_FILE);
        });
    }

    private function getJsonFile($path)
    {
        return new JsonResponse(json_decode(file_get_contents($path), true));
    }

}
