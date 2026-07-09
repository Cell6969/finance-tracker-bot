<?php

namespace App\Enums;

enum ConversationStep: string
{
    // Income flow
    case IncomeSelectCategory = 'income:select_category';
    case IncomeSelectWallet = 'income:select_wallet';
    case IncomeInputAmount = 'income:input_amount';

    // Expense flow
    case ExpenseSelectCategory = 'expense:select_category';
    case ExpenseSelectWallet = 'expense:select_wallet';
    case ExpenseInputAmount = 'expense:input_amount';

    // Wallet flow
    case WalletMenu = 'wallet:menu';
    case WalletCreateName = 'wallet:create_name';
    case WalletCreateType = 'wallet:create_type';
    case WalletCreateBalance = 'wallet:create_balance';
    case WalletDeleteConfirm = 'wallet:delete_confirm';

    // Transfer flow
    case TransferSelectFrom = 'transfer:select_from';
    case TransferSelectTo = 'transfer:select_to';
    case TransferInputAmount = 'transfer:input_amount';

    // Dashboard
    case Dashboard = 'dashboard:show';
}
