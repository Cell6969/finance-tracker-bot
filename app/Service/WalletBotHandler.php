<?php

namespace App\Service;

use App\Enums\ConversationStep;
use App\Enums\WalletType;
use App\Models\Guest;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class WalletBotHandler
{
    public function __construct(
        private WalletService $walletService,
        private TelegramSessionService $sessionService,
        private BotKeyboardService $keyboardService,
    ) {}

    public function handle(string $type, int $chatId, Guest $guest, string $data): void
    {
        if ($type === 'callback') {
            $this->handleCallback($chatId, $guest, $data);
        } else {
            $this->handleMessage($chatId, $guest, $data);
        }
    }

    private function handleCallback(int $chatId, Guest $guest, string $data): void
    {
        if ($data === 'action:wallet') {
            $this->showWalletMenu($chatId, $guest);

            return;
        }

        $session = $guest->telegramSession;
        if (! $session) {
            return;
        }

        switch ($session->step) {
            case ConversationStep::WalletMenu->value:
                $this->processWalletMenu($chatId, $guest, $data);
                break;
            case ConversationStep::WalletCreateType->value:
                $this->selectWalletType($chatId, $guest, $data);
                break;
            case ConversationStep::WalletDeleteConfirm->value:
                $this->processDeleteConfirm($chatId, $guest, $data);
                break;
        }
    }

    private function handleMessage(int $chatId, Guest $guest, string $text): void
    {
        $session = $guest->telegramSession;
        if (! $session) {
            return;
        }

        switch ($session->step) {
            case ConversationStep::WalletCreateName->value:
                $this->processWalletName($chatId, $guest, $text);
                break;
            case ConversationStep::WalletCreateBalance->value:
                $this->processWalletBalance($chatId, $guest, $text);
                break;
        }
    }

    private function showWalletMenu(int $chatId, Guest $guest): void
    {
        $this->sessionService->updateSession(
            $this->sessionService->getOrCreateSession($guest),
            'wallet',
            ConversationStep::WalletMenu->value,
            []
        );

        $keyboard = Keyboard::make()->inline()
            ->row([
                Keyboard::inlineButton(['text' => '➕ Tambah Dompet', 'callback_data' => 'wallet:create']),
            ])
            ->row([
                Keyboard::inlineButton(['text' => '📋 Daftar Dompet', 'callback_data' => 'wallet:list']),
            ])
            ->row([
                Keyboard::inlineButton(['text' => '🗑️ Hapus Dompet', 'callback_data' => 'wallet:delete']),
            ])
            ->row([
                Keyboard::inlineButton(['text' => '🏠 Menu Utama', 'callback_data' => 'action:back_start']),
            ]);

        $this->reply($chatId, '💳 *Menu Dompet*\n\nSilakan pilih:', $keyboard);
    }

    private function processWalletMenu(int $chatId, Guest $guest, string $data): void
    {
        switch ($data) {
            case 'wallet:create':
                $this->startCreateWallet($chatId, $guest);
                break;
            case 'wallet:list':
                $this->listWallets($chatId, $guest);
                break;
            case 'wallet:delete':
                $this->startDeleteWallet($chatId, $guest);
                break;
        }
    }

    private function startCreateWallet(int $chatId, Guest $guest): void
    {
        $this->sessionService->updateSession(
            $guest->telegramSession,
            'wallet',
            ConversationStep::WalletCreateName->value,
            []
        );

        $this->reply($chatId, '💳 *Tambah Dompet*\n\nSilakan masukkan nama dompet:', $this->keyboardService->cancelKeyboard());
    }

    private function processWalletName(int $chatId, Guest $guest, string $text): void
    {
        $name = trim($text);
        if (empty($name)) {
            $this->reply($chatId, '⚠️ Nama dompet tidak boleh kosong. Silakan masukkan nama:', $this->keyboardService->cancelKeyboard());

            return;
        }

        $this->sessionService->updateSession(
            $guest->telegramSession,
            'wallet',
            ConversationStep::WalletCreateType->value,
            ['name' => $name]
        );

        $keyboard = Keyboard::make()->inline();
        foreach (WalletType::toArray() as $value => $label) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $label,
                    'callback_data' => "type:{$value}",
                ]),
            ]);
        }
        $keyboard->row([
            Keyboard::inlineButton(['text' => '❌ Batalkan', 'callback_data' => 'action:cancel']),
        ]);

        $this->reply($chatId, "Nama dompet: *{$name}*\n\nSilakan pilih tipe dompet:", $keyboard);
    }

    private function selectWalletType(int $chatId, Guest $guest, string $data): void
    {
        if (! str_starts_with($data, 'type:')) {
            return;
        }

        $type = str_replace('type:', '', $data);
        $payload = $guest->telegramSession->payload;
        $payload['type'] = $type;

        $this->sessionService->updateSession(
            $guest->telegramSession,
            'wallet',
            ConversationStep::WalletCreateBalance->value,
            $payload
        );

        $this->reply($chatId, '💵 Masukkan saldo awal (angka saja):', $this->keyboardService->cancelKeyboard());
    }

    private function processWalletBalance(int $chatId, Guest $guest, string $text): void
    {
        if (! preg_match('/^\d+$/', $text)) {
            $this->reply($chatId, '⚠️ Format salah. Masukkan angka saja:', $this->keyboardService->cancelKeyboard());

            return;
        }

        $payload = $guest->telegramSession->payload;

        try {
            $this->walletService->createWallet($guest, [
                'name' => $payload['name'],
                'type' => $payload['type'],
                'balance' => (float) $text,
            ]);

            $this->sessionService->destroySession($guest);
            $this->reply($chatId, "✅ Dompet *{$payload['name']}* berhasil dibuat!", $this->keyboardService->backToMenuKeyboard());
        } catch (\Exception $e) {
            $this->reply($chatId, '❌ Terjadi kesalahan saat membuat dompet.', $this->keyboardService->cancelKeyboard());
        }
    }

    private function listWallets(int $chatId, Guest $guest): void
    {
        $wallets = $this->walletService->getWalletsForGuest($guest);

        if ($wallets->isEmpty()) {
            $this->reply($chatId, '❌ Anda belum memiliki dompet.', $this->keyboardService->backToMenuKeyboard());

            return;
        }

        $text = "📋 *Daftar Dompet*\n\n";
        foreach ($wallets as $wallet) {
            $text .= "• {$wallet->name} ({$wallet->type_label}): Rp ".number_format($wallet->balance, 0, ',', '.')."\n";
        }

        $this->reply($chatId, $text, $this->keyboardService->backToMenuKeyboard());
    }

    private function startDeleteWallet(int $chatId, Guest $guest): void
    {
        $wallets = $this->walletService->getWalletsForGuest($guest);

        if ($wallets->isEmpty()) {
            $this->reply($chatId, '❌ Anda belum memiliki dompet.', $this->keyboardService->backToMenuKeyboard());

            return;
        }

        $this->sessionService->updateSession(
            $guest->telegramSession,
            'wallet',
            ConversationStep::WalletDeleteConfirm->value,
            []
        );

        $keyboard = Keyboard::make()->inline();
        foreach ($wallets as $wallet) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => "{$wallet->name} - Rp ".number_format($wallet->balance, 0, ',', '.'),
                    'callback_data' => "del_wal:{$wallet->id}",
                ]),
            ]);
        }
        $keyboard->row([
            Keyboard::inlineButton(['text' => '❌ Batalkan', 'callback_data' => 'action:cancel']),
        ]);

        $this->reply($chatId, '🗑️ *Hapus Dompet*\n\nPilih dompet yang akan dihapus:', $keyboard);
    }

    private function processDeleteConfirm(int $chatId, Guest $guest, string $data): void
    {
        if (! str_starts_with($data, 'del_wal:')) {
            return;
        }

        $walletId = str_replace('del_wal:', '', $data);

        try {
            $wallet = $guest->accounts()->findOrFail($walletId);
            $this->walletService->deleteWallet($wallet);
            $this->sessionService->destroySession($guest);
            $this->reply($chatId, '✅ Dompet berhasil dihapus.', $this->keyboardService->backToMenuKeyboard());
        } catch (\Exception $e) {
            $this->reply($chatId, '❌ Terjadi kesalahan saat menghapus dompet.', $this->keyboardService->cancelKeyboard());
        }
    }

    private function reply(int $chatId, string $text, ?Keyboard $keyboard = null): void
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];
        if ($keyboard) {
            $params['reply_markup'] = $keyboard;
        }
        Telegram::sendMessage($params);
    }
}
