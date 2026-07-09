<?php

namespace App\Http\Controllers;

use App\Service\TelegramBotService;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookController extends Controller
{
    public function __construct(
        protected TelegramBotService $telegramBotService
    ) {}

    public function handle()
    {
        $update = Telegram::getWebhookUpdate();
        $this->telegramBotService->handle($update);

        return response()->json(['ok' => true]);
    }
}
