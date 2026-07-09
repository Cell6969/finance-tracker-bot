<?php

namespace App\Service;

use App\Enums\ConversationStep;
use App\Models\Guest;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class ExpenseBotHandler
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
        if ($data === 'action:expense') {
            $this->startExpenseFlow($chatId, $guest);

            return;
        }

        $session = $guest->telegramSession;
        if (! $session) {
            return;
        }

        switch ($session->step) {
            case ConversationStep::ExpenseSelectCategory->value:
                $this->selectCategory($chatId, $guest, $data);
                break;
            case ConversationStep::ExpenseSelectWallet->value:
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

        if ($session->step === ConversationStep::ExpenseInputAmount->value) {
            $this->processAmount($chatId, $guest, $text);
        }
    }

    private function startExpenseFlow(int $chatId, Guest $guest): void
    {
        $this->sessionService->updateSession(
            $this->sessionService->getOrCreateSession($guest),
            'expense',
            ConversationStep::ExpenseSelectCategory->value,
            []
        );

        $categories = $this->categoryService->getCategoriesByType($guest, 'expense');

        if ($categories->isEmpty()) {
            $this->reply($chatId, '❌ Anda belum memiliki kategori pengeluaran. Silakan buat di web terlebih dahulu.', $this->keyboardService->backToMenuKeyboard());

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

        $this->reply($chatId, '💸 *Pengeluaran*\n\nSilakan pilih kategori:', $keyboard);
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
            'expense',
            ConversationStep::ExpenseSelectWallet->value,
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
            'expense',
            ConversationStep::ExpenseInputAmount->value,
            $payload
        );

        $this->reply($chatId, '💵 Masukkan nominal dan catatan (opsional).\n\nContoh: `50000 Makan Siang`', $this->keyboardService->cancelKeyboard());
    }

    private function processAmount(int $chatId, Guest $guest, string $text): void
    {
        if (! preg_match('/^(\d+)(.*)$/', $text, $matches)) {
            $this->reply($chatId, '⚠️ Format salah. Masukkan angka terlebih dahulu.\n\nContoh: `50000 Makan Siang`', $this->keyboardService->cancelKeyboard());

            return;
        }

        $amount = (float) $matches[1];
        $description = trim($matches[2]);
        $payload = $guest->telegramSession->payload;

        try {
            $this->transactionService->createExpense($guest, [
                'account_id' => $payload['account_id'],
                'category_id' => $payload['category_id'],
                'amount' => $amount,
                'description' => $description,
            ]);

            $this->sessionService->destroySession($guest);
            $this->reply($chatId, '✅ Berhasil mencatat pengeluaran sebesar *Rp '.number_format($amount, 0, ',', '.').'*', $this->keyboardService->backToMenuKeyboard());
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
