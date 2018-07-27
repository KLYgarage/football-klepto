<?php declare(strict_types=1);

namespace Football\Test;

use Football\Provider;

class CompetitionStandingApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Provider
     * @var \Football\Provider
     */
    private $provider;

    protected function setUp(): void
    {
        $env = \loadTestEnv();

        $this->provider = new Provider(
            $env['API_KEY']
        );
    }

    /**
     * Assert instance of provider not null
     */
    public function testInstanceNotNull(): void
    {
        $this->assertNotNull($this->provider);
    }

    /**
     * Assert api key
     */
    public function testGetApiKey(): void
    {
        $this->assertNotEmpty($this->provider->getApiKey());
        $this->assertGreaterThan(0, strlen($this->provider->getApiKey()));
    }

    /**
     * Test get list competitions
     * @return array|object
     */
    public function testListCompetitions()
    {
        $competitions = $this->provider->listCompetitions();
        $this->assertNotNull($competitions);
        return $competitions;
    }

    /**
     * @depends testListCompetitions
     * @param array|\object $competitions
     * @return int|string
     */
    public function testGetCompetitionId($competitions)
    {
        if (! is_array($competitions)) {
            $competitions = (array) $competitions;
        }

        $competitionAttr = [
            'count', 'filters', 'competitions',
        ];

        foreach ($competitionAttr as $attr) {
            $this->assertArrayHasKey($attr, $competitions);
        }

        if ($competitions['count'] < 1) {
            $this->markTestSkipped('Empty competitions');
        }

        return $competitions['competitions'][0]['id'];
    }

    /**
     * Test get standings on particular competition
     * @depends testGetCompetitionId
     * @param  int|string $competitionId
     */
    public function testGetStandingsByCompetitionId($competitionId): void
    {
        try {
            $standings = $this->provider->getStandingsByCompetitionId($competitionId);

            $this->assertNotNull($standings);
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n";

            $competitionId = 2003;

            $standings = $this->provider->getStandingsByCompetitionId($competitionId);

            $this->assertNotNull($standings);
        }
    }
}
