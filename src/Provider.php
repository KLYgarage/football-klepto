<?php declare(strict_types=1);

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
    public const REST_SERVER = 'http://api.football-data.org';

    /**
     * Competition endpoint
     */
    public const COMPETITION_ENDPOINT = '/v2/competitions';

    /**
     * Area endpoint
     */
    public const AREA_ENDPOINT = '/v2/areas';

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
     */
    public function __construct(string $apiKey = '')
    {
        $this->apiKey = $apiKey;
        $this->httpClient = new Client([
            'base_uri' => self::REST_SERVER,
            'headers' => [
                'X-Auth-Token' => $this->apiKey,
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
     * List all competitions
     */
    public function listCompetitions(): object
    {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::COMPETITION_ENDPOINT
            )->getBody(),
            false
        );
    }

    /**
     * List competition by area
     * @param array
     */
    public function listCompetitionByArea(array $filter = ['areas' => '']): object
    {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::COMPETITION_ENDPOINT . '?' . http_build_query($filter)
            )->getBody(),
            false
        );
    }

    public function getCompetitionById(int $id): object
    {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::COMPETITION_ENDPOINT . '/' . (string) $id
            )->getBody(),
            false
        );
    }

    /**
     * List area of competitions
     */
    public function listAreas(): object
    {
        return json_decode(
            (string) $this->httpClient->request(
                'GET',
                self::AREA_ENDPOINT
            )->getBody(),
            false
        );
    }
}
