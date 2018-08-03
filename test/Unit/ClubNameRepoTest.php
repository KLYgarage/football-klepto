<?php declare(strict_types=1);

namespace Football\Test;

use Football\Repository\ClubNameRepo;

class ClubNameRepoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Club name repo
     * @var \Football\Repository\ClubNameRepo
     */
    private $clubNameRepo;

    protected function setUp(): void
    {
        $this->clubNameRepo = new ClubNameRepo();
    }

    public function testInstanceNotNull(): void
    {
        $this->assertNotNull($this->clubNameRepo);
    }

    public function testGetDefaultData(): void
    {
        $data = $this->clubNameRepo->list();

        $this->assertNotNull($data);

        $this->assertNotEmpty($data);
    }

    public function testDataFromVar(): void
    {
        $data = [
            'italia' => [
                'foo' => 'bar',
                'bazz' => 'bizz',
            ],
        ];

        $this->clubNameRepo = new ClubNameRepo($data);

        $this->assertNotNull($this->clubNameRepo);

        $data = $this->clubNameRepo->list();

        $this->assertNotNull($data);

        $this->assertNotEmpty($data);
    }

    public function testDataFromFilePath(): void
    {
        $filePath = getcwd() . '/' . 'club_name_mapping.json';

        $this->clubNameRepo = new ClubNameRepo($filePath);

        $data = $this->clubNameRepo->list();

        $this->assertNotNull($data);

        $this->assertNotEmpty($data);
    }

    public function testMapClubName(): void
    {
        $league = 'italia';

        $clubName = 'Atalanta BC';

        $result = $this->clubNameRepo->search(
            [$league, $clubName]
        );

        $this->assertNotFalse($result);
    }
}
