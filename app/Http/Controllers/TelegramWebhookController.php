<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Prompts\Key;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookController extends Controller
{
    private $botAction = [
        'action:expense' => '💸 Pengeluaran',
        'action:income' => '💰 Pemasukan',
        'action:wallet' => '💳 Dompet',
        'action:asset' => '📊 Aset',
        'action:report' => '📈 Laporan',
        'action:setting' => '⚙️ Pengaturan',
    ];

    public function handle()
    {
        $update = Telegram::getWebhookUpdate();
        $message = $update->getMessage();
        logger()->info($update);

        // Handle if the message is a callback query
        if ($update->isType('callback_query')) {
            $callbackQuery = $update->getCallbackQuery();
            $chatId = $callbackQuery->getMessage()->getChat()->getId();
            $data = $callbackQuery->getData();

            // Handle the callback query data
            if (isset($this->botAction[$data])) {
                $responseText = 'Anda memilih: ' . $this->botAction[$data];
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $responseText,
                ]);
            } else {
                $responseText = 'Data callback tidak valid.';
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $responseText,
                ]);
            }
            return response()->json(['ok' => true]);
        }

        // Handle if the messsage is a command
        $text = $message->text;
        if ($text === '/start') {
            $chatId = $message->getChat()->getId();
            $responseText = 'Selamat datang di Keuangan Panda! Silakan pilih salah satu opsi di bawah ini:';

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $responseText,
                'reply_markup' => $this->home(),
            ]);
        }

        return response()->json(['ok' => true]);
    }


    private function home() : Keyboard
    {
        return Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '💸 Pengeluaran',
                    'callback_data' => 'action:expense',
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '💰 Pemasukan',
                    'callback_data' => 'action:income',
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '💳 Dompet',
                    'callback_data' => 'action:wallet',
                ])
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '💳 Aset',
                    'callback_data' => 'action:asset',
                ])
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '📊 Dashboard',
                    'callback_data' => 'action:dashboard',
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '⚙️ Pengaturan',
                    'callback_data' => 'action:setting',
                ]),
            ]);
    }
}
