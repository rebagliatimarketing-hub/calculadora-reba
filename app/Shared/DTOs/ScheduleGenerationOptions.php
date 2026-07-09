<?php

namespace App\Shared\DTOs;

use Carbon\CarbonImmutable;

class ScheduleGenerationOptions
{
    public function __construct(
        public CarbonImmutable $startDate,
        public array $allowedWeekdays = [6],
        public string $startTime = '10:00',
        public int $classDurationMinutes = 180,
        public string $frequencyType = 'monthly',
        public ?int $roomId = null,
        public ?int $zoomAccountId = null,
        public ?int $speakerId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            startDate: CarbonImmutable::parse($data['start_date']),
            allowedWeekdays: array_map('intval', $data['allowed_weekdays'] ?? [6]),
            startTime: $data['start_time'] ?? '10:00',
            classDurationMinutes: (int) ($data['class_duration_minutes'] ?? 180),
            frequencyType: $data['frequency_type'] ?? 'monthly',
            roomId: $data['room_id'] ?? null,
            zoomAccountId: $data['zoom_account_id'] ?? null,
            speakerId: $data['speaker_id'] ?? null,
        );
    }
}
