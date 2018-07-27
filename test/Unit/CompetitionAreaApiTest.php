<?php declare(strict_types=1);

use Football\Provider;

class CompetitionAreaApiTest extends \PHPUnit\Framework\TestCase
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
     * @depends testGetCompetitionId
     * @param int|string $competitionId
     */
    public function testGetParticularCompetition($competitionId): void
    {
        try {
            $competition = $this->provider->getCompetitionById($competitionId);

            $this->assertNotNull($competition);
        } catch (\Throwable $e) {
            $competitionId = 2000;

            $competition = $this->provider->getCompetitionById($competitionId);

            $this->assertNotNull($competition);
        }
    }

    /**
     * Test list areas
     * @return array|\object
     */
    public function testListAreas()
    {
        try {
            $areas = $this->provider->listAreas();

            $this->assertNotNull($areas);

            return $areas;
        } catch (\Throwable $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    /**
     * @depends testListAreas
     * @param  array|\object $areas
     * @return int|string
     */
    public function testGetAreaId($areas)
    {
        if (! is_array($areas)) {
            $areas = (array) $areas;
        }

        $areasAttr = [
            'count', 'filters', 'areas',
        ];

        foreach ($areasAttr as $attr) {
            $this->assertArrayHasKey($attr, $areas);
        }

        if ($areas['count'] < 1) {
            $this->markTestSkipped('Empty areas');
        }

        return $areas['areas'][0]['id'];
    }

    /**
     * Test get area details by id
     * @depends testGetAreaId
     * @param  int|string $areaId
     */
    public function testGetAreaDetails($areaId): void
    {
        try {
            $area = $this->provider->getAreaById($areaId);

            $this->assertNotNull($area);
        } catch (\Throwable $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    /**
     * Test filter competition by area
     * @depends testGetAreaId
     * @param  int|string $areaId
     * @return [type]         [description]
     */
    public function testFilterCompetitionByArea($areaId)
    {
        try {
            $filter = [
                'areas' => (string) $areaId,
            ];

            $competitionsByArea = $this->provider->listCompetitionByArea($filter);

            $this->assertNotNull($competitionsByArea);
        } catch (\Throwable $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }
}
