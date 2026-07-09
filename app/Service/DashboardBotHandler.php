<?php

namespace App\Service;

use App\Models\Guest;
use Telegram\Bot\Laravel\Facades\Telegram;

class DashboardBotHandler
{
    public function __construct(
        private DashboardService $dashboardService,
        private BotKeyboardService $keyboardService,
    ) {}

    public function handle(string $type, int $chatId, Guest $guest, string $data): void
    {
        if ($data === 'action:dashboard') {
            $this->showDashboard($chatId, $guest);
        }
    }

    private function showDashboard(int $chatId, Guest $guest): void
    {
        $data = $this->dashboardService->getDashboardData($guest);

        $text = "📊 *Dashboard Keuangan*\n\n"
            .'💰 *Total Saldo:* Rp '.number_format($data['total_balance'], 0, ',', '.')."\n\n"
            .'📈 *Pemasukan Bulan Ini:* Rp '.number_format($data['monthly_income'], 0, ',', '.')."\n"
            .'📉 *Pengeluaran Bulan Ini:* Rp '.number_format($data['monthly_expense'], 0, ',', '.')."\n\n"
            ."💳 *Dompet:*\n";

        foreach ($data['wallets'] as $wallet) {
            $text .= "  • {$wallet['name']} ({$wallet['type_label']}): Rp ".number_format($wallet['balance'], 0, ',', '.')."\n";
        }

        $text .= "\n📋 *Transaksi Terbaru:*\n";
        foreach ($data['recent_transactions'] as $tx) {
            $text .= "  {$tx['type_icon']} {$tx['type_label']}: Rp ".number_format($tx['amount'], 0, ',', '.')." ({$tx['description']})\n";
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $this->keyboardService->backToMenuKeyboard(),
        ]);
    }
}
