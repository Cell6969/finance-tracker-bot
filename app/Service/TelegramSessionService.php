<?php

namespace App\Service;

use App\Models\Guest;
use App\Models\TelegramSession;

class TelegramSessionService
{
    public function getOrCreateSession(Guest $guest): TelegramSession
    {
        $session = TelegramSession::firstOrNew(['guest_id' => $guest->id]);

        return $session;
    }

    public function updateSession(TelegramSession $session, string $conversation, string $step, array $payload = []): TelegramSession
    {
        $session->conversation = $conversation;
        $session->step = $step;
        $session->payload = $payload;
        $session->save();

        return $session;
    }

    public function getSession(Guest $guest): ?TelegramSession
    {
        return TelegramSession::where('guest_id', $guest->id)->first();
    }

    public function destroySession(Guest $guest): void
    {
        TelegramSession::where('guest_id', $guest->id)->delete();
    }

    public function hasActiveSession(Guest $guest): bool
    {
        return TelegramSession::where('guest_id', $guest->id)->exists();
    }
}
