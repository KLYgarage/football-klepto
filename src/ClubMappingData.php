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
        if ($data !== null && ! empty($data)) {
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
        if ($data !== null && ! empty($data)) {
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
        if ($this->clubNameMapping !== null && isset($this->clubNameMapping)) {
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
        $keys = array_keys($data);

        foreach ($data as $key => &$value) {
            if (! $this->hasKeyStrings($value)) {
                $value = array_merge(...$value);
            }
        }
        return empty($normalized) ? $data : $normalized;
    }

    /**
     * Check if array contains string key
     * @param  mixed[] $array
     */
    private function hasKeyStrings(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}
