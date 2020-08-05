<?php

namespace App\Service;

use Cassandra\Exception\UnauthorizedException;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WeatherService
{
    private $client;
    private $apiKey;
    private $units;
    private $mode;
    private $lang;

    public function __construct($apiKey, $units, $mode, $lang)
    {
        $this->client = HttpClient::create();
        $this->apiKey = $apiKey;
        $this->units = $units;
        $this->mode = $mode;
        $this->lang = $lang;
    }

    /* Fonction permettant de traduire la direction du vent, de degré en Orientation Nord, Est, Ouest, Sud */
    /**
     * @param $degree
     * @return string
     */
    function toTextualDescription($degree)
    {
        switch ($degree) {
            case $degree >= 360 && $degree <= 21:
                $orientation = "N";
                break;
            case $degree >= 22 && $degree <= 44:
                $orientation = "NNE";
                break;
            case $degree >= 45 && $degree <= 66:
                $orientation = "NE";
                break;
            case $degree >= 67 && $degree <= 89:
                $orientation = "ENE";
                break;
            case $degree >= 90 && $degree <= 111:
                $orientation = "E";
                break;
            case $degree >= 112 && $degree <= 134:
                $orientation = "ESE";
                break;
            case $degree >= 135 && $degree <= 156:
                $orientation = "SE";
                break;
            case $degree >= 157 && $degree <= 179:
                $orientation = "SSE";
                break;
            case $degree >= 180 && $degree <= 201:
                $orientation = "S";
                break;
            case $degree >= 202 && $degree <= 224:
                $orientation = "SSO";
                break;
            case $degree >= 225 && $degree <= 246:
                $orientation = "SO";
                break;
            case $degree >= 247 && $degree <= 269:
                $orientation = "OSO";
                break;
            case $degree >= 270 && $degree <= 291:
                $orientation = "Ouest";
                break;
            case $degree >= 292 && $degree <= 314:
                $orientation = "ONO";
                break;
            case $degree >= 315 && $degree <= 336:
                $orientation = "NO";
                break;
            case $degree >= 337 && $degree <= 359:
                $orientation = "NNO";
                break;
            default:
                $orientation = "no data";
        }
        return $orientation;
    }

    /**
     * @param $endpoint
     * @return string
     * Base de l'url de l'API afin de l'utiliser ailleurs
     */
    public function baseurl($endpoint)
    {
        return "https://api.openweathermap.org/data/2.5/{$endpoint}&lang=" . $this->lang . "&units=" . $this->units . "&mode=" . $this->mode . "&appid=" . $this->apiKey;
    }

    public function getDataWithSearch($search)
    {
        return json_decode($this->client->request('GET', $this->baseurl("weather?q={$search}"))->getContent(), true);
    }

    /**
     * @param $idCity
     * @return mixed|void
     * Requête pour récupérer la ville souhaitée
     */
    public function getCityData($idCity)
    {
        if ($this->apiKey) {
            return json_decode($this->client->request('GET', $this->baseurl("weather?id={$idCity}"))->getContent(), true);
        }
        return;
    }

    /**
     * @param $lat
     * @param $lon
     * @return mixed|void
     * Requête pour récupérer les data de la ville choisi par la longitude et latitude
     */
    public function getDataWeather($lat, $lon)
    {
        if ($this->apiKey) {
            return json_decode($this->client->request('GET', $this->baseurl("onecall?lat={$lat}&lon={$lon}"))->getContent(), true);
        }
        return;
    }

    /**
     * @param $lat
     * @param $lon
     * @param $id
     * @return array
     */
    public function getWeather($lat, $lon, $id)
    {
        /* Tableau contenant les informations relative à la météo */
        $weather = [];
        /* Paramétrage de la langue française */
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');

        /* Requête pour récupérer toutes les infos de la météo */
        try {
            $datasCity = $this->getCityData($id);
            $datasWeather = $this->getDataWeather($lat, $lon);
            $merge = array_merge($datasCity, $datasWeather);
            array_push($weather, $merge);

        } catch (\Exception $e) {
            if ($e->getCode() === 401) {
                throw new \Exception('Exception: La Clé API est manquante, mal renseigné ou vérifiez votre abonnement (Code:' . $e->getCode() . ').' . PHP_EOL . 'https://openweathermap.org/');
            } elseif ($e->getCode() === 404) {
                throw new \Exception('Mauvaise requête, Nom de ville ou id de ville incorrect (Code:' . $e->getCode() . ').');
            } elseif ($e->getCode() === 429) {
                throw new \Exception('Vous avez atteint la limite de requête à l\'API pour un abonnement gratuit (Code:' . $e->getCode() . ').');
            }
        }

        if (empty($weather)) {
            return $weather = [];
        } else {
            $weather = $weather[0];
            $previsions = [];

            for ($i = 1; $i <= 5; $i++) {
                $timestamp = $weather['daily'][$i]['dt'];
                $formatDatePrevision = strftime("%a", $timestamp);
                $prevision = [
                    'date'     => $formatDatePrevision,
                    'icone'    => $weather['daily'][$i]['weather'][0]['icon'],
                    'humidite' => $weather['daily'][$i]['humidity'],
                    'minimale' => $weather['daily'][$i]['temp']['min'],
                    'maximale' => $weather['daily'][$i]['temp']['max'],
                ];
                array_push($previsions, $prevision);
            }

            /* Formattage de la date en timestamp en français */
            $dateFormat = strftime("%a %d %B %H:%M", $weather['current']['dt']);

            /* Utilisation de la fonction de transformation des degré en orientation
             * Possibilité d'en faie un service plus tard afin de traiter la transformation des deg en orientation dans un autre fichier
             */
            $directionWind = $this->toTextualDescription($weather['current']['wind_deg']);
            return [
                'nom'            => $weather['name'], // Nom en français
                'country'        => $weather['sys']['country'], // Code national
                'date'           => $dateFormat, // Date au format iso
                'icone'          => $weather['current']['weather'][0]['icon'], // Icone de météo
                'temperature'    => round($weather['current']['temp']), // Température en °C
                'description'    => $weather['current']['weather'][0]['description'], // Description du temp
                'ressenti'       => round($weather['current']['feels_like']), // Ressenti en °C
                'humidite'       => $weather['current']['humidity'], // humidité
                'uv'             => round($weather['current']['uvi']), // Indice UV
                'vent'           => round(($weather['current']['wind_speed'] / 1000) * (3600)), // Vitesse du vent en Km/h
                'direction_vent' => $directionWind, // Direction du vent
                'previsions'     => $previsions, // Prévision sur 5 jours
            ];
        }
    }
}
