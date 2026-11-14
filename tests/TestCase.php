<?php
namespace ItmoSoft\Loyalty\Tests;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use ItmoSoft\Loyalty\LoyaltyServiceProvider;
abstract class TestCase extends Orchestra {
 protected function getPackageProviders($app): array { return [LoyaltyServiceProvider::class]; }
 protected function setUp(): void {
  parent::setUp();
  config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','itmosoft-loyalty.prefix'=>'','itmosoft-loyalty.accounts_table'=>'itmosoft_loyalty_accounts','itmosoft-loyalty.transactions_table'=>'itmosoft_loyalty_transactions','itmosoft-loyalty.allow_negative_balance'=>false,'itmosoft-loyalty.minimum_redemption'=>1]);
  Schema::create('users',function(Blueprint $table){$table->id();$table->string('name');$table->timestamps();});
  $this->artisan('migrate')->run();
 }
}
