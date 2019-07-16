<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class WeatherService
{
    private $client;
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->client = HttpClient::create();
        $this->apiKey = $apiKey;
    }

    /**
     * @return array
     */
    public function getWeather()
    {
        $response = $this->client->request('GET', 'https://api.darksky.net/forecast/' . $this->apiKey . '/37.8267,-122.4233');

        return [
            'temperature' => '20', // en °C
            'vent' => '17', // en km/H
        ];
    }
}
