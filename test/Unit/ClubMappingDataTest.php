<?php declare(strict_types=1);

namespace Football\Test;

use Football\ClubMappingData;

class ClubMappingDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Club name mapping
     * @var \Football\ClubMappingData
     */
    private $clubNameMapping;

    protected function setUp(): void
    {
        $this->clubNameMapping = new ClubMappingData();
    }

    public function testInstanceNotNull(): void
    {
        $this->assertNotNull($this->clubNameMapping);
    }

    public function testDefaultData(): void
    {
        $this->assertNotEmpty(
            $this->clubNameMapping->getClubNameMappingData()
        );
    }

    /**
     * Test custom data not empty
     */
    public function testCustomDataNotEmpty(): \Football\ClubMappingData
    {
        $data = [
            'italia' => [
                'foo' => 'bar',
                'bazz' => 'bizz',
            ],
        ];

        $testMap = new ClubMappingData($data);

        $this->assertNotNull($testMap);

        $this->assertNotEmpty($testMap->getClubNameMappingData());

        return $testMap;
    }

    public function testGetDataDefaultByLeague(): void
    {
        $league = 'italia';

        $clubNames = $this->clubNameMapping->getClubNameMapping($league);

        $this->assertNotEmpty($clubNames);
    }

    /**
     * @depends testCustomDataNotEmpty
     */
    public function testGetDataCustomByLeague(\Football\ClubMappingData $testMap): void
    {
        $league = 'italia';

        $clubNames = $testMap->getClubNameMapping($league);

        $this->assertNotEmpty($clubNames);
    }

    public function testMapClubNameUsingDefaultData(): void
    {
        $league = 'italia';
        $clubName = 'Atalanta BC';
        $equi = $this->clubNameMapping->mapClubName($clubName, $league);
        $this->assertNotFalse($equi);
    }

    /**
     * @depends testCustomDataNotEmpty
     */
    public function testMapClubNameUsingCustomData(\Football\ClubMappingData $testMap): void
    {
        $league = 'italia';
        $clubName = 'foo';
        $equi = $testMap->mapClubName($clubName, $league);
        $this->assertNotFalse($equi);
    }
}
