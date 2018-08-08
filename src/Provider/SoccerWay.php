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
     * Calling this function
     * actually user
     * could grab list areas
     */
    public function listCompetitions(
        array $filter = [
            self::QUERY_KEY_MENU => '',
        ],
        bool $convertToArray = true
    ) {
        return [];
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
        $rawHtml = (string) $this->webClient->request(
            'GET',
            self::COMPETITIONS_ENDPOINT . '?' . http_build_query($filter)
        )->getBody()->getContents();

        // grab list areas
        // signed by
        // [data-area_id]

        $xPath = $this->cssSelector->toXPath('li[data-area_id]');

        $crawler = new Crawler($rawHtml);

        $crawler = $crawler->filterXpath($xPath);

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
     * @inheritDoc
     */
    public function getCompetitionById(
        $id,
        bool $convertToArray
    ) {
        return [];
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

        return array_filter($areas, function ($v) use ($id) {
            return $v['id'] === (string) $id;
        });
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
}
