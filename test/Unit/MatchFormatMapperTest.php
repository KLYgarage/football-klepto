<?php declare(strict_types=1);

namespace Football\Test;

use Football\MatchFormatMapper;
use Football\Provider;

class MatchFormatMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Provider
     * @var \Football\Provider
     */
    private $provider;

    /**
     * Formatter
     * @var \Football\MatchFormatMapper
     */
    private $formatter;

    protected function setUp(): void
    {
        $env = \loadTestEnv();

        $this->provider = new Provider(
            $env['API_KEY']
        );

        $this->formatter = new MatchFormatMapper();
    }

    public function testInstanceNotNull(): void
    {
        $this->assertNotNull($this->provider);

        $this->assertNotNull($this->formatter);
    }

    public function testFormatSchedules(): void
    {
        //italian seria A
        $competitionId = 2019;

        $matches = $this->provider->getMatchesByCompetitionId($competitionId);

        $bolaNetSchedules = $this->formatter->formatSchedulesToBolaNet(
            $matches,
            $this->formatter::ITALIAN_LEAGUE
        );

        $this->assertNotNull($bolaNetSchedules);
    }
}
