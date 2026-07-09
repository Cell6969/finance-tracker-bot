<?php

namespace App\Service;

use App\Enums\ConversationStep;
use App\Models\Guest;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class IncomeBotHandler
{
    public function __construct(
        private CategoryService $categoryService,
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
        if ($data === 'action:income') {
            $this->startIncomeFlow($chatId, $guest);

            return;
        }

        $session = $guest->telegramSession;
        if (! $session) {
            return;
        }

        switch ($session->step) {
            case ConversationStep::IncomeSelectCategory->value:
                $this->selectCategory($chatId, $guest, $data);
                break;
            case ConversationStep::IncomeSelectWallet->value:
                $this->selectWallet($chatId, $guest, $data);
                break;
        }
    }

    private function handleMessage(int $chatId, Guest $guest, string $text): void
    {
        $session = $guest->telegramSession;
        if (! $session) {
            return;
        }

        if ($session->step === ConversationStep::IncomeInputAmount->value) {
            $this->processAmount($chatId, $guest, $text);
        }
    }

    private function startIncomeFlow(int $chatId, Guest $guest): void
    {
        $this->sessionService->updateSession(
            $this->sessionService->getOrCreateSession($guest),
            'income',
            ConversationStep::IncomeSelectCategory->value,
            []
        );

        $categories = $this->categoryService->getCategoriesByType($guest, 'income');

        if ($categories->isEmpty()) {
            $this->reply($chatId, '❌ Anda belum memiliki kategori pemasukan. Silakan buat di web terlebih dahulu.', $this->keyboardService->backToMenuKeyboard());

            return;
        }

        $keyboard = Keyboard::make()->inline();
        foreach ($categories as $category) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $category->name,
                    'callback_data' => "cat:{$category->id}",
                ]),
            ]);
        }
        $keyboard->row([
            Keyboard::inlineButton(['text' => '❌ Batalkan', 'callback_data' => 'action:cancel']),
        ]);

        $this->reply($chatId, '💰 *Pemasukan*\n\nSilakan pilih kategori:', $keyboard);
    }

    private function selectCategory(int $chatId, Guest $guest, string $data): void
    {
        if (! str_starts_with($data, 'cat:')) {
            return;
        }

        $categoryId = str_replace('cat:', '', $data);
        $payload = ['category_id' => $categoryId];

        $this->sessionService->updateSession(
            $guest->telegramSession,
            'income',
            ConversationStep::IncomeSelectWallet->value,
            $payload
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
                    'callback_data' => "wal:{$wallet->id}",
                ]),
            ]);
        }
        $keyboard->row([
            Keyboard::inlineButton(['text' => '❌ Batalkan', 'callback_data' => 'action:cancel']),
        ]);

        $this->reply($chatId, '💳 Silakan pilih dompet:', $keyboard);
    }

    private function selectWallet(int $chatId, Guest $guest, string $data): void
    {
        if (! str_starts_with($data, 'wal:')) {
            return;
        }

        $walletId = str_replace('wal:', '', $data);
        $payload = $guest->telegramSession->payload;
        $payload['account_id'] = $walletId;

        $this->sessionService->updateSession(
            $guest->telegramSession,
            'income',
            ConversationStep::IncomeInputAmount->value,
            $payload
        );

        $this->reply($chatId, '💵 Masukkan nominal dan catatan (opsional).\n\nContoh: `1000000 Gaji Juli`', $this->keyboardService->cancelKeyboard());
    }

    private function processAmount(int $chatId, Guest $guest, string $text): void
    {
        // Regex to extract number and description
        if (! preg_match('/^(\d+)(.*)$/', $text, $matches)) {
            $this->reply($chatId, '⚠️ Format salah. Masukkan angka terlebih dahulu.\n\nContoh: `1000000 Gaji Juli`', $this->keyboardService->cancelKeyboard());

            return;
        }

        $amount = (float) $matches[1];
        $description = trim($matches[2]);
        $payload = $guest->telegramSession->payload;

        try {
            $this->transactionService->createIncome($guest, [
                'account_id' => $payload['account_id'],
                'category_id' => $payload['category_id'],
                'amount' => $amount,
                'description' => $description,
            ]);

            $this->sessionService->destroySession($guest);
            $this->reply($chatId, '✅ Berhasil mencatat pemasukan sebesar *Rp '.number_format($amount, 0, ',', '.').'*', $this->keyboardService->backToMenuKeyboard());
        } catch (\Exception $e) {
            $this->reply($chatId, '❌ Terjadi kesalahan saat menyimpan transaksi.', $this->keyboardService->cancelKeyboard());
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
