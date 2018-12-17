<?php declare(strict_types=1);

namespace Football\Provider;

use \GuzzleHttp\Client;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class to crawling in
 * https://id.soccerway.com
 */
class SoccerWay implements ProviderInterface
{
    public const WEB_URL = 'https://id.soccerway.com';

    public const CONTENT_ENDPOINT = '/a/block_teams_index_club_teams';

    public const COMPETITION_MATCHES_SUMMARY_ENDPOINT = '/a/block_competition_matches_summary';

    public const COMPETITION_TABLE_ENDPOINT = '/a/block_competition_tables';

    public const MATCH_FIELD = 'Pertandingan';

    public const DEFAULT_GAME_WEEK = 38;

    public const QUERY_KEY_MENU = 'ICID';

    public const COMPETITIONS = 'TN_02';

    public const COMPETITIONS_ENDPOINT = '/competitions';

    /**
     * Web client
     * @var \GuzzleHttp\Client
     */
    private $webClient;

    /**
     * Css selector translator
     * @var \Symfony\Component\CssSelector\CssSelectorConverter
     */
    private $cssSelector;

    public function __construct()
    {
        $this->webClient = new Client(
            ['base_uri' => self::WEB_URL]
        );

        $this->cssSelector = new CssSelectorConverter();
    }

    /**
     * @inheritDoc
     * Filter criteria
     * is based on area
     * id or name
     */
    public function listCompetitions(
        array $filter = [
            'area' => '',
        ],
        bool $convertToArray = true
    ) {
        if (empty($filter['area'])) {
            throw new \Exception('Filter criteria is required', 1);
        }

        $areaId = is_numeric($filter['area']) ? (int) $filter['area']
         : $this->getAreaByName($filter['area'])['id'];

        $param = [
            'action' => 'expandItem',
            'block_id' => 'page_teams_1_block_teams_index_club_teams_2',
            'callback_params' => '{"level":"1"}',
            'params' => '{"area_id":"' . $areaId . '","level":"2","item_key":"area_id"}',

        ];


        $crawler = $this->crawlerGate(
            self::CONTENT_ENDPOINT,
            'ul > li',
            $param
        );

        $competitions = [];

        foreach ($crawler as $competitionNode) {
            $els = $competitionNode->getElementsByTagName('a');
            $el = $this->peekDOMNodeList($els);
            $hrefParts = array_values(
                array_filter(
                    $this->splitSentence(
                        $el->getAttribute('href'),
                        '/'
                    )
                )
            );
            array_push(
                $competitions,
                [
                    'id' => $this->filterCharFromSentence(end($hrefParts)),
                    'competition_name' => trim($el->nodeValue),
                    'href' => $el->getAttribute('href'),
                ]
            );
        }

        return $competitions;
    }

    /**
     * @inheritDoc
     */
    public function getCompetitionById(
        $id,
        array $filter = [
            'area' => '',
        ],
        bool $convertToArray = true
    ) {
        $competitions = $this->listCompetitions($filter);

        return current(
            array_filter($competitions, function ($v) use ($id) {
                return $v['id'] === $id;
            })
        );
    }

    /**
     * Get competition by name
     */
    public function getCompetitionByName(
        string $competitionName = '',
        array $filter = [
            'area' => '',
        ]
    ): array {
        $competitions = $this->listCompetitions($filter);

        return current(
            array_filter($competitions, function ($v) use ($competitionName) {
                return $competitionName === $v['competition_name'];
            })
        );
    }

    /**
     * @inheritDoc
     */
    public function listMatches(
        array $filter = [
            'area' => '',
            'competitionName' => '',
        ],
        bool $convertToArray = true
) {
        $competition = $this->getCompetitionByName(
            $filter['competitionName'],
            $filter
        );

        $roundId = $this->getRoundId($competition['href'], 'h2 > a');

        $competitionId = $competition['id'];

        // crawl again
        $counter = 0;

        $matches = [];

        while ($counter < self::DEFAULT_GAME_WEEK) {
            $param = [

                'action' => 'changePage',
                'block_id' => 'page_competition_1_block_competition_matches_summary_5',
                'callback_params' => '{"page":"' . ($counter - 1) . '","block_service_id":"competition_summary_block_competitionmatchessummary","round_id":"' . $roundId . '","outgroup":"","view":"2","competition_id":"' . $competitionId . '"}',
                'params' => '{"page":"' . $counter . '"}',

            ];

            $matchCrawler = $this->crawlerGate(
                self::COMPETITION_MATCHES_SUMMARY_ENDPOINT,
                'table > tbody > tr',
                $param
            );

            foreach ($matchCrawler as $matchNode) {
                $els = $matchNode->getElementsByTagName('td');
                // get time
                // column 2 date
                // column 4 time
                // column 3, club home
                $clubHome = trim($els->item(2)->nodeValue);

                $clubAway = trim($els->item(4)->nodeValue);

                $detailEl = $els->item($els->length - 1)->getElementsByTagName('a')->item(0)->getAttribute('href');

                array_push($matches, [
                    'id' => $this->getIdFromHref($detailEl),
                    'schedule_ina' => $this->createMatchTime(
                        $els->item(1)->nodeValue,
                        $els->item(3)->nodeValue
                    ),
                    'club_home' => $clubHome,
                    'club_away' => $clubAway,
                    'url_detail_match' => $detailEl,
                    'week' => $counter + 1,
                ]);
            }

            $counter++;
        }


        return $matches;
    }

    /**
     * @inheritDoc
     */
    public function listAreas(
        array $filter = [
            self::QUERY_KEY_MENU => '',
        ],
        bool $convertToArray = true
    ) {
        $crawler = $this->crawlerGate(
            self::COMPETITIONS_ENDPOINT . '?' . http_build_query($filter),
            'li[data-area_id]'
        );

        $areas = [];

        foreach ($crawler as $areaNode) {
            $area = [
                'id' => $this->getValueByAttribute('data-area_id', $areaNode),
                'area_name' => trim($areaNode->nodeValue),
            ];

            $aTagEls = $areaNode->getElementsByTagName('a');

            $area['href'] = $this->getValueByAttribute('href', $aTagEls);

            array_push($areas, $area);
        }

        return $areas;
    }

    /**
     * Get area by name
     * @return array|\object
     */
    public function getAreaByName(string $areaName)
    {
        //load area list
        //by calling listAreas
        //filter area_name to match
        //with expected areaName

        $filter = [
            self::QUERY_KEY_MENU => self::COMPETITIONS,
        ];

        $areas = $this->listAreas($filter);

        return current(array_filter($areas, function ($v) use ($areaName) {
            return strtolower($v['area_name']) === strtolower($areaName);
        }));
    }

    /**
     * @inheritDoc
     */
    public function getAreaById($id, bool $convertToArray = true)
    {
        //load area list
        //by calling listAreas
        //filter id field to match
        //with expected id

        $filter = [
            self::QUERY_KEY_MENU => self::COMPETITIONS,
        ];

        $areas = $this->listAreas($filter);

        return current(array_filter($areas, function ($v) use ($id) {
            return $v['id'] === (string) $id;
        }));
    }

    /**
     * @inheritDoc
     */
    public function getTeamById(
        $id,
        array $filter = [
            'area' => '',
        ],
        bool $convertToArray = true
    ) {
        if (empty($filter['area'])) {
            throw new \Exception('Filter criteria is required', 1);
        }

        $competitions = $this->listCompetitions($filter);

        foreach ($competitions as $competition) {
            $teams = $this->getTeamByCompetitionId(
                $competition['id']
            );

            $team = array_filter($teams, function ($v) use ($id) {
                return $v['id'] === (string) $id;
            });

            if (! empty($team)) {
                return current(
                    $team
                );
            }
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTeamByCompetitionId(
        $competitionId,
        array $filter = [
            'area' => '',
        ],
        bool $convertToArray = true
    ) {
        $param = [
            'action' => 'expandItem',
            'block_id' => 'page_teams_1_block_teams_index_club_teams_2',
            'callback_params' => '{"level":"2"}',
            'params' => '{"competition_id":"' . $competitionId . '","level":"3","item_key":"competition_id"}',

        ];

        $crawler = $this->crawlerGate(
            self::CONTENT_ENDPOINT,
            'ul > li',
            $param
        );

        $teams = [];

        foreach ($crawler as $teamNodes) {
            $els = $teamNodes->getElementsByTagName('a');
            $el = $this->peekDOMNodeList($els);
            $hrefParts = array_values(
                array_filter(
                    $this->splitSentence(
                        $el->getAttribute('href'),
                        '/'
                    )
                )
            );
            array_push(
                $teams,
                [
                    'id' => $this->filterCharFromSentence(end($hrefParts)),
                    'team_name' => trim($el->nodeValue),
                    'href' => $el->getAttribute('href'),
                ]
            );
        }

        return $teams;
    }

    /**
     * @inheritDoc
     */
    public function getMatchById(
        $matchId,
        array $filter = [
            'competitionId' => '',
            'area' => '',
            'competitionName' => '',
        ],
        bool $convertToArray = true
    ) {
        $matches = $this->listMatches($filter);

        $match = array_values(array_filter($matches, function ($v) use ($matchId) {
            return $v['id'] === $matchId;
        }));

        return end($match);
    }

    /**
     * @inheritDoc
     */
    public function getStandingsByCompetitionId(
        $competitionId,
        array $filter = [
            'area' => '',
        ],
        bool $convertToArray = true
    ) {
        $competition = $this->getCompetitionById($competitionId, $filter);

        $roundId = $this->getRoundId($competition['href'], 'h2 > a');

        $seasonId = $this->getSeasonId($competition['href'], '#season_id_selector > option');

        $param = [

            'action' => 'changeTable',
            'block_id' => 'page_competition_1_block_competition_tables_7',
            'callback_params' => '{"season_id":' . $seasonId . ',"round_id":' . $roundId . ',"outgroup":false,"competition_id":' . $competitionId . ',"new_design_callback":false}',
            'params' => '{"type":"competition_league_table"}',

        ];

        $standingsCrawler = $this->crawlerGate(
            self::COMPETITION_TABLE_ENDPOINT,
            'table > tbody > tr',
            $param
        );

        $standings = [];


        foreach ($standingsCrawler as $standing) {
            $els = $standing->getElementsByTagName('td');
            $rank = trim($els->item(0)->nodeValue);
            $clubName = trim($els->item(2)->nodeValue);
            $play = trim($els->item(3)->nodeValue);
            $win = trim($els->item(4)->nodeValue);
            $draw = trim($els->item(5)->nodeValue);
            $lose = trim($els->item(6)->nodeValue);
            $points = trim($els->item(10)->nodeValue);
            array_push(
                $standings,
                ['rank' => $rank,
                    'clubName' => $clubName,
                    'play' => $play,
                    'win' => $win,
                    'draw' => $draw,
                    'lose' => $lose,
                    'points' => $points, ]
            );
        }

        return $standings;
    }

    /**
     * Get value of domElement by
     * @param  \DOMElement|\DOMNodeList|\Symfony\Component\DomCrawler\Crawler $domElement
     * @return array|string
     */
    private function getValueByAttribute(string $attribute, $domElement)
    {
        $values = [];
        if (is_iterable($domElement)) {
            foreach ($domElement as $node) {
                if ($node->hasAttribute($attribute)) {
                    $values[] = $node->getAttribute($attribute);
                }
            }
            if (count($values) === 1) {
                return array_shift($values);
            }
            return empty($values) ? '' : $values;
        }
        return $domElement instanceof \DOMElement
            && $domElement->hasAttribute($attribute)
        ? $domElement->getAttribute($attribute) : '';
    }

    /**
     * Crawling request gate
     */
    private function crawlerGate(
        string $endpoint = '',
        string $css = '',
        array $param = ['']
    ): \Symfony\Component\DomCrawler\Crawler {
        $resp = (string) $this->webClient->request(
            'GET',
            $endpoint . '?' . http_build_query($param)
        )->getBody()->getContents();

        // try to decode

        $rawHtml = $resp;

        if (json_decode($resp, true)) {
            $resp = json_decode($resp, true);
            $rawHtml = $resp['commands'][0]['parameters']['content'];
        }

        $crawler = new Crawler($rawHtml);

        $xPath = $this->cssSelector->toXPath($css);

        return $crawler->filterXpath($xPath);
    }

    /**
     * Peek element of DOMNOdeList
     */
    private function peekDOMNodeList(\DOMNodeList $DOMNodeList): ?\DOMElement
    {
        if (! empty($DOMNodeList)) {
            return $DOMNodeList->item(0);
        }
        return new \DOMElement('', '', '');
    }

    /**
     * Split sentence by delimiter
     */
    private function splitSentence(string $sentence = '', string $delimiter = ''): array
    {
        return explode($delimiter, $sentence);
    }

    /**
     * Filter char from sentence
     * Only picks number
     */
    private function filterCharFromSentence(string $str): string
    {
        $result = preg_replace('/[^0-9]/', '', $str);

        return ! empty($result) ? $result : $str;
    }

    /**
     * Peek array
     * @return mixed
     */
    private function peekArray(array $array)
    {
        $top = current($array);
        return $top ?: [];
    }

    /**
     * create match time
     */
    private function createMatchTime(string $date = '', string $time = '', string $format = 'd-m-Y H:i'): string
    {
        if (! empty($date) && ! empty($time)) {
            $timeParts = $this->splitSentence(str_replace(' ', '', trim($time)), ':');


            if (count($timeParts) > 1) {
                $matchTime = \DateTime::createFromFormat('d/m/y', trim($date));

                $matchTime->setTimeZone(new \DateTimeZone('Asia/Jakarta'));

                $matchTime->setTime((int) $timeParts[0], (int) $timeParts[1], 0);

                return $matchTime->format($format);
            }

            return '';
        }

        return '';
    }

    /**
     * Get round id from competition
     * By crawling to competition href
     */
    private function getRoundId(string $competitionHref = '', string $css = ''): string
    {
        $nodeValueExpected = self::MATCH_FIELD;

        $crawler = $this->crawlerGate(
            $competitionHref,
            $css
        )->reduce(function (Crawler $node) use ($nodeValueExpected) {
            return strtolower(
                trim($node->text())
            ) === strtolower($nodeValueExpected);
        });

        // get current url

        $currentUrl = $this->getValueByAttribute('href', $crawler);

        // if it is array
        // peek
        if (is_array($currentUrl)) {
            $currentUrl = $this->peekArray($currentUrl);
        }
        // get round_id
        // usually stores
        // in url
        $urlParts = array_values(
            array_filter($this->splitSentence((string) $currentUrl, '/'), function ($v) {
                $txt = $this->filterCharFromSentence($v);
                return is_numeric($txt);
            })
        );

        return $this->filterCharFromSentence(
            end(
                $urlParts
            )
        );
    }

    /**
     * Get season id
     * currently
     * it is located in select button
     */
    private function getSeasonId(string $competitionHref = '', string $css = ''): string
    {
        $crawler = $this->crawlerGate(
            $competitionHref,
            $css
        );
        $filtered = [];
        foreach ($crawler as $node) {
            if (! empty($node->attributes->getNamedItem('selected'))) {
                array_push($filtered, $node);
            }
        }

        $urlParts = array_values(
            array_filter($this->splitSentence((string) $this->getValueByAttribute('value', $filtered), '/'), function ($v) {
                $txt = $this->filterCharFromSentence($v);
                return is_numeric($txt);
            })
        );

        return $this->filterCharFromSentence(end($urlParts));
    }

    /**
     * Get id from href
     * Usually id takes place
     * on the last segment
     */
    private function getIdFromHref(string $href): string
    {
        $urlParts = $this->splitSentence($href, '/');
        $urlParts = array_values(array_filter($urlParts, function ($v) {
            return is_numeric($v);
        }));
        return end($urlParts);
    }
}
