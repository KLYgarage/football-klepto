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
        try {
            $competition = $this->getCompetitionByName(
                $filter['competitionName'],
                $filter
            );

            $nodeValueExpected = 'Pertandingan';

            $crawler = $this->crawlerGate(
                $competition['href'],
                'h2 > a'
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
            $urlParts = $this->splitSentence((string) $currentUrl, '/');
            $urlParts = array_filter($urlParts, function ($v) {
                $txt = $this->filterCharFromSentence($v);
                return is_numeric($txt);
            });
            $urlParts = array_values($urlParts);
            $roundId = $this->filterCharFromSentence(end(
                $urlParts
            ));

            $competitionId = $competition['id'];

            // crawl again
            $counter = 0;

            $gameWeek = 38;

            $matches = [];

            while ($counter < $gameWeek) {
                $param = [

                    'action' => 'changePage',
                    'block_id' => 'page_competition_1_block_competition_matches_summary_5',
                    'callback_params' => '{"page":"' . ($counter - 1) . '","block_service_id":"competition_summary_block_competitionmatchessummary","round_id":"' . $roundId . '","outgroup":"","view":"2","competition_id":"' . $competitionId . '"}',
                    'params' => '{"page":"' . ($counter) . '"}',

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


                    $timeParts = $this->splitSentence(str_replace(' ', '', trim($els->item(3)->nodeValue)), ':');

                    $matchTime = '';

                    if (count($timeParts) > 1) {
                        $matchTime = \DateTime::createFromFormat('d/m/y', trim($els->item(1)->nodeValue));

                        $matchTime->setTimeZone(new \DateTimeZone('Asia/Jakarta'));

                        $matchTime->setTime((int) $timeParts[0], (int) $timeParts[1], 0);
                    }

                    array_push($matches, [
                        'schedule_ina' => ($matchTime !== '') ? $matchTime->format('d-m-Y H:i') : '',
                        'club_home' => $clubHome,
                        'club_away' => $clubAway,
                        'url_detail_match' => $detailEl,
                        'week' => $counter + 1,
                    ]);
                }

                $counter++;
            }


            return $matches;
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n";

            return false;
        }
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
        bool $convertToArray
    ) {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getStandingsByCompetitionId(
        $competitionId,
        bool $convertToArray
    ) {
        return [];
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
        return ($domElement instanceof \DOMElement
            && $domElement->hasAttribute($attribute))
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
}
