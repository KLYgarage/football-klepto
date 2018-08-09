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

    public const QUERY_KEY_MENU = 'ICID';

    public const RESULT_AND_SCHEDULES = 'TN_01';

    public const COMPETITIONS = 'TN_02';

    public const TEAMS_ENDPOINT = '/teams/club-teams';

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

        $area = [];

        if (is_numeric($filter['area'])) {
            $areaId = (int) $filter['area'];
            $area = $this->getAreaById($areaId);
        } else {
            $areaName = $filter['area'];
            $area = $this->getAreaByName($areaName);
        }

        // if area
        // has already
        // been got
        // create url
        // to grab html
        // from WEB_URL + href
        $crawler = $this->crawlerGate(
            $area['href'],
            'div[id*=page_match_1_block_competition_left_tree] > ul > li'
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
                    'id' => end($hrefParts),
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
     * @inheritDoc
     */
    public function listMatches(array $filter, bool $convertToArray)
    {
        return [];
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
            //$areaNodes = $domElement->getElementsByTagName('a');
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
     * @param  bool|boolean $convertToArray
     * @return array|\object
     */
    public function getAreaByName(string $areaName, bool $convertToArray = true)
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
    public function getTeamById($id, bool $convertToArray)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTeamByCompetitionId(
        $competitionId,
        array $filter,
        bool $convertToArray
    ) {
        return [];
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
     * @param  \DOMElement|\DOMNodeList $domElement
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
    private function crawlerGate(string $endpoint = '', string $css = ''): \Symfony\Component\DomCrawler\Crawler
    {
        $rawHtml = (string) $this->webClient->request(
            'GET',
            $endpoint
        )->getBody()->getContents();

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
}
