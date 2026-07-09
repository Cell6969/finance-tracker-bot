<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

#[Signature('app:telegram-set-webhook')]
#[Description('Command description')]
class TelegramSetWebhook extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Telegram::setWebhook([
            'url' => config('telegram.bots.mybot.webhook_url'),
        ]);

        $this->info('Telegram webhook set successfully.');
    }
}
