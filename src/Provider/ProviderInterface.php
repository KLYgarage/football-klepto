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
     * @return array|\object|boolean
     */
    public function listMatches(array $filter, bool $convertToArray);

    /**
     * List all available areas
     * with/without criteria
     * @return array|\object
     */
    public function listAreas(array $filter, bool $convertToArray);

    /**
     * List one particular competition
     * @param  int|string          $id
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function getCompetitionById(
        $id,
        array $filter,
        bool $convertToArray
    );

    /**
     * List one particular area.
     * @param  int|string $id
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function getAreaById($id, bool $convertToArray);

    /**
     * Show one particular team
     * @param  int|string $id
     * @return array|\object
     */
    public function getTeamById(
        $id,
        array $filter,
        bool $convertToArray
);

    /**
     * List all teams for a particular competition
     * @param  int|string $competitionId
     * @return array|\object
     */
    public function getTeamByCompetitionId(
        $competitionId,
        array $filter,
        bool $convertToArray
    );

    /**
     * Show one particular match
     * @param  int|string          $matchId
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function getMatchById(
        $matchId,
        bool $convertToArray
    );

    /**
     * Show Standings for a particular competition
     * @param  int|string
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function getStandingsByCompetitionId(
        $competitionId,
        array $filter,
        bool $convertToArray
    );
}
