<?php declare(strict_types=1);

use Football\Provider;

class CompetitionApiTest extends \PHPUnit\Framework\TestCase
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
    public function testCompetitionHasKeys($competitions)
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
     * @depends testCompetitionHasKeys
     * @param int|string $competitionId
     */
    public function testGetParticularCompetition($competitionId): void
    {
        try {
            $competition = $this->provider->getCompetitionById($competitionId);

            $this->assertNotNull($competition);
        } catch (\Throwable $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }
}
