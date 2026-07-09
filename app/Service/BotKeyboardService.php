<?php

namespace App\Service;

use Telegram\Bot\Keyboard\Keyboard;

class BotKeyboardService
{
    public function mainMenuKeyboard(): Keyboard
    {
        return Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '💰 Pemasukan',
                    'callback_data' => 'action:income',
                ]),
                Keyboard::inlineButton([
                    'text' => '💸 Pengeluaran',
                    'callback_data' => 'action:expense',
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '🔄 Transfer',
                    'callback_data' => 'action:transfer',
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '💳 Dompet',
                    'callback_data' => 'action:wallet',
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '📊 Dashboard',
                    'callback_data' => 'action:dashboard',
                ]),
            ]);
    }

    public function cancelKeyboard(): Keyboard
    {
        return Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '❌ Batalkan',
                    'callback_data' => 'action:cancel',
                ]),
            ]);
    }

    public function backToMenuKeyboard(): Keyboard
    {
        return Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '🏠 Menu Utama',
                    'callback_data' => 'action:back_start',
                ]),
            ]);
    }
}
