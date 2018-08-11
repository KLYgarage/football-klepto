<?php declare(strict_types=1);

namespace Football\Test;

use Football\Provider\SoccerWay;

class SoccerWayTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Soccerway instance
     * @var \Football\Provider\Soccerway
     */
    private $soccerWay;

    protected function setUp(): void
    {
        $this->soccerWay = new SoccerWay();
    }

    public function testInstanceNotNull(): void
    {
        $this->assertNotNull($this->soccerWay);
    }

    public function testListAreas(): void
    {
        $filter = [
            SoccerWay::QUERY_KEY_MENU => SoccerWay::COMPETITIONS,
        ];

        $areas = $this->soccerWay->listAreas($filter);

        $this->assertNotNull($areas);

        $this->assertNotEmpty($areas);
    }

    public function testGetAreaById(): void
    {
        $area = $this->soccerWay->getAreaById(8);

        $this->assertNotNull($area);

        $this->assertNotEmpty($area);
    }

    public function testGetAreaByName(): void
    {
        $areaName = 'Italia';

        $area = $this->soccerWay->getAreaByName($areaName);

        $this->assertNotNull($area);

        $this->assertNotEmpty($area);
    }

    /**
     * Test list competitions
     */
    public function testListCompetitions(): array
    {
        $areaName = 'Italia';

        $filter = [
            'area' => $areaName,
        ];

        $competitions = $this->soccerWay->listCompetitions($filter);

        $this->assertNotNull($competitions);

        $this->assertNotEmpty($competitions);

        //print_r($competitions);

        return [$competitions, $filter];
    }

    /**
     * @depends testListCompetitions
     * @return array
     */
    public function testGetCompetitionById(array $param = [''])
    {
        $competition = $this->soccerWay->getCompetitionById(
            $param[0][0]['id'],
            $param[1]
        );

        $this->assertNotNull($competition);

        $this->assertNotEmpty($competition);

        $this->assertSame($competition, $param[0][0]);

        //print_r($competition);
    }

    public function testGetTeamByCompetitionId(): void
    {
        $teams = $this->soccerWay->getTeamByCompetitionId(13);

        $this->assertNotNull($teams);

        $this->assertNotEmpty($teams);
    }

    public function testGetTeamById(): void
    {
        $areaName = 'Italia';

        $filter = [
            'area' => $areaName,
        ];

        $team = $this->soccerWay->getTeamById(1245, $filter);

        $this->assertNotNull($team);

        $this->assertNotEmpty($team);
    }

    public function testGetCompetitonByName(): void
    {
        $filter = [
            'area' => 'Italia',
        ];

        $competition = $this->soccerWay->getCompetitionByName('Serie A', $filter);

        $this->assertNotNull($competition);

        $this->assertNotEmpty($competition);
    }

    public function testListMatches(): void
    {
        $filter = [
            'area' => 'Italia',
            'competitionName' => 'Serie A',
        ];

        $matches = $this->soccerWay->listMatches($filter);

        $this->assertNotFalse($matches);

        $this->assertNotEmpty($matches);

        //print_r($matches);
    }

    public function testListMatchesInggris(): void
    {
        $filter = [
            'area' => 'Inggris',
            'competitionName' => 'Liga Primer',
        ];

        $matches = $this->soccerWay->listMatches($filter);

        $this->assertNotFalse($matches);

        $this->assertNotEmpty($matches);

        //print_r($matches);
    }

    public function testGetStandingsByCompetitionId()
    {
        $filter = array(
            'area'=>'Italia'
        );
        $standings = $this->soccerWay->getStandingsByCompetitionId("13",$filter);
        $this->assertNotNull($standings);
        $this->assertNotEmpty($standings);
    }

    public function testGetMatchById()
    {
        $matchId = 2864136;

        $filter = array(
            'competitionId'=>"13",
            "area"=>"Italia",
            "competitionName"=>"Serie A"
        );

        $match = $this->soccerWay->getMatchById((string)$matchId,$filter);

        $this->assertNotNull($match);

        $this->assertNotEmpty($match);
    }
}
