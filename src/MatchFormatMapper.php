<?php declare(strict_types=1);

namespace Football;

class MatchFormatMapper
{
    public const ITALIAN_LEAGUE = 'italia';

    /**
     * Club name mapping
     * @var string[]
     */
    private $clubNameMapping;

    /**
     * Constructor
     */
    public function __construct(?\Football\ClubMappingData $clubNameMapping = null)
    {
        date_default_timezone_set('asia/jakarta');

        if ($clubNameMapping === null) {
            $this->clubNameMapping = new ClubMappingData();
        }
    }

    /**
     * Format schedules from football-data.org
     * to conform with bola net
     * @param  array|\object $matches
     * @param  string $league
     */
    public function formatSchedulesToBolaNet($matches, $league): array
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
                    'club_home_bola' => $this->clubNameMapping->mapClubName(
                        $match['homeTeam']['name'],
                        $league
                    ),
                    'club_away' => $match['awayTeam']['name'],
                    'club_away_bola' => $this->clubNameMapping->mapClubName(
                        $match['awayTeam']['name'],
                        $league
                    ),
                    'url_detail_match' => Provider::REST_SERVER . Provider::MATCH_ENDPOINT . '/' . $match['id'],
                    'week' => $match['matchday'],
                ]);
            }

            return $schedules;
        }
        return [];
    }
}
