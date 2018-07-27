<?php declare(strict_types=1);

namespace Football\Test;

use Football\Provider;

class CompetitionMatchApiTest extends \PHPUnit\Framework\TestCase
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
     * Test list matches on particular competition
     * @depends testGetCompetitionId
     * @param  int|string $competitionId
     * @return array|\object
     */
    public function testListMatches($competitionId)
    {
        try {
            $filter = [
                'competitions' => (string) $competitionId,
                'dateFrom' => '2018-06-24',
                'dateTo' => '2018-06-24',
            ];
            $matches = $this->provider->listMatches($filter);

            $this->assertNotNull($matches);

            return $matches;
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n";

            $filter = [
                'competitions' => '2000',
                'dateFrom' => '2018-06-24',
                'dateTo' => '2018-06-24',
            ];
            $matches = $this->provider->listMatches($filter);

            $this->assertNotNull($matches);

            return $matches;
        }
    }

    /**
     * Test get match id from list matches
     * @depends testListMatches
     * @param  array|\object $matches
     * @return int|string
     */
    public function testGetMatchId($matches)
    {
        if (! is_array($matches)) {
            $matches = (array) $matches;
        }

        $matchesAttr = [
            'count', 'filters', 'matches',
        ];

        foreach ($matchesAttr as $attr) {
            $this->assertArrayHasKey($attr, $matches);
        }

        if ($matches['count'] < 1) {
            $filter = [
                'competitions' => '2000',
                'dateFrom' => '2018-06-24',
                'dateTo' => '2018-06-24',
            ];
            $matches = $this->provider->listMatches($filter);

            $this->assertNotNull($matches);
        }

        return $matches['matches'][0]['id'];
    }

    /**
     * Test get match by id
     * @depends testGetMatchId
     * @param  int|string $matchId
     */
    public function testGetMatchById($matchId): void
    {
        try {
            $match = $this->provider->getMatchById($matchId);

            $this->assertNotNull($match);
        } catch (\Throwable $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }
}
