<?php

use Football\Provider;

/**
 *
 */
class ProviderTest extends \PHPUnit\Framework\TestCase
{
    private $provider;

    public function setUp()
    {
        $env = \loadTestEnv();

        $this->provider = new Provider(
            $env['API_KEY']
        );
    }

    public function testInstanceNotNull()
    {
        $this->assertNotNull($this->provider);
    }

    public function testGetApiKey()
    {
        $this->assertNotEmpty($this->provider->getApiKey());
        $this->assertGreaterThan(0, strlen($this->provider->getApiKey()));
    }
}
