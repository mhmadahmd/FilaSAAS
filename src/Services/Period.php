<?php

namespace Mhmadahmd\Filasaas\Services;

use Carbon\Carbon;

class Period
{
    protected Carbon $startDate;

    protected Carbon $endDate;

    public function __construct(string $interval, int $period = 1, ?Carbon $startDate = null)
    {
        $this->startDate = $startDate ?? now();
        $this->endDate = $this->calculateEndDate($interval, $period);
    }

    protected function calculateEndDate(string $interval, int $period): Carbon
    {
        return match ($interval) {
            'day' => $this->startDate->copy()->addDays($period),
            'week' => $this->startDate->copy()->addWeeks($period),
            'month' => $this->startDate->copy()->addMonths($period),
            'year' => $this->startDate->copy()->addYears($period),
            default => $this->startDate->copy()->addMonths($period),
        };
    }

    public function getStartDate(): Carbon
    {
        return $this->startDate;
    }

    public function getEndDate(): Carbon
    {
        return $this->endDate;
    }
}
