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

    public function testListCompetitons(): void
    {
        $areaName = 'Italia';

        $filter = [
            'area' => $areaName,
        ];

        $competitions = $this->soccerWay->listCompetitions($filter);

        $this->assertNotNull($competitions);

        $this->assertNotEmpty($competitions);
    }
}
