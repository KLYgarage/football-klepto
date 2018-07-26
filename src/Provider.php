<?php declare(strict_types=1);

namespace Football;

/**
 * Class to interact with
 * Football API
 */
class Provider
{
    /**
     * Api key
     * @var string
     */
    private $apiKey;

    /**
     * Constructor
     */
    public function __construct(string $apiKey = '')
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get client api key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
