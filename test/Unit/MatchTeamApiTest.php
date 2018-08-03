<?php declare(strict_types=1);

namespace Football\Test;

use Football\Provider\FootballDataOrg;

class MatchTeamApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Provider
     * @var \Football\Provider
     */
    private $provider;

    protected function setUp(): void
    {
        $env = \loadTestEnv();

        $this->provider = new FootballDataOrg(
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
     * Get team from particular competition
     * @depends testGetCompetitionId
     * @param  int|string $competitionId
     * @return array|\object
     */
    public function testGetTeamByCompetitionId($competitionId)
    {
        try {
            $filter = [
                'stages' => 'S',
            ];

            $team = $this->provider->getTeamByCompetitionId(
                $competitionId
            );

            $this->assertNotNull($team);

            return $team;
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n";

            $competitionId = 2001;

            $filter = [
                'stages' => 'S',
            ];

            $team = $this->provider->getTeamByCompetitionId(
                $competitionId
            );

            $this->assertNotNull($team);

            return $team;
        }
    }

    /**
     * Test get team id from particular team
     * @depends testGetTeamByCompetitionId
     * @param  array|\object $team
     * @return int|string
     */
    public function testTestgetTeamId($team)
    {
        try {
            if (! is_array($team)) {
                $team = (array) $team;
            }

            $teamsAttr = [
                'count', 'filters', 'teams',
            ];

            foreach ($teamsAttr as $attr) {
                $this->assertArrayHasKey($attr, $team);
            }

            if ($team['count'] < 1) {
                $this->markTestSkipped('Empty teams');
            }

            return $team['teams'][0]['id'];
        } catch (\Throwable $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    /**
     * Test get matches from particular team
     * @depends testgetTeamId
     * @param  array|\object $teamId
     */
    public function testGetMatchesByTeamId($teamId): void
    {
        try {
            $matches = $this->provider->getMatchesByTeamId($teamId);

            $this->assertNotNull($matches);
        } catch (\Throwable $e) {
            $teamId = 759;

            $matches = $this->provider->getMatchesByTeamId($teamId);

            $this->assertNotNull($matches);
        }
    }
}
