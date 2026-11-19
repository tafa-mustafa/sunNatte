<?php

namespace Tests\Unit;

use App\Services\ContributionReminderService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ContributionReminderServiceTest extends TestCase
{
    public function test_due_date_is_two_days_ahead(): void
    {
        $service = new ContributionReminderService;
        $now = Carbon::parse('2026-10-01');

        $this->assertSame('2026-10-03', $service->dueOn($now));
        $this->assertSame(
            'Rappel : cotisation pour GIE Liberté 6 dans 2 jours (2026-10-03).',
            $service->message('GIE Liberté 6', '2026-10-03')
        );
        $this->assertSame('cotisation-j2:4:9:2026-10-03', $service->cacheKey(4, 9, '2026-10-03'));
    }
}
