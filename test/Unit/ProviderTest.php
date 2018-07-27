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

    public function testGetCompetitionById(): void
    {
        $id = 2000;

        $competition = $this->provider->getCompetitionById($id);

        $this->assertNotNull($competition);
    }

    public function testGetAreaById(): void
    {
        $id = 2000;

        $area = $this->provider->getAreaById($id);

        $this->assertNotNull($area);
    }

    public function testGetTeamById(): void
    {
        $id = 18;

        $team = $this->provider->getTeamById($id);

        $this->assertNotNull($team);
    }

    public function testGetTeamByCompetitionId(): void
    {
        $competitionId = 2001;

        $filter = [
            'stages' => 'S',
        ];

        $team = $this->provider->getTeamByCompetitionId(
            $competitionId
        );

        $this->assertNotNull($team);
    }
}
