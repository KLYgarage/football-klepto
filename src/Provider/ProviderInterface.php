<?php declare(strict_types=1);

namespace Football\Provider;

/**
 * Interface for football data source
 * Provider
 */
interface ProviderInterface
{
    /**
     * List all available competitions
     * with/without criteria
     * @return array|\object
     */
    public function listCompetitions(array $filter, bool $convertToArray);

    /**
     * List matches
     * with/without criteria
     * @return array|\object
     */
    public function listMatches(array $filter, bool $convertToArray);

    /**
     * List all available areas
     * @return array|\object
     */
    public function listAreas(bool $convertToArray);
}
