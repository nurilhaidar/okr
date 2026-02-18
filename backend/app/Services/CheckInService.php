<?php

namespace App\Services;

use App\Models\Objective;
use Carbon\Carbon;

class CheckInService
{
    /**
     * Get the period that needs a check-in for the given objective.
     *
     * @param Objective $objective
     * @return array|null Returns ['start' => Carbon, 'end' => Carbon] or null if no check-in needed
     */
    public function getPeriodNeedingCheckIn(Objective $objective): ?array
    {
        // If objective has no start date, cannot determine period
        if (!$objective->start_date) {
            return null;
        }

        $now = now();
        $startDate = Carbon::parse($objective->start_date);

        // If start date is in the future, no check-in needed yet
        if ($startDate->gt($now)) {
            return null;
        }

        // Get the current tracking period based on tracking_type
        $period = $this->getCurrentTrackingPeriod($objective->tracking_type, $startDate);

        // Check if a check-in already exists for this period
        if ($this->hasCheckInForPeriod($objective, $period)) {
            return null;
        }

        return $period;
    }

    /**
     * Get the current tracking period based on tracking type.
     *
     * @param string $trackingType
     * @param Carbon $startDate
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    protected function getCurrentTrackingPeriod(string $trackingType, Carbon $startDate): array
    {
        $now = now();

        return match ($trackingType) {
            'daily' => $this->getDailyPeriod($now),
            'weekly' => $this->getWeeklyPeriod($startDate, $now),
            'monthly' => $this->getMonthlyPeriod($startDate, $now),
            'quarterly' => $this->getQuarterlyPeriod($startDate, $now),
            default => $this->getDailyPeriod($now),
        };
    }

    /**
     * Get daily period (today).
     */
    protected function getDailyPeriod(Carbon $now): array
    {
        return [
            'start' => $now->copy()->startOfDay(),
            'end' => $now->copy()->endOfDay(),
        ];
    }

    /**
     * Get weekly period.
     * Week starts from the objective's start date.
     */
    protected function getWeeklyPeriod(Carbon $startDate, Carbon $now): array
    {
        $daysDiff = $startDate->diffInDays($now);
        $weeksElapsed = intdiv($daysDiff, 7);

        $periodStart = $startDate->copy()->addWeeks($weeksElapsed);
        $periodEnd = $periodStart->copy()->addDays(6)->endOfDay();

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
        ];
    }

    /**
     * Get monthly period.
     * Month starts from the objective's start date.
     */
    protected function getMonthlyPeriod(Carbon $startDate, Carbon $now): array
    {
        $monthsDiff = $startDate->diffInMonths($now);

        $periodStart = $startDate->copy()->addMonths($monthsDiff);
        $periodEnd = $periodStart->copy()->addMonth()->subDay()->endOfDay();

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
        ];
    }

    /**
     * Get quarterly period.
     * Quarter starts from the objective's start date.
     */
    protected function getQuarterlyPeriod(Carbon $startDate, Carbon $now): array
    {
        $monthsDiff = $startDate->diffInMonths($now);
        $quartersElapsed = intdiv($monthsDiff, 3);

        $periodStart = $startDate->copy()->addMonths($quartersElapsed * 3);
        $periodEnd = $periodStart->copy()->addMonths(3)->subDay()->endOfDay();

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
        ];
    }

    /**
     * Check if a check-in exists for the given period.
     */
    protected function hasCheckInForPeriod(Objective $objective, array $period): bool
    {
        return $objective->checkIns()
            ->whereBetween('date', [$period['start'], $period['end']])
            ->exists();
    }
}
