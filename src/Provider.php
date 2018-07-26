<?php

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
     * @param string $apiKey
     */
    public function __construct($apiKey = '')
    {
        $this->apiKey = $apiKey;
    }
    /**
     * Get client api key
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }
}
