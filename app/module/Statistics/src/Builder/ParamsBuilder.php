<?php

namespace Statistics\Builder;

use DateTime;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;

/**
 * Class ParamsBuilder
 *
 * @package Statistics\Builder
 */
class ParamsBuilder
{

    /**
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return ParamsTo[]
     */
    public static function reportStatsParams(DateTime $start, DateTime $end): array
    {
        $startDate = (clone $start)->modify('first day of this month')->setTime(0, 0, 0);
        $endDate   = (clone $end)->modify('last day of this month')->setTime(23, 59, 59);

        return [
            (new ParamsTo())
                ->setStatName(StatsEnum::AVERAGE_POST_LENGTH)
                ->setStartDate($startDate)
                ->setEndDate($endDate),
            (new ParamsTo())
                ->setStatName(StatsEnum::MAX_POST_LENGTH)
                ->setStartDate($startDate)
                ->setEndDate($endDate),
            (new ParamsTo())
                ->setStatName(StatsEnum::TOTAL_POSTS_PER_WEEK)
                ->setStartDate($startDate)
                ->setEndDate($endDate),
            (new ParamsTo())
                ->setStatName(StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH)
                ->setStartDate($startDate)
                ->setEndDate($endDate),
        ];
    }
}
