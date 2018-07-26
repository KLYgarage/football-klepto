<?php declare(strict_types=1);

use Football\Provider;

class ProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * provider
     *
     * @var Provider
     */
    private $provider;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $env = \loadTestEnv();

        $this->provider = new Provider(
            $env['API_KEY']
        );
    }

    /**
     * Undocumented function
     */
    public function testInstanceNotNull(): void
    {
        $this->assertNotNull($this->provider);
    }

    /**
     * Undocumented function
     */
    public function testGetApiKey(): void
    {
        $this->assertNotEmpty($this->provider->getApiKey());
        $this->assertGreaterThan(0, strlen($this->provider->getApiKey()));
    }

    public function testListCompetitions(): void
    {
        $competitions = $this->provider->listCompetitions();
        $this->assertNotNull($competitions);
    }

    public function testListCompetitionsByArea(): void
    {
        $filter = [
            'areas' => '2000',
        ];

        $competitionsByArea = $this->provider->listCompetitionByArea($filter);

        $this->assertNotNull($competitionsByArea);
    }

    public function testListAreas(): void
    {
        $areas = $this->provider->listAreas();

        $this->assertNotNull($areas);
    }
}
