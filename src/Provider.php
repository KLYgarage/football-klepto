<?php

namespace Football;

use \GuzzleHttp\Client;


/**
 * Class to interact with
 * Football API
 */
class Provider
{
    /**

     * Server address
     */
    const REST_SERVER = 'http://api.football-data.org';
    /**
     * Competition endpoint
     */
    const COMPETITION_ENDPOINT = '/v2/competitions';
    /**
     * Area endpoint
     */
    const AREA_ENDPOINT = '/v2/areas';
    /**

     * Api key
     * @var string
     */
    private $apiKey;
    /**

     * Guzzle client
     * @var \GuzzleHttp\Client
     */
    private $httpClient;
    /**

     * Constructor
     * @param string $apiKey
     */
    public function __construct($apiKey = '')
    {
        $this->apiKey = $apiKey;
        $this->httpClient = new Client([
            'base_uri' => self::REST_SERVER,
            'headers'  => [
                'X-Auth-Token'       => $this->apiKey,
                'X-Response-Control' => 'full',
            ],
        ]);
    }
    /**
     * Get client api key
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * List all competitions
     * @return array
     */
    public function listCompetitions()
    {
        return json_decode(
            $this->httpClient->request(
                'GET',
                self::COMPETITION_ENDPOINT
            )->getBody()
        );
    }
    /**
     * List competitions by area
     * @param  array  $filter area id e.g :
     * @return array
     */
    public function listCompetitionByArea($filter = array('areas'=>''))
    {
        return json_decode(
            $this->httpClient->request(
                'GET',
                self::COMPETITION_ENDPOINT.'?'.http_build_query($filter)
            )->getBody()
        );
    }
    /**
     * List area of competitions
     * @return array
     */
    public function listAreas()
    {
        return json_decode(
            $this->httpClient->request(
                'GET',
                self::AREA_ENDPOINT
            )->getBody()
        );
    }
}
