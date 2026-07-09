<?php

namespace App\Modules\Launches\Services;

class LaunchScoringService
{
    public function calculate(array $data): int
    {
        $weights = [
            'market_demand' => 0.30,
            'survey_interest' => 0.25,
            'specialty_recurrence' => 0.15,
            'commercial_opportunity' => 0.15,
            'operational_ease' => 0.10,
            'differentiation' => 0.05,
        ];

        $score = 0;

        foreach ($weights as $field => $weight) {
            $value = max(0, min(100, (int) ($data[$field] ?? 0)));
            $score += $value * $weight;
        }

        return (int) round($score);
    }
}
