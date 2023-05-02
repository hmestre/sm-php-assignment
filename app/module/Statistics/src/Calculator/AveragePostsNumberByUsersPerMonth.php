<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostsNumberByUsersPerMonth extends AbstractCalculator
{

    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $postsMonth = [];

    /**
     * @var array
     */
    private $usersMonth = [];


    /**
     * Undocumented function
     *
     * @param SocialPostTo $postTo
     * @return void
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $keyMonth = $postTo->getDate()->format('F');
        $this->postsMonth[$keyMonth]    = ($this->postsMonth[$keyMonth] ?? 0) + 1;
        $this->usersMonth[$keyMonth][]  = $postTo->getAuthorId();
    }

    /**
     * Undocumented function
     *
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        $stats = new StatisticsTo();
        foreach ($this->postsMonth as $splitPeriod => $total) {
            $uniqueUsersMonth = count(array_unique($this->usersMonth[$splitPeriod]));
            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($splitPeriod)
                ->setValue($total/$uniqueUsersMonth)
                ->setUnits(self::UNITS);

            $stats->addChild($child);
        }

        return $stats;
    }
}
