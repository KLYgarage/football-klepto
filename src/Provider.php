<?php

namespace Football;

/**
 *
 */
class Provider
{
    private $apiKey;
    
    public function __construct($apiKey = '')
    {
        $this->apiKey = $apiKey;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }
}
