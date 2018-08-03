<?php declare(strict_types=1);

namespace Football\Repository;

/**
 * Class to provide
 * Club name mapping
 * From supported sources
 * JSON file and array
 */
class ClubNameRepo implements RepositoryInterface
{
    public const DEFAULT_FILE = 'club_name_mapping.json';

    /**
     * Default file path
     * @var string
     */
    private $defaultFilePath;

    /**
     * @var array<string[]>
     */
    private $clubNameMapping = [];

    /**
     * Constructor
     * @param null|string|array $args
     */
    public function __construct($args = null)
    {
        $this->defaultFilePath = getcwd() . '/' . self::DEFAULT_FILE;
        $this->load($args);
    }

    /**
     * @inheritDoc
     * @param  null|string|array $args
     * @throws \Exception
     */
    public function load($args = null): void
    {
        if ($args === null || is_string($args)) {
            $this->loadFromFile($args);
        } else {
            $this->loadFromVar(
                $this->normalizeMappingData($args)
            );
        }
    }

    /**
     * @inheritDoc
     * Map club name
     * @param  array $args
     * @return string|bool
     */
    public function search($args = null)
    {
        $this->filterArrayInstance($args);

        try {
            $league = array_shift($args);
            $clubName = array_shift($args);
            return $this->clubNameMapping[$league][$clubName];
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function list($args = null)
    {
        if (! empty($this->clubNameMapping)) {
            return $this->clubNameMapping;
        }
        return [];
    }

    /**
     * Load data from file
     * Prefered file is
     * php array saved with json_encode
     * Read more http://php.net/manual/en/function.unserialize.php
     * @throws \Exception
     */
    private function loadFromFile(?string $filePath = null): void
    {
        if ($filePath === null) {
            $filePath = $this->defaultFilePath;
        }

        $file = file_get_contents($filePath);

        if (! $file) {
            throw new \Exception('File Not Found', 1);
        }

        $this->clubNameMapping = $this->normalizeMappingData(
            json_decode($file, true)
        );
    }

    /**
     * Load data from variable
     */
    private function loadFromVar(array $var = []): void
    {
        $this->clubNameMapping = $var;
    }

    /**
     * Normalize data
     */
    private function normalizeMappingData(array $data = []): array
    {
        try {
            array_walk($data, function (&$v): void {
                $v = array_merge(...array_filter($v, function ($value, $key) {
                    return is_numeric($key);
                }, ARRAY_FILTER_USE_BOTH));
            });

            return $data;
        } catch (\Throwable $e) {
            return $data;
        }
    }

    /**
     * Check var is array
     * @param  mixed $x
     * @return mixed
     * @throws \Exception
     */
    private function filterArrayInstance($x)
    {
        if (is_array($x)) {
            return $x;
        }
        throw new \Exception('Array required', 1);
    }
}
