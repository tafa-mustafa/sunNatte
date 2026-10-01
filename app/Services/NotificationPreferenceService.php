<?php

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;

class NotificationPreferenceService
{
    public function matrix(User $user): array
    {
        $stored = NotificationPreference::where('user_id', $user->id)->get()->keyBy('event');
        $events = [];
        foreach (config('notifications') as $event => $defaults) {
            $row = $stored->get($event);
            $events[] = [
                'event' => $event,
                'push' => $row ? (bool) $row->push : (bool) $defaults['push'],
                'sms' => $row ? (bool) $row->sms : (bool) $defaults['sms'],
                'email' => $row ? (bool) $row->email : (bool) $defaults['email'],
            ];
        }

        return $events;
    }

    public function allows(User $user, string $event, string $channel): bool
    {
        $defaults = config('notifications.'.$event, ['push' => true, 'sms' => false, 'email' => false]);
        if (! $user->exists) {
            return (bool) ($defaults[$channel] ?? false);
        }

        $row = NotificationPreference::where('user_id', $user->id)->where('event', $event)->first();
        if (! $row) {
            return (bool) ($defaults[$channel] ?? false);
        }

        return (bool) $row->{$channel};
    }

    public function update(User $user, string $event, array $channels): array
    {
        if (! config()->has('notifications.'.$event)) {
            return ['ok' => false, 'message' => 'Type d\'événement inconnu.'];
        }

        $pref = NotificationPreference::updateOrCreate(
            ['user_id' => $user->id, 'event' => $event],
            [
                'push' => array_key_exists('push', $channels) ? (bool) $channels['push'] : true,
                'sms' => array_key_exists('sms', $channels) ? (bool) $channels['sms'] : false,
                'email' => array_key_exists('email', $channels) ? (bool) $channels['email'] : false,
            ]
        );

        return ['ok' => true, 'preference' => $pref];
    }
}
