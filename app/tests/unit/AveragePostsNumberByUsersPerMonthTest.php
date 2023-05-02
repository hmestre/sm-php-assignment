<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;
use SocialPost\Dto\SocialPostTo;
use Statistics\Calculator\AveragePostsNumberByUsersPerMonth;

/**
 * Undocumented class
 */
class AveragePostsNumberByUsersPerMonthTest extends TestCase
{
    private $params;
    private $startDate;
    private $endDate;
    private $calculator;

    protected function setUp(): void
    {
        $this->startDate    = new \DateTime('2022-01-01');
        $this->endDate      = new \DateTime('2023-04-01');

        $this->params       = new ParamsTo();
        $this->params->setStatName(StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH)
                        ->setStartDate($this->startDate)
                        ->setEndDate($this->endDate);
        $this->calculator = new AveragePostsNumberByUsersPerMonth();
    }

     /**
     * no values
     *
     * @return void
     */
    public function test_should_calculate_stats_0_user_0_post(): void
    {
        $this->calculator->setParameters($this->params);
        $posts = [];
        $this->accumulatePosts($this->calculator, $posts);
        $result = $this->calculator->calculate();

        $this->assertCount(0, $result->getChildren());
    }

    /**
     * 1 post for user, the response needs to be 1
     *
     * @return void
     */
    public function test_should_calculate_stats_1_user_1_post_1_month(): void
    {
        $this->calculator->setParameters($this->params);
        $post = $this->createPosts(1,'dummy1', $this->startDate);
        $this->calculator->accumulateData($post[0]);
        $result = $this->calculator->calculate();
        $statsItem = $result->getChildren()[0];
        $this->assertEquals(1, $statsItem->getValue());
    }

    /**
     * 10 posts for a single author the response will need to be 10
     *
     * @return void
     */
    public function test_should_calculate_stats_1_user_10_post_1_month(): void
    {
        $this->calculator->setParameters($this->params);
        $posts = $this->createPosts(10, 'dummyAuthor1', $this->startDate);
        $this->accumulatePosts($this->calculator, $posts);
        $result = $this->calculator->calculate();
        $statsItem = $result->getChildren()[0];
        $this->assertEquals(10, $statsItem->getValue());
    }

    /**
     * 2 authors with 5 post each, the response must be 5
     *
     * @return void
     */
    public function test_should_calculate_stats_2_user_10_post_1_month(): void
    {
        $this->calculator->setParameters($this->params);

        $posts = $this->createPosts(5, 'dummyAuthor1', $this->startDate);
        $posts = array_merge($posts,$this->createPosts(5, 'dummyAuthor2', $this->startDate));
        $this->accumulatePosts($this->calculator, $posts);
        $result = $this->calculator->calculate();

        $statsItem = $result->getChildren()[0];
        $this->assertEquals(5, $statsItem->getValue());
    }


     /**
     * 1 author with 5 posts in 2 months, the response must be 4 for the 1st month and 1 for the second
     *
     * @return void
     */
    public function test_should_calculate_stats_1_user_5_post_2_month(): void
    {
        $this->calculator->setParameters($this->params);

        $secondMonth = clone $this->startDate;
        $secondMonth->add(new \DateInterval('P1M'));


        $posts = $this->createPosts(4, 'dummyAuthor1', $this->startDate);
        $posts = array_merge($posts,$this->createPosts(1, 'dummyAuthor1', $secondMonth));
        $this->accumulatePosts($this->calculator, $posts);
        $result = $this->calculator->calculate();

        $statsItem = $result->getChildren()[0];
        $this->assertEquals(4, $statsItem->getValue());

        $statsItem = $result->getChildren()[1];
        $this->assertEquals(1, $statsItem->getValue());
    }

     /**
     * 2 authors with 20 posts in 2 months, the response must be 4 for the 1st month and 1 for the second
     *
     * @return void
     */
    public function test_should_calculate_stats_2_user_20_post_2_month(): void
    {
        $this->calculator->setParameters($this->params);

        $secondMonth = clone $this->startDate;
        $secondMonth->add(new \DateInterval('P1M'));


        $posts = $this->createPosts(4, 'dummyAuthor1', $this->startDate);
        $posts = array_merge($posts,$this->createPosts(4, 'dummyAuthor1', $secondMonth));
        $posts = array_merge($posts,$this->createPosts(9, 'dummyAuthor2', $this->startDate));
        $posts = array_merge($posts,$this->createPosts(3, 'dummyAuthor2', $secondMonth));

        $this->accumulatePosts($this->calculator, $posts);
        $result = $this->calculator->calculate();

        $statsItem = $result->getChildren()[0];
        $this->assertEquals(6.5, $statsItem->getValue());

        $statsItem = $result->getChildren()[1];
        $this->assertEquals(3.5, $statsItem->getValue());
    }


    /**
     * 3 authors, 3 months and 20 posts
     * month1 author1 and author2 should be 2.5
     * month2 author2 should be 2
     * month3 all authors 1,2 and 3 should be 4.333333333333333
     *
     * @return void
     */
    public function test_should_calculate_stats_4_user_20_post_3_month(): void
    {
        $this->calculator->setParameters($this->params);

        $secondMonth = clone $this->startDate;
        $secondMonth->add(new \DateInterval('P1M'));
        $thirdMonth = clone $this->startDate;
        $thirdMonth->add(new \DateInterval('P6M'));


        $posts = $this->createPosts(2, 'dummyAuthor1', $this->startDate);
        $posts = array_merge($posts,$this->createPosts(3, 'dummyAuthor2', $this->startDate));
        $posts = array_merge($posts,$this->createPosts(2, 'dummyAuthor2', $secondMonth));
        $posts = array_merge($posts,$this->createPosts(1, 'dummyAuthor1', $thirdMonth));
        $posts = array_merge($posts,$this->createPosts(7, 'dummyAuthor2', $thirdMonth));
        $posts = array_merge($posts,$this->createPosts(5, 'dummyAuthor3', $thirdMonth));

        $this->accumulatePosts($this->calculator, $posts);
        $result = $this->calculator->calculate();

        $statsItem = $result->getChildren()[0];
        $this->assertEquals(2.5, $statsItem->getValue());

        $statsItem = $result->getChildren()[1];
        $this->assertEquals(2, $statsItem->getValue());

        $statsItem = $result->getChildren()[2];
        $this->assertEquals(4.333333333333333, $statsItem->getValue());
    }

    /**
     * create an array of SocialPostTo
     *
     * @param integer $numPosts
     * @param string $authorId
     * @param \DateTime $postDate
     * @return array
     */
    private static function createPosts(int $numPosts, string $authorId, \DateTime $postDate):array
    {
        $posts = [];
        for ($n = 1; $n <= $numPosts; $n++) {
            $post = new SocialPostTo();
            $post->setDate($postDate);
            $post->setAuthorId($authorId);
            $posts[] = $post;
        }
        return $posts;
    }

    /**
     * accumulate post for a calculator
     *
     * @param NoopCalculator $calculator
     * @param array $posts
     * @return void
     */
    function accumulatePosts(AveragePostsNumberByUsersPerMonth $calculator, array $posts) :void
    {
        foreach ($posts as $post) {
            $calculator->accumulateData($post);
        }
    }
}
