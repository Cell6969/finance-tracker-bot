<?php

namespace App\Service;

use App\Enums\ConversationStep;
use App\Models\Guest;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TransferBotHandler
{
    public function __construct(
        private WalletService $walletService,
        private TransactionService $transactionService,
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
        if ($data === 'action:transfer') {
            $this->startTransferFlow($chatId, $guest);

            return;
        }

        $session = $guest->telegramSession;
        if (! $session) {
            return;
        }

        switch ($session->step) {
            case ConversationStep::TransferSelectFrom->value:
                $this->selectFromWallet($chatId, $guest, $data);
                break;
            case ConversationStep::TransferSelectTo->value:
                $this->selectToWallet($chatId, $guest, $data);
                break;
        }
    }

    private function handleMessage(int $chatId, Guest $guest, string $text): void
    {
        $session = $guest->telegramSession;
        if (! $session) {
            return;
        }

        if ($session->step === ConversationStep::TransferInputAmount->value) {
            $this->processAmount($chatId, $guest, $text);
        }
    }

    private function startTransferFlow(int $chatId, Guest $guest): void
    {
        $this->sessionService->updateSession(
            $this->sessionService->getOrCreateSession($guest),
            'transfer',
            ConversationStep::TransferSelectFrom->value,
            []
        );

        $wallets = $this->walletService->getWalletsForGuest($guest);
        if ($wallets->isEmpty()) {
            $this->reply($chatId, '❌ Anda belum memiliki dompet. Silakan buat di web terlebih dahulu.', $this->keyboardService->backToMenuKeyboard());

            return;
        }

        $keyboard = Keyboard::make()->inline();
        foreach ($wallets as $wallet) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $wallet->name,
                    'callback_data' => "from_wal:{$wallet->id}",
                ]),
            ]);
        }
        $keyboard->row([
            Keyboard::inlineButton(['text' => '❌ Batalkan', 'callback_data' => 'action:cancel']),
        ]);

        $this->reply($chatId, '🔄 *Transfer Saldo*\n\nSilakan pilih dompet asal:', $keyboard);
    }

    private function selectFromWallet(int $chatId, Guest $guest, string $data): void
    {
        if (! str_starts_with($data, 'from_wal:')) {
            return;
        }

        $fromWalletId = str_replace('from_wal:', '', $data);
        $payload = ['from_account_id' => $fromWalletId];

        $this->sessionService->updateSession(
            $guest->telegramSession,
            'transfer',
            ConversationStep::TransferSelectTo->value,
            $payload
        );

        $wallets = $this->walletService->getWalletsForGuest($guest);
        $keyboard = Keyboard::make()->inline();
        foreach ($wallets as $wallet) {
            if ($wallet->id == $fromWalletId) {
                continue;
            }

            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $wallet->name,
                    'callback_data' => "to_wal:{$wallet->id}",
                ]),
            ]);
        }
        $keyboard->row([
            Keyboard::inlineButton(['text' => '❌ Batalkan', 'callback_data' => 'action:cancel']),
        ]);

        $this->reply($chatId, '💳 Silakan pilih dompet tujuan:', $keyboard);
    }

    private function selectToWallet(int $chatId, Guest $guest, string $data): void
    {
        if (! str_starts_with($data, 'to_wal:')) {
            return;
        }

        $toWalletId = str_replace('to_wal:', '', $data);
        $payload = $guest->telegramSession->payload;
        $payload['to_account_id'] = $toWalletId;

        $this->sessionService->updateSession(
            $guest->telegramSession,
            'transfer',
            ConversationStep::TransferInputAmount->value,
            $payload
        );

        $this->reply($chatId, '💵 Masukkan nominal dan catatan (opsional).\n\nContoh: `50000 Bayar Hutang`', $this->keyboardService->cancelKeyboard());
    }

    private function processAmount(int $chatId, Guest $guest, string $text): void
    {
        if (! preg_match('/^(\d+)(.*)$/', $text, $matches)) {
            $this->reply($chatId, '⚠️ Format salah. Masukkan angka terlebih dahulu.\n\nContoh: `50000 Bayar Hutang`', $this->keyboardService->cancelKeyboard());

            return;
        }

        $amount = (float) $matches[1];
        $description = trim($matches[2]);
        $payload = $guest->telegramSession->payload;

        try {
            $this->transactionService->createTransfer($guest, [
                'from_account_id' => $payload['from_account_id'],
                'to_account_id' => $payload['to_account_id'],
                'amount' => $amount,
                'description' => $description,
            ]);

            $this->sessionService->destroySession($guest);
            $this->reply($chatId, '✅ Berhasil transfer sebesar *Rp '.number_format($amount, 0, ',', '.').'*', $this->keyboardService->backToMenuKeyboard());
        } catch (\Exception $e) {
            $this->reply($chatId, '❌ Terjadi kesalahan saat memproses transfer.', $this->keyboardService->cancelKeyboard());
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
