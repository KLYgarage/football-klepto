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
    public function getTeamById($id, bool $convertToArray)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTeamByCompetitionId(
        $competitionId = -1,
        array $filter = [
            'area' => '',
        ],
        bool $convertToArray = true
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
}
