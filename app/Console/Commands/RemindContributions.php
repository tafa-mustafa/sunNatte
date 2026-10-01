<?php

namespace App\Console\Commands;

use App\Services\ContributionReminderService;
use Illuminate\Console\Command;

class RemindContributions extends Command
{
    protected $signature = 'tontines:remind-contributions';

    protected $description = 'Envoie les rappels de cotisation J-2 (push database)';

    public function handle(ContributionReminderService $reminders): int
    {
        $sent = $reminders->remind();
        $this->info("Rappels envoyés : {$sent}");

        return self::SUCCESS;
    }
}
