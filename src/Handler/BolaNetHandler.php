<?php declare(strict_types=1);

namespace Football\Handler;

use Football\Provider\FootballDataOrg;
use Football\Repository\ClubNameRepo;

class BolaNetHandler implements BolaNetHandlerInterface
{
    public const FOOTBALL_DATA_ORG = 'football-data.org';

    public const ITALIAN_LEAGUE = 'italia';

    /**
     * Club name repository
     * @var \Football\Repository\ClubNameRepo
     */
    private $clubRepo;

    /**
     * Api Key
     * @var string
     */
    private $apiKey;

    /**
     * Provider
     * @var \Football\Provider\ProviderInterface
     */
    private $provider;

    /**
     * Constructor
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->clubRepo = new ClubNameRepo();
    }

    /**
     * Get schedules
     * @throws \Exception
     */
    public function getSchedules(): array
    {
        if (func_num_args()) {
            switch (func_get_arg(0)) {
                case self::FOOTBALL_DATA_ORG:
                    $competitionId = func_get_arg(1);

                    $this->provider = new FootballDataOrg($this->apiKey);

                    return $this->formatFromFootballData(
                        $this->provider->getMatchesByCompetitionId($competitionId),
                        func_get_arg(2)
                    );

                default:
                    throw new \Exception('Error Processing Request', 1);
            }
        }
        throw new \Exception('Invalid number of arguments', 1);
    }

    /**
     * Format football-data.org to conform with
     * bola net
     * @param array|\object
     */
    private function formatFromFootballData($matches, string $league): array
    {
        if (! is_array($matches)) {
            $matches = (array) $matches;
        }

        if (isset($matches['matches'])) {
            $schedules = [];

            foreach ($matches['matches'] as $match) {
                $time = strtotime($match['utcDate']);
                array_push($schedules, [
                    'schedule' => $time,
                    'schedule_ina' => date('Y-m-d H:i:s', $time),
                    'club_home' => $match['homeTeam']['name'],
                    'club_home_bola' => $this->clubRepo->search([
                        $league,
                        $match['homeTeam']['name'],
                    ]),
                    'club_away' => $match['awayTeam']['name'],
                    'club_away_bola' => $this->clubRepo->search([
                        $league,
                        $match['awayTeam']['name'],

                    ]),
                    'url_detail_match' => FootballDataOrg::REST_SERVER
                    . FootballDataOrg::MATCH_ENDPOINT . '/' . $match['id'],
                    'week' => $match['matchday'],
                ]);
            }

            return $schedules;
        }
        return [];
    }
}
