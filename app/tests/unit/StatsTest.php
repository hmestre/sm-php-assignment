<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;
use Statistics\Extractor\StatisticsToExtractor;
use Statistics\Calculator\Factory\StatisticsCalculatorFactory;

class CalculatorTest extends TestCase
{

    /**
     * @path to data json
     */
    const TEST_DATA_PATH        = __DIR__ . '/../data/';
    const TEST_DATA_FILE_NAME   = 'social-posts-response.json';
    private $startDate;
    private $endDate;


    protected function setUp(): void
    {
        $this->startDate    = new \DateTime('2018-08-10T17:08:53+00:00');
        $this->endDate      = new \DateTime('2018-08-11T06:38:54+00:00');

    }


    /**
     * @return array
     */
    public function calculationDataProvider(): array
    {
        return [
            [
                StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH,
                1,
                ['children',0,'children',0,'value'],
                'Average post number per user per month'
            ],
            [
                StatsEnum::AVERAGE_POST_LENGTH,
                495.25,
                ['children',0,'value'],
                'Average post length'
            ],
            [
                StatsEnum::MAX_POST_LENGTH,
                638,
                ['children',0,'value'],
                'Max post length'
            ],
            [
                StatsEnum::TOTAL_POSTS_PER_WEEK,
                4,
                ['children',0,'children',0,'value'],
                'Total posts per week'
            ]
            // add more test cases here
        ];
    }

    /**
     * @dataProvider calculationDataProvider
     */
    public function test_Calculate_factory(
        string $statName,
        float $expected,
        array $expectedResultPath,
        string $legend
    ): void
    {

        $this->assertFileExists(self::TEST_DATA_PATH . self::TEST_DATA_FILE_NAME);

        $response       = file_get_contents(self::TEST_DATA_PATH . self::TEST_DATA_FILE_NAME);
        $responseData   = empty($response) ? null : \GuzzleHttp\json_decode($response, true);
        $posts          = $responseData['data']['posts'] ?? null;

        $this->assertNotNull($posts);

        $hydrator   = new \SocialPost\Hydrator\FictionalPostHydrator;
        $param      = new ParamsTo();
        $param->setStatName($statName)
            ->setStartDate($this->startDate)
            ->setEndDate($this->endDate);

        $calculator = StatisticsCalculatorFactory::create([$param]);
        $extractor  = new StatisticsToExtractor();

        foreach ($posts as $postData) {
            $post = $hydrator->hydrate($postData);
            $calculator->accumulateData($post);
        }

        $stats      = $calculator->calculate();
        $response   = $extractor->extract($stats, [$statName => '']);

        $this->assertEquals($expected, self::getExpectedValue($response,$expectedResultPath),$legend);
    }

    /**
     * return the expected value
     *
     * @param array $response
     * @param array $path
     * @return float
     */
    private function getExpectedValue(array $response, array $path):float
    {
        $value = $response;
        foreach ($path as $key) {
          if (is_array($value) && array_key_exists($key, $value)) {
            $value = $value[$key];
          } else {
            return null;
          }
        }
        return $value;
    }

}
