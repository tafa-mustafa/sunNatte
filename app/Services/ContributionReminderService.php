<?php

namespace App\Services;

use App\Models\Tirage;
use App\Notifications\ContributionDueNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ContributionReminderService
{
    public function dueOn(?Carbon $now = null): string
    {
        return ($now ?? now())->copy()->addDays(2)->toDateString();
    }

    public function cacheKey(int $tontineId, int $userId, string $date): string
    {
        return "cotisation-j2:{$tontineId}:{$userId}:{$date}";
    }

    public function message(string $tontineNom, string $date): string
    {
        return "Rappel : cotisation pour {$tontineNom} dans 2 jours ({$date}).";
    }

    public function remind(?Carbon $now = null): int
    {
        $date = $this->dueOn($now);
        $sent = 0;

        $tirages = Tirage::with(['tontine.users'])
            ->whereDate('date_versement', $date)
            ->get();

        foreach ($tirages as $tirage) {
            $tontine = $tirage->tontine;
            if (! $tontine) {
                continue;
            }

            foreach ($tontine->users as $member) {
                $key = $this->cacheKey($tontine->id, $member->id, $date);
                if (Cache::has($key)) {
                    continue;
                }

                $message = $this->message($tontine->nom, $date);
                $due = new ContributionDueNotification($tontine, $date);
                app(NotificationDispatcher::class)->send($member, 'rappel_cotisation', $message, $due);
                Cache::put($key, true, now()->addDays(3));
                $sent++;
            }
        }

        return $sent;
    }
}
