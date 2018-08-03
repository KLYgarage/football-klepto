<?php declare(strict_types=1);

namespace Football\Test;

use Football\Handler\BolaNetHandler;

class BolaNetHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Bola net handler
     * @var \Football\Handler\BolaNetHandler
     */
    private $bolaNet;

    protected function setUp(): void
    {
        $env = \loadTestEnv();
        $this->bolaNet = new BolaNetHandler($env['API_KEY']);
    }

    public function testInstanceNotNull(): void
    {
        $this->assertNotNull($this->bolaNet);
    }

    public function testGetMatchSchedules(): void
    {
        //italian seria A
        $competitionId = 2019;

        $data = $this->bolaNet->getSchedules(
            BolaNetHandler::FOOTBALL_DATA_ORG,
            $competitionId,
            BolaNetHandler::ITALIAN_LEAGUE
        );

        $this->assertNotNull($data);
    }
}
