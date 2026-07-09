<?php

namespace Tests\Unit;

use App\Modules\Launches\Services\LaunchScoringService;
use PHPUnit\Framework\TestCase;

class LaunchScoringServiceTest extends TestCase
{
    public function test_it_calculates_weighted_score(): void
    {
        $score = (new LaunchScoringService)->calculate([
            'market_demand' => 100,
            'survey_interest' => 80,
            'specialty_recurrence' => 60,
            'commercial_opportunity' => 70,
            'operational_ease' => 50,
            'differentiation' => 40,
        ]);

        $this->assertSame(77, $score);
    }
}
