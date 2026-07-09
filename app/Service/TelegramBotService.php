<?php

namespace App\Service;

use App\Models\Guest;
use App\Models\TelegramSession;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class TelegramBotService
{
    private array $menuActions = [
        'action:income' => 'income',
        'action:expense' => 'expense',
        'action:transfer' => 'transfer',
        'action:wallet' => 'wallet',
        'action:dashboard' => 'dashboard',
        'action:setting' => 'setting',
    ];

    private array $walletSubActions = [
        'wallet:create' => 'wallet',
        'wallet:list' => 'wallet',
        'wallet:delete' => 'wallet',
    ];

    public function __construct(
        private WalletBotHandler $walletHandler,
        private IncomeBotHandler $incomeHandler,
        private ExpenseBotHandler $expenseHandler,
        private TransferBotHandler $transferHandler,
        private DashboardBotHandler $dashboardHandler,
        private TelegramSessionService $sessionService,
        private BotKeyboardService $keyboardService,
    ) {}

    public function handle(Update $update): void
    {
        if ($update->isType('callback_query')) {
            $this->handleCallback($update);

            return;
        }

        if ($update->isType('message')) {
            $this->handleMessage($update);

            return;
        }
    }

    private function handleCallback(Update $update): void
    {
        $callbackQuery = $update->getCallbackQuery();
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $data = $callbackQuery->getData();
        $guest = $this->resolveGuestFromCallback($update);

        if (! $guest) {
            $this->reply($chatId, '❌ Akun tidak ditemukan. Silakan daftar terlebih dahulu di web.');

            return;
        }

        // Back to main menu
        if ($data === 'action:back_start') {
            $this->sessionService->destroySession($guest);
            $this->replyMenu($chatId);

            return;
        }

        // Cancel active conversation
        if ($data === 'action:cancel') {
            $this->sessionService->destroySession($guest);
            $this->reply($chatId, '✅ Dibatalkan.', $this->mainMenuKeyboard());

            return;
        }

        // Main menu actions
        if (isset($this->menuActions[$data])) {
            $handlerName = $this->menuActions[$data];
            $this->dispatchHandler($handlerName, 'callback', $chatId, $guest, $data);

            return;
        }

        // Wallet sub-actions
        if (isset($this->walletSubActions[$data])) {
            $this->walletHandler->handle('callback', $chatId, $guest, $data);

            return;
        }

        // Active session conversation - route to handler
        $session = $guest->telegramSession;
        if ($session) {
            $this->routeToActiveHandler('callback', $chatId, $guest, $session, $data);

            return;
        }

        $this->reply($chatId, '⚠️ Action tidak valid.', $this->mainMenuKeyboard());
    }

    private function handleMessage(Update $update): void
    {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $text = $message->text ?? '';
        $guest = $this->resolveGuestFromMessage($update);

        if (! $guest) {
            $this->reply($chatId, '❌ Akun tidak ditemukan. Silakan daftar terlebih dahulu di web.');

            return;
        }

        // Command: /start
        if ($text === '/start') {
            $this->sessionService->destroySession($guest);
            $this->replyMenu($chatId);

            return;
        }

        // Command: /cancel
        if ($text === '/cancel') {
            $this->sessionService->destroySession($guest);
            $this->reply($chatId, '✅ Dibatalkan.', $this->mainMenuKeyboard());

            return;
        }

        // Route to active session handler
        $session = $guest->telegramSession;
        if ($session && $session->conversation !== 'idle') {
            $this->routeToActiveHandler('message', $chatId, $guest, $session, $text);

            return;
        }

        $this->reply($chatId, 'Silakan pilih menu:', $this->mainMenuKeyboard());
    }

    private function routeToActiveHandler(string $type, int $chatId, Guest $guest, TelegramSession $session, string $data): void
    {
        $conversation = $session->conversation;

        $handler = match (true) {
            str_starts_with($conversation, 'wallet') => $this->walletHandler,
            str_starts_with($conversation, 'income') => $this->incomeHandler,
            str_starts_with($conversation, 'expense') => $this->expenseHandler,
            str_starts_with($conversation, 'transfer') => $this->transferHandler,
            default => null,
        };

        if ($handler) {
            $handler->handle($type, $chatId, $guest, $data);

            return;
        }

        $this->sessionService->destroySession($guest);
        $this->reply($chatId, '⚠️ Session expired.', $this->mainMenuKeyboard());
    }

    private function dispatchHandler(string $handlerName, string $type, int $chatId, Guest $guest, string $data): void
    {
        $handler = match ($handlerName) {
            'wallet' => $this->walletHandler,
            'income' => $this->incomeHandler,
            'expense' => $this->expenseHandler,
            'transfer' => $this->transferHandler,
            'dashboard' => $this->dashboardHandler,
            default => null,
        };

        if ($handler) {
            $handler->handle($type, $chatId, $guest, $data);
        }
    }

    private function resolveGuestFromCallback(Update $update): ?Guest
    {
        $from = $update->getCallbackQuery()->getFrom();
        if (! $from) {
            return null;
        }

        $username = $from->getUsername();

        return Guest::where('username', $username)->first();
    }

    private function resolveGuestFromMessage(Update $update): ?Guest
    {
        $from = $update->getMessage()->getFrom();
        if (! $from) {
            return null;
        }

        $username = $from->getUsername();

        return Guest::where('username', $username)->first();
    }

    public function replyMenu(int $chatId): void
    {
        $text = "🐼 *Panda Account*\n\n"
            ."Selamat datang di Personal Financial Tracker!\n"
            .'Silakan pilih menu di bawah ini:';

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $this->mainMenuKeyboard(),
        ]);
    }

    public function mainMenuKeyboard(): Keyboard
    {
        return $this->keyboardService->mainMenuKeyboard();
    }

    public function cancelKeyboard(): Keyboard
    {
        return $this->keyboardService->cancelKeyboard();
    }

    public function backToMenuKeyboard(): Keyboard
    {
        return $this->keyboardService->backToMenuKeyboard();
    }

    private function reply(int $chatId, string $text, ?Keyboard $keyboard = null): void
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        if ($keyboard) {
            $params['reply_markup'] = $keyboard;
        }

        Telegram::sendMessage($params);
    }
}
