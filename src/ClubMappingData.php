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

    /**
     * constructor
     * @param array|null $data
     */
    public function __construct(?array $data = null)
    {
        if (! empty($data)) {
            $this->clubNameMapping = $this->normalizeMappingData($data);
        } else {
            $this->initClubNameMapping();
        }
    }

    /**
     * Set club name mapping from custom source
     * @param array|null $data
     * @throws \Exception
     */
    public function setClubNameMappingData(?array $data = null): void
    {
        if (! empty($data)) {
            $this->clubNameMapping = $data;
        }
        throw new \Exception('Invalid data source', 1);
    }

    /**
     * Get all club names mapping
     * @return string[]
     */
    public function getClubNameMappingData(): array
    {
        if (! empty($this->clubNameMapping)) {
            return $this->clubNameMapping;
        }
        return [];
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
        try {
            return $this->clubNameMapping[$league][$clubName];
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n";
            return false;
        }
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

        $this->clubNameMapping = $this->normalizeMappingData(
            json_decode($file, true)
        );
    }

    /**
     * Normalize data
     */
    private function normalizeMappingData(?array $data = []): array
    {
        try {
            array_walk($data, function (&$v, $k): void {
                $v = array_merge(...array_filter($v, function ($value, $key) {
                    return is_numeric($key);
                }, ARRAY_FILTER_USE_BOTH));
            });

            return $data;
        } catch (\Throwable $e) {
            return $data;
        }
    }
}
