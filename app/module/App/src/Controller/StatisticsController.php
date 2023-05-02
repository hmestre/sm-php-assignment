<?php

namespace App\Controller;

use DateTime;
use SocialPost\Service\SocialPostService;
use Statistics\Builder\ParamsBuilder;
use Statistics\Enum\StatsEnum;
use Statistics\Extractor\StatisticsToExtractor;
use Statistics\Service\StatisticsService;

/**
 * Class StatisticsController
 *
 * @package App\Controller
 */
class StatisticsController extends Controller
{

    private const STAT_LABELS = [
        StatsEnum::TOTAL_POSTS_PER_WEEK                    => 'Total posts split by week',
        StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH => 'Average number of posts per user per month',
        StatsEnum::AVERAGE_POST_LENGTH                     => 'Average character length/post in a given date range',
        StatsEnum::MAX_POST_LENGTH                         => 'Longest post by character length in a given date range',
    ];

    /**
     * @var StatisticsService
     */
    private $statsService;

    /**
     * @var SocialPostService
     */
    private $socialService;

    /**
     * @var StatisticsToExtractor
     */
    private $extractor;

    /**
     * StatisticsController constructor.
     *
     * @param StatisticsService     $statsService
     * @param SocialPostService     $socialService
     * @param StatisticsToExtractor $extractor
     */
    public function __construct(
        StatisticsService $statsService,
        SocialPostService $socialService,
        StatisticsToExtractor $extractor
    ) {
        $this->statsService  = $statsService;
        $this->socialService = $socialService;
        $this->extractor     = $extractor;
    }

    /**
     * @param array $params
     */
    public function indexAction(array $params)
    {
        try {
            $startDate = $this->extractDate($params['start_date'] ?? null);
            $endDate   = $this->extractDate($params['end_date'] ?? null);
            $params    = ParamsBuilder::reportStatsParams($startDate, $endDate);

            $posts = $this->socialService->fetchPosts();
            $stats = $this->statsService->calculateStats($posts, $params);

            $response = [
                'stats' => $this->extractor->extract($stats, self::STAT_LABELS),
            ];
        } catch (\Throwable $throwable) {
            http_response_code(500);

            $response = ['message' => 'An error occurred'];
        }

        $this->render($response, 'json', false);
    }

    /**
     * @param ?string $date
     *
     * @return DateTime
     */
    private function extractDate(?string $date): DateTime
    {
        $dateTime = DateTime::createFromFormat('F, Y', $date);

        if (false === $dateTime) {
            $dateTime = new DateTime();
        }

        return $dateTime;
    }
}
