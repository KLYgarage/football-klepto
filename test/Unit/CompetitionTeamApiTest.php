<?php declare(strict_types=1);

namespace Football\Test;

use Football\Provider\FootballDataOrg;

class CompetitionTeamApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Provider
     * @var \Football\Provider\FootballDataOrg
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
     * Test get team by id
     * @depends testGetTeamByCompetitionId
     * @param  int|string $teamId
     */
    public function testGetTeamById($teamId): void
    {
        try {
            $team = $this->provider->getTeamById($teamId);

            $this->assertNotNull($team);
        } catch (\Throwable $e) {
            $id = 18;

            $team = $this->provider->getTeamById($id);

            $this->assertNotNull($team);
        }
    }
}
