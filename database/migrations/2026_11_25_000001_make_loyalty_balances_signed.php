<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('itmosoft-loyalty.prefix', '');
        $accounts = $prefix.config('itmosoft-loyalty.accounts_table', 'itmosoft_loyalty_accounts');
        $transactions = $prefix.config('itmosoft-loyalty.transactions_table', 'itmosoft_loyalty_transactions');

        Schema::table($accounts, function (Blueprint $table) {
            $table->bigInteger('balance')->default(0)->change();
        });

        Schema::table($transactions, function (Blueprint $table) {
            $table->bigInteger('balance_after')->default(0)->change();
        });
    }

    public function down(): void
    {
        // Keep signed columns on rollback: converting them to unsigned could
        // destroy valid negative balances created while allow_negative_balance is enabled.
    }
};
