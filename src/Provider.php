<?php declare (strict_types = 1);

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
       public const REST_SERVER='http://api.football-data.org';/**
     * Competition endpoint
     */
    	public const COMPETITION_ENDPOINT='/v2/competitions';/**
     * Area endpoint
     */
    	public const AREA_ENDPOINT='/v2/areas';/**
     * Team endpoint
     */
    public const TEAM_ENDPOINT='/v2/teams';/**
     * Api key
     * @var string
     */private $apiKey;

    /**
     * Guzzle client
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * Constructor
     */
    public function __construct(string $apiKey = '')
    {
        $this->apiKey     = $apiKey;
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
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * List competitions
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function listCompetitions(bool $convertToArray = true)
    {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::COMPETITION_ENDPOINT
            )->getBody(),
            $convertToArray
        );
    }

    /**
     * List competition by area
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function listCompetitionByArea(
        array $filter = ['areas' => ''],
        bool $convertToArray = true
    ) {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::COMPETITION_ENDPOINT . '?' . http_build_query($filter)
            )->getBody(),
            $convertToArray
        );
    }

    /**
     * Get competition by id
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function getCompetitionById(
        int $id,
        bool $convertToArray = true
    ) {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::COMPETITION_ENDPOINT . '/' . (string) $id
            )->getBody(),
            $convertToArray
        );
    }

    /**
     * List all areas
     * @return array|\object
     */
    public function listAreas(bool $convertToArray = true)
    {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::AREA_ENDPOINT
            )->getBody(),
            $convertToArray
        );
    }
    /**
     * Get area by id
     * @param  int          $id
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function getAreaById(int $id, bool $convertToArray = true)
    {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::AREA_ENDPOINT . '/' . (string) $id
            )->getBody(),
            $convertToArray
        );
    }
    /**
     * Get team by id
     * @param  int          $id
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function getTeamById(int $id, bool $convertToArray = true)
    {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::TEAM_ENDPOINT . '/' . (string) $id
            )->getBody(),
            $convertToArray
        );
    }
    /**
     * Get team based on id competition
     * @param  int          $competitionId
     * @param  array        $filter
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function getTeamByCompetitionId(
        int $competitionId,
        array $filter = ['stages' => ''],
        bool $convertToArray = true
    ) {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::COMPETITION_ENDPOINT.'/'.(string)$competitionId.'/teams' . '?' . http_build_query($filter)
            )->getBody(),
            $convertToArray
        );
    }
}
