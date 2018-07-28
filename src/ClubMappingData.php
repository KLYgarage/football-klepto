<?php declare(strict_types=1);

namespace Football;

class ClubMappingData
{
    public const DEFAULT_JSON_FILE = 'club_name_mapping.json';

    /**
     * Club name mapping
     * @var string[]
     */
    private $clubNameMapping;

    public function __construct()
    {
        $this->initClubNameMapping();
    }

    /**
     * Get club name mapping based on league
     * @throws \Exception
     */
    public function getClubNameMapping(string $league): array
    {
        try {
            return $this->clubNameMapping[$league];
        } catch (\Throwable $e) {
            echo $e->getMessage();
            return [];
        }
    }

    /**
     * Map club name
     * @return string|bool
     */
    public function mapClubName(string $clubName, string $league)
    {
        foreach ($this->clubNameMapping[$league] as $key => $value) {
            if (isset($value[$clubName])) {
                return $value[$clubName];
            }
        }
        return false;
    }

    /**
     * Init club name mapping
     */
    private function initClubNameMapping(): void
    {
        $file = file_get_contents(getcwd() . '/' . self::DEFAULT_JSON_FILE);

        if (! $file) {
            throw new \Exception('JSON file not found', 1);
        }
        $this->clubNameMapping = json_decode($file, true);

        //print_r($this->clubNameMapping);
    }
}
