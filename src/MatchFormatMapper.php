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

    public function __construct()
    {
        date_default_timezone_set('asia/jakarta');

        $this->initClubNameMapping();
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
                    'club_home_bola' => $this->mapClubName(
                        $match['homeTeam']['name'],
                        $league
                    ),
                    'club_away' => $match['awayTeam']['name'],
                    'club_away_bola' => $this->mapClubName(
                        $match['awayTeam']['name'],
                        $league
                    ),
                    'url_away' => 'http://api.football-data.org/v2/teams/' . $match['awayTeam']['id'],
                    'url_detail_match' => 'http://api.football-data.org/v2/matches/' . $match['id'],
                    'week' => $match['matchday'],
                ]);
            }

            return $schedules;
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
     * Map club names received from football-data.org
     * @return string|bool
     */
    public function mapClubName(string $clubName, stirng $league)
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
        $this->clubNameMapping = [
            self::ITALIAN_LEAGUE => [
                'AC Milan' => 'AC Milan',
                'Atalanta BC' => 'Atalanta',
                'Benevento' => 'Benevento',
                'Bologna FC 1909' => 'Bologna FC',
                'Cagliari Calcio' => 'Cagliari',
                'AC Chievo Verona' => 'Chievo',
                'Crotone' => 'Crotone',
                'ACF Fiorentina' => 'Fiorentina',
                'Frosinone Calcio' => 'Frosinone',
                'Genoa CFC' => 'Genoa',
                'Hellas Verona' => 'Hellas Verona',
                'FC Internazionale Milano' => 'Inter Milan',
                'Juventus FC' => 'Juventus',
                'SS Lazio' => 'Lazio',
                'SSC Napoli' => 'Napoli',
                'AS Roma' => 'AS Roma',
                'Parma Calcio 1913' => 'Parma',
                'SPAL 2013' => 'SPAL',
                'UC Sampdoria' => 'Sampdoria',
                'US Sassuolo Calcio' => 'Sassuolo',
                'Torino FC' => 'Torino',
                'Udinese Calcio' => 'Udinese',
                'Empoli FC' => 'Empoli',
            ],
        ];
    }
}
