# Product Requirements Document (PRD)

# Panda Account - Personal Financial Tracker

## 1. Overview

Panda Account merupakan aplikasi personal financial tracker yang memungkinkan pengguna mencatat pemasukan, pengeluaran, transfer antar dompet, serta melihat kondisi keuangan melalui dashboard.

Aplikasi memiliki dua media interaksi:

* Web Application (Laravel)
* Telegram Bot

Seluruh data berasal dari backend yang sama sehingga transaksi yang dibuat melalui Telegram langsung muncul di Web Dashboard.

---

# 2. Goals

MVP bertujuan untuk:

* Mencatat pemasukan
* Mencatat pengeluaran
* Mengelola beberapa wallet
* Transfer saldo antar wallet
* Dashboard sederhana
* Input transaksi melalui Telegram

---

# 3. User

Target user adalah pengguna individu yang ingin mencatat keuangan pribadi dengan cepat tanpa harus membuka website.

---

# 4. Platform

## Web

* Dashboard
* Wallet Management
* Category Management
* Transaction History
* Reports

## Telegram

* Quick Input
* Dashboard Summary
* Wallet Management

---

# 5. Core Modules

## 5.1 Guest

Menyimpan identitas user aplikasi.

Field utama:

* id
* username
* name
* telegram_username
* created_at

Rule:

* Telegram menggunakan username sebagai identitas.
* Jika username berubah maka dianggap sebagai akun berbeda (sesuai Terms of Use).

---

## 5.2 Wallet (Provider)

Media penyimpanan uang.

Contoh:

* BCA
* Mandiri
* Cash
* Dana
* OVO

Field:

* id
* guest_id
* name
* type
* balance

Type:

* bank
* ewallet
* cash
* investment

---

## 5.3 Category

Kategori transaksi.

Field:

* id
* guest_id
* name
* type

Type:

* income
* expense

Contoh Income:

* Gaji
* Bonus
* Freelance

Contoh Expense:

* Makan
* Transport
* Hiburan

---

## 5.4 Transaction

Semua aktivitas keuangan akan tercatat sebagai Transaction.

Field:

* id
* guest_id
* provider_id
* category_id (nullable)
* transaction_type
* amount
* note
* transaction_date

Transaction Type:

* income
* expense
* transfer

Rule:

Income

* Menambah saldo wallet

Expense

* Mengurangi saldo wallet

Transfer

* Tidak menggunakan category

---

## 5.5 Transfer

Detail perpindahan saldo.

Field:

* id
* transaction_id
* from_provider_id
* to_provider_id
* amount

Rule:

Setiap Transfer selalu memiliki Transaction.

Tidak semua Transaction adalah Transfer.

---

## 5.6 Telegram Session

Menyimpan state conversation Telegram.

Field:

* id
* guest_id
* conversation
* step
* payload (JSON)

Payload digunakan untuk menyimpan data sementara selama conversation.

Contoh:

```json
{
  "provider_id": 3,
  "category_id": 5,
  "amount": 100000
}
```

Session akan dihapus ketika conversation selesai.

---

# 6. Telegram Bot

## Menu Utama

* 💰 Pemasukan
* 💸 Pengeluaran
* 💳 Wallet
* 📊 Dashboard
* ⚙️ Pengaturan

Menu menggunakan Inline Keyboard.

---

# 7. Telegram Conversation

Contoh flow Tambah Wallet

User klik

Wallet

↓

Tambah Wallet

↓

Bot meminta nama wallet

↓

User mengirim text

↓

Bot meminta tipe wallet

↓

User memilih

↓

Bot meminta saldo awal

↓

User mengirim nominal

↓

Wallet dibuat

↓

Session dihapus

---

Contoh flow Income

Klik

Pemasukan

↓

Pilih kategori

↓

Pilih wallet

↓

Bot meminta nominal

↓

User mengirim

1000000 Gaji Juli

↓

Transaction dibuat

↓

Saldo wallet diperbarui

↓

Bot mengirim konfirmasi

---

# 8. Dashboard

Dashboard menampilkan:

* Total Saldo
* Total Income Bulan Ini
* Total Expense Bulan Ini
* Cash Flow
* Wallet Summary
* Recent Transactions

---

# 9. Business Rules

* User harus memiliki akun web sebelum menggunakan Telegram Bot.
* Telegram menggunakan username sebagai identitas akun.
* Setiap Transaction akan memengaruhi saldo wallet.
* Transfer menghasilkan:

  * 1 Transaction
  * 1 Transfer
* Income dan Expense wajib memiliki Category.
* Transfer tidak menggunakan Category.
* Satu user hanya memiliki satu Telegram Session aktif.
* Session dihapus setelah flow selesai atau dibatalkan.

---

# 10. MVP Scope

Included:

* Authentication
* Wallet CRUD
* Category CRUD
* Income
* Expense
* Transfer
* Dashboard
* Telegram Bot
* Telegram Session
* Web Dashboard

Excluded:

* Multi Currency
* Harga Emas/Saham
* Asset Conversion
* Scheduled Transaction
* OCR Receipt
* AI Financial Insight
* Budgeting
* Recurring Transaction
* Telegram Mini App

---

# 11. Future Roadmap

Version 2

* Telegram Mini App
* Asset Portfolio
* Gold Investment
* Stock Investment
* Mutual Fund
* Currency Conversion
* Budget Planner
* Financial Goal
* Monthly Report PDF
* AI Spending Analysis
