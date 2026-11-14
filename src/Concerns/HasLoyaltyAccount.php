<?php
namespace ItmoSoft\Loyalty\Concerns;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use ItmoSoft\Loyalty\Models\LoyaltyAccount;
trait HasLoyaltyAccount {
 public function loyaltyAccount(): MorphOne { return $this->morphOne(LoyaltyAccount::class, 'customer'); }
 public function getOrCreateLoyaltyAccount(): LoyaltyAccount {
  return $this->loyaltyAccount()->firstOrCreate([], ['balance'=>0,'currency'=>config('itmosoft-loyalty.default_currency','points')]);
 }
 public function loyaltyBalance(): int { return (int) ($this->loyaltyAccount?->balance ?? 0); }
}
