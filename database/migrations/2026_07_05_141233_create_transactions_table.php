<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');

            $table->string('type')->comment('income, expense, transfer')->index();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('idr');
            $table->string('description')->nullable();
            $table->date('transaction_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
