<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  $prefix=config('itmosoft-loyalty.prefix','');
  $accounts=$prefix.config('itmosoft-loyalty.accounts_table','itmosoft_loyalty_accounts');
  $transactions=$prefix.config('itmosoft-loyalty.transactions_table','itmosoft_loyalty_transactions');
  Schema::create($accounts,function(Blueprint $table) {
   $table->id(); $table->morphs('customer'); $table->unsignedBigInteger('balance')->default(0);
   $table->string('currency',32)->default('points'); $table->json('metadata')->nullable(); $table->timestamps();
   $table->unique(['customer_type','customer_id'],'itmosoft_loyalty_customer_unique');
  });
  Schema::create($transactions,function(Blueprint $table) use($accounts) {
   $table->id(); $table->foreignId('loyalty_account_id')->constrained($accounts)->cascadeOnDelete();
   $table->string('type',32); $table->integer('points'); $table->unsignedBigInteger('balance_after');
   $table->string('reason',191); $table->string('reference',191)->nullable(); $table->timestamp('expires_at')->nullable();
   $table->json('metadata')->nullable(); $table->timestamps();
   $table->index(['type','created_at'],'itmosoft_loyalty_type_created_idx'); $table->index('reference','itmosoft_loyalty_reference_idx');
  });
 }
 public function down(): void {
  $prefix=config('itmosoft-loyalty.prefix','');
  $transactions=$prefix.config('itmosoft-loyalty.transactions_table','itmosoft_loyalty_transactions');
  $accounts=$prefix.config('itmosoft-loyalty.accounts_table','itmosoft_loyalty_accounts');
  Schema::dropIfExists($transactions); Schema::dropIfExists($accounts);
 }
};
